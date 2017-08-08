<?php

/**
 * VCA_ASM_Admin_Geography class.
 *
 * This class contains properties and methods for
 * the "geography" admin user interface
 * as well as for the addition and editing of regional information.
 *
 * @package VcA Activity & Supporter Management
 * @since 1.0
 */

if ( ! class_exists( 'VCA_ASM_Admin_Geography' ) ) :

class VCA_ASM_Admin_Geography {

	/**
	 * Geography Controller
	 *
	 * @since 1.0
	 * @access public
	 */
	public function control() {
		global $current_user, $wpdb,
			$vca_asm_finances, $vca_asm_geography,
			$vca_asm_admin_settings;

		$messages = array();

		if ( isset( $_GET['id'] ) ) {
			$region_user_query = $wpdb->get_results(
				"SELECT has_user, user_id, pass, user FROM " .
				$wpdb->prefix . "vca_asm_geography " .
				"WHERE id = " . $_GET['id'] . " LIMIT 1", ARRAY_A
			);
			$region_user = isset( $region_user_query[0] ) ? $region_user_query[0] : false;
		}

		if ( isset( $_GET['todo'] ) ) {
			switch ( $_GET['todo'] ) {

				case "delete":
					$has_cap = false;
					if ( isset( $_GET['id'] ) && $_GET['id'] != NULL ) {
						$type = $vca_asm_geography->get_type( $_GET['id'], false );
						if ( in_array( $type, array( 'city', 'lc', 'cell' ) ) ) {
							$type = 'city';
							$nation = $vca_asm_geography->has_nation( $_GET['id'] );
						}
						if (
							$current_user->has_cap( 'vca_asm_manage_network_global' ) ||
							(
								$current_user->has_cap( 'vca_asm_manage_network_nation' ) &&
								(
									'cg' === $type ||
									(
										'city' === $type &&
										is_numeric( $nation ) &&
										$nation == $vca_asm_geography->has_nation( get_user_meta( $current_user->ID, 'city', true ) )
									)
								)
							)
						) {
							$has_cap = true;
						}
						if ( ! $has_cap ) {
							$messages[] = array(
								'type' => 'error-pa',
								'message' => __( 'You cannot delete this data. Sorry.', 'vca-asm' )
							);
						} else {
							$success = $vca_asm_geography->delete( $_GET['id'] );
							if ( $success ) {
								$messages[] = array(
									'type' => 'message',
									'message' => __( 'The selected geographical unit has been successfully deleted.', 'vca-asm' )
								);
							} else {
								$messages[] = array(
									'type' => 'error-pa',
									'message' => __( 'There was an error deleting this data. Sorry.', 'vca-asm' )
								);
							}
						}
					} else {
						$messages[] = array(
							'type' => 'error-pa',
							'message' => __( 'There was an error deleting this data. Sorry.', 'vca-asm' )
						);
					}
					unset($_GET['todo'], $_GET['id']);
					if ( isset( $type ) ) {
						$this->view( $messages, $type );
					} else {
						$this->view( $messages );
					}
				break;

				case "save":
					$has_cap = false;
					if ( isset( $_GET['id'] ) && $_GET['id'] != NULL ) {
						$type = $vca_asm_geography->get_type( $_GET['id'], false );
						if ( in_array( $type, array( 'city', 'lc', 'cell' ) ) ) {
							$type = 'city';
							$nation = $vca_asm_geography->has_nation( $_GET['id'] );
						}
						if (
							$current_user->has_cap( 'vca_asm_manage_network_global' ) ||
							(
								$current_user->has_cap( 'vca_asm_manage_network_nation' ) &&
								(
									'cg' === $type ||
									(
										'city' === $type &&
										is_numeric( $nation ) &&
										$nation == $vca_asm_geography->has_nation( get_user_meta( $current_user->ID, 'city', true ) )
									)
								)
							)
						) {
							$has_cap = true;
						}
					} elseif ( isset( $_GET['type'] ) && $_GET['type'] != NULL ) {
						if (
							$current_user->has_cap( 'vca_asm_manage_network_global' ) ||
							(
								$current_user->has_cap( 'vca_asm_manage_network_nation' ) &&
								in_array( $_GET['type'], array( 'city', 'cg' ) )
							)
						) {
							$has_cap = true;
						}
					}

					if ( ! $has_cap ) {
						$messages[] = array(
							'type' => 'error-pa',
							'message' => sprintf( __( 'You do not have the required capabilities to edit/save %s. Sorry.', 'vca-asm' ), ( ! empty( $_POST['name'] ) ? $_POST['name'] : __( 'this data', 'vca-asm' ) ) )
						);
					} else {
						if ( isset( $_POST['has_user'] ) ) {
							$has_user = 1;
							if( ( ! isset( $_POST['user'] ) || ! isset( $_POST['pass'] ) || ! isset( $_POST['email'] ) ) && $region_user['has_user'] != 1 ) {
								$messages[] = array(
									'type' => 'error',
									'message' => __( 'If this region is supposed to have a Head-Of User, please set its username, password and email.', 'vca-asm' )
								);

								$this->edit( array( 'id' => $_GET['id'], 'messages' => $messages ) );
								return;
							} elseif ( ( ! isset( $_POST['pass'] ) || ! isset( $_POST['email'] ) ) && $region_user['has_user'] != 1 ) {
								$messages[] = array(
									'type' => 'error',
									'message' => __( 'Please do not leave the password or email field blank as long as the region has a user assigned.', 'vca-asm' )
								);
								if ( isset( $_GET['id'] ) ) {
									$this->edit( array( 'id' => $_GET['id'], 'messages' => $messages ) );
								} else {
									$this->edit( array( 'messages' => $messages ) );
								}
								return;
							} elseif ( isset( $region_user ) && is_array( $region_user ) && 1 == $region_user['has_user'] ) {
								if ( ! empty( $region_user['pass'] ) ) {
									$old_pass = rtrim( mcrypt_decrypt( MCRYPT_RIJNDAEL_256, md5(REGION_KEY), base64_decode($region_user['pass']), MCRYPT_MODE_CBC, md5(md5(REGION_KEY) ) ), "\0");
									if ( $old_pass != $_POST['pass'] ) {
										wp_update_user(
											array(
												'ID' => $region_user['user_id'],
												'user_pass' => $_POST['pass'],
												'user_email' => $_POST['email']
											)
										);
									} else {
										wp_update_user(
											array(
												'ID' => $region_user['user_id'],
												'user_email' => $_POST['email']
											)
										);
									}
									$new_pass = base64_encode( mcrypt_encrypt( MCRYPT_RIJNDAEL_256, md5(REGION_KEY), $_POST['pass'], MCRYPT_MODE_CBC, md5(md5(REGION_KEY)) ) );
								} else {
									if( 'unbekannt...' != $_POST['pass'] ) {
										wp_update_user(
											array(
												'ID' => $region_user['user_id'],
												'user_pass' => $_POST['pass'],
												'user_email' => $_POST['email']
											)
										);
										$new_pass = base64_encode( mcrypt_encrypt( MCRYPT_RIJNDAEL_256, md5(REGION_KEY), $_POST['pass'], MCRYPT_MODE_CBC, md5(md5(REGION_KEY)) ) );
									} else {
										wp_update_user(
											array(
												'ID' => $region_user['user_id'],
												'user_email' => $_POST['email']
											)
										);
										$new_pass = '';
									}
								}
								$new_user = $region_user['user'];
								$region_user_id = $region_user['user_id'];
							} elseif ( ! isset( $region_user ) || 1 != $region_user['has_user'] ) {
								$region_user_id = wp_create_user( $_POST['user'], $_POST['pass'], $_POST['email'] );
								if( ! is_int( $region_user_id ) ) {
									$messages[] = array(
										'type' => 'error',
										'message' => __( 'Either the username is empty, already exists or the email is already in use.', 'vca-asm' )
									);
									$this->edit( array( 'id' => $_GET['id'], 'messages' => $messages ) );
									return;
								}
								$user_obj = new WP_User( $region_user_id );
								$user_obj->remove_role( 'supporter' );
								$user_obj->add_role( 'city' );
								update_user_meta( $region_user_id, 'membership', '2' );
								update_user_meta( $region_user_id, 'first_name', 'Viva con Agua' );
								update_user_meta( $region_user_id, 'last_name', $_POST['name'] );
								update_user_meta( $region_user_id, 'nickname', 'VcA ' . $_POST['name'] );
								update_user_meta( $region_user_id, 'mail_switch', 'none' );
								update_user_meta( $region_user_id, 'birthday', '1159444800' );
								$new_pass = base64_encode( mcrypt_encrypt( MCRYPT_RIJNDAEL_256, md5(REGION_KEY), $_POST['pass'], MCRYPT_MODE_CBC, md5(md5(REGION_KEY)) ) );
								$new_user = $_POST['user'];
							}
						} elseif ( ! isset( $_POST['has_user'] ) && isset( $region_user ) && is_array( $region_user ) && $region_user['has_user'] == 1 ) {
							wp_delete_user( $region_user['user_id'] );
							$has_user = 0;
							$region_user_id = 0;
							$new_pass = '';
							$new_user = '';
						} else {
							$has_user = 0;
							$region_user_id = 0;
							$new_pass = '';
							$new_user = '';
						}

						if ( isset( $_GET['id'] ) && $_GET['id'] != NULL ) {
							$wpdb->update(
								$wpdb->prefix.'vca_asm_geography',
								array(
									'name' => $_POST['name'],
									'type' => $_POST['type'],
									'phone_code' => ( isset( $_POST['phone_code'] ) && is_numeric( $_POST['phone_code'] ) ) ? $_POST['phone_code'] : 0,
									'alpha_code' => ( isset( $_POST['alpha_code'] ) && ! empty( $_POST['alpha_code'] ) ) ? $_POST['alpha_code'] : 'xx',
									'currency_name' => ( isset( $_POST['currency_name'] ) && ! empty( $_POST['currency_name'] ) ) ? $_POST['currency_name'] : '',
									'currency_code' => ( isset( $_POST['currency_code'] ) && ! empty( $_POST['currency_code'] ) ) ? $_POST['currency_code'] : '',
									'currency_minor_name' => ( isset( $_POST['currency_minor_name'] ) && ! empty( $_POST['currency_minor_name'] ) ) ? $_POST['currency_minor_name'] : '',
									'has_user' => $has_user,
									'user_id' => $region_user_id,
									'user' => $new_user,
									'pass' => $new_pass
								),
								array( 'id'=> $_GET['id'] ),
								array( '%s', '%s', '%d', '%s', '%s', '%s', '%s', '%d', '%d', '%s', '%s' ),
								array( '%d' )
							);
							$region_id = $_GET['id'];

							$former_children = $vca_asm_geography->get_descendants( $region_id, array(
									'data' => 'id',
									'format' => 'array'
							) );
							if( ! empty( $_POST['children'] ) && is_array( $_POST['children'] ) ) {
								foreach ( $_POST['children'] as $descendant ) {
									if( ! in_array( $descendant, $former_children ) ) {
										$wpdb->insert(
											$wpdb->prefix.'vca_asm_geography_hierarchy',
											array(
												'ancestor' => $region_id,
												'ancestor_type' => $_POST['type'],
												'descendant' => $descendant
											),
											array( '%d', '%s', '%d' )
										);
									} else {
										if ( ( $key = array_search( $descendant, $former_children ) ) !== false ) {
											unset( $former_children[$key] );
										}
									}
								}
							}
							if ( ! empty( $former_children ) ) {
								$wpdb->query(
									"DELETE FROM " . $wpdb->prefix."vca_asm_geography_hierarchy " .
									"WHERE ancestor = " . $region_id .
									" AND descendant IN (" . implode( ',', $former_children ) . ")"
								);
							}

							$former_nation = $vca_asm_geography->get_ancestors( $region_id, array(
									'data' => 'id',
									'format' => 'string',
									'type' => 'nation'
							) );
							if ( ! empty( $_POST['parent_nation'] ) && is_numeric( $_POST['parent_nation'] ) ) {
								if( $_POST['parent_nation'] != $former_nation ) {
									if ( empty( $former_nation ) ) {
										$wpdb->insert(
											$wpdb->prefix.'vca_asm_geography_hierarchy',
											array(
												'ancestor' => $_POST['parent_nation'],
												'ancestor_type' => 'nation',
												'descendant' => $region_id
											),
											array( '%d', '%s', '%d' )
										);
									} else {
										$wpdb->update(
											$wpdb->prefix.'vca_asm_geography_hierarchy',
											array(
												'ancestor' => $_POST['parent_nation']
											),
											array(
												'ancestor_type' => 'nation',
												'descendant' => $region_id
											),
											array( '%d' ),
											array( '%s', '%d' )
										);
									}
								}
							}

							$messages[] = array(
								'type' => 'message-pa',
								'message' => sprintf( __( '%s successfully updated!', 'vca-asm' ), $_POST['name'] )
							);

						} else {
							$wpdb->insert(
								$wpdb->prefix.'vca_asm_geography',
								array(
									'name' => $_POST['name'],
									'type' => $_POST['type'],
									'phone_code' => ( isset( $_POST['phone_code'] ) && is_numeric( $_POST['phone_code'] ) ) ? $_POST['phone_code'] : 0,
									'alpha_code' => ( isset( $_POST['alpha_code'] ) && ! empty( $_POST['alpha_code'] ) ) ? $_POST['alpha_code'] : 'xx',
									'currency_name' => ( isset( $_POST['currency_name'] ) && is_numeric( $_POST['currency_name'] ) ) ? $_POST['currency_name'] : '',
									'currency_code' => ( isset( $_POST['currency_code'] ) && is_numeric( $_POST['currency_code'] ) ) ? $_POST['currency_code'] : '',
									'has_user' => $has_user,
									'user_id' => isset( $region_user_id ) ? $region_user_id : 0,
									'user' => $_POST['user'],
									'pass' => base64_encode( mcrypt_encrypt( MCRYPT_RIJNDAEL_256, md5(REGION_KEY), $_POST['pass'], MCRYPT_MODE_CBC, md5(md5(REGION_KEY)) ) ),
									'supporters' => 0,
									'members' => 0
								),
								array( '%s', '%s', '%d', '%s', '%s', '%s', '%d', '%d', '%s', '%s', '%d', '%d' )
							);
							$region_id = $wpdb->insert_id;

							if ( in_array( $_POST['type'], array( 'city', 'lc', 'cell' ) ) ) {
								$vca_asm_finances->create_account( $region_id, 'econ' );
								$vca_asm_finances->create_account( $region_id, 'donations' );
							}

							if ( 'nation' === $_POST['type'] ) {
								$vca_asm_admin_settings->insert_autoresponses( $region_id );
							}

							if ( ! empty( $_POST['parent_nation'] ) && is_numeric( $_POST['parent_nation'] ) ) {
								$wpdb->insert(
									$wpdb->prefix.'vca_asm_geography_hierarchy',
									array(
										'ancestor' => $_POST['parent_nation'],
										'ancestor_type' => 'nation',
										'descendant' => $region_id
									),
									array( '%d', '%s', '%d' )
								);
							}

							if( ! empty( $_POST['children'] ) && is_array( $_POST['children'] ) ) {
								foreach ( $_POST['children'] as $descendant ) {
									$wpdb->insert(
										$wpdb->prefix.'vca_asm_geography_hierarchy',
										array(
											'ancestor' => $region_id,
											'ancestor_type' => $_POST['type'],
											'descendant' => $descendant
										),
										array( '%d', '%s', '%d' )
									);
								}
							}

							$messages[] = array(
								'type' => 'message-pa',
								'message' => sprintf( __( '%s successfully added!', 'vca-asm' ), $_POST['name'] )
							);
						}
						/* Set City User's region ID */
						if ( $has_user == 1 ) {
							update_user_meta( $region_user_id, 'region', $region_id );
							update_user_meta( $region_user_id, 'city', $region_id );
							$nat_id = $vca_asm_geography->has_nation( $region_id );
							$nat_id = $nat_id ? $nat_id : 40;
							update_user_meta( $region_user_id, 'nation', $nat_id );
						}
					}

					if ( isset( $type ) ) {
						$this->view( $messages, $type );
					} else {
						$this->view( $messages );
					}
				break;

				case "edit":
					$this->edit( array( 'id' => $_GET['id'] ) );
				break;

				case "new":
					$this->edit();
				break;

				default:
					$this->view( $messages );
			}
		} else {
			$this->view( $messages );
		}
	}

	/**
	 * Region administration menu
	 *
	 * @since 1.0
	 * @access public
	 */
	public function view( $messages = array(), $active_tab = 'city' ) {
		global $vca_asm_geography, $vca_asm_admin;
				$current_user = wp_get_current_user();

		if ( isset( $_GET['tab'] ) && in_array( $_GET['tab'], array( 'city', 'cg', 'nation', 'ng' ) ) ) {
			$active_tab = $_GET['tab'];
		} elseif ( ! in_array( $active_tab, array( 'city', 'cg', 'nation', 'ng' ) ) ) {
			$active_tab = 'city';
		}

		$output = '';

		$adminpage = new VCA_ASM_Admin_Page( array(
			'icon' => 'icon-geography',
			'title' => _x( 'Geography', 'Geography Admin Menu', 'vca-asm' ),
			'messages' => $messages,
			'url' => '?page=vca-asm-geography',
			'tabs' => array(
				array(
					'title' => _x( 'Cities (Crews)', 'Geography Admin Menu', 'vca-asm' ),
					'value' => 'city',
					'icon' => 'icon-city'
				),
				array(
					'title' => _x( 'City Groups', 'Geography Admin Menu', 'vca-asm' ),
					'value' => 'cg',
					'icon' => 'icon-city-group'
				),
				array(
					'title' => _x( 'Countries', 'Geography Admin Menu', 'vca-asm' ),
					'value' => 'nation',
					'icon' => 'icon-nation'
				),
				array(
					'title' => _x( 'Country Groups', 'Geography Admin Menu', 'vca-asm' ),
					'value' => 'ng',
					'icon' => 'icon-nation-group'
				)
			),
			'active_tab' => $active_tab
		));

		$button = '';
		if (
			$current_user->has_cap( 'vca_asm_manage_network_global' ) ||
		   (
				$current_user->has_cap( 'vca_asm_manage_network_nation' ) &&
				(
					'city' === $active_tab ||
					'cg' === $active_tab
				)
		   )
		) {
			$button = '<form method="post" action="admin.php?page=vca-asm-geography&amp;todo=new&amp;type=' . $active_tab . '">' .
				'<input type="submit" class="button-secondary" value="+ ' . sprintf( __( 'add %s', 'vca-asm' ), $vca_asm_geography->convert_type( $active_tab ) ) . '" />' .
			'</form>';
		}

		$output .= $adminpage->top();

		$output .= '<br />' . $button . '<br />';

		$output .= $this->list_geography( $active_tab );

		$output .= '<br />' . $button;

		$output .= $adminpage->bottom();

		echo $output;
	}

	/**
	 * Lists geographical units of specified type
	 *
	 * @since 1.0
	 * @access private
	 */
	private function list_geography( $type ) {
		global $vca_asm_geography, $vca_asm_utilities;
		$current_user = wp_get_current_user();

		if ( ! get_transient( 'vca-asm-update-member-count' ) ) {
			$vca_asm_geography->update_member_count();
			set_transient( 'vca-asm-update-member-count', 1, 60*60*24 );
		}

		$url = "admin.php?page=vca-asm-geography";

		extract( $vca_asm_utilities->table_order() );
		$rows = $vca_asm_geography->get_all( $orderby, $order, $type );

		$name_col = array(
			'id' => 'name',
			'title' => $vca_asm_geography->convert_type( $type ),
			'sortable' => true,
			'strong' => true,
			'link' => array(
				'title' => __( 'Edit %s', 'vca-asm' ),
				'title_row_data' => 'name',
				'url' => 'admin.php?page=vca-asm-geography&todo=edit&id=%d',
				'url_row_data' => 'id'
			),
			'actions' => array( 'edit', 'delete' ),
			'cap' => $type
		);
		$type_col = array(
			'id' => 'type',
			'title' => __( 'Type', 'vca-asm' ),
			'sortable' => true,
			'conversion' => 'geo-type'
		);
		$groups_col = array(
			'id' => 'groups',
			'title' => __( 'Group(s)', 'vca-asm' ),
			'sortable' => false
		);
		$phone_col = array(
			'id' => 'phone_code',
			'title' => __( 'Phone Country Code', 'vca-asm' ),
			'sortable' => false,
			'conversion' => 'pcc'
		);
		$supp_col = array(
			'id' => 'supporters',
			'title' => __( 'Supporters', 'vca-asm' ),
			'sortable' => true
		);
		$mem_col = array(
			'id' => 'members',
			'title' => __( 'Members', 'vca-asm' ),
			'sortable' => true
		);

		switch ( $type ) {
			case "cg":
				$columns = array(
					$name_col,
					$supp_col,
					$mem_col
				);
			break;

			case "nation":
				$columns = array(
					$name_col,
					$groups_col,
					$phone_col,
					$supp_col,
					$mem_col
				);
			break;

			case "ng":
				$columns = array(
					$name_col,
					$supp_col,
					$mem_col
				);
			break;

			default:
			case "city":
				$columns = array(
					$name_col,
					$type_col,
					$groups_col,
					$supp_col,
					$mem_col
				);
			break;
		}

		$args = array(
			'base_url' => 'admin.php?page=vca-asm-geography',
			'sort_url' => 'admin.php?page=vca-asm-geography',
			'echo' => false
		);
		$the_table = new VCA_ASM_Admin_Table( $args, $columns, $rows );
		return $the_table->output();
	}

	/**
	 * Edit a region
	 *
	 * @since 1.0
	 * @access public
	 */
	public function edit( $args = array() )
	{
		global $current_user, $vca_asm_admin, $vca_asm_geography;

		$default_args = array(
			'id' => NULL,
			'type' => 'city',
			'messages' => array()
		);
		extract( wp_parse_args( $args, $default_args ), EXTR_SKIP );

		$url = 'admin.php?page=vca-asm-geography';
		if ( ! empty( $id ) ) {
			$form_action = $url . '&todo=save&id=' . $id;
		} else {
			$form_action = $url . '&todo=save&type=' . $type;
		}

		if( NULL === $id ) {
			$type = ! empty( $_GET['type'] ) ? $_GET['type'] : $type;
			$fields = $this->create_fields( $type );
			$title = sprintf( __( 'Add New %s', 'vca-asm' ), $vca_asm_geography->convert_type( $type ) );
		} else {
			list( $fields, $name ) = $this->populate_fields( $id );
			$title = sprintf( __( 'Edit &quot;%s&quot;', 'vca-asm' ), $name );
			$type = $vca_asm_geography->get_type( $id, false );
		}
		$type = ( $type === 'lc' || $type === 'cell' ) ? 'city' : $type;

		$output = '<div class="wrap">' .
				'<div id="icon-' . $type . '" class="icon32-pa"></div>' .
				'<h2>' . $title . '</h2><br />';

		if( NULL === $id ) {
			$output .= '<ul class="horiz-list">' .
					'<li>' .
						__( 'Add new', 'vca-asm' ) . ': ' .
					'</li>' .
					'<li>' .
						'<a title="' . __( 'New city (Crew)', 'vca-asm' ) . '" ' .
						'href="admin.php?page=vca-asm-geography&amp;todo=new&amp;type=city">' .
							__( 'City', 'vca-asm' ) .
						'</a>' .
					'</li>' .
					'<li>' .
						'<a title="' . __( 'New group of cities', 'vca-asm' ) . '" ' .
						'href="admin.php?page=vca-asm-geography&amp;todo=new&amp;type=cg">' .
							__( 'City Group', 'vca-asm' ) .
						'</a>' .
					'</li>';
			if ( $current_user->has_cap( 'vca_asm_manage_network_global' ) ) {
				$output .= '<li>' .
							'<a title="' . __( 'New country', 'vca-asm' ) . '" ' .
							'href="admin.php?page=vca-asm-geography&amp;todo=new&amp;type=nation">' .
								__( 'Country', 'vca-asm' ) .
							'</a>' .
						'</li>' .
						'<li>' .
							'<a title="' . __( 'New group of countries', 'vca-asm' ) . '" ' .
							'href="admin.php?page=vca-asm-geography&amp;todo=new&amp;type=ng">' .
								__( 'Country Group', 'vca-asm' ) .
							'</a>' .
						'</li>';
			}
			$output .= '</ul><br />';
		}

		$user_nation = $vca_asm_geography->has_nation( get_user_meta( $current_user->ID, 'city', true ) );
		if (
			$current_user->has_cap( 'vca_asm_manage_network_global' ) ||
			(
				$current_user->has_cap( 'vca_asm_manage_network_nation' ) &&
				(
					NULL === $id ||
					(
						$user_nation &&
						(
							(
								'city' === $type &&
								$user_nation === $vca_asm_geography->has_nation( $id )
							) ||
							(
								'nation' === $type &&
								$user_nation == $id
							)
						)
					)
				)
			)
		) {
			$args = array(
				'echo' => false,
				'form' => true,
				'metaboxes' => true,
				'action' => $form_action,
				'id' => $id,
				'back' => true,
				'back_url' => $url . '&tab=' . $type,
				'fields' => $fields
			);
			$form = new VCA_ASM_Admin_Form( $args );
			$output .= $form->output();
		} else {
			$messages = array(
				array(
					'type' => 'error',
					'message' => __( 'You do not have the rights to edit this. Sorry.', 'vca-asm' )
				)
			);
			$output .= $vca_asm_admin->convert_messages( $messages );
		}

		$output .= '</div>';

		echo $output;
	}

	/**
	 * Returns an array of fields for a city
	 *
	 * @since 1.0
	 * @access private
	 */
	private function create_fields( $type = 'city' ) {
		global $current_user, $vca_asm_geography;

		$ccbg_label = '';
		$ccbg_desc = '';
		$ccbg_options = array();
		if( 'nation' === $type ) {
			$ccbg_label = __( 'Cities', 'vca-asm' );
			$ccbg_desc = __( 'The cities that are part of this nation', 'vca-asm' );
			$ccbg_options = $vca_asm_geography->options_array( 'type=city&not_has_nation=true' );
		} elseif( 'cg' === $type ) {
			$ccbg_label = __( 'Cities', 'vca-asm' );
			$ccbg_desc = __( 'The cities that are part of this group', 'vca-asm' );
			$ccbg_options = $vca_asm_geography->options_array( 'type=city' );
		} elseif( 'ng' === $type ) {
			$ccbg_label = __( 'Countries', 'vca-asm' );
			$ccbg_desc = __( 'The countries that are part of this group', 'vca-asm' );
			$ccbg_options = $vca_asm_geography->options_array( 'type=nation' );
		}

		$children_cb_group = array(
			'type' => 'checkbox_group',
			'label' => $ccbg_label,
			'id' => 'children',
			'desc' => $ccbg_desc,
			'options' => $ccbg_options
		);

		switch ( $type ) {
			case 'ng':
				$fields = array(
					array(
						'title' => __( 'The group', 'vca-asm' ),
						'fields' => array(
							array(
								'type' => 'text',
								'label' => __( 'Name of Group', 'vca-asm' ),
								'id' => 'name',
								'desc' => __( 'The name or title of the group of countries', 'vca-asm' )
							),
							array(
								'type' => 'hidden',
								'id' => 'type',
								'value' => 'ng'
							)
						)
					),
					array(
						'title' => __( 'Countries', 'vca-asm' ),
						'fields' => array(
							$children_cb_group
						)
					)
				);
			break;

			case 'nation':
				$fields = array(
					array(
						'title' => __( 'The country', 'vca-asm' ),
						'fields' => array(
							array(
								'type' => 'text',
								'label' => __( 'Name of country', 'vca-asm' ),
								'id' => 'name',
								'desc' => __( 'The name the country', 'vca-asm' )
							),
							array(
								'type' => 'hidden',
								'id' => 'type',
								'value' => 'nation'
							),
							array(
								'type' => 'text',
								'label' => __( 'Phone Country Code', 'vca-asm' ),
								'id' => 'phone_code',
								'desc' => __( 'The phone extension of the country (without &quot;+&quot; or &quot;00&quot;)', 'vca-asm' )
							),
							array(
								'type' => 'text',
								'label' => __( '2-letter Country Code', 'vca-asm' ),
								'id' => 'alpha_code',
								'desc' => __( 'The 2-letter country code (as defined by <a target="_blank" title="Read the standard" href="http://en.wikipedia.org/wiki/ISO_3166-1_alpha-2">ISO 3166-1-alpha-2</a>)', 'vca-asm' )
							),
							array(
								'type' => 'text',
								'label' => __( 'Currency (Name)', 'vca-asm' ),
								'id' => 'currency_name',
								'desc' => __( 'The name of the local currency', 'vca-asm' )
							),
							array(
								'type' => 'text',
								'label' => __( 'Currency (Code)', 'vca-asm' ),
								'id' => 'currency_code',
								'desc' => __( 'The 3-letter code of the local currency (as defined by <a target="_blank" title="Read the standard" href="http://en.wikipedia.org/wiki/ISO_4217">ISO 4217</a>)', 'vca-asm' )
							),
							array(
								'type' => 'text',
								'label' => __( 'Currency (Minor Unit, Name)', 'vca-asm' ),
								'id' => 'currency_minor_name',
								'desc' => __( 'The name of the minor unit of the local currency', 'vca-asm' )
							)
						)
					),
					array(
						'title' => __( 'Cities', 'vca-asm' ),
						'fields' => array(
							$children_cb_group
						)
					),
					array(
						'title' => __( 'Parent Groups', 'vca-asm' ),
						'fields' => array(
							array(
								'type' => 'groups',
								'label' => __( 'Groups', 'vca-asm' ),
								'id' => 'groups',
								'desc' => __( 'The group(s) this country is part of', 'vca-asm' )
							)
						)
					)
				);
			break;

			case 'cg':
				$fields = array(
					array(
						'title' => __( 'The group', 'vca-asm' ),
						'fields' => array(
							array(
								'type' => 'text',
								'label' => __( 'Name of city group', 'vca-asm' ),
								'id' => 'name',
								'desc' => __( 'The name or title of the group of cities / region', 'vca-asm' )
							),
							array(
								'type' => 'hidden',
								'id' => 'type',
								'value' => 'cg'
							)
						)
					),
					array(
						'title' => __( 'Cities', 'vca-asm' ),
						'fields' => array(
							$children_cb_group
						)
					)
				);
			break;

			case 'city':
			default:
				$fields = array(
					array(
						'title' => __( 'The city', 'vca-asm' ),
						'fields' => array(
							array(
								'type' => 'text',
								'label' => __( 'Name of city', 'vca-asm' ),
								'id' => 'name',
								'desc' => __( 'The name or title of the city', 'vca-asm' )
							),
							array(
								'type' => 'select',
								'label' => __( 'Status', 'vca-asm' ),
								'id' => 'type',
								'options' => array(
									array(
										'label' => __( 'Crew', 'vca-asm' ),
										'value' => 'lc'
									),
									array(
										'label' => __( '(old-school) Cell', 'vca-asm' ),
										'value' => 'cell'
									),
									array(
										'label' => __( 'City only', 'vca-asm' ),
										'value' => 'city'
									)
								),
								'desc' => __( 'Select the type of the region - is it just a city or also a Crew?', 'vca-asm' )
							),
							array(
								'type' => 'text',
								'label' => __( 'City Code', 'vca-asm' ),
								'id' => 'alpha_code',
								'desc' => __( 'A 1- to 3-letter abbreviation for this city. Choose something intuitive, such as a car number plate of the region.', 'vca-asm' )
							)
						)
					)
				);

				if ( $current_user->has_cap( 'vca_asm_manage_network_global' ) ) {
					$fields[] = array(
						'title' => __( 'Parent Groups', 'vca-asm' ),
						'fields' => array(
							array(
								'type' => 'select',
								'label' => __( 'Country', 'vca-asm' ),
								'id' => 'parent_nation',
								'options' => $vca_asm_geography->options_array( array(
									'orderby' => 'name',
									'order' => 'ASC',
									'please_select' => true,
									'type' => 'nation'
								)),
								'desc' => __( 'The country this city is part of', 'vca-asm' ),
								'disabled' => ( ! $current_user->has_cap( 'vca_asm_manage_network_global' ) ),
								'value' => $vca_asm_geography->has_nation( get_user_meta( $current_user->ID, 'city', true ) )
							),
							array(
								'type' => 'groups',
								'label' => __( 'Groups', 'vca-asm' ),
								'id' => 'groups',
								'desc' => __( 'The group(s) this city is part of', 'vca-asm' )
							)
						)
					);
				} else {
					$fields[] = array(
						'title' => __( 'Parent Groups', 'vca-asm' ),
						'fields' => array(
							array(
								'type' => 'select',
								'label' => __( 'Country', 'vca-asm' ),
								'id' => 'parent_nation_disabled',
								'options' => $vca_asm_geography->options_array( array(
									'orderby' => 'name',
									'order' => 'ASC',
									'please_select' => true,
									'type' => 'nation'
								)),
								'desc' => __( 'The country this city is part of', 'vca-asm' ),
								'disabled' => ( ! $current_user->has_cap( 'vca_asm_manage_network_global' ) ),
								'value' => $vca_asm_geography->has_nation( get_user_meta( $current_user->ID, 'city', true ) )
							),
							array(
								'type' => 'hidden',
								'id' => 'parent_nation',
								'value' => $vca_asm_geography->has_nation( get_user_meta( $current_user->ID, 'city', true ) )
							),
							array(
								'type' => 'groups',
								'label' => __( 'Groups', 'vca-asm' ),
								'id' => 'groups',
								'desc' => __( 'The group(s) this city is part of', 'vca-asm' )
							)
						)
					);
				}

				$fields[] = array(
					'title' => __( 'Administrative User', 'vca-asm' ),
					'fields' => array(
						array(
							'type' => 'checkbox',
							'label' => __( 'Administrative User?', 'vca-asm' ),
							'id' => 'has_user',
							'desc' => __( 'If the city is supposed to have a dedicated administrative user, check this box', 'vca-asm' )
						),
						array(
							'type' => 'text',
							'label' => __( "Administrative User's Username", 'vca-asm' ),
							'id' => 'user',
							'desc' => __( "With the above box checked, this will the administrative user's username. Please only use alphanumeric characters. Avoid spaces and special characters.", 'vca-asm' )
						),
						array(
							'type' => 'text',
							'label' => __( "Administrative User's Password", 'vca-asm' ),
							'id' => 'pass',
							'desc' => __( "With the above box checked, this will be the administrative user's password.", 'vca-asm' )
						),
						array(
							'type' => 'text',
							'label' => __( "Administrative User's Email", 'vca-asm' ),
							'id' => 'email',
							'desc' => __( "With the above box checked, this will be the administrative user's email address.", 'vca-asm' )
						)
					)
				);
			break;
		}

		return $fields;
	}

	/**
	 * Populates region fields with values
	 *
	 * @since 1.0
	 * @access private
	 */
	private function populate_fields( $id ) {
		global $wpdb, $vca_asm_geography;
		$current_user = wp_get_current_user();
		
		$type = $vca_asm_geography->get_meta_type( $id );
		$fields = $this->create_fields( $type );

		/* fill fields with existing data */
		$name = '';
		$data = $wpdb->get_results(
			"SELECT * FROM " .
			$wpdb->prefix . "vca_asm_geography " .
			"WHERE id = " . $id . " LIMIT 1", ARRAY_A
		);

		$data = $data[0];
		$data['phone_code'] = 0 === $data['phone_code'] ? '' : $data['phone_code'];

		if( $data['has_user'] == 1 ) {
			$head_of_obj = get_userdata(  $data['user_id'] );
			if( $head_of_obj === false ) {
				$wpdb->update(
					$wpdb->prefix.'vca_asm_geography',
					array(
						'has_user' => 0,
						'user_id' => 0,
						'user' => '',
						'pass' => ''
					),
					array( 'id'=> $id ),
					array( '%d', '%d', '%s', '%s' ),
					array( '%d' )
				);
				$data['has_user'] = 0;
				$data['pass'] = '';
				$data['user'] = '';
				$has_head_of = false;
			} else {
				$has_head_of = true;
			}
		} else {
			$has_head_of = false;
		}
		if( 'city' !== $type ) {
			$children = $vca_asm_geography->get_descendants( $id, array( 'format' => 'array', 'data' => 'id' ) );
			$data['children'] = $children;
		}
		if( 'ng' !== $type ) {
			$data['groups'] = $vca_asm_geography->get_ancestors( $id, array( 'format' => 'array', 'data' => 'all', 'deep' => true ) );
			$ngs = array();
			$cgs = array();
			foreach ( $data['groups'] as $ancestor ) {
				if ( 'ng' === $ancestor['ancestor_type'] ) {
					$ngs[] = $ancestor;
				} elseif ( 'cg' === $ancestor['ancestor_type'] ) {
					$cgs[] = $ancestor;
				} elseif ( 'nation' === $ancestor['ancestor_type'] ) {
					$nation = $ancestor;
				}
			}
			$data['groups'] = array_merge( $cgs, $ngs );
			if ( isset( $nation ) && ! empty( $nation ) ) {
				$data['parent_nation'] = $nation['ancestor'];
				$data['parent_nation_disabled'] = $nation['ancestor'];
			} else {
				$data['parent_nation'] = 0;
				$data['parent_nation_disabled'] = 0;
			}
		}

		$bcount = count( $fields );
		for ( $i = 0; $i < $bcount; $i++ ) {
			$fcount = count( $fields[$i]['fields'] );
			for ( $j = 0; $j < $fcount; $j++ ) {
				if ( empty( $_POST['submitted'] ) ) {
					$name = $data['name'];
					$fields[$i]['fields'][$j]['value'] = $data[$fields[$i]['fields'][$j]['id']];
				} else {
					$fields[$i]['fields'][$j]['value'] = $_POST[$fields[$i]['fields'][$j]['id']];
				}

				if( 'children' === $fields[$i]['fields'][$j]['id'] && 'nation' === $type ) {
					$options = $vca_asm_geography->options_array( array(
						'type' => 'city',
						'not_has_nation' => true,
						'descendants_of' => $id
					));
					$fields[$i]['fields'][$j]['options'] = $options;
				}

				if( $has_head_of === true ) {
					switch( $fields[$i]['fields'][$j]['id'] ) {
						case 'has_user':
							$fields[$i]['fields'][$j]['desc'] = '<strong>' . __( 'Attention', 'vca-asm' ) . ':</strong> ' .
								__( 'This city is assigned a city user account. Unchecking this box and saving the city results in the permanent deletion of that user!', 'vca-asm' );
						break;

						case 'user':
							$fields[$i]['fields'][$j]['desc'] = __( 'The username (login) of the head of user. It cannot be changed (other than through deletion and creation of a new user).', 'vca-asm' );
							$fields[$i]['fields'][$j]['disabled'] = true;
						break;

						case 'pass':
							$fields[$i]['fields'][$j]['desc'] = __( 'This is the current password of the city-user. If you change this and save the region, it will be updated as well.', 'vca-asm' );
							$fields[$i]['fields'][$j]['disabled'] = false;
							if( ! empty( $fields[$i]['fields'][$j]['value'] ) ) {
								$fields[$i]['fields'][$j]['value'] = rtrim(mcrypt_decrypt(MCRYPT_RIJNDAEL_256, md5(REGION_KEY), base64_decode($fields[$i]['fields'][$j]['value']), MCRYPT_MODE_CBC, md5(md5(REGION_KEY))), "\0");
							} else {
								$fields[$i]['fields'][$j]['value'] = 'unbekannt...';
							}
	;
						break;

						case 'email':
							$fields[$i]['fields'][$j]['value'] = $head_of_obj->user_email;
							$fields[$i]['fields'][$j]['desc'] = __( 'This is the current email address assigned to the Head Of User.', 'vca-asm' );
							$fields[$i]['fields'][$j]['disabled'] = false;
						break;
					}
				}
			}
		}

		return array( $fields, $name );
	}

} // class

endif; // class exists

?>