<?php

/**
 * VCA_ASM_Admin_Actions class.
 *
 * This class contains properties and methods for
 * the activities management
 *
 * @package VcA Activity & Supporter Management
 * @since 1.3
 */

if ( ! class_exists( 'VCA_ASM_Admin_Actions' ) ) :

class VCA_ASM_Admin_Actions {

	/**
	 * Controller for the Actions Admin Menu
	 *
	 * @since 1.3
	 * @access public
	 */
	public function actions_overview() {
		echo '<div class="wrap">' .
			'<div id="icon-actions" class="icon32-pa"></div><h2>Übersicht: Aktionsbereich</h2>';
		$feech = new VCA_ASM_Admin_Future_Feech( array(
			'title' => 'Übersicht: Aktionsbereich',
			'version' => '1.6 (?)',
			'explanation' => 'Statistiken etc.'
		));
		$feech->output();
		echo '</div>';
	}

} // class

endif; // class exists

?>