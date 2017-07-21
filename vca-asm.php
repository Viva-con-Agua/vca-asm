<?php

/*
Plugin Name: VcA Activity & Supporter Management
Plugin URI: http://pool.vivaconagua.org
Description: Tool for Viva con Agua (NGO) to manage its supporters and the activities within their network
Version: 1.6.1
Author: Johannes Pilkahn
Author URI: http://karlgehttanzen.de
License: GPL3
*/

/**
 * Copyright 2011  Johannes Pilkahn  (email : j.pilkahn@vivaconagua.org)
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License, version 3, as
 * published by the Free Software Foundation.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
 *
 * @author     Johannes Pilkahn <j.pilkahn@vivaconagua.org>
 * @copyright  2011-2015 Johannes Pilkahn
 * @license    http://www.gnu.org/licenses/gpl-3.0.en.html  GPL v3
 * @version    Release: 1.6
 * @link       http://pool.vivaconagua.org
 */

/**
 * This file is the root of the plugin,
 * initially read by WordPress.
 *
 * It sets a few global constants,
 * conditionally includes/requires other logical files,
 * sets the locale,
 * holds an installation routine (and its triggering hook),
 * holds a routine to update the database (and its triggering hook),
 * as well as a clean up routine run on plugin deactivation.
 */

/**
 * Holds the absolute location of the main plugin file (this file)
 *
 * @var const VCA_ASM_FILE
 * @since 1.0
 */
if ( ! defined( 'VCA_ASM_FILE' ) ) {
	define( 'VCA_ASM_FILE', __FILE__ );
}

if ( ! defined( 'LC_MESSAGES' ) ) {
	define( 'LC_MESSAGES', 5 );
}

/**
 * Holds the absolute location of VcA Activity & Supporter Management
 *
 * @var const VCA_ASM_ABSPATH
 * @since 1.0
 */
if ( ! defined( 'VCA_ASM_ABSPATH' ) ) {
	define( 'VCA_ASM_ABSPATH', dirname( __FILE__ ) );
}

/**
 * Holds the URL of VcA Activity & Supporter Management
 *
 * @var const VCA_ASM_RELPATH
 * @since 1.0
 */
if ( ! defined( 'VCA_ASM_RELPATH' ) ) {
	define( 'VCA_ASM_RELPATH', plugin_dir_url( __FILE__ ) );
}

/**
 * Holds the name of the VcA Activity & Supporter Management directory
 *
 * @var const VCA_ASM_DIRNAME
 * @since 1.0
 */
if ( !defined( 'VCA_ASM_DIRNAME' ) ) {
	define( 'VCA_ASM_DIRNAME', basename( VCA_ASM_ABSPATH ) );
}

/**
 * Utility Constant: Profile URI
 *
 * @var const VCA_ASM_PROFILE_URI
 * @since 1.6
 */
if ( !defined( 'VCA_ASM_PROFILE_URI' ) ) {
	define( 'VCA_ASM_PROFILE_URI', 'profile' );
}

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
require_once( VCA_ASM_ABSPATH . '/includes/class-vca-asm-list-activities.php' );
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

/* 2nd party code */
require_once( VCA_ASM_ABSPATH . '/lib/PHPExcel.php' );

/* Download Generators */
require_once( VCA_ASM_ABSPATH . '/models/class-vca-asm-workbook.php' );
require_once( VCA_ASM_ABSPATH . '/models/class-vca-asm-workbook-finances.php' );
require_once( VCA_ASM_ABSPATH . '/models/class-vca-asm-workbook-participants.php' );

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
 * @return void
 *
 * @since 1.3
 */
function vca_asm_user_locale()
{
	add_filter( 'locale', 'vca_asm_set_locale', 1 );
}

/**
 * Sets the locale depending on a user's settings
 *
 * @return void
 *
 * @since 1.3
 */
function vca_asm_set_locale( $locale )
{
	$current_user = wp_get_current_user();
	
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
 * @global string $vca_asm_db_version
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
function vca_asm_install()
{
   global $wpdb, $vca_asm_db_version;

	/* SQL statements to create required tables */
	$sql = array();
	$sql[] = "CREATE TABLE " . $wpdb->prefix . "vca_asm_applications (
		id int UNSIGNED NOT NULL AUTO_INCREMENT ,
		supporter int UNSIGNED NOT NULL ,
		activity int UNSIGNED NOT NULL ,
		state tinyint UNSIGNED NOT NULL ,
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
		scope varchar(255) NOT NULL ,
		action tinytext NOT NULL ,
		switch tinyint UNSIGNED NOT NULL ,
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
		id int UNSIGNED NOT  NULL AUTO_INCREMENT ,
		city_id int UNSIGNED NOT  NULL ,
		type varchar(255) NOT NULL ,
		balance int NOT  NULL ,
		last_updated varchar(255) NOT NULL ,
		balanced_month varchar(255) NOT NULL ,
		last_receipt varchar(255) NOT NULL ,
		total int NOT  NULL ,
		UNIQUE KEY id (id)
	) DEFAULT CHARACTER SET = utf8 COLLATE = utf8_general_ci;";
	$sql[] = "CREATE TABLE " . $wpdb->prefix . "vca_asm_finances_meta (
		id int UNSIGNED NOT  NULL AUTO_INCREMENT ,
		type varchar(255) NOT NULL ,
		name varchar(255) NOT NULL ,
		value varchar(255) NOT NULL ,
		description text NOT NULL ,
		related_id int UNSIGNED NOT  NULL ,
		UNIQUE KEY id (id)
	) DEFAULT CHARACTER SET = utf8 COLLATE = utf8_general_ci;";
	$sql[] = "CREATE TABLE " . $wpdb->prefix . "vca_asm_finances_transactions (
		id int UNSIGNED NOT  NULL AUTO_INCREMENT ,
		city_id int UNSIGNED NOT  NULL ,
		amount int NOT  NULL ,
		account_type varchar(255) NOT NULL ,
		transaction_type varchar(255) NOT NULL ,
		transaction_date varchar(255) NOT NULL ,
		entry_time varchar(255) NOT NULL ,
		receipt_date varchar(255) NOT NULL ,
		receipt_id varchar(255) NOT NULL ,
		receipt_status tinyint NOT NULL ,
		cash tinyint NOT NULL ,
		cost_center varchar(255) NOT NULL ,
		ei_account varchar(255) NOT NULL ,
		meta_1 text NOT NULL ,
		meta_2 text NOT NULL ,
		meta_3 text NOT NULL ,
		meta_4 text NOT NULL ,
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
					'scope' => 'ge',
					'switch' => 1
				),
				array( '%s', '%s', '%d' )
			);
		}
	}

	/* set up custom taxonomies */
	add_action( 'init', 'vca_asm_setup_taxonomies' );

	/* update db version number */
   update_option( 'vca_asm_db_version', $vca_asm_db_version );
}

/**
 * Factorised part of installation routine.
 * Sets up taxonomies for custom post types.
 *
 * @since 1.0
 */
function vca_asm_setup_taxonomies()
{
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

/**
 * Compares the DB version number saved as an option in the database
 * with the one defined above in this file.
 *
 * @since 1.0
 */
function vca_asm_update_db_check()
{
    global $vca_asm_db_version;
    if( get_site_option( 'vca_asm_db_version' ) != $vca_asm_db_version ) {
        vca_asm_install();
    }
}

/**
 * Runs when the plugin is deactivated/uninstalled.
 * Garbage collection of the WordPress core's pseudo-cron.
 *
 * @since 1.0
 */
function vca_asm_clear_cron()
{
	$vca_asm_tmp_cron =  new VCA_ASM_Cron();

	foreach ( $vca_asm_tmp_cron->hooks as $hook ) {
		$timestamp = wp_next_scheduled( $hook );
		wp_unschedule_event( $timestamp, $hook );
	}
}

/**
 * Hooks
 *
 * @since 1.0
 */
add_action( 'plugins_loaded', 'vca_asm_update_db_check' );
register_activation_hook( VCA_ASM_FILE, 'vca_asm_install' );
register_deactivation_hook( VCA_ASM_FILE, 'vca_asm_clear_cron' );

?>