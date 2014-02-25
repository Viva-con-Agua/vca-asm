<?php

/**
 * VCA_ASM_Admin_Update class.
 *
 * This class contains properties and methods to
 * update the data structure of the Pool
 *
 * This is subject to constant change
 *
 * @package VcA Activity & Supporter Management
 * @since 1.3
 */

if ( ! class_exists( 'VCA_ASM_Admin_Update' ) ) :

class VCA_ASM_Admin_Update {

	/**
	 * Admin Menu
	 *
	 * @since 1.3
	 * @access public
	 */
	public function admin_menu() {
		add_submenu_page(
			'vca-asm-settings',
			'Update',
			'Update',
			'vca_asm_set_mode',
			'vca-asm-update',
			array( &$this, 'control' )
		);
	}

	/**
	 * Update Routine and executing page & button
	 *
	 * @since 1.3
	 * @access public
	 */
	public function control() {
		global $wpdb, $vca_asm_geography, $vca_asm_admin, $vca_asm_activities;

		$messages = array();

		if ( isset( $_GET['todo'] ) && 'update' === $_GET['todo'] ) {
			$updated = true;

			$users = get_users();

			//$cities = $vca_asm_geography->get_all( 'name', 'ASC', 'city' );
			//$cts = array();
			//foreach ( $cities as $ct ) {
			//	$cts[] = $ct['id'];
			//}
			//$nations = $vca_asm_geography->get_all( 'name', 'ASC', 'nation' );
			//$nts = array();
			//foreach ( $nations as $nt ) {
			//	$nts[] = $nt['id'];
			//}

			$cases = 0;
			$cases2 = 0;
			$vals = array();
			$vals2 = array();

			foreach ( $users as $user ) {
				$city = get_user_meta( $user->ID, 'city', true );
				$region = get_user_meta( $user->ID, 'region', true );
				$nation = get_user_meta( $user->ID, 'nation', true );
				$supp_fname = get_user_meta( $user->ID, 'first_name', true );
				$supp_lname = get_user_meta( $user->ID, 'last_name', true );

				if ( empty( $supp_fname ) || empty( $supp_lname ) ) {
					//update_user_meta( $user->ID, 'nation', NULL );
					//update_user_meta( $user->ID, 'city', 0 );
					//update_user_meta( $user->ID, 'region', 0 );
					//update_user_meta( $user->ID, 'membership', 0 );
					$cases++;
				}
				if ( empty( $nation ) && 0 !== $nation && '0' !== $nation ) {
					//update_user_meta( $user->ID, 'nation', NULL );
					$cases2++;
				}
			}

			print '<pre>$cases = '
    . htmlspecialchars( print_r( $cases, TRUE ), ENT_QUOTES, 'utf-8', FALSE )
    . "</pre>\n";

			print '<pre>$cases2 = '
    . htmlspecialchars( print_r( $cases2, TRUE ), ENT_QUOTES, 'utf-8', FALSE )
    . "</pre>\n";

			print '<pre>$vals = '
    . htmlspecialchars( print_r( $vals, TRUE ), ENT_QUOTES, 'utf-8', FALSE )
    . "</pre>\n";

			print '<pre>$vals2 = '
    . htmlspecialchars( print_r( $vals2, TRUE ), ENT_QUOTES, 'utf-8', FALSE )
    . "</pre>\n";

			//$activities = get_posts(
			//	array(
			//		'post_type' => $vca_asm_activities->activity_types,
			//		'posts_per_page' => -1
			//	)
			//);
			//
			//foreach ( $activities as $activity ) {
			//	$city = get_post_meta( $activity->ID, 'geo', true );
			//	update_post_meta( $activity->ID, 'city', $city );
			//	$nation = $vca_asm_geography->has_nation( $city );
			//	if ( $nation ) {
			//		update_post_meta( $activity->ID, 'nation', $nation );
			//	} else {
			//		update_post_meta( $activity->ID, 'nation', 40 );
			//	}
			//}

		}

		$admin_page = new VCA_ASM_Admin_Page( array(
			'echo' => true,
			'icon' => 'icon-settings',
			'title' => 'Datenstruktur von 1.2 an 1.3 anpassen',
			'url' => '?page=vca-asm-update',
			'messages' => $messages
		));
		$admin_page->top();

		$update_form = new VCA_ASM_Admin_Form( array(
			'echo' => true,
			'form' => true,
			'metaboxes' => false,
			'action' => '?page=vca-asm-update&todo=update',
			'button' => 'Let\'s do it!',
			'top_button' => false
		));
		$update_form->output();

		if ( ! empty( $updated ) && true === $updated ) {
			$messages = array(
				array(
					'type' => 'message-pa',
					'message' => 'Updated ' . $cases . ' Data Sets.'
				)
			);
			echo $vca_asm_admin->convert_messages( $messages );
		}

		$admin_page->bottom();
	}

	/**
	 * PHP4 style constructor
	 *
	 * @since 1.3
	 * @access public
	 */
	public function VCA_ASM_Admin_Update() {
		$this->__construct();
	}

	/**
	 * PHP5 style constructor
	 *
	 * @since 1.3
	 * @access public
	 */
	public function __construct() {
		add_action( 'admin_menu', array( &$this, 'admin_menu' ), 19 );
	}

} // class

endif; // class exists

?>