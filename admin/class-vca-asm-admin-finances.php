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

		$output = '<div class="wrap">' .
			'<div id="icon-finances" class="icon32-pa"></div><h2>' . _x( 'Finances', 'Finances Admin Menu', 'vca-asm' ) . '</h2><br /><br />' .
				'<p><dfn>Verfügbar ab Version 1.3</dfn></p>' .
			'</div>';

		echo $output;
	}

} // class

endif; // class exists

?>