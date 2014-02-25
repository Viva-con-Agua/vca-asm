<?php

/**
 * VCA_ASM_Admin_Slot_Allocation class.
 *
 * This class contains properties and methods for
 * the handling of supporters applications to activities
 *
 * @package VcA Activity & Supporter Management
 * @since 1.1
 */

if ( ! class_exists( 'VCA_ASM_Admin_Slot_Allocation' ) ) :

class VCA_ASM_Admin_Slot_Allocation {

	/**
	 * Class Properties
	 *
	 * @since 1.3
	 */
	private $department = 'actions';
	private $departments = array(
		'actions' => 'Actions',
		'education' => 'Education',
		'network' => 'Network'
	);
	private $active_tab = 'apps';

	/**
	 * Initial admin page
	 *
	 * @since 1.3
	 * @access public
	 */
	public function control() {
		global $current_user,
			$vca_asm_activities, $vca_asm_utilities;

		$admin_city = get_user_meta( $current_user->ID, 'city', true );
		$admin_nation = get_user_meta( $current_user->ID, 'nation', true );

		if ( isset( $_GET['page'] ) ) {
			$dep = explode( '-', $_GET['page'] );
			if ( 'slot' === $dep[3] ) {
				switch ( $dep[2] ) {
					case 'education':
						$this->department = 'education';
					break;
					case 'network':
						$this->department = 'network';
					break;
				}
			}
		}

		if ( isset( $_GET['activity'] ) ) {
			$this->slot_allocation_control( $_GET['activity'] );
			return true;
		}

		$url = 'admin.php?page=vca-asm-' . $this->department . '-slot-allocation';
		$sort_url = $url;

		$activity_types = $vca_asm_activities->activities_by_department[$this->department] ? $vca_asm_activities->activities_by_department[$this->department] : array();

		/* table order */
		extract( $vca_asm_utilities->table_order( 'timeframe' ), EXTR_OVERWRITE );

		/***** QUERY *****/

		$pagination_args = array( 'pagination' => false );

		if ( empty( $activity_types ) ) {
			$rows = array();
			$empty_message = __( 'This department does not have any activities yet...', 'vca-asm' );
		} elseif ( isset( $_GET['activities'] ) && empty( $_GET['activities'] ) ) {
			$rows = array();
			$empty_message = __( 'You have to select at least one type of activity, if you want results to be shown here...', 'vca-asm' );
		} elseif ( isset( $_GET['phases'] ) && empty( $_GET['phases'] ) ) {
			$rows = array();
			$empty_message = __( 'You have to select at least one phase, if you want results to be shown here...', 'vca-asm' );
		} else {

			if ( isset( $_GET['activities'] ) ) {
				$post_types = $_GET['activities'];
				foreach ( $post_types as $type ) {
					$sort_url .= '&activities%5B%5D=' . $type;
				}
			} else {
				$post_types = array();
				foreach ( $activity_types as $type ) {
					$post_types[] = $type['slug'];
				}
			}

			$phases = isset( $_GET['phases'] ) ? $_GET['phases'] : array( 'app', 'ft' );

			$activities = array();

			foreach ( $phases as $phase ) {
				$sort_url .= '&phases%5B%5D=' . $phase;
				switch ( $phase ) {
					case 'bf':
						$phase_acts = get_posts( array(
							'post_type' => $post_types,
							'post_status' => 'publish',
							'numberposts' => 99999,
							'meta_query' => array(
								array(
									'key' => 'start_app',
									'value' => time(),
									'compare' => '>',
									'type' => 'numeric'
								)
							)
						));
						$activities = array_merge( $activities, $phase_acts );
					break;

					case 'app':
						$phase_acts = get_posts( array(
							'post_type' => $post_types,
							'post_status' => 'publish',
							'numberposts' => 99999,
							'meta_query' => array(
								'relation' => 'AND',
								array(
									'key' => 'start_app',
									'value' => time(),
									'compare' => '<=',
									'type' => 'numeric'
								),
								array(
									'key' => 'end_app',
									'value' => time() - 86400,
									'compare' => '>',
									'type' => 'numeric'
								)
							)
						));
						$activities = array_merge( $activities, $phase_acts );
					break;

					case 'ft':
						$phase_acts = get_posts( array(
							'post_type' => $post_types,
							'post_status' => 'publish',
							'numberposts' => 99999,
							'meta_query' => array(
								'relation' => 'AND',
								array(
									'key' => 'end_app',
									'value' => time() - 86400,
									'compare' => '<=',
									'type' => 'numeric'
								),
								array(
									'key' => 'end_act',
									'value' => time(),
									'compare' => '>=',
									'type' => 'numeric'
								)
							)
						));
						$activities = array_merge( $activities, $phase_acts );
					break;

					case 'pst':
						$phase_acts = get_posts( array(
							'post_type' => $post_types,
							'post_status' => 'publish',
							'numberposts' => 99999,
							'meta_query' => array(
								array(
									'key' => 'end_act',
									'value' => time(),
									'compare' => '<',
									'type' => 'numeric'
								)
							)
						));
						$activities = array_merge( $activities, $phase_acts );
					break;
				}
			}

			if ( empty( $activities ) ) {
				$rows = array();
			} else {

				if ( ! $current_user->has_cap( 'vca_asm_manage_' . $this->department . '_global' ) ) {
					$relevant_activities = array();
					foreach ( $activities as $activity ) {
						$cty_slots = get_post_meta( $activity->ID, 'cty_slots', true );
						if (
							(
								$current_user->has_cap( 'vca_asm_manage_' . $this->department . '_nation' ) &&
								get_post_meta( $activity->ID, 'nation', true ) === $admin_nation
							) || (
								$current_user->has_cap( 'vca_asm_manage_' . $this->department ) &&
								(
									(
										get_post_meta( $activity->ID, 'city', true ) === $admin_city &&
										'delegate' === get_post_meta( $activity->ID, 'delegate', true )
									) || (
										is_array( $cty_slots ) && array_key_exists( $admin_city, $cty_slots )
									)
								)
							)
						) {
							$relevant_activities[] = $activity;
						}
					}
					$activities = $relevant_activities;
				}

				$activities_ordered = array();
				$i = 0;
				foreach ( $activities as $key => $activity ) {
					if ( 'name' === $orderby ) {
						$activities_ordered[$i]['name'] = trim( $activity->post_title );
					} else if ( 'type' === $orderby ) {
						$activities_ordered[$i]['type'] = $vca_asm_activities->to_nicename[$activity->post_type];
					} else if ( 'timeframe' === $orderby ) {
						$activities_ordered[$i]['timeframe'] = get_post_meta( $activity->ID, 'start_act', true );
					} else if ( 'start_app' === $orderby ) {
						$activities_ordered[$i]['start_app'] = get_post_meta( $activity->ID, 'start_app', true );
					} else if ( 'end_app' === $orderby ) {
						$activities_ordered[$i]['end_app'] = get_post_meta( $activity->ID, 'end_app', true );
					} else if ( 'slots' === $orderby ) {
						$activities_ordered[$i]['slots'] = 0; /* currently not sortable */
					} else if ( 'applications' === $orderby ) {
						$activities_ordered[$i]['applications'] = 0; /* currently not sortable */
					} else if ( 'participants' === $orderby ) {
						$activities_ordered[$i]['participants'] = 0; /* currently not sortable */
					} else {
						$activities_ordered[$i][$orderby] = get_post_meta( $activity->ID, $orderby, true );
					}
					$activities_ordered[$i]['key'] = $key;
					$activities_ordered[$i]['object'] = $activity;
					$i++;
				}
				$activities_ordered = $vca_asm_utilities->sort_by_key( $activities_ordered, $orderby, $order );

				$act_cnt = count( $activities_ordered );
				if ( $act_cnt > 100 ) {
					$cur_page = isset( $_GET['p'] ) ? $_GET['p'] : 1;
					$pagination_offset = 100 * ( $cur_page - 1 );
					$total_pages = ceil( $act_cnt / 100 );
					$cur_end = $total_pages == $cur_page ? $pagination_offset + ( $act_cnt % 100 ) : $pagination_offset + 100;

					$pagination_args = array(
						'pagination' => true,
						'total_pages' => $total_pages,
						'current_page' => $cur_page
					);
				} else {
					$cur_page = 1;
					$pagination_offset = 0;
					$cur_end = $act_cnt;
				}

				$rows = array();
				$i = 0;
				foreach ( $activities_ordered as $activity ) {
					$activity_info = new VCA_ASM_Activity( $activity['object']->ID );

					$rows[$i]['id'] = $activity['object']->ID;
					$rows[$i]['name'] = $activity['object']->post_title;
					$rows[$i]['type'] = $vca_asm_activities->activities_to_nicename[$activity['object']->post_type] ?
						$vca_asm_activities->activities_to_nicename[$activity['object']->post_type] :
						$activity['object']->post_type;
					$rows[$i]['start_app'] = date( 'd. M Y', $activity_info->meta['start_app'][0] );
					$rows[$i]['end_app'] = date( 'd. M Y', $activity_info->meta['end_app'][0] );
					$rows[$i]['timeframe'] = date( 'd. M Y (H:i)', $activity_info->meta['start_act'][0] ) .
						'<br />' . __( 'until', 'vca-asm' ) . ' ' .
						date( 'd. M Y (H:i)', $activity_info->meta['end_act'][0] );
					$rows[$i]['slots'] = $activity_info->total_slots;
					$rows[$i]['applications'] = $activity_info->applicants_count;
					$rows[$i]['waiting'] = $activity_info->waiting_count;
					$rows[$i]['participants'] = $activity_info->participants_count;
					$i++;
				}

			}

			$empty_message = __( 'No activities for the current filter criteria...', 'vca-asm' );
		}

		/***** OUTPUT *****/

		$page = new VCA_ASM_Admin_Page(	array(
			'echo' => true,
			'icon' => 'icon-' . $this->department,
			'title' => __( 'Slots &amp; Participants', 'vca-asm' ) . ', ' . sprintf( __( '%s Department', 'vca-asm' ), $this->departments[$this->department] ),
			'url' => $url,
			'messages' => array()
		));

		$mbs = new VCA_ASM_Admin_Metaboxes( array(
			'echo' => true,
			'columns' => 1,
			'running' => 1,
			'id' => 'supporter-filter',
			'title' => _x( 'Filter Activities', 'Slot Allocation', 'vca-asm' ),
			'js' => false
		));

		$filter_fields = array(
			array(
				'id' => 'page',
				'type' => 'hidden',
				'value' => 'vca-asm-' . $this->department . '-slot-allocation'
			),
			array(
				'id' => 'phases',
				'type' => 'checkbox-group',
				'label' => __( 'Phase', 'vca-asm' ),
				'options' => array(
					array(
						'label' => __( 'before application phase', 'vca-asm' ),
						'value' => 'bf',
					),
					array(
						'label' => __( 'in application phase', 'vca-asm' ),
						'value' => 'app',
					),
					array(
						'label' => __( 'future activities where the application phase has ended', 'vca-asm' ),
						'value' => 'ft',
					),
					array(
						'label' => __( 'past activities', 'vca-asm' ),
						'value' => 'pst',
					)
				),
				'desc' => __( 'Filter activities by phase', 'vca-asm' ),
				'value' => ! empty( $_GET['phases'] ) ?  $_GET['phases'] : array( 'app', 'ft' ),
				'cols' => 1
			)
		);

		if ( 1 < count( $activity_types ) ) {
			$options = array();
			$default = array();
			foreach ( $activity_types as $activity ) {
				$options[] = array(
					'label' => $activity['name'],
					'value' => $activity['slug'],
				);
				$default[] = $activity['slug'];
			}

			$filter_fields[] = array(
				'id' => 'activities',
				'type' => 'checkbox-group',
				'label' => __( 'Activities', 'vca-asm' ),
				'options' => $options,
				'desc' => __( 'Show only some of this department&apos;s activitites', 'vca-asm' ),
				'value' => ! empty( $_GET['activities'] ) ?  $_GET['activities'] : $default,
				'cols' => 1
			);
		}

		$form = new VCA_ASM_Admin_Form( array(
			'echo' => true,
			'form' => true,
			'method' => 'get',
			'metaboxes' => false,
			'url' => $url,
			'action' => $url . '&todo=filter',
			'button' => __( 'Filter', 'vca-asm' ),
			'top_button' => false,
			'has_cap' => true,
			'fields' => $filter_fields
		));

		$columns = array(
			array(
				'id' => 'name',
				'title' => __( 'Activity', 'vca-asm' ),
				'strong' => true,
				'sortable' => true,
				'link' => array(
					'title' => __( 'Manage applicants & participants of %s', 'vca-asm' ),
					'title_row_data' => 'name',
					'url' => 'admin.php?page=vca-asm-' . $this->department . '-slot-allocation&activity=%d&tab=apps',
					'url_row_data' => 'id'
				),
				'actions' => array( 'manage_apps' ),
				'cap' => array( 'slots-'.$this->department )
			),
			array(
				'id' => 'type',
				'title' => __( 'Type', 'vca-asm' ),
				'sortable' => true,
				'actions' => array( 'edit_act' ),
				'cap' => array( 'edit-act-'.$this->department )
			),
			array(
				'id' => 'timeframe',
				'title' => __( 'Timeframe', 'vca-asm' ),
				'sortable' => true
			),
			array(
				'id' => 'start_app',
				'title' => __( 'Application Phase', 'vca-asm' ) . ' (' . __( 'Start', 'vca-asm' ) . ')',
				'sortable' => true
			),
			array(
				'id' => 'end_app',
				'title' => __( 'Application Phase', 'vca-asm' ) . ' (' . __( 'End', 'vca-asm' ) . ')',
				'sortable' => true
			),
			array(
				'id' => 'slots',
				'title' => __( 'Slots (open)', 'vca-asm' ),
				'sortable' => false
			),
			array(
				'id' => 'applications',
				'title' => __( 'Applications', 'vca-asm' ),
				'sortable' => false
			),
			array(
				'id' => 'participants',
				'title' => __( 'Participants', 'vca-asm' ),
				'sortable' => false
			)
		);

		$tbl_args = array(
			'page_slug' => 'vca-asm-slot-allocation',
			'base_url' => $url,
			'sort_url' =>  ! empty( $sort_url ) ? $sort_url : $url,
			'icon' => 'icon-'.$this->department,
			'headline' => sprintf( __( 'Activities of the %s department', 'vca-asm' ), $this->departments[$this->department] ),
			'empty_message' => ! empty( $empty_message ) ? $empty_message : '',
			'dspl_cnt' => true,
			'count' => ! empty( $act_cnt ) ? $act_cnt : 0,
			'cnt_txt' => __( '%d Activities', 'vca-asm' ),
			'orderby' => 'timeframe'
		);
		$tbl_args = array_merge( $tbl_args, $pagination_args );
		$tbl = new VCA_ASM_Admin_Table( $tbl_args, $columns, $rows );

		$page->top();

		$mbs->top();
		$mbs->mb_top();
		$form->output();
		$mbs->mb_bottom();
		$mbs->bottom();

		$tbl->output();

		$page->bottom();
	}

	/**
	 * Returns a query object of activities
	 * where the application phase has begun and the activity itself has not
	 *
	 * @since 1.1
	 * @access private
	 */
	private function get_activites_in_application_phase() {

		$args = array(
			'posts_per_page' 	=>	-1,
			'post_type'         =>	'festival',
			'post_status'       =>	'publish',
			'meta_query' => array(
				'relation' => 'AND',
				array(
					'key' => 'start_app',
					'value' => time(),
					'compare' => '<=',
					'type' => 'numeric'
				),
				array(
					'key' => 'start_date',
					'value' => time(),
					'compare' => '>=',
					'type' => 'numeric'
				)
			),
			'orderby'           =>	'title',
			'order'             =>	'ASC'

		);

		$activities = new WP_Query( $args );

		return $activities;
	}

	/**
	 * Slot allocation menu controller
	 *
	 * @since 1.2
	 * @access public
	 */
	public function slot_allocation_control( $activity_id ) {
		global $current_user, $vca_asm_registrations, $vca_asm_admin, $vca_asm_admin_supporters;

		$active_tab = isset( $_GET['tab'] ) ? $_GET['tab'] : 'apps';
		$this->active_tab = $active_tab;

		$the_activity = new VCA_ASM_Activity( $activity_id );

		$title = sprintf( __( 'Supporter Management for &quot;%s&quot;', 'vca-asm' ), $the_activity->name );

		$profile_url = 'admin.php?page=vca-asm-' . $this->department . '-slot-allocation&activity=' . $activity_id . '&tab=' . $active_tab;
		if( isset( $_GET['orderby'] ) ) {
			$profile_url .= '&orderby=' . $_GET['orderby'];
		}
		if( isset( $_GET['order'] ) ) {
			$profile_url .= '&order=' . $_GET['order'];
		}
		if( isset( $_GET['profile'] ) ) {
			$supporter = new VCA_ASM_Supporter( intval( $_GET['profile'] ) );
			if( $supporter->exists ) {
				$vca_asm_admin_supporters->supporter_profile( $supporter, $profile_url );
				return true;
			}
		}

		$messages = array();

		$success = 0;
		$slots_fail = false;

		if( isset( $_GET['id'] ) ) {
			$name = get_user_meta( $_GET['id'], 'first_name', true );
		} else {
			$multiple_names = '';
			$name_arr = array();
		}

		if ( isset( $_GET['todo'] ) ) {

			switch ( $_GET['todo'] ) {

				case "deny":
					if( isset( $_GET['id'] ) ) {
						$success = $vca_asm_registrations->deny_application( intval( $activity_id ), intval( $_GET['id'] ) );
					} elseif( isset( $_GET['supporters'] ) ) {
						foreach( $_GET['supporters'] as $supporter ) {
							$partial_success = $vca_asm_registrations->deny_application( intval( $activity_id ), intval( $supporter ) );
							$success = $success + intval( $partial_success );
							if( $partial_success > 0 ) {
								$tmp_name = get_user_meta( intval( $supporter ), 'first_name', true );
								$name_arr[] = ! empty( $tmp_name ) ? $tmp_name : __( 'unknown Supporter', 'vca-asm' );
							}
						}
						$last_name = array_shift( $name_arr );
						$multiple_names = implode( ', ', $name_arr ) . ' &amp; ' . $last_name;
					}
					if( $success > 1 ) {
						$messages[] = array(
							'type' => 'message-pa',
							'message' => sprintf( _x( 'Successfully moved %1$s (%2$d) to the waiting list.', 'Message', 'vca-asm' ), $multiple_names, $success )
						);
					} elseif( $success === 1 ) {
						if( ! empty( $name ) ) {
							$messages[] = array(
								'type' => 'message-pa',
								'message' => sprintf( _x( 'Successfully moved %s to the waiting list.', 'Message', 'vca-asm' ), $name )
							);
						} elseif( ! empty( $last_name ) ) {
							$messages[] = array(
								'type' => 'message-pa',
								'message' => sprintf( _x( 'Successfully moved %s to the waiting list.', 'Message', 'vca-asm' ), $last_name )
							);
						} else {
							$messages[] = array(
								'type' => 'message-pa',
								'message' => _x( 'Successfully moved one supporter to the waiting list.', 'Message', 'vca-asm' )
							);
						}
					} elseif( $success == 0 ) {
						$messages[] = array(
							'type' => 'error-pa',
							'message' => _x( 'Application denial not successful. Sorry.', 'Message', 'vca-asm' )
						);
					}
					unset( $_GET['todo'], $_GET['id'], $_GET['supporters'] );
					if( $success > 0 ) {
						$the_activity->reset();
						$active_tab = 'waiting';
					} else {
						$active_tab = 'apps';
					}
				break;

				case "accept":
					if( isset( $_GET['id'] ) ) {
						$free = $vca_asm_registrations->get_free_slots( intval( $activity_id ), intval( $_GET['id'] ) );
						if( $free > 0 ) {
							$success = $vca_asm_registrations->accept_application( intval( $activity_id ), intval( $_GET['id'] ) );
						} else {
							$slots_fail = true;
						}
					} elseif( isset( $_GET['supporters'] ) ) {
						foreach( $_GET['supporters'] as $supporter ) {
							$free = $vca_asm_registrations->get_free_slots( intval( $activity_id ), intval( $supporter ) );
							if( $free > 0 ) {
								$partial_success = $vca_asm_registrations->accept_application( intval( $activity_id ), intval( $supporter ) );
								$success = $success + intval( $partial_success );
								if( $partial_success > 0 ) {
									$tmp_name = get_user_meta( intval( $supporter ), 'first_name', true );
									$name_arr[] = ! empty( $tmp_name ) ? $tmp_name : __( 'unknown Supporter', 'vca-asm' );
								}
							} else {
								$slots_fail = true;
							}
						}
						$last_name = array_shift( $name_arr );
						$multiple_names = implode( ', ', $name_arr ) . ' &amp; ' . $last_name;
					}
					if( $success > 1 ) {
						$messages[] = array(
							'type' => 'message-pa',
							'message' => sprintf( _x( 'Successfully accepted the applications of %1$s (%2$d)', 'Message', 'vca-asm' ), $multiple_names, $success )
						);
					} elseif( $success === 1 ) {
						if( ! empty( $name ) ) {
							$messages[] = array(
								'type' => 'message-pa',
								'message' => sprintf( _x( 'Successfully accepted the applications of %s.', 'Message', 'vca-asm' ), $name )
							);
						} elseif( ! empty( $last_name ) ) {
							$messages[] = array(
								'type' => 'message-pa',
								'message' => sprintf( _x( 'Successfully accepted the application of %s.', 'Message', 'vca-asm' ), $last_name )
							);
						} else {
							$messages[] = array(
								'type' => 'message-pa',
								'message' => _x( 'Successfully accepted the application of one supporter.', 'Message', 'vca-asm' )
							);
						}
					} elseif( $success == 0 && true !== $slots_fail ) {
						$messages[] = array(
							'type' => 'error-pa',
							'message' => _x( 'Application acceptance not successful. Sorry.', 'Message', 'vca-asm' )
						);
					}
					if( true === $slots_fail && $success == 0 ) {
						$messages[] = array(
							'type' => 'error-pa',
							'message' => _x( 'Could not accept any applications of this contingent, all available slots have been assigned...', 'Message', 'vca-asm' )
						);
					} elseif( true === $slots_fail ) {
						$messages[] = array(
							'type' => 'error-pa',
							'message' => _x( 'Some applications however could not be accepted. It appears that you selected more supporters than free slots existed.', 'Message', 'vca-asm' )
						);
					}
					unset( $_GET['todo'], $_GET['id'], $_GET['supporters'] );
					if( $success > 0 ) {
						$the_activity->reset();
						$active_tab = 'accepted';
					} else {
						$active_tab = 'apps';
					}
				break;

				case "revoke":
					if( isset( $_GET['id'] ) ) {
						$success = $vca_asm_registrations->revoke_registration( intval( $activity_id ), intval( $_GET['id'] ) );
					} elseif( isset( $_GET['supporters'] ) ) {
						foreach( $_GET['supporters'] as $supporter ) {
							$partial_success = $vca_asm_registrations->revoke_registration( intval( $activity_id ), intval( $supporter ) );
							$success = $success + intval( $partial_success );
							if( $partial_success > 0 ) {
								$tmp_name = get_user_meta( intval( $supporter ), 'first_name', true );
								$name_arr[] = ! empty( $tmp_name ) ? $tmp_name : __( 'unknown Supporter', 'vca-asm' );
							}
						}
						$last_name = array_shift( $name_arr );
						$multiple_names = implode( ', ', $name_arr ) . ' &amp; ' . $last_name;
					}
					if( $success > 1 ) {
						$messages[] = array(
							'type' => 'message-pa',
							'message' => sprintf( _x( 'Successfully removed %1$s (%2$d) from participants.', 'Message', 'vca-asm' ), $multiple_names, $success )
						);
					} elseif( $success === 1 ) {
						if( ! empty( $name ) ) {
							$messages[] = array(
								'type' => 'message-pa',
								'message' => sprintf( _x( 'Successfully removed %s from participants.', 'Message', 'vca-asm' ), $name )
							);
						} elseif( ! empty( $last_name ) ) {
							$messages[] = array(
								'type' => 'message-pa',
								'message' => sprintf( _x( 'Successfully removed %s from participants.', 'Message', 'vca-asm' ), $last_name )
							);
						} else {
							$messages[] = array(
								'type' => 'message-pa',
								'message' => _x( 'Successfully removed one supporter from participants.', 'Message', 'vca-asm' )
							);
						}
					} elseif( $success == 0 ) {
						$messages[] = array(
							'type' => 'error-pa',
							'message' => _x( 'Participant removal not successful. Sorry.', 'Message', 'vca-asm' )
						);
					}
					unset( $_GET['todo'], $_GET['id'], $_GET['supporters'] );
					$active_tab = 'accepted';
					$the_activity->reset();
				break;
			}
		}

		if ( ! $the_activity->upcoming ) {
			$tabs = array(
				array(
					'value' => 'apps',
					'icon' => 'icon-applications',
					'title' => _x( 'Denied Applications', 'Slot Allocation Admin Menu', 'vca-asm' )
				),
				array(
					'value' => 'accepted',
					'icon' => 'icon-accepted-applications',
					'title' => _x( 'Participants', 'Slot Allocation Admin Menu', 'vca-asm' )
				),
				array(
					'value' => 'data',
					'icon' => 'icon-data',
					'title' => _x( 'Lists &amp; Mailing', 'Slot Allocation Admin Menu', 'vca-asm' )
				)
			);
		} else {
			$tabs = array(
				array(
					'value' => 'apps',
					'icon' => 'icon-applications',
					'title' => _x( 'Applications', 'Slot Allocation Admin Menu', 'vca-asm' )
				),
				array(
					'value' => 'waiting',
					'icon' => 'icon-waiting',
					'title' => _x( 'Waiting List', 'Slot Allocation Admin Menu', 'vca-asm' )
				),
				array(
					'value' => 'accepted',
					'icon' => 'icon-accepted-applications',
					'title' => _x( 'Participants', 'Slot Allocation Admin Menu', 'vca-asm' )
				),
				array(
					'value' => 'data',
					'icon' => 'icon-data',
					'title' => _x( 'Lists &amp; Mailing', 'Slot Allocation Admin Menu', 'vca-asm' )
				)
			);
			if ( false ) { // future feature
				$tabs[] = array(
					'value' => 'slots',
					'icon' => 'icon-supporters',
					'title' => _x( 'Quotas &amp; Slots', 'Slot Allocation Admin Menu', 'vca-asm' )
				);
			}
		}

		$page = new VCA_ASM_Admin_Page( array(
			'echo' => true,
			'icon' => 'icon-supporters',
			'title' => $title,
			'active_tab' => $active_tab,
			'url' => '?page=vca-asm-' . $this->department . '-slot-allocation&activity=' . $activity_id,
			'tabs' => $tabs,
			'messages' => $messages,
			'back' => true,
			'back_url' => '?page=vca-asm-' . $this->department . '-slot-allocation'
		));

		$page->top();

		switch ( $active_tab ) {

			case "slots":
				$this->reallocate_slots( $the_activity );
			break;

			case "data":
				$this->data_links( $the_activity );
			break;

			default:
				$this->slot_allocation_list( $the_activity, $active_tab );
			break;
		}

		$page->bottom();
	}

	/**
	 * Displays Links to download activity specific data
	 * and to contact participant groups
	 *
	 * @since 1.3
	 * @access private
	 */
	private function data_links( $the_activity ) {
		global $current_user, $vca_asm_registrations;
		get_currentuserinfo();

		$admin_city = get_user_meta( $current_user->ID, 'city', true );
		$admin_nation = get_user_meta( $current_user->ID, 'nation', true );

		$post_city = $the_activity->city;
		$post_nation = $the_activity->nation;
		$department = $the_activity->department;

		$applicants = array();
		$waiting = array();
		$participants = array();

		$excel_params = array(
			'relpath' => VCA_ASM_RELPATH,
			'pID' => $the_activity->ID
		);
		wp_localize_script( 'vca-asm-excel-export', 'excelParams', $excel_params );

		if (
			$current_user->has_cap( 'vca_asm_manage_' . $department . '_global' ) ||
			(
				$current_user->has_cap( 'vca_asm_manage_' . $department . '_nation' ) &&
				$admin_nation &&
				$admin_nation === $post_nation
			) ||
			(
				$current_user->has_cap( 'vca_asm_manage_' . $department ) &&
				$post_delegation &&
				$admin_city &&
				$admin_city === $post_city
			)
		) {
			$applicants = $the_activity->applicants;
			$waiting = $the_activity->waiting;
			$participants = $the_activity->participants;
			$scope = 'global';
		} elseif (
			$current_user->has_cap( 'vca_asm_manage_' . $department . '_nation' ) &&
			$admin_nation
		) {
			$scope = 'nation';
			if (
				array_key_exists( $admin_nation, $the_activity->applicants_count_by )
			) {
				$applicants = $the_activity->applicants_by_quota[$admin_nation];
			}
			if (
				array_key_exists( $admin_nation, $the_activity->waiting_count_by_quota )
			) {
				$waiting = $the_activity->waiting_by_quota[$admin_nation];
			}
			if (
				array_key_exists( $admin_nation, $the_activity->participants_count_by_quota )
			) {
				$participants = $the_activity->participants_by_quota[$admin_nation];
			}
		} elseif (
			$current_user->has_cap( 'vca_asm_manage_' . $department ) &&
			$admin_city
		) {
			$scope = 'city';
			if (
				array_key_exists( $admin_city, $the_activity->applicants_count_by_slots )
			) {
				$applicants = $the_activity->applicants_by_slots[$admin_city];
			}
			if (
				array_key_exists( $admin_city, $the_activity->waiting_count_by_slots )
			) {
				$waiting = $the_activity->waiting_by_slots[$admin_city];
			}
			if (
				array_key_exists( $admin_city, $the_activity->participants_count_by_slots )
			) {
				$participants = $the_activity->participants_by_slots[$admin_city];
			}
		}

		$mb_args = array(
			'echo' => true,
			'columns' => 1,
			'running' => 1,
			'id' => '',
			'title' => __( 'Lists', 'vca-asm' ),
			'js' => false
		);
		$mb_env = new VCA_ASM_Admin_Metaboxes( $mb_args );

		$mb_env->top();

		$mb_env->mb_top();
			$output = '<table class="table-inside-table table-mobile-collapse subtable">';

			$output .= '<tr><td>' .
					'<strong>' . __( 'Applicants', 'vca-asm' ) . '</strong>' .
				'</td></tr>';
			if ( ! empty( $applicants ) ) {
				$output .= '<tr><td>' .
						'<a id="excel-download" href="#spreadsheet-full" onclick="p1exportExcel(\'applicants\');">' .
							__( 'Download applicant data as an MS Excel spreadsheet', 'vca-asm' ) .
							' (' . _x( 'including sensitive data, never (!) forward', 'non-sensitive data', 'vca-asm' ) . ')' .
						'</a>' .
					'</td></tr><tr><td>' .
						'<a id="excel-download-minimal" href="#spreadsheet-minimal" onclick="p1exportExcelMin(\'applicants\');">' .
							__( 'Download applicant data as an MS Excel spreadsheet', 'vca-asm' ) .
							' (' . _x( 'safe to forward', 'non-sensitive data', 'vca-asm' ) . ')' .
						'</a>' .
					'</td></tr>';
			} else {
				$output .= '<tr><td>' .
						__( 'Currently no applicants', 'vca-asm' ) .
					'</td></tr>';
			}
			if ( $the_activity->upcoming ) {
				$output .= '<tr><td style="padding-top:1em">' .
						'<strong>' . __( 'Waiting List', 'vca-asm' ) . '</strong>' .
					'</td></tr>';
				if ( ! empty( $waiting ) ) {
					$output .= '<tr><td>' .
							'<a id="excel-download" href="#spreadsheet-full" onclick="p1exportExcel(\'waiting\');">' .
								__( 'Download waiting list as an MS Excel spreadsheet', 'vca-asm' ) .
								' (' . _x( 'including sensitive data, never (!) forward', 'non-sensitive data', 'vca-asm' ) . ')' .
							'</a>' .
						'</td></tr><tr><td>' .
							'<a id="excel-download-minimal" href="#spreadsheet-minimal" onclick="p1exportExcelMin(\'waiting\');">' .
								__( 'Download waiting list as an MS Excel spreadsheet', 'vca-asm' ) .
								' (' . _x( 'safe to forward', 'non-sensitive data', 'vca-asm' ) . ')' .
							'</a>' .
						'</td></tr>';
				} else {
					$output .= '<tr><td>' .
							__( 'Waiting List currently empty', 'vca-asm' ) .
						'</td></tr>';
				}
			}
			$output .= '<tr><td style="padding-top:1em">' .
						'<strong>' . __( 'Participants', 'vca-asm' ) . '</strong>' .
					'</td></tr>';
			if ( ! empty( $participants ) ) {
				$output .= '<tr><td>' .
						'<a id="excel-download" href="#spreadsheet-full" onclick="p1exportExcel(\'participants\');">' .
							__( 'Download participant data as an MS Excel spreadsheet', 'vca-asm' ) .
							' (' . _x( 'including sensitive data, never (!) forward', 'non-sensitive data', 'vca-asm' ) . ')' .
						'</a>' .
					'</td></tr><tr><td>' .
						'<a id="excel-download-minimal" href="#spreadsheet-minimal" onclick="p1exportExcelMin(\'participants\');">' .
							__( 'Download participant data as an MS Excel spreadsheet', 'vca-asm' ) .
							' (' . _x( 'safe to forward', 'non-sensitive data', 'vca-asm' ) . ')' .
						'</a>' .
					'</td></tr>';
			} else {
				$output .= '<tr><td>' .
						__( 'No participants yet', 'vca-asm' ) .
					'</td></tr>';
			}

			$output .= '<iframe id="excel-frame" src="" style="display:none; visibility:hidden;"></iframe>' .
					'</table>';
			echo $output;
		$mb_env->mb_bottom();

		$mb_env->mb_top( array( 'title' =>  __( 'E-Mails', 'vca-asm' ) ) );
			$output = '<table class="table-inside-table table-mobile-collapse subtable">';

			$output .= '<tr><td>' .
					'<strong>' . __( 'Applicants', 'vca-asm' ) . '</strong>' .
				'</td></tr>';
			if ( ! empty( $applicants ) ) {
				$output .= '<tr><td>' .
						'<a href="' .
							get_bloginfo('url') . '/wp-admin/admin.php?page=vca-asm-compose&tab=activity&activity=' . $the_activity->ID . '&group=apps' .
						'">';
							if ( $the_activity->upcoming ) {
								$output .= __( 'Send an email to all current applicants', 'vca-asm' );
							} else {
								$output .= __( 'Send an email to all (denied) applicants', 'vca-asm' );
							}
						$output .= '</a>' .
					'</td></tr>';
			} else {
				$output .= '<tr><td>' .
						__( 'Currently no applicants', 'vca-asm' ) .
					'</td></tr>';
			}
			if ( $the_activity->upcoming ) {
				$output .= '<tr><td style="padding-top:1em">' .
						'<strong>' . __( 'Waiting List', 'vca-asm' ) . '</strong>' .
					'</td></tr>';
				if ( ! empty( $waiting ) ) {
					$output .= '<tr><td>' .
							'<a href="' .
								get_bloginfo('url') . '/wp-admin/admin.php?page=vca-asm-compose&tab=activity&activity=' . $the_activity->ID . '&group=waiting' .
							'">' .
								__( 'Send an email to all supporters currently on the waiting list', 'vca-asm' ) .
							'</a>' .
						'</td></tr>';
				} else {
					$output .= '<tr><td>' .
							__( 'Waiting List currently empty', 'vca-asm' ) .
						'</td></tr>';
				}
			}
			$output .= '<tr><td style="padding-top:1em">' .
					'<strong>' . __( 'Participants', 'vca-asm' ) . '</strong>' .
				'</td></tr>';
			if ( ! empty( $participants ) ) {
				$output .= '<tr><td>' .
						'<a href="' .
							get_bloginfo('url') . '/wp-admin/admin.php?page=vca-asm-compose&tab=activity&activity=' . $the_activity->ID . '&group=parts' .
						'">';
							if ( $the_activity->upcoming ) {
								$output .= __( 'Send an email to all accepted applicants', 'vca-asm' );
							} else {
								$output .= __( 'Send an email to all supporters that participated', 'vca-asm' );
							}
						$output .= '</a>' .
					'</td></tr>';
			} else {
				$output .= '<tr><td>' .
						__( 'No participants yet', 'vca-asm' ) .
					'</td></tr>';
			}

			$output .= '</table>';
			echo $output;
		$mb_env->mb_bottom();

		$mb_env->bottom();
	}

	/**
	 * List all supporters
	 * - applying to the currently selected activity
	 * - or on the waiting list for the currently selected activity
	 * - or accepted to the currently selected activity
	 *
	 * @since 1.2
	 * @access private
	 */
	private function slot_allocation_list( $the_activity, $list_type = 'apps' ) {
		global $current_user, $wpdb, $vca_asm_admin_supporters, $vca_asm_geography, $vca_asm_registrations, $vca_asm_utilities;
		get_currentuserinfo();

		$admin_city = get_user_meta( $current_user->ID, 'city', true );
		$admin_city_status = $vca_asm_geography->get_status( $admin_city );
		$admin_nation = get_user_meta( $current_user->ID, 'nation', true );

		$columns = array(
			array(
				'id' => 'avatar',
				'title' => __( 'Photo', 'vca-asm' ),
				'sortable' => false,
				'mobile' => false
			),
			array(
				'id' => 'first_name',
				'title' => __( 'First Name', 'vca-asm' ),
				'sortable' => true,
				'strong' => true,
				'actions' => array(),
				'cap' => array( 'manage-'.$list_type )
			),
			array(
				'id' => 'last_name',
				'title' => __( 'Last Name', 'vca-asm' ),
				'sortable' => true,
				'strong' => true
			),
			array(
				'id' => 'note',
				'title' => __( 'Note', 'vca-asm' ),
				'sortable' => true
			)
		);

		switch( $list_type ) {
			case 'waiting':
				$columns[1]['actions'][] = 'waitinglist_accept';
			break;

			case 'accepted':
				$columns[1]['actions'][] = 'revoke_accepted';
			break;

			case 'apps':
			default:
				$columns[1]['actions'][] = 'app_accept';
				$columns[1]['actions'][] = 'app_deny';
			break;
		}

		$columns[] = array(
			'id' => 'city',
			'title' => __( 'City', 'vca-asm' ),
			'sortable' => true,
			'legacy-mobile' => false
		);
		$columns[] = array(
			'id' => 'membership',
			'title' => __( 'Membership Status', 'vca-asm' ),
			'sortable' => true,
			'conversion' => 'membership',
			'legacy-screen' => false
		);
		$columns[] = array(
			'id' => 'user_email',
			'title' => __( 'Email Address', 'vca-asm' ),
			'sortable' => true,
			'tablet' => false
		);
		$columns[] = array(
			'id' => 'mobile',
			'title' => __( 'Mobile Phone', 'vca-asm' ),
			'sortable' => true
		);
		$columns[] = array(
			'id' => 'age',
			'title' => __( 'Age', 'vca-asm' ),
			'sortable' => true,
			'tablet' => false
		);
		$columns[] = array(
			'id' => 'gender',
			'title' => __( 'Gender', 'vca-asm' ),
			'sortable' => true,
			'legacy-screen' => false
		);

		$bulk_actions = array();

		if ( ! $the_activity->upcoming ) {

			switch( $list_type ) {
				case "accepted":
					$supporters_by_quota = $the_activity->participants_by_slots;
					$nottin = __( 'Nobody participated in this activity', 'vca-asm' );
					$list_nicename = __( 'Participants', 'vca-asm' );
					$bulk_btn = __( 'Remove!', 'vca-asm' );
					$bulk_confirm = __( 'Remove the supporters from the list of participants?', 'vca-asm' ) .
						'\\n\\n' .
						__( 'Attention: This cannot be undone!', 'vca-asm' );
					$bulk_desc = __( 'Remove selected supporters from the participants list', 'vca-asm' );
					$bulk_actions = array( 0 => array( 'value' => 'removepart' ) );
				break;

				default:
				case "apps":
					$supporters_by_quota = $the_activity->applicants_by_slots;
					$nottin = __( 'No applications were denied for this activity', 'vca-asm' );
					$list_nicename = __( 'Denied Applications', 'vca-asm' );
					$bulk_btn = __( 'Remove!', 'vca-asm' );
					$bulk_confirm = __( 'Remove the supporters from the list of denied applications?', 'vca-asm' ) .
						'\\n\\n' .
						__( 'Attention: This cannot be undone!', 'vca-asm' );
					$bulk_desc = __( 'Remove selected supporters from the list of denied applications', 'vca-asm' );
					$bulk_actions = array( 0 => array( 'value' => 'removeapp' ) );
				break;
			}

		} else {

			switch( $list_type ) {
				case "waiting":
					$supporters_by_quota = $the_activity->waiting_by_slots;
					$nottin = __( 'Currently, this waiting list is empty...', 'vca-asm' );
					$list_nicename = __( 'Waiting List', 'vca-asm' );
					$bulk_btn = __( 'Accept application(s) in retrospect', 'vca-asm' );
					$bulk_confirm = __( 'Accept all selected applications and move the selected supporters from the waiting list to participants?', 'vca-asm' );
					$bulk_desc = __( 'Move selected supporters to participants', 'vca-asm' );
					$bulk_actions = array( 0 => array( 'value' => 'accept' ) );
				break;

				case "accepted":
					$supporters_by_quota = $the_activity->participants_by_slots;
					$nottin = __( 'So far, no applications have been accepted yet...', 'vca-asm' );
					$list_nicename = __( 'Participants', 'vca-asm' );
					$bulk_btn = __( 'Revoke!', 'vca-asm' );
					$bulk_confirm = __( 'Revoke all selected accepted applications and remove the supporters from the list of participants?', 'vca-asm' ) .
						'\\n\\n' .
						__( 'Attention: This does not move the supporters to the waiting list - it removes them from the participants entirely!', 'vca-asm' );
					$bulk_desc = __( 'Revoke selected accepted applications', 'vca-asm' );
					$bulk_actions = array( 0 => array( 'value' => 'revoke' ) );
				break;

				default:
				case "apps":
					$supporters_by_quota = $the_activity->applicants_by_slots;
					$nottin = __( 'No current applications...', 'vca-asm' );
					$list_nicename = __( 'Applications', 'vca-asm' );
					$bulk_btn = __( 'Execute', 'vca-asm' );
					$bulk_confirm = __( 'Manage all selected applications?', 'vca-asm' );
					$bulk_desc = __( 'Handle selected applications', 'vca-asm' );
					$bulk_actions = array(
						array(
							'value' => 'accept',
							'label' => __( 'Accept', 'vca-asm' )
						),
						array(
							'value' => 'deny',
							'label' => __( 'Deny', 'vca-asm' )
						)
					);
				break;
			}

		}

		$url = 'admin.php?page=vca-asm-' . $this->department . '-slot-allocation&activity=' . $the_activity->ID . '&tab=' . $list_type;
		$tbl_args = array(
			'page_slug' => 'vca-asm-' . $this->department . '-slot-allocation',
			'base_url' => $url,
			'sort_url' => $url,
			'icon' => 'icon-supporter',
			'show_empty_message' => true,
			'empty_message' => $nottin,
			'dspl_cnt' => false,
			'count' => 0,
			'cnt_txt' => '',
			'with_bulk' => true,
			'bulk_btn' => $bulk_btn ? $bulk_btn : __( 'Execute', 'vca-asm' ),
			'bulk_confirm' => $bulk_confirm ? $bulk_confirm : '',
			'bulk_name' => 'supporters',
			'bulk_param' => 'todo',
			'bulk_desc' => $bulk_desc ? $bulk_desc : '',
			'extra_bulk_html' => '<input type="hidden" name="activity" value="' . $the_activity->ID . '" />',
			'bulk_actions' => $bulk_actions ? $bulk_actions : array()
		);

		$tables = array();
		if (
			$current_user->has_cap(  'vca_asm_manage_' . $this->department . '_global' )
			|| (
				$current_user->has_cap(  'vca_asm_manage_' . $this->department . '_nation' ) &&
				$the_activity->nation === $admin_nation
			) || (
				$the_activity->delegation === 'delegate' &&
				$the_activity->city === $admin_city
			)
		) {
			if ( 0 < $the_activity->global_slots || ! empty( $supporters_by_quota[1410065407] ) ) {
				$tables[0] = array(
					'headline' => _x( 'General', 'female', 'vca-asm' ) . ' ' . $list_nicename,
					'quota' => 0,
					'slots' => $the_activity->global_slots,
					'supps_of_quota' => ! empty( $supporters_by_quota[1410065407] ) ? $supporters_by_quota[1410065407] :
						( isset( $supporters_by_quota[0] ) ? $supporters_by_quota[0] : array() )
				);
			}
			foreach ( $the_activity->ctr_slots as $ctr => $slots ) {
				if ( 0 < $slots ) {
					$tables[] = array(
						'headline' => $list_nicename . ', ' . $vca_asm_geography->get_name( $ctr ),
						'quota' => $ctr,
						'slots' => $slots,
						'supps_of_quota' => isset( $supporters_by_quota[$ctr] ) ? $supporters_by_quota[$ctr] : array()
					);
				}
			}
			foreach ( $the_activity->cty_slots as $cty => $slots ) {
				if ( 0 < $slots ) {
					$tables[] = array(
						'headline' => $list_nicename . ', ' . $vca_asm_geography->get_status( $cty ) . ' ' . $vca_asm_geography->get_name( $cty ),
						'quota' => $cty,
						'slots' => $slots,
						'supps_of_quota' => isset( $supporters_by_quota[$cty] ) ? $supporters_by_quota[$cty] : array()
					);
				}
			}
		} elseif ( current_user_can( 'vca_asm_manage_' . $this->department . '_nation' ) ) {
			$admin_nation = $vca_asm_geography->has_nation( $admin_city );
			if ( $admin_nation ) {
				if ( isset( $the_activity->ctr_slots[$admin_nation] ) && 0 < $the_activity->ctr_slots[$admin_nation] ) {
					$tables[] = array(
						'headline' => $list_nicename . ', ' . $vca_asm_geography->get_name( $admin_nation ),
						'quota' => $admin_nation,
						'slots' => $the_activity->ctr_slots[$admin_nation],
						'supps_of_quota' => isset( $supporters_by_quota[$admin_nation] ) ? $supporters_by_quota[$admin_nation] : array()
					);
				}
				foreach ( $the_activity->cty_slots as $cty => $slots ) {
					$cty_ctr = $vca_asm_geography->has_nation( $cty );
					if ( $cty_ctr && $cty_ctr === $admin_nation && 0 < $slots ) {
						$tables[] = array(
							'headline' => $list_nicename . ', ' . $vca_asm_geography->get_status( $cty ) . ' ' . $vca_asm_geography->get_name( $cty ),
							'quota' => $cty,
							'slots' => $slots,
							'supps_of_quota' => isset( $supporters_by_quota[$cty] ) ? $supporters_by_quota[$cty] : array()
						);
					}
				}
			}
		} elseif ( current_user_can( 'vca_asm_manage_' . $this->department ) ) {
			if ( isset( $the_activity->cty_slots[$admin_city] ) && 0 < $the_activity->cty_slots[$admin_city] ) {
				$tables[] = array(
					'headline' => $list_nicename . ', ' . $vca_asm_geography->get_status( $admin_city ) . ' ' . $vca_asm_geography->get_name( $admin_city ),
					'quota' => $admin_city,
					'slots' => $the_activity->cty_slots[$admin_nation],
					'supps_of_quota' => isset( $supporters_by_quota[$admin_city] ) ? $supporters_by_quota[$admin_city] : array()
				);
			}
		}

		$empty_flag = true;
		foreach ( $tables as $table ) {
			$occupied = isset( $the_activity->participants_count_by_slots[$table['quota']] ) ?
				$the_activity->participants_count_by_slots[$table['quota']] :
				0;
			$free = $table['slots'] - $occupied;

			$tbl_args['headline'] = $table['headline'] .
				'<br />' . sprintf( __( 'Slots: %1$s, of which %2$s are free', 'vca-asm' ), $table['slots'], $free );

			$rows = $this->gimme_rows( $table['supps_of_quota'], $the_activity->ID, $list_type );
			if ( ! empty( $rows ) ) {
				$empty_flag = false;
			}

			$tbl = new VCA_ASM_Admin_Table( $tbl_args, $columns, $rows );

			$tbl->output();
		}

		/* Possibly a future feature
		if ( ! $empty_flag ) {

			$excel_params = array(
				'relpath' => VCA_ASM_RELPATH,
				'pID' => $the_activity->ID
			);
			wp_localize_script( 'vca-asm-excel-export', 'excelParams', $excel_params );

			$mb_args = array(
				'echo' => true,
				'columns' => 1,
				'running' => 1,
				'id' => '',
				'title' => __( 'Lists &amp; Mailing', 'vca-asm' ),
				'js' => false
			);
			$mb_env = new VCA_ASM_Admin_Metaboxes( $mb_args );

			$mb_env->top();
			$mb_env->mb_top();

			switch ( $list_type ) {

				case 'apps':

				break;

				case 'waiting':

				break;

				case 'participants':
				default:

				break;

			}

			$mb_env->mb_bottom();
			$mb_env->bottom();
		}*/
	}

	/**
	 * Populates the rows for a particular table
	 *
	 * @since 1.3
	 * @access private
	 */
	private function gimme_rows( $supps_of_quota, $activity_id = 0, $list_type = 'apps' ) {
		global $vca_asm_geography, $vca_asm_utilities, $vca_asm_admin_supporters;

		$rows = array();
		$soq_count = count( $supps_of_quota );

		$cities = $vca_asm_geography->get_ids();
		$stati = $vca_asm_geography->get_stati();
		$stati_conv = $vca_asm_geography->get_stati_conv();

		$stati = $vca_asm_geography->get_stati();

		for ( $i = 0; $i < $soq_count; $i++ ) {
			$supp_id = intval( $supps_of_quota[$i] );
			$supp_region = get_user_meta( $supp_id, 'city', true );
			$supp_nation = get_user_meta( $supp_id, 'nation', true );
			$supp_bday = get_user_meta( $supp_id, 'birthday', true );
			$supp_age = ! empty( $supp_bday ) ? $vca_asm_utilities->date_diff( time(), intval( $supp_bday ) ) : array( 'year' => __( 'not set', 'vca-asm' ) );
			$supp_info = get_userdata( $supp_id );
			$avatar = get_avatar( $supp_id, 32 );
			$photo_info = $vca_asm_admin_supporters->photo_info( $supp_id );
			$avatar_tooltip = '<span class="photo-tooltip" onmouseover="tooltip(' . $photo_info . ');" onmouseout="exit();">' .
				$avatar .
				'</span>';
			$note_info = $vca_asm_admin_supporters->note_info( $activity_id, $supp_id, $list_type );
			if( ! empty( $note_info ) ) {
				$note_indicator = '<span class="note-tooltip tooltip-trigger" onmouseover="tooltip(' . $note_info . ');" onmouseout="exit();">' .
						__( 'YES!', 'vca-asm' ) .
					'</span>';
			} else {
				$note_indicator = __( 'None', 'vca-asm' );
			}

			$rows[$i]['check'] = $supp_id;
			$rows[$i]['avatar'] = $avatar_tooltip;
			$rows[$i]['id'] = $supp_id;
			$rows[$i]['username'] = $supp_info->user_name;
			$rows[$i]['first_name'] = get_user_meta( $supp_id, 'first_name', true );
			$rows[$i]['last_name'] = get_user_meta( $supp_id, 'last_name', true );
			$rows[$i]['user_email'] = $supp_info->user_email;
			$db_num = get_user_meta( $supp_id, 'mobile', true );
			$rows[$i]['mobile'] = $vca_asm_utilities->normalize_phone_number(
				$db_num,
				array( 'nice' => true, 'nat_id' => $supp_nation ? $supp_nation : 0 )
			);
			$raw_num = $vca_asm_utilities->normalize_phone_number( $db_num, array( 'nat_id' => $supp_nation ? $supp_nation : 0 ) );
			$rows[$i]['mobile-order'] = empty( $raw_num ) ? '999999999999999' : substr( $raw_num . '0000000000000000000', 0, 15 );
			$rows[$i]['city'] = $cities[$supp_region];
			if( $supp_region != 0 ) {
				$rows[$i]['city'] .= ' (' . $stati_conv[$supp_region] . ')';
			}
			$rows[$i]['note'] = $note_indicator;
			$geo_status = isset( $stati[$supp_region] ) ? $stati[$supp_region] : 'city';
			$rows[$i]['membership'] = $vca_asm_admin_supporters->get_membership_status( $supp_id, $geo_status );
			$rows[$i]['membership_raw'] = get_user_meta( $supp_id, 'membership', true );
			$rows[$i]['age'] = $supp_age['year'];
			$rows[$i]['age-order'] = empty( $supp_bday ) ? 1 : ( doubleval(555555555555) - doubleval( $supp_bday ) );
			$rows[$i]['gender'] = $vca_asm_utilities->convert_strings( get_user_meta( $supp_id, 'gender', true ) );
		}

		if( isset( $_GET['orderby'] ) ) {
			$orderby = $_GET['orderby'];
		} else {
			$orderby = 'first_name';
		}
		if( isset( $_GET['order'] ) ) {
			$order = $_GET['order'];
			if( $order == 'ASC') {
				$toggle_order = 'DESC';
			} else {
				$toggle_order = 'ASC';
			}
		} else {
			$order = 'ASC';
			$toggle_order = 'DESC';
		}

		if ( 'age' === $orderby || 'mobile' === $orderby ) {
			$rows = $vca_asm_utilities->sort_by_key( $rows, $orderby . '-order', $order );
		} else {
			$rows = $vca_asm_utilities->sort_by_key( $rows, $orderby, $order );
		}

		return $rows;
	}

	/**
	 * Reallocate Slots
	 *
	 * @since 1.2
	 * @access private
	 */
	private function reallocate_slots( $the_activity ) {
		$feech = new VCA_ASM_Admin_Future_Feech( array(
			'echo' => true,
			'headline' => 'Kontingente &amp; Plätze',
			'title' => sprintf( 'Kontingente für %s', $the_activity->name ),
			'explanation' => 'Hier wird in Zukunft analog zum Aktivitätsmenü die Verteilung von Plätzen auf Kontingente vorgenommen werden können. (sofern erforderliche Rechte vorhanden sind, versteht sich)',
			'version' => '1.3.1'
		));
		$feech->output();
	}

	/**
	 * Constructor
	 *
	 * @since 1.3
	 * @access public
	 */
	public function __construct() {
		$this->departments = array(
			'actions' => _x( 'Actions', 'Department Name', 'vca-asm' ),
			'education' => _x( 'Education', 'Department Name', 'vca-asm' ),
			'network' => _x( 'Network', 'Department Name', 'vca-asm' )
		);
	}

} // class

endif; // class exists

?>