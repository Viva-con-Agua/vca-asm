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

if ( ! class_exists( 'VCA_ASM_Admin' ) ) :

class VCA_ASM_Admin {

	/**
	 * Displays admin menu
	 * (Menus added here are added *before* CPT-(sub-)menus)
	 *
	 * @since 1.0
	 * @access public
	 */
	public function high_priority_admin_menu() {
		global $vca_asm_admin_actions, $vca_asm_admin_emails, $vca_asm_admin_education, $vca_asm_admin_finances, $vca_asm_admin_geography, $vca_asm_admin_goldeimer, $vca_asm_admin_home, $vca_asm_admin_network, $vca_asm_admin_settings, $vca_asm_admin_supporters;

		/* Home */
		add_menu_page(
			__( 'Home', 'vca-asm' ),
			__( 'Home', 'vca-asm' ),
			'read',
			'vca-asm-home',
			array( $vca_asm_admin_home, 'home' ),
			VCA_ASM_RELPATH . 'img/icon-home_32.png',
			101
		);
		add_submenu_page(
			'vca-asm-home',
			'',
			'',
			'read',
			'vca-asm-home',
			array( $vca_asm_admin_home, 'home' )
		);

		/* Supporter / User */
		add_menu_page(
			__( 'Supporters', 'vca-asm' ),
			__( 'Supporters', 'vca-asm' ),
			'vca_asm_view_supporters',
			'vca-asm-supporters',
			array( $vca_asm_admin_supporters, 'control' ),
			VCA_ASM_RELPATH . 'img/icon-supporters_32.png',
			102
		);

		/* Actions Menu */
		add_menu_page(
			__( 'Actions', 'vca-asm' ),
			__( 'Actions', 'vca-asm' ),
			'vca_asm_view_actions',
			'vca-asm-actions',
			array( $vca_asm_admin_actions, 'actions_overview' ),
			VCA_ASM_RELPATH . 'img/icon-actions_32.png',
			103
		);
		add_submenu_page(
			'vca-asm-actions',
			'',
			'',
			'vca_asm_view_actions',
			'vca-asm-actions',
			array( $vca_asm_admin_actions, 'actions_overview' )
		);

		/* + low prio submenus */

		/* Education Menu */
		add_menu_page(
			__( 'Education', 'vca-asm' ),
			__( 'Education', 'vca-asm' ),
			'vca_asm_view_education',
			'vca-asm-education',
			array( $vca_asm_admin_education, 'education_overview' ),
			VCA_ASM_RELPATH . 'img/icon-education_32.png',
			104
		);
		add_submenu_page(
			'vca-asm-education',
			'',
			'',
			'vca_asm_view_education',
			'vca-asm-education',
			array( $vca_asm_admin_education, 'education_overview' )
		);
		add_submenu_page(
			'vca-asm-education',
			'Knowledge Tour',
			'Knowledge Tour',
			'vca_asm_view_education',
			'vca-asm-education-tour',
			array( $vca_asm_admin_education, 'pseudo_tour' )
		);

		/* Network Menu */
		add_menu_page(
			__( 'Network', 'vca-asm' ),
			__( 'Network', 'vca-asm' ),
			'vca_asm_view_network',
			'vca-asm-network',
			array( $vca_asm_admin_network, 'network_overview' ),
			VCA_ASM_RELPATH . 'img/icon-network_32.png',
			105
		);
		add_submenu_page(
			'vca-asm-network',
			'',
			'',
			'vca_asm_view_network',
			'vca-asm-network',
			array( $vca_asm_admin_network, 'network_overview' )
		);
		add_submenu_page(
			'vca-asm-network',
			__( 'Geography', 'vca-asm' ),
			__( 'Geography', 'vca-asm' ),
			'vca_asm_view_network',
			'vca-asm-geography',
			array( $vca_asm_admin_geography, 'control' )
		);

		/* Network Menu */
		add_menu_page(
			__( 'Goldeimer', 'vca-asm' ),
			__( 'Goldeimer', 'vca-asm' ),
			'vca_asm_view_goldeimer',
			'vca-asm-goldeimer',
			array( $vca_asm_admin_goldeimer, 'goldeimer_overview' ),
			VCA_ASM_RELPATH . 'img/icon-goldeimer_32.png',
			106
		);
		add_submenu_page(
			'vca-asm-goldeimer',
			'',
			'',
			'vca_asm_view_goldeimer',
			'vca-asm-goldeimer',
			array( $vca_asm_admin_goldeimer, 'goldeimer_overview' )
		);

		/* Emails Menu */
		add_menu_page(
			__( 'Emails', 'vca-asm' ),
			__( 'Emails', 'vca-asm' ),
			'vca_asm_view_emails',
			'vca-asm-emails',
			array( $vca_asm_admin_emails, 'sent_control' ),
			VCA_ASM_RELPATH . 'img/icon-mail_32.png',
			107
		);
		add_submenu_page(
			'vca-asm-emails',
			__( '', 'vca-asm' ),
			__( '', 'vca-asm' ),
			'vca_asm_view_emails',
			'vca-asm-emails',
			array( $vca_asm_admin_emails, 'sent_control' )
		);
		add_submenu_page(
			'vca-asm-emails',
			__( 'Compose', 'vca-asm' ),
			__( 'Compose', 'vca-asm' ),
			'vca_asm_send_emails',
			'vca-asm-compose',
			array( $vca_asm_admin_emails, 'compose_control' )
		);
		add_submenu_page(
			'vca-asm-emails',
			__( 'Outbox', 'vca-asm' ),
			__( 'Outbox', 'vca-asm' ),
			'vca_asm_view_emails',
			'vca-asm-outbox',
			array( $vca_asm_admin_emails, 'outbox_control' )
		);
		add_submenu_page(
			'vca-asm-emails',
			__( 'Sent Items', 'vca-asm' ),
			__( 'Sent Items', 'vca-asm' ),
			'vca_asm_view_emails',
			'vca-asm-emails',
			array( $vca_asm_admin_emails, 'sent_control' )
		);

		/* Finances Menu */
		add_menu_page(
			__( 'Finances', 'vca-asm' ),
			__( 'Finances', 'vca-asm' ),
			'vca_asm_view_finances',
			'vca-asm-finances',
			array( $vca_asm_admin_finances, 'overview_control' ),
			VCA_ASM_RELPATH . 'img/icon-finances_32.png',
			108
		);
		add_submenu_page(
			'vca-asm-finances',
			__( 'Overview', 'vca-asm' ),
			__( 'Overview', 'vca-asm' ),
			'vca_asm_view_finances',
			'vca-asm-finances',
			array( $vca_asm_admin_finances, 'overview_control' )
		);
		add_submenu_page(
			'vca-asm-finances',
			__( 'Donations', 'vca-asm' ),
			__( 'Donations', 'vca-asm' ),
			'vca_asm_view_finances',
			'vca-asm-finances-accounts-donations',
			array( $vca_asm_admin_finances, 'accounts_control' )
		);
		add_submenu_page(
			'vca-asm-finances',
			__( 'Structural Funds', 'vca-asm' ),
			__( 'Structural Funds', 'vca-asm' ),
			'vca_asm_view_finances',
			'vca-asm-finances-accounts-econ',
			array( $vca_asm_admin_finances, 'accounts_control' )
		);
		add_submenu_page(
			'vca-asm-finances',
			__( 'Settings', 'vca-asm' ),
			__( 'Settings', 'vca-asm' ),
			'vca_asm_view_finances_nation',
			'vca-asm-finances-settings',
			array( $vca_asm_admin_finances, 'settings_control' )
		);

		///* Blog Menu */
		//add_menu_page(
		//	__( 'Blog', 'vca-asm' ),
		//	__( 'Blog', 'vca-asm' ),
		//	'edit_post',
		//	'edit.php',
		//	'',
		//	VCA_ASM_RELPATH . 'img/icon-write_32.png',
		//	108
		//);

		/* Settings Menu */
		add_menu_page(
			__( 'Settings', 'vca-asm' ),
			__( 'Settings', 'vca-asm' ),
			'vca_asm_view_options',
			'vca-asm-settings',
			array( $vca_asm_admin_settings, 'control' ),
			VCA_ASM_RELPATH . 'img/icon-settings_32.png',
			111
		);
		add_submenu_page(
			'vca-asm-settings',
			__( 'Settings', 'vca-asm' ),
			__( 'Pool', 'vca-asm' ),
			'vca_asm_view_options',
			'vca-asm-settings',
			array( $vca_asm_admin_settings, 'control' )
		);
		add_submenu_page(
			'vca-asm-settings',
			__( 'Maintenance Mode Settings', 'vca-asm' ),
			__( 'Maintenance Mode', 'vca-asm' ),
			'vca_asm_set_mode',
			'vca-asm-mode-settings',
			array( $vca_asm_admin_settings, 'mode_control' )
		);
		add_submenu_page(
			'vca-asm-settings',
			__( 'WP Users', 'vca-asm' ),
			__( 'WP Users', 'vca-asm' ),
			'vca_asm_wp_users_ui',
			'users.php'
		);
	}

	/**
	 * Adds to the admin menu
	 * (Menus added here are added *after* CPT-(sub-)menus)
	 *
	 * @since 1.3
	 * @access public
	 */
	public function low_priority_admin_menu() {
		global $vca_asm_admin_slot_allocation;

		add_submenu_page(
			'vca-asm-actions',
			__( 'Slots &amp; Participants', 'vca-asm' ),
			__( 'Slots &amp; Participants', 'vca-asm' ),
			'vca_asm_view_actions',
			'vca-asm-actions-slot-allocation',
			array( $vca_asm_admin_slot_allocation, 'control' )
		);
		add_submenu_page(
			'vca-asm-education',
			__( 'Slots &amp; Participants', 'vca-asm' ),
			__( 'Slots &amp; Participants', 'vca-asm' ),
			'vca_asm_view_education',
			'vca-asm-education-slot-allocation',
			array( $vca_asm_admin_slot_allocation, 'control' )
		);
		add_submenu_page(
			'vca-asm-network',
			__( 'Slots &amp; Participants', 'vca-asm' ),
			__( 'Slots &amp; Participants', 'vca-asm' ),
			'vca_asm_view_network',
			'vca-asm-network-slot-allocation',
			array( $vca_asm_admin_slot_allocation, 'control' )
		);
		add_submenu_page(
			'vca-asm-goldeimer',
			__( 'Slots &amp; Participants', 'vca-asm' ),
			__( 'Slots &amp; Participants', 'vca-asm' ),
			'vca_asm_view_goldeimer',
			'vca-asm-goldeimer-slot-allocation',
			array( $vca_asm_admin_slot_allocation, 'control' )
		);
	}

    /**
     * Converts message arrays into html output
     *
     * @since 1.0
     * @access public
     * @param array $messages
     * @return string
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
	
	public function add_custom_admin_footer() {
		echo '<div class="wp-navbar-vca-container"><div id="navigation-widget"></div><script type="text/javascript" src="/dispenser/javascript/navigation_widget.js"></script></div>';
	}
	
	public function add_custom_admin_header() {
		
		echo '<link rel="stylesheet" href="//maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css" integrity="sha384-BVYiiSIFeK1dGmJRAkycuHAHRg32OmUcww7on3RYdg4Va+PmSTsz/K68vbdEjh4u" crossorigin="anonymous">' .
		'<script src="https://code.jquery.com/jquery-3.2.1.min.js" integrity="sha256-hwg4gsxgFZhOsEEamdOYGBf13FyQuiTwlAQgxVSNgt4=" crossorigin="anonymous"></script>' .
		'<script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js"></script>' .
		'<script src="/dispenser/javascript/dropzone.js"></script>' .
		'<script src="/dispenser/javascript/config.js"></script>' .
		'<link href="/arise/arise.css" rel="stylesheet">' .
		'<link rel="stylesheet" media="screen" href="/dispenser/css/vca.css">' . 
		'<style>.navbar { width: 100% !important; right: 0!important; position: absolute !important; z-index: 100 !important;}</style>';
		
	}

	/**
	 * Constructor
	 *
	 * @since 1.0
	 * @access public
	 */
	public function __construct() {
		add_action( 'admin_menu', array( $this, 'high_priority_admin_menu' ), 9 );
		add_action( 'admin_menu', array( $this, 'low_priority_admin_menu' ), 11 );
		add_action( 'admin_head', array( $this, 'add_custom_admin_header' ));
		add_action( 'admin_head', array( $this, 'add_custom_admin_footer' ));
	}

} // class

endif; // class exists

?>