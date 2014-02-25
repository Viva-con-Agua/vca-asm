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
		global $wpdb, $current_user, $vca_asm_regions, $vca_asm_registrations, $vca_asm_admin_activities, $vca_asm_admin_applications, $vca_asm_admin_emails, $vca_asm_admin_finances, $vca_asm_admin_settings, $vca_asm_admin_supporters;

		/* Home */
		add_menu_page(
			__( 'Home', 'vca-asm' ),
			__( 'Home', 'vca-asm' ),
			'read',
			'vca-asm-home',
			array( &$this, 'home' ),
			VCA_ASM_RELPATH . 'admin/images/icon-home_32.png',
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
			array( &$vca_asm_admin_supporters, 'supporters_control' ),
			VCA_ASM_RELPATH . 'admin/images/icon-supporters_32.png',
			102
		);
		add_submenu_page(
			'vca-asm-supporters',
			'',
			'',
			'vca_asm_view_supporters',
			'vca-asm-supporters',
			array( &$vca_asm_admin_supporters, 'supporters_control' )
		);

		/* Regions Menu */
		add_menu_page(
			__( 'Regions', 'vca-asm' ),
			__( 'Regions', 'vca-asm' ),
			'vca_asm_edit_regions',
			'vca-asm-regions',
			array( &$vca_asm_regions, 'regions_control' ),
			VCA_ASM_RELPATH . 'admin/images/icon-regions_32.png',
			103
		);
		add_submenu_page(
			'vca-asm-regions',
			'',
			'',
			'vca_asm_edit_regions',
			'vca-asm-regions',
			array( &$vca_asm_regions, 'regions_control' )
		);

		/* Emails Menu */
		add_menu_page(
			__( 'Emails', 'vca-asm' ),
			__( 'Emails', 'vca-asm' ),
			'vca_asm_send_emails',
			'vca-asm-compose',
			array( &$vca_asm_admin_emails, 'compose_control' ),
			VCA_ASM_RELPATH . 'admin/images/icon-mail_32.png',
			104
		);
		add_submenu_page(
			'vca-asm-compose',
			__( 'Compose', 'vca-asm' ),
			__( 'Compose', 'vca-asm' ),
			'vca_asm_send_emails',
			'vca-asm-compose',
			array( &$vca_asm_admin_emails, 'compose_control' )
		);
		add_submenu_page(
			'vca-asm-compose',
			__( 'Sent Items', 'vca-asm' ),
			__( 'Sent Items', 'vca-asm' ),
			'vca_asm_send_emails',
			'vca-asm-emails',
			array( &$vca_asm_admin_emails, 'sent_control' )
		);

		///* Blog Menu */
		//add_menu_page(
		//	__( 'Blog', 'vca-asm' ),
		//	__( 'Blog', 'vca-asm' ),
		//	'edit_post',
		//	'edit.php',
		//	'',
		//	VCA_ASM_RELPATH . 'admin/images/icon-write_32.png',
		//	105
		//);

		/* Activities Menu */
		add_menu_page(
			__( 'Activities', 'vca-asm' ),
			__( 'Activities', 'vca-asm' ),
			'vca_asm_edit_activities',
			'vca-asm-activities',
			array( &$vca_asm_admin_activities, 'control' ),
			VCA_ASM_RELPATH . 'admin/images/icon-activities_32.png',
			106
		);
		add_submenu_page(
			'vca-asm-activities',
			'',
			'',
			'vca_asm_edit_activities',
			'vca-asm-activities',
			array( &$vca_asm_admin_activities, 'control' )
		);
		add_submenu_page(
			'vca-asm-activities',
			__( 'Slot Allocation', 'vca-asm' ),
			__( 'Slot Allocation', 'vca-asm' ),
			'vca_asm_manage_applications',
			'vca-asm-slot-allocation',
			array( &$vca_asm_admin_applications, 'slot_allocation_control' )
		);
		add_submenu_page(
			'vca-asm-activities',
			'Bildungsworkshops',
			'Bildungsworkshops',
			'vca_asm_send_global_emails',
			'vca-asm-activities-education',
			array( &$this, 'education_control' )
		);

		/* Finances Menu */
		add_menu_page(
			__( 'Finances', 'vca-asm' ),
			__( 'Finances', 'vca-asm' ),
			'vca_asm_edit_finances',
			'vca-asm-finances',
			array( &$vca_asm_admin_finances, 'control' ),
			VCA_ASM_RELPATH . 'admin/images/icon-finances_32.png',
			107
		);
		add_submenu_page(
			'vca-asm-finances',
			'',
			'',
			'vca_asm_edit_finances',
			'vca-asm-finances',
			array( &$vca_asm_admin_finances, 'control' )
		);

		/* Settings Menu */
		add_menu_page(
			__( 'Settings', 'vca-asm' ),
			__( 'Settings', 'vca-asm' ),
			'vca_asm_manage_options',
			'vca-asm-settings',
			array( &$vca_asm_admin_settings, 'control' ),
			VCA_ASM_RELPATH . 'admin/images/icon-settings_32.png',
			108
		);
		add_submenu_page(
			'vca-asm-settings',
			'',
			'',
			'vca_asm_manage_options',
			'vca-asm-settings',
			array( &$vca_asm_admin_settings, 'control' )
		);
	}

	/**
	 * Admin Home Page
	 *
	 * @since 1.0
	 * @access public
	 */
	public function home() {
		global $current_user, $wpdb, $vca_asm_regions;
		get_currentuserinfo();

		$stats = new VCA_ASM_Stats();
		$admin_region = get_user_meta( $current_user->ID, 'region', true );
		$admin_region_name = $vca_asm_regions->get_name( $admin_region );
		$admin_region_status = $vca_asm_regions->get_status( $admin_region );

		if( in_array( 'region', $current_user->roles ) ) {
			$headline = 'Viva con Agua | ' . $admin_region_status . ' ' . $admin_region_name;
		} else {
			$headline = 'Viva con Agua | Supporter Pool';
		}

		$output = '<div class="wrap">' .
			'<div id="icon-home" class="icon32-pa"></div><h2>' . $headline . '</h2><br />' .
				'<p><a class="button" title="' . __( '&larr; Back to the frontend', 'vca-asm' ) . '" href="' . get_bloginfo('url') . '">' .
				__( '&larr; Back to the frontend', 'vca-asm' ) . '</a>' . '&nbsp;&nbsp;&nbsp;' .
					'<a class="button-primary" title="' . __( 'Log me out', 'vca-asm' ) .
					'" href="' . wp_logout_url( get_bloginfo('url') ) . '">' . __( 'Logout', 'vca-asm' ) . '</a>' .
				'</p>';

		if( in_array( 'content_admin', $current_user->roles ) || in_array( 'administrator', $current_user->roles ) ) {
			$output .= '<h3 class="title title-top-pa">Entwicklungsfortschritt, Pool v1.2</h3>' .
				'<p>' .
					'aktuell aufgespielte Version: 1.2-rc1 (26.11.2012)<br />' .
					'<a title="aktueller Stand" href="http://pool.vivaconagua.org/fortschritt/">' .
						'Entwicklungsfortschritt einsehen' .
					'</a>' .
					'<br />(Head-Of-Benutzer und Abteilungsassistenzen sehen diese Nachricht nicht)' .
				'</p>';
		}

		$output .= '<h3 class="title title-top-pa">' . __( 'Supporters', 'vca-asm' ) . '</h3>' .
				'<p>' .
					sprintf( _x( '%1$s registered supporters, %2$s of which are from your %3$s', 'Statistics', 'vca-asm' ),
						'<strong>' . $stats->supporters_total_total . '</strong>',
						'<strong>' . $stats->supporters_total_region . '</strong>',
						$admin_region_status
					) . '<br />' .
					sprintf( _x( '&quot;Active Members&quot;: %1$s (your %3$s: %2$s)', 'Statistics', 'vca-asm' ),
						'<strong>' . $stats->supporters_active_total . '</strong>',
						'<strong>' . $stats->supporters_active_region . '</strong>',
						$admin_region_status
					) . '<br />' .
					sprintf( _x( 'Current pending membership applications: %1$s (your %3$s: %2$s)', 'Statistics', 'vca-asm' ),
						'<strong>' . $stats->supporters_applied_total . '</strong>',
						'<strong>' . $stats->supporters_applied_region . '</strong>',
						$admin_region_status
					) . '<br />' .
					sprintf( _x( 'The remaining %1$s (your %3$s: %2$s) have not applied for active membership.', 'Statistics', 'vca-asm' ),
						'<strong>' . $stats->supporters_inactive_total . '</strong>',
						'<strong>' . $stats->supporters_inactive_region . '</strong>',
						$admin_region_status
					) . '<br />' .
					sprintf( _x( '%1$s of those (your %3$s: %2$s) only have very incomplete (not even a name submitted) profiles.', 'Statistics', 'vca-asm' ),
						'<strong>' . $stats->supporters_incomplete_total . '</strong>',
						'<strong>' . $stats->supporters_incomplete_region . '</strong>',
						$admin_region_status
					) .
				'</p>' .
				'<h3 class="title title-top-pa">' . __( 'Regions', 'vca-asm' ) . '</h3>' .
				'<p>' .
					sprintf( _x( '%s Regions', 'Statistics', 'vca-asm' ),
							'<strong>' . $stats->regions_total . '</strong>' ) . '<br />' .
					sprintf( _x( '%1$s of those are Cells', 'Statistics', 'vca-asm' ),
							'<strong>' . $stats->regions_cells . '</strong>' ) . '<br />' .
					sprintf( _x( '%1$s of those are Local Crews', 'Statistics', 'vca-asm' ),
							'<strong>' . $stats->regions_crews . '</strong>' ) .
				'</p>' .
				'<h3 class="title title-top-pa">' . __( 'Activities', 'vca-asm' ) . '</h3>' .
				'<p>' .
					sprintf( _x( '%1$s Festivals in total, of which %2$s are in the future (applications for %3$s of those are still open)', 'Statistics', 'vca-asm' ),
						'<strong>' . $stats->activities_festivals_total . '</strong>',
						'<strong>' . $stats->activities_festivals_upcoming . '</strong>',
						'<strong>' . $stats->activities_festivals_appphase . '</strong>'
					);
				//if( 0 < $stats->activities_festivals_current ) {
				//	$output .= sprintf( _x( ', %1$s lie in the past and %2$s are currently happening!', 'Statistics', 'vca-asm' ),
				//		'<strong>' . $stats->activities_festivals_past . '</strong>',
				//		'<strong>' . $stats->activities_festivals_current . '</strong>'
				//	);
				//} else {
				//	$output .= sprintf( _x( ' and %s lie in the past.', 'Statistics', 'vca-asm' ),
				//		'<strong>' . $stats->activities_festivals_past . '</strong>'
				//	);
				//}
				$output .= '.</p>';



		//if( 1 === $current_user->ID ) {
		//
		//		$oldregs = $wpdb->get_results(
		//			"SELECT * FROM " . $wpdb->prefix."vca_asm_registrations_old_2", ARRAY_A
		//		);
		//
		//		$supps = array();
		//		foreach( $oldregs as $onereg ) {
		//			if( ! in_array( $onereg['supporter'], $supps ) ) {
		//				$end_date = intval( get_post_meta( $onereg['activity'], 'end_date', true ) );
		//				$thetitle = get_the_title( $onereg['activity'] );
		//				if( 1349049600 > $end_date && 'konz' !== strtolower(substr($thetitle,0,4)) ) {
		//					$supps[] = $onereg['supporter'];
		//				}
		//			}
		//		}
		//
		//		foreach( $supps as $supp ) {
		//			$userobj = new WP_User( $supp );
		//			$output .= $userobj->user_email . '<br />';
		//		}
		//}


			$output .= '</div>';

		echo $output;
	}

	/**
	 * Temporary Pseudo Menus
	 *
	 * @since 1.2
	 * @access public
	 */
	public function education_control() {

		$output = '<div class="wrap">' .
			'<div id="icon-education" class="icon32-pa"></div><h2>Bildungsworkshops</h2><br /><br />' .
				'<p><dfn>Verfügbar ab Version 1.3</dfn></p>' .
			'</div>';

		echo $output;
	}
	public function network_meeting_control() {
		global $current_user;

		$output = '<div class="wrap">' .
			'<div id="icon-network" class="icon32-pa"></div><h2>Netzwerktreffen</h2><br /><br />' .
				'<p><dfn>Verfügbar ab Version 1.3</dfn></p>' .
				'<p><em>Das aktuelle Netzwerktreffen ist noch als Pseudo-Festival eingepflegt.<br />';

		if( $current_user->has_cap( 'vca_asm_edit_others_activities' ) ) {
			$output .= '<a title="Netzwerktreffen editieren" href="' . get_site_option('url') . '/wp-admin/post.php?post=318&action=edit">Das aktuelle Netzwerktreffen bearbeiten</a><br />';
		}
		$output .= '<a title="Bewerbungen bearbeiten" href="' . get_site_option('url') . '/wp-admin/post.php?post=318&action=edit">Platzvergabe</a></em></p></div>';

		echo $output;
	}
	public function display_temp_menu() {
		add_submenu_page(
			'vca-asm-activities',
			'Netzwerkreffen',
			'Netzwerkreffen',
			'vca_asm_edit_activities',
			'vca-asm-activities-network-meeting',
			array( &$this, 'network_meeting_control' )
		);
	}

	/**
	 * Converts message arrays into html output
	 *
	 * @since 1.0
	 * @access public
	 */
	public function convert_messages( $messages = array() ) {
		$output = '';

		foreach( $messages as $message ) {
			$output .= '<div class="' . $message['type'] . '"><p>' .
					$message['message'] .
				'</p></div>';
		}

		return $output;
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
		add_action( 'admin_menu', array( &$this, 'display_admin_menu' ), 9 );
		add_action( 'admin_menu', array( &$this, 'display_temp_menu' ), 11 );
	}

} // class

endif; // class exists

?>