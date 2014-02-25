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
		
		/* integrate into the "Members" plugin */
		if ( function_exists( 'members_get_capabilities' ) ) {
			add_filter( 'members_get_capabilities', array( &$this, 'extra_caps' ) );
		}
		
		/* VcA ASM's @global objects (these need to be accessible from other classes) */
		$GLOBALS['vca_asm_mailer'] =& new VCA_ASM_Mailer();
		$GLOBALS['vca_asm_regions'] =& new VCA_ASM_Regions();
		$GLOBALS['vca_asm_registrations'] =& new VCA_ASM_Registrations();
		$GLOBALS['vca_asm_utilities'] =& new VCA_ASM_Utilities();
			
		/* VcA ASM's objects */	
		$vca_asm_activities =& new VCA_ASM_Activities();
		$vca_asm_lists =& new VCA_ASM_Lists();
		$vca_asm_profile =& new VCA_ASM_Profile();
	}
	
	/**
	 * Adds plugin-specific user capabilities
	 *
	 * @since 1.0
	 * @access public
	 */	
	public function extra_caps( $caps ) {
		$caps[] = 'vca_asm_manage_options';
		$caps[] = 'vca_asm_view_supporters';
		$caps[] = 'vca_asm_view_all_supporters';
		$caps[] = 'vca_asm_promote_supporters';
		$caps[] = 'vca_asm_promote_all_supporters';
		$caps[] = 'vca_asm_edit_regions';
		$caps[] = 'vca_asm_send_emails';
		$caps[] = 'vca_asm_send_global_emails';
		$caps[] = 'vca_asm_edit_autoresponses';
		$caps[] = 'vca_asm_publish_activities';
		$caps[] = 'vca_asm_edit_activities';
		$caps[] = 'vca_asm_edit_others_activities';
		$caps[] = 'vca_asm_delete_activities';
		$caps[] = 'vca_asm_delete_others_activities';
		$caps[] = 'vca_asm_read_private_activities';
		$caps[] = 'vca_asm_manage_applications';
		$caps[] = 'vca_asm_manage_all_applications';
	return $caps;
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
	}	
} // class

endif; // class exists

?>