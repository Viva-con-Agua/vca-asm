<?php

/**
 * VCA_ASM_Supporter class.
 *
 * An instance of this class holds all information on a single supporter
 *
 * @package VcA Activity & Supporter Management
 * @since 1.2
 */

if ( ! class_exists( 'VCA_ASM_Supporter' ) ) :

class VCA_ASM_Supporter {

	/**
	 * Class Properties
	 *
	 * @since 1.2
	 */
	public $ID = 0;
	public $supporter_id = 0;

	public $exists = false;

	public $first_name = '';
	public $last_name = '';
	public $user_name = '';
	public $nice_name = '';

	public $age = '';
	public $avatar = '';
	public $avatar_medium = '';
	public $avatar_small = '';
	public $birthday = '';
	public $birthday_combined = '';
	public $email = '';
	public $gender = '';
	public $residence = '';
	public $last_activity = '';
	public $membership_id = 0;
	public $membership = '';
	public $mobile = '';

	public $region = '';
	public $region_id = '';
	public $city = '';
	public $city_id = '';
	public $nation = '';
	public $nation_id = '';

	public $registration_date = '';
	public $role_slug = '';
	public $role = '';


	/**
	 * Checks whether a user of id exists
	 *
	 * @since 1.2
	 * @access public
	 */
	public function check_exists( $supporter_id  ) {
		global $wpdb;

		$login_query = $wpdb->get_results(
			"SELECT user_login " .
			"FROM " . $wpdb->prefix . "users " .
			"WHERE ID = " . $supporter_id,
			ARRAY_A
		);
		if( ! empty( $login_query[0]['user_login'] ) ) {
			$this->exists = true;
			$this->gather_meta( $supporter_id );
		}
	}

	/**
	 * Assigns values to class properties
	 *
	 * @since 1.2
	 * @access public
	 */
	public function gather_meta( $supporter_id ) {
		global $wpdb, $wp_roles, $vca_asm_geography, $vca_asm_utilities;

		$supp_region = get_user_meta( $supporter_id, 'city', true );
		$supp_nation = get_user_meta( $supporter_id, 'nation', true );
		$supp_bday = get_user_meta( $supporter_id, 'birthday', true );
		$supp_age = ! empty( $supp_bday ) ? $vca_asm_utilities->date_diff( time(), intval( $supp_bday ) ) : array( 'year' => __( 'not set', 'vca-asm' ) );
		$user_obj = get_userdata( $supporter_id );
		$user_roles = $user_obj->roles;
		$user_role = array_shift( $user_roles );

		$this->first_name = $user_obj->first_name;
		$this->last_name = $user_obj->last_name;
		$this->user_name = $user_obj->user_login;
		if( ! empty( $this->first_name ) && ! empty( $this->last_name ) ) {
			$this->nice_name = $this->first_name . ' ' . $this->last_name;
		} elseif( ! empty( $this->first_name ) ) {
			$this->nice_name = $this->first_name;
		} elseif( ! empty( $this->last_name ) ) {
			$this->nice_name = $this->last_name;
		} elseif( ! empty( $this->user_name ) ) {
			$this->nice_name = $this->user_name;
		} else {
			$this->nice_name = __( 'unknown Supporter', 'vca-asm' );
		}

		$this->age = $supp_age['year'];
		$this->avatar = get_avatar( $supporter_id );
		$this->avatar_medium = get_avatar( $supporter_id, 96 );
		$this->avatar_small = get_avatar( $supporter_id, 32 );
		$this->birthday = ! empty( $supp_bday ) ? strftime ( '%e. %B %Y', $supp_bday ) : __( 'not set', 'vca-asm' );
		$this->birthday_combined = ! empty( $supp_bday ) ? strftime ( '%e. %B %Y', $supp_bday ) . ' (' . $supp_age['year'] . ')' : __( 'not set', 'vca-asm' );
		$this->email = $user_obj->user_email;
		$this->gender = $vca_asm_utilities->convert_strings( get_user_meta( $supporter_id, 'gender', true ) );
		$this->gender = ! empty( $this->gender ) ? $this->gender : __( 'not set', 'vca-asm' );
		$this->last_activity = strftime ( '%e. %B %Y', time( get_user_meta( $supporter_id, 'vca_asm_last_activity', true ) ) );
		$this->membership_id = intval( get_user_meta( $supporter_id, 'membership', true ) );
		$this->membership = $vca_asm_utilities->convert_strings( $this->membership_id );
		$this->mobile = $vca_asm_utilities->normalize_phone_number(
							get_user_meta( $supporter_id, 'mobile', true ),
							array( 'nice' => true, 'nat_id' => $supp_nation ? $supp_nation : 0 )
						);
		$this->residence = get_user_meta( $supporter_id, 'residence', true );
		$this->region = ! empty( $supp_region ) ? $vca_asm_geography->get_name( $supp_region ) : __( 'not set', 'vca-asm' );
		$this->region_id = intval( $supp_region );
		$this->city = $this->region;
		$this->city_id = $this->region_id;
		$this->nation = ! empty( $supp_nation ) ? $vca_asm_geography->get_name( $supp_nation ) : __( 'not set', 'vca-asm' );
		$this->nation_id = intval( $supp_nation );
		$this->registration_date = strftime( '%e. %B %Y', strtotime( $user_obj->user_registered ) );
		$this->role_slug = ! empty( $user_role ) ? $user_role : 'supporter';
		$this->role = $wp_roles->role_names[$this->role_slug];
	}

	/**
	 * PHP5 style constructor
	 *
	 * @since 1.0
	 * @access public
	 */
	public function __construct( $supporter_id ) {
		$this->supporter_id = $supporter_id;
		$this->ID = $this->supporter_id;
		$this->check_exists( $this->supporter_id );
	}

} // class

endif; // class exists

?>