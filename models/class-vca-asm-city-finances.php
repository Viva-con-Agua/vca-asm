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
	 * @since 1.5
	 */
	public $id = 0;
	public $nation_id = '';

	public $name = '';
	public $type = '';
	public $type_nice = '';

	public $currency_name = 'Euro';
	public $currency_minor = 'Cent';
	public $currency_symbol = '&euro;';

	public $action_required = false;

	public $action_required_city = false;
	public $action_required_office = false;

	public $action_required_econ = false;
	public $action_required_don = false;

	public $action_required_econ_city = false;
	public $action_required_don_city = false;
	public $action_required_econ_office = false;
	public $action_required_don_office = false;

	public $action_required_don_transfer = false;
	public $action_required_don_confirm_transfer = false;
	public $action_required_don_confirm_external_transfer = false;
	public $action_required_don_balance = false;

	public $action_required_econ_transfer = false;
	public $action_required_econ_confirm_transfer = false;
	public $action_required_econ_balance = false;
	public $action_required_econ_send_receipts = false;
	public $action_required_econ_confirm_receipts = false;

	public $donations_total = 0;
	public $donations_by_years = array();
	public $donations_current_year = 0;
	public $donations_total_formatted = '';
	public $donations_current_year_formatted = '';

	public $balance_econ = 0;
	public $balance_don = 0;
	public $balance_econ_formatted = '';
	public $balance_don_formatted = '';

	public $econ_annual_revenue = 0;
	public $econ_annual_expenses = 0;
	public $econ_annual_revenue_formatted = '';
	public $econ_annual_expenses_formatted = '';

	public $max_econ = 150;
	public $has_econ_surplus = false;
	public $econ_surplus = 0;

	public $balanced_month_econ_string = '';
	public $balanced_month_don_string = '';
	public $balanced_month_econ_name = '';
	public $balanced_month_don_name = '';
	public $balanced_month_econ_threshold_stamp = 0;
	public $balanced_month_don_threshold_stamp = 0;

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
		$this->type = $vca_asm_geography->get_type( $id, false, false );
		$this->type_nice = $vca_asm_geography->get_type( $id );

		$this->currency_name = 'Euro';
		$this->currency_minor = 'Cent';
		$this->currency_symbol = '&euro;';

		$this->donations_by_years = $vca_asm_finances->get_donations( $id, true );
		$this->donations_total = $this->donations_by_years['total'];
		$this->donations_current_year = ! empty( $this->donations_by_years[date('Y')] ) ? $this->donations_by_years[date('Y')] : 0;

		$this->balance_econ = $vca_asm_finances->get_balance( $id, 'econ' );
		$this->balance_don = $vca_asm_finances->get_balance( $id, 'donations' );

		$this->balance_econ_formatted = number_format( $this->balance_econ/100, 2, ',', '.' ) . ' ' . $this->currency_symbol;
		$this->balance_don_formatted = number_format( $this->balance_don/100, 2, ',', '.' ) . ' ' . $this->currency_symbol;

		$this->econ_annual_revenue = $vca_asm_finances->get_transactions( array( 'account_type' => 'econ', 'transaction_type' => 'revenue', 'annual' => date( 'Y' ), 'sum' => true, 'formatted' => true, 'city_id' => $id ) );
		$this->econ_annual_expenses = $vca_asm_finances->get_transactions( array( 'account_type' => 'econ', 'transaction_type' => 'expenditure', 'annual' => date( 'Y' ), 'sum' => true, 'formatted' => true, 'city_id' => $id ) );
		$this->econ_annual_revenue_formatted = number_format( $this->econ_annual_revenue/100, 2, ',', '.' ) . ' ' . $this->currency_symbol;
		$this->econ_annual_expenses_formatted = number_format( $this->econ_annual_expenses/100, 2, ',', '.' ) . ' ' . $this->currency_symbol;

		$this->max_econ = intval( $vca_asm_finances->get_meta( $this->nation_id, 'related_id', 'limit-'.$this->type , 'value' ) ) * 100;
		$this->econ_surplus = $this->balance_econ - $this->max_econ;
		$this->has_econ_surplus = ( $this->econ_surplus > 0 );
		$this->econ_surplus_formatted = number_format( $this->econ_surplus/100, 2, ',', '.' ) . ' ' . $this->currency_symbol;

		$this->receipts = $vca_asm_finances->get_receipts( $id, array( 'status' => 1, 'data_type' => 'receipt_id', 'split' => true ) );
		$this->late_receipts = $this->receipts['late'];
		$this->sent_receipts = $vca_asm_finances->get_receipts( $id, array( 'status' => 2, 'data_type' => 'receipt_id' ) );
		$this->current_receipts = $this->receipts['current'];

		$this->late_donations = array(); /* ATTENTION */
		$this->current_donations = array(); /* ATTENTION */

		$this->balanced_month_econ_string = $vca_asm_finances->get_balanced_month( $id, 'econ' );
		$this->balanced_month_don_string = $vca_asm_finances->get_balanced_month( $id, 'donations' );

		$this->balanced_month_econ_threshold_stamp = $vca_asm_finances->get_balanced_threshold_stamp( $id, 'econ' );
		$this->balanced_month_don_threshold_stamp = $vca_asm_finances->get_balanced_threshold_stamp( $id, 'donations' );
		$this->balanced_month_econ_name = strftime( '%B %Y', $this->balanced_month_econ_threshold_stamp );
		$this->balanced_month_don_name = strftime( '%B %Y', $this->balanced_month_don_threshold_stamp );

		$this->donations_total_formatted = number_format( $this->donations_total/100, 2, ',', '.' ) . ' ' . $this->currency_symbol;
		$this->donations_current_year_formatted = number_format( $this->donations_current_year/100, 2, ',', '.' ) . ' ' . $this->currency_symbol;

		$this->action_required_don_transfer = ( 0 < $this->balance_don );
		$confirmable_don_transfers = $vca_asm_finances->get_transactions(array(
			'city_id' => $id,
			'account_type' => 'donations',
			'transaction_type' => 'transfer',
			'date_limit' => false,
			'receipt_status' => 2
		));
		$this->action_required_don_confirm_transfer = ! empty( $confirmable_don_transfers );
		$confirmable_external_transfers = $vca_asm_finances->get_transactions(array(
			'city_id' => $id,
			'account_type' => 'donations',
			'transaction_type' => 'donation',
			'date_limit' => false,
			'receipt_status' => 2
		));
		$this->action_required_don_confirm_external_transfer = ! empty( $confirmable_external_transfers );
		$this->action_required_don_balance = ( 12 * intval( date('Y') ) + intval( date( 'n' ) ) > 12 * intval( $balanced_year_don ) + intval( ltrim( $balanced_month_don, '0' ) ) + 1 );

		$this->action_required_econ_transfer = $this->has_econ_surplus;
		$confirmable_econ_transfers = $vca_asm_finances->get_transactions(array(
			'city_id' => $id,
			'account_type' => 'econ',
			'transaction_type' => 'transfer',
			'date_limit' => false,
			'receipt_status' => 2
		));
		$this->action_required_econ_confirm_transfer = ! empty( $confirmable_econ_transfers );
		$this->action_required_econ_balance = ( 12 * intval( date('Y') ) + intval( date( 'n' ) ) > 12 * intval( $balanced_year_econ ) + intval( ltrim( $balanced_month_econ, '0' ) ) + 1 );
		$this->action_required_econ_send_receipts = ! empty( $this->late_receipts );
		$this->action_required_econ_confirm_receipts = ! empty( $this->sent_receipts );

		$this->action_required_don_city = (
			$this->action_required_don_transfer ||
			$this->action_required_don_balance
		);
		$this->action_required_econ_city = (
			$this->action_required_econ_transfer ||
			$this->action_required_econ_balance ||
			$this->action_required_econ_send_receipts
		);
		$this->action_required_don_office = (
			$this->action_required_don_confirm_transfer
		);
		$this->action_required_econ_office = (
			$this->action_required_econ_confirm_transfer ||
			$this->action_required_econ_confirm_receipts
		);

		$this->action_required_city = ( $this->action_required_econ_city | $this->action_required_don_city );
		$this->action_required_office = ( $this->action_required_econ_office | $this->action_required_don_office );

		$this->action_required_econ = ( $this->action_required_econ_city | $this->action_required_econ_office );
		$this->action_required_don = ( $this->action_required_don_city | $this->action_required_don_office );

		$this->action_required = ( $this->action_required_don | $this->action_required_econ );
	}

	/**
	 * Constructor
	 *
	 * @since 1.5
	 * @access public
	 */
	public function __construct( $city_id ) {
		$this->id = $city_id;
		$this->gather_meta( $this->id );
	}

} // class

endif; // class exists

?>