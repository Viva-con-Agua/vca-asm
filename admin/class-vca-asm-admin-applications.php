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
	 * @todo reload page after database manipulations
	 * 		to account for new menu values
	 * 		(or find other solution!)
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
		
		while ( $activities_obj->have_posts() ) : $activities_obj->the_post();
			
			$slots_arr = get_post_meta( get_the_ID(), 'slots', true );
			$admin_region = get_user_meta( $current_user->ID, 'region', true );
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
		
		return $activities;
	}
	
	/**
	 * Applications administration menu
	 *
	 * @since 1.1
	 * @access public
	 */
	public function applications_control() {
		global $vca_asm_registrations;
		
		switch ( $_GET['todo'] ) {
			
			case "deny":
				if( isset( $_GET['id'] ) && isset( $_GET['activity'] ) ) {
					$vca_asm_registrations->deny_application( intval( $_GET['activity'] ), intval( $_GET['id'] ) );
				} elseif( isset( $_GET['supporters'] ) && isset( $_GET['activity'] ) ) {
					foreach( $_GET['supporters'] as $supporter ) {
						$vca_asm_registrations->deny_application( intval( $_GET['activity'] ), intval( $supporter ) );
					}
				}
				unset( $_GET['todo'], $_GET['id'], $_GET['supporters'] );
				$this->applications_list();
			break;
			
			case "accept":
				if( isset( $_GET['id'] ) && isset( $_GET['activity'] ) ) {
					$region = intval( get_user_meta( intval( $_GET['id'] ), 'region', true ) );
					$free = $vca_asm_registrations->get_free_slots( $_GET['activity'], $region );
					if( $free > 0 ) {
						$vca_asm_registrations->accept_application( intval( $_GET['activity'] ), intval( $_GET['id'] ) );
					}
				} elseif( isset( $_GET['supporters'] ) && isset( $_GET['activity'] ) ) {
					foreach( $_GET['supporters'] as $supporter ) {
						$region = intval( get_user_meta( intval( $supporter ), 'region', true ) );
						$free = $vca_asm_registrations->get_free_slots( $_GET['activity'], $region );
						if( $free > 0 ) {
							$vca_asm_registrations->accept_application( intval( $_GET['activity'] ), intval( $supporter ) );
						}
					}
				}
				unset( $_GET['todo'], $_GET['id'], $_GET['supporters'] );
				$this->applications_list();
			break;		
			
			default:
				$this->applications_list();
		}
	}
	
	/**
	 * Waiting List administration menu
	 *
	 * @since 1.1
	 * @access public
	 */
	public function waiting_control() {
		global $vca_asm_registrations;
		
		switch ( $_GET['todo'] ) {
			
			case "accept":
				if( isset( $_GET['id'] ) && isset( $_GET['activity'] ) ) {
					$region = intval( get_user_meta( intval( $_GET['id'] ), 'region', true ) );
					$free = $vca_asm_registrations->get_free_slots( $_GET['activity'], $region );
					if( $free > 0 ) {
						$vca_asm_registrations->accept_application( intval( $_GET['activity'] ), intval( $_GET['id'] ) );
					}
				} elseif( isset( $_GET['supporters'] ) && isset( $_GET['activity'] ) ) {
					foreach( $_GET['supporters'] as $supporter ) {
						$region = intval( get_user_meta( intval( $supporter ), 'region', true ) );
						$free = $vca_asm_registrations->get_free_slots( $_GET['activity'], $region );
						if( $free > 0 ) {
							$vca_asm_registrations->accept_application( intval( $_GET['activity'] ), intval( $supporter ) );
						}
					}
				}
				unset( $_GET['todo'], $_GET['id'], $_GET['supporters'] );
				$this->waiting_list();
			break;		
			
			default:
				$this->waiting_list();
		}
	}
	
	/**
	 * Waiting List administration menu
	 *
	 * @since 1.1
	 * @access public
	 */
	public function registrations_control() {
		global $vca_asm_registrations;
		
		switch ( $_GET['todo'] ) {
			
			case "revoke":
				if( isset( $_GET['id'] ) && isset( $_GET['activity'] ) ) {
					$vca_asm_registrations->revoke_registration( intval( $_GET['activity'] ), intval( $_GET['id'] ) );
				} elseif( isset( $_GET['supporters'] ) && isset( $_GET['activity'] ) ) {
					foreach( $_GET['supporters'] as $supporter ) {
						$vca_asm_registrations->revoke_registration( intval( $_GET['activity'] ), intval( $supporter ) );
					}
				}
				unset( $_GET['todo'], $_GET['id'], $_GET['supporters'] );
				$this->registrations_list();
			break;		
			
			default:
				$this->registrations_list();
		}
	}
	
	/**
	 * List all supporters currently applying to the currently selected activity
	 *
	 * @todo compact the application, waiting and registration list methods
	 *
	 * @since 1.1
	 * @access private
	 */
	private function applications_list() {
		global $current_user, $wpdb, $vca_asm_admin_supporters, $vca_asm_regions, $vca_asm_registrations, $vca_asm_utilities;
		get_currentuserinfo();
		
		$activities = $this->get_activities_data();
			
		if( ! isset( $_GET['activity'] ) ) {
			$activity = array_slice( $activities, 0, 1 );
			$activity = $activity[0];
		} else {
			$activity = $activities[$_GET['activity']];
		}
		
		$output = '<div class="wrap"><h2>' . __( 'Applications', 'vca-asm' ) . '</h2>' .
			'<form action="" method="get">' .
			'<input type="hidden" name="page" value="vca-asm-applications" />' .
			'<div class="tablenav top">' .
				'<div class="alignleft actions">' .
					'<select name="activity" id="activity-selector">';
		
		foreach( $activities as $single_activity ) {
			$output .= '<option value="' . $single_activity['id'] . '"';
			if( isset( $_GET['activity'] ) && $_GET['activity'] == $single_activity['id'] ) {
				$output .= ' selected="selected"';
			}
			$output .= '>' . $single_activity['title'] . ' (' . $single_activity['applications'] . ')&nbsp;</option>';
		}
		
		$output .= '</select>' .
				'</div>' .
				'<div class="alignleft actions" style="margin-left:30px">' .
					__( 'Handle selected applications', 'vca-asm' ) . ': ' .
					'<select name="todo" id="todo">' .
						'<option value="accept">' . __( 'Accept', 'vca-asm' ) . '&nbsp;</option>' .
						'<option value="deny">' . __( 'Deny', 'vca-asm' ) . '&nbsp;</option>' .
					'</select>' .
					'<input type="submit" name="" id="handle-applications-submit" class="button-secondary" value="' .
						__( 'Execute', 'vca-asm' ) .
					'" onclick="if ( confirm(\'' .
						__( 'Manage all selected applications?', 'vca-asm' ) .
						'\') ) { return true; } return false;"  style="margin-left:6px">' .
				'</div>' .
				'<br class="clear">' .
			'</div>' .
			'<h2>' . sprintf( __( 'Applications for "%s"', 'vca-asm' ), $activity['title'] ) . '</h2>';
		
		$columns = array(
			array(
				'id' => 'check',
				'check' => true,
				'name' => 'supporters'
			),
			array(
				'id' => 'avatar',
				'title' => __( 'Avatar', 'vca-asm' ),
				'sortable' => false
			),
			array(
				'id' => 'first_name',
				'title' => __( 'First Name', 'vca-asm' ),
				'sortable' => true,
				'strong' => true,
				'application_manageable' => true,
				'quickinfo' => true
			),
			array(
				'id' => 'last_name',
				'title' => __( 'Last Name', 'vca-asm' ),
				'sortable' => true,
				'strong' => true
			),
			array(
				'id' => 'region',
				'title' => __( 'Region', 'vca-asm' ),
				'sortable' => true
			),
			array(
				'id' => 'age',
				'title' => __( 'Age', 'vca-asm' ),
				'sortable' => true
			),
			array(
				'id' => 'note',
				'title' => __( 'Note', 'vca-asm' ),
				'sortable' => true
			)
		);
		
		$applications = $vca_asm_registrations->get_activity_applications( $activity['id'] );
		$supp_arr = array();
		$slots_arr = $activity['slots'];
		foreach( $slots_arr as $region => $slots ) {
			$supp_arr[$region] = array();
		}
		foreach( $applications as $supporter ) {
			$supp_region = intval( get_user_meta( $supporter, 'region', true ) );
			$supp_mem_status = intval( get_user_meta( $supporter, 'membership', true ) );
			
			if( $supp_mem_status == 2 && array_key_exists( $supp_region, $supp_arr ) ) {
				$supp_arr[$supp_region][] = $supporter;
			} else {
				$supp_arr[0][] = $supporter;
			}
		}
		$regions = $vca_asm_regions->get_ids();
		$stati_conv = $vca_asm_regions->get_stati_conv();
		$url = 'admin.php?page=vca-asm-applications&activity=' . $activity['id'];
		
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
			$regional_applications = $supp_arr[$region];
			$racount = count( $regional_applications );
			for ( $i = 0; $i < $racount; $i++ ) {
				$supp_id = intval( $regional_applications[$i] );
				$supp_region = get_user_meta( $supp_id, 'region', true );
				$supp_age = $vca_asm_utilities->date_diff( time(), intval( get_user_meta( $supp_id, 'birthday', true ) ) );
				$supp_info = get_userdata( $supp_id );
				$avatar = get_avatar( $supp_id, 32 );
				$supporter_quick_info = $vca_asm_admin_supporters->quickinfo( $activity['id'], $supp_id );
				$avatar_tooltip = '<span class="supporter-tooltip" onmouseover="tooltip(' . $supporter_quick_info . ');" onmouseout="exit();">' .
					$avatar .
					'</span>';
				$notes = $wpdb->get_results(
					"SELECT notes FROM " .
					$wpdb->prefix . "vca_asm_applications " .
					"WHERE activity=" . $activity['id'] . " AND supporter=" . $supp_id . ' LIMIT 1', ARRAY_A
				);
				if( ! empty( $notes[0]['notes'] ) ) {
					$note_indicator = '<span class="supporter-tooltip" onmouseover="tooltip(' . $supporter_quick_info . ');" onmouseout="exit();">' .
							__( 'YES!', 'vca-asm' ) .
						'</span>';
				} else {
					$note_indicator = __( 'None', 'vca-asm' );
				}
				
				$rows[$i]['check'] = $supp_id;
				$rows[$i]['avatar'] = $avatar_tooltip;
				$rows[$i]['first_name'] = $supp_info->first_name;
				$rows[$i]['last_name'] = $supp_info->last_name;
				$rows[$i]['region'] = $regions[$supp_region];
				if( $supp_region != 0 ) {
					$rows[$i]['region'] .= ' (' . $stati_conv[$supp_region] . ')';
				}
				$rows[$i]['age'] = $supp_age['year'];
				$rows[$i]['note'] = $note_indicator;
				$rows[$i]['id'] = $supp_id;
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
			$rows = $this->sort_by_key( $rows, $orderby, $order );
			
			$region_name = $vca_asm_regions->get_name($region);
			$free = $vca_asm_registrations->get_free_slots( $activity['id'], $region );
			$output .= '<h3>';
			if( $region == 0 ) {
				$output .= __( 'Global Applications', 'vca-asm' );
			} else {
				$output .= sprintf( __( 'Applications for region "%s":', 'vca-asm' ), $region_name );
			}
			$output .= '<br />' .
				sprintf( __( 'Slots: %1$s, of which %2$s are free', 'vca-asm' ), $slots, $free ) .
				'</h3>';
			
			if( ! empty( $rows ) ) {
				$skip_wrap = true;
				require( VCA_ASM_ABSPATH . '/templates/admin-table.php' );
			} else {
				$output .= '<div class="message"><p><strong>' . __( 'No current applications...', 'vca-asm' ) . '</strong></p></div>';
			}
		}
		
		$output .= '</form></div><script type="text/javascript">' .
				'jQuery("#activity-selector").change(function() {' .
					'var activity = jQuery(this).val();' .
					'var url = "' . get_bloginfo('url') . '/wp-admin/admin.php?page=vca-asm-applications&activity=";' .
					'var destination = url + activity;' .
					'window.location = destination;' .
				'});' .
			'</script>';
		
		echo $output;
	}
	
	/**
	 * List all supporters currently on the waiting list for the selected activity
	 *
	 * @todo compact the application, waiting and registration list methods
	 *
	 * @since 1.1
	 * @access private
	 */
	private function waiting_list() {
		global $current_user, $wpdb, $vca_asm_admin_supporters, $vca_asm_regions, $vca_asm_registrations, $vca_asm_utilities;
		get_currentuserinfo();
		
		$activities = $this->get_activities_data();
			
		if( ! isset( $_GET['activity'] ) ) {
			$activity = array_slice( $activities, 0, 1 );
			$activity = $activity[0];
		} else {
			$activity = $activities[$_GET['activity']];
		}
		
		$output = '<div class="wrap"><h2>' . __( 'Waiting List', 'vca-asm' ) . '</h2>' .
			'<form action="" method="get">' .
			'<input type="hidden" name="page" value="vca-asm-waiting-list" />' .
			'<div class="tablenav top">' .
				'<div class="alignleft actions">' .
					'<select name="activity" id="activity-selector">';
		
		foreach( $activities as $single_activity ) {
			$output .= '<option value="' . $single_activity['id'] . '"';
			if( isset( $_GET['activity'] ) && $_GET['activity'] == $single_activity['id'] ) {
				$output .= ' selected="selected"';
			}
			$output .= '>' . $single_activity['title'] . ' (' . $single_activity['waiting'] . ')&nbsp;</option>';
		}
		
		$output .= '</select>' .
				'</div>' .
				'<div class="alignleft actions" style="margin-left:30px">' .
					'<input type="hidden" name="todo" value="accept" />' .
					__( 'Move selected supporter off the waiting list', 'vca-asm' ) . ': ' .
					'<input type="submit" name="" id="handle-applications-submit" class="button-secondary" value="' .
						__( 'Accept application(s)', 'vca-asm' ) .
					'" onclick="if ( confirm(\'' .
						__( 'Accept all selected applications?', 'vca-asm' ) .
						'\') ) { return true; } return false;"  style="margin-left:6px">' .
				'</div>' .
				'<br class="clear">' .
			'</div>' .
			'<h2>' . sprintf( __( 'Waiting List for "%s"', 'vca-asm' ), $activity['title'] ) . '</h2>';
		
		$columns = array(
			array(
				'id' => 'check',
				'check' => true,
				'name' => 'supporters'
			),
			array(
				'id' => 'avatar',
				'title' => __( 'Avatar', 'vca-asm' ),
				'sortable' => false
			),
			array(
				'id' => 'first_name',
				'title' => __( 'First Name', 'vca-asm' ),
				'sortable' => true,
				'strong' => true,
				'waiting_manageable' => true,
				'quickinfo' => true
			),
			array(
				'id' => 'last_name',
				'title' => __( 'Last Name', 'vca-asm' ),
				'sortable' => true,
				'strong' => true
			),
			array(
				'id' => 'region',
				'title' => __( 'Region', 'vca-asm' ),
				'sortable' => true
			),
			array(
				'id' => 'age',
				'title' => __( 'Age', 'vca-asm' ),
				'sortable' => true
			),
			array(
				'id' => 'note',
				'title' => __( 'Note', 'vca-asm' ),
				'sortable' => true
			)
		);
		
		$waiting = $vca_asm_registrations->get_activity_waiting( $activity['id'] );
		$supp_arr = array();
		$slots_arr = $activity['slots'];
		foreach( $slots_arr as $region => $slots ) {
			$supp_arr[$region] = array();
		}
		foreach( $waiting as $supporter ) {
			$supp_region = intval( get_user_meta( $supporter, 'region', true ) );
			$supp_mem_status = intval( get_user_meta( $supporter, 'membership', true ) );
			
			if( $supp_mem_status == 2 && array_key_exists( $supp_region, $supp_arr ) ) {
				$supp_arr[$supp_region][] = $supporter;
			} else {
				$supp_arr[0][] = $supporter;
			}
		}
		$regions = $vca_asm_regions->get_ids();
		$stati_conv = $vca_asm_regions->get_stati_conv();
		$url = 'admin.php?page=vca-asm-waiting-list&activity=' . $activity['id'];
		
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
			$regional_waiting = $supp_arr[$region];
			$rwcount = count( $regional_waiting );
			for ( $i = 0; $i < $rwcount; $i++ ) {
				$supp_id = intval( $regional_waiting[$i] );
				$supp_region = get_user_meta( $supp_id, 'region', true );
				$supp_age = $vca_asm_utilities->date_diff( time(), intval( get_user_meta( $supp_id, 'birthday', true ) ) );
				$supp_info = get_userdata( $supp_id );
				$avatar = get_avatar( $supp_id, 32 );
				$supporter_quick_info = $vca_asm_admin_supporters->quickinfo( $activity['id'], $supp_id );
				$avatar_tooltip = '<span class="supporter-tooltip" onmouseover="tooltip(' . $supporter_quick_info . ');" onmouseout="exit();">' .
					$avatar .
					'</span>';
				$notes = $wpdb->get_results(
					"SELECT notes FROM " .
					$wpdb->prefix . "vca_asm_applications " .
					"WHERE activity=" . $activity['id'] . " AND supporter=" . $supp_id . ' LIMIT 1', ARRAY_A
				);
				if( ! empty( $notes[0]['notes'] ) ) {
					$note_indicator = '<span class="supporter-tooltip" onmouseover="tooltip(' . $supporter_quick_info . ');" onmouseout="exit();">' .
							__( 'YES!', 'vca-asm' ) .
						'</span>';
				} else {
					$note_indicator = __( 'None', 'vca-asm' );
				}
				
				$rows[$i]['check'] = $supp_id;
				$rows[$i]['avatar'] = $avatar_tooltip;
				$rows[$i]['first_name'] = $supp_info->first_name;
				$rows[$i]['last_name'] = $supp_info->last_name;
				$rows[$i]['region'] = $regions[$supp_region];
				if( $supp_region != 0 ) {
					$rows[$i]['region'] .= ' (' . $stati_conv[$supp_region] . ')';
				}
				$rows[$i]['age'] = $supp_age['year'];
				$rows[$i]['note'] = $note_indicator;
				$rows[$i]['id'] = $supp_id;
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
			$rows = $this->sort_by_key( $rows, $orderby, $order );
			
			$region_name = $vca_asm_regions->get_name($region);
			$free = $vca_asm_registrations->get_free_slots( $activity['id'], $region );
			$output .= '<h3>';
			if( $region == 0 ) {
				$output .= __( 'Global Waiting List', 'vca-asm' );
			} else {
				$output .= sprintf( __( 'Waiting List for region "%s":', 'vca-asm' ), $region_name );
			}
			$output .= '<br />' .
				sprintf( __( 'Slots: %1$s, of which %2$s are free', 'vca-asm' ), $slots, $free ) .
				'</h3>';
			
			if( ! empty( $rows ) ) {
				$skip_wrap = true;
				require( VCA_ASM_ABSPATH . '/templates/admin-table.php' );
			} else {
				$output .= '<div class="message"><p><strong>' . __( 'No supporters currently on the Waiting List...', 'vca-asm' ) . '</strong></p></div>';
			}
		}
		
		$output .= '</form></div><script type="text/javascript">' .
				'jQuery("#activity-selector").change(function() {' .
					'var activity = jQuery(this).val();' .
					'var url = "' . get_bloginfo('url') . '/wp-admin/admin.php?page=vca-asm-waiting-list&activity=";' .
					'var destination = url + activity;' .
					'window.location = destination;' .
				'});' .
			'</script>';
		
		echo $output;
	}
	
	/**
	 * List all supporters registered to the currently selected activity
	 *
	 * @todo compact the application, waiting and registration list methods
	 *
	 * @since 1.1
	 * @access private
	 */
	private function registrations_list() {
		global $current_user, $wpdb, $vca_asm_admin_supporters, $vca_asm_regions, $vca_asm_registrations, $vca_asm_utilities;
		get_currentuserinfo();
		
		$activities = $this->get_activities_data();
			
		if( ! isset( $_GET['activity'] ) ) {
			$activity = array_slice( $activities, 0, 1 );
			$activity = $activity[0];
		} else {
			$activity = $activities[$_GET['activity']];
		}
		
		$output = '<div class="wrap"><h2>' . __( 'Accepted Applications', 'vca-asm' ) . '</h2>' .
			'<form action="" method="get">' .
			'<input type="hidden" name="page" value="vca-asm-registrations" />' .
			'<div class="tablenav top">' .
				'<div class="alignleft actions">' .
					'<select name="activity" id="activity-selector">';
		
		foreach( $activities as $single_activity ) {
			$output .= '<option value="' . $single_activity['id'] . '"';
			if( isset( $_GET['activity'] ) && $_GET['activity'] == $single_activity['id'] ) {
				$output .= ' selected="selected"';
			}
			$output .= '>' . $single_activity['title'] . ' (' . $single_activity['registrations'] . ')&nbsp;</option>';
		}
		
		$output .= '</select>' .
				'</div>' .
				'<div class="alignleft actions" style="margin-left:30px">' .
					'<input type="hidden" name="todo" value="revoke" />' .
					__( 'Revoke selected accepted applications', 'vca-asm' ) . ': ' .
					'<input type="submit" name="" id="handle-applications-submit" class="button-secondary" value="' .
						__( 'Revoke!', 'vca-asm' ) .
					'" onclick="if ( confirm(\'' .
						__( 'Revoke all selected accepted applications?', 'vca-asm' ) .
						'\') ) { return true; } return false;"  style="margin-left:6px">' .
				'</div>' .
				'<br class="clear">' .
			'</div>' .
			'<h2>' . sprintf( __( 'Accepted Applications for "%s"', 'vca-asm' ), $activity['title'] ) . '</h2>';
		
		$columns = array(
			array(
				'id' => 'check',
				'check' => true,
				'name' => 'supporters'
			),
			array(
				'id' => 'avatar',
				'title' => __( 'Avatar', 'vca-asm' ),
				'sortable' => false
			),
			array(
				'id' => 'first_name',
				'title' => __( 'First Name', 'vca-asm' ),
				'sortable' => true,
				'strong' => true,
				'registration_manageable' => true,
				'quickinfo' => true
			),
			array(
				'id' => 'last_name',
				'title' => __( 'Last Name', 'vca-asm' ),
				'sortable' => true,
				'strong' => true
			),
			array(
				'id' => 'region',
				'title' => __( 'Region', 'vca-asm' ),
				'sortable' => true
			),
			array(
				'id' => 'age',
				'title' => __( 'Age', 'vca-asm' ),
				'sortable' => true
			),
			array(
				'id' => 'note',
				'title' => __( 'Note', 'vca-asm' ),
				'sortable' => true
			)
		);
		
		$registrations = $vca_asm_registrations->get_activity_registrations( $activity['id'] );
		$supp_arr = array();
		$slots_arr = $activity['slots'];
		foreach( $slots_arr as $region => $slots ) {
			$supp_arr[$region] = array();
		}
		foreach( $registrations as $supporter ) {
			$contingent = $wpdb->get_results(
				"SELECT contingent FROM " .
				$wpdb->prefix . "vca_asm_registrations " .
				"WHERE activity = " . $activity['id'] . " AND supporter = " . $supporter .
				" LIMIT 1", ARRAY_A
			);
			$contingent = $contingent[0]['contingent'];
			$supp_arr[$contingent][] = $supporter;
		}
		$regions = $vca_asm_regions->get_ids();
		$stati_conv = $vca_asm_regions->get_stati_conv();
		$url = 'admin.php?page=vca-asm-registrations&activity=' . $activity['id'];
		
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
			$regional_registrations = $supp_arr[$region];
			$rrcount = count( $regional_registrations );
			for ( $i = 0; $i < $rrcount; $i++ ) {
				$supp_id = intval( $regional_registrations[$i] );
				$supp_region = get_user_meta( $supp_id, 'region', true );
				$supp_age = $vca_asm_utilities->date_diff( time(), intval( get_user_meta( $supp_id, 'birthday', true ) ) );
				$supp_info = get_userdata( $supp_id );
				$avatar = get_avatar( $supp_id, 32 );
				$supporter_quick_info = $vca_asm_admin_supporters->quickinfo( $activity['id'], $supp_id, 'registered' );
				$avatar_tooltip = '<span class="supporter-tooltip" onmouseover="tooltip(' . $supporter_quick_info . ');" onmouseout="exit();">' .
					$avatar .
					'</span>';
				$notes = $wpdb->get_results(
					"SELECT notes FROM " .
					$wpdb->prefix . "vca_asm_registrations " .
					"WHERE activity=" . $activity['id'] . " AND supporter=" . $supp_id . ' LIMIT 1', ARRAY_A
				);
				if( ! empty( $notes[0]['notes'] ) ) {
					$note_indicator = '<span class="supporter-tooltip" onmouseover="tooltip(' . $supporter_quick_info . ');" onmouseout="exit();">' .
							__( 'YES!', 'vca-asm' ) .
						'</span>';
				} else {
					$note_indicator = __( 'None', 'vca-asm' );
				}
				
				$rows[$i]['check'] = $supp_id;
				$rows[$i]['avatar'] = $avatar_tooltip;
				$rows[$i]['first_name'] = $supp_info->first_name;
				$rows[$i]['last_name'] = $supp_info->last_name;
				$rows[$i]['region'] = $regions[$supp_region];
				if( $supp_region != 0 ) {
					$rows[$i]['region'] .= ' (' . $stati_conv[$supp_region] . ')';
				}
				$rows[$i]['age'] = $supp_age['year'];
				$rows[$i]['note'] = $note_indicator;
				$rows[$i]['id'] = $supp_id;
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
			$rows = $this->sort_by_key( $rows, $orderby, $order );
			
			$region_name = $vca_asm_regions->get_name($region);
			$free = $vca_asm_registrations->get_free_slots( $activity['id'], $region );
			$output .= '<h3>';
			if( $region == 0 ) {
				$output .= __( 'Global Accepted Applications', 'vca-asm' );
			} else {
				$output .= sprintf( __( 'Accepted Applications for region "%s":', 'vca-asm' ), $region_name );
			}
			$output .= '<br />' .
				sprintf( __( 'Slots: %1$s, of which %2$s are free', 'vca-asm' ), $slots, $free ) .
				'</h3>';
			
			if( ! empty( $rows ) ) {
				$skip_wrap = true;
				require( VCA_ASM_ABSPATH . '/templates/admin-table.php' );
			} else {
				$output .= '<div class="message"><p><strong>' . __( 'No accepted applications yet...', 'vca-asm' ) . '</strong></p></div>';
			}
		}
		
		$output .= '</form></div><script type="text/javascript">' .
				'jQuery("#activity-selector").change(function() {' .
					'var activity = jQuery(this).val();' .
					'var url = "' . get_bloginfo('url') . '/wp-admin/admin.php?page=vca-asm-registrations&activity=";' .
					'var destination = url + activity;' .
					'window.location = destination;' .
				'});' .
			'</script>';
		
		echo $output;
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
			uasort( $arr, array(&$this, 'sbk_cmp_desc') );
		} else {
			uasort( $arr, array(&$this, 'sbk_cmp_asc') );
		}
		return ( $arr ); 
	}
	private function sbk_cmp_asc( $a, $b ) {
		global $vca_asm_key2sort;
		return( strcasecmp( $a[$vca_asm_key2sort], $b[$vca_asm_key2sort] ) );
	}
	private function sbk_cmp_desc( $b, $a ) {
		global $vca_asm_key2sort;
		return( strcasecmp( $a[$vca_asm_key2sort], $b[$vca_asm_key2sort] ) );
	}
	
} // class

endif; // class exists

?>