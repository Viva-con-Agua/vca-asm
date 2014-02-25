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
	public $supporter_id = 0;

	public $exists = false;

	public $first_name = '';
	public $last_name = '';
	public $nice_name = '';

	public $age = '';
	public $avatar = '';
	public $birthday = '';
	public $city = '';
	public $email = '';
	public $gender = '';
	public $last_activity = '';
	public $membership_id = 0;
	public $membership = '';
	public $mobile = '';
	public $region = '';
	public $region_id = '';
	public $registration_date = '';


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
		global $wpdb, $vca_asm_regions, $vca_asm_utilities;

		$supp_region = get_user_meta( $supporter_id, 'region', true );
		$supp_bday = get_user_meta( $supporter_id, 'birthday', true );
		$supp_age = ! empty( $supp_bday ) ? $vca_asm_utilities->date_diff( time(), intval( $supp_bday ) ) : array( 'year' => __( 'not set', 'vca-asm' ) );
		$user_obj = get_userdata( $supporter_id );

		$this->first_name = $user_obj->first_name;
		$this->last_name = $user_obj->last_name;
		if( ! empty( $supporter->first_name ) && ! empty( $supporter->last_name ) ) {
			$this->nice_name = $supporter->first_name . ' ' . $supporter->last_name;
		} elseif( ! empty( $supporter->first_name ) ) {
			$this->nice_name = $supporter->first_name;
		} elseif( ! empty( $supporter->last_name ) ) {
			$this->nice_name = $supporter->last_name;
		} else {
			$this->nice_name = __( 'unknown Supporter', 'vca-asm' );
		}

		$this->age = $supp_age['year'];
		$this->avatar = get_avatar( $supporter_id );
		$this->birthday = ! empty( $supp_bday ) ? strftime ( '%e. %B %Y', $supp_bday ) : __( 'not set', 'vca-asm' );
		$this->birthday_combined = ! empty( $supp_bday ) ? strftime ( '%e. %B %Y', $supp_bday ) . ' (' . $supp_age['year'] . ')' : __( 'not set', 'vca-asm' );
		$this->city = get_user_meta( $supporter_id, 'city', true );
		$this->city = ! empty( $this->city ) ? $this->city : __( 'not set', 'vca-asm' );
		$this->email = $user_obj->user_email;
		$this->gender = $vca_asm_utilities->convert_strings( get_user_meta( $supporter_id, 'gender', true ) );
		$this->gender = ! empty( $this->gender ) ? $this->gender : __( 'not set', 'vca-asm' );
		$this->last_activity = strftime ( '%e. %B %Y', time( get_user_meta( $supporter_id, 'vca_asm_last_activity', true ) ) );
		$this->membership_id = intval( get_user_meta( $supporter_id, 'membership', true ) );
		$this->membership = $vca_asm_utilities->convert_strings( $this->membership_id );
		$this->mobile = $vca_asm_utilities->normalize_phone_number( get_user_meta( $supporter_id, 'mobile', true ), true );
		$this->region = $vca_asm_regions->get_name($supp_region);
		$this->region_id = intval( $supp_region );
		$this->registration_date = strftime( '%e. %B %Y', strtotime( $user_obj->user_registered ) );
	}

	/**
	 * PHP4 style constructor
	 *
	 * @since 1.0
	 * @access public
	 */
	public function VcA_ASM_Stats( $supporter_id ) {
		$this->__construct( $supporter_id );
	}

	/**
	 * PHP5 style constructor
	 *
	 * @since 1.0
	 * @access public
	 */
	public function __construct( $supporter_id ) {
		$this->supporter_id = $supporter_id;
		$this->check_exists( $this->supporter_id );
		add_shortcode( 'vca-asm-supporter-vcard', array( &$this, 'supporter_vcard' ) );
	}

} // class

endif; // class exists

?>