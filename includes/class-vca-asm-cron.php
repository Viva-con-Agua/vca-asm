<?php

/**
 * VCA_ASM_Cron class.
 *
 * This class provides properties and methods that allow the rest of the application to schedule recurring events as needed.
 *
 * @package VcA Activity & Supporter Management
 * @since 1.3
 *
 * Structure:
 * - Properties
 * - Constructor
 * - Intervals
 * - Tests
 */

if ( ! class_exists( 'VCA_ASM_Cron' ) ) :

class VCA_ASM_Cron
{

	/* ============================= CLASS PROPERTIES ============================= */

	/**
	 * This plugin's cron hooks (populated by other classes)
	 *
	 * @var array $hooks
	 * @since 1.3
	 */
	public $hooks = array();

	/* ============================= CONSTRUCTOR ============================= */

	/**
	 * Constructor
	 *
	 * @return void
	 *
	 * @since 1.3
	 * @access public
	 */
	public function __construct()
	{
		add_filter( 'cron_schedules', array( $this, 'add_non_core_scheduling_intervals' ) );
		add_shortcode( 'pilles-cron-tests', array( $this, 'pilles_cron_tests' ) );
		//add_action( 'pilles_test_hook', array( $this, 'pilles_test_exec' ) );
		//if ( ! wp_next_scheduled( 'pilles_test_hook' ) ) {
		//	wp_schedule_event( time(), '5minutely', 'pilles_test_hook' );
		//}
		//$timestamp = wp_next_scheduled( 'pilles_test_hook' );
		//wp_unschedule_event( $timestamp, 'pilles_test_hook' );
	}

	/* ============================= INTERVALS ============================= */

	/**
	 * Add more scheduling interval options
	 *
	 * WP natively supports 3 scheduling intervals.
	 * This method adds additional ones.
	 * (Intervals/values in seconds)
	 *
	 * @param array $schedules
	 * @return array $schedules
	 *
	 * @since 1.3
	 * @access public
	 */
	public function add_non_core_scheduling_intervals( $schedules )
	{
		$schedules['minutely'] = array(
			'interval' => 60,
			'display'  => __( 'Every minute', 'vca-asm' )
		);
		for ( $i = 2; $i < 31; $i++ ) {
			$schedules[$i.'minutely'] = array(
				'interval' => 60*$i,
				'display'  => sprintf( __( 'Every %d minutes', 'vca-asm' ), $i )
			);
		}
		$schedules['halfhourly'] = array(
			'interval' => 1801,
			'display'  => __( 'Every half an hour', 'vca-asm' )
		);

		return $schedules;
	}

	/* ============================= TESTS ============================= */

	/**
	 * Shortcode handler, returns cron test output
	 *
	 * @return string $output
	 *
	 * @since 1.3
	 * @access public
	 */
	public function pilles_cron_tests()
	{
		$output = '';
		$output .= '<pre>wp_get_schedule - vca_asm_check_outbox<br /><br /><br />'
			. htmlspecialchars( print_r( wp_get_schedule( 'vca_asm_check_outbox' ), TRUE ), ENT_QUOTES, 'utf-8', FALSE )
			. "</pre><br /><br /><br />\n";
		$output .= '<pre>Cronjobs:<br /><br /><br />'
			. htmlspecialchars( print_r( _get_cron_array(), TRUE ), ENT_QUOTES, 'utf-8', FALSE )
			. "</pre>\n";

		return $output;
	}

	/**
	 * Just for testing!
	 *
	 * @return void
	 *
	 * @global object $wpdb
	 *
	 * @since 1.3
	 * @access public
	 */
	public function pilles_test_exec()
	{
		global $wpdb;

		$wpdb->insert(
			$wpdb->prefix . 'test_shit',
			array( 'numeric' => 1 ),
			array( '%d' )
		);
	}
}

endif; // class exists

?>