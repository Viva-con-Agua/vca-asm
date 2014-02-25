<?php

/**
 * VCA_ASM_Admin_Supporters class.
 *
 * This class contains properties and methods for
 * the supporter management.
 * Only admin users have access to the global
 * wordpress user management section.
 * Via the admin menu entry created by this class,
 * Head-Ofs and Department Managers can manage all users
 * that are in the "supporter" user group
 * (or role, as in correct wp lingo).
 *
 * @package VcA Activity & Supporter Management
 * @since 1.0
 */

if ( ! class_exists( 'VCA_ASM_Admin_Supporters' ) ) :

class VCA_ASM_Admin_Supporters {

	/**
	 * Sorting Methods
	 *
	 * @since 1.0
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

	/**
	 * Fetches membership status from databse and converts to human readable form
	 *
	 * @since 1.0
	 * @access public
	 */
	public function get_membership_status( $id, $region_status ) {
		$status = get_user_meta( $id, 'membership', true );
		if( $region_status != 'region' ) {
			switch( $status ) {
				case '1':
					return __( 'has applied...', 'vca-asm' );
				break;
				case '2':
					return __( 'Yes', 'vca-asm' );
				break;
				case '0':
				default:
					return __( 'No', 'vca-asm' );
				break;
			}
		} else {
			return '---';
		}
	}

	/**
	 * Builds the HTML for the avatar pop-up on mouseover for the backend
	 *
	 * @since 1.0
	 * @access public
	 */
	public function photo_info( $supporter ) {

		$avatar = preg_replace( '/"/', '&quot;', get_avatar( $supporter ) );
		$avatar = preg_replace( '/\'/', '&quot;', $avatar );

		$photo_info = '\'' . $avatar . '\'';

		return $photo_info;
	}

	/**
	 * Builds the HTML for the note-info pop-up on mouseover for the backend
	 *
	 * @since 1.0
	 * @access public
	 */
	public function note_info( $activity, $supporter, $type ) {
		global $wpdb;

		if( 'accepted' == $type ) {
			$notes = $wpdb->get_results(
				"SELECT notes FROM " .
				$wpdb->prefix . "vca_asm_registrations " .
				"WHERE activity=" . $activity . " AND supporter=" . $supporter . ' LIMIT 1', ARRAY_A
			);
		} else {
			$notes = $wpdb->get_results(
				"SELECT notes FROM " .
				$wpdb->prefix . "vca_asm_applications " .
				"WHERE activity=" . $activity . " AND supporter=" . $supporter . ' LIMIT 1', ARRAY_A
			);
		}
		$note = $notes[0]['notes'];

		if( empty( $note ) ) {
			return false;
		}

		$note = preg_replace( "/&apos;|'|\r|\n/", "", str_replace( '"', '&quot;', nl2br( trim( $note ) ) ) );

		$note_info = '\'' .
			'<p>' . $note . '</p>' . '\'';

		return $note_info;
	}

	/**
	 * Supporters Admin Menu Controller
	 *
	 * @since 1.0
	 * @access public
	 */
	public function supporters_control() {
		global $current_user, $vca_asm_mailer, $vca_asm_regions;
		get_currentuserinfo();
		$admin_region = get_user_meta( $current_user->ID, 'region', true );
		$messages = array();

		if( isset( $_GET['profile'] ) ) {
			$profile_url = 'admin.php?page=vca-asm-supporters';
			if( isset( $_GET['orderby'] ) ) {
				$profile_url .= '&orderby=' . $_GET['orderby'];
			}
			if( isset( $_GET['order'] ) ) {
				$profile_url .= '&order=' . $_GET['order'];
			}
			if( isset( $_GET['todo'] ) ) {
			   if( 'search' ===  $_GET['todo'] &&
					( isset( $_POST['term'] ) || isset( $_GET['term'] ) )
				) {
					if( isset( $_POST['term'] ) ) {
						$term = $_POST['term'];
					} else {
						$term = $_GET['term'];
					}
					$profile_url .= '&amp;todo=search&amp;term=' . $term;
			   } elseif( 'filter' ===  $_GET['todo'] ) {
					$profile_url .= '&amp;todo=filter';
					if( isset( $_POST['dead-filter'] ) ) {
						$profile_url .= '&amp;df=1';
					} elseif( isset( $_GET['df'] ) && $_GET['df'] == 1 ) {
						$profile_url .= '&amp;df=1';
					}
					if( isset( $_POST['membership-filter'] ) ) {
						$profile_url .= '&amp;mf=' . htmlspecialchars( serialize( $_POST['membership-filter'] ) );
					} elseif( isset( $_GET['mf'] ) ) {
						$profile_url .= '&amp;mf=' . htmlspecialchars( $_GET['mf'] );
					}
					if( isset( $_POST['region-filter'] ) ) {
						$profile_url .= '&amp;rf=' . htmlspecialchars( serialize( $_POST['region-filter'] ) );
					} elseif( isset( $_GET['rf'] ) ) {
						$profile_url .= '&amp;rf=' . htmlspecialchars( $_GET['rf'] );
					}
			   }
			}
			$supporter = new VCA_ASM_Supporter( intval( $_GET['profile'] ) );
			if( $supporter->exists ) {
				$this->supporter_profile( $supporter, $profile_url );
				return true;
			}
		}

		if( isset( $_GET['id'] ) ) {
			$name = get_user_meta( $_GET['id'], 'first_name', true );
		} else {
			$multiple_names = '';
			$name_arr = array();
		}

		$success = 0;

		switch ( $_GET['todo'] ) {

			case "remove":
			case "deny":
				if( isset( $_GET['id'] ) ) {
					if( $current_user->has_cap('vca_asm_promote_all_supporters') ||
					   ( $current_user->has_cap('vca_asm_promote_supporters') && $admin_region == get_user_meta( $_GET['id'], 'region', true ) )
					) {
						update_user_meta( $_GET['id'], 'membership', '0' );
						$region_name = $regions[ get_user_meta( $_GET['id'], 'region', true ) ];
						$vca_asm_mailer->auto_response( $_GET['id'], 'mem_denied', $region_name );
						$success++;
					}
				} elseif( isset( $_GET['supporters'] ) && is_array( $_GET['supporters'] ) ) {
					foreach( $_GET['supporters'] as $supporter ) {
						if( $current_user->has_cap('vca_asm_promote_all_supporters') ||
						   ( $current_user->has_cap('vca_asm_promote_supporters') && $admin_region == get_user_meta( intval( $supporter ), 'region', true ) )
						) {
							if( '0' != get_user_meta( intval( $supporter ), 'membership', true ) ) {
								$success++;
								update_user_meta( intval( $supporter ), 'membership', '0' );
								$region_name = $regions[ get_user_meta( intval( $supporter ), 'region', true ) ];
								$vca_asm_mailer->auto_response( intval( $supporter ), 'mem_denied', $region_name );
								$tmp_name = get_user_meta( intval( $supporter ), 'first_name', true );
								$name_arr[] = ! empty( $tmp_name ) ? $tmp_name : __( 'unknown Supporter', 'vca-asm' );
							}
						}
					}
					$last_name = array_shift( $name_arr );
					$multiple_names = implode( ', ', $name_arr ) . ' &amp; ' . $last_name;
				}
				if( $success > 1 ) {
					$messages[] = array(
						'type' => 'message-pa',
						'message' => sprintf( _x( 'Denied membership or revoked it, respectively, to %1$s (%2$d).', 'Admin Supporters', 'vca-asm' ), $multiple_names, $success )
					);
				} elseif( $success === 1 ) {
					if( ! empty( $name ) ) {
						$messages[] = array(
							'type' => 'message-pa',
							'message' => sprintf( _x( 'Denied membership to %s, or revoked it, respectively.', 'Admin Supporters', 'vca-asm' ), $name )
						);
					} elseif( ! empty( $name_arr[0] ) ) {
						$messages[] = array(
							'type' => 'message-pa',
							'message' => sprintf( _x( 'Denied membership to %s, or revoked it, respectively.', 'Admin Supporters', 'vca-asm' ), $name_arr[0] )
						);
					} else {
						$messages[] = array(
							'type' => 'message',
							'message' => _x( 'Denied membership to one supporter.', 'Message', 'vca-asm' )
						);
					}
				} else {
					$messages[] = array(
						'type' => 'error-pa',
						'message' => _x( 'No memberships denied / revoked...', 'Admin Supporters', 'vca-asm' )
					);
				}
				unset( $_GET['todo'], $_GET['id'], $_GET['supporters'] );
				if( $success === 0 ) {
				} elseif( is_numeric( $success ) ) {
				} else {
				}
				$this->list_supporters( $messages );
			break;

			case "accept":
			case "promote":
				if( isset( $_GET['id'] ) ) {
					if( $current_user->has_cap('vca_asm_promote_all_supporters') ||
					   ( $current_user->has_cap('vca_asm_promote_supporters') && $admin_region == get_user_meta( $_GET['id'], 'region', true ) )
					) {
						update_user_meta( $_GET['id'], 'membership', '2' );
						$region_name = $regions[ get_user_meta( $_GET['id'], 'region', true ) ];
						$vca_asm_mailer->auto_response( $_GET['id'], 'mem_accepted', $region_name );
						$name = get_user_meta( intval( $_GET['id'] ), 'first_name', true );
						$success = empty( $name ) ? 1 : $name;
					}
				} elseif( isset( $_GET['supporters'] ) && is_array( $_GET['supporters'] ) ) {
					foreach( $_GET['supporters'] as $supporter ) {
						if( $current_user->has_cap('vca_asm_promote_all_supporters') ||
						   ( $current_user->has_cap('vca_asm_promote_supporters') && $admin_region == get_user_meta( intval( $supporter ), 'region', true ) )
						) {
							if( '2' != get_user_meta( intval( $supporter ), 'membership', true ) ) {
								$success++;
								update_user_meta( intval( $supporter ), 'membership', '2' );
								$region_name = $regions[ get_user_meta( intval( $supporter ), 'region', true ) ];
								$vca_asm_mailer->auto_response( intval( $supporter ), 'mem_accepted', $region_name );
								$tmp_name = get_user_meta( intval( $supporter ), 'first_name', true );
								$name_arr[] = ! empty( $tmp_name ) ? $tmp_name : __( 'unknown Supporter', 'vca-asm' );
							}
						}
					}
					$last_name = array_shift( $name_arr );
					$multiple_names = implode( ', ', $name_arr ) . ' &amp; ' . $last_name;
				}
				if( $success > 1 ) {
					$messages[] = array(
						'type' => 'message',
						'message' => sprintf( _x( 'Successfully promoted %1$s (%2$d)!', 'Message', 'vca-asm' ), $multiple_names, $success )
					);
				} elseif( $success === 1 ) {
					if( ! empty( $name ) ) {
						$messages[] = array(
							'type' => 'message-pa',
							'message' => sprintf( _x( 'Successfully promoted %s!', 'Admin Supporters', 'vca-asm' ), $name )
						);
					} elseif( ! empty( $name_arr[0] ) ) {
						$messages[] = array(
							'type' => 'message',
							'message' => sprintf( _x( 'Successfully promoted %1$s (%2$d)!', 'Message', 'vca-asm' ), $name_arr[0], $success )
						);
					} else {
						$messages[] = array(
							'type' => 'message',
							'message' => _x( 'Successfully promoted one supporter!', 'Message', 'vca-asm' )
						);
					}
				} else {
					$messages[] = array(
						'type' => 'error-pa',
						'message' => _x( 'No supporters promoted...', 'Admin Supporters', 'vca-asm' )
					);
				}
				unset( $_GET['todo'], $_GET['id'], $_GET['supporters'] );
				$this->list_supporters( $messages );
			break;

			case "delete":
				if( isset( $_GET['id'] ) ) {
					if( $current_user->has_cap('vca_asm_delete_all_supporters') ||
					   ( $current_user->has_cap('vca_asm_delete_supporters') && $admin_region == get_user_meta( $_GET['id'], 'region', true ) )
					) {
						$first_name = get_user_meta( $_GET['id'], 'first_name', true );
						$deleted = wp_delete_user( intval( $_GET['id'] ) );
						if( $deleted === true ) {
							if( ! empty( $first_name ) ) {
								$message = sprintf( _x( 'Successfully deleted %s', 'Admin Supporters', 'vca-asm' ), $first_name );
							} else {
								$message = _x( 'Successfully deleted the selected supporter', 'Admin Supporters', 'vca-asm' );
							}
							$messages[] = array(
								'type' => 'message-pa',
								'message' => $message
							);
						} else {
							$messages[] = array(
								'type' => 'error-pa',
								'message' => _x( 'Could not delete the selected supporter...', 'Admin Supporters', 'vca-asm' )
							);
						}
					} else {
						$messages[] = array(
							'type' => 'error-pa',
							'message' => _x( 'You do not have the right to delete supporters!', 'Admin Supporters', 'vca-asm' )
						);
					}
				}
				unset( $_GET['todo'], $_GET['id'] );
				$this->list_supporters( $messages );
			break;

			default:
				$this->list_supporters( $messages );
		}
	}

	/**
	 * Outputs a complete Supporter Profile
	 *
	 * @since 1.2
	 * @access private
	 */
	public function supporter_profile( $supporter, $back_action = 'admin.php?page=vca-asm-supporters' ) {
		global $vca_asm_utilities;

		if( ! empty( $supporter->first_name ) && ! empty( $supporter->last_name ) ) {
			$title = $supporter->first_name . ' ' . $supporter->last_name;
		} elseif( ! empty( $supporter->first_name ) ) {
			$title = $supporter->first_name;
		} elseif( ! empty( $supporter->last_name ) ) {
			$title = $supporter->last_name;
		} else {
			$title = __( 'unknown Supporter', 'vca-asm' );
		}

		$output = '<div class="wrap">' .
			'<div id="icon-supporter" class="icon32-pa"></div><h2>' . $title . '</h2><br />' .
				$supporter->avatar . '<br /><br />' .
				'<table class="profile-table">' .
				'<tr><td>' .
					_x( 'Region', 'Admin Supporters', 'vca-asm' ) .
				'</td><td>' .
					$supporter->region .
				'</td></tr>' .
				'<tr><td>' .
					_x( 'Membership', 'Admin Supporters', 'vca-asm' ) .
				'</td><td>' .
					$supporter->membership .
				'</td></tr>' .
				'<tr><td>&nbsp;</td><td></td></tr>' .
				'<tr><td>' .
					_x( 'Email Address', 'Admin Supporters', 'vca-asm' ) .
				'</td><td>' .
					$supporter->email .
				'</td></tr>' .
				'<tr><td>' .
					_x( 'Mobile Phone', 'Admin Supporters', 'vca-asm' ) .
				'</td><td>' .
					$supporter->mobile .
				'</td></tr>' .
				'<tr><td>&nbsp;</td><td></td></tr>' .
				'<tr><td>' .
					_x( 'Birthday', 'Admin Supporters', 'vca-asm' ) .
				'</td><td>' .
					$supporter->birthday_combined .
				'</td></tr>' .
				'<tr><td>' .
					_x( 'Gender', 'Admin Supporters', 'vca-asm' ) .
				'</td><td>' .
					$supporter->gender .
				'</td></tr>' .
				'<tr><td>' .
					_x( 'City', 'Admin Supporters', 'vca-asm' ) .
				'</td><td>' .
					$supporter->city .
				'</td></tr>' .
				'<tr><td>&nbsp;</td><td></td></tr>' .
				'<tr><td>' .
					_x( 'Registered since', 'Admin Supporters', 'vca-asm' ) .
				'</td><td>' .
					$supporter->registration_date .
				'</td></tr>' .
				'<tr><td>' .
					_x( 'Last Login', 'Admin Supporters', 'vca-asm' ) .
				'</td><td>' .
					$supporter->last_activity .
				'</td></tr>' .
				'</table>' .
				'<form name="vca_asm_supporter_all" method="post" action="' . $back_action . '">' .
					'<input type="hidden" name="submitted" value="y"/>' .
					'<p class="submit">' .
						'<input type="submit" name="submit" id="submit" class="button"' .
							' value="&larr; ' . _x( 'back', 'Admin Supporters', 'vca-asm' ) .
				'"></p></form></div>';

		echo $output;
	}

	/**
	 * Lists all supporters
	 *
	 * @since 1.0
	 * @access private
	 */
	private function list_supporters( $messages = array() ) {
		global $current_user, $wpdb, $vca_asm_admin, $vca_asm_regions, $vca_asm_utilities;
		get_currentuserinfo();
		$admin_region = get_user_meta( $current_user->ID, 'region', true );
		$status = $vca_asm_regions->get_status( $admin_region );

		if( $current_user->has_cap('vca_asm_promote_supporters') ) {
			$promotable = true;
		} else {
			$promotable = false;
		}
		if( $current_user->has_cap('vca_asm_delete_supporters') ) {
			$deletable = true;
		} else {
			$deletable = false;
		}
		//if( $current_user->has_cap('vca_asm_send_emails') ) {
		//	$mailable = true;
		//} else {
		//	$mailable = false;
		//}

		$url = "admin.php?page=vca-asm-supporters";
		$sort_url = $url;

		/* table order */
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

		$headline = _x( 'Supporter Overview', 'Admin Supporters', 'vca-asm' );
		$table_headline = _x( 'All Supporters', 'Admin Supporters', 'vca-asm' );
		$metaqueries = array();
		$metaqueries['relation'] = 'AND';

		if( ! $current_user->has_cap('vca_asm_view_all_supporters') ) {
			$headline = str_replace( '%region_status%', $status, _x( 'Supporters of your %region_status%', 'Admin Supporters', 'vca-asm' ) );
			$table_headline = str_replace( '%region_status%', $status, _x( 'All Supporters of your %region_status%', 'Admin Supporters', 'vca-asm' ) );
			$metaqueries[] = array(
				'key' => 'region',
				'value' => intval( $admin_region )
			);
		}

		if( isset( $_GET['todo'] ) && 'filter' ===  $_GET['todo'] ) {
			$table_headline = _x( 'Filtered Supporters', 'Admin Supporters', 'vca-asm' );
			$sort_url = $url . '&amp;todo=filter';
			if( $current_user->has_cap('vca_asm_view_all_supporters') ) {
				if( isset( $_POST['region-filter'] ) && is_array( $_POST['region-filter'] ) ) {
					$rf_serialized = htmlspecialchars( serialize( $_POST['region-filter'] ) );
					$sort_url .= '&amp;rf=' . $rf_serialized;
					$metaqueries[] = array(
						'key' => 'region',
						'value' => $_POST['region-filter'],
						'compare' => 'IN'
					);
				} elseif( isset( $_GET['rf'] ) ) {
					$rf_unserialized = unserialize( htmlspecialchars_decode( $_GET['rf'] ) );
					$sort_url .= '&amp;rf=' . htmlspecialchars( $_GET['rf'] );
					$metaqueries[] = array(
						'key' => 'region',
						'value' => $rf_unserialized,
						'compare' => 'IN'
					);
				}
			} else {
				$table_headline =
					str_replace( '%region_status%', $status,
						_x( 'Filtered Supporters of your %region_status%', 'Admin Supporters', 'vca-asm' ) );
			}
			if( isset( $_POST['membership-filter'] ) && is_array( $_POST['membership-filter'] ) ) {
				$mf_serialized = htmlspecialchars( serialize( $_POST['membership-filter'] ) );
				$sort_url .= '&amp;mf=' . $mf_serialized;
				$metaqueries[] = array(
					'key' => 'membership',
					'value' => $_POST['membership-filter'],
					'compare' => 'IN'
				);
			} elseif( isset( $_GET['mf'] ) ) {
				$sort_url .= '&amp;mf=' . htmlspecialchars( $_GET['mf'] );
				$mf_unserialized = unserialize( htmlspecialchars_decode( $_GET['mf'] ) );
				$metaqueries[] = array(
					'key' => 'membership',
					'value' => $mf_unserialized,
					'compare' => 'IN'
				);
			}
			if( isset( $_POST['dead-filter'] ) ) {
				$sort_url .= '&amp;df=1';
			} elseif( isset( $_GET['df'] ) && $_GET['df'] == 1 ) {
				$sort_url .= '&amp;df=1';
			}
		}

		$args = array(
			'role' => 'supporter',
			'meta_query' => $metaqueries
		);
		$supporters = get_users( $args );

		if( isset( $_GET['todo'] ) &&
		   'search' ===  $_GET['todo'] &&
		   ( isset( $_POST['term'] ) || isset( $_GET['term'] ) )
		) {
			if( isset( $_POST['term'] ) ) {
				$term = $_POST['term'];
			} else {
				$term = $_GET['term'];
			}
			$sort_url = $url . '&amp;todo=search&amp;term=' . $term;
			$supp_query_results = $supporters;
			$supporters = array();
			$supp_ids = array();
			foreach( $supp_query_results as $temp_supp ) {
				if( strstr( mb_strtolower( get_user_meta( $temp_supp->ID, 'first_name', true ) ), mb_strtolower( $term ) ) ||
					strstr( mb_strtolower( get_user_meta( $temp_supp->ID, 'last_name', true ) ), mb_strtolower( $term ) )
				) {
					$supporters[] = $temp_supp;
					$supp_ids[] = $temp_supp->ID;
				}
			}
			$supporters_by_mail = $wpdb->get_results(
				"SELECT ID FROM " .
				$wpdb->prefix . "users " .
				"WHERE user_email LIKE '%" . $term . "%'", ARRAY_A
			);
			foreach( $supporters_by_mail as $temp_supp ) {
				$temp_sobj = new WP_User( $temp_supp['ID'] );
				if( ( $current_user->has_cap('vca_asm_view_all_supporters') ||
				   $admin_region === get_user_meta( $temp_supp['ID'], 'region', true ) ) &&
				   in_array( 'supporter', $temp_sobj->roles ) &&
				   ! in_array( $temp_supp['ID'], $supp_ids ) )
				{
					$supporters[] = get_userdata( $temp_supp['ID'] );
				}
			}
			$table_headline = str_replace( '%results%', count( $supporters ), str_replace( '%term%', $term, _x( 'Showing %results% search results for &quot;%term%&quot;', 'Admin Supporters', 'vca-asm' ) ) );
		}

		$profile_url = $sort_url . '&orderby=' . $orderby . '&order=' . $order;

		$regions = $vca_asm_regions->get_ids();
		$stati = $vca_asm_regions->get_stati();
		$stati_conv = $vca_asm_regions->get_stati_conv();

		$supporters_ordered = array();
		$i = 0;
		foreach ( $supporters as $key => $supporter ) {
			$supp_fname = get_user_meta( $supporter->ID, 'first_name', true );
			$supp_lname = get_user_meta( $supporter->ID, 'last_name', true );
			if( 'search' !== $_GET['todo'] && 1 != $_GET['df'] && empty( $_POST['dead-filter'] ) && ( empty( $supp_fname ) || empty( $supp_lname ) ) ) {
				continue;
			}
			if ( $orderby === 'region' ||  $orderby === 'membership' ) {
				$supp_region = get_user_meta( $supporter->ID, 'region', true );
				$supporters_ordered[$i]['region'] = mb_substr( $regions[$supp_region], 0, 3 );
				if( $orderby === 'membership' ) {
					$supporters_ordered[$i]['membership'] = $this->get_membership_status( $supporter->ID, $stati[$supp_region] );
				}
			} elseif ( $orderby === 'user_email' ) {
				$supporters_ordered[$i]['user_email'] = $supporter->user_email;
			} elseif ( $orderby === 'age' ) {
				$supp_bday = get_user_meta( $supporter->ID, 'birthday', true );
				$supporters_ordered[$i]['age'] = empty( $supp_bday ) ? 1 : ( doubleval(555555555555) - doubleval( $supp_bday ) );
			} elseif ( $orderby === 'mobile' ) {
				$raw_num = $vca_asm_utilities->normalize_phone_number( get_user_meta( $supporter->ID, 'mobile', true ) );
				$supporters_ordered[$i]['mobile'] = empty( $raw_num ) ? '999999999999999' : substr( $raw_num . '0000000000000000000', 0, 15 );
			} elseif ( $orderby === 'gender' ) {
				$supporters_ordered[$i]['gender'] = $vca_asm_utilities->convert_strings( get_user_meta( $supporter->ID, 'gender', true ) );
			} else {
				$supporters_ordered[$i][$orderby] = get_user_meta( $supporter->ID, $orderby, true );
			}
			$supporters_ordered[$i]['key'] = $key;
			$i++;
		}
		$supporters_ordered = $this->sort_by_key( $supporters_ordered, $orderby, $order );

		$user_count = count( $supporters_ordered );
		if( $user_count > 100 ) {
			$cur_page = isset( $_GET['p'] ) ? $_GET['p'] : 1;
			$pagination_offset = 100 * ( $cur_page - 1 );
			$total_pages = ceil( $user_count / 100 );
			$cur_end = $total_pages == $cur_page ? $pagination_offset + ( $user_count % 100 ) : $pagination_offset + 100;
			$pagination_url =
				str_replace( '{', '%lcurl%',
					str_replace( '}', '%rcurl%',
						str_replace( ':', '%colon%',
							$sort_url
						)
					)
				) .
				'&orderby=' . $orderby . '&order=' . $order . '%_%';

			$pagination_html = paginate_links( array(
				'base' => $pagination_url,
				'format' => '&p=%#%#tbl',
				'prev_text' => __( '&laquo; Previous', 'vca-asm' ),
				'next_text' => __( 'Next &raquo;', 'vca-asm' ),
				'total' => $total_pages,
				'current' => $cur_page,
				'end_size' => 1,
				'mid_size' => 2,
			));
			$pagination_html = str_replace( '%colon%', ':', str_replace( '%lcurl%', '{', str_replace( '%rcurl%', '}', $pagination_html ) ) );

		} else {
			$cur_page = 1;
			$pagination_offset = 0;
			$pagination_html = '';
			$cur_end = $user_count;
		}

		$rows = array();
		for ( $i = $pagination_offset; $i < $cur_end; $i++ ) {
			$supp_obj = $supporters[$supporters_ordered[$i]['key']];
			$supp_id = $supp_obj->ID;
			$supp_fname = get_user_meta( $supp_id, 'first_name', true );
			$supp_lname = get_user_meta( $supp_id, 'last_name', true );
			$supp_region = get_user_meta( $supp_id, 'region', true );
			$supp_bday = get_user_meta( $supp_id, 'birthday', true );
			$supp_age = ! empty( $supp_bday ) ? $vca_asm_utilities->date_diff( time(), intval( $supp_bday ) ) : array( 'year' => __( 'not set', 'vca-asm' ) );
			if( empty ( $supp_region ) ) {
				$supp_region = '0';
			}
			$photo_info = $this->photo_info( $supp_id );

			$rows[$i]['check'] = $supp_id;
			$rows[$i]['avatar'] = '<span class="photo-tooltip tooltip-trigger" onmouseover="tooltip(' .
					$photo_info .
				');" onmouseout="exit();">' .
					get_avatar( $supp_id, 32 ) .
				'</span>';
			$rows[$i]['id'] = $supp_id;
			$rows[$i]['username'] = $supp_obj->user_name;
			$rows[$i]['first_name'] = empty( $supp_fname ) ? __( 'not set', 'vca-asm' ) : $supp_fname;
			$rows[$i]['last_name'] = empty( $supp_lname ) ? __( 'not set', 'vca-asm' ) : $supp_lname;
			$rows[$i]['user_email'] = $supp_obj->user_email;
			$rows[$i]['mobile'] = $vca_asm_utilities->normalize_phone_number( get_user_meta( $supp_id, 'mobile', true ), true );
			$rows[$i]['region'] = $regions[$supp_region];
			if( $supp_region != 0 ) {
				$rows[$i]['region'] .= ' (' . $stati_conv[$supp_region] . ')';
			}
			$rows[$i]['membership'] = $this->get_membership_status( $supp_id, $stati[$supp_region] );
			$rows[$i]['membership_raw'] = get_user_meta( $supp_id, 'membership', true );
			$rows[$i]['age'] = $supp_age['year'];
			$rows[$i]['gender'] = $vca_asm_utilities->convert_strings( get_user_meta( $supp_id, 'gender', true ) );
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
				'profileable' => true,
				'deletable-user' => $deletable
			),
			array(
				'id' => 'last_name',
				'title' => __( 'Last Name', 'vca-asm' ),
				'sortable' => true,
				'strong' => true
			)
		);

		if( $current_user->has_cap('vca_asm_view_all_supporters') ) {
			$columns[] = array(
				'id' => 'region',
				'title' => __( 'Region', 'vca-asm' ),
				'sortable' => true
			);
		}

		$columns[] = array(
			'id' => 'membership',
			'title' => __( 'Membership Status', 'vca-asm' ),
			'sortable' => true,
			'promotable' => $promotable,
			'conversion' => 'membership'
		);
		$columns[] = array(
			'id' => 'user_email',
			'title' => __( 'Email Address', 'vca-asm' ),
			'sortable' => true,
			'mailable' => false
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

		$search_fields = array(
			array(
				'type' => 'text',
				'label' =>  _x( 'Search Supporters', 'Admin Supporters', 'vca-asm' ),
				'id' => 'term',
				'desc' => _x( "You can search the supporters by first and last name as well as email address.", 'Admin Supporters', 'vca-asm' )
			)
		);

		$filter_fields = array();

		if( $current_user->has_cap('vca_asm_view_all_supporters') ) {
			$region_options_raw = $vca_asm_regions->select_options( _x( 'no specific region', 'Regions', 'vca-asm' ) );
			$region_options = array();
			foreach( $region_options_raw as $region_option ) {
				if( isset( $_POST['region-filter'] ) && is_array( $_POST['region-filter'] ) ) {
					if( in_array( $region_option['value'], $_POST['region-filter'] ) ) {
						$region_option['checked'] = true;
					}
				} elseif( isset( $_GET['rf'] ) ) {
					$rf_unserialized = unserialize( htmlspecialchars_decode( $_GET['rf'] ) );
					if( in_array( $region_option['value'], $rf_unserialized ) ) {
						$region_option['checked'] = true;
					}
				} else {
					$region_option['checked'] = true;
				}
				$region_options[] = $region_option;
			}
			$filter_fields[] = array(
				'type' => 'checkbox_group',
				'label' => _x( 'Region', 'Admin Supporters', 'vca-asm' ),
				'id' => 'region-filter',
				'options' => $region_options,
				'desc' => _x( "Show only supporters of certain region(s)", 'Admin Supporters', 'vca-asm' ),
				'cols' => 3,
				'extra' => 'bulk_deselect'
			);
			$membership_labels = array(
				2 => _x( 'Supporters that are &quot;active members&quot; of their region', 'Admin Supporters', 'vca-asm' ),
				1 => _x( 'Supporters that have applied for membership status', 'Admin Supporters', 'vca-asm' ),
				0 => _x( 'Supporters that simply are registered to the Pool', 'Admin Supporters', 'vca-asm' )
			);
		} else {
			$membership_labels = array(
				2 => str_replace( '%region_status%', $status, _x( 'Supporters that are &quot;active members&quot; of your %region_status%', 'Admin Supporters', 'vca-asm' ) ),
				1 => str_replace( '%region_status%', $status, _x( 'Supporters that have applied for membership to your %region_status%', 'Admin Supporters', 'vca-asm' ) ),
				0 => _x( 'Supporters that simply live in your region', 'Admin Supporters', 'vca-asm' )
			);
		}

		if( isset( $_POST['region-filter'] ) && is_array( $_POST['region-filter'] ) ) {
			$checked_mem_options = array(
				0 => ( in_array( 0, $_POST['membership-filter'] ) ? true : false ),
				1 => ( in_array( 1, $_POST['membership-filter'] ) ? true : false ),
				2 => ( in_array( 2, $_POST['membership-filter'] ) ? true : false )
			);
		} elseif( isset( $_GET['mf'] ) ) {
			$mf_unserialized = unserialize( htmlspecialchars_decode( $_GET['mf'] ) );
			$checked_mem_options = array(
				0 => ( in_array( 0, $mf_unserialized ) ? true : false ),
				1 => ( in_array( 1, $mf_unserialized ) ? true : false ),
				2 => ( in_array( 2, $mf_unserialized ) ? true : false )
			);
		} else {
			$checked_mem_options = array(
				0 => true,
				1 => true,
				2 => true
			);
		}

		$filter_fields[] = array(
			'type' => 'checkbox_group',
			'label' => _x( 'Membership', 'Admin Supporters', 'vca-asm' ),
			'id' => 'membership-filter',
			'options' => array(
				array(
					'label' => $membership_labels[2],
					'value' => 2,
					'checked' => $checked_mem_options[2]
				),
				array(
					'label' => $membership_labels[1],
					'value' => 1,
					'checked' => $checked_mem_options[1]
				),
				array(
					'label' => $membership_labels[0],
					'value' => 0,
					'checked' => $checked_mem_options[0]
				)
			),
			'desc' => _x( "Show only supporters with a certain membership status.", 'Admin Supporters', 'vca-asm' ),
			'cols' => 1
		);

		if( ! empty( $_POST['dead-filter'] ) ) {
			$value_df = 1;
		} elseif( ! empty( $_GET['df'] ) ) {
			$value_df = 1;
		} else {
			$value_df = 0;
		}
		$filter_fields[] = array(
			'type' => 'checkbox',
			'label' => _x( '&quot;Dead Profiles&quot;', 'Admin Supporters', 'vca-asm' ),
			'id' => 'dead-filter',
			'desc' => _x( "By default this list shows supporters that have at least provided their first and last names. To include those that have not, activate this Filter.", 'Admin Supporters', 'vca-asm' ),
			'value' => $value_df
		);

		/* count membership applications */
		if( $current_user->has_cap('vca_asm_promote_all_supporters') ) {
			$pending =
				$wpdb->get_var(
					$wpdb->prepare(
						"SELECT COUNT(*) FROM $wpdb->usermeta WHERE meta_key='membership' AND meta_value='1'" ) );
		} elseif( $current_user->has_cap('vca_asm_promote_supporters') ) {
			$pending_users = get_users( array(
				'role' => 'supporter',
				'meta_query' => array(
					'relation' => 'AND',
					array(
						'key' => 'region',
						'value' => $admin_region
					),
					array(
						'key' => 'membership',
						'value' => '1'
					)
				)
			));
			$pending = count( $pending_users );
		}

		$skip_wrap = true;

		$output = '<div class="wrap">' .
			'<div id="icon-supporters" class="icon32-pa"></div><h2>' . $headline . '</h2><br />';

		if( ! empty( $messages ) ) {
			$output .= $vca_asm_admin->convert_messages( $messages );
		}

		if( ! empty( $pending ) ) {
			if( $current_user->has_cap('vca_asm_promote_all_supporters') ) {
				$output .= '<h3 class="title">' . _x( 'Membership Applications', 'Admin Supporters', 'vca-asm' ) . '</h3>' .
					'<p><strong>' .
						sprintf( _x( 'In total, currently %d supporters are waiting for their applications to be answered...', 'Admin Supporters', 'vca-asm' ), $pending ) .
					'</p></strong>';
			} else {
				$output .= '<h3 class="title">' . _x( 'Membership Applications', 'Admin Supporters', 'vca-asm' ) . '</h3>' .
					'<div id="message" class="updated highlight"><p>' .
						sprintf( _x( 'You have %d pending applications waiting to be answered...', 'Admin Supporters', 'vca-asm' ), $pending ) .
					'</p></div>';
			}
			$output .= '<span class="description">' . _x( 'Supporters are categorized in two groups: Those that are active in their cells or local crews and part of the creative process revolving around Viva con Agua and those that simply take an interest in VcA and/or show up for a festival or the like once in a while. Supporters may apply for the status of $quot;active membership$quot; via their &quot;Profile &amp; Settings&quot; menu.', 'Admin Supporters', 'vca-asm' ) . '</span>';
		}

		$output .= '<h3 class="title title-top-pa">' . _x( 'Search', 'Admin Supporters', 'vca-asm' ) . '</h3>' .
			'<form name="vca_asm_supporter_search" method="post" action="'.$url .'&amp;todo=search">' .
				'<input type="hidden" name="submitted" value="y"/>';
					$fields = $search_fields;
					require( VCA_ASM_ABSPATH . '/templates/admin-form.php' );
				$output .= '<input type="submit" name="submit" id="submit" class="button-primary"' .
						' value="' . _x( 'Search', 'Admin Supporters', 'vca-asm' ) .
					'"></form>' .
			'<h3 class="title title-top-pa">' . _x( 'Filter', 'Admin Supporters', 'vca-asm' ) . '</h3>' .
			'<form name="vca_asm_supporter_filter" method="post" action="'.$url .'&amp;todo=filter">' .
				'<input type="hidden" name="submitted" value="y"/>';
					$fields = $filter_fields;
					require( VCA_ASM_ABSPATH . '/templates/admin-form.php' );
				$output .= '<input type="submit" name="submit" id="submit" class="button-primary"' .
						' value="' . _x( 'Filter', 'Admin Supporters', 'vca-asm' ) .
					'"></form>' .
			'<h3 id="tbl" class="title title-top-pa">' . $table_headline . '</h3>';

			if( empty( $rows ) ) {
				$output .= '';
			} else {
				$output .= '<form action="" class="bulk-action-form" method="get">' .
					'<input type="hidden" name="page" value="vca-asm-supporters" />' .
					'<div class="tablenav top">' .
						'<div class="alignleft actions">' .
							__( 'Change selected supporters\' Membership Status', 'vca-asm' ) . ': ' .
							'<select name="todo" id="todo" class="bulk-action simul-select">' .
								'<option value="accept">' . __( 'Accept application / Add as members', 'vca-asm' ) . '&nbsp;</option>' .
								'<option value="deny">' . __( 'Deny application / End membership', 'vca-asm' ) . '&nbsp;</option>' .
							'</select>' .
							'<input type="submit" name="" id="handle-memberships" class="button-secondary do-bulk-action" value="' .
								__( 'Execute', 'vca-asm' ) .
							'" onclick="if ( confirm(\'' .
								__( 'Manage the membership of all selected supporters?', 'vca-asm' ) .
								'\') ) { return true; } return false;"  style="margin-left:6px" />' .
						'</div>' .
						'<div class="tablenav-pages">' .
						'<span class="displaying-num">' . sprintf( __( '%d Supporters', 'vca-asm' ), $user_count ) . '</span>' .
						'<span class="pagination-links">' . $pagination_html . '</span></div>' .
					'</div>';
				require( VCA_ASM_ABSPATH . '/templates/admin-table.php' );
				$output .= '<div class="tablenav bottom">' .
						'<div class="alignleft actions no-js-hide">' .
							__( 'Change selected supporters\' Membership Status', 'vca-asm' ) . ': ' .
							'<select name="todo" id="todo" class="bulk-action simul-select">' .
								'<option value="accept">' . __( 'Accept application / Add as members', 'vca-asm' ) . '&nbsp;</option>' .
								'<option value="deny">' . __( 'Deny application / End membership', 'vca-asm' ) . '&nbsp;</option>' .
							'</select>' .
							'<input type="submit" name="" id="handle-memberships" class="button-secondary do-bulk-action" value="' .
								__( 'Execute', 'vca-asm' ) .
							'" onclick="if ( confirm(\'' .
								__( 'Manage the membership of all selected supporters?', 'vca-asm' ) .
								'\') ) { return true; } return false;"  style="margin-left:6px">' .
						'</div>' .
						'<div class="tablenav-pages">' .
						'<span class="displaying-num">' . sprintf( __( '%d Supporters', 'vca-asm' ), $user_count ) . '</span>' .
						'<span class="pagination-links">' . $pagination_html . '</span></div>' .
					'</div></form>';
			}

		if( isset( $_GET['todo'] ) ) {
			$output .= '<form name="vca_asm_supporter_all" method="post" action="admin.php?page=vca-asm-supporters">' .
					'<input type="hidden" name="submitted" value="y"/>' .
					'<p class="submit">' .
						'<input type="submit" name="submit" id="submit" class="button-primary"' .
							' value="' . _x( 'Show all supporters', 'Admin Supporters', 'vca-asm' ) .
				'"></p></form>';
		}

		$output .= '</div>';

		echo $output;
	}

} // class

endif; // class exists

?>