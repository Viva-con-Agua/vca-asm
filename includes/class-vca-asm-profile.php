<?php

/**
 * VCA_ASM_Profile class.
 * This class contains properties and methods for additional user profile fields.
 *
 * @package VcA Activity & Supporter Management
 * @since 1.0
 */

if ( ! class_exists( 'VCA_ASM_Profile' ) ) :

class VCA_ASM_Profile {

	/**
	 * Returns an array containing new profile fields
	 *
	 * @since 1.0
	 * @access private
	 */
	private function create_extra_profile_fields( $part = false ) {
		global $current_user;

		if ( in_array( 'city', $current_user->roles ) ) {
			$is_city = true;
			$disable_field = true;
		} else {
			$is_city = false;
			$disable_field = false;
		}

		list( $nation_field, $city_field, $membership_field ) = $this->geo_options();

		if ( $is_city ) {
			$fields = array();
		} else {
			$fields = array(
				array(
					'label' => _x( 'Mobile Phone', 'User Profile', 'vca-asm' ),
					'id' => 'mobile',
					'type' => 'tel'
				),
				array(
					'label' => _x( 'About you', 'User Profile', 'vca-asm' ),
					'type' => 'section'
				),
				array(
					'label' => _x( 'Residence', 'User Profile', 'vca-asm' ),
					'id' => 'residence',
					'type' => 'text',
					'disabled' => $disable_field
				),
				array(
					'label' => _x( 'Birthday', 'User Profile', 'vca-asm' ),
					'id' => 'birthday',
					'type' => 'date',
					'row-class' => 'multi-selects',
					'disabled' => $disable_field
				),
				array(
					'label' => _x( 'Gender', 'User Profile', 'vca-asm' ),
					'id' => 'gender',
					'type' => 'select',
					'disabled' => $disable_field,
					'options' => array(
						array(
							'label' => __( 'female', 'vca-asm' ),
							'value' => 'female'
						),
						array(
							'label' => __( 'male', 'vca-asm' ),
							'value' => 'male'
						)
					)
				)
			);
		}

		$fields[] =	array(
			'label' => _x( 'Avatar', 'User Profile', 'vca-asm' ),
			'type' => 'section',
			'admin_hide' => true
		);
		$fields[] =	array(
			'type' => 'avatar',
			'id' => 'simple-local-avatar',
			'admin_hide' => true
		);

		if ( ! $is_city ) {
			$fields[] =	array(
				'label' => _x( 'Geography', 'User Profile', 'vca-asm' ),
				'type' => 'section'
			);
			$fields[] =	$nation_field;
			$fields[] =	$city_field;
			$fields[] =	$membership_field;
			$fields[] =	array(
				'label' => _x( 'Newsletter', 'User Profile', 'vca-asm' ),
				'type' => 'section'
			);
			$fields[] =	array(
				'label' => _x( 'News Options', 'User Profile', 'vca-asm' ),
				'id' => 'mail_switch',
				'type' => 'select',
				'disabled' => $disable_field,
				'options' => array(
					array(
						'label' => __( 'Global &amp; regional news', 'vca-asm' ),
						'value' => 'all'
					),
					array(
						'label' => __( 'Only global news', 'vca-asm' ),
						'value' => 'global'
					),
					array(
						'label' => __( 'Only regional news', 'vca-asm' ),
						'value' => 'regional'
					),
					array(
						'label' => __( 'None', 'vca-asm' ),
						'value' => 'none'
					)
				),
				'desc' => __( 'Choose in what case to receive emails. News from your region, global news, both or none.', 'vca-asm' )
			);
		}
		$fields[] =	array(
			'label' => 'Language',
			'type' => 'section'
		);
		$fields[] =	array(
			'label' => 'Preferred Language of the Pool',
			'id' => 'pool_lang',
			'type' => 'select',
			'options' => array(
				array(
					'label' => 'Deutsch',
					'value' => 'de'
				),
				array(
					'label' => 'English',
					'value' => 'en'
				)
			),
			'desc' => 'Choose in what language you&apos;d like to use the Pool'
		);

		if ( 'custom' === $part && ! $is_city ) {
			$fields = array_slice( $fields, 0, 11 );
		} elseif ( 'custom' === $part ) {
			$fields = array_slice( $fields, 0, 2 );
		} elseif ( 'settings' === $part && ! $is_city ) {
			$fields = array_slice( $fields, 11 );
		} elseif ( 'settings' === $part ) {
			$fields = array_slice( $fields, 2 );
		}
		return $fields;
	}

	/**
	 * Returns an array for the cell membership profile field
	 *
	 * Content depends on supporter's membership status.
	 * A supporter may cancel his or her membership whenever he or she likes,
	 * he or she however cannot join a cell (or local crew) without approval.
	 *
	 * @global object vca_asm_geography
	 * @global object vca_asm_utilities
	 *
	 * @since 1.0
	 * @access private
	 */
	private function geo_options() {
		global $vca_asm_geography, $vca_asm_utilities;

		$disable_field = false;
		if( is_admin() ) {
			global $user_id;
			$edited_user = new WP_User( $user_id );
			$mem = get_user_meta( $edited_user->ID, 'membership', true );
			$user_city = get_user_meta( $edited_user->ID, 'city', true );
			$user_nation = get_user_meta( $edited_user->ID, 'nation', true );
		} else {
			global $current_user;
			$mem = get_user_meta( $current_user->ID, 'membership', true );
			$user_city = get_user_meta( $current_user->ID, 'city', true );
			$user_nation = get_user_meta( $current_user->ID, 'nation', true );
			if ( ( is_array( $current_user->roles ) && ( in_array( 'head_of', $current_user->roles ) ) || in_array( 'city', $current_user->roles ) ) ) {
				$disable_field = true;
			}
		}

		$geo_desc = _x( 'Choose your city (Cell or Local Crew), if applicable. Should you not be able to find yours, please send an email to <a title="Send Mail" href="mailto:zellen@vivaconagua.org">zellen@vivaconagua.org</a>', 'User Profile', 'vca-asm' );

		if ( 'ch' === $vca_asm_utilities->current_country() ) {
			$geo_desc = _x( 'Choose your city (Cell or Local Crew), if applicable. Should you not be able to find yours, please send an email to <a title="Send Mail" href="mailto:zellen@vivaconagua.ch">zellen@vivaconagua.ch</a>', 'User Profile', 'vca-asm' );
		}

		switch( $mem ) {
			case '2':
				$nation_field = array(
					'row-class' => 'nation-selector',
					'label' => _x( 'Country', 'User Profile', 'vca-asm' ),
					'id' => 'nation',
					'type' => 'select',
					'options' => $vca_asm_geography->options_array( array(
						'global_option_last' => __( 'other, non-listed country', 'vca-asm' ),
						'please_select' => true,
						'please_select_value' => NULL,
						'type' => 'nation'
					)),
					'desc' => _x( "You currently are a confirmed member of this Cell or Local Crew. You can change your regional affiliation again only if you choose to cancel your membership. You will have to apply for membership of the new region's Cell or Local Crew again.", 'User Profile', 'vca-asm' ),
					'disabled' => true
				);
				$city_field = array(
					'row-class' => 'city-selector',
					'label' => _x( 'City', 'User Profile', 'vca-asm' ),
					'id' => 'city',
					'type' => 'select',
					'options' => $vca_asm_geography->options_array( array(
						'global_option' => __( 'not chosen...', 'vca-asm' ),
						'please_select' => false,
						'type' => 'city',
						'grouped' => false,
						'descendants_of' => ( isset( $user_nation ) && ! empty( $user_nation ) && is_numeric( $user_nation ) ) ?
							$user_nation : 40
					)),
					'disabled' => true
				);
				$membership_field = array(
					'row-class' => 'membership-selector',
					'label' => _x( 'I am an active member of my region', 'User Profile', 'vca-asm' ),
					'id' => 'membership',
					'type' => 'membership',
					'desc' => _x( 'Uncheck to cancel membership', 'User Profile', 'vca-asm' ),
					'disabled' => $disable_field
				);
			break;

			case '1':
				$nation_field = array(
					'row-class' => 'nation-selector',
					'label' => _x( 'Country', 'User Profile', 'vca-asm' ),
					'id' => 'nation',
					'type' => 'select',
					'options' => $vca_asm_geography->options_array( array(
						'global_option_last' => __( 'other, non-listed country', 'vca-asm' ),
						'please_select' => true,
						'please_select_value' => NULL,
						'type' => 'nation'
					)),
					'desc' => _x( 'You have applied for membership status in the selected region. You can change your regional affiliation again only if you choose to withdraw your membership application.', 'User Profile', 'vca-asm' ),
					'disabled' => true
				);
				$city_field = array(
					'row-class' => 'city-selector',
					'label' => _x( 'City', 'User Profile', 'vca-asm' ),
					'id' => 'city',
					'type' => 'select',
					'options' => $vca_asm_geography->options_array( array(
						'global_option' => __( 'not chosen...', 'vca-asm' ),
						'please_select' => false,
						'type' => 'city',
						'grouped' => false,
						'descendants_of' => ( isset( $user_nation ) && ! empty( $user_nation ) && is_numeric( $user_nation ) ) ?
							$user_nation : 40
					)),
					'disabled' => true
				);
				$membership_field = array(
					'row-class' => 'membership-selector',
					'label' => _x( 'I am an active member of my region', 'User Profile', 'vca-asm' ),
					'id' => 'membership',
					'type' => 'membership',
					'desc' => _x( "You have applied for membership of this region's Cell or Local Crew. To withdraw your application, simply uncheck the box.", 'User Profile', 'vca-asm' ),
					'disabled' => $disable_field
				);
			break;

			case '0':
			default:
				$nation_field = array(
					'row-class' => 'nation-selector',
					'label' => _x( 'Country', 'User Profile', 'vca-asm' ),
					'id' => 'nation',
					'type' => 'select',
					'options' => $vca_asm_geography->options_array( array(
						'global_option_last' => __( 'other, non-listed country', 'vca-asm' ),
						'please_select' => true,
						'please_select_value' => NULL,
						'type' => 'nation'
					)),
					'desc' => $geo_desc,
					'disabled' => $disable_field
				);
				$city_field = array(
					'row-class' => 'city-selector',
					'label' => _x( 'City', 'User Profile', 'vca-asm' ),
					'id' => 'city',
					'type' => 'select',
					'options' => $vca_asm_geography->options_array( array(
						'global_option' => __( 'not chosen...', 'vca-asm' ),
						'please_select' => false,
						'type' => 'city',
						'grouped' => false,
						'descendants_of' => ( isset( $user_nation ) && ! empty( $user_nation ) && is_numeric( $user_nation ) ) ?
							$user_nation : 40
					)),
					'disabled' => $disable_field
				);
				$membership_field = array(
					'row-class' => 'membership-selector',
					'label' => _x( 'I am an active member of my region', 'User Profile', 'vca-asm' ),
					'id' => 'membership',
					'type' => 'membership',
					'desc' => _x( '<strong>Important:</strong> If you are an active member of this Cell or Local Crew, set this checkmark to apply for member status.', 'User Profile', 'vca-asm' ),
					'disabled' => $disable_field
				);
			break;
		}
		return array( $nation_field, $city_field, $membership_field );
	}

	/**
	 * Localizes JS
	 *
	 * @since 1.3
	 * @access public
	 */
	public function set_script_params( $user ) {
		global $vca_asm_geography;

		if ( is_admin() ) {
			wp_localize_script( 'vca-asm-admin-profile', 'nationalHierarchy', $vca_asm_geography->national_hierarchy );
		} else {
			wp_localize_script( 'vca-asm-profile', 'nationalHierarchy', $vca_asm_geography->national_hierarchy );
		}
	}

	/**
	 * Adds to user's profile view
	 *
	 * @since 1.0
	 * @access public
	 */
	public function user_extra_profile_fields( $user ) {
		$fields = $this->create_extra_profile_fields();
		require_once( VCA_ASM_ABSPATH . '/templates/frontend-profile.php' );
	}
	public function user_extra_profile_fields_custom( $user ) {
		$fields = $this->create_extra_profile_fields( 'custom' );
		require( VCA_ASM_ABSPATH . '/templates/frontend-profile.php' );
	}
	public function user_extra_profile_fields_settings( $user ) {
		$fields = $this->create_extra_profile_fields( 'settings' );
		require( VCA_ASM_ABSPATH . '/templates/frontend-profile.php' );
	}

	/**
	 * Adds to admin's userprofile view
	 *
	 * @since 1.0
	 * @access public
	 */
	public function admin_extra_profile_fields( $user ) {
		$fields = $this->create_extra_profile_fields();
		require_once( VCA_ASM_ABSPATH . '/templates/admin-profile.php' );
	}

	public function save_extra_profile_fields( $user_id ) {
		global $vca_asm_geography, $vca_asm_mailer;

		if ( ! current_user_can( 'edit_user', $user_id ) ) {
			return false;
		}

		if ( isset( $_POST['deleteme'] ) && $_POST['deleteme'] == 'forever' ) {
			wp_delete_user( $user_id );
			wp_redirect( get_bloginfo('url'), 200 );
			exit;
			return false;
		}

		$fields = $this->create_extra_profile_fields();
		if ( ! empty( $fields ) ) {
			foreach ( $fields as $field ) {
				switch( $field['type'] ) {
					case 'date':
						update_user_meta(
							$user_id,
							$field['id'],
							mktime( 0, 0, 0,
								$_POST[ $field['id'] . '-month' ],
								$_POST[ $field['id'] . '-day' ],
								$_POST[ $field['id'] . '-year' ]
							)
						);
					break;

					case 'membership':
						$this_user = new WP_User( $user_id );
						if( in_array( 'city', $this_user->roles ) ) {
							update_user_meta( $user_id, $field['id'], '2' );
						} else {
							$regions = $vca_asm_geography->get_ids();
							$city_id = get_user_meta( $user_id, 'city', true );
							$geo_name = isset( $_POST['city'] ) ? $regions[$_POST['city']] : $regions[$city_id];
							$old = get_user_meta( $user_id, $field['id'], true );
							if( isset( $_POST[ $field['id'] ] ) ) {
								if( ( ( is_array( $this_user->roles ) && ! in_array( 'supporter', $this_user->roles ) ) || ( ! is_array( $this_user->roles ) && 'supporter' != $this_user->roles ) ) && $old != '2' ) {
									update_user_meta( $user_id, $field['id'], '2' );
									$vca_asm_mailer->auto_response(
										$user_id,
										'mem_accepted',
										array(
											'city' => $geo_name,
											'city_id' => $city_id
										)
									);
								} elseif( empty( $old ) ) {
									update_user_meta( $user_id, $field['id'], '1' );
								}
							} elseif( ! isset( $_POST[ $field['id'] ] ) && ( $old !== '0' || $old !== 0 ) ) {
								update_user_meta( $user_id, $field['id'], '0' );
								if( $old == '2' ) {
									$vca_asm_mailer->auto_response(
										$user_id,
										'mem_cancelled',
										array(
											'city' => $geo_name,
											'city_id' => $city_id
										)
									);
								}
							}
						}
					break;

					default:
						if( isset( $field['id'] ) && ( ! isset( $field['disabled'] ) || $field['disabled'] !== true ) ) {
							$new = isset( $_POST[$field['id']] ) ? $_POST[$field['id']] : '';
							update_user_meta( $user_id, $field['id'], $new );
							if ( 'city' === $field['id'] ) {
								update_user_meta( $user_id, 'region', $new );
							} else if ( 'region' === $field['id'] ) {
								update_user_meta( $user_id, 'city', $new );
							}
						}
					break;
				}
			}
		}
	}

	/**
	 * Verifies whether a new user has accepted the terms & conditions (upon registration)
	 * otherwise stops execution of registration process
	 *
	 * @since 1.0
	 * @access public
	 */
	public function verify_tc_acceptance( $sanitized_user_login, $user_email, $errors ) {
		if( ! isset( $_POST['terms_conditions'] ) || $_POST['terms_conditions'] != 'agreed' ) {
			$errors->add( 'tc_not_accepted', __( 'Please confirm that you agree to the terms &amp; conditions.', 'vca-asm' ) );
		}
	}

	/**
	 * Saves the acceptance of the terms & conditions to the database
	 *
	 * @since 1.0
	 * @access public
	 */
	public function save_on_registration( $user_id ) {
		update_user_meta( $user_id, 'mail_switch', 'all' );
		update_user_meta( $user_id, 'membership', 0 );
		update_user_meta( $user_id, 'nation', NULL );
		update_user_meta( $user_id, 'region', 0 );
		update_user_meta( $user_id, 'city', 0 );
		update_user_meta( $user_id, 'terms_and_conditions', 'agreed' );
	}

	/**
	 * Shortcode handler for the supporter vCard
	 *
	 * @since 1.2
	 * @access public
	 */
	public function supporter_vcard( $atts ) {
		global $current_user, $vca_asm_utilities;

		$supporter = new VCA_ASM_Supporter( $current_user->ID );

		$output = '<div class="island vcard" style="overflow:hidden;">' .
				'<table class="meta-table">' .

					'<tr>' .
						'<td><h3>' . $supporter->nice_name . '</h3></td>' .
						'<td class="avatar-cell" rowspan="2">' . $supporter->avatar . '</td>' .
					'</tr>' .

					'<tr><td><table class="meta-table">' .

						'<tr>' .
							'<td><p class="label">' . _x( 'Country', 'Admin Supporters', 'vca-asm' ) . '</p>' .
							'<p class="metadata">' . $supporter->nation . '</p></td>' .

							'<td><p class="label">' . _x( 'Registered since', 'Admin Supporters', 'vca-asm' ) . '</p>' .
							'<p class="metadata">' . $supporter->registration_date . '</p></td>' .
						'</tr>' .
						'<tr>' .
							'<td><p class="label">' . _x( 'City', 'Admin Supporters', 'vca-asm' ) . '</p>' .
							'<p class="metadata">' . $supporter->city . '</p></td>' .

							'<td><p class="label">' . _x( 'Last Login', 'Admin Supporters', 'vca-asm' ) . '</p>' .
							'<p class="metadata">' . $supporter->last_activity . '</p></td>' .
						'</tr>' .
						'<tr>' .
							'<td><p class="label">' . _x( 'Membership', 'Admin Supporters', 'vca-asm' ) . '</p>' .
							'<p class="metadata">' . $vca_asm_utilities->convert_strings( $supporter->membership ) . '</p></td>' .

							'<td></td>' .
						'</tr>' .

					'</table></td></tr>' .

				'</table>' .

				'<p style="text-align:right;font-size:1.2em;line-height:1.75;margin:-1.75em 0 0;">' .
					'<a href="' . get_bloginfo( 'url' ) . '/profil/" title="' .
						_x( 'Edit your Profile &amp; Settings', 'Admin Supporters', 'vca-asm' ) . '">'.
							'&uarr; ' . _x( 'Edit Profile', 'Admin Supporters', 'vca-asm' ) .
				'</a></p>' .

			'</div>';

		return $output;
	}

	/**
	 * PHP4 style constructor
	 *
	 * @since 1.0
	 * @access public
	 */
	public function VCA_ASM_Profile() {
		$this->__construct();
	}

	/**
	 * PHP5 style constructor
	 *
	 * @since 1.0
	 * @access public
	 */
	public function __construct() {
		add_action( 'register_post', array( $this, 'verify_tc_acceptance' ), 1, 3 );
		add_action( 'user_register', array( $this, 'save_on_registration' ), 100 );
		add_action( 'show_user_profile', array( $this, 'user_extra_profile_fields' ) );
		add_action( 'edit_user_profile', array( $this, 'admin_extra_profile_fields' ) );
		add_action( 'vca_theme_show_user_profile', array( $this, 'user_extra_profile_fields_custom' ) );
		add_action( 'wp_enqueue_scripts', array( $this, 'set_script_params' ), 20 );
		add_action( 'admin_enqueue_scripts', array( $this, 'set_script_params' ), 20 );
		add_action( 'vca_theme_show_user_settings', array( $this, 'user_extra_profile_fields_settings' ) );
		add_action( 'personal_options_update', array( $this, 'save_extra_profile_fields' ) );
		add_action( 'edit_user_profile_update', array( $this, 'save_extra_profile_fields' ) );
		add_shortcode( 'vca-asm-supporter-vcard', array( $this, 'supporter_vcard' ) );
	}
}

endif; // class exists

?>