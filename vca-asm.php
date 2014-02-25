<?php

/*
Plugin Name: VcA Activity & Supporter Management
Plugin URI: http://pool.vivaconagua.org
Description: Tool for Viva con Agua to manage it's supporters and their activities within the network
Version: 1.1
Author: Johannes Pilkahn
Author URI: http://tramprennen.org    // subject to change
License: GPL3
*/

/*  Copyright 2012  Johannes Pilkahn  (email : j.pilkahn@gmail.com)

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License, version 3, as
    published by the Free Software Foundation.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/


/**
 * Holds the absolute location of the main plugin file (this file)
 *
 * @since 1.0
 */
if ( ! defined( 'VCA_ASM_FILE' ) )
	define( 'VCA_ASM_FILE', __FILE__ );

/**
 * Holds the absolute location of VcA Activity & Supporter Management
 *
 * @since 1.0
 */
if ( ! defined( 'VCA_ASM_ABSPATH' ) )
	define( 'VCA_ASM_ABSPATH', dirname( __FILE__ ) );

/**
 * Holds the URL of VcA Activity & Supporter Management
 *
 * @since 1.0
 */
if ( ! defined( 'VCA_ASM_RELPATH' ) )
	define( 'VCA_ASM_RELPATH', plugin_dir_url( __FILE__ ) );

/**
 * Holds the name of the VcA Activity & Supporter Management directory
 *
 * @since 1.0
 */
if ( !defined( 'VCA_ASM_DIRNAME' ) )
	define( 'VCA_ASM_DIRNAME', basename( VCA_ASM_ABSPATH ) );

/**
 * Enqueue the plugin's javascript
 *
 * @since 1.0
 */
function vca_asm_enqueue() {
	if( is_admin() ) {
		wp_register_script( 'vca-asm-custom-field-instances', VCA_ASM_RELPATH . 'js/repeatable-custom-fields.js',
			array( 'jquery-ui-slider', 'jquery-ui-datepicker' ), '1.191', true );
		wp_register_script( 'vca-asm-admin-generic', VCA_ASM_RELPATH . 'js/admin-generic.js', false, '1.193', true );
		wp_register_script( 'vca-asm-admin-email-preview', VCA_ASM_RELPATH . 'js/admin-email-preview.js', false, '1.191', true );
		wp_register_script( 'vca-asm-admin-profile', VCA_ASM_RELPATH . 'js/admin-profile.js', false, '1.191', true );
		wp_register_script( 'vca-asm-admin-settings', VCA_ASM_RELPATH . 'js/admin-settings.js', false, '1.193', true );
		wp_register_script( 'vca-asm-tooltip', VCA_ASM_RELPATH . 'js/tooltip.js', false, '1.191', true );

		wp_enqueue_script( 'vca-asm-custom-field-instances' );
		wp_enqueue_script( 'jquery-ui-datepicker' );
		wp_enqueue_script( 'jquery-ui-slider' );
		wp_enqueue_script( 'vca-asm-admin-profile' );
		wp_enqueue_script( 'vca-asm-admin-generic' );
		wp_enqueue_script( 'vca-asm-tooltip' );

		wp_enqueue_style( 'jquery-ui-custom', VCA_ASM_RELPATH . 'css/jquery-ui-custom.css' );
		wp_enqueue_style( 'vca-asm-admin-generic-style', VCA_ASM_RELPATH . 'css/admin-generic.css', false, '1.193' );
		wp_enqueue_style( 'vca-asm-tooltips', VCA_ASM_RELPATH . 'css/admin-tooltips.css', false, '1.191' );
	} else {
		wp_register_script( 'vca-asm-profile', VCA_ASM_RELPATH . 'js/profile.js', false, '1.191', true );
		wp_register_script( 'vca-asm-strength-meter-init', VCA_ASM_RELPATH . 'js/strength-meter-init.js', false, '1.19', true );

		wp_enqueue_script( 'vca-asm-profile' );

		wp_enqueue_style( 'vca-asm-activities', VCA_ASM_RELPATH . 'css/activities.css', false, '1.191' );
	}
}
add_action( 'wp_loaded', 'vca_asm_enqueue' );

/**
 * Require needed files
 *
 * @since 1.0
 */
require_once( VCA_ASM_ABSPATH . '/includes/class-vca-asm.php' );
require_once( VCA_ASM_ABSPATH . '/includes/class-vca-asm-activities.php' );
require_once( VCA_ASM_ABSPATH . '/includes/class-vca-asm-lists.php' );
require_once( VCA_ASM_ABSPATH . '/includes/class-vca-asm-mailer.php' );
require_once( VCA_ASM_ABSPATH . '/includes/class-vca-asm-profile.php' );
require_once( VCA_ASM_ABSPATH . '/includes/class-vca-asm-regions.php' );
require_once( VCA_ASM_ABSPATH . '/includes/class-vca-asm-registrations.php' );
require_once( VCA_ASM_ABSPATH . '/includes/class-vca-asm-security.php' );
require_once( VCA_ASM_ABSPATH . '/includes/class-vca-asm-supporter.php' );
require_once( VCA_ASM_ABSPATH . '/includes/class-vca-asm-utilities.php' );
require_once( VCA_ASM_ABSPATH . '/includes/class-php2excel.php' );


/**
 * VCA_ASM Objects
 *
 * @global object $vca_asm
 * @since 1.0
 */
$GLOBALS['vca_asm'] =& new VCA_ASM();

/**
 * Admin UI
 *
 * @since 1.0
 */
if ( is_admin() ) {
	require_once( VCA_ASM_ABSPATH . '/admin/class-vca-asm-admin.php' );
	require_once( VCA_ASM_ABSPATH . '/admin/class-vca-asm-admin-activities.php' );
	require_once( VCA_ASM_ABSPATH . '/admin/class-vca-asm-admin-applications.php' );
	require_once( VCA_ASM_ABSPATH . '/admin/class-vca-asm-admin-emails.php' );
	require_once( VCA_ASM_ABSPATH . '/admin/class-vca-asm-admin-finances.php' );
	require_once( VCA_ASM_ABSPATH . '/admin/class-vca-asm-admin-settings.php' );
	require_once( VCA_ASM_ABSPATH . '/admin/class-vca-asm-admin-supporters.php' );
	require_once( VCA_ASM_ABSPATH . '/includes/class-vca-asm-stats.php' );
	require_once( ABSPATH . '/wp-admin/includes/user.php' );
	/**
	 * vca_asm_admin object
	 *
	 * @since 1.0
	 */
	$vca_asm_admin =& new VCA_ASM_Admin();
	$GLOBALS['vca_asm_admin_activities'] =& new VCA_ASM_Admin_Activities();
	$GLOBALS['vca_asm_admin_applications'] =& new VCA_ASM_Admin_Applications();
	$GLOBALS['vca_asm_admin_emails'] =& new VCA_ASM_Admin_Emails();
	$GLOBALS['vca_asm_admin_finances'] =& new VCA_ASM_Admin_Finances();
	$GLOBALS['vca_asm_admin_settings'] =& new VCA_ASM_Admin_Settings();
	$GLOBALS['vca_asm_admin_supporters'] =& new VCA_ASM_Admin_Supporters();
}

/**
 * Incrementing database version
 *
 * increase to alter tables and table structures
 *
 * @since 1.0
 */
$vca_asm_db_version = "3.7";

/**
 * Installation & Update Routines
 *
 * Creates and/or updates plugin's tables.
 * The install method is only triggered on plugin installation
 * and when the database version number
 * ( "$vca_asm_db_version", see above )
 * has changed.
 *
 * @since 1.0
 */
function vca_asm_install() {
   global $wpdb, $vca_asm_db_version;

	/* SQL statements to create required tables */
	$sql = array();
	$sql[] = "CREATE TABLE " . $wpdb->prefix . "vca_asm_applications (
		id int UNSIGNED NOT NULL AUTO_INCREMENT ,
		supporter int UNSIGNED NOT NULL ,
		activity int UNSIGNED NOT NULL ,
		state tinyint UNSIGNED ,
		notes text NOT NULL ,
		UNIQUE KEY id (id)
    );";
	$sql[] = "CREATE TABLE " . $wpdb->prefix . "vca_asm_emails (
		id int UNSIGNED NOT NULL AUTO_INCREMENT ,
		time bigint NOT NULL ,
		sent_by bigint UNSIGNED NOT NULL ,
		subject text NOT NULL ,
		message longtext NOT NULL ,
		membership tinyint UNSIGNED NOT NULL ,
		receipient_group varchar(255) NOT NULL ,
		receipient_id int NOT NULL ,
		UNIQUE KEY id (id)
	);";
	$sql[] = "CREATE TABLE " . $wpdb->prefix . "vca_asm_regions (
		id int UNSIGNED NOT NULL AUTO_INCREMENT ,
		name varchar(255) NOT NULL ,
		status tinytext NOT NULL ,
		has_user tinyint UNSIGNED ,
		user_id bigint NOT NULL ,
		user varchar(255) NOT NULL ,
		pass varchar(255) NOT NULL ,
		supporters int NOT NULL ,
		members int NOT NULL ,
		UNIQUE KEY id (id)
    );";
	$sql[] = "CREATE TABLE " . $wpdb->prefix . "vca_asm_registrations (
		id int UNSIGNED NOT NULL AUTO_INCREMENT ,
		supporter int UNSIGNED NOT NULL ,
		activity int UNSIGNED NOT NULL ,
		contingent int UNSIGNED ,
		notes text NOT NULL ,
		UNIQUE KEY id (id)
	);";
	$sql[] = "CREATE TABLE " . $wpdb->prefix . "vca_asm_registrations_old (
		id int UNSIGNED NOT NULL AUTO_INCREMENT ,
		supporter int UNSIGNED NOT NULL ,
		activities text NOT NULL ,
		UNIQUE KEY id (id)
	);";
	$sql[] = "CREATE TABLE " . $wpdb->prefix . "vca_asm_applications_old (
		id int UNSIGNED NOT NULL AUTO_INCREMENT ,
		supporter int UNSIGNED NOT NULL ,
		activities text NOT NULL ,
		UNIQUE KEY id (id)
	);";
	$sql[] = "CREATE TABLE " . $wpdb->prefix . "vca_asm_auto_responses (
		id int UNSIGNED NOT NULL AUTO_INCREMENT ,
		action tinytext NOT NULL ,
		switch tinyint UNSIGNED ,
		subject text NOT NULL ,
		message text NOT NULL ,
		UNIQUE KEY id (id)
	);";

	/* comparison of above with db, db adjustments */
	require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
	dbDelta($sql);

	/* set default options */
	if( ! get_option( 'pass_strength_supporter' ) ) {
		add_option( 'pass_strength_supporter', 3 );
	}
	if( ! get_option( 'pass_strength_admin' ) ) {
		add_option( 'pass_strength_admin', 4 );
	}
	if( ! get_option( 'pass_reset_cycle_supporter' ) ) {
		add_option( 'pass_reset_cycle_supporter', 0 );
	}
	if( ! get_option( 'pass_reset_cycle_admin' ) ) {
		add_option( 'pass_reset_cycle_admin', 6 );
	}
	if( ! get_option( 'automatic_logout_period' ) ) {
		add_option( 'automatic_logout_period', 20 );
	}

	$users = get_users();
	foreach ( $users as $user ) {
		update_user_meta( $user->ID, 'vca_asm_last_pass_reset', time() );
	}

	/* fill autoresponses table, dirty!, replace! */
	$test = $wpdb->get_results(
		"SELECT * FROM " . $wpdb->prefix . "vca_asm_auto_responses", ARRAY_A
	);
	if( ! isset( $test[0]['action'] ) ) {
		$actions = array(
			'applied',
			'accepted',
			'accepted_late',
			'denied',
			'reg_revoked',
			'mem_accepted',
			'mem_denied',
			'mem_cancelled'
		);
		foreach( $actions as $action ) {
			$wpdb->insert(
				$wpdb->prefix . 'vca_asm_auto_responses',
				array(
					'action' => $action,
					'switch' => 1
				),
				array( '%s', '%d' )
			);
		}
	}

	/* update db version number */
   update_option( "vca_asm_db_version", $vca_asm_db_version );
}

function vca_asm_update_db_check() {
    global $vca_asm_db_version;
    if( get_site_option( 'vca_asm_db_version' ) != $vca_asm_db_version ) {
        vca_asm_install();
    }
}


function update_umetar() {
	global $wpdb;

	$wpdb->update(
		'vca1312_usermeta',
		array(
			'meta_key' => 'vca1312_capabilities'
		),
		array( 'meta_key' => 'wp_capabilities' ),
		array( '%s'	),
		array( '%s' )
	);
}

add_action( 'plugins_loaded', 'vca_asm_update_db_check' );
register_activation_hook( VCA_ASM_FILE, 'vca_asm_install' );


?>
