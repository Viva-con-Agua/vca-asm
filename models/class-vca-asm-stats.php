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
	public $supporters_complete_total = 0;
	public $supporters_incomplete_total = 0;

	public $supporters_complete_under25 = 0;
	public $supporters_complete_over25 = 0;
	public $supporters_complete_under25_clean = 0;
	public $supporters_complete_over25_clean = 0;
	public $supporters_average_age = 0;

	public $supporters_total_city = 0;
	public $supporters_active_city = 0;
	public $supporters_applied_city = 0;
	public $supporters_inactive_city = 0;
	public $supporters_complete_city = 0;
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
	public function set_properties() {
		//delete_transient( 'vca-asm-statistics' );
		if ( ! get_transient( 'vca-asm-statistics' ) ) {
			$this->gather_global_stats();
			set_transient( 'vca-asm-statistics', 1, 60*60*24 );
		} else {

			global $wpdb;

			$stats = $wpdb->get_results(
				"SELECT cache FROM " .
				$wpdb->prefix . "vca_asm_cache " .
				"WHERE handle = 'stats' LIMIT 1", ARRAY_A
			);

			$cached_stats = unserialize( $stats[0]['cache'] );

			foreach ( $cached_stats as $cached_stat_key => $cached_stat_value ) {
				$this->{$cached_stat_key} = $cached_stat_value;
			}

			$this->gather_local_stats();
		}
	}

	/**
	 * Gather global statistics
	 *
	 * @since 1.2
	 * @access public
	 */
	public function gather_global_stats() {
		global $current_user, $wpdb, $vca_asm_activities, $vca_asm_geography, $vca_asm_utilities;

		$admin_city = get_user_meta( $current_user->ID, 'city', true );
		$user_count = count_users();
		$this->supporters_total_total = $user_count['avail_roles']['supporter'];
		$this->supporters_total_city = count( get_users( array(
			'role' => 'supporter',
			'meta_query' => array(
				array(
					'key' => 'city',
					'value' => $admin_city,
					'compare' => '=',
					'type' => 'numeric'
				)
			)
		) ) );
		$this->admins_total = $user_count['total_users'] - ( $user_count['avail_roles']['pending'] + $user_count['avail_roles']['supporter'] + $user_count['avail_roles']['city'] );
		$users_city = count( get_users( array(
				'meta_query' => array(
				array(
					'key' => 'city',
					'value' => $admin_city,
					'compare' => '=',
					'type' => 'numeric'
				)
			)
		) ) );
		$city_users_city = count( get_users( array(
			'role' => 'city',
			'meta_query' => array(
				array(
					'key' => 'city',
					'value' => $admin_city,
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
					'key' => 'city',
					'value' => $admin_city,
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
					'key' => 'city',
					'value' => $admin_city,
					'compare' => '=',
					'type' => 'numeric'
				)
			)
		) ) );

		$inactive = get_users( array( 'role' => 'supporter' ) );
		$age_outliers = 0;
		$ages_sum = 0;
		$ages_sum_clean = 0;
		foreach( $inactive as $supporter ) {
			$mem = get_user_meta( $supporter->ID, 'membership', true );
			$supp_fname = get_user_meta( $supporter->ID, 'first_name', true );
			$supp_lname = get_user_meta( $supporter->ID, 'last_name', true );
			$supp_city = get_user_meta( $supporter->ID, 'city', true );
			$supp_bday = get_user_meta( $supporter->ID, 'birthday', true );
			if( empty( $mem ) ) {
				$this->supporters_inactive_total++;
				if( $admin_city === $supp_city ) {
					$this->supporters_inactive_city++;
				}
			}
			if( empty( $supp_fname ) || empty( $supp_lname ) || empty( $supp_bday ) ) {
				$this->supporters_incomplete_total++;
				if( $admin_city === $supp_city ) {
					$this->supporters_incomplete_city++;
				}
			} else {
				$this->supporters_complete_total++;
				if( $admin_city === $supp_city ) {
					$this->supporters_complete_city++;
				}
				$supp_age = $vca_asm_utilities->date_diff( time(), intval( $supp_bday ) );
				$ages_sum = $ages_sum + intval($supp_age['year']);
				if ( 16 < $supp_age['year'] && $supp_age['year'] < 41 ) {
					$ages_sum_clean = $ages_sum_clean + intval($supp_age['year']);
					if ( 25 > $supp_age['year'] ) {
						$this->supporters_complete_under25_clean++;
					} else {
						$this->supporters_complete_over25_clean++;
					}
				} else {
					$age_outliers++;
				}
				if ( 25 > $supp_age['year'] ) {
					$this->supporters_complete_under25++;
				} else {
					$this->supporters_complete_over25++;
				}
			}
		}

		$this->supporters_average_age = round( $ages_sum_clean / ( $this->supporters_complete_total - $age_outliers ), 2 );

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

		$to_be_cached = serialize( get_object_vars( $this ) );

		$exists = $wpdb->get_results(
			"SELECT handle FROM " .
			$wpdb->prefix . "vca_asm_cache " .
			"WHERE handle = 'stats' LIMIT 1", ARRAY_A
		);

		if ( ! empty( $exists ) ) {

			$wpdb->update(
				$wpdb->prefix.'vca_asm_cache',
				array( 'cache' => $to_be_cached ),
				array( 'handle'=> 'stats' ),
				array( '%s' ),
				array( '%s' )
			);

		} else {

			$wpdb->insert(
				$wpdb->prefix.'vca_asm_cache',
				array( 'cache' => $to_be_cached, 'handle'=> 'stats' ),
				array( '%s', '%s' )
			);

		}
	}

	/**
	 * Gather local statistics
	 *
	 * @since 1.4
	 * @access public
	 */
	public function gather_local_stats() {
		global $current_user;

		$admin_city = get_user_meta( $current_user->ID, 'city', true );
		$all_city_users = get_users( array(
			'role' => 'supporter',
			'meta_query' => array(
				array(
					'key' => 'city',
					'value' => $admin_city,
					'compare' => '=',
					'type' => 'numeric'
				)
			)
		));
		$this->supporters_total_city = count( $all_city_users );
		$users_city = count( get_users( array(
				'meta_query' => array(
				array(
					'key' => 'city',
					'value' => $admin_city,
					'compare' => '=',
					'type' => 'numeric'
				)
			)
		) ) );
		$city_users_city = count( get_users( array(
			'role' => 'city',
			'meta_query' => array(
				array(
					'key' => 'city',
					'value' => $admin_city,
					'compare' => '=',
					'type' => 'numeric'
				)
			)
		) ) );
		$this->admins_city = $users_city - ( $city_users_city + $this->supporters_total_city );

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
					'key' => 'city',
					'value' => $admin_city,
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
					'key' => 'city',
					'value' => $admin_city,
					'compare' => '=',
					'type' => 'numeric'
				)
			)
		) ) );

		foreach( $all_city_users as $supporter ) {
			$mem = get_user_meta( $supporter->ID, 'membership', true );
			$supp_fname = get_user_meta( $supporter->ID, 'first_name', true );
			$supp_lname = get_user_meta( $supporter->ID, 'last_name', true );
			if( empty( $mem ) ) {
				$this->supporters_inactive_city++;
			}
			if( empty( $supp_fname ) || empty( $supp_lname ) || empty( $supp_bday ) ) {
				$this->supporters_incomplete_city++;
			} else {
				$this->supporters_complete_city++;
			}
		}
	}

	/**
	 * Constructor
	 *
	 * @since 1.0
	 * @access public
	 */
	public function __construct() {
		$this->set_properties();
	}

} // class

endif; // class exists

?>