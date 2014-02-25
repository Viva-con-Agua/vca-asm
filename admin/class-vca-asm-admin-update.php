<?php

/**
 * VCA_ASM_Admin_Update class.
 *
 * This class contains properties and methods to
 * update the data structure of the Pool
 * from Version 1.2 to 1.3
 *
 * Could be reused for any further major structural updates
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

			//$users = get_users();
			//
			//foreach ( $users as $user ) {
			//	if( 1 !== $user->ID ) {
			//	$residence = get_user_meta( $user->ID, 'city', true );
			//	$city = get_user_meta( $user->ID, 'region', true );
			//	update_user_meta( $user->ID, 'residence', $residence );
			//	update_user_meta( $user->ID, 'city', $city );
			//	$nation = $vca_asm_geography->has_nation( $city );
			//	if ( $nation ) {
			//		update_user_meta( $user->ID, 'nation', $nation );
			//	} else {
			//		update_user_meta( $user->ID, 'nation', 0 );
			//	}
			//	}
			//}

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
					'message' => 'Updated.'
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