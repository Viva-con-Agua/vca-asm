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
	 * Class Properties
	 *
	 * @since 1.4
	 */
	private $per_page = 50;

	/**** THE ADMIN PAGE *****/

	/**
	 * Supporters Admin Menu Controller
	 *
	 * @since 1.0
	 * @access public
	 */
	public function control() {
        /** @var vca_asm_geography $vca_asm_geography */
        /** @var vca_asm_mailer $vca_asm_mailer */
		global $vca_asm_mailer, $vca_asm_geography;
		$current_user = wp_get_current_user();

		$admin_city = get_user_meta( $current_user->ID, 'city', true );
		$admin_nation = $vca_asm_geography->has_nation( $admin_city );

		$cities = $vca_asm_geography->get_names( 'city' );

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
					$profile_url .= '&todo=search&term=' . $term;
			   } elseif( 'filter' ===  $_GET['todo'] ) {
					$profile_url .= '&todo=filter';
					if( isset( $_POST['dead-filter'] ) ) {
						$profile_url .= '&df=1';
					} elseif( isset( $_GET['df'] ) && $_GET['df'] == 1 ) {
						$profile_url .= '&df=1';
					}
					if( isset( $_POST['membership-filter'] ) ) {
						$profile_url .= '&mf=' . wp_json_encode($_POST['membership-filter']);
					} elseif( isset( $_GET['mf'] ) ) {
						$profile_url .= '&mf=' . $_GET['mf'];
					}
					if( isset( $_POST['geo-filter'] ) ) {
						$profile_url .= '&gf=' . wp_json_encode( $_POST['geo-filter'] );
					} elseif( isset( $_GET['gf'] ) ) {
						$profile_url .= '&gf=' . $_GET['gf'];
					}
					if( isset( $_POST['geo-filter-by'] ) ) {
						$profile_url .= '&gfb=' . $_POST['geo-filter-by'];
					} elseif( isset( $_GET['gfb'] ) ) {
						$profile_url .= '&gfb=' . $_GET['gfb'];
					}
					if( isset( $_POST['role-filter'] ) ) {
						$profile_url .= '&rf=' . wp_json_encode( $_POST['role-filter']);
					} elseif( isset( $_GET['rf'] ) ) {
						$profile_url .= '&rf=' . $_GET['rf'];
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

		$todo = isset( $_GET['todo'] ) ? $_GET['todo'] : '';

		switch ( $todo ) {

			case "remove":
			case "deny":
				if( isset( $_GET['id'] ) ) {
					$user_city = get_user_meta( $_GET['id'], 'city', true );
					if( $current_user->has_cap('vca_asm_promote_supporters_global') ||
					   (
							$current_user->has_cap('vca_asm_promote_supporters_nation') &&
							$admin_nation &&
							$admin_nation == $vca_asm_geography->has_nation( $user_city )
					   ) ||
					   (
							$current_user->has_cap('vca_asm_promote_supporters') &&
							$admin_city == $user_city
					   )
					) {
						update_user_meta( $_GET['id'], 'membership', '0' );
						$city_name = $cities[$user_city];
						$vca_asm_mailer->auto_response(
							$_GET['id'],
							'mem_denied',
							array(
								'city' => $city_name,
								'city_id' => $user_city
							)
						);
						$success++;
					}
				} elseif( isset( $_GET['supporters'] ) && is_array( $_GET['supporters'] ) ) {
					foreach( $_GET['supporters'] as $supporter ) {
						$user_city = get_user_meta( intval( $supporter ), 'city', true );
						$membership = get_user_meta( intval( $supporter ), 'membership', true );
						if( $current_user->has_cap('vca_asm_promote_supporters_global') ||
						   (
								$current_user->has_cap('vca_asm_promote_supporters_nation') &&
								$admin_nation &&
								$admin_nation == $vca_asm_geography->has_nation( $user_city )
						   ) ||
						   (
								$current_user->has_cap('vca_asm_promote_supporters') &&
								$admin_city == $user_city
						   )
						) {
							if( 0 != $membership ) {
								$success++;
								update_user_meta( intval( $supporter ), 'membership', '0' );
								$city_name = $cities[$user_city];
								$vca_asm_mailer->auto_response(
									intval( $supporter ),
									'mem_denied',
									array(
										'city' => $city_name,
										'city_id' => $user_city
									)
								);
								$tmp_name = get_user_meta( intval( $supporter ), 'first_name', true );
								$name_arr[] = ! empty( $tmp_name ) ? $tmp_name : __( 'unknown Supporter', 'vca-asm' );
							}
						}
					}
					$last_name = array_shift( $name_arr );
					$multiple_names = implode( ', ', $name_arr ) . ' &amp; ' . $last_name;
				}
				if ( $success > 1 ) {
					$messages[] = array(
						'type' => 'message-pa',
						'message' => sprintf( _x( 'Denied membership or revoked it, respectively, to %1$s (%2$d).', 'Admin Supporters', 'vca-asm' ), $multiple_names, $success )
					);
				} elseif ( $success === 1 ) {
					if( ! empty( $name ) ) {
						$messages[] = array(
							'type' => 'message-pa',
							'message' => sprintf( _x( 'Denied membership to %s, or revoked it, respectively.', 'Admin Supporters', 'vca-asm' ), $name )
						);
					} elseif ( ! empty( $name_arr[0] ) ) {
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
				$this->list_supporters( $messages );
			break;

			case "accept":
			case "promote":
				if ( isset( $_GET['id'] ) ) {
					$user_city = get_user_meta( $_GET['id'], 'city', true );
					if ( $current_user->has_cap('vca_asm_promote_supporters_global') ||
					   (
							$current_user->has_cap('vca_asm_promote_supporters_nation') &&
							$admin_nation &&
							$admin_nation == $vca_asm_geography->has_nation( $user_city )
					   ) ||
					   (
							$current_user->has_cap('vca_asm_promote_supporters') &&
							$admin_city == $user_city
					   )
					) {
						update_user_meta( $_GET['id'], 'membership', '2' );
						$city_name = $cities[$user_city];
						$vca_asm_mailer->auto_response(
							$_GET['id'],
							'mem_accepted',
							array(
								'city' => $city_name,
								'city_id' => $user_city
							)
						);
						$name = get_user_meta( intval( $_GET['id'] ), 'first_name', true );
						$success++;
					}
				} elseif( isset( $_GET['supporters'] ) && is_array( $_GET['supporters'] ) ) {
					foreach( $_GET['supporters'] as $supporter ) {
						$user_city = get_user_meta( intval( $supporter ), 'city', true );
						$membership = get_user_meta( intval( $supporter ), 'membership', true );
						if ( $current_user->has_cap('vca_asm_promote_supporters_global') ||
						   (
								$current_user->has_cap('vca_asm_promote_supporters_nation') &&
								$admin_nation &&
								$admin_nation == $vca_asm_geography->has_nation( $user_city )
						   ) ||
						   (
								$current_user->has_cap('vca_asm_promote_supporters') &&
								$admin_city == $user_city
						   )
						) {
							if ( 2 != $membership ) {
								$success++;
								update_user_meta( intval( $supporter ), 'membership', '2' );
								$city_name = $cities[$user_city];
								$vca_asm_mailer->auto_response(
									intval( $supporter ),
									'mem_accepted',
									array(
										'city' => $city_name,
										'city_id' => $user_city
									)
								);
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
							'message' => sprintf( _x( 'Successfully promoted %s!', 'Message', 'vca-asm' ), $name_arr[0] )
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
					$user_city = get_user_meta( $_GET['id'], 'city', true );
					if( $current_user->has_cap('vca_asm_promote_supporters_global') ||
					   (
							$current_user->has_cap('vca_asm_promote_supporters_nation') &&
							$admin_nation &&
							$admin_nation == $vca_asm_geography->has_nation( $user_city )
					   ) ||
					   (
							$current_user->has_cap('vca_asm_promote_supporters') &&
							$admin_city == $user_city
					   )
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

	/**** VIEWS *****/

    /**
     * Outputs a complete Supporter Profile
     *
     * @since 1.2
     * @access private
     * @param $supporter
     * @param string $back_action
     */
	public function supporter_profile( $supporter, $back_action = 'admin.php?page=vca-asm-supporters' ) {
        /** @var vca_asm_geography $vca_asm_geography */
        /** @var vca_asm_roles $vca_asm_roles */
        /** @var vca_asm_admin $vca_asm_admin */
		global $wp_roles, $vca_asm_geography, $vca_asm_roles, $vca_asm_admin;
		$current_user = wp_get_current_user();

		$messages = array();

		$admin_city = get_user_meta( $current_user->ID, 'city', true );
		$admin_nation = $vca_asm_geography->has_nation( $admin_city );

		if ( isset( $_GET['profile_todo'] ) && 'update_role' === $_GET['profile_todo'] ) {
			$current_roles = $current_user->roles;
			$current_role = array_shift( $current_roles );
			if ( in_array( $current_role, $vca_asm_roles->admin_roles ) &&
				in_array( $supporter->role_slug, $vca_asm_roles->user_sub_roles() ) &&
				(
					in_array( $current_role, $vca_asm_roles->global_admin_roles ) ||
					(
						$admin_nation &&
						$vca_asm_geography->has_nation( $supporter->city_id ) &&
						$admin_nation == $vca_asm_geography->has_nation( $supporter->city_id )
					)
				)
			) {
				if (
					isset( $_GET['role'] ) &&
					in_array( $_GET['role'], $vca_asm_roles->user_sub_roles() ) &&
					array_key_exists( $_GET['role'], $wp_roles->roles )
				) {
					$user_obj = new WP_User( $supporter->ID );
					$user_obj->set_role( $_GET['role'] );

					$messages[] = array(
						'type' => 'message-pa',
						'message' => sprintf(
							__( 'Successfully updated %1$s from &quot;%2$s&quot; to &quot;%3$s&quot;.', 'vca-asm' ),
							$supporter->nice_name,
							$supporter->role,
							$vca_asm_roles->translated_roles[$_GET['role']]
						)
					);
					$update_role_success = true;
				} else {
					$messages[] = array(
						'type' => 'error-pa',
						'message' => __( 'Could not change role...', 'vca-asm' )
					);
				}
			} else {
				$messages[] = array(
					'type' => 'error-pa',
					'message' => __( 'You do not have the rights required to change this user&apos;s role. Sorry.', 'vca-asm' )
				);
			}
		}

		$page = new VCA_ASM_Admin_Page( array(
			'echo' => true,
			'icon' => 'icon-supporter',
			'title' => $supporter->nice_name,
			'url' => 'admin.php?page=vca-asm-supporters',
			'messages' => $messages
		));

		$page->top();

		if (
			$current_user->has_cap( 'vca_asm_view_supporters_global' ) ||
			(
				$current_user->has_cap( 'vca_asm_view_supporters_nation' ) &&
				$admin_nation &&
				$vca_asm_geography->has_nation( $supporter->city_id ) &&
				$admin_nation == $vca_asm_geography->has_nation( $supporter->city_id )
			) ||
			(
				$current_user->has_cap( 'vca_asm_view_supporters' ) &&
				$admin_city &&
				$admin_city == $supporter->city_id
			)
		) {
			$mbs = new VCA_ASM_Admin_Metaboxes( array(
				'echo' => true,
				'title' => __( 'Profile', 'vca-asm' ) . ': ' . $supporter->nice_name,
				'id' => 'profile'
			));
			$mbs->top();
			$mbs->mb_top();

			$table = '<table class="profile-table">' .
					'<tr><td>' .
						$supporter->avatar .
					'</td><td>'.
						'<table>' .
							'<tr><td>' .
								__( 'Country', 'vca-asm' ) .
							'</td><td>' .
								$supporter->nation .
							'</td></tr>' .
							'<tr><td>' .
								__( 'City', 'vca-asm' ) .
							'</td><td>' .
								$supporter->city .
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
								__( 'Residence', 'vca-asm' ) .
							'</td><td>' .
								$supporter->residence .
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
					'</td></tr>' .
				'</table>';

			echo $table;

			$mbs->mb_bottom();

			if ( isset( $_GET['change'] ) && 'role' === $_GET['change'] ) {
				$current_roles = $current_user->roles;
				$current_role = array_shift( $current_roles );
				if ( in_array( $current_role, $vca_asm_roles->admin_roles ) &&
					in_array( $supporter->role_slug, $vca_asm_roles->user_sub_roles() ) &&
					(
						in_array( $current_role, $vca_asm_roles->global_admin_roles ) ||
						(
							$admin_nation &&
							$vca_asm_geography->has_nation( $supporter->city_id ) &&
							$admin_nation == $vca_asm_geography->has_nation( $supporter->city_id )
						)
					)
				) {
					$mbs->mb_top( array( 'title' => __( 'Change role', 'vca-asm' ), 'id' => 'role' ) );

					$role_options = array();
					foreach ( $vca_asm_roles->user_sub_roles() as $role_slug ) {
						$role_options[] = array(
							'label' => $vca_asm_roles->translated_roles[$role_slug],
							'value' => $role_slug
						);
					}

					$role_fields = array(
						array(
							'type' => 'select',
							'id' => 'role',
							'label' => __( 'Role', 'vca-asm' ),
							'options' => $role_options,
							'desc' => __( 'Change this user&apos;s role', 'vca-asm' ),
							'value' => ( isset( $_GET['role'] ) && isset( $update_role_success ) && true === $update_role_success ) ? $_GET['role'] : $supporter->role
						),
						array(
							'type' => 'hidden',
							'id' => 'profile_todo',
							'value' => 'update_role'
						),
						array(
							'type' => 'hidden',
							'id' => 'page',
							'value' => 'vca-asm-supporters'
						)
					);

					if ( isset( $_GET['gf'] ) ) {
						$role_fields[] = array(
							'type' => 'hidden',
							'id' => 'gf',
							'value' => $_GET['gf'] 
						);
					}
					if ( isset( $_GET['gfb'] ) ) {
						$role_fields[] = array(
							'type' => 'hidden',
							'id' => 'gfb',
							'value' => $_GET['gfb']
						);
					}
					if ( isset( $_GET['mf'] ) ) {
						$role_fields[] = array(
							'type' => 'hidden',
							'id' => 'mf',
							'value' => $_GET['mf'] 
						);
					}
					if ( isset( $_GET['rf'] ) ) {
						$role_fields[] = array(
							'type' => 'hidden',
							'id' => 'rf',
							'value' => $_GET['rf']
						);
					}
					if ( isset( $_GET['df'] ) ) {
						$role_fields[] = array(
							'type' => 'hidden',
							'id' => 'df',
							'value' => $_GET['df']
						);
					}
					if ( isset( $_GET['term'] ) ) {
						$role_fields[] = array(
							'type' => 'hidden',
							'id' => 'term',
							'value' => $_GET['term']
						);
					}
					if ( isset( $_GET['order'] ) ) {
						$role_fields[] = array(
							'type' => 'hidden',
							'id' => 'order',
							'value' => $_GET['order']
						);
					}
					if ( isset( $_GET['orderby'] ) ) {
						$role_fields[] = array(
							'type' => 'hidden',
							'id' => 'orderby',
							'value' => $_GET['orderby']
						);
					}
					if ( isset( $_GET['profile'] ) ) {
						$role_fields[] = array(
							'type' => 'hidden',
							'id' => 'profile',
							'value' => $_GET['profile']
						);
					}
					if ( isset( $_GET['todo'] ) ) {
						$role_fields[] = array(
							'type' => 'hidden',
							'id' => 'todo',
							'value' => $_GET['todo']
						);
					}
					if ( isset( $_GET['change'] ) ) {
						$role_fields[] = array(
							'type' => 'hidden',
							'id' => 'change',
							'value' => $_GET['change']
						);
					}

					$role_form = new VCA_ASM_Admin_Form( array(
						'echo' => true,
						'form' => true,
						'method' => 'get',
						'url' => '#',
						'action' => '',
						'button' => __( 'Change', 'vca-asm' ),
						'top_button' => false,
						'fields' => $role_fields
					));

					$role_form->output();

					$mbs->mb_bottom();
				}
			}

			$mbs->bottom();

		} else {
			$messages[] = array(
				'type' => 'error-pa',
				'message' => __( 'You do not have the rights required to view this supporter profile. Sorry.', 'vca-asm' )
			);
			echo $vca_asm_admin->convert_messages( $messages );
		}

		echo '<form name="vca_asm_supporter_all" method="post" action="' . $back_action . '">' .
					'<input type="hidden" name="submitted" value="y"/>' .
					'<p class="submit">' .
						'<input type="submit" name="submit" id="submit" class="button"' .
							' value="&larr; ' . _x( 'back', 'Admin Supporters', 'vca-asm' ) .
				'"></p></form>';

		$page->bottom();
	}

    /**
     * Lists all supporters
     *
     * @since 1.0
     * @access private
     * @param array $messages
     */
	private function list_supporters( $messages = array() ) {
        /** @var vca_asm_geography $vca_asm_geography */
        /** @var vca_asm_utilities $vca_asm_utilities */
		global $wpdb, $vca_asm_geography, $vca_asm_utilities;
		$current_user = wp_get_current_user();

		$admin_city = get_user_meta( $current_user->ID, 'city', true );
		$status = $vca_asm_geography->get_type( $admin_city );
		$admin_nation = $vca_asm_geography->has_nation( $admin_city );
		$admin_nation_name = $vca_asm_geography->get_name( $admin_nation );

		$url = "admin.php?page=vca-asm-supporters";
		$sort_url = $url;

		/* table order */
		extract( $vca_asm_utilities->table_order( 'first_name' ), EXTR_OVERWRITE );

		$headline = _x( 'Supporter Overview', 'Admin Supporters', 'vca-asm' );
		$table_headline = _x( 'All Supporters', 'Admin Supporters', 'vca-asm' );
		$metaqueries = array();
		$metaqueries['relation'] = 'AND';

		if( ! $current_user->has_cap('vca_asm_view_supporters_global') ) {
			if( $current_user->has_cap('vca_asm_view_supporters_nation') ) {
				$headline = str_replace( '%country%', $admin_nation_name, _x( 'Supporters from %country%', 'Admin Supporters', 'vca-asm' ) );
				$table_headline = str_replace( '%country%', $admin_nation_name, _x( 'All supporters from %country%', 'Admin Supporters', 'vca-asm' ) );
				$metaqueries[] = array(
					'key' => 'nation',
					'value' => $admin_nation
				);
			} else {
				$headline = str_replace( '%region_status%', $status, _x( 'Supporters of your %region_status%', 'Admin Supporters', 'vca-asm' ) );
				$table_headline = str_replace( '%region_status%', $status, _x( 'All Supporters of your %region_status%', 'Admin Supporters', 'vca-asm' ) );
				$metaqueries[] = array(
					'key' => 'city',
					'value' => intval( $admin_city )
				);
			}
		}

		if( isset( $_GET['todo'] ) && 'filter' ===  $_GET['todo'] ) {
			$geo_filter_by = 'city';
			if ( isset( $_POST['geo-filter-by'] ) && in_array( $_POST['geo-filter-by'], array( 'cg', 'nation', 'ng' ) ) ) {
				$geo_filter_by = $_POST['geo-filter-by'];
			} elseif ( isset( $_GET['gfb'] ) && in_array( $_GET['gfb'], array( 'cg', 'nation', 'ng' ) ) ) {
				$geo_filter_by = $_GET['gfb'];
			}
			$table_headline = _x( 'Filtered Supporters', 'Admin Supporters', 'vca-asm' );
			$sort_url = $url . '&amp;todo=filter';
			if( $current_user->has_cap('vca_asm_view_supporters_global') || $current_user->has_cap('vca_asm_view_supporters_nation') ) {
				if( isset( $_POST['geo-filter-'.$geo_filter_by] ) && is_array( $_POST['geo-filter-'.$geo_filter_by] ) ||
				    isset( $_GET['gf'] )
				) {
					$units = isset( $_POST['geo-filter-'.$geo_filter_by] ) ? $_POST['geo-filter-'.$geo_filter_by] : json_decode( $_GET['gf'] );
					$query_units = $units;
					if ( ! in_array( $geo_filter_by, array( 'city', 'nation' ) ) ) {
						switch ( $geo_filter_by ) {
							case 'cg':
								$query_units = array();
								foreach ( $units as $cg ) {
									if ( 0 == $cg ) {
										$cg_cities = $vca_asm_geography->get_cities_without( 'cg' );
									} else {
										$cg_cities = $vca_asm_geography->get_descendants( $cg, 'type=city&data=id' );
									}
									$query_units = array_merge( $query_units, $cg_cities );
								}
							break;

							case 'ng':
								$query_units = array();
								foreach ( $units as $ng ) {
									if ( 0 == $ng ) {
										$ng_nations = $vca_asm_geography->get_nations_without( 'ng' );
									} else {
										$ng_nations = $vca_asm_geography->get_descendants( $ng, 'type=nation&data=id' );
									}
									$query_units = array_merge( $query_units, $ng_nations );
								}
							break;
						}
					} elseif ( 'nation' === $geo_filter_by && is_array( $units ) && in_array( 'not-set', $units ) ) {
						$query_units[] = '';
					}

                    $gf_serialized = json_encode( $units );
                    $sort_url .= '&gf=' . urlencode($gf_serialized) .'&gfb=' . $geo_filter_by;

                    if ( in_array( $geo_filter_by, array( 'nation', 'ng' ) ) ) {
						$metaqueries[] = array(
							'key' => 'nation',
							'value' => $query_units,
							'compare' => 'IN'
						);
					} else {
						$metaqueries[] = array(
							'key' => 'city',
							'value' => $query_units,
							'compare' => 'IN'
						);
					}
				}
			} else {
				$table_headline =
					str_replace( '%region_status%', $status,
						_x( 'Filtered Supporters of your %region_status%', 'Admin Supporters', 'vca-asm' ) );
			}
			if( isset( $_POST['membership-filter'] ) && is_array( $_POST['membership-filter'] ) ) {
				$mf_serialized = wp_json_encode( $_POST['membership-filter'] );
				$sort_url .= '&amp;mf=' . urlencode($mf_serialized);
				$metaqueries[] = array(
					'key' => 'membership',
					'value' => $_POST['membership-filter'],
					'compare' => 'IN'
				);
			} elseif( isset( $_GET['mf'] ) ) {
				$sort_url .= '&mf=' . wp_json_encode($_GET['mf']);
                $mf = str_replace('\\', '', $_GET['mf']);
                $mf_unserialized = json_decode( $mf );
				$metaqueries[] = array(
					'key' => 'membership',
					'value' => $mf_unserialized,
					'compare' => 'IN'
				);
			}
			if( isset( $_POST['role-filter'] ) && is_array( $_POST['role-filter'] ) ) {
				if ( 1 === count( $_POST['role-filter'] ) ) {
					$rf = $_POST['role-filter'][0];
				} else {
					$rf = 'all';
				}
				$sort_url .= '&amp;rf=' . $rf;
			} elseif( isset( $_GET['rf'] ) ) {
				$rf = $_GET['rf'];
				$sort_url .= '&amp;rf=' . $rf;
			}
			if( isset( $_POST['dead-filter'] ) ) {
				$sort_url .= '&amp;df=1';
			} elseif( isset( $_GET['df'] ) && $_GET['df'] == 1 ) {
				$sort_url .= '&amp;df=1';
			}
			$empty_message = __( 'No results for the current filter criteria...', 'vca-asm' );
		}

		if ( isset( $rf ) && 'supporter' !== $rf ) {
			$args = array(
				'meta_query' => $metaqueries
			);
		} else {
			$args = array(
				'role' => 'supporter',
				'meta_query' => $metaqueries
			);
		}
		$supporters = get_users( $args );

		if( isset( $_GET['todo'] ) &&
		   'filter' ===  $_GET['todo'] &&
		   ( isset( $_POST['term'] ) || isset( $_GET['term'] ) ) &&
		   ( ! empty( $_POST['term'] ) || ! empty( $_GET['term'] ) )
		) {
			if( isset( $_POST['term'] ) ) {
				$term = $_POST['term'];
			} else {
				$term = $_GET['term'];
			}
			$sort_url = ! empty( $sort_url ) ? $sort_url . '&amp;term=' . $term : $url . '&amp;term=' . $term;
			$supp_query_results = $supporters;
			$supporters = array();
			$supp_ids = array();
			foreach ( $supp_query_results as $temp_supp ) {
				$terms = explode( ' ', $term );
				if ( count( $terms ) <= 1 ) {
					if (
						(
							strstr( mb_strtolower( get_user_meta( $temp_supp->ID, 'first_name', true ) ), mb_strtolower( $term ) ) ||
							strstr( mb_strtolower( get_user_meta( $temp_supp->ID, 'last_name', true ) ), mb_strtolower( $term ) ) ||
							strstr( mb_strtolower( $temp_supp->user_email ), mb_strtolower( $term ) )
						) &&
						! in_array( 'city', $temp_supp->roles ) &&
						! in_array( 'head_of', $temp_supp->roles )
					) {
						$supporters[] = $temp_supp;
						$supp_ids[] = $temp_supp->ID;
					}
				} else {
					foreach ( $terms as $partial_term ) {
						
						$partial_term = trim($partial_term);
						if (empty($partial_term)) {
							continue;
						}
						
						if (
							(
								strstr( mb_strtolower( get_user_meta( $temp_supp->ID, 'first_name', true ) ), mb_strtolower( $partial_term ) ) ||
								strstr( mb_strtolower( get_user_meta( $temp_supp->ID, 'last_name', true ) ), mb_strtolower( $partial_term ) ) ||
								strstr( mb_strtolower( $temp_supp->user_email ), mb_strtolower( $partial_term ) )
							) &&
							! in_array( $temp_supp->ID, $supp_ids ) &&
							! in_array( 'city', $temp_supp->roles ) &&
							! in_array( 'head_of', $temp_supp->roles )
						) {
							$supporters[] = $temp_supp;
							$supp_ids[] = $temp_supp->ID;
						}
					}
				}
			}
			$table_headline = str_replace( '%results%', count( $supporters ), str_replace( '%term%', $term, _x( 'Showing %results% search results for &quot;%term%&quot;', 'Admin Supporters', 'vca-asm' ) ) );
			$empty_message = sprintf( __( 'No results for the search term &quot;%s&quot;...', 'vca-asm' ), $term );
		}

		$cities = $vca_asm_geography->get_names( 'city' );
		$nations = $vca_asm_geography->get_names( 'nation' );
		$stati = $vca_asm_geography->get_types();
		$stati_conv = $vca_asm_geography->get_region_id_to_type();

		$supporters_ordered = array();
		$i = 0;
		foreach ( $supporters as $key => $supporter ) {
			$supporter_roles = $supporter->roles;
			$supporter_role = array_shift( $supporter_roles );
			if ( ( empty( $supporter->roles ) || ! in_array( $supporter_role, array( 'head_of', 'city' ) ) ) &&
				( ( ! isset( $rf  ) || '!supp' !== $rf ) ||
					(
						! empty( $supporter->roles ) &&
						'supporter' !== $supporter_role
					)
				)
			) {
				$supp_fname = get_user_meta( $supporter->ID, 'first_name', true );
				$supp_lname = get_user_meta( $supporter->ID, 'last_name', true );
				if( ( ! isset( $_GET['todo'] ) || 'search' !== $_GET['todo'] ) && ( ! isset( $_GET['df'] ) || 1 != $_GET['df'] ) && empty( $_POST['dead-filter'] ) && ( empty( $supp_fname ) || empty( $supp_lname ) ) ) {
					continue;
				}
				if ( $orderby === 'city' ||  $orderby === 'membership' ) {
					$supp_city = get_user_meta( $supporter->ID, 'city', true );
					$supporters_ordered[$i]['city'] = mb_substr( $cities[$supp_city], 0, 4 );
					if( $orderby === 'membership' ) {
						$supporters_ordered[$i]['membership'] = ( isset( $supp_city ) && $supp_city != 0 ) ? $this->get_membership_status( $supporter->ID, $stati[$supp_city] ) : __( 'No', 'vca-asm' );
					}
				} elseif ( $orderby === 'nation' ) {
					$supp_nation = get_user_meta( $supporter->ID, 'nation', true );
					$supp_nation = ( ! empty( $supp_nation ) || $supp_nation === 0 || $supp_nation === '0' ) ? $supp_nation : 'empty';
					$supporters_ordered[$i]['nation'] = mb_substr( $nations[$supp_nation], 0, 4 );
				} elseif ( $orderby === 'user_email' ) {
					$supporters_ordered[$i]['user_email'] = $supporter->user_email;
				} elseif ( $orderby === 'role' ) {
					$supporters_ordered[$i]['role'] = $supporter_role;
				} elseif ( $orderby === 'age' ) {
					$supp_bday = get_user_meta( $supporter->ID, 'birthday', true );
					$supporters_ordered[$i]['age'] = empty( $supp_bday ) ? 1 : ( doubleval(555555555555) - doubleval( $supp_bday ) );
				} elseif ( $orderby === 'mobile' ) {
					$supp_nation = get_user_meta( $supp_id, 'nation', true );
					$raw_num = $vca_asm_utilities->normalize_phone_number(
									get_user_meta( $supporter->ID, 'mobile', true ),
									array( 'nat_id' => $supp_nation ? $supp_nation : 0 )
								);
					$supporters_ordered[$i]['mobile'] = empty( $raw_num ) ? '999999999999999' : substr( $raw_num . '0000000000000000000', 0, 15 );
				} elseif ( $orderby === 'gender' ) {
					$supporters_ordered[$i]['gender'] = $vca_asm_utilities->convert_strings( get_user_meta( $supporter->ID, 'gender', true ) );
				} else {
					$supporters_ordered[$i][$orderby] = get_user_meta( $supporter->ID, $orderby, true );
				}
				$supporters_ordered[$i]['key'] = $key;
				$i++;
			}
		}
		$supporters_ordered = $vca_asm_utilities->sort_by_key( $supporters_ordered, $orderby, $order );

		$user_count = count( $supporters_ordered );
		if ( $user_count > $this->per_page ) {
			$cur_page = isset( $_GET['p'] ) ? $_GET['p'] : 1;
			$pagination_offset = $this->per_page * ( $cur_page - 1 );
			$total_pages = ceil( $user_count / $this->per_page );
			$cur_end = $total_pages == $cur_page ? $pagination_offset + ( $user_count % $this->per_page ) : $pagination_offset + $this->per_page;

			$pagination_args = array(
				'pagination' => true,
				'total_pages' => $total_pages,
				'current_page' => $cur_page
			);
		} else {
			$pagination_offset = 0;
			$cur_end = $user_count;
			$pagination_args = array( 'pagination' => false );
		}

		$rows = array();
		for ( $i = $pagination_offset; $i < $cur_end; $i++ ) {
			$supp_obj = $supporters[$supporters_ordered[$i]['key']];
			$supp_roles = $supp_obj->roles;
			$supp_role = array_shift( $supp_roles );
			$supp_id = $supp_obj->ID;
			$supp_fname = get_user_meta( $supp_id, 'first_name', true );
			$supp_lname = get_user_meta( $supp_id, 'last_name', true );
			$supp_city = get_user_meta( $supp_id, 'city', true );
			$supp_nation = get_user_meta( $supp_id, 'nation', true );
			$supp_nation = ( ! empty( $supp_nation ) || $supp_nation === 0 || $supp_nation === '0' ) ? $supp_nation : 'empty';
			$supp_bday = get_user_meta( $supp_id, 'birthday', true );
			$supp_age = ! empty( $supp_bday ) ? $vca_asm_utilities->date_diff( time(), intval( $supp_bday ) ) : array( 'year' => __( 'not set', 'vca-asm' ) );
			if( empty ( $supp_city ) ) {
				$supp_city = '0';
			}
			$photo_info = $this->photo_info( $supp_id );
			$supp_role = ! empty( $supp_role ) ? $supp_role : 'supporter';
			$supp_role_slug = $supp_role;
			$roles = get_option( 'wp_user_roles' );
			$supp_role = ! empty( $roles[$supp_role]['name'] ) ? $roles[$supp_role]['name'] : $supp_role;

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
			$rows[$i]['mobile'] = $vca_asm_utilities->normalize_phone_number(
										get_user_meta( $supp_id, 'mobile', true ),
										array(
											'nice' => true,
											'nat_id' => $supp_nation ? $supp_nation : 0
										)
									);
			$rows[$i]['nation'] = $nations[$supp_nation];
			$rows[$i]['region'] = $cities[$supp_city];
			if ( $supp_city != 0 ) {
				$rows[$i]['region'] .= ' (' . $stati_conv[$supp_city] . ')';
			}
			$rows[$i]['city'] = $rows[$i]['region'];
			$rows[$i]['role'] = $supp_role;
			$rows[$i]['role_slug'] = $supp_role_slug;
			$rows[$i]['membership'] = ( isset( $supp_city ) && $supp_city != 0 ) ? $this->get_membership_status( $supp_id, $stati[$supp_city] ) :  __( 'No', 'vca-asm' );
			$rows[$i]['membership_raw'] = get_user_meta( $supp_id, 'membership', true );
			$rows[$i]['age'] = $supp_age['year'];
			$rows[$i]['gender'] = $vca_asm_utilities->convert_strings( get_user_meta( $supp_id, 'gender', true ) );
		}

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
				'actions' => array( 'profile', 'delete-user' ),
				'cap' => array( 'profile', 'delete-user' )
			),
			array(
				'id' => 'last_name',
				'title' => __( 'Last Name', 'vca-asm' ),
				'sortable' => true,
				'strong' => true
			)
		);

		if( $current_user->has_cap( 'vca_asm_view_supporters_global' ) ) {
			$columns[] = array(
				'id' => 'nation',
				'title' => __( 'Country', 'vca-asm' ),
				'sortable' => true
			);
		}

		if( $current_user->has_cap( 'vca_asm_view_supporters_nation' ) || $current_user->has_cap( 'vca_asm_view_supporters_global' ) ) {
			$columns[] = array(
				'id' => 'city',
				'title' => __( 'City', 'vca-asm' ),
				'sortable' => true
			);
			$columns[] = array(
				'id' => 'role',
				'title' => __( 'User role', 'vca-asm' ),
				'sortable' => true,
				'actions' => array( 'role' ),
				'cap' => 'role'
			);
		}

		$columns[] = array(
			'id' => 'membership',
			'title' => __( 'Membership Status', 'vca-asm' ),
			'sortable' => true,
			'conversion' => 'membership',
			'actions' => array( 'edit_membership' ),
			'cap' => 'promote'
		);
		$columns[] = array(
			'id' => 'user_email',
			'title' => __( 'Email Address', 'vca-asm' ),
			'sortable' => true
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
			'legacy-screen' => false
		);
		$columns[] = array(
			'id' => 'gender',
			'title' => __( 'Gender', 'vca-asm' ),
			'sortable' => true,
			'legacy-screen' => false
		);

		$filter_fields = array(
			array(
				'type' => 'text',
				'label' =>  _x( 'Search Term', 'Admin Supporters', 'vca-asm' ),
				'id' => 'term',
				'desc' => _x( "You can search the Pool's userbase by first and last name as well as email address.", 'Admin Supporters', 'vca-asm' ) . '<br />' . _x( 'If you only want to filter the list and not search for anything specific, simply leave this field empty.', 'Admin Supporters', 'vca-asm' )
			)
		);

		if( $current_user->has_cap('vca_asm_view_supporters_global') ) {

			wp_enqueue_script( 'vca-asm-admin-supporter-filter' );
			$filter_params = array(
				'gfb' => ! empty( $_POST['geo-filter-by'] ) ? $_POST['geo-filter-by'] : ( ! empty( $_GET['gfb'] ) ? $_GET['gfb'] : 'city' )
			);
			wp_localize_script( 'vca-asm-admin-supporter-filter', 'filterParams', $filter_params );

			$filter_fields[] = array(
				'type' => 'select',
				'label' => _x( 'Filter by', 'Admin Supporters', 'vca-asm' ),
				'id' => 'geo-filter-by',
				'options' => array(
					array(
						'label' => __( 'Cities', 'vca-asm' ),
						'value' => 'city'
					),
					array(
						'label' => __( 'City Groups', 'vca-asm' ),
						'value' => 'cg'
					),
					array(
						'label' => __( 'Countries', 'vca-asm' ),
						'value' => 'nation'
					),
					array(
						'label' => __( 'Country Groups', 'vca-asm' ),
						'value' => 'ng'
					)
				),
				'desc' => _x( 'Select which geographical unit to filter by', 'Admin Supporters', 'vca-asm' ),
				'js-only' => true,
				'value' => $filter_params['gfb']
			);
		}

		if ( $current_user->has_cap('vca_asm_view_supporters_global') || $current_user->has_cap('vca_asm_view_supporters_nation') ) {
			if ( $current_user->has_cap('vca_asm_view_supporters_global') ) {
				$limit = false;
			} else {
				$limit = $admin_nation ? $admin_nation : 0;
			}
			$region_options_raw = $vca_asm_geography->options_array( array(
				'global_option' => __( 'not (yet) chosen', 'vca-asm' ),
				'type' => 'city',
				'descendants_of' => $limit
			));
			$region_options = array();
			foreach( $region_options_raw as $region_option ) {
				if( isset( $_POST['geo-filter-city'] ) && is_array( $_POST['geo-filter-city'] ) ) {
					if( in_array( $region_option['value'], $_POST['geo-filter-city'] ) ) {
						$region_option['checked'] = true;
					}
				} elseif( isset( $_GET['gf'] ) ) {
				    $gf = str_replace('\\', '', $_GET['gf']);
                    $gf_unserialized = json_decode( $gf );
                    if( in_array( $region_option['value'], $gf_unserialized ) ) {
						$region_option['checked'] = true;
					}
				} else {
					$region_option['checked'] = true;
				}
				$region_options[] = $region_option;
			}
			$filter_fields[] = array(
				'type' => 'checkbox_group',
				'label' => __( 'Cities', 'vca-asm' ),
				'id' => 'geo-filter-city',
				'row-class' => 'geo-filter',
				'options' => $region_options,
				'desc' => _x( 'Show only supporters from certain cities', 'Admin Supporters', 'vca-asm' ),
				'cols' => 3,
				'extra' => 'bulk_deselect'
			);

			$membership_labels = array(
				2 => _x( 'Supporters that are &quot;active members&quot; of their city', 'Admin Supporters', 'vca-asm' ),
				1 => _x( 'Supporters that have applied for membership status', 'Admin Supporters', 'vca-asm' ),
				0 => _x( 'Supporters that simply are registered to the Pool', 'Admin Supporters', 'vca-asm' )
			);

		} else {

			$membership_labels = array(
				2 => str_replace( '%region_status%', $status, _x( 'Supporters that are &quot;active members&quot; of your %region_status%', 'Admin Supporters', 'vca-asm' ) ),
				1 => str_replace( '%region_status%', $status, _x( 'Supporters that have applied for membership to your %region_status%', 'Admin Supporters', 'vca-asm' ) ),
				0 => _x( 'Supporters that simply live in your city', 'Admin Supporters', 'vca-asm' )
			);

		}

		if( $current_user->has_cap('vca_asm_view_supporters_global') ) {
			$region_options_raw = $vca_asm_geography->options_array( array(
				'global_option' => __( 'not part of any group', 'vca-asm' ),
				'type' => 'cg'
			));
			$region_options = array();
			foreach( $region_options_raw as $region_option ) {
				if( isset( $_POST['geo-filter-cg'] ) && is_array( $_POST['geo-filter-cg'] ) ) {
					if( in_array( $region_option['value'], $_POST['geo-filter-cg'] ) ) {
						$region_option['checked'] = true;
					}
				} elseif( isset( $_GET['gf'] ) ) {
                    $gf = str_replace('\\', '', $_GET['gf']);
                    $gf_unserialized = json_decode( $gf );
					if( in_array( $region_option['value'], $gf_unserialized ) ) {
						$region_option['checked'] = true;
					}
				} else {
					$region_option['checked'] = true;
				}
				$region_options[] = $region_option;
			}
			$filter_fields[] = array(
				'type' => 'checkbox_group',
				'label' => __( 'City Groups', 'vca-asm' ),
				'id' => 'geo-filter-cg',
				'row-class' => 'geo-filter',
				'options' => $region_options,
				'desc' => _x( 'Show only supporters from certain city groups', 'Admin Supporters', 'vca-asm' ),
				'cols' => 3,
				'extra' => 'bulk_deselect',
				'js-only' => true
			);

			$region_options_raw = $vca_asm_geography->options_array( array(
				'global_option_last' => __( 'other, non-listed country', 'vca-asm' ),
				'please_select' => true,
				'please_select_value' => 'not-set',
				'please_select_text' => __( 'not chosen...', 'vca-asm' ),
				'type' => 'nation'
			));
			$region_options = array();
			foreach( $region_options_raw as $region_option ) {
				if( isset( $_POST['geo-filter-nation'] ) && is_array( $_POST['geo-filter-nation'] ) ) {
					if( in_array( $region_option['value'], $_POST['geo-filter-nation'] ) ) {
						$region_option['checked'] = true;
					}
				} elseif( isset( $_GET['gf'] ) ) {
                    $gf = str_replace('\\', '', $_GET['gf']);
                    $gf_unserialized = json_decode( $gf );
					if( in_array( $region_option['value'], $gf_unserialized ) ) {
						$region_option['checked'] = true;
					}
				} else {
					$region_option['checked'] = true;
				}
				$region_options[] = $region_option;
			}
			$filter_fields[] = array(
				'type' => 'checkbox_group',
				'label' => __( 'Countries', 'vca-asm' ),
				'id' => 'geo-filter-nation',
				'row-class' => 'geo-filter',
				'options' => $region_options,
				'desc' => _x( 'Show only supporters from certain countries', 'Admin Supporters', 'vca-asm' ),
				'cols' => 3,
				'extra' => 'bulk_deselect',
				'js-only' => true
			);

			$region_options_raw = $vca_asm_geography->options_array( array(
				'global_option' => __( 'without associated country group', 'vca-asm' ),
				'type' => 'ng'
			));
			$region_options = array();
			foreach( $region_options_raw as $region_option ) {
				if( isset( $_POST['geo-filter-ng'] ) && is_array( $_POST['geo-filter-ng'] ) ) {
					if( in_array( $region_option['value'], $_POST['geo-filter-ng'] ) ) {
						$region_option['checked'] = true;
					}
				} elseif( isset( $_GET['gf'] ) ) {
                    $gf = str_replace('\\', '', $_GET['gf']);
                    $gf_unserialized = json_decode( $gf );
					if( in_array( $region_option['value'], $gf_unserialized ) ) {
						$region_option['checked'] = true;
					}
				} else {
					$region_option['checked'] = true;
				}
				$region_options[] = $region_option;
			}
			$filter_fields[] = array(
				'type' => 'checkbox_group',
				'label' => __( 'Country Groups', 'vca-asm' ),
				'id' => 'geo-filter-ng',
				'row-class' => 'geo-filter',
				'options' => $region_options,
				'desc' => _x( 'Show only supporters from certain country groups', 'Admin Supporters', 'vca-asm' ),
				'cols' => 3,
				'extra' => 'bulk_deselect',
				'js-only' => true
			);
		}

		if ( $current_user->has_cap('vca_asm_view_supporters_global') || $current_user->has_cap('vca_asm_view_supporters_nation') ) {
			$filter_fields[] = array(
				'type' => 'checkbox_group',
				'label' => __( 'User Role', 'vca-asm' ),
				'id' => 'role-filter',
				'options' => array(
					array(
						'value' => 'supporter',
						'label' => _x( 'Supporter', 'Plural Form', 'vca-asm' )
					),
					array(
						'value' => '!supp',
						'label' => __( 'Users with access to Administration', 'vca-asm' )
					)
				),
				'desc' => _x( 'Choose whether to only show supporters, administrative users or both.', 'Admin Supporters', 'vca-asm' ),
				'cols' => 1,
				'value' => ( isset( $_POST['role-filter'] ) ) ? $_POST['role-filter'] : ( ( isset( $_GET['rf'] ) ) ? ( 'all' === $_GET['rf'] ? array( 'supporter', '!supp' ) : $_GET['rf'] ) : array( 'supporter', '!supp' ) )
			);
        }

		if( isset( $_POST['membership-filter'] ) && is_array( $_POST['membership-filter'] ) ) {
			$checked_mem_options = array(
				0 => ( in_array( 0, $_POST['membership-filter'] ) ? true : false ),
				1 => ( in_array( 1, $_POST['membership-filter'] ) ? true : false ),
				2 => ( in_array( 2, $_POST['membership-filter'] ) ? true : false )
			);
		} elseif( isset( $_GET['mf'] ) ) {

			$mf = str_replace('\\', '', $_GET['mf']);
			$mf_unserialized = json_decode( $mf );

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
				$wpdb->get_var( "SELECT COUNT(*) FROM $wpdb->usermeta WHERE meta_key='membership' AND meta_value='1'" );
		} elseif( $current_user->has_cap('vca_asm_promote_supporters') ) {
			$pending_users = get_users( array(
				'role' => 'supporter',
				'meta_query' => array(
					'relation' => 'AND',
					array(
						'key' => 'city',
						'value' => $admin_city
					),
					array(
						'key' => 'membership',
						'value' => '1'
					)
				)
			));
			$pending = count( $pending_users );
		}

		/** OUTPUT **/

		$page = new VCA_ASM_Admin_Page( array(
			'echo' => true,
			'icon' => 'icon-supporters',
			'title' => $headline,
			'url' => 'admin.php?page=vca-asm-supporters',
			'messages' => $messages
		));
		$page->top();

		$mbs = new VCA_ASM_Admin_Metaboxes( array(
			'echo' => true,
			'title' => _x( 'Membership Applications', 'Admin Supporters', 'vca-asm' ),
			'id' => 'membership',
			'js' => true
		));
		$mbs->top();

		if( ! empty( $pending ) ) {
			$mbs->mb_top();
			if( $current_user->has_cap('vca_asm_promote_all_supporters') ) {
				echo '<p><strong>' .
						sprintf( _x( 'In total, currently %d supporters are waiting for their applications to be answered...', 'Admin Supporters', 'vca-asm' ), $pending ) .
					'</strong></p>';
			} else {
				echo '<div class="message-pa"><p>' .
						sprintf( _x( 'You have %d pending applications waiting to be answered...', 'Admin Supporters', 'vca-asm' ), $pending ) .
					'</p></div>';
			}
			echo '<span class="description">' . _x( 'Supporters are categorized in two groups: Those that are active in their cells or local crews and part of the creative process revolving around Viva con Agua and those that simply take an interest in VcA and/or show up for a festival or the like once in a while. Supporters may apply for the status of $quot;active membership$quot; via their &quot;Profile &amp; Settings&quot; menu.', 'Admin Supporters', 'vca-asm' ) . '</span>';
			$mbs->mb_bottom();
		}

		$mbs->mb_top( array(
			'title' => _x( 'Search and Filter Criteria', 'Admin Supporters', 'vca-asm' ),
			'id' => 'filter'
		));

		$filter_form = new VCA_ASM_Admin_Form( array(
			'echo' => true,
			'form' => true,
			'method' => 'post',
			'metaboxes' => false,
			'url' => $url,
			'action' => $url .'&amp;todo=filter',
			'id' => 0,
			'button' => _x( 'Search / Filter', 'Admin Supporters', 'vca-asm' ),
			'top_button' => false,
			'back' => false,
			'back_url' => '#',
			'fields' => $filter_fields
		));
		$filter_form->output();

		$mbs->mb_bottom();
		$mbs->bottom();

		$tbl_args = array(
			'echo' => true,
			'page_slug' => 'vca-asm-supporters',
			'base_url' => 'admin.php?page=vca-asm-supporters',
			'sort_url' => ! empty( $sort_url ) ? $sort_url : '',
			'headline' => $table_headline,
			'icon' => 'icon-supporters',
			'dspl_cnt' => true,
			'count' => $user_count,
			'cnt_txt' => __( '%d Supporters', 'vca-asm' ),
			'empty_message' => ! empty( $empty_message ) ? $empty_message : '',
			'with_bulk' => true,
			'bulk_confirm' => __( 'Manage the membership of all selected supporters?', 'vca-asm' ),
			'bulk_name' => 'supporters',
			'bulk_desc' => __( 'Change selected supporters\' Membership Status', 'vca-asm' ),
			'bulk_actions' => array(
				array(
					'label' => __( 'Accept application / Add as members', 'vca-asm' ),
					'value' => 'accept'
				),
				array(
					'label' => __( 'Deny application / End membership', 'vca-asm' ),
					'value' => 'deny'
				)
			)
		);
		$tbl_args = array_merge( $tbl_args, $pagination_args );
		$tbl = new VCA_ASM_Admin_Table( $tbl_args, $columns, $rows );

		$tbl->output();

		if( isset( $_GET['todo'] ) ) {
			echo '<form name="vca_asm_supporter_all" method="post" action="admin.php?page=vca-asm-supporters">' .
					'<input type="hidden" name="submitted" value="y"/>' .
					'<p class="submit">' .
						'<input type="submit" name="submit" id="submit" class="button-secondary"' .
							' value="&larr;&nbsp;' . _x( 'Show all supporters', 'Admin Supporters', 'vca-asm' ) .
				'"></p></form>';
		}

		$page->bottom();
	}

	/**** UTILITY METHODS *****/

    /**
     * Fetches membership status from databse and converts to human readable form
     *
     * @since 1.0
     * @access public
     * @param $id
     * @param $region_status
     * @return string
     */
	public function get_membership_status( $id, $region_status ) {
		$status = get_user_meta( $id, 'membership', true );
		if( $region_status != 'city' ) {
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
     * @param $supporter
     * @return string
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
     * @param $activity
     * @param $supporter
     * @param $type
     * @return bool|string
     */
	public function note_info( $activity, $supporter, $type ) {
        /** @var wpdb $wpdb */
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
		$note = isset( $notes[0]['notes'] ) ? $notes[0]['notes'] : '';

		if( empty( $note ) ) {
			return false;
		}

		$note = preg_replace( "/&apos;|'|\r|\n/", "", str_replace( '"', '&quot;', nl2br( trim( $note ) ) ) );

		$note_info = '\'' .
			'<p>' . $note . '</p>' . '\'';

		return $note_info;
	}

} // class

endif; // class exists

?>