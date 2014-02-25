<?php

/**
 * VCA_ASM_Roles class.
 *
 * This class contains properties and methods for
 * the management of user roles
 *
 * @package VcA Activity & Supporter Management
 * @since 1.3
 */

if ( ! class_exists( 'VCA_ASM_Roles' ) ) :

class VCA_ASM_Roles {

	/**
	 * Class Properties
	 *
	 * @since 1.3
	 */
	public $global_admin_roles = array(
		'actions_global',
		'education_global',
		'financial_global',
		'management_global',
		'network_global',
		'administrator'
	);
	public $admin_roles = array(
		'actions_global',
		'actions_national',
		'education_global',
		'education_national',
		'financial_global',
		'financial_national',
		'management_global',
		'management_national',
		'network_global',
		'network_national',
		'administrator'
	);
	public $translated_roles = array();

	/**
	 * This method returns an array of roles,
	 * that a user can manage
	 *
	 * @since 1.3
	 * @access public
	 */
	public function user_sub_roles( $user_id = 0 ) {
		global $current_user;
		get_currentuserinfo();

		$user_id = ! empty( $user_id ) ? $user_id : $current_user->ID;
		$user = $user_id === $current_user->ID ? $current_user : get_userdata( $user_id );

		if ( is_array( $user->roles ) && ! empty( $user->roles ) ) {
			$user_role = $user->roles[0];
		} else {
			$user_role = 'default';
		}

		switch ( $user_role ) {

			case 'actions_global':
				$sub_roles = array( 'supporter', 'actions_national', 'actions_global' );
			break;
			case 'actions_national':
				$sub_roles = array( 'supporter', 'actions_national' );
			break;

			case 'education_global':
				$sub_roles = array( 'supporter', 'education_national', 'education_global' );
			break;
			case 'education_national':
				$sub_roles = array( 'supporter', 'education_national' );
			break;

			case 'financial_global':
				$sub_roles = array( 'supporter', 'financial_national', 'financial_global' );
			break;
			case 'financial_national':
				$sub_roles = array( 'supporter', 'financial_national' );
			break;

			case 'network_global':
				$sub_roles = array( 'supporter', 'network_national', 'network_global' );
			break;
			case 'network_national':
				$sub_roles = array( 'supporter', 'network_national' );
			break;

			/* Management Role */
			case 'management_global':
			case 'administrator':
				$sub_roles = array(
					'supporter',
					'watchdog_national',
					'watchdog_global',
					'actions_national',
					'actions_global',
					'education_national',
					'education_global',
					'network_national',
					'network_global',
					'financial_national',
					'financial_global',
					'management_national',
					'management_global'
				);
			break;
			case 'management_national':
				$sub_roles = array(
					'supporter',
					'watchdog_national',
					'actions_national',
					'education_national',
					'network_national',
					'financial_national',
					'management_national'
				);
			break;

			default:
				$sub_roles = array();
		}

		return $sub_roles;
	}


	/**
	 * This method makes static DB entries of role names
	 * gettext translatable
	 *
	 * @since 1.3
	 * @access public
	 */
	public function translatable_role_names() {
		global $wp_roles;

		$roles = $wp_roles->roles;
		$role_names = $wp_roles->role_names;

		/* define the translatable roles here */
		$translated_roles = $this->translated_roles;

		foreach ( $roles as $role_slug => $role_info ) {
			if ( array_key_exists( $role_slug, $translated_roles ) ) {
				$roles[$role_slug]['name'] = $translated_roles[$role_slug];
				$role_names[$role_slug] = $translated_roles[$role_slug];
			}
		}

		$wp_roles->roles = $roles;
		$wp_roles->role_names = $role_names;

		return $roles;
	}

	/**
	 * PHP4 style constructor
	 *
	 * @since 1.3
	 * @access public
	 */
	public function VCA_ASM_Roles() {
		$this->__construct();
	}

	/**
	 * PHP5 style constructor
	 *
	 * @since 1.3
	 * @access public
	 */
	public function __construct() {
		/* define the translatable roles here */
		$this->translated_roles = array(
			'administrator' => _x( 'Pool Manager', 'Role Names', 'vca-asm' ) . ' (' . _x( 'admin', 'Role Scope', 'vca-asm' ) . ')',
			'supporter' => _x( 'Supporter', 'Role Names', 'vca-asm' ),
			'head_of' => _x( 'City User', 'Role Names', 'vca-asm' ),
			'city' => _x( 'City User', 'Role Names', 'vca-asm' ),
			'actions_national' => _x( 'Action Department', 'Role Names', 'vca-asm' ) . ' (' . _x( 'national', 'Role Scope', 'vca-asm' ) . ')',
			'actions_global' => _x( 'Action Department', 'Role Names', 'vca-asm' ) . ' (' . _x( 'global', 'Role Scope', 'vca-asm' ) . ')',
			'education_national' => _x( 'Education Department', 'Role Names', 'vca-asm' ) . ' (' . _x( 'national', 'Role Scope', 'vca-asm' ) . ')',
			'education_global' => _x( 'Education Department', 'Role Names', 'vca-asm' ) . ' (' . _x( 'global', 'Role Scope', 'vca-asm' ) . ')',
			'financial_national' => _x( 'Financial Department', 'Role Names', 'vca-asm' ) . ' (' . _x( 'national', 'Role Scope', 'vca-asm' ) . ')',
			'financial_global' => _x( 'Financial Department', 'Role Names', 'vca-asm' ) . ' (' . _x( 'global', 'Role Scope', 'vca-asm' ) . ')',
			'management_national' => _x( 'Pool Manager', 'Role Names', 'vca-asm' ) . ' (' . _x( 'national', 'Role Scope', 'vca-asm' ) . ')',
			'management_global' => _x( 'Pool Manager', 'Role Names', 'vca-asm' ) . ' (' . _x( 'global', 'Role Scope', 'vca-asm' ) . ')',
			'network_national' => _x( 'Network Department', 'Role Names', 'vca-asm' ) . ' (' . _x( 'national', 'Role Scope', 'vca-asm' ) . ')',
			'network_global' => _x( 'Network Department', 'Role Names', 'vca-asm' ) . ' (' . _x( 'global', 'Role Scope', 'vca-asm' ) . ')',
			'watchdog_national' => _x( 'Watchdog', 'Role Names', 'vca-asm' ) . ' (' . _x( 'national', 'Role Scope', 'vca-asm' ) . ')',
			'watchdog_global' => _x( 'Watchdog', 'Role Names', 'vca-asm' ) . ' (' . _x( 'global', 'Role Scope', 'vca-asm' ) . ')'
		);
	}

} // class

endif; // class exists

?>