<?php

/**
 * VCA_ASM_Finances_Workbook class.
 *
 * This class contains properties and methods for
 * the handling of financial data
 *
 * @package VcA Activity & Supporter Management
 * @since 1.5
 */

if ( ! class_exists( 'VCA_ASM_Finances_Workbook' ) ) :

class VCA_ASM_Finances_Workbook
{

	/**
	 * Class Properties
	 *
	 * @since 1.5
	 */
	public $default_args = array(
		'scope' => 'nation',
		'id' => 0,
		'timeframe' => 'month',
		'month' => 1,
		'year' => 2014
	);
	public $args = array();

	private $workbook = object;

	/**
	 * Constructor
	 *
	 * @since 1.5
	 * @access public
	 */
	public function __construct( $args = array() )
	{
		global $current_user;

		$this->default_args['id'] = get_user_meta( $current_user->ID, 'nation', true );
		$this->default_args['month'] = date( '%m' );
		$this->default_args['year'] = date( '%Y' );

		$this->args = wp_parse_args( $args, $this->default_args );

		$this->workbook = new PHPExcel();
	}

	/**
	 *
	 *
	 * @param array $args
	 * @return array $transactions
	 *
	 * @since 1.5
	 * @access public
	 */
	public function get_annual( $args = array() )
	{

	}

} // class

endif; // class exists

?>