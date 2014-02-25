<?php

/**
 * VCA_ASM_City_Finances class.
 *
 * An instance of this class holds all information on the financial situation of a city
 *
 * @package VcA Activity & Supporter Management
 * @since 1.5
 */

if ( ! class_exists( 'VCA_ASM_City_Finances' ) ) :

class VCA_ASM_City_Finances {

	/**
	 * Class Properties
	 *
	 * @since 1.2
	 */
	public $id = 0;
	public $nation_id = '';

	public $name = '';
	public $status = '';
	public $status_short = '';

	public $donations_total = 0;
	public $donations_current_year = 0;

	public $balance_econ;
	public $balance_don;

	public $late_donations = array();
	public $current_donations = array();

	public $late_receipts = array();
	public $current_receipts = array();
	public $sent_receipts = array();

	/**
	 * Assigns values to class properties
	 *
	 * @since 1.5
	 * @access public
	 */
	public function gather_meta( $id ) {
		global $wpdb,
			$vca_asm_finances, $vca_asm_geography, $vca_asm_utilities;

		$this->nation_id = $vca_asm_geography->has_nation( $id );

		$this->name = $vca_asm_geography->get_name( $id );
		$this->status = $vca_asm_geography->get_type( $id );
		$this->status_short = $vca_asm_geography->get_type( $id, false );

		$this->donations_years = $vca_asm_finances->get_donations( $id, true );
		$this->donations_total = $this->donations_years['total'];

		$this->balance_econ = $vca_asm_finances->get_balance( $id, 'econ' );
		$this->balance_don = $vca_asm_finances->get_balance( $id, 'donations' );

		$this->late_donations = array();
		$this->current_donations = array();

		$this->receipts = $vca_asm_finances->get_receipts( $id, array( 'status' => 1, 'data_type' => 'receipt_id', 'split' => true ) );
		$this->late_receipts = $this->receipts['late'];
		$this->sent_receipts = $vca_asm_finances->get_receipts( $id, array( 'status' => 2, 'data_type' => 'receipt_id' ) );
		$this->current_receipts = $this->receipts['current'];
	}

	/**
	 * PHP5 style constructor
	 *
	 * @since 1.0
	 * @access public
	 */
	public function __construct( $city_id ) {
		$this->id = $city_id;
		$this->gather_meta( $this->id );
	}

} // class

endif; // class exists

?>