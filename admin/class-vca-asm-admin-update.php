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

		if ( isset( $_GET['todo'] ) && 'update' === $_GET['todo'] ) {
			$updated = true;

			//$actis = get_posts(
			//	array(
			//		'post_type' => $vca_asm_activities->activity_types,
			//		'posts_per_page' => -1
			//	)
			//);
			//
			//foreach ( $actis as $act ) {
			//	$region = get_post_meta( $act->ID, 'region', true );
			//	if ( ! empty( $region ) ) {
			//		update_post_meta( $act->ID, 'geo', $region );
			//	}
			//}

			//$old_apps = $wpdb->get_results(
			//	"SELECT * FROM " .
			//	$wpdb->prefix . "vca_asm_applications_very_old", ARRAY_A
			//);
			//
			//foreach ( $old_apps as $app ) {
			//	$activities = unserialize($app['activities']);
			//	foreach ( $activities as $act ) {
			//		$wpdb->insert(
			//			$wpdb->prefix . 'vca_asm_applications_old',
			//			array(
			//				'supporter' => $app['supporter'],
			//				'activity' => $act
			//			),
			//			array(
			//				'%d',
			//				'%d'
			//			)
			//		);
			//	}
			//}
			//
			//$wpdb->query(
			//	"DELETE FROM " . $wpdb->prefix . "usermeta" .
			//	" WHERE meta_key LIKE 'meta-box%'"
			//);
			//
			//$misc = array(686);
			//$concerts = array(441,410,215,485);
			//
			//foreach ( $misc as $activ ) {
			//	wp_update_post( array(
			//		'ID' => $activ,
			//		'post_type' => 'miscactions',
			//	));
			//}
			//
			//foreach ( $concerts as $activ ) {
			//	wp_update_post( array(
			//		'ID' => $activ,
			//		'post_type' => 'concert',
			//	));
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

		if ( $updated ) {
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