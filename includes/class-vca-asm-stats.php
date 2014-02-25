<?php

/**
 * VCA_ASM_Stats class.
 *
 * This class contains properties and methods for
 * the activities management
 *
 * @package VcA Activity & Supporter Management
 * @since 1.2
 */

if ( ! class_exists( 'VCA_ASM_Stats' ) ) :

class VCA_ASM_Stats {

	/**
	 * Class Properties
	 *
	 * @since 1.2
	 */
	public $supporters_total_total = 0;
	public $supporters_active_total = 0;
	public $supporters_applied_total = 0;
	public $supporters_inactive_total = 0;
	public $supporters_incomplete_total = 0;
	public $supporters_total_region = 0;
	public $supporters_active_region = 0;
	public $supporters_applied_region = 0;
	public $supporters_inactive_region = 0;
	public $supporters_incomplete_region = 0;
	public $regions_total = 0;
	public $regions_cells = 0;
	public $regions_crews = 0;
	public $activities_total_total = 0;
	public $activities_total_upcoming = 0;
	public $activities_total_appphase = 0;
	public $activities_total_current = 0;
	public $activities_total_past = 0;
	public $activities_festivals_total = 0;
	public $activities_festivals_upcoming = 0;
	public $activities_festivals_appphase = 0;
	public $activities_festivals_current = 0;
	public $activities_festivals_past = 0;


	/**
	 * Assigns values to class properties
	 *
	 * @since 1.2
	 * @access public
	 */
	public function gather_stats() {
		global $current_user, $wpdb, $vca_asm_regions;
		get_currentuserinfo();

		$admin_region = get_user_meta( $current_user->ID, 'region', true );
		$user_count = count_users();
		$this->supporters_total_total = $user_count['avail_roles']['supporter'];
		$this->supporters_total_region = count( get_users( array(
			'role' => 'supporter',
			'meta_query' => array(
				array(
					'key' => 'region',
					'value' => $admin_region,
					'compare' => '=',
					'type' => 'numeric'
				)
			)
		) ) );
		$this->supporters_active_total = count( get_users( array(
			'role' => 'supporter',
			'meta_query' => array(
				array(
					'key' => 'membership',
					'value' => 2,
					'compare' => '=',
					'type' => 'numeric'
				)
			)
		) ) );
		$this->supporters_active_region = count( get_users( array(
			'role' => 'supporter',
			'meta_query' => array(
				'relation' => 'AND',
				array(
					'key' => 'membership',
					'value' => 2,
					'compare' => '=',
					'type' => 'numeric'
				),
				array(
					'key' => 'region',
					'value' => $admin_region,
					'compare' => '=',
					'type' => 'numeric'
				)
			)
		) ) );
		$this->supporters_applied_total = count( get_users( array(
			'role' => 'supporter',
			'meta_query' => array(
				array(
					'key' => 'membership',
					'value' => 1,
					'compare' => '=',
					'type' => 'numeric'
				)
			)
		) ) );
		$this->supporters_applied_region = count( get_users( array(
			'role' => 'supporter',
			'meta_query' => array(
				'relation' => 'AND',
				array(
					'key' => 'membership',
					'value' => 1,
					'compare' => '=',
					'type' => 'numeric'
				),
				array(
					'key' => 'region',
					'value' => $admin_region,
					'compare' => '=',
					'type' => 'numeric'
				)
			)
		) ) );

		$inactive = get_users( array( 'role' => 'supporter' ) );
		foreach( $inactive as $supporter ) {
			$mem = get_user_meta( $supporter->ID, 'membership', true );
			if( empty( $mem ) ) {
				$this->supporters_inactive_total++;
				if( $admin_region === get_user_meta( $supporter->ID, 'region', true ) ) {
					$this->supporters_inactive_region++;
				}
			}
			$supp_fname = get_user_meta( $supporter->ID, 'first_name', true );
			$supp_lname = get_user_meta( $supporter->ID, 'last_name', true );
			if( empty( $supp_fname ) || empty( $supp_lname ) ) {
				$this->supporters_incomplete_total++;
				if( $admin_region === get_user_meta( $supporter->ID, 'region', true ) ) {
					$this->supporters_incomplete_region++;
				}
			}
		}

		$this->regions_total = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COUNT(id) FROM " .
				$wpdb->prefix . "vca_asm_regions"
			)
		);
		$this->regions_cells = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COUNT(id) FROM " .
				$wpdb->prefix . "vca_asm_regions " .
				"WHERE status = 'cell'"
			)
		);
		$this->regions_crews = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COUNT(id) FROM " .
				$wpdb->prefix . "vca_asm_regions " .
				"WHERE status = 'lc'"
			)
		);

		$this->activities_festivals_total = count( get_posts( array(
			'post_type'         =>	'festival',
			'post_status'       =>	'publish',
			'numberposts' => 99999
		) ) );
		$this->activities_festivals_upcoming = count( get_posts( array(
			'post_type'         =>	'festival',
			'post_status'       =>	'publish',
			'numberposts' => 99999,
			'meta_query' => array(
				array(
					'key' => 'start_date',
					'value' => time(),
					'compare' => '>',
					'type' => 'numeric'
				)
			)
		) ) );
		$this->activities_festivals_appphase = count( get_posts( array(
			'post_type'         =>	'festival',
			'post_status'       =>	'publish',
			'numberposts' => 99999,
			'meta_query' => array(
				'relation' => 'AND',
				array(
					'key' => 'start_app',
					'value' => time(),
					'compare' => '<=',
					'type' => 'numeric'
				),
				array(
					'key' => 'end_app',
					'value' => time() + 86400,
					'compare' => '>=',
					'type' => 'numeric'
				)
			)
		) ) );
		$this->activities_festivals_current = count( get_posts( array(
			'post_type'         =>	'festival',
			'post_status'       =>	'publish',
			'numberposts' => 99999,
			'meta_query' => array(
				'relation' => 'AND',
				array(
					'key' => 'start_date',
					'value' => time(),
					'compare' => '<=',
					'type' => 'numeric'
				),
				array(
					'key' => 'end_date',
					'value' => time() + 86400,
					'compare' => '>=',
					'type' => 'numeric'
				)
			)
		) ) );
		$this->activities_festivals_past = count( get_posts( array(
			'post_type'         =>	'festival',
			'post_status'       =>	'publish',
			'numberposts' => 99999,
			'meta_query' => array(
				array(
					'key' => 'end_date',
					'value' => time(),
					'compare' => '<',
					'type' => 'numeric'
				)
			)
		) ) );
		$this->activities_total_total = $this->activities_festivals_total;
		$this->activities_total_upcoming = $this->activities_festivals_upcoming;
		$this->activities_total_appphase = $this->activities_festivals_appphase;
		$this->activities_total_current = $this->activities_festivals_current;
		$this->activities_total_past = $this->activities_festivals_past;
	}

	/**
	 * PHP4 style constructor
	 *
	 * @since 1.0
	 * @access public
	 */
	public function VcA_ASM_Stats() {
		$this->__construct();
	}

	/**
	 * PHP5 style constructor
	 *
	 * @since 1.0
	 * @access public
	 */
	public function __construct() {
		$this->gather_stats();
	}

} // class

endif; // class exists

?>