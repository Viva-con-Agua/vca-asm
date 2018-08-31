<?php

/**
 * VCA_ASM_City_Finances class.
 *
 * Model
 * An instance of this class holds all information on the financial situation of a city
 *
 * @package VcA Activity & Supporter Management
 * @since 1.5
 *
 * Structure:
 * - Properties
 * --- Initial
 * --- Geography
 * --- Financial Data
 * --- Attention Flags
 * --- Messages
 * - Constructor
 * - Property Population
 * - Secondaray Parameters
 */

if ( ! class_exists( 'VCA_ASM_City_Finances' ) ) :

class VCA_ASM_City_Finances
{

	/* ============================= CLASS PROPERTIES ============================= */

	/* +++++++++ INITIAL +++++++++ */

	public $id = 0;
	public $default_args = array(
		'url' => '?page=vca-asm-finances',
		'link_title' => 'Do something about it!',
		'referrer' => '0',
		'short' => false,
		'formatted' => false,
		'linked' => false
	);
	public $args = array();
	public $url = '?page=vca-asm-finances';
	public $link_title = '';
	public $referrer = '';

	/* +++++++++ GEOGRAPHY +++++++++ */

	public $name = '';
	public $type = '';
	public $type_nice = '';
	public $nation_id = '';

	public $currency_name = 'Euro';
	public $currency_minor = 'Cent';
	public $currency_symbol = '&euro;';

	/* +++++++++ FINANCIAL DATA +++++++++ */

	public $donations_total = 0;
	public $donations_by_years = array();
    public $donations_current_year = 0;
    public $donations_last_year = 0;
    public $donations_total_formatted = '';
	public $donations_current_year_formatted = '';
	public $donations_last_year_formatted = '';

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

	public $late_receipts = array();
	public $current_receipts = array();
	public $sendable_receipts = array();
	public $sent_receipts = array();
	public $late_receipts_full = array();
	public $current_receipts_full = array();
	public $sendable_receipts_full = array();
	public $sent_receipts_full = array();

	public $confirmable_don_transfers = array();
	public $confirmable_econ_transfers = array();
	public $confirmable_external_transfers = array();

	/* +++++++++ ATTENTION FLAGS +++++++++ */

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
	public $action_required_don_balance_several = false;

	public $action_required_econ_transfer = false;
	public $action_required_econ_confirm_transfer = false;
	public $action_required_econ_balance = false;
	public $action_required_econ_balance_several = false;
	public $action_required_econ_send_receipts = false;
	public $action_required_econ_send_receipts_late = false;
	public $action_required_econ_confirm_receipts = false;

	/* +++++++++ MESSAGES +++++++++ */

	public $message_strings = array();
	public $message_strings_short = array();

	public $messages = array();
	public $messages_meta = array();
	public $messages_meta_office = array();
	public $messages_meta_city = array();
	public $messages_city = array();
	public $messages_office = array();
	public $messages_don = array();
	public $messages_econ = array();
	public $messages_don_city = array();
	public $messages_econ_city = array();
	public $messages_don_office = array();
	public $messages_econ_office = array();

	/* ============================= CONSTRUCTOR ============================= */

	/**
	 * Constructor
	 *
	 * @since 1.5
	 * @access public
	 */
	public function __construct( $city_id, $args = array() )
	{
		$this->id = $city_id;

		$this->default_args['link_title'] = __( 'Do something about it!', 'vca-asm' );
		$this->args = wp_parse_args( $args, $this->default_args );
		extract( $this->args );

		if ( isset( $this->args['js'] ) && true === $this->args['js'] ) {
			wp_enqueue_script( 'postbox' );
			add_action( 'admin_footer', array( $this, 'print_script' ) );
		}

		$this->url = $url;
		$this->link_title = $link_title;
		$this->referrer = $referrer;
		$this->translatable_messages();
		$this->gather_meta( $this->id );
		$this->set_action_flags();
		$this->set_messages( $short, $formatted, $linked );
	}

	/**
	 * Assigns values to $message_strings property
	 * Utility method, translatability
	 *
	 * @since 1.5
	 * @access private
	 */
	private function translatable_messages()
	{
		/**
		 * Messages as clearly formulated as possible
		 * with punctuation
		 */
		$this->message_strings = array(
			'action_required' => __( 'There are financial tasks to be executed in this city.', 'vca-asm' ),
			'city' => __( 'The city&apos;s SPOCs need to execute tasks.', 'vca-asm' ),
			'office' => __( 'The central office needs to execute tasks.', 'vca-asm' ),
			'econ' => __( 'The structural account requires attention.', 'vca-asm' ),
			'don' => __( 'The donations account requires attention.', 'vca-asm' ),
			'econ_city' => __( 'The structural account requires attention by the city&apos;s SPOCs.', 'vca-asm' ),
			'don_city' => __( 'The donations account requires attention by the city&apos;s SPOCs.', 'vca-asm' ),
			'econ_office' => __( 'The structural account requires attention by the central office.', 'vca-asm' ),
			'don_office' => __( 'The donations account requires attention by the central office.', 'vca-asm' ),
			'econ_balance' => __( 'Last month is not yet balanced (Structural account).', 'vca-asm' ),
			'don_balance' => __( 'Last month is not yet balanced (Donations account).', 'vca-asm' ),
			'econ_balance_several' => __( 'The Structural account is more than one month behind in balancing!', 'vca-asm' ) . ' ' . __( 'Please do so as soon as possible!', 'vca-asm' ),
			'don_balance_several' => __( 'The Donations account is more than one month behind in balancing!', 'vca-asm' ) . ' ' . __( 'Please do so as soon as possible!', 'vca-asm' ),
			'econ_transfer' => __( 'The city needs to transfer structural funds to the central office.', 'vca-asm' ),
			'don_transfer' => __( 'The city needs to transfer donations to the central office.', 'vca-asm' ),
			'econ_confirm_transfer' => __( 'The central office needs to confirm the reception of the transfer of structural funds.', 'vca-asm' ),
			'don_confirm_transfer' => __( 'The central office needs to confirm the reception of the transfer of donations from the city.', 'vca-asm' ),
			'econ_send_receipts' => __( 'Receipts for structural expenditures need to be sent to the central office.', 'vca-asm' ),
			'econ_send_receipts_late' => __( 'Receipts for structural expenditures from the previous month need to be sent to the central office.', 'vca-asm' ) . ' ' . __( 'Please do so as soon as possible!', 'vca-asm' ),
			'econ_confirm_receipts' => __( 'The central office needs to confirm the reception of receipts.', 'vca-asm' ),
			'don_confirm_external_transfer' => __( 'The central office needs to confirm the reception of the transfer of donations from external sources', 'vca-asm' )
		);

		/**
		 * Messages as short as possible
		 * without losing meaning
		 * without (final) punctuation
		 */
		$this->message_strings_short = array(
			'action_required' => __( 'Attention required', 'vca-asm' ),
			'city' => __( 'Attention required by city', 'vca-asm' ),
			'office' => __( 'Attention required by office', 'vca-asm' ),
			'econ' => __( 'Structural account requires attention', 'vca-asm' ),
			'don' => __( 'Donations account requires attention', 'vca-asm' ),
			'econ_city' => __( 'Structural account: Tasks for the city', 'vca-asm' ),
			'don_city' => __( 'Donations account: Tasks for the city', 'vca-asm' ),
			'econ_office' => __( 'Structural account: Tasks for the office', 'vca-asm' ),
			'don_office' => __( 'Donations account: Tasks for the office', 'vca-asm' ),
			'econ_balance' => __( 'Last month not balanced (Structural)', 'vca-asm' ),
			'don_balance' => __( 'Last month not balanced (Donations)', 'vca-asm' ),
			'econ_balance_several' => __( 'Structural account: more than a month behind', 'vca-asm' ),
			'don_balance_several' => __( 'Donations account: more than a month behind', 'vca-asm' ),
			'econ_transfer' => __( 'Structural account: transfer from city required', 'vca-asm' ),
			'don_transfer' => __( 'Donations account: transfer from city required', 'vca-asm' ),
			'econ_confirm_transfer' => __( 'Structural account: transfers need confirmation', 'vca-asm' ),
			'don_confirm_transfer' => __( 'Donations account: transfers need confirmation', 'vca-asm' ),
			'econ_send_receipts' => __( 'Structural account: Receipts need to be sent', 'vca-asm' ),
			'econ_send_receipts' => __( 'Structural account: Receipts from last month (!) need to be sent', 'vca-asm' ),
			'econ_confirm_receipts' => __( 'Structural account: Receipts need to be confirmed', 'vca-asm' ),
			'don_confirm_external_transfer' => __( 'Donations account: External transfers need confirmation', 'vca-asm' )
		);
	}

	/* ============================= GATHERING DATA & POPULATING PROPERTIES ============================= */

	/**
	 * Assigns values to class properties
	 *
	 * @since 1.5
	 * @access private
	 */
	private function gather_meta( $id )
	{
        global /** @var VCA_ASM_Finances $vca_asm_finances */
        $vca_asm_finances, $vca_asm_geography;

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
		$this->donations_last_year = ! empty( $this->donations_by_years[date('Y') - 1] ) ? $this->donations_by_years[date('Y') - 1] : 0;

		$this->donations_total_formatted = number_format( $this->donations_total/100, 2, ',', '.' ) . ' ' . $this->currency_symbol;
		$this->donations_current_year_formatted = number_format( $this->donations_current_year/100, 2, ',', '.' ) . ' ' . $this->currency_symbol;
		$this->donations_last_year_formatted = number_format( $this->donations_last_year/100, 2, ',', '.' ) . ' ' . $this->currency_symbol;

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

		$receipts = $vca_asm_finances->get_receipts( $id, array( 'status' => 1, 'data_type' => 'receipt_id', 'split' => true ) );
		$this->late_receipts = $receipts['late'];
		$this->current_receipts = $receipts['current'];
		$this->sendable_receipts = array_merge( $this->late_receipts, $this->current_receipts );
		$this->sent_receipts = $vca_asm_finances->get_receipts( $id, array( 'status' => 2, 'data_type' => 'receipt_id' ) );
		$receipts_full = $vca_asm_finances->get_receipts( $id, array( 'status' => 1, 'data_type' => 'all', 'split' => true ) );
		$this->late_receipts_full = $receipts_full['late'];
		$this->current_receipts_full = $receipts_full['current'];
		$this->sendable_receipts_full = array_merge( $this->late_receipts_full, $this->current_receipts_full );
		$this->sent_receipts_full = $vca_asm_finances->get_receipts( $id, array( 'status' => 2, 'data_type' => 'all' ) );

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

	/* ============================= SETTING SECONDARY PARAMETERS ============================= */

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
		$this->action_required_don_balance_several = ( 12 * intval( date( 'Y' ) ) + intval( date( 'n' ) ) > 12 * intval( date( 'Y', $this->balanced_month_don_threshold_stamp ) ) + intval( date( 'n', $this->balanced_month_don_threshold_stamp ) ) + 2 );
		$this->action_required_econ_balance = ( 12 * intval( date( 'Y' ) ) + intval( date( 'n' ) ) > 12 * intval( date( 'Y', $this->balanced_month_econ_threshold_stamp ) ) + intval( date( 'n', $this->balanced_month_econ_threshold_stamp ) ) + 1 );
		$this->action_required_econ_balance_several = ( 12 * intval( date( 'Y' ) ) + intval( date( 'n' ) ) > 12 * intval( date( 'Y', $this->balanced_month_econ_threshold_stamp ) ) + intval( date( 'n', $this->balanced_month_econ_threshold_stamp ) ) + 2 );

		$this->action_required_econ_send_receipts = ! empty( $this->sendable_receipts );
		$this->action_required_econ_send_receipts_late = ! empty( $this->late_receipts );
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
	public function set_messages( $short = false, $formatted = false, $linked = false )
	{
		$append_key = true === $short ? '_short' : '';

		$span_warning = true === $formatted ? '<span class="warning">' : '';
		$span_neutral = true === $formatted ? '<span class="neutral-msg">' : '';
		$span_close = true === $formatted ? '</span>' : '';

		$anchor_city_overview = true === $formatted ? '<a title="' . $this->link_title . '" href="' . $this->url . '&cid=' . $this->id . '&referrer=' . $this->referrer . '">' : '';
		$anchor_econ_revenue = true === $formatted ? '<a title="' . $this->link_title . '" href="' . $this->url . '-accounts-econ&acc_type=econ&tab=revenue&cid=' . $this->id . '&referrer=' . $this->referrer . '">' : '';
		$anchor_econ_expenditure = true === $formatted ? '<a title="' . $this->link_title . '" href="' . $this->url . '-accounts-econ&acc_type=econ&tab=expenditure&cid=' . $this->id . '&referrer=' . $this->referrer . '">' : '';
		$anchor_econ_transfer = true === $formatted ? '<a title="' . $this->link_title . '" href="' . $this->url . '-accounts-econ&acc_type=econ&tab=transfer&cid=' . $this->id . '&referrer=' . $this->referrer . '">' : '';
		$anchor_don_donation = true === $formatted ? '<a title="' . $this->link_title . '" href="' . $this->url . '-accounts-donations&acc_type=donations&tab=donation&cid=' . $this->id . '&referrer=' . $this->referrer . '">' : '';
		$anchor_don_transfer = true === $formatted ? '<a title="' . $this->link_title . '" href="' . $this->url . '-accounts-donations&acc_type=donations&tab=transfer&cid=' . $this->id . '&referrer=' . $this->referrer . '">' : '';
		$anchor_close = true === $formatted ? '</a>' : '';

		if ( $this->action_required ) {
			$this->messages_meta['action_required'] = $span_warning . $this->{'message_strings'.$append_key}['action_required']  . $span_close;
		}

		if ( $this->action_required_city ) {
			$this->messages_meta['city'] = $span_warning . $this->{'message_strings'.$append_key}['city']  . $span_close;
		}

		if ( $this->action_required_office ) {
			$this->messages_meta['office'] = $span_warning . $this->{'message_strings'.$append_key}['office']  . $span_close;
		}

		if ( $this->action_required_econ_city ) {
			$this->messages_meta['econ_city'] = $span_warning . $this->{'message_strings'.$append_key}['econ_city']  . $span_close;
			$this->messages_meta_city['econ'] = $span_warning . $this->{'message_strings'.$append_key}['econ']  . $span_close;
		}

		if ( $this->action_required_don_city ) {
			$this->messages_meta['don_city'] = $span_warning . $this->{'message_strings'.$append_key}['don_city']  . $span_close;
			$this->messages_meta_city['don'] = $span_warning . $this->{'message_strings'.$append_key}['don']  . $span_close;
		}

		if ( $this->action_required_econ_office ) {
			$this->messages_meta['econ_office'] = $span_warning . $this->{'message_strings'.$append_key}['econ_office']  . $span_close;
			$this->messages_meta_office['econ'] = $span_warning . $this->{'message_strings'.$append_key}['econ']  . $span_close;
		}

		if ( $this->action_required_don_office ) {
			$this->messages_meta['don_office'] = $span_warning . $this->{'message_strings'.$append_key}['don_office']  . $span_close;
			$this->messages_meta_office['don'] = $span_warning . $this->{'message_strings'.$append_key}['don']  . $span_close;
		}

		if ( $this->action_required_econ_balance_several ) {
			$this->messages['econ_balance'] = $span_warning . $anchor_city_overview . $this->{'message_strings'.$append_key}['econ_balance_several'] . $anchor_close . $span_close;
			$this->messages_city['econ_balance'] = $span_warning . $anchor_city_overview . $this->{'message_strings'.$append_key}['econ_balance_several'] . $anchor_close . $span_close;
			$this->messages_econ['econ_balance'] = $span_warning . $anchor_city_overview . $this->{'message_strings'.$append_key}['econ_balance_several'] . $anchor_close . $span_close;
			$this->messages_econ_city['econ_balance'] = $span_warning . $anchor_city_overview . $this->{'message_strings'.$append_key}['econ_balance_several'] . $anchor_close . $span_close;
		} elseif ( $this->action_required_econ_balance ) {
			$this->messages['econ_balance'] = $span_warning . $anchor_city_overview . $this->{'message_strings'.$append_key}['econ_balance'] . $anchor_close . $span_close;
			$this->messages_city['econ_balance'] = $span_warning . $anchor_city_overview . $this->{'message_strings'.$append_key}['econ_balance'] . $anchor_close . $span_close;
			$this->messages_econ['econ_balance'] = $span_warning . $anchor_city_overview . $this->{'message_strings'.$append_key}['econ_balance'] . $anchor_close . $span_close;
			$this->messages_econ_city['econ_balance'] = $span_warning . $anchor_city_overview . $this->{'message_strings'.$append_key}['econ_balance'] . $anchor_close . $span_close;
		}

		if ( $this->action_required_don_balance_several ) {
			$this->messages['don_balance'] = $span_warning . $anchor_city_overview . $this->{'message_strings'.$append_key}['don_balance_several'] . $anchor_close . $span_close;
			$this->messages_city['don_balance'] = $span_warning . $anchor_city_overview . $this->{'message_strings'.$append_key}['don_balance_several'] . $anchor_close . $span_close;
			$this->messages_don['don_balance'] = $span_warning . $anchor_city_overview . $this->{'message_strings'.$append_key}['don_balance_several'] . $anchor_close . $span_close;
			$this->messages_don_city['don_balance'] = $span_warning . $anchor_city_overview . $this->{'message_strings'.$append_key}['don_balance_several'] . $anchor_close . $span_close;
		} elseif ( $this->action_required_don_balance ) {
			$this->messages['don_balance'] = $span_warning . $anchor_city_overview . $this->{'message_strings'.$append_key}['don_balance'] . $anchor_close . $span_close;
			$this->messages_city['don_balance'] = $span_warning . $anchor_city_overview . $this->{'message_strings'.$append_key}['don_balance'] . $anchor_close . $span_close;
			$this->messages_don['don_balance'] = $span_warning . $anchor_city_overview . $this->{'message_strings'.$append_key}['don_balance'] . $anchor_close . $span_close;
			$this->messages_don_city['don_balance'] = $span_warning . $anchor_city_overview . $this->{'message_strings'.$append_key}['don_balance'] . $anchor_close . $span_close;
		}

		if ( $this->action_required_econ_transfer ) {
			$this->messages['econ_transfer'] = $span_warning . $anchor_econ_transfer . $this->{'message_strings'.$append_key}['econ_transfer'] . $anchor_close . $span_close;
			$this->messages_city['econ_transfer'] = $span_warning . $anchor_econ_transfer . $this->{'message_strings'.$append_key}['econ_transfer'] . $anchor_close . $span_close;
			$this->messages_econ['econ_transfer'] = $span_warning . $anchor_econ_transfer . $this->{'message_strings'.$append_key}['econ_transfer'] . $anchor_close . $span_close;
			$this->messages_econ_city['econ_transfer'] = $span_warning . $anchor_econ_transfer . $this->{'message_strings'.$append_key}['econ_transfer'] . $anchor_close . $span_close;
		}

		if ( $this->action_required_don_transfer ) {
			$this->messages['don_transfer'] = $span_warning . $anchor_don_transfer . $this->{'message_strings'.$append_key}['don_transfer'] . $anchor_close . $span_close;
			$this->messages_city['don_transfer'] = $span_warning . $anchor_don_transfer . $this->{'message_strings'.$append_key}['don_transfer'] . $anchor_close . $span_close;
			$this->messages_don['don_transfer'] = $span_warning . $anchor_don_transfer . $this->{'message_strings'.$append_key}['don_transfer'] . $anchor_close . $span_close;
			$this->messages_don_city['don_transfer'] = $span_warning . $anchor_don_transfer . $this->{'message_strings'.$append_key}['don_transfer'] . $anchor_close . $span_close;
		}

		if ( $this->action_required_econ_confirm_transfer ) {
			$this->messages['econ_confirm_transfer'] = $span_warning . $anchor_econ_transfer . $this->{'message_strings'.$append_key}['econ_confirm_transfer'] . $anchor_close . $span_close;
			$this->messages_office['econ_confirm_transfer'] = $span_warning . $anchor_econ_transfer . $this->{'message_strings'.$append_key}['econ_confirm_transfer'] . $anchor_close . $span_close;
			$this->messages_econ['econ_confirm_transfer'] = $span_warning . $anchor_econ_transfer . $this->{'message_strings'.$append_key}['econ_confirm_transfer'] . $anchor_close . $span_close;
			$this->messages_econ_office['econ_confirm_transfer'] = $span_warning . $anchor_econ_transfer . $this->{'message_strings'.$append_key}['econ_confirm_transfer'] . $anchor_close . $span_close;
		}

		if ( $this->action_required_don_confirm_transfer ) {
			$this->messages['don_confirm_transfer'] = $span_warning . $anchor_don_transfer . $this->{'message_strings'.$append_key}['don_confirm_transfer'] . $anchor_close . $span_close;
			$this->messages_office['don_confirm_transfer'] = $span_warning . $anchor_don_transfer . $this->{'message_strings'.$append_key}['don_confirm_transfer'] . $anchor_close . $span_close;
			$this->messages_don['don_confirm_transfer'] = $span_warning . $anchor_don_transfer . $this->{'message_strings'.$append_key}['don_confirm_transfer'] . $anchor_close . $span_close;
			$this->messages_don_office['don_confirm_transfer'] = $span_warning . $anchor_don_transfer . $this->{'message_strings'.$append_key}['don_confirm_transfer'] . $anchor_close . $span_close;
		}

		if ( $this->action_required_don_confirm_external_transfer ) {
			$this->messages['don_confirm_external_transfer'] = $span_warning . $anchor_don_transfer . $this->{'message_strings'.$append_key}['don_confirm_external_transfer'] . $anchor_close . $span_close;
			$this->messages_office['don_confirm_external_transfer'] = $span_warning . $anchor_don_transfer . $this->{'message_strings'.$append_key}['don_confirm_external_transfer'] . $anchor_close . $span_close;
			$this->messages_don['don_confirm_external_transfer'] = $span_warning . $anchor_don_transfer . $this->{'message_strings'.$append_key}['don_confirm_external_transfer'] . $anchor_close . $span_close;
			$this->messages_don_office['don_confirm_external_transfer'] = $span_warning . $anchor_don_transfer . $this->{'message_strings'.$append_key}['don_confirm_external_transfer'] . $anchor_close . $span_close;
		}

		if ( $this->action_required_econ_send_receipts_late ) {
			$this->messages['econ_send_receipts'] = $span_warning . $anchor_econ_expenditure . $this->{'message_strings'.$append_key}['econ_send_receipts_late'] . $anchor_close . $span_close;
			$this->messages_city['econ_send_receipts'] = $span_warning . $anchor_econ_expenditure . $this->{'message_strings'.$append_key}['econ_send_receipts_late'] . $anchor_close . $span_close;
			$this->messages_econ['econ_send_receipts'] = $span_warning . $anchor_econ_expenditure . $this->{'message_strings'.$append_key}['econ_send_receipts_late'] . $anchor_close . $span_close;
			$this->messages_econ_city['econ_send_receipts'] = $span_warning . $anchor_econ_expenditure . $this->{'message_strings'.$append_key}['econ_send_receipts_late'] . $anchor_close . $span_close;
		} elseif ( $this->action_required_econ_send_receipts ) {
			$this->messages['econ_send_receipts'] = $span_neutral . $anchor_econ_expenditure . $this->{'message_strings'.$append_key}['econ_send_receipts'] . $anchor_close . $span_close;
			$this->messages_city['econ_send_receipts'] = $span_neutral . $anchor_econ_expenditure . $this->{'message_strings'.$append_key}['econ_send_receipts'] . $anchor_close . $span_close;
			$this->messages_econ['econ_send_receipts'] = $span_neutral . $anchor_econ_expenditure . $this->{'message_strings'.$append_key}['econ_send_receipts'] . $anchor_close . $span_close;
			$this->messages_econ_city['econ_send_receipts'] = $span_neutral . $anchor_econ_expenditure . $this->{'message_strings'.$append_key}['econ_send_receipts'] . $anchor_close . $span_close;
		}

		if ( $this->action_required_econ_confirm_receipts ) {
			$this->messages['econ_confirm_receipts'] = $span_warning . $anchor_econ_expenditure . $this->{'message_strings'.$append_key}['econ_confirm_receipts'] . $anchor_close . $span_close;
			$this->messages_office['econ_confirm_receipts'] = $span_warning . $anchor_econ_expenditure . $this->{'message_strings'.$append_key}['econ_confirm_receipts'] . $anchor_close . $span_close;
			$this->messages_econ['econ_confirm_receipts'] = $span_warning . $anchor_econ_expenditure . $this->{'message_strings'.$append_key}['econ_confirm_receipts'] . $anchor_close . $span_close;
			$this->messages_econ_office['econ_confirm_receipts'] = $span_warning . $anchor_econ_expenditure . $this->{'message_strings'.$append_key}['econ_confirm_receipts'] . $anchor_close . $span_close;
		}
	}

} // class

endif; // class exists

?>