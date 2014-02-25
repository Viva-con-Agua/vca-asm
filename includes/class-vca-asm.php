<?php

/**
 * VCA_ASM class.
 *
 * This class holds all VCA_ASM components.
 *
 * @package VcA Activity & Supporter Management
 * @since 1.0
 */

if ( ! class_exists( 'VCA_ASM' ) ) :

class VCA_ASM {

	/**
	 * Initializes the plugin
	 *
	 * @since 1.0
	 * @access public
	 */
	public function init() {
		/* add multilinguality support */
		load_plugin_textdomain( 'vca-asm', false, VCA_ASM_DIRNAME . '/languages/' );
		setlocale ( LC_ALL , 'de_DE.UTF-8' );
		setlocale( LC_MESSAGES, 'en_US' );
		setlocale( LC_NUMERIC, 'en_US' );

		/* integrate into the "Members" plugin */
		if ( function_exists( 'members_get_capabilities' ) ) {
			add_filter( 'members_get_capabilities', array( &$this, 'extra_caps' ) );
		}

		/* VcA ASM's @global objects (these need to be accessible from within other classes) */
		$GLOBALS['vca_asm_utilities'] =& new VCA_ASM_Utilities(); // used in other constructors, needs to be instantiated first

		$GLOBALS['vca_asm_activities'] =& new VCA_ASM_Activities();
		$GLOBALS['vca_asm_mailer'] =& new VCA_ASM_Mailer();
		$GLOBALS['vca_asm_geography'] =& new VCA_ASM_Geography();
		$GLOBALS['vca_asm_regions'] =& new VCA_ASM_Geography(); // legacy
		$GLOBALS['vca_asm_registrations'] =& new VCA_ASM_Registrations();

		/* other objects */
		$vca_asm_lists =& new VCA_ASM_Lists();
		$vca_asm_profile =& new VCA_ASM_Profile();
		$vca_asm_security =& new VCA_ASM_Security();

		if ( is_admin() ) {
			$GLOBALS['vca_asm_admin'] =& new VCA_ASM_Admin();
			$GLOBALS['vca_asm_admin_actions'] =& new VCA_ASM_Admin_Actions();
			$GLOBALS['vca_asm_admin_education'] =& new VCA_ASM_Admin_Education();
			$GLOBALS['vca_asm_admin_emails'] =& new VCA_ASM_Admin_Emails();
			$GLOBALS['vca_asm_admin_finances'] =& new VCA_ASM_Admin_Finances();
			$GLOBALS['vca_asm_admin_geography'] =& new VCA_ASM_Admin_Geography();
			$GLOBALS['vca_asm_admin_home'] =& new VCA_ASM_Admin_Home();
			$GLOBALS['vca_asm_admin_network'] =& new VCA_ASM_Admin_Network();
			$GLOBALS['vca_asm_admin_settings'] =& new VCA_ASM_Admin_Settings();
			$GLOBALS['vca_asm_admin_slot_allocation'] =& new VCA_ASM_Admin_Slot_Allocation();
			$GLOBALS['vca_asm_admin_applications'] =& new VCA_ASM_Admin_Slot_Allocation();
			$GLOBALS['vca_asm_admin_supporters'] =& new VCA_ASM_Admin_Supporters();

			/* so far only used in the backend */
			$GLOBALS['vca_asm_roles'] =& new VCA_ASM_Roles();

			/* used on major updates */
			// $GLOBALS['vca_asm_admin_update'] =& new VCA_ASM_Admin_Update();
		}
	}

	/**
	 * Adds plugin-specific user capabilities
	 *
	 * @since 1.0
	 * @access public
	 */
	public function extra_caps( $caps ) {
		$caps[] = 'vca_asm_view_options';
		$caps[] = 'vca_asm_manage_options';
		$caps[] = 'vca_asm_set_mode';

		$caps[] = 'vca_asm_view_actions';
		$caps[] = 'vca_asm_view_actions_nation';
		$caps[] = 'vca_asm_view_actions_global';
		$caps[] = 'vca_asm_manage_actions';
		$caps[] = 'vca_asm_manage_actions_nation';
		$caps[] = 'vca_asm_manage_actions_global';

		$caps[] = 'vca_asm_view_education';
		$caps[] = 'vca_asm_view_education_nation';
		$caps[] = 'vca_asm_view_education_global';
		$caps[] = 'vca_asm_manage_education';
		$caps[] = 'vca_asm_manage_education_nation';
		$caps[] = 'vca_asm_manage_education_global';

		$caps[] = 'vca_asm_view_network';
		$caps[] = 'vca_asm_view_network_nation';
		$caps[] = 'vca_asm_view_network_global';
		$caps[] = 'vca_asm_manage_network';
		$caps[] = 'vca_asm_manage_network_nation';
		$caps[] = 'vca_asm_manage_network_global';

		$caps[] = 'vca_asm_view_supporters';
		$caps[] = 'vca_asm_view_supporters_nation';
		$caps[] = 'vca_asm_view_supporters_global';
		$caps[] = 'vca_asm_promote_supporters';
		$caps[] = 'vca_asm_promote_supporters_nation';
		$caps[] = 'vca_asm_promote_supporters_global';
		$caps[] = 'vca_asm_delete_supporters';
		$caps[] = 'vca_asm_delete_supporters_nation';
		$caps[] = 'vca_asm_delete_supporters_global';

		$caps[] = 'vca_asm_view_emails';
		$caps[] = 'vca_asm_view_emails_nation';
		$caps[] = 'vca_asm_view_emails_global';
		$caps[] = 'vca_asm_send_emails';
		$caps[] = 'vca_asm_send_emails_nation';
		$caps[] = 'vca_asm_send_emails_global';

		$caps[] = 'vca_asm_view_finances';
		$caps[] = 'vca_asm_view_finances_nation';
		$caps[] = 'vca_asm_view_finances_global';
		$caps[] = 'vca_asm_manage_finances';
		$caps[] = 'vca_asm_manage_finances_nation';
		$caps[] = 'vca_asm_manage_finances_global';

		$caps[] = 'vca_asm_publish_actions_activities';
		$caps[] = 'vca_asm_edit_actions_activities';
		$caps[] = 'vca_asm_edit_others_actions_activities';
		$caps[] = 'vca_asm_delete_actions_activities';
		$caps[] = 'vca_asm_delete_others_actions_activities';
		$caps[] = 'vca_asm_read_private_actions_activities';

		$caps[] = 'vca_asm_publish_education_activities';
		$caps[] = 'vca_asm_edit_education_activities';
		$caps[] = 'vca_asm_edit_others_education_activities';
		$caps[] = 'vca_asm_delete_education_activities';
		$caps[] = 'vca_asm_delete_others_education_activities';
		$caps[] = 'vca_asm_read_private_education_activities';

		$caps[] = 'vca_asm_publish_network_activities';
		$caps[] = 'vca_asm_edit_network_activities';
		$caps[] = 'vca_asm_edit_others_network_activities';
		$caps[] = 'vca_asm_delete_network_activities';
		$caps[] = 'vca_asm_delete_others_network_activities';
		$caps[] = 'vca_asm_read_private_network_activities';

		return $caps;
	}

	function clean_unwanted_caps(){
		$delete_caps = array(
			'vca_asm_send_emails_city',
			'vca_asm_send_global_emails',
			'vca_asm_publish_activities',
			'vca_asm_edit_activities',
			'vca_asm_edit_others_activities',
			'vca_asm_delete_activities',
			'vca_asm_delete_others_activities',
			'vca_asm_read_private_activities',
			'vca_asm_manage_applications',
			'vca_asm_manage_all_applications',
			'vca_asm_edit_regions',
			'vca_asm_manage_actions_city',
			'vca_asm_manage_education_city',
			'vca_asm_manage_network_city',
			'vca_asm_manage_applications_city',
			'vca_asm_access_actions',
			'vca_asm_access_education',
			'vca_asm_access_finances',
			'vca_asm_access_network',
			'vca_asm_view_all_supporters',
			'vca_asm_promote_all_supporters',
			'vca_asm_delete_all_supporters',
			'vca_asm_manage_applications',
			'vca_asm_manage_applications_nation',
			'vca_asm_manage_applications_global',
			'vca_asm_edit_autoresponses'
		);
		global $wp_roles;
		foreach ($delete_caps as $cap) {
			foreach (array_keys($wp_roles->roles) as $role) {
				$wp_roles->remove_cap($role, $cap);
			}
		}
	}

	/**
	 * PHP4 style constructor
	 *
	 * @since 1.0
	 * @access public
	 */
	public function VCA_ASM() {
		$this->__construct();
	}

	/**
	 * PHP5 style constructor
	 *
	 * @since 1.0
	 * @access public
	 */
	public function __construct() {
		add_action( 'init', array( &$this, 'init' ) );
		add_action( 'admin_init', array( &$this, 'clean_unwanted_caps' ) );
	}
} // class

endif; // class exists

?>