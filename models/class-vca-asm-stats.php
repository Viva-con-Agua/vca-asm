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
	public $supporters_total_city = 0;
	public $supporters_active_city = 0;
	public $supporters_applied_city = 0;
	public $supporters_inactive_city = 0;
	public $supporters_incomplete_city = 0;
	public $admins_total = 0;
	public $admins_city = 0;
	public $cities_total = 0;
	public $cities_cells = 0;
	public $cities_crews = 0;
	public $regions_total = 0;
	public $regions_cells = 0;
	public $regions_crews = 0;
	public $city_groups = 0;
	public $countries = 0;
	public $country_groups = 0;
	public $activities_count = array(
		'all' => array(
			'total' => 0,
			'upcoming' => 0,
			'appphase' => 0,
			'current' => 0,
			'past' => 0
		)
	);


	/**
	 * Assigns values to class properties
	 *
	 * @since 1.2
	 * @access public
	 */
	public function gather_stats() {
		global $current_user, $wpdb, $vca_asm_activities, $vca_asm_geography;
		get_currentuserinfo();

		$admin_region = get_user_meta( $current_user->ID, 'region', true );
		$user_count = count_users();
		$this->supporters_total_total = $user_count['avail_roles']['supporter'];
		$this->supporters_total_city = count( get_users( array(
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
		$this->admins_total = $user_count['total_users'] - ( $user_count['avail_roles']['supporter'] + $user_count['avail_roles']['city'] );
		$users_city = count( get_users( array(
				'meta_query' => array(
				array(
					'key' => 'region',
					'value' => $admin_region,
					'compare' => '=',
					'type' => 'numeric'
				)
			)
		) ) );$city_users_city = count( get_users( array(
			'role' => 'city',
			'meta_query' => array(
				array(
					'key' => 'region',
					'value' => $admin_region,
					'compare' => '=',
					'type' => 'numeric'
				)
			)
		) ) );
		$this->admins_city = $users_city - ( $city_users_city + $this->supporters_total_city );

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
		$this->supporters_active_city = count( get_users( array(
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
		$this->supporters_applied_city = count( get_users( array(
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
					$this->supporters_inactive_city++;
				}
			}
			$supp_fname = get_user_meta( $supporter->ID, 'first_name', true );
			$supp_lname = get_user_meta( $supporter->ID, 'last_name', true );
			if( empty( $supp_fname ) || empty( $supp_lname ) ) {
				$this->supporters_incomplete_total++;
				if( $admin_region === get_user_meta( $supporter->ID, 'region', true ) ) {
					$this->supporters_incomplete_city++;
				}
			}
		}

		$this->cities_total = $wpdb->get_var(
			"SELECT COUNT(id) FROM " .
			$wpdb->prefix . "vca_asm_geography " .
			"WHERE type = 'cell' OR type = 'lc' OR type = 'city'"
		);
		$this->regions_total = $this->cities_total;
		$this->cities_cells = $wpdb->get_var(
			"SELECT COUNT(id) FROM " .
			$wpdb->prefix . "vca_asm_geography " .
			"WHERE type = 'cell'"
		);
		$this->regions_cells = $this->cities_cells;
		$this->cities_crews = $wpdb->get_var(
			"SELECT COUNT(id) FROM " .
			$wpdb->prefix . "vca_asm_geography " .
			"WHERE type = 'lc'"
		);
		$this->regions_crews = $this->cities_crews;
		$this->city_groups = $wpdb->get_var(
			"SELECT COUNT(id) FROM " .
			$wpdb->prefix . "vca_asm_geography " .
			"WHERE type = 'cg'"
		);
		$this->countries = $wpdb->get_var(
			"SELECT COUNT(id) FROM " .
			$wpdb->prefix . "vca_asm_geography " .
			"WHERE type = 'nation'"
		);
		$this->country_groups = $wpdb->get_var(
			"SELECT COUNT(id) FROM " .
			$wpdb->prefix . "vca_asm_geography " .
			"WHERE type = 'ng'"
		);

		if ( is_array( $vca_asm_activities->activity_types ) && ! empty( $vca_asm_activities->activity_types ) ) {

			foreach ( $vca_asm_activities->activity_types as $activity_type ) {

				$total = count( get_posts( array(
					'post_type' => $activity_type,
					'post_status' => 'publish',
					'numberposts' => 99999
				) ) );
				$upcoming = count( get_posts( array(
					'post_type' => $activity_type,
					'post_status' => 'publish',
					'numberposts' => 99999,
					'meta_query' => array(
						array(
							'key' => 'start_act',
							'value' => time(),
							'compare' => '>',
							'type' => 'numeric'
						)
					)
				) ) );
				$appphase = count( get_posts( array(
					'post_type' => $activity_type,
					'post_status' => 'publish',
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
				$current = count( get_posts( array(
					'post_type' => $activity_type,
					'post_status' => 'publish',
					'numberposts' => 99999,
					'meta_query' => array(
						'relation' => 'AND',
						array(
							'key' => 'start_act',
							'value' => time(),
							'compare' => '<=',
							'type' => 'numeric'
						),
						array(
							'key' => 'end_act',
							'value' => time(),
							'compare' => '>=',
							'type' => 'numeric'
						)
					)
				) ) );
				$past = count( get_posts( array(
					'post_type' => $activity_type,
					'post_status' => 'publish',
					'numberposts' => 99999,
					'meta_query' => array(
						array(
							'key' => 'end_act',
							'value' => time(),
							'compare' => '<',
							'type' => 'numeric'
						)
					)
				) ) );

				$this->activities_count['all']['total'] = $this->activities_count['all']['total'] + $total;
				$this->activities_count['all']['upcoming'] = $this->activities_count['all']['upcoming'] + $upcoming;
				$this->activities_count['all']['appphase'] = $this->activities_count['all']['appphase'] + $appphase;
				$this->activities_count['all']['current'] = $this->activities_count['all']['current'] + $current;
				$this->activities_count['all']['past'] = $this->activities_count['all']['past'] + $past;

				$this->activities_count[$activity_type] = array();
				$this->activities_count[$activity_type]['total'] = $total;
				$this->activities_count[$activity_type]['upcoming'] = $upcoming;
				$this->activities_count[$activity_type]['appphase'] = $appphase;
				$this->activities_count[$activity_type]['current'] = $current;
				$this->activities_count[$activity_type]['past'] = $past;
			}

		}
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