<?php

/**
 * VCA_ASM_Admin_Education class.
 *
 * This class contains properties and methods for
 * the activities management
 *
 * @package VcA Activity & Supporter Management
 * @since 1.3
 */

if ( ! class_exists( 'VCA_ASM_Admin_Education' ) ) :

class VCA_ASM_Admin_Education {

	/**
	 * Controller for the Education Admin Menu
	 *
	 * @since 1.3
	 * @access public
	 */
	public function education_overview() {
		echo '<div class="wrap">' .
			'<div id="icon-education" class="icon32-pa"></div><h2>Übersicht: Bildungsbereich</h2>';
		$feech = new VCA_ASM_Admin_Future_Feech( array(
			'title' => 'Übersicht: Bildungsbereich',
			'version' => '1.5',
			'explanation' => 'Statistiken etc.'
		));
		$feech->output();
		echo '</div>';
	}

	/**
	 * Educational Workshops Pseudo Admin-Page
	 *
	 * @since 1.3
	 * @access public
	 */
	public function pseudo_workshops() {
		echo '<div class="wrap">' .
			'<div id="icon-education" class="icon32-pa"></div><h2>Free Teacher Workshops</h2>';
		$feech = new VCA_ASM_Admin_Future_Feech( array(
			'title' => 'Free Teacher Workshops',
			'version' => '1.5',
			'explanation' => 'Erster Aktivitätstyp des Bildungsbereichs: der Free Teacher Workshop.'
		));
		$feech->output();
		echo '</div>';
	}

} // class

endif; // class exists

?>