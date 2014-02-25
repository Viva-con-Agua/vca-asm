<?php

/**
 * VCA_ASM_Admin_Activities class.
 *
 * This class contains properties and methods for
 * the activities management
 *
 * @package VcA Activity & Supporter Management
 * @since 1.2
 */

if ( ! class_exists( 'VCA_ASM_Admin_Activities' ) ) :

class VCA_ASM_Admin_Activities {

	/**
	 * Controller for the Activities Admin Menu
	 *
	 * @since 1.2
	 * @access public
	 */
	public function control() {

		$output = '<div class="wrap">' .
				'<div id="icon-activities" class="icon32-pa"></div><h2>' . _x( 'Activities', 'Activities Admin Menu', 'vca-asm' ) . '</h2><br /><br />' .
				'<p>' .
					'<a title="Ab ins Festival Menü" href="http://pool.vivaconagua.org/wp-admin/edit.php?post_type=festival">Festivals editieren und/oder hinzufügen</a>' .
				'</p>' .
				'<p>' .
					'<a title="Bewerbungen bearbeiten / Plätze vergeben" href="http://pool.vivaconagua.org/wp-admin/admin.php?page=vca-asm-slot-allocation">Bewerbungen / Platzvergabe</a>' .
				'</p>' .
			'</div>';

		echo $output;
	}

} // class

endif; // class exists

?>