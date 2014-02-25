<?php

/**
 * VCA_ASM_Admin class.
 * 
 * This class contains properties and methods to set up
 * the administration backend.
 *
 * @package VcA Activity & Supporter Management
 * @since 1.0
 */

/**
 * Menu Positions of custom post types:
 *
 * festival - 105
 */

if ( ! class_exists( 'VCA_ASM_Admin' ) ) :

class VCA_ASM_Admin {

	/**
	 * Displays admin menu
	 *
	 * @todo move counting functionality into separate methos in registrations class
	 *
	 * @global object vca_asm_regions
	 * @see class VCA_ASM_Regions in /includes/class-vca-asm-regions.php
	 *
	 * @since 1.0
	 * @access public
	 */
	public function display_admin_menu() {
		global $wpdb, $current_user, $vca_asm_regions, $vca_asm_registrations, $vca_asm_admin_applications, $vca_asm_admin_emails, $vca_asm_admin_supporters;
		get_currentuserinfo();
		
		if( $current_user->has_cap('vca_asm_promote_all_supporters') ) {
			$pending =
				$wpdb->get_var(
					$wpdb->prepare(
						"SELECT COUNT(*) FROM $wpdb->usermeta WHERE meta_key='membership' AND meta_value='1'" ) );
		} elseif( $current_user->has_cap('vca_asm_promote_supporters') ) {
			$all_pending =
				$wpdb->get_results(
					"SELECT user_id FROM $wpdb->usermeta WHERE meta_key='membership' AND meta_value='1'", ARRAY_A );
			$admin_region = get_user_meta( $current_user->ID, 'region', true );
			$pending = 0;
			foreach( $all_pending as $single ) {
				$supp_region = get_user_meta( $single['user_id'], 'region', true );
				if( $admin_region === $supp_region ) {
					$pending++;
				}
			}
		} else {
			$pending = 0;
		}
		$pending = number_format_i18n( $pending );
		
		if( $current_user->has_cap('vca_asm_manage_all_applications') ) {
			$app_count =
				$wpdb->get_var(
					$wpdb->prepare(
						"SELECT COUNT(*) FROM " .
						$wpdb->prefix . "vca_asm_applications WHERE state = 0" ) );
			$wait_count =
				$wpdb->get_var(
					$wpdb->prepare(
						"SELECT COUNT(*) FROM " .
						$wpdb->prefix . "vca_asm_applications WHERE state = 1" ) );
			$reg_count = 0;
			$all_regs =
				$wpdb->get_results(
					"SELECT supporter, activity FROM " .
					$wpdb->prefix . "vca_asm_registrations", ARRAY_A );
			foreach( $all_regs as $single ) {
				$start_date = intval( get_post_meta( $single['activity'], 'start_date', true ) ) + 82800;
				$current_time = time();
				if( $start_date > $current_time ) {
					$reg_count++;
				} else {
					$vca_asm_registrations->move_registration_to_old( intval( $single['activity'] ), intval( $single['supporter'] ) );
				}
			}
		} elseif( $current_user->has_cap('vca_asm_manage_applications') ) {
			$all_apps =
				$wpdb->get_results(
					"SELECT supporter, activity FROM " .
					$wpdb->prefix . "vca_asm_applications WHERE state = 0", ARRAY_A );
			$all_wait =
				$wpdb->get_results(
					"SELECT supporter, activity FROM " .
					$wpdb->prefix . "vca_asm_applications WHERE state = 1", ARRAY_A );
			$all_regs =
				$wpdb->get_results(
					"SELECT supporter, activity FROM " .
					$wpdb->prefix . "vca_asm_registrations", ARRAY_A );
			$admin_region = get_user_meta( $current_user->ID, 'region', true );
			
			$app_count = 0;
			$wait_count = 0;
			$reg_count = 0;
			foreach( $all_apps as $single ) {
				$supp_region = get_user_meta( $single['supporter'], 'region', true );
				$supp_mem_status = get_user_meta( $single['supporter'], 'membership', true );
				$slots_arr = get_post_meta( $single['activity'], 'slots', true );
				$post_region = get_post_meta( $single['activity'], 'region', true );
				$delegation = get_post_meta( $single['activity'], 'delegate', true );
				if( ( $admin_region === $supp_region && $supp_mem_status == 2 && ( is_array( $slots_arr ) && array_key_exists( $supp_region, $slots_arr ) ) ) || ( $delegation == 'delegate' && $post_region == $admin_region ) ) {
					$app_count++;
				}
			}
			foreach( $all_wait as $single ) {
				$supp_region = get_user_meta( $single['supporter'], 'region', true );
				$supp_mem_status = get_user_meta( $single['supporter'], 'membership', true );
				$slots_arr = get_post_meta( $single['activity'], 'slots', true );
				$post_region = get_post_meta( $single['activity'], 'region', true );
				$delegation = get_post_meta( $single['activity'], 'delegate', true );
				if( ( $admin_region === $supp_region && $supp_mem_status == 2 && ( is_array( $slots_arr ) && array_key_exists( $supp_region, $slots_arr ) ) ) || ( $delegation == 'delegate' && $post_region == $admin_region ) ) {
					$wait_count++;
				}
			}
			foreach( $all_regs as $single ) {
				$supp_region = get_user_meta( $single['supporter'], 'region', true );
				$supp_mem_status = get_user_meta( $single['supporter'], 'membership', true );
				$slots_arr = get_post_meta( $single['activity'], 'slots', true );
				$post_region = get_post_meta( $single['activity'], 'region', true );
				$delegation = get_post_meta( $single['activity'], 'delegate', true );
				if( ( $admin_region === $supp_region && $supp_mem_status == 2 && ( is_array( $slots_arr ) && array_key_exists( $supp_region, $slots_arr ) ) ) || ( $delegation == 'delegate' && $post_region == $admin_region ) ) {
					$start_date = intval( get_post_meta( $single['activity'], 'start_date', true ) ) + 82800;
					$current_time = time();
					if( $start_date > $current_time ) {
						$reg_count++;
					} else {
						$vca_asm_registrations->move_registration_to_old( intval( $single['activity'] ), intval( $single['supporter'] ) );
					}
				}
			}
		} else {
			$app_count = 0;
			$wait_count = 0;
			$reg_count = 0;
		}
		$app_count = number_format_i18n( $app_count );
		$wait_count = number_format_i18n( $wait_count );
		$reg_count = number_format_i18n( $reg_count );
		
		/* Home */
		add_menu_page(
			__( 'Home', 'vca-asm' ),
			__( 'Home', 'vca-asm' ),
			'read',
			'vca-asm-home',
			array( &$this, 'home' ),
			VCA_ASM_RELPATH . 'admin/home-icon.png',
			101
		);
		add_submenu_page(
			'vca-asm-home',
			'',
			'',
			'read',
			'vca-asm-home',
			array( &$this, 'home' )
		);
		
		/* Supporter Menu*/
		add_menu_page(
			__( 'Supporter', 'vca-asm' ),
			__( 'Supporter', 'vca-asm' ),
			'vca_asm_view_supporters',
			'vca-asm-supporters',
			array( &$vca_asm_admin_supporters, 'list_supporters' ),
			VCA_ASM_RELPATH . 'admin/supporters-icon.png',
			102
		);
		add_submenu_page(
			'vca-asm-supporters',
			__( 'All Supporters', 'vca-asm' ),
			__( 'All Supporters', 'vca-asm' ),
			'vca_asm_view_supporters',
			'vca-asm-supporters',
			array( &$vca_asm_admin_supporters, 'list_supporters' )
		);
		add_submenu_page(
			'vca-asm-supporters',
			sprintf( __( 'Membership Status (%s)', 'vca-asm' ), $pending),
			sprintf( __( 'Membership Status (%s)', 'vca-asm' ), $pending),
			'vca_asm_promote_supporters',
			'vca-asm-supporter-memberships',
			array( &$vca_asm_admin_supporters, 'promotions' )
		);
		
		/* Emails Menu*/
		add_menu_page(
			__( 'Emails', 'vca-asm' ),
			__( 'Emails', 'vca-asm' ),
			'vca_asm_send_emails',
			'vca-asm-emails',
			array( &$vca_asm_admin_emails, 'mail_form' ),
			VCA_ASM_RELPATH . 'admin/emails-icon.png',
			103
		);
		add_submenu_page(
			'vca-asm-emails',
			__( 'Send an E-Mail', 'vca-asm' ),
			__( 'Send Mail', 'vca-asm' ),
			'vca_asm_send_emails',
			'vca-asm-emails',
			array( &$vca_asm_admin_emails, 'mail_form' )
		);
		add_submenu_page(
			'vca-asm-emails',
			__( 'Auto Responses', 'vca-asm' ),
			__( 'Auto Responses', 'vca-asm' ),
			'vca_asm_edit_autoresponses',
			'vca-asm-emails-autoresponses',
			array( &$vca_asm_admin_emails, 'autoresponses_edit' )
		);
		
		/* Regions Menu */
		add_menu_page(
			__( 'Regions', 'vca-asm' ),
			__( 'Regions', 'vca-asm' ),
			'vca_asm_edit_regions',
			'vca-asm-regions',
			array( &$vca_asm_regions, 'regions_control' ),
			VCA_ASM_RELPATH . 'admin/regions-icon.png',
			104
		);
		add_submenu_page(
			'vca-asm-regions',
			__( 'All Regions', 'vca-asm' ),
			__( 'All Regions', 'vca-asm' ),
			'vca_asm_edit_regions',
			'vca-asm-regions',
			array( &$vca_asm_regions, 'regions_control' )
		);
		add_submenu_page(
			'vca-asm-regions',
			__( 'Add New Region', 'vca-asm' ),
			__( 'Add new', 'vca-asm' ),
			'vca_asm_edit_regions',
			'vca-asm-regions-new',
			array( &$vca_asm_regions, 'regions_edit' )
		);
		
		/* Applications Menu */
		add_menu_page(
			sprintf( __( 'Applications (%s)', 'vca-asm' ), $app_count),
			sprintf( __( 'Applications (%s)', 'vca-asm' ), $app_count),
			'vca_asm_manage_applications',
			'vca-asm-applications',
			array( &$vca_asm_admin_applications, 'applications_control' ),
			VCA_ASM_RELPATH . 'admin/applications-icon.png',
			106
		);
		add_submenu_page(
			'vca-asm-applications',
			sprintf( __( 'Applications (%s)', 'vca-asm' ), $app_count),
			sprintf( __( 'Applications (%s)', 'vca-asm' ), $app_count),
			'vca_asm_manage_applications',
			'vca-asm-applications',
			array( &$vca_asm_admin_applications, 'applications_control' )
		);
		add_submenu_page(
			'vca-asm-applications',
			sprintf( __( 'Waiting List (%s)', 'vca-asm' ), $wait_count),
			sprintf( __( 'Waiting List (%s)', 'vca-asm' ), $wait_count),
			'vca_asm_manage_applications',
			'vca-asm-waiting-list',
			array( &$vca_asm_admin_applications, 'waiting_control' )
		);
		add_submenu_page(
			'vca-asm-applications',
			sprintf( __( 'Accepted Applications (%s)', 'vca-asm' ), $reg_count),
			sprintf( __( 'Accepted Applications (%s)', 'vca-asm' ), $reg_count),
			'vca_asm_manage_applications',
			'vca-asm-registrations',
			array( &$vca_asm_admin_applications, 'registrations_control' )
		);
	}
	
	/**
	 * Admin Home Page
	 *
	 * @since 1.0
	 * @access public
	 */
	public function home() {
		global $wpdb;
		
		$user_count = count_users();
		$region_count =  $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COUNT(id) FROM " .
				$wpdb->prefix . "vca_asm_regions"
			)
		);
		
		$output = '<div class="wrap">' .
				'<h2>Viva con Agua Supporter Pool</h2>' .
				'<div id="welcome-panel" class="welcome-panel">' .
				'<p>' . __( 'Regions', 'vca-asm' ) . ': ' . $region_count . '</p>' .
				'<p>' . __( 'Supporter', 'vca-asm' ) . ': ' . $user_count['avail_roles']['supporter'] . '</p>' .
				'<p><a class="button" title="' . __( 'Back to the frontend', 'vca-asm' ) . '" href="' . get_bloginfo('url') . '">' .
				__( 'Back to the front!', 'vca-asm' ) . '</a></p>' .
				'<p><a class="button-primary" title="' . __( 'Log me out', 'vca-asm' ) .
					'" href="' . wp_logout_url( get_bloginfo('url') ) . '">' . __( 'Logout', 'vca-asm' ) . '</a></p>' .
				'</div></div>'; 
		
		echo $output;
	}
	
	/**
	 * PHP4 style constructor
	 *
	 * @since 1.0
	 * @access public
	 */
	public function VCA_ASM_Admin() {
		$this->__construct();
	}

	/**
	 * PHP5 style constructor
	 *
	 * @since 1.0
	 * @access public
	 */
	public function __construct() {
		add_action( 'admin_menu', array( &$this, 'display_admin_menu' ) );
	}
	
} // class

endif; // class exists

?>