<?php

/**
 * VCA_ASM_Admin_Goldeimer class.
 *
 * This class contains properties and methods for
 * "Goldeimer Komposttoiletten"
 *
 * @package VcA Activity & Supporter Management
 * @since 1.5
 */

if ( ! class_exists( 'VCA_ASM_Admin_Goldeimer' ) ) :

class VCA_ASM_Admin_Goldeimer {

	/**
	 * Controller for the Goldeimer Admin Menu
	 *
	 * @since 1.5
	 * @access public
	 */
	public function goldeimer_overview() {
		echo '<div class="wrap">' .
			'<div id="icon-goldeimer" class="icon32-pa"></div><h2>Goldeimer | ' . __( 'Overview', 'vca-asm' ) . '</h2>';
		$feech = new VCA_ASM_Admin_Future_Feech( array(
			'title' => 'Goldeimer | ' . __( 'Overview', 'vca-asm' ),
			'version' => '1.6 (?)',
			'explanation' => 'Statistiken etc.'
		));
		$feech->output();
		echo '</div>';
	}

} // class

endif; // class exists

?>