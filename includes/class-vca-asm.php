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
		date_default_timezone_set( 'Europe/Berlin' );

		/* add multilinguality support */
		load_plugin_textdomain( 'vca-asm', false, VCA_ASM_DIRNAME . '/languages/' );

		/* integrate into the "Members" plugin */
		if ( function_exists( 'members_get_capabilities' ) ) {
			add_filter( 'members_get_capabilities', array( $this, 'extra_caps' ) );
		}

		/* VcA ASM's @global objects (these need to be accessible from within other classes) */
		$GLOBALS['vca_asm_cron'] = new VCA_ASM_Cron(); // used in other constructors, needs to be instantiated first
		$GLOBALS['vca_asm_utilities'] = new VCA_ASM_Utilities(); // used in other constructors, needs to be instantiated first

		$GLOBALS['vca_asm_activities'] = new VCA_ASM_Activities();
		$GLOBALS['vca_asm_mailer'] = new VCA_ASM_Mailer();
		$GLOBALS['vca_asm_geography'] = new VCA_ASM_Geography();
		$GLOBALS['vca_asm_regions'] = new VCA_ASM_Geography(); // legacy
		$GLOBALS['vca_asm_finances'] = new VCA_ASM_Finances();
		$GLOBALS['vca_asm_registrations'] = new VCA_ASM_Registrations();

		/* other objects */
		$vca_asm_lists = new VCA_ASM_Lists();
		$vca_asm_profile = new VCA_ASM_Profile();
		$vca_asm_security = new VCA_ASM_Security();

		if ( is_admin() ) {
			$GLOBALS['vca_asm_admin'] = new VCA_ASM_Admin();
			$GLOBALS['vca_asm_admin_actions'] = new VCA_ASM_Admin_Actions();
			$GLOBALS['vca_asm_admin_education'] = new VCA_ASM_Admin_Education();
			$GLOBALS['vca_asm_admin_emails'] = new VCA_ASM_Admin_Emails();
			$GLOBALS['vca_asm_admin_finances'] = new VCA_ASM_Admin_Finances();
			$GLOBALS['vca_asm_admin_geography'] = new VCA_ASM_Admin_Geography();
			$GLOBALS['vca_asm_admin_goldeimer'] = new VCA_ASM_Admin_Goldeimer();
			$GLOBALS['vca_asm_admin_home'] = new VCA_ASM_Admin_Home();
			$GLOBALS['vca_asm_admin_network'] = new VCA_ASM_Admin_Network();
			$GLOBALS['vca_asm_admin_settings'] = new VCA_ASM_Admin_Settings();
			$GLOBALS['vca_asm_admin_slot_allocation'] = new VCA_ASM_Admin_Slot_Allocation();
			$GLOBALS['vca_asm_admin_applications'] = new VCA_ASM_Admin_Slot_Allocation();
			$GLOBALS['vca_asm_admin_supporters'] = new VCA_ASM_Admin_Supporters();

			/* so far only used in the backend */
			$GLOBALS['vca_asm_roles'] = new VCA_ASM_Roles();

			/* sporadically used to alter data structure or the like */
			$GLOBALS['vca_asm_admin_update'] = new VCA_ASM_Admin_Update();
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

		$caps[] = 'vca_asm_view_goldeimer';
		$caps[] = 'vca_asm_view_goldeimer_nation';
		$caps[] = 'vca_asm_view_goldeimer_global';
		$caps[] = 'vca_asm_manage_goldeimer';
		$caps[] = 'vca_asm_manage_goldeimer_nation';
		$caps[] = 'vca_asm_manage_goldeimer_global';

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

		$caps[] = 'vca_asm_publish_goldeimer_activities';
		$caps[] = 'vca_asm_edit_goldeimer_activities';
		$caps[] = 'vca_asm_edit_others_goldeimer_activities';
		$caps[] = 'vca_asm_delete_goldeimer_activities';
		$caps[] = 'vca_asm_delete_others_goldeimer_activities';
		$caps[] = 'vca_asm_read_private_goldeimer_activities';

		return $caps;
	}

	/**
	 * Enqueue the plugin's javascript & styles
	 *
	 * @since 1.0
	 */
	public function vca_asm_admin_enqueue() {
		global $pagenow;

		$jqui_params = array(
			'monthNames' => array(
				_x( 'January', 'Months', 'vca-asm' ),
				_x( 'February', 'Months', 'vca-asm' ),
				_x( 'March', 'Months', 'vca-asm' ),
				_x( 'April', 'Months', 'vca-asm' ),
				_x( 'May', 'Months', 'vca-asm' ),
				_x( 'June', 'Months', 'vca-asm' ),
				_x( 'July', 'Months', 'vca-asm' ),
				_x( 'August', 'Months', 'vca-asm' ),
				_x( 'September', 'Months', 'vca-asm' ),
				_x( 'October', 'Months', 'vca-asm' ),
				_x( 'November', 'Months', 'vca-asm' ),
				_x( 'December', 'Months', 'vca-asm' )
			),
			'dayNamesMin' => array(
				_x( 'Sun', 'Weekdays, Shortform', 'vca-asm' ),
				_x( 'Mon', 'Weekdays, Shortform', 'vca-asm' ),
				_x( 'Tue', 'Weekdays, Shortform', 'vca-asm' ),
				_x( 'Wed', 'Weekdays, Shortform', 'vca-asm' ),
				_x( 'Thu', 'Weekdays, Shortform', 'vca-asm' ),
				_x( 'Fri', 'Weekdays, Shortform', 'vca-asm' ),
				_x( 'Sat', 'Weekdays, Shortform', 'vca-asm' )
			)
		);
		$generic_params = array(
			'strings' => array(
				'btnDeselect' => __( 'Deselect all', 'vca-asm' ),
				'btnSelect' => __( 'Select all', 'vca-asm' )
			)
		);

		wp_register_script( 'vca-asm-admin-email-preview', VCA_ASM_RELPATH . 'js/admin-email-preview.js', array( 'jquery' ), '2013.11.6.1', true );
		wp_register_script( 'vca-asm-admin-email-compose', VCA_ASM_RELPATH . 'js/admin-email-compose.js', array( 'jquery' ), '2014.06.08.1', true );
		wp_register_script( 'vca-asm-admin-finances', VCA_ASM_RELPATH . 'js/admin-finances.js', array( 'jquery' ), '2014.04.02.3', true );
		wp_register_script( 'vca-asm-admin-finances-spreadsheet-form', VCA_ASM_RELPATH . 'js/admin-finances-spreadsheet-form.js', array( 'jquery' ), '2014.04.02.3', true );
		wp_register_script( 'vca-asm-admin-generic', VCA_ASM_RELPATH . 'js/admin-generic.js', array( 'jquery' ), '2013.11.6.1', true );
		wp_register_script( 'vca-asm-admin-repeatable-custom-fields', VCA_ASM_RELPATH . 'js/admin-repeatable-custom-fields.js',
			array( 'jquery', 'jquery-ui-slider', 'jquery-ui-datepicker' ), '2013.11.6.1', true );
		wp_register_script( 'vca-asm-admin-jquery-ui-integration', VCA_ASM_RELPATH . 'js/admin-jquery-ui-integration.js',
			array( 'jquery', 'jquery-ui-slider', 'jquery-ui-datepicker' ), '2014.04.01.10', true );
		wp_register_script( 'vca-asm-admin-profile', VCA_ASM_RELPATH . 'js/admin-profile.js', false, '2013.11.6.1', true );
		wp_register_script( 'vca-asm-admin-quotas', VCA_ASM_RELPATH . 'js/admin-quotas.js',
			array( 'jquery', 'jquery-ui-slider' ), '2013.11.6.1', true );
		wp_register_script( 'vca-asm-admin-settings', VCA_ASM_RELPATH . 'js/admin-settings.js', array( 'jquery', 'jquery-ui-slider' ), '2013.11.6.1', true );
		wp_register_script( 'vca-asm-admin-supporter-filter', VCA_ASM_RELPATH . 'js/admin-supporter-filter.js', array( 'jquery' ), '2013.11.6.1', true );
		wp_register_script( 'vca-asm-admin-validation', VCA_ASM_RELPATH . 'js/admin-validation.js', array( 'jquery' ), '2014.04.22.5', true );
		wp_register_script( 'vca-asm-tooltip', VCA_ASM_RELPATH . 'js/tooltip.js', array( 'jquery' ), '2013.11.6.1', true );
		wp_register_script( 'vca-asm-ctr-to-cty', VCA_ASM_RELPATH . 'js/ctr-to-cty.js', array( 'jquery' ), '2013.11.6.1', true );

		/* used throughout the backend, enqueued everywhere */
		wp_enqueue_script( 'jquery-ui-datepicker' );
		wp_enqueue_script( 'jquery-ui-slider' );
		wp_enqueue_script( 'vca-asm-admin-jquery-ui-integration' );
		wp_enqueue_script( 'vca-asm-admin-generic' );
		wp_enqueue_script( 'vca-asm-tooltip' );

		wp_localize_script( 'vca-asm-admin-generic', 'genericParams', $generic_params );
		wp_localize_script( 'vca-asm-admin-jquery-ui-integration', 'jquiParams', $jqui_params );

		/* used on activity/post edit screens only */
		if( 'post.php' == $pagenow || 'post-new.php' == $pagenow ) {
			wp_enqueue_script( 'vca-asm-admin-validation' );
			wp_enqueue_script( 'vca-asm-admin-repeatable-custom-fields' );
			wp_enqueue_script( 'vca-asm-admin-quotas' );
			wp_enqueue_script( 'vca-asm-ctr-to-cty' );
		}

		/* conditional (context) based enqueue as well ? */
		wp_enqueue_script( 'vca-asm-admin-profile' );

		wp_register_style( 'jquery-ui-framework', VCA_ASM_RELPATH . 'css/jquery-ui-framework.css' );
		wp_register_style( 'jquery-ui-custom', VCA_ASM_RELPATH . 'css/jquery-ui-custom.css' );
		wp_register_style( 'vca-asm-admin-generic-style', VCA_ASM_RELPATH . 'css/admin-generic.css', false, '2014.5.7.2' );

		wp_register_style( 'vca-asm-tooltips', VCA_ASM_RELPATH . 'css/admin-tooltips.css', false, '2013.11.6.1' );

		wp_enqueue_style( 'jquery-ui-framework' );
		wp_enqueue_style( 'jquery-ui-custom' );
		wp_enqueue_style( 'vca-asm-admin-generic-style' );
		wp_enqueue_style( 'vca-asm-tooltips' );
	}


	public function vca_asm_frontend_enqueue() {
		global $vca_asm_activities;

		wp_register_script( 'isotope-metafizzy', VCA_ASM_RELPATH . 'js/jquery.isotope.min.js', array( 'jquery' ), '2013.11.6.1', true );
		wp_register_script( 'jquery-scrollTo', VCA_ASM_RELPATH . 'js/jquery.scrollTo.min.js', array( 'jquery' ), '2013.11.6.1', true );
		wp_register_script( 'vca-asm-activities', VCA_ASM_RELPATH . 'js/vca-asm-activities.js', array( 'jquery', 'isotope-metafizzy' ), '2013.11.15.7', true );
		wp_register_script( 'vca-asm-profile', VCA_ASM_RELPATH . 'js/profile.js', array( 'jquery' ), '2013.11.6.1', true );
		wp_register_script( 'vca-asm-strength-meter-init', VCA_ASM_RELPATH . 'js/strength-meter-init.js', array( 'jquery' ), '2013.11.6.1', true );

		wp_enqueue_script( 'vca-asm-profile' );

		wp_register_style( 'vca-asm-activities-style', VCA_ASM_RELPATH . 'css/activities.css', false, '2014.5.7.15' );
		wp_register_style( 'vca-asm-isotope-style', VCA_ASM_RELPATH . 'css/isotope.css', false, '2013.11.6.3' );

		if ( is_singular( $vca_asm_activities->activity_types ) ) {
			wp_enqueue_style( 'vca-asm-activities-style' );
		}
	}

	function clean_unwanted_caps(){
		$delete_caps = array(
			'vca_asm_submit_finances'
		);
		global $wp_roles;
		foreach ($delete_caps as $cap) {
			foreach ( array_keys($wp_roles->roles) as $role ) {
				$wp_roles->remove_cap( $role, $cap );
			}
		}
	}

	public function start_session() {
		if( ! session_id() ) {
			session_start();
		}
	}

	function end_session() {
		session_destroy ();
	}

	/**
	 * Constructor
	 *
	 * @since 1.0
	 * @access public
	 */
	public function __construct() {
		add_action( 'wp_loaded', array( $this, 'init' ) );
		add_action( 'wp_enqueue_scripts', array( $this, 'vca_asm_frontend_enqueue' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'vca_asm_admin_enqueue' ) );
		add_action( 'admin_init', array( $this, 'clean_unwanted_caps' ) );
		add_action( 'init', array( $this, 'start_session' ), 1 );
		add_action( 'wp_logout', array( $this, 'end_session' ) );
		add_action( 'wp_login', array( $this, 'end_session' ) );
	}
} // class

endif; // class exists

?>