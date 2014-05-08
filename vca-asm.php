<?php

/*
Plugin Name: VcA Activity & Supporter Management
Plugin URI: http://pool.vivaconagua.org
Description: Tool for Viva con Agua (NGO) to manage its supporters and their activities within their network
Version: 1.5
Author: Johannes Pilkahn
Author URI: http://tramprennen.org // subject to change
License: GPL3
*/

/*  Copyright 2012  Johannes Pilkahn  (email : j.pilkahn@vivaconagua.org)

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
 * Require needed files
 *
 * @since 1.0
 */
/* E-Mail Template, used in cronjob (callback in mailer class), needs to be included before */
require_once( VCA_ASM_ABSPATH . '/templates/class-vca-asm-email-html.php' );
/* core of the plugin, frontend (usually insantiated only once)*/
require_once( VCA_ASM_ABSPATH . '/includes/class-vca-asm.php' );
require_once( VCA_ASM_ABSPATH . '/includes/class-vca-asm-activities.php' );
require_once( VCA_ASM_ABSPATH . '/includes/class-vca-asm-cron.php' );
require_once( VCA_ASM_ABSPATH . '/includes/class-vca-asm-finances.php' );
require_once( VCA_ASM_ABSPATH . '/includes/class-vca-asm-geography.php' );
require_once( VCA_ASM_ABSPATH . '/includes/class-vca-asm-lists.php' );
require_once( VCA_ASM_ABSPATH . '/includes/class-vca-asm-mailer.php' );
require_once( VCA_ASM_ABSPATH . '/includes/class-vca-asm-profile.php' );
require_once( VCA_ASM_ABSPATH . '/includes/class-vca-asm-registrations.php' );
require_once( VCA_ASM_ABSPATH . '/includes/class-vca-asm-security.php' );
require_once( VCA_ASM_ABSPATH . '/includes/class-vca-asm-utilities.php' );
/* classes that hold data sets (multiple instances) */
require_once( VCA_ASM_ABSPATH . '/models/class-vca-asm-activity.php' );
require_once( VCA_ASM_ABSPATH . '/models/class-vca-asm-city-finances.php' );
require_once( VCA_ASM_ABSPATH . '/models/class-vca-asm-stats.php' );
require_once( VCA_ASM_ABSPATH . '/models/class-vca-asm-supporter.php' );
/* foreign code */
require_once( VCA_ASM_ABSPATH . '/lib/class-php2excel.php' );
/* template classes (non-OOP templates are included on the spot) */
if ( ! is_admin() ) {
	require_once( VCA_ASM_ABSPATH . '/templates/class-vca-asm-frontend-activities.php' );
}

/**
 * Admin UI
 *
 * @since 1.0
 */
if ( is_admin() ) {
	/* functional classes (usually insantiated only once) */
	require_once( VCA_ASM_ABSPATH . '/admin/class-vca-asm-admin.php' );
	require_once( VCA_ASM_ABSPATH . '/admin/class-vca-asm-admin-actions.php' );
	require_once( VCA_ASM_ABSPATH . '/admin/class-vca-asm-admin-education.php' );
	require_once( VCA_ASM_ABSPATH . '/admin/class-vca-asm-admin-emails.php' );
	require_once( VCA_ASM_ABSPATH . '/admin/class-vca-asm-admin-home.php' );
	require_once( VCA_ASM_ABSPATH . '/admin/class-vca-asm-admin-finances.php' );
	require_once( VCA_ASM_ABSPATH . '/admin/class-vca-asm-admin-geography.php' );
	require_once( VCA_ASM_ABSPATH . '/admin/class-vca-asm-admin-goldeimer.php' );
	require_once( VCA_ASM_ABSPATH . '/admin/class-vca-asm-admin-network.php' );
	require_once( VCA_ASM_ABSPATH . '/admin/class-vca-asm-admin-settings.php' );
	require_once( VCA_ASM_ABSPATH . '/admin/class-vca-asm-admin-slot-allocation.php' );
	require_once( VCA_ASM_ABSPATH . '/admin/class-vca-asm-admin-supporters.php' );

	/* so far only used in the backend... */
	require_once( VCA_ASM_ABSPATH . '/includes/class-vca-asm-roles.php' );
	require_once( VCA_ASM_ABSPATH . '/includes/class-vca-asm-validation.php' );

	/* template classes (non-OOP templates are included on the spot) */
	require_once( VCA_ASM_ABSPATH . '/templates/class-vca-asm-admin-form.php' );
	require_once( VCA_ASM_ABSPATH . '/templates/class-vca-asm-admin-future-feech.php' );
	require_once( VCA_ASM_ABSPATH . '/templates/class-vca-asm-admin-metaboxes.php' );
	require_once( VCA_ASM_ABSPATH . '/templates/class-vca-asm-admin-page.php' );
	require_once( VCA_ASM_ABSPATH . '/templates/class-vca-asm-admin-table.php' );

	/* WP-core classes */
	require_once( ABSPATH . '/wp-admin/includes/user.php' );

	/* used on updates or for testing */
	require_once( VCA_ASM_ABSPATH . '/admin/class-vca-asm-admin-update.php' );
}

/**
 * Sets the locale depending on a user's settings
 *
 * @since 1.3
 */
function vca_asm_user_locale() {
	add_filter( 'locale', 'vca_asm_set_locale', 1 );
}
function vca_asm_set_locale( $locale ) {
	global $current_user;
	get_currentuserinfo();

	setlocale( LC_TIME, "" );

	if ( 'en' === get_user_meta( $current_user->ID, 'pool_lang', true ) ) {
		setlocale ( LC_ALL , 'en_US' );
		setlocale( LC_MESSAGES, 'en_US' );
		setlocale( LC_NUMERIC, 'en_US' );
		return 'en_US';
	}

	setlocale ( LC_ALL , 'de_DE.UTF-8' );
	setlocale( LC_MESSAGES, 'en_US' );
	setlocale( LC_NUMERIC, 'en_US' );

	return $locale;
}
add_action( 'plugins_loaded', 'vca_asm_user_locale' );

/**
 * VCA_ASM Initial Object
 *
 * @global object $vca_asm
 * @since 1.0
 */
$vca_asm = new VCA_ASM();

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
 * is changed.
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
    ) DEFAULT CHARACTER SET = utf8 COLLATE = utf8_general_ci;";
	$sql[] = "CREATE TABLE " . $wpdb->prefix . "vca_asm_applications_old (
		id int UNSIGNED NOT NULL AUTO_INCREMENT ,
		supporter int UNSIGNED NOT NULL ,
		activity int UNSIGNED NOT NULL ,
		notes text NOT NULL ,
		UNIQUE KEY id (id)
	) DEFAULT CHARACTER SET = utf8 COLLATE = utf8_general_ci;";
	$sql[] = "CREATE TABLE " . $wpdb->prefix . "vca_asm_auto_responses (
		id int UNSIGNED NOT NULL AUTO_INCREMENT ,
		action tinytext NOT NULL ,
		switch tinyint UNSIGNED ,
		subject text NOT NULL ,
		message text NOT NULL ,
		UNIQUE KEY id (id)
	) DEFAULT CHARACTER SET = utf8 COLLATE = utf8_general_ci;";
	$sql[] = "CREATE TABLE " . $wpdb->prefix . "vca_asm_emails (
		id int UNSIGNED NOT NULL AUTO_INCREMENT ,
		time bigint NOT NULL ,
		sent_by bigint UNSIGNED NOT NULL ,
		from varchar(255) NOT NULL ,
		from_name text NOT NULL ,
		subject text NOT NULL ,
		message longtext NOT NULL ,
		membership tinyint UNSIGNED NOT NULL ,
		receipient_group varchar(255) NOT NULL ,
		receipient_id int UNSIGNED NOT NULL ,
		format varchar(255) NOT NULL ,
		status tinyint UNSIGNED NOT NULL ,
		receipient_type varchar(255) NOT NULL ,
		UNIQUE KEY id (id)
	) DEFAULT CHARACTER SET = utf8 COLLATE = utf8_general_ci;";
	$sql[] = "CREATE TABLE " . $wpdb->prefix . "vca_asm_emails_queue (
		id int UNSIGNED NOT NULL AUTO_INCREMENT ,
		mail_id int UNSIGNED NOT NULL ,
		receipients longtext NOT NULL ,
		total_receipients int UNSIGNED NOT NULL ,
		UNIQUE KEY id (id)
	) DEFAULT CHARACTER SET = utf8 COLLATE = utf8_general_ci;";
	$sql[] = "CREATE TABLE " . $wpdb->prefix . "vca_asm_finances_accounts (
		UNIQUE KEY id (id)
	) DEFAULT CHARACTER SET = utf8 COLLATE = utf8_general_ci;";
	$sql[] = "CREATE TABLE " . $wpdb->prefix . "vca_asm_finances_meta (
		UNIQUE KEY id (id)
	) DEFAULT CHARACTER SET = utf8 COLLATE = utf8_general_ci;";
	$sql[] = "CREATE TABLE " . $wpdb->prefix . "vca_asm_finances_transactions (
		UNIQUE KEY id (id)
	) DEFAULT CHARACTER SET = utf8 COLLATE = utf8_general_ci;";
	$sql[] = "CREATE TABLE " . $wpdb->prefix . "vca_asm_geography (
		id int UNSIGNED NOT NULL AUTO_INCREMENT ,
		name varchar(255) NOT NULL ,
		type tinytext NOT NULL ,
		members int NOT NULL ,
		supporters int NOT NULL ,
		has_user tinyint UNSIGNED ,
		user_id bigint NOT NULL ,
		user varchar(255) NOT NULL ,
		pass varchar(255) NOT NULL ,
		phone_code int UNSIGNED NOT NULL,
		alpha_code tinytext NOT NULL ,
		currency_name text NOT NULL ,
		currency_code tinytext NOT NULL ,
		UNIQUE KEY id (id)
    ) DEFAULT CHARACTER SET = utf8 COLLATE = utf8_general_ci;";
	$sql[] = "CREATE TABLE " . $wpdb->prefix . "vca_asm_geography_hierarchy (
		id int UNSIGNED NOT NULL AUTO_INCREMENT ,
		ancestor int UNSIGNED NOT NULL ,
		ancestor_type tinytext NOT NULL ,
		descendant int UNSIGNED NOT NULL ,
		UNIQUE KEY id (id)
    ) DEFAULT CHARACTER SET = utf8 COLLATE = utf8_general_ci;";
	$sql[] = "CREATE TABLE " . $wpdb->prefix . "vca_asm_registrations (
		id int UNSIGNED NOT NULL AUTO_INCREMENT ,
		supporter int UNSIGNED NOT NULL ,
		activity int UNSIGNED NOT NULL ,
		contingent int UNSIGNED ,
		notes text NOT NULL ,
		UNIQUE KEY id (id)
	) DEFAULT CHARACTER SET = utf8 COLLATE = utf8_general_ci;";
	$sql[] = "CREATE TABLE " . $wpdb->prefix . "vca_asm_registrations_old (
		id int UNSIGNED NOT NULL AUTO_INCREMENT ,
		supporter int UNSIGNED NOT NULL ,
		activity int UNSIGNED NOT NULL ,
		quota int UNSIGNED ,
		notes text NOT NULL ,
		UNIQUE KEY id (id)
	) DEFAULT CHARACTER SET = utf8 COLLATE = utf8_general_ci;";

	/* comparison of above with db, db adjustments */
	require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
	dbDelta( $sql );

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
		$last_reset = get_user_meta( $user->ID, 'vca_asm_last_pass_reset', true );
		if ( empty( $last_reset ) ) {
			update_user_meta( $user->ID, 'vca_asm_last_pass_reset', time() );
		}
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

	/* set up custom taxonomies */
	add_action( 'init', 'vca_asm_setup_taxonomies' );

	/* update db version number */
   update_option( 'vca_asm_db_version', $vca_asm_db_version );
}

function vca_asm_setup_taxonomies() {
	$activities_id = NULL;
	if ( ! is_category( 'activities' ) ) {
		$activities_id = wp_create_category( array(
			'cat_name' => 'Activities',
			'category_description' => "Parent Category to group all of VcA's Activities",
			'category_nicename' => 'activites'
		) );
	}
	if ( ! empty( $activities_id ) ) {
		if ( ! is_category( 'actions' ) ) {
			wp_create_category( array(
				'cat_name' => 'Actions',
				'category_description' => "Activities of the Action-Department",
				'category_nicename' => 'actions',
				'category_parent' => $activities_id
			) );
		}
		if ( ! is_category( 'education' ) ) {
			wp_create_category( array(
				'cat_name' => 'Education',
				'category_description' => "Activities of the Education-Department",
				'category_nicename' => 'education',
				'category_parent' => $activities_id
			) );
		}
		if ( ! is_category( 'network' ) ) {
			wp_create_category( array(
				'cat_name' => 'Network',
				'category_description' => "Activities of the Network-Department",
				'category_nicename' => 'network',
				'category_parent' => $activities_id
			) );
		}
	}
}

function vca_asm_update_db_check() {
    global $vca_asm_db_version;
    if( get_site_option( 'vca_asm_db_version' ) != $vca_asm_db_version ) {
        vca_asm_install();
    }
}

function vca_asm_clear_cron() {
	$vca_asm_tmp_cron =  new VCA_ASM_Cron();

	foreach ( $vca_asm_tmp_cron->hooks as $hook ) {
		$timestamp = wp_next_scheduled( $hook );
		wp_unschedule_event( $timestamp, $hook );
	}
}

add_action( 'plugins_loaded', 'vca_asm_update_db_check' );
register_activation_hook( VCA_ASM_FILE, 'vca_asm_install' );
register_deactivation_hook( VCA_ASM_FILE, 'vca_asm_clear_cron' );

?>
