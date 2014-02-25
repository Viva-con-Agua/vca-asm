<?php

/**
 * VCA_ASM_Admin_Finances class.
 *
 * This class contains properties and methods for
 * the activities management
 *
 * @package VcA Activity & Supporter Management
 * @since 1.2
 */

if ( ! class_exists( 'VCA_ASM_Admin_Finances' ) ) :

class VCA_ASM_Admin_Finances {

	/**
	 * Controller for the Finances Admin Menu
	 *
	 * @since 1.2
	 * @access public
	 */
	public function control() {
		echo '<div class="wrap">' .
			'<div id="icon-finances" class="icon32-pa"></div><h2>(Zellen-)Finanzen</h2>';
		$feech = new VCA_ASM_Admin_Future_Feech( array(
			'title' => '(Zellen-)Finanzen',
			'version' => '1.4',
			'explanation' => 'Hier werden in Zukunft die Spenden- und Wirtschaftskonten der Zellen verwaltet werden können.'
		));
		$feech->output();
		echo '</div>';
	}

} // class

endif; // class exists

?>