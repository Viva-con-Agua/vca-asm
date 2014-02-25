<?php

/**
 * VCA_ASM_Admin_Network class.
 *
 * This class contains properties and methods for
 * the activities management
 *
 * @package VcA Activity & Supporter Management
 * @since 1.3
 */

if ( ! class_exists( 'VCA_ASM_Admin_Network' ) ) :

class VCA_ASM_Admin_Network {

	/**
	 * Controller for the Network Admin Menu
	 *
	 * @since 1.3
	 * @access public
	 */
	public function network_overview() {
		echo '<div class="wrap">' .
			'<div id="icon-network" class="icon32-pa"></div><h2>Übersicht: Netzwerk</h2>';
		$feech = new VCA_ASM_Admin_Future_Feech( array(
			'title' => 'Übersicht: Netzwerk',
			'version' => '1.6 (?)',
			'explanation' => 'Statistiken etc.'
		));
		$feech->output();
		echo '</div>';
	}

	/**
	 * Network Gatherings Pseudo Admin-Page
	 *
	 * @since 1.3
	 * @access public
	 */
	public function pseudo_gatherings() {
		echo '<div class="wrap">' .
			'<div id="icon-network" class="icon32-pa"></div><h2>Netzwerktreffen</h2>';
		$feech = new VCA_ASM_Admin_Future_Feech( array(
			'title' => 'Netzwerktreffen',
			'version' => '1.5',
			'explanation' => 'Erster Aktivitätstyp des Netzwerkbereichs: das Netzwerktreffen.'
		));
		$feech->output();
		echo '</div>';
	}

} // class

endif; // class exists

?>