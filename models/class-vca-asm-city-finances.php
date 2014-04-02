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

class VCA_ASM_City_Finances
{

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
	public $sendable_receipts = array();
	public $sent_receipts = array();

	public $confirmable_don_transfers = array();
	public $confirmable_econ_transfers = array();
	public $confirmable_external_transfers = array();

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

	public $message_strings = array();
	public $message_strings_short = array();

	public $messages = array();
	public $messages_don = array();
	public $messages_econ = array();

	/**
	 * Assigns values to $message_strings property
	 * Utility method, translatability
	 *
	 * @since 1.5
	 * @access private
	 */
	private function translatable_messages()
	{
		$this->message_strings = array(
			'action_required' => __( '', 'vca-asm' ),
			'city' => __( '', 'vca-asm' ),
			'office' => __( '', 'vca-asm' ),
			'econ' => __( '', 'vca-asm' ),
			'don' => __( '', 'vca-asm' ),
			'econ_city' => __( '', 'vca-asm' ),
			'don_city' => __( '', 'vca-asm' ),
			'econ_office' => __( '', 'vca-asm' ),
			'don_office' => __( '', 'vca-asm' ),
			'econ_balance' => __( '', 'vca-asm' ),
			'don_balance' => __( '', 'vca-asm' ),
			'econ_transfer' => __( '', 'vca-asm' ),
			'don_transfer' => __( '', 'vca-asm' ),
			'econ_confirm_transfer' => __( '', 'vca-asm' ),
			'don_confirm_transfer' => __( '', 'vca-asm' ),
			'econ_send_receipts' => __( '', 'vca-asm' ),
			'econ_confirm_receipts' => __( '', 'vca-asm' ),
			'don_confirm_external_transfer' => __( '', 'vca-asm' )
		);
	}

	/**
	 * Assigns values to class properties
	 *
	 * @since 1.5
	 * @access private
	 */
	private function gather_meta( $id )
	{
		global $wpdb,
			$vca_asm_finances, $vca_asm_geography, $vca_asm_utilities;

		$this->nation_id = $vca_asm_geography->has_nation( $id );

		$this->name = $vca_asm_geography->get_name( $id );
		$this->type = $vca_asm_geography->get_type( $id, false, false );
		$this->type_nice = $vca_asm_geography->get_type( $id );

		$this->currency_name = 'Euro'; /* ATTENTION */
		$this->currency_minor = 'Cent'; /* ATTENTION */
		$this->currency_symbol = '&euro;'; /* ATTENTION */

		$this->donations_by_years = $vca_asm_finances->get_donations( $id, true );
		$this->donations_total = $this->donations_by_years['total'];
		$this->donations_current_year = ! empty( $this->donations_by_years[date('Y')] ) ? $this->donations_by_years[date('Y')] : 0;

		$this->donations_total_formatted = number_format( $this->donations_total/100, 2, ',', '.' ) . ' ' . $this->currency_symbol;
		$this->donations_current_year_formatted = number_format( $this->donations_current_year/100, 2, ',', '.' ) . ' ' . $this->currency_symbol;

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
		$this->current_receipts = $this->receipts['current'];
		$this->sendable_receipts = array_merge( $this->late_receipts, $this->current_receipts );
		$this->sent_receipts = $vca_asm_finances->get_receipts( $id, array( 'status' => 2, 'data_type' => 'receipt_id' ) );

		$this->late_donations = array(); /* ATTENTION */
		$this->current_donations = array(); /* ATTENTION */

		$this->balanced_month_econ_string = $vca_asm_finances->get_balanced_month( $id, 'econ' );
		$this->balanced_month_don_string = $vca_asm_finances->get_balanced_month( $id, 'donations' );

		$this->balanced_month_econ_threshold_stamp = $vca_asm_finances->get_balanced_threshold_stamp( $id, 'econ' );
		$this->balanced_month_don_threshold_stamp = $vca_asm_finances->get_balanced_threshold_stamp( $id, 'donations' );
		$this->balanced_month_econ_name = strftime( '%B %Y', $this->balanced_month_econ_threshold_stamp );
		$this->balanced_month_don_name = strftime( '%B %Y', $this->balanced_month_don_threshold_stamp );

		$this->confirmable_don_transfers = $vca_asm_finances->get_transactions(array(
			'city_id' => $id,
			'account_type' => 'donations',
			'transaction_type' => 'transfer',
			'date_limit' => false,
			'receipt_status' => 2
		));
		$this->confirmable_external_transfers = $vca_asm_finances->get_transactions(array(
			'city_id' => $id,
			'account_type' => 'donations',
			'transaction_type' => 'donation',
			'date_limit' => false,
			'receipt_status' => 2
		));
		$this->confirmable_econ_transfers = $vca_asm_finances->get_transactions(array(
			'city_id' => $id,
			'account_type' => 'econ',
			'transaction_type' => 'transfer',
			'date_limit' => false,
			'receipt_status' => 2
		));
	}

	/**
	 * Sets boolean action flags
	 *
	 * @since 1.5
	 * @access private
	 */
	private function set_action_flags()
	{
		$this->action_required_don_transfer = ( 0 < $this->balance_don );
		$this->action_required_econ_transfer = $this->has_econ_surplus;

		$this->action_required_don_confirm_transfer = ! empty( $this->confirmable_don_transfers );
		$this->action_required_don_confirm_external_transfer = ! empty( $this->confirmable_external_transfers );
		$this->action_required_econ_confirm_transfer = ! empty( $this->confirmable_econ_transfers );

		$this->action_required_don_balance = ( 12 * intval( date( 'Y' ) ) + intval( date( 'n' ) ) > 12 * intval( date( 'Y', $this->balanced_month_don_threshold_stamp ) ) + intval( date( 'n', $this->balanced_month_don_threshold_stamp ) ) + 1 );
		$this->action_required_econ_balance = ( 12 * intval( date( 'Y' ) ) + intval( date( 'n' ) ) > 12 * intval( date( 'Y', $this->balanced_month_econ_threshold_stamp ) ) + intval( date( 'n', $this->balanced_month_econ_threshold_stamp ) ) + 1 );

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
			$this->action_required_don_confirm_external_transfer ||
			$this->action_required_econ_confirm_receipts
		);

		$this->action_required_city = ( $this->action_required_econ_city || $this->action_required_don_city );
		$this->action_required_office = ( $this->action_required_econ_office || $this->action_required_don_office );

		$this->action_required_econ = ( $this->action_required_econ_city || $this->action_required_econ_office );
		$this->action_required_don = ( $this->action_required_don_city || $this->action_required_don_office );

		$this->action_required = ( $this->action_required_don || $this->action_required_econ );
	}

	/**
	 * Sets messages according to action flags
	 *
	 * @since 1.5
	 * @access private
	 */
	private function set_messages()
	{
		return false;
	}

	/**
	 * Constructor
	 *
	 * @since 1.5
	 * @access public
	 */
	public function __construct( $city_id )
	{
		$this->id = $city_id;
		$this->translatable_messages();
		$this->gather_meta( $this->id );
		$this->set_action_flags();
		$this->set_messages();
	}

} // class

endif; // class exists

?>