<?php

/**
 * VCA_ASM_Regions class.
 *
 * This class contains properties and methods for
 * the addition, editing and deletion of regions.
 *
 * Further it provides a method that returns
 * an array of regions for use in other classes.
 *
 * @package VcA Activity & Supporter Management
 * @since 1.0
 */

if ( ! class_exists( 'VCA_ASM_Regions' ) ) :

class VCA_ASM_Regions {

	/**
	 * Returns the name of a region if fed its ID
	 *
	 * @since 1.0
	 * @access public
	 */
	public function get_name( $id ) {
		global $wpdb;

		$region_query = $wpdb->get_results(
				"SELECT name FROM " .
				$wpdb->prefix . "vca_asm_regions " .
				"WHERE id =" . $id, ARRAY_A
		);
		$region = $region_query[0]['name'];
		if( empty( $region ) ) {
			$region = __( 'no region', 'vca-asm' );
		}

		return $region;
	}

	/**
	 * Returns an array of raw region data
	 *
	 * @since 1.0
	 * @access public
	 */
	public function get_all( $orderby = 'name', $order = 'ASC' ) {
		global $wpdb;

		$regions = $wpdb->get_results(
				"SELECT * FROM " .
				$wpdb->prefix . "vca_asm_regions " .
				"ORDER BY " .
				$orderby . " " . $order, ARRAY_A
		);

		return $regions;
	}

	/**
	 * Converts status string to proper name
	 *
	 * @since 1.0
	 * @access private
	 */
	private function convert_stati( $status ) {
		switch( $status ) {
			case 'cell':
				return __( 'Cell', 'vca-asm' );
			break;
			case 'lc':
				return __( 'Local Crew', 'vca-asm' );
			break;
			default:
			case 'region':
				return __( 'region', 'vca-asm' );
			break;
		}
	}

	/**
	 * Returns an array of regions with id as key and name as value
	 *
	 * @since 1.0
	 * @access public
	 */
	public function get_ids() {

		$raw = $this->get_all();
		$regions = array();

		foreach( $raw as $region ) {
			$regions[$region['id']] = $region['name'];
		}
		$regions['0'] = _x( 'no specific region', 'Regions', 'vca-asm' );

		return $regions;
	}

	/**
	 * Returns the status of a region by ID
	 *
	 * @since 1.0
	 * @access public
	 */
	public function get_status( $id ) {
		global $wpdb;

		$status_query = $wpdb->get_results(
			"SELECT status FROM " .
			$wpdb->prefix . "vca_asm_regions " .
			"WHERE id =" . $id, ARRAY_A
		);
		$status = $status_query[0]['status'];
		if( empty( $status ) ) {
			$status = __( 'no region', 'vca-asm' );
		}

		return $this->convert_stati( $status );
	}

	/**
	 * Returns an array of regions with id as key and human readable status as value
	 *
	 * @since 1.0
	 * @access public
	 */
	public function get_stati() {

		$raw = $this->get_all();
		$regions = array();

		foreach( $raw as $region ) {
			$regions[$region['id']] = $region['status'];
		}

		return $regions;
	}

	/**
	 * Returns an array of regions with id as key and human readable status as value
	 *
	 * @since 1.0
	 * @access public
	 */
	public function get_stati_conv() {

		$raw = $this->get_all();
		$regions = array();

		foreach( $raw as $region ) {
			$regions[$region['id']] = $this->convert_stati( $region['status'] );
		}

		return $regions;
	}

	/**
	 * Updates the supporter and member count in the regions table
	 *
	 * @todo mem_count in single SQL query
	 *
	 * @since 1.0
	 * @access public
	 */
	public function update_member_count() {
		global $wpdb;

		$raw = $this->get_all();

		foreach( $raw as $region ) {
			$supporters = $wpdb->get_results(
					"SELECT user_id FROM " .
					$wpdb->prefix . "usermeta " .
					"WHERE meta_key = 'region' AND meta_value = " .
					$region['id'], ARRAY_A
			);
			$supp_count = count($supporters);
			$mem_count = 0;
			foreach( $supporters as $supporter ) {
				$mem_status = get_user_meta( $supporter['user_id'], 'membership', true );
				if( $mem_status == 2 ) {
					$mem_count++;
				}
			}

			$wpdb->update(
				$wpdb->prefix.'vca_asm_regions',
				array(
					'supporters' => $supp_count,
					'members' => $mem_count
				),
				array( 'id'=> $region['id'] ),
				array( '%d', '%d' ),
				array( '%d' )
			);
		}
	}

	/**
	 * Returns an array of region data to be used in a dropdown menu
	 *
	 * @since 1.0
	 * @access public
	 */
	public function select_options( $global_option = '', $orderby = 'name', $order = 'ASC', $please_select = false ) {

		$raw = $this->get_all( $orderby, $order );
		$regions = array();
		if( $please_select === true ) {
			$regions[0] = array(
				'label' => __( 'Please select...', 'vca-asm' ),
				'value' => 'please_select', // js alert if selected on save, @see frontend-profile template
				'class' => 'please-select'
			);
		}

		if( ! empty( $global_option ) ) {
			$regions[] = array(
				'label' => $global_option,
				'value' => 0,
				'class' => 'global'
			);
		}

		foreach( $raw as $region ) {
			$regions[] = array(
				'label' => $region['name'],
				'value' => $region['id'],
				'class' => $region['status']
			);
		}

		return $regions;
	}

	/**
	 * Returns an array of fields for a region
	 *
	 * @since 1.0
	 * @access private
	 */
	private function create_fields() {
		$fields = array(
			array(
				'type' => 'text',
				'label' => __( 'Name of region', 'vca-asm' ),
				'id' => 'name',
				'desc' => __( 'The name of title of the region', 'vca-asm' )
			),
			array(
				'type' => 'select',
				'label' => __( 'Status', 'vca-asm' ),
				'id' => 'status',
				'options' => array(
					array(
						'label' => __( 'Region', 'vca-asm' ),
						'value' => 'region'
					),
					array(
						'label' => __( 'Local Crew', 'vca-asm' ),
						'value' => 'lc'
					),
					array(
						'label' => __( 'Cell', 'vca-asm' ),
						'value' => 'cell'
					)
				),
				'desc' => __( 'Select the type of the region - is it a Cell or Local Crew or simply a geographical region?', 'vca-asm' )
			),
			array(
				'type' => 'checkbox',
				'label' => __( 'Head Of User?', 'vca-asm' ),
				'id' => 'has_user',
				'desc' => __( 'If the region is supposed to have a dedicated Head Of user, check this box', 'vca-asm' )
			),
			array(
				'type' => 'text',
				'label' => __( "Head Of Username", 'vca-asm' ),
				'id' => 'user',
				'desc' => __( "With the above box checked, this will the Head Of user's username. Please only use alphanumeric characters. Avoid spaces and special characters.", 'vca-asm' )
			),
			array(
				'type' => 'text',
				'label' => __( "Head Of's Password", 'vca-asm' ),
				'id' => 'pass',
				'desc' => __( "With the above box checked, this will be the Head Of user's password.", 'vca-asm' )
			),
			array(
				'type' => 'text',
				'label' => __( "Head Of's Email", 'vca-asm' ),
				'id' => 'email',
				'desc' => __( "With the above box checked, this will be the Head Of user's email address.", 'vca-asm' )
			)
		);
		return $fields;
	}

	/**
	 * Region administration menu
	 *
	 * @since 1.0
	 * @access public
	 */
	public function regions_control() {
		global $wpdb;

		if ($_GET['id']) {
			$region_user_query = $wpdb->get_results(
				"SELECT has_user, user_id, pass, user FROM " .
				$wpdb->prefix . "vca_asm_regions " .
				"WHERE id = " . $_GET['id'] . " LIMIT 1", ARRAY_A
			);
			$region_user = $region_user_query[0];
		}

		switch ($_GET['todo']) {

			case "delete":
				if ($_GET['id']) {
					$wpdb->query(
						"DELETE FROM " .
						$wpdb->prefix . "vca_asm_regions " .
						"WHERE id='" . $_GET['id'] . "' LIMIT 1"
					);
					if( $region_user['has_user'] == 1 ) {
						wp_delete_user( $region_user['user_id'] );
					}
					echo '<div class="updated"><p><strong>' .
					__( 'The selected region has been successfully deleted.', 'vca-asm' ) .
					'</strong></p></div>';
				}
				unset($_GET['todo'], $_GET['id']);
				$this->regions_list();
			break;

			case "save":
				if( isset( $_POST['has_user'] ) ) {
					$has_user = 1;
					if( ( ! isset( $_POST['user'] ) || ! isset( $_POST['pass'] ) || ! isset( $_POST['email'] ) ) && $region_user['has_user'] != 1 ) {
						echo '<div class="error"><p><strong>' .
							__( 'If this region is supposed to have a Head-Of User, please set its username, password and email.', 'vca-asm' ) .
							'</strong></p></div>';
						$this->regions_edit( $_GET['id'] );
						return;
					} elseif( ( ! isset( $_POST['pass'] ) || ! isset( $_POST['email'] ) ) && $region_user['has_user'] != 1 ) {
						echo '<div class="error"><p><strong>' .
							__( 'Please do not leave the password or email field blank as long as the region has a user assigned.', 'vca-asm' ) .
							'</strong></p></div>';
						$this->regions_edit( $_GET['id'] );
						return;
					} elseif( $region_user['has_user'] == 1 ) {
						if( ! empty( $region_user['pass'] ) ) {
							$old_pass = rtrim(mcrypt_decrypt(MCRYPT_RIJNDAEL_256, md5(REGION_KEY), base64_decode($region_user['pass']), MCRYPT_MODE_CBC, md5(md5(REGION_KEY))), "\0");
							if( $old_pass != $_POST['pass'] ) {
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
					} elseif( $region_user['has_user'] != 1 ) {
						$region_user_id = wp_create_user( $_POST['user'], $_POST['pass'], $_POST['email'] );
						if( ! is_int( $region_user_id ) ) {
							echo '<div class="error"><p><strong>' .
								__( 'Either the username is empty, already exists or the email is already in use.', 'vca-asm' ) .
								'</strong></p></div>';
							$this->regions_edit( $_GET['id'] );
							return;
						}
						$user_obj = new WP_User( $region_user_id );
						$user_obj->remove_role( 'supporter' );
						$user_obj->add_role( 'head_of' );
						update_user_meta( $region_user_id, 'membership', '2' );
						update_user_meta( $region_user_id, 'first_name', 'Head Of' );
						update_user_meta( $region_user_id, 'last_name', $_POST['name'] );
						update_user_meta( $region_user_id, 'city', $_POST['name'] );
						update_user_meta( $region_user_id, 'birthday', '1159444800' );
						$new_pass = base64_encode( mcrypt_encrypt( MCRYPT_RIJNDAEL_256, md5(REGION_KEY), $_POST['pass'], MCRYPT_MODE_CBC, md5(md5(REGION_KEY)) ) );
						$new_user = $_POST['user'];
					}
				} elseif( ! isset( $_POST['has_user'] ) && $region_user['has_user'] == 1 ) {
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
				if( isset( $_GET['id'] ) && $_GET['id'] != NULL ) {
					$wpdb->update(
						$wpdb->prefix.'vca_asm_regions',
						array(
							'name' => $_POST['name'],
							'status' => $_POST['status'],
							'has_user' => $has_user,
							'user_id' => $region_user_id,
							'user' => $new_user,
							'pass' => $new_pass
						),
						array( 'id'=> $_GET['id'] ),
						array( '%s', '%s', '%d', '%d', '%s', '%s' ),
						array( '%d' )
					);
					$region_id = $_GET['id'];
					echo '<div class="updated"><p><strong>' .
						__( 'Region successfully updated!', 'vca-asm' ) .
						'</strong></p></div>';
				} else {
					$wpdb->insert(
						$wpdb->prefix.'vca_asm_regions',
						array(
							'name' => $_POST['name'],
							'status' => $_POST['status'],
							'has_user' => $has_user,
							'user_id' => $region_user_id,
							'user' => $_POST['user'],
							'pass' => base64_encode( mcrypt_encrypt( MCRYPT_RIJNDAEL_256, md5(REGION_KEY), $_POST['pass'], MCRYPT_MODE_CBC, md5(md5(REGION_KEY)) ) ),
							'supporters' => 0,
							'members' => 0),
						array( '%s', '%s', '%d', '%d', '%s', '%s', '%d', '%d' )
					);
					$region_id = $wpdb->insert_id;
					echo '<div class="updated"><p><strong>' .
						__( 'Region successfully added!', 'vca-asm' ) .
						'</strong></p></div>';
				}
				/* Set Head Of's region ID */
				if( $has_user == 1 ) {
					update_user_meta( $region_user_id, 'region', $region_id );
				}

				$this->regions_list();
			break;

			case "edit":
				$this->regions_edit( $_GET['id'] );
			break;

			case "new":
				$this->regions_edit();
			break;

			default:
				$this->regions_list();
		}
	}

	/**
	 * List all regions
	 *
	 * @since 1.0
	 * @access private
	 */
	private function regions_list() {

		$this->update_member_count();

		$url = "admin.php?page=vca-asm-regions";

		if( isset( $_GET['orderby'] ) ) {
			$orderby = $_GET['orderby'];
		} else {
			$orderby = 'name';
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
		$rows = $this->get_all( $orderby, $order );

		$columns = array(
			array(
				'id' => 'name',
				'title' => __( 'Region', 'vca-asm' ),
				'sortable' => true,
				'strong' => true,
				'editable' => true
			),
			array(
				'id' => 'status',
				'title' => __( 'Status', 'vca-asm' ),
				'sortable' => true,
				'conversion' => 'region-status'
			),
			array(
				'id' => 'supporters',
				'title' => __( 'Supporters', 'vca-asm' ),
				'sortable' => true
			),
			array(
				'id' => 'members',
				'title' => __( 'Members', 'vca-asm' ),
				'sortable' => true
			)
		);

		$icon = '<div id="icon-regions" class="icon32-pa"></div>';
		$headline = __( 'Regions: Cells &amp; Local Crews', 'vca-asm' ) .
			' <a href="admin.php?page=vca-asm-regions&amp;todo=new" class="add-new-h2">' .
				__( 'Add New', 'vca-asm' ) .
			'</a>';

		require( VCA_ASM_ABSPATH . '/templates/admin-table.php' );
	}

	/**
	 * Populates region fields with values
	 *
	 * @since 1.0
	 * @access private
	 */
	private function populate_fields( $id ) {
		global $wpdb;

		$fields = $this->create_fields();

		/* fill fields with existing data */
		$fcount = count($fields);
		$name = '';
		$data = $wpdb->get_results(
			"SELECT * FROM " .
			$wpdb->prefix . "vca_asm_regions " .
			"WHERE id = " . $id . " LIMIT 1", ARRAY_A
		);
		$data = $data[0];
		if( $data['has_user'] == 1 ) {
			$head_of_obj = get_userdata(  $data['user_id'] );
			if( $head_of_obj === false ) {
				$wpdb->update(
					$wpdb->prefix.'vca_asm_regions',
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

		for ( $i = 0; $i < $fcount; $i++ ) {
			if ( empty( $_POST['submitted'] ) ) {
				$name = $data['name'];
				$fields[$i]['value'] = $data[$fields[$i]['id']];
			} else {
				$fields[$i]['value'] = $_POST[$fields[$i]['id']];
			}

			if( $has_head_of === true ) {
				switch( $fields[$i]['id'] ) {
					case 'has_user':
						$fields[$i]['desc'] = '<strong>' . __( 'Attention', 'vca-asm' ) . ':</strong> ' .
							__( 'This region is assigned a head of user. Unchecking this box and saving the region results in the permanent deletion of the user!', 'vca-asm' );
					break;

					case 'user':
						$fields[$i]['desc'] = __( 'The username (login) of the head of user. It cannot be changed (other than through deletion and creation of a new user).', 'vca-asm' );
						$fields[$i]['disabled'] = true;
					break;

					case 'pass':
						$fields[$i]['desc'] = __( 'This is the current password of the region-user. If you change this and save the region, it will be updated as well.', 'vca-asm' );
						$fields[$i]['disabled'] = false;
						if( ! empty( $fields[$i]['value'] ) ) {
							$fields[$i]['value'] = rtrim(mcrypt_decrypt(MCRYPT_RIJNDAEL_256, md5(REGION_KEY), base64_decode($fields[$i]['value']), MCRYPT_MODE_CBC, md5(md5(REGION_KEY))), "\0");
						} else {
							$fields[$i]['value'] = 'unbekannt...';
						}
;
					break;

					case 'email':
						$fields[$i]['value'] = $head_of_obj->user_email;
						$fields[$i]['desc'] = __( 'This is the current email address assigned to the Head Of User.', 'vca-asm' );
						$fields[$i]['disabled'] = false;
					break;
				}
			}
		}

		return array( $fields, $name );
	}

	/**
	 * Edit a region
	 *
	 * @since 1.0
	 * @access public
	 */
	public function regions_edit( $id = NULL ) {

		$url = "admin.php?page=vca-asm-regions";
		$form_action = $url . "&amp;todo=save&amp;id=" . $id;

		if( $id == NULL ) {
			$fields = $this->create_fields();
			$title = __( 'Add New Region', 'vca-asm' );
		} else {
			list( $fields, $name ) = $this->populate_fields( $id );
			$title = sprintf( __( 'Edit "%s"', 'vca-asm' ), $name );
		}

		$output = '<div class="wrap">' .
				'<div id="icon-regions" class="icon32-pa"></div>' .
				'<h2>' . $title . '</h2><br />' .
				'<form name="vca_asm_region_edit_form" method="post" action="' . $form_action . '">' .
					'<input type="hidden" name="submitted" value="y"/>' .
					'<input type="hidden" name="edit_val" value="' . $id . '"/>' ;
						require( VCA_ASM_ABSPATH . '/templates/admin-form.php' );
				$output .= '<p class="submit">' .
					'<input type="submit" name="submit" id="submit" class="button-primary" value="' .
					__( 'Save Region', 'vca-asm' ) .
					'"></p></form>' .
			'</div>';

		echo $output;
	}

} // class

endif; // class exists

?>