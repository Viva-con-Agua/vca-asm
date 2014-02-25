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
	private function create_extra_profile_fields() {
		global $current_user;
		get_currentuserinfo();
		
		if( ( is_array( $current_user->roles ) && in_array( 'head_of', $current_user->roles ) ) || ( ! is_array( $current_user->roles ) && 'head_of' == $current_user->roles ) ) {
			$disable_field = true;
		} else {
			$disable_field = false;
		}
		
		list( $region_field, $membership_field ) = $this->region_options();
		
		$fields = array(
			array(
				'label' => _x( 'Mobile Phone', 'User Profile', 'vca-asm' ),
				'id' => 'mobile',
				'type' => 'text'
			),
			array(
				'label' => _x( 'About you', 'User Profile', 'vca-asm' ),
				'type' => 'section'
			),
			array(
				'label' => _x( 'City', 'User Profile', 'vca-asm' ),
				'id' => 'city',
				'type' => 'text',
				'disabled' => $disable_field
			),
			array(
				'label' => _x( 'Birthday', 'User Profile', 'vca-asm' ),
				'id' => 'birthday',
				'type' => 'date',
				'disabled' => $disable_field
			),
			array(
				'label' => _x( 'Gender', 'User Profile', 'vca-asm' ),
				'id' => 'gender',
				'type' => 'select',
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
			),
			array(
				'label' => _x( 'Avatar', 'User Profile', 'vca-asm' ),
				'type' => 'section',
				'admin_hide' => true
			),
			array(
				'type' => 'avatar',
				'id' => 'simple-local-avatar',
				'admin_hide' => true
			),
			array(
				'label' => _x( 'Region', 'User Profile', 'vca-asm' ),
				'type' => 'section'
			),
			$region_field,
			$membership_field,
			array(
				'label' => _x( 'Newsletter', 'User Profile', 'vca-asm' ),
				'type' => 'section'
			),
			array(
				'label' => _x( 'News Options', 'User Profile', 'vca-asm' ),
				'id' => 'mail_switch',
				'type' => 'select',
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
			)
		);
		return $fields;
	}

	/**
	 * Returns an array for the cell membership profile field
	 * 
	 * Content depends on supporter's membership status.
	 * A supporter may cancel his or her membership whenever he or she likes,
	 * he or she however cannot join a cell (or local crew) without approval.
	 *
	 * @global object vca_asm_regions
	 * @see class VCA_ASM_Regions in /includes/class-vca-asm-regions.php
	 *
	 * @since 1.0
	 * @access private
	 */
	private function region_options() {
		global $vca_asm_regions;
		
		if( is_admin() ) {
			$mem = get_user_meta( $user->ID, 'membership', true );
		} else {
			global $current_user;
			get_currentuserinfo();
			$mem = get_user_meta( $current_user->ID, 'membership', true );
			$user_region = get_user_meta( $current_user->ID, 'region', true );
		}
		
		switch( $mem ) {
			case '2':
			$region_field = array(
				'row-class' => 'region-selector',
				'label' => _x( 'Region', 'User Profile', 'vca-asm' ),
				'id' => 'region',
				'type' => 'select',
				'desc' => _x( "You currently are a confirmed member of this Cell or Local Crew. You can change your regional affiliation again only if you choose to cancel your membership. You will have to apply for membership of the new region's Cell or Local Crew again.", 'User Profile', 'vca-asm' ),
				'options' => $vca_asm_regions->select_options( _x( 'no specific region', 'Regions', 'vca-asm' ) ),
				'disabled' => true
			);
			if( ! is_admin() && isset( $current_user ) && ( ( is_array( $current_user->roles ) && in_array( 'head_of', $current_user->roles ) ) || ( ! is_array( $current_user->roles ) && 'head_of' == $current_user->roles ) ) ) {
				$disable_field = true;
			} else {
				$disable_field = false;
			}
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
			$region_field = array(
				'row-class' => 'region-selector',
				'label' => _x( 'Region', 'User Profile', 'vca-asm' ),
				'id' => 'region',
				'type' => 'select',
				'desc' => _x( 'You have applied for membership status in the selected region. You can change your regional affiliation again only if you choose to withdraw your membership application.', 'User Profile', 'vca-asm' ),
				'options' => $vca_asm_regions->select_options( _x( 'no specific region', 'Regions', 'vca-asm' ) ),
				'disabled' => true
			);
			$membership_field = array(
				'row-class' => 'membership-selector',
				'label' => _x( 'I am an active member of my region', 'User Profile', 'vca-asm' ),
				'id' => 'membership',
				'type' => 'membership',
				'desc' => _x( "You have applied for membership of this region's Cell or Local Crew. To withdraw your application, simply uncheck the box.", 'User Profile', 'vca-asm' )
			);
			break;
		
			case '0':
			default:
			if( isset( $user_region ) && $user_region !== '' ) {
				$select_options = $vca_asm_regions->select_options( _x( 'no specific region', 'Regions', 'vca-asm' ) );
			} else {
				$select_options = $vca_asm_regions->select_options( _x( 'no specific region', 'Regions', 'vca-asm' ), 'name', 'ASC', true );
			}
			$region_field = array(
				'row-class' => 'region-selector',
				'label' => _x( 'Region', 'User Profile', 'vca-asm' ),
				'id' => 'region',
				'type' => 'select',
				'desc' => _x( 'Choose your region, if applicable. Should you not be able to find yours, please send an email to <a title="Send Mail" href="mailto:Zellen@vivaconagua.org">Zellen@vivaconagua.org</a>', 'User Profile', 'vca-asm' ),
				'options' => $select_options
			);
			$membership_field = array(
				'row-class' => 'membership-selector',
				'label' => _x( 'I am an active member of my region', 'User Profile', 'vca-asm' ),
				'id' => 'membership',
				'type' => 'membership',
				'desc' => _x( '<strong>Important:</strong> If you are an active member of this Cell or Local Crew, set this checkmark to apply for member status.', 'User Profile', 'vca-asm' )
			);
			break;
		}
		return array( $region_field, $membership_field );
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
		global $vca_asm_regions, $vca_asm_mailer;
		
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
						if( ( is_array( $this_user->roles ) && in_array( 'head_of', $this_user->roles ) ) || ( ! is_array( $this_user->roles ) && 'head_of' == $this_user->roles ) ) {
							update_user_meta( $user_id, $field['id'], '2' );
						} else {
							$regions = $vca_asm_regions->get_ids();
							$region_name = $regions[ $_POST['region'] ];
							$old = get_user_meta( $user_id, $field['id'], true );
							if( isset( $_POST[ $field['id'] ] ) ) {
								if( ( ( is_array( $this_user->roles ) && ! in_array( 'supporter', $this_user->roles ) ) || ( ! is_array( $this_user->roles ) && 'supporter' != $this_user->roles ) ) && $old != '2' ) {
									update_user_meta( $user_id, $field['id'], '2' );
									$vca_asm_mailer->auto_response( $user_id, 'mem_accepted', $region_name );
								} elseif( empty( $old ) ) {
									update_user_meta( $user_id, $field['id'], '1' );
								}
							} elseif( ! isset( $_POST[ $field['id'] ] ) && $old != '0' ) {
								update_user_meta( $user_id, $field['id'], '0' );
								if( $old == '2' ) {
									$vca_asm_mailer->auto_response( $user_id, 'mem_cancelled', $region_name );
								}
							}
						}
					break;
					
					default:
						if( $field['disabled'] !== true ) {
							update_user_meta( $user_id, $field['id'], $_POST[$field['id']] );
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
		update_user_meta( $user_id, 'terms_and_conditions', 'agreed' );
		update_user_meta( $user_id, 'mail_switch', 'all' );
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
		add_action( 'register_post', array( &$this, 'verify_tc_acceptance' ), 1, 3 );
		add_action( 'user_register', array( &$this, 'save_on_registration' ), 100 );
		add_action( 'show_user_profile', array( &$this, 'user_extra_profile_fields' ) );
		add_action( 'edit_user_profile', array( &$this, 'admin_extra_profile_fields' ) );
		add_action( 'personal_options_update', array( &$this, 'save_extra_profile_fields' ) );
		add_action( 'edit_user_profile_update', array( &$this, 'save_extra_profile_fields' ) );
	}
}

endif; // class exists

?>
