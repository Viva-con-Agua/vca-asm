<?php

/**
 * VCA_ASM_Admin_Home class.
 *
 * This class contains properties and methods for
 * the home screen of the backend UI
 *
 * @package VcA Activity & Supporter Management
 * @since 1.3
 */

if ( ! class_exists( 'VCA_ASM_Admin_Home' ) ) :

class VCA_ASM_Admin_Home {

	/**
	 * Controller for the Home Screen
	 *
	 * @since 1.3
	 * @access public
	 */
	public function home() {
		global $current_user,
			$vca_asm_geography;

		$has_tasks = false;

		$admin_city = get_user_meta( $current_user->ID, 'city', true );

		if ( ( in_array( 'city', $current_user->roles ) && in_array( $current_user->ID, array( 86, 92, 139 ) ) ) || 1 === $current_user->ID ) {
			$the_city_finances = new VCA_ASM_City_Finances( $admin_city );
			if ( $the_city_finances->action_required_city ) {
				$has_tasks = count( $the_city_finances->messages_city );
			}
		}

		$num_tasks_str = '';

		if( $has_tasks ) {
			$default_tab = 'tasks';
			$num_tasks_str = ' (' . $has_tasks . ')';
		} else {
			$default_tab = 'stats';
		}
		$active_tab = isset( $_GET[ 'tab' ] ) ? $_GET[ 'tab' ] : $default_tab;

		$page = new VCA_ASM_Admin_Page( array(
			'echo' => true,
			'icon' => 'icon-home',
			'title' => _x( 'Viva con Agua | Pool', 'Home Admin Menu', 'vca-asm' ),
			'url' => '?page=vca-asm-home',
			'logout_buttons' => true,
			'active_tab' => $active_tab,
			'tabs' => array(
				array(
					'value' => 'stats',
					'icon' => 'icon-stats',
					'title' => _x( 'Statistics', 'Home Admin Menu', 'vca-asm' )
				),
				array(
					'value' => 'tasks',
					'icon' => 'icon-tasks',
					'title' => _x( 'Tasks', 'Home Admin Menu', 'vca-asm' ) . $num_tasks_str
				),
				array(
					'value' => 'log',
					'icon' => 'icon-log',
					'title' => _x( 'Activity Log', 'Home Admin Menu', 'vca-asm' )
				)
			)
		));

		$page->top();

		if( 'log' === $active_tab ) {
			$this->view_log();
		} elseif( 'tasks' === $active_tab ) {
			$this->view_tasks();
		} else {
			$this->view_stats();
		}

		$page->bottom();
	}

	/**
	 * The Pool Stats (as far the specific user is allowed to see)
	 *
	 * @since 1.3
	 * @access public
	 */
	public function view_stats() {
		global $current_user, $wpdb, $vca_asm_geography;

		$stats = new VCA_ASM_Stats();
		$admin_city = get_user_meta( $current_user->ID, 'city', true );
		$admin_city_name = $vca_asm_geography->get_name( $admin_city );
		$admin_city_status = $vca_asm_geography->get_type( $admin_city );

		$output = '<div id="poststuff">' .
			'<div id="post-body" class="metabox-holder columns-1">' .
			'<div id="postbox-container-1" class="postbox-container">' .
			'<div class="postbox ">' .
				'<h3 class="no-hover"><span>' . __( 'Supporters', 'vca-asm' ) . '</span></h3>' .
				'<div class="inside">' .
					'<p>' .
						sprintf( _x( '%1$s registered supporters, %2$s of which are from your %3$s', 'Statistics', 'vca-asm' ),
							'<strong>' . $stats->supporters_total_total . '</strong>',
							'<strong>' . $stats->supporters_total_city . '</strong>',
							$admin_city_status
						) . '<br />' .
						sprintf( _x( '&quot;Active Members&quot;: %1$s (your %3$s: %2$s)', 'Statistics', 'vca-asm' ),
							'<strong>' . $stats->supporters_active_total . '</strong>',
							'<strong>' . $stats->supporters_active_city . '</strong>',
							$admin_city_status
						) . '<br />' .
						sprintf( _x( 'Current pending membership applications: %1$s (your %3$s: %2$s)', 'Statistics', 'vca-asm' ),
							'<strong>' . $stats->supporters_applied_total . '</strong>',
							'<strong>' . $stats->supporters_applied_city . '</strong>',
							$admin_city_status
						) . '<br />' .
						sprintf( _x( 'The remaining %1$s (your %3$s: %2$s) have not applied for active membership.', 'Statistics', 'vca-asm' ),
							'<strong>' . $stats->supporters_inactive_total . '</strong>',
							'<strong>' . $stats->supporters_inactive_city . '</strong>',
							$admin_city_status
						) . '<br />' .
						sprintf( _x( '%1$s of those (your %3$s: %2$s) only have very incomplete (not even a name submitted) profiles.', 'Statistics', 'vca-asm' ),
							'<strong>' . $stats->supporters_incomplete_total . '</strong>',
							'<strong>' . $stats->supporters_incomplete_city . '</strong>',
							$admin_city_status
						) . '<br />' .
						sprintf( _x( 'The average age of all supporters (%1$s) is %2$s, %3$s are under 25 years old and %4$s are 25 or older', 'Statistics', 'vca-asm' ),
							'<strong>' . $stats->supporters_complete_total . '</strong>',
							'<strong>' . $stats->supporters_average_age . '</strong>',
							'<strong>' . $stats->supporters_complete_under25 . '</strong>',
							'<strong>' . $stats->supporters_complete_over25 . '</strong>'
						) .
					'</p>' .
					'<p>' .
						sprintf( _x( '%1$s administrative users, %2$s of which are from your %3$s', 'Statistics', 'vca-asm' ),
							'<strong>' . $stats->admins_total . '</strong>',
							'<strong>' . $stats->admins_city . '</strong>',
							$admin_city_status
						) .
					'</p>' .
				'</div>' .
			'</div>' .
			'<div class="postbox ">' .
				'<h3 class="no-hover"><span>' . __( 'Geography', 'vca-asm' ) . '</span></h3>' .
				'<div class="inside">' .
					'<p>' .
						sprintf( _x( '%s Cities', 'Statistics', 'vca-asm' ),
								'<strong>' . $stats->cities_total . '</strong>' ) . '<br />' .
						sprintf( _x( '%1$s of those are Crews', 'Statistics', 'vca-asm' ),
								'<strong>' . $stats->cities_crews . '</strong>' ) . '<br />' .
						sprintf( _x( '%1$s of those are (old-school) Cells', 'Statistics', 'vca-asm' ),
								'<strong>' . $stats->cities_cells . '</strong>' ) .
					'</p>' .
					'<p>' .
						sprintf( _x( '%s City Groups', 'Statistics', 'vca-asm' ),
								'<strong>' . $stats->city_groups . '</strong>' ) . '<br />' .
						sprintf( _x( '%s Countries', 'Statistics', 'vca-asm' ),
								'<strong>' . $stats->countries . '</strong>' ) . '<br />' .
						sprintf( _x( '%s Country Groups', 'Statistics', 'vca-asm' ),
								'<strong>' . $stats->country_groups . '</strong>' ) .
					'</p>' .
				'</div>' .
			'</div>' .
			'<div class="postbox ">' .
				'<h3 class="no-hover"><span>' . __( 'Activities', 'vca-asm' ) . '</span></h3>' .
				'<div class="inside">' .
					'<p>' .
						sprintf( _x( '%1$s activities in total, of which %2$s are in the future (applications for %3$s of those are still open)', 'Statistics', 'vca-asm' ),
							'<strong>' . $stats->activities_count['all']['total'] . '</strong>',
							'<strong>' . $stats->activities_count['all']['upcoming'] . '</strong>',
							'<strong>' . $stats->activities_count['all']['appphase'] . '</strong>'
						) .
					'</p>' .
					'<p>' .
						sprintf( _x( '%1$s festivals in total, of which %2$s are in the future (applications for %3$s of those are still open)', 'Statistics', 'vca-asm' ),
							'<strong>' . $stats->activities_count['festival']['total'] . '</strong>',
							'<strong>' . $stats->activities_count['festival']['upcoming'] . '</strong>',
							'<strong>' . $stats->activities_count['festival']['appphase'] . '</strong>'
						) . '<br />' .
						sprintf( _x( '%1$s concerts in total, of which %2$s are in the future (applications for %3$s of those are still open)', 'Statistics', 'vca-asm' ),
							'<strong>' . $stats->activities_count['concert']['total'] . '</strong>',
							'<strong>' . $stats->activities_count['concert']['upcoming'] . '</strong>',
							'<strong>' . $stats->activities_count['concert']['appphase'] . '</strong>'
						) . '<br />' .
						sprintf( _x( '%1$s miscellaneous activities (Actions Department) in total, of which %2$s are in the future (applications for %3$s of those are still open)', 'Statistics', 'vca-asm' ),
							'<strong>' . $stats->activities_count['miscactions']['total'] . '</strong>',
							'<strong>' . $stats->activities_count['miscactions']['upcoming'] . '</strong>',
							'<strong>' . $stats->activities_count['miscactions']['appphase'] . '</strong>'
						) . '<br />' .
					'</p>' .
					'<p>' .
						sprintf( _x( '%1$s network gatherings in total, of which %2$s are in the future (applications for %3$s of those are still open)', 'Statistics', 'vca-asm' ),
							'<strong>' . $stats->activities_count['nwgathering']['total'] . '</strong>',
							'<strong>' . $stats->activities_count['nwgathering']['upcoming'] . '</strong>',
							'<strong>' . $stats->activities_count['nwgathering']['appphase'] . '</strong>'
						) .
					'</p>' .
				'</div>' .
			'</div></div></div></div>';

		echo $output;

	}

	/**
	 * The User's current tasks
	 *
	 * @since 1.3
	 * @access public
	 */
	public function view_tasks() {
		global $current_user,
			$vca_asm_geography;

		$has_tasks = false;

		$admin_city = get_user_meta( $current_user->ID, 'city', true );

		if ( ( in_array( 'city', $current_user->roles ) && in_array( $current_user->ID, array( 86, 92, 139 ) ) ) || 1 === $current_user->ID ) {
			$the_city_finances = new VCA_ASM_City_Finances(
				$admin_city,
				array(
					'short' => false,
					'formatted' => true,
					'linked' => true,
					'referrer' => 'tasks'
				)
			);
			if ( $the_city_finances->action_required_city ) {
				$has_tasks = count( $the_city_finances->messages_city );
			}
		}

		$mbs = new VCA_ASM_Admin_Metaboxes( array(
			'echo' => false,
			'columns' => 1,
			'running' => 1,
			'id' => '',
			'title' => $has_tasks ? __( 'Financial Tasks', 'vca-asm' ) : __( 'Tasks', 'vca-asm' ),
			'js' => false
		));

		$output = $mbs->top();
		$output .= $mbs->mb_top();

		if ( $has_tasks ) {
			foreach ( $the_city_finances->messages_city as $message ) {
				$output .= '<p>' . $message . '</p>';
			}
		} else {
			$output .= '<p>' . _x( 'None', 'Tasks', 'vca-asm' ) . '...</p>';
		}

		$output .= $mbs->mb_bottom();
		$output .= $mbs->bottom();

		echo $output;
	}

	/**
	 * The Activity Log
	 *
	 * @since 1.3
	 * @access public
	 */
	public function view_log() {
		$feech = new VCA_ASM_Admin_Future_Feech( array(
			'title' => 'Aktivitäts-Log',
			'version' => '1.4',
			'explanation' => 'Hier wird ein Verwaltungsbenutzer seine/ihre eigenen und die Aktivitäten der ihm/ihr untergeordneten Nutzer einsehen können.'
		));
		$feech->output();
	}

} // class

endif; // class exists

?>