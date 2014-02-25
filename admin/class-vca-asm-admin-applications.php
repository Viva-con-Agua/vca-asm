<?php

/**
 * VCA_ASM_Admin_Applications class.
 *
 * This class contains properties and methods for
 * the handling of supporters applications to activities
 *
 * @package VcA Activity & Supporter Management
 * @since 1.1
 */

if ( ! class_exists( 'VCA_ASM_Admin_Applications' ) ) :

class VCA_ASM_Admin_Applications {

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
	 * Returns an array of activities
	 * and correspinding relevant data
	 * depending on the admin user's capabilities
	 *
	 * @since 1.1
	 * @access private
	 */
	private function get_activities_data() {
		global $current_user, $vca_asm_registrations;
		get_currentuserinfo();

		$activities_obj = $this->get_activites_in_application_phase();
		$activities = array();
		$admin_region = get_user_meta( $current_user->ID, 'region', true );

		while ( $activities_obj->have_posts() ) : $activities_obj->the_post();

			$slots_arr = get_post_meta( get_the_ID(), 'slots', true );
			$post_region = get_post_meta( get_the_ID(), 'region', true );
			$delegation = get_post_meta( get_the_ID(), 'delegate', true );

			if( current_user_can( 'vca_asm_manage_all_applications' ) || ( $delegation == 'delegate' && $post_region == $admin_region ) ) {
				$app_count = $vca_asm_registrations->get_activity_application_count( get_the_ID() );
				$wait_count = $vca_asm_registrations->get_activity_waiting_count( get_the_ID() );
				$reg_count = $vca_asm_registrations->get_activity_registration_count( get_the_ID() );
			} else {
				if( ! array_key_exists( $admin_region, $slots_arr ) ) {
					continue;
				}
				$app_count = $vca_asm_registrations->get_activity_application_count( get_the_ID(), $admin_region );
				$wait_count = $vca_asm_registrations->get_activity_waiting_count( get_the_ID(), $admin_region );
				$reg_count = $vca_asm_registrations->get_activity_registration_count( get_the_ID(), $admin_region );
			}

			$activities[get_the_ID()] = array(
				'id' => get_the_ID(),
				'title' => get_the_title(),
				'slots' => $slots_arr,
				'applications' => $app_count,
				'waiting' => $wait_count,
				'registrations' => $reg_count
			);

		endwhile;

		wp_reset_postdata();

		if( ! empty( $activities ) ) {
			return $activities;
		} else {
			return false;
		}
	}

	/**
	 * Slot allocation menu controller
	 * groups the previously separate three slot menus in tabs
	 *
	 * @since 1.2
	 * @access public
	 */
	public function slot_allocation_control() {
		global $current_user, $vca_asm_registrations, $vca_asm_admin, $vca_asm_admin_supporters;

		$active_tab = isset( $_GET['tab'] ) ? $_GET['tab'] : 'apps';

		$activities = $this->get_activities_data();

		$activity = isset( $_GET['activity'] ) ? $activities[$_GET['activity']] : array_shift( array_slice( $activities, 0, 1 ) );
		$title = isset( $_GET['activity'] ) ? sprintf( __( 'Slot Allocation for &quot;%s&quot;', 'vca-asm' ), $activity['title'] ) : __( 'Slot Allocation', 'vca-asm' );
		$url_qs = '&activity=' . $activity['id'];

		$profile_url = 'admin.php?page=vca-asm-slot-allocation' . $url_qs . '&tab=' . $active_tab;
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

		$output = '<div class="wrap"><div id="icon-supporter" class="icon32-pa"></div><h2>' .
				$title .
			'</h2><br />';

		if( false === $activities ) {
			$output .= '<div class="error"><p>' .
					_x( 'Currently no slots need to be allocated...', 'Slot Allocation', 'vca-asm' ) .
				'</p></div>';
			echo $output;
			return false;
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

		switch ( $_GET['todo'] ) {

			case "deny":
				if( isset( $_GET['id'] ) ) {
					$success = $vca_asm_registrations->deny_application( intval( $activity['id'] ), intval( $_GET['id'] ) );
				} elseif( isset( $_GET['supporters'] ) ) {
					foreach( $_GET['supporters'] as $supporter ) {
						$partial_success = $vca_asm_registrations->deny_application( intval( $activity['id'] ), intval( $supporter ) );
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
					$active_tab = 'waiting';
				} else {
					$active_tab = 'apps';
				}
			break;

			case "accept":
				if( isset( $_GET['id'] ) ) {
					$region = intval( get_user_meta( intval( $_GET['id'] ), 'region', true ) );
					$free = $vca_asm_registrations->get_free_slots( intval( $activity['id'] ), $region );
					if( $free > 0 ) {
						$success = $vca_asm_registrations->accept_application( intval( $activity['id'] ), intval( $_GET['id'] ) );
					} else {
						$slots_fail = true;
					}
				} elseif( isset( $_GET['supporters'] ) ) {
					foreach( $_GET['supporters'] as $supporter ) {
						$region = intval( get_user_meta( intval( $supporter ), 'region', true ) );
						$free = $vca_asm_registrations->get_free_slots( intval( $activity['id'] ), $region );
						if( $free > 0 ) {
							$partial_success = $vca_asm_registrations->accept_application( intval( $activity['id'] ), intval( $supporter ) );
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
					$active_tab = 'accepted';
				} else {
					$active_tab = 'apps';
				}
			break;

			case "revoke":
				if( isset( $_GET['id'] ) ) {
					$success = $vca_asm_registrations->revoke_registration( intval( $activity['id'] ), intval( $_GET['id'] ) );
				} elseif( isset( $_GET['supporters'] ) ) {
					foreach( $_GET['supporters'] as $supporter ) {
						$partial_success = $vca_asm_registrations->revoke_registration( intval( $activity['id'] ), intval( $supporter ) );
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
			break;
		}

		$_GET['tab'] = $active_tab;

		$admin_region = get_user_meta( $current_user->ID, 'region', true );
		$post_region = get_post_meta( $activity['id'], 'region', true );
		$delegation = get_post_meta( $activity['id'], 'delegate', true );
		if( $current_user->has_cap( 'vca_asm_edit_others_activities' ) ||
		   ( $current_user->has_cap( 'vca_asm_edit_activities' ) &&
				( $post_region == $admin_region ||
					$delegation == 'delegate'
		) ) ) {
			$slots_tab = '<a href="?page=vca-asm-slot-allocation' . $url_qs . '&tab=slots" class="nav-tab ' . ( $active_tab == 'slots' ? 'nav-tab-active' : '' ) . '">' .
					'<div class="nav-tab-icon nt-icon-slots"></div>' .
					_x( 'Slots', 'Slot Allocation Admin Menu', 'vca-asm' ) .
				'</a>';
		} elseif( $active_tab === 'slots' ) {
			$active_tab = 'apps';
			$slots_tab = '';
		}

		$output .= '<form action="" method="get">' .
				'<input type="hidden" name="page" value="vca-asm-slot-allocation" />' .
				'<div class="tablenav main-select">' .
					'<div class="alignleft actions">' .
						'<select name="activity" id="activity-selector">';

		foreach( $activities as $single_activity ) {
			$output .= '<option value="' . $single_activity['id'] . '"';
			if( $activity['id'] == $single_activity['id'] ) {
				$output .= ' selected="selected"';
			}
			$output .= '>' . $single_activity['title'] . ' (' . $single_activity['applications'] . ')&nbsp;</option>';
		}

		$output .= '</select>' .
			'<input type="hidden" name="tab" value="' . $active_tab . '" />' .
			'<input type="submit" name="" id="change-activity-submit" class="button-secondary" value="' .
				__( 'Change Activity', 'vca-asm' ) .
				'" style="margin-left:6px">' .
			'</div></div></form>' .
			'<h2 class="nav-tab-wrapper">' .
				'<a href="?page=vca-asm-slot-allocation' . $url_qs . '&tab=apps" class="nav-tab ' . ( $active_tab == 'apps' ? 'nav-tab-active' : '' ) . '">' .
					'<div class="nav-tab-icon nt-icon-applications"></div>' .
					_x( 'Applications', 'Slot Allocation Admin Menu', 'vca-asm' ) .
				'</a>' .
				'<a href="?page=vca-asm-slot-allocation' . $url_qs . '&tab=waiting" class="nav-tab ' . ( $active_tab == 'waiting' ? 'nav-tab-active' : '' ) . '">' .
					'<div class="nav-tab-icon nt-icon-waiting"></div>' .
					_x( 'Waiting List', 'Slot Allocation Admin Menu', 'vca-asm' ) .
				'</a>' .
				'<a href="?page=vca-asm-slot-allocation' . $url_qs . '&tab=accepted" class="nav-tab ' . ( $active_tab == 'accepted' ? 'nav-tab-active' : '' ) . '">' .
					'<div class="nav-tab-icon nt-icon-accepted-applications"></div>' .
					_x( 'Participants', 'Slot Allocation Admin Menu', 'vca-asm' ) .
				'</a>' .
				$slots_tab . '</h2>';

		echo $output;

		if( ! empty( $messages ) ) {
			echo $vca_asm_admin->convert_messages( $messages );
		}

		switch ( $active_tab ) {

			case "slots":
				$this->reallocate_slots( $activity );
			break;

			default:
				$this->slot_allocation_list( $activity, $active_tab );
			break;
		}

		echo '</div>';
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
	private function slot_allocation_list( $activity, $list_type = 'apps' ) {
		global $current_user, $wpdb, $vca_asm_admin_supporters, $vca_asm_regions, $vca_asm_registrations, $vca_asm_utilities;
		get_currentuserinfo();
		$admin_region = get_user_meta( $current_user->ID, 'region', true );
		$status = $vca_asm_regions->get_status( $admin_region );

		$output = '';

		if( $current_user->has_cap('vca_asm_send_emails') ) {
			$mailable = true;
		} else {
			$mailable = false;
		}

		$columns = array(
			array(
				'id' => 'check',
				'check' => true,
				'name' => 'supporters'
			),
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
				'app_handling' => $list_type
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

		if( $current_user->has_cap('vca_asm_view_all_supporters') ) {
			$columns[] = array(
				'id' => 'region',
				'title' => __( 'Region', 'vca-asm' ),
				'sortable' => true,
				'legacy-mobile' => false
			);
		}

		$columns[] = array(
			'id' => 'membership',
			'title' => __( 'Membership Status', 'vca-asm' ),
			'sortable' => true,
			'conversion' => 'membership'
		);
		$columns[] = array(
			'id' => 'user_email',
			'title' => __( 'Email Address', 'vca-asm' ),
			'sortable' => true,
			'mailable' => $mailable,
			'tablet' => false
		);
		$columns[] = array(
			'id' => 'mobile',
			'title' => __( 'Mobile Phone', 'vca-asm' ),
			'sortable' => true,
			'legacy-screen' => false
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

		switch( $list_type ) {
			case "waiting":
				$supporters = $vca_asm_registrations->get_activity_waiting( $activity['id'] );
				$nottin = __( 'Currently, this waiting list is empty...', 'vca-asm' );
				$list_nicename = __( 'Waiting List', 'vca-asm' );
				$selector = '<div class="alignleft actions">' .
							'<input type="hidden" name="todo" value="accept" />' .
							__( 'Move selected supporters to participants', 'vca-asm' ) . ': ' .
							'<input type="submit" name="" id="handle-applications-submit" class="button-secondary do-bulk-action" value="' .
								__( 'Accept application(s) in retrospect', 'vca-asm' ) .
							'" onclick="if ( confirm(\'' .
								__( 'Accept all selected applications and move the selected supporters from the waiting list to participants?', 'vca-asm' ) .
								'\') ) { return true; } return false;"  style="margin-left:6px">' .
						'</div>';
			break;

			case "accepted":
				$supporters = $vca_asm_registrations->get_activity_registrations( $activity['id'] );
				$nottin = __( 'So far, no applications have been accepted yet...', 'vca-asm' );
				$list_nicename = __( 'Participants', 'vca-asm' );
				$selector = '<div class="alignleft actions">' .
							'<input type="hidden" name="todo" value="revoke" />' .
							__( 'Revoke selected accepted applications', 'vca-asm' ) . ': ' .
							'<input type="submit" name="" id="handle-accepted-submit" class="button-secondary do-bulk-action" value="' .
								__( 'Revoke!', 'vca-asm' ) .
							'" onclick="if ( confirm(\'' .
								__( 'Revoke all selected accepted applications and remove the supporters from the list of participants?', 'vca-asm' ) .
								'\\n\\n' .
								__( 'Attention: This does not move the supporters to the waiting list - it removes them from the participants entirely!', 'vca-asm' ) .
								'\') ) { return true; } return false;"  style="margin-left:6px">' .
						'</div>';
			break;

			default:
			case "apps":
				$supporters = $vca_asm_registrations->get_activity_applications( $activity['id'] );
				$nottin = __( 'No current applications...', 'vca-asm' );
				$list_nicename = __( 'Applications', 'vca-asm' );
				$selector = '<div class="alignleft actions">' .
							__( 'Handle selected applications', 'vca-asm' ) . ': ' .
							'<select name="todo" id="todo" class="bulk-action simul-select">' .
								'<option value="accept">' . __( 'Accept', 'vca-asm' ) . '&nbsp;</option>' .
								'<option value="deny">' . __( 'Deny', 'vca-asm' ) . '&nbsp;</option>' .
							'</select>' .
							'<input type="submit" name="" id="handle-applications-submit" class="button-secondary do-bulk-action" value="' .
								__( 'Execute', 'vca-asm' ) .
							'" onclick="if ( confirm(\'' .
								__( 'Manage all selected applications?', 'vca-asm' ) .
								'\') ) { return true; } return false;"  style="margin-left:6px">' .
						'</div>';
			break;
		}

		$supp_arr = array();
		$slots_arr = $activity['slots'];
		foreach( $slots_arr as $region => $slots ) {
			$supp_arr[$region] = array();
		}

		foreach( $supporters as $supporter ) {
			if( $list_type == 'accepted' ) {
				$contingent = $wpdb->get_results(
					"SELECT contingent FROM " .
					$wpdb->prefix . "vca_asm_registrations " .
					"WHERE activity = " . $activity['id'] . " AND supporter = " . $supporter .
					" LIMIT 1", ARRAY_A
				);
				$contingent = $contingent[0]['contingent'];
				$supp_arr[$contingent][] = $supporter;
			} else {
				$supp_region = intval( get_user_meta( $supporter, 'region', true ) );
				$supp_mem_status = intval( get_user_meta( $supporter, 'membership', true ) );

				if( $supp_mem_status == 2 && array_key_exists( $supp_region, $supp_arr ) ) {
					$supp_arr[$supp_region][] = $supporter;
				} else {
					$supp_arr[0][] = $supporter;
				}
			}
		}
		$regions = $vca_asm_regions->get_ids();
		$stati = $vca_asm_regions->get_stati();
		$stati_conv = $vca_asm_regions->get_stati_conv();
		$url = 'admin.php?page=vca-asm-slot-allocation&activity=' . $activity['id'] . '&tab=' . $list_type;
		$sort_url = $url;
		$profile_url = $url;
		if( isset( $_GET['orderby'] ) ) {
			$profile_url .= '&orderby=' . $_GET['orderby'];
		}
		if( isset( $_GET['order'] ) ) {
			$profile_url .= '&order=' . $_GET['order'];
		}

		foreach( $slots_arr as $region => $slots ) {
			if( ! current_user_can( 'vca_asm_manage_all_applications' ) ) {
				$admin_region = get_user_meta( $current_user->ID, 'region', true );
				$post_region = get_post_meta( $activity['id'], 'region', true );
				$delegation = get_post_meta( $activity['id'], 'delegate', true );

				if( ( $delegation != 'delegate' || $post_region != $admin_region ) && $admin_region != $region ) {
					continue;
				}
			}

			$rows = array();
			$regional_supps = $supp_arr[$region];
			$rscount = count( $regional_supps );
			for ( $i = 0; $i < $rscount; $i++ ) {
				$supp_id = intval( $regional_supps[$i] );
				$supp_region = get_user_meta( $supp_id, 'region', true );
				$supp_bday = get_user_meta( $supp_id, 'birthday', true );
				$supp_age = ! empty( $supp_bday ) ? $vca_asm_utilities->date_diff( time(), intval( $supp_bday ) ) : array( 'year' => __( 'not set', 'vca-asm' ) );
				$supp_info = get_userdata( $supp_id );
				$avatar = get_avatar( $supp_id, 32 );
				$photo_info = $vca_asm_admin_supporters->photo_info( $supp_id );
				$avatar_tooltip = '<span class="photo-tooltip" onmouseover="tooltip(' . $photo_info . ');" onmouseout="exit();">' .
					$avatar .
					'</span>';
				$note_info = $vca_asm_admin_supporters->note_info( $activity['id'], $supp_id, $list_type );
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
				$rows[$i]['mobile'] = $vca_asm_utilities->normalize_phone_number( $db_num, true );
				$raw_num = $vca_asm_utilities->normalize_phone_number( $db_num );
				$rows[$i]['mobile-order'] = empty( $raw_num ) ? '999999999999999' : substr( $raw_num . '0000000000000000000', 0, 15 );
				$rows[$i]['region'] = $regions[$supp_region];
				if( $supp_region != 0 ) {
					$rows[$i]['region'] .= ' (' . $stati_conv[$supp_region] . ')';
				}
				$rows[$i]['note'] = $note_indicator;
				$rows[$i]['membership'] = $vca_asm_admin_supporters->get_membership_status( $supp_id, $stati[$supp_region] );
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
				$rows = $this->sort_by_key( $rows, $orderby . '-order', $order );
			} else {
				$rows = $this->sort_by_key( $rows, $orderby, $order );
			}

			$region_name = $vca_asm_regions->get_name($region);
			$region_status = $vca_asm_regions->get_status($region);
			$free = $vca_asm_registrations->get_free_slots( $activity['id'], $region );
			$output .= '<h3>';
			if( $region == 0 ) {
				$output .= _x( 'General', 'female', 'vca-asm' ) . ' ' . $list_nicename;
			} else {
				$output .= $list_nicename . ', ' . $region_status . ' ' . $region_name;
			}
			$output .= '<br />' .
				sprintf( __( 'Slots: %1$s, of which %2$s are free', 'vca-asm' ), $slots, $free ) .
				'</h3><form action="" class="bulk-action-form" method="get">' .
					'<input type="hidden" name="page" value="vca-asm-slot-allocation" />' .
					'<input type="hidden" name="activity" value="' . $activity['id'] . '" />';

			if( ! empty( $rows ) ) {
				$skip_wrap = true;
				$output .= '<div class="tablenav top no-js-hide">' .
						$selector .
					'</div>';
				require( VCA_ASM_ABSPATH . '/templates/admin-table.php' );
				$output .= '<div class="tablenav bottom">' .
						$selector .
					'</div></form>';
			} else {
				$output .= '<div class="message-secondary"><p>' . $nottin . '</p></div>';
			}
		}

		echo $output;
	}

	/**
	 * Reallocate Slots
	 *
	 * @since 1.2
	 * @access private
	 */
	private function reallocate_slots( $activity ) {
		echo '<br /><p>Hier wird in Zukunft auch die Platzverteilung (sofern Rechte vorhanden) vorgenommen werden können.</p><p><dfn>Verfügbar (spätestens) ab Version 1.3</dfn></p>';
	}

	/**
	 * Sorting Methods
	 *
	 * @since 1.1
	 * @access private
	 */
	private function sort_by_key( $arr, $key, $order ) {
	    global $vca_asm_key2sort;
		$vca_asm_key2sort = $key;
		if( $order == 'DESC' ) {
			usort( $arr, array(&$this, 'sbk_cmp_desc') );
		} else {
			usort( $arr, array(&$this, 'sbk_cmp_asc') );
		}
		return ( $arr );
	}
	private function sbk_cmp_asc( $a, $b ) {
		global $vca_asm_key2sort;
		$encoding = mb_internal_encoding();
		return strcmp( mb_strtolower( $a[$vca_asm_key2sort], $encoding ), mb_strtolower( $b[$vca_asm_key2sort], $encoding ) );
	}
	private function sbk_cmp_desc( $b, $a ) {
		global $vca_asm_key2sort;
		$encoding = mb_internal_encoding();
		return strcmp( mb_strtolower( $a[$vca_asm_key2sort], $encoding ), mb_strtolower( $b[$vca_asm_key2sort], $encoding ) );
	}

} // class

endif; // class exists

?>