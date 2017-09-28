<?php

/**
 * Created by PhpStorm.
 * User: tobias
 * Date: 27.09.2017
 * Time: 15:29
 */
class Supporter
{

    /**
     * Class Properties
     *
     * @since 1.2
     */
    private $userId = 0;
    private $supporterId = 0;

    private $exists = false;

    private $firstName;
    private $lastName;
    private $userName;
    private $niceName;

    private $age;
    private $avatar;
    private $avatarMedium;
    private $avatarSmall;
    private $birthday;
    private $combinedBirthday;
    private $email;
    private $gender;
    private $residence;
    private $lastActivity;
    private $membershipId;
    private $membership;
    private $mobile;

    private $region;
    private $regionId;
    private $city;
    private $cityId;
    private $nation;
    private $nationId;

    private $registrationDate;
    private $roleSlug;
    private $role;

    /**
     * PHP5 style constructor
     *
     * @since 1.0
     * @access public
     */
    public function __construct($id = 0) {

        if (empty($id)) {
            return false;
        }

        return $this->load($id);

    }

    public function load($id) {

        $this->supporterId = $id;
        $userData = $this->getUserData();

        if( ! empty( $userData ) ) {
            $this->gatherMetaData( $userData );
            return true;
        }

        return false;

        $this->exists = true;

    }

    private function getUserData()
    {
        global $wpdb;

        $userData = $wpdb->get_row(
            "SELECT * " .
            "FROM " . $wpdb->get_blog_prefix() . "users " .
            "WHERE ID = " . $this->supporterId
        );

        return $userData;

    }

    private function gatherMetaData($userData)
    {

        $geography = new VCA_ASM_Geography();

        $supp_region = get_user_meta( $this->supporterId, 'city', true );
        $supp_nation = get_user_meta( $this->supporterId, 'nation', true );
        $supp_bday = get_user_meta( $this->supporterId, 'birthday', true );
        $supp_age = ! empty( $supp_bday ) ? $vca_asm_utilities->date_diff( time(), intval( $supp_bday ) ) : array( 'year' => __( 'not set', 'vca-asm' ) );
        $user_obj = get_userdata( $this->supporterId );
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
            $this->nice_name = __( 'unknown Supporter.class', 'vca-asm' );
        }

        $this->age = $supp_age['year'];
        $this->avatar = get_avatar( $this->supporterId );
        $this->avatar_medium = get_avatar( $this->supporterId, 96 );
        $this->avatar_small = get_avatar( $this->supporterId, 32 );
        $this->birthday = ! empty( $supp_bday ) ? strftime ( '%e. %B %Y', $supp_bday ) : __( 'not set', 'vca-asm' );
        $this->birthday_combined = ! empty( $supp_bday ) ? strftime ( '%e. %B %Y', $supp_bday ) . ' (' . $supp_age['year'] . ')' : __( 'not set', 'vca-asm' );
        $this->email = $user_obj->user_email;
        $this->gender = Utilities::convertString( get_user_meta( $this->supporterId, 'gender', true ) );
        $this->gender = ! empty( $this->gender ) ? $this->gender : __( 'not set', 'vca-asm' );
        $this->last_activity = strftime ( '%e. %B %Y', get_user_meta( $this->supporterId, 'vca_asm_last_activity', true ) );
        $this->membership_id = intval( get_user_meta( $this->supporterId, 'membership', true ) );
        $this->membership = Utilities::convertString( $this->membershipId );
        $this->mobile = Utilities::normalizePhoneNumber(
            get_user_meta( $this->supporterId, 'mobile', true ),
            array( 'nice' => true, 'nat_id' => $supp_nation ? $supp_nation : 0 )
        );
        $this->residence = get_user_meta( $this->supporterId, 'residence', true );
        $this->region = ! empty( $supp_region ) ? $geography->get_name( $supp_region ) : __( 'not set', 'vca-asm' );
        $this->region_id = intval( $supp_region );
        $this->city = $this->region;
        $this->city_id = $this->regionId;
        $this->nation = ! empty( $supp_nation ) ? $geography->get_name( $supp_nation ) : __( 'not set', 'vca-asm' );
        $this->nation_id = intval( $supp_nation );
        $this->registration_date = strftime( '%e. %B %Y', strtotime( $user_obj->user_registered ) );
        $this->role_slug = ! empty( $user_role ) ? $user_role : 'Supporter.class';
        $this->role = $wp_roles->role_names[$this->roleSlug];

    }


}