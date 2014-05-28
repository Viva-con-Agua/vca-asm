<?php

/**
 * VCA_ASM_Workbook_Finances class.
 *
 * This class contains properties and methods for
 * the output of financial account statements as M$ Excel Spreadsheets
 *
 * @package VcA Activity & Supporter Management
 * @since 1.5
 */

if ( ! class_exists( 'VCA_ASM_Workbook_Finances' ) ) :

class VCA_ASM_Workbook_Finances extends VCA_ASM_Workbook
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
		'year' => 2014,
		'month' => 1,
		'type' => 'city',
		'format' => 'xlsx',
		'gridlines' => true
	);
	public $args = array();

	private $initial_time = NULL;

	public $nation_name = 'Germany';

	public $top_row_range = 0;

	public $title_start = '';
	public $title_frame_name = '';
	public $title_frame_data = '';
	public $title_type = '';
	public $title_scope = '';

	/**
	 * Constructor
	 *
	 * @since 1.5
	 * @access public
	 */
	public function __construct( $args = array() )
	{
		global $current_user,
			$vca_asm_geography;

		$this->default_args['id'] = get_user_meta( $current_user->ID, 'nation', true );
		$this->default_args['month'] = date( 'm' );
		$this->default_args['year'] = date( 'Y' );

		$this->args = wp_parse_args( $args, $this->default_args );
		extract( $this->args );

		$this->nation_name = $vca_asm_geography->get_name( $id );

		/* document properties */
		$this->title_start = __( 'ASCII Cells LC', 'vca-asm' ) . ' ' . __( 'Account Statement', 'vca-asm' );
		switch ( $timeframe ) {
			case 'month':
				$this->title_frame_name = __( 'Month', 'vca-asm' );
				$this->title_frame_data = iconv( 'UTF-8', 'ASCII//TRANSLIT', strftime( '%B %Y', strtotime( '01.' . $month . '.' . $year ) ) );
				$this->initial_time = mktime( 23, 59, 59, ($month - 1), date( 't', mktime( 0, 0, 0, ($month - 1), 15, $year ) ), $year );
			break;

			case 'year':
				$this->title_frame_name = __( 'Year', 'vca-asm' );
				$this->title_frame_data = $year;
				$this->initial_time = mktime( 23, 59, 59, 12, 31, ($year - 1) );
			break;

			case 'total':
			default:
				$this->title_frame_name = __( 'Total', 'vca-asm' );
				$this->title_frame_data = '';
			break;
		};
		if ( 'nation' === $type ) {
			$this->title_type = __( 'by country', 'vca-asm' );
		} else {
			$this->title_type = __( 'by city', 'vca-asm' );
		}
		if ( 'nation' === $scope ) {
			$this->title_scope = $this->nation_name;
		}

		$this->format = $this->args['format'];

		$this->args['creator'] = 'Viva con Agua de Sankt Pauli e.V.';
		$this->args['title'] = preg_replace( '!\s+!', ' ', $this->title_start . ': ' . $this->title_frame_name . ' ' . $this->title_frame_data . ', ' . $this->title_type . ( ! empty( $this->title_scope ) ? ' (' . $this->title_scope . ')' : '' ) );
		$this->args['filename'] = str_replace( ' ', '_', str_replace( array( ',', ':', ';', '?', '.', '!' ), '', $this->args['title'] ) );
		$this->args['subject'] = __( 'Accounting', 'vca-asm' );

		$this->init( $this->args );

		$this->customize_template();

		if ( $this->sheets( $this->cities() ) ) {
			$this->workbook->removeSheetByIndex( 0 );
		}
	}

	/**
	 * Adds to the template worksheet
	 *
	 * @since 1.5
	 * @access public
	 */
	public function customize_template( $type = 'city' )
	{
		extract( $this->args );

		$this->template->getPageSetup()->setOrientation( PHPExcel_Worksheet_PageSetup::ORIENTATION_LANDSCAPE );

		$this->template->mergeCells( 'A1:O1' )
				->freezePane( 'B5' )
				->setCellValue( 'A1', strtoupper( __( 'Account Statement', 'vca-asm' ) . ': ' . __( 'Revenues & Expenses', 'vca-asm' ) . ', ' . __( 'Structural Funds', 'vca-asm' ) ) );

		$this->template->setCellValue( 'B2', __( 'City', 'vca-asm' ) )
			->setCellValue( 'B3', __( 'Country', 'vca-asm' ) )
			->setCellValue( 'D2', __( 'Month', 'vca-asm' ) )
			->setCellValue( 'D3', __( 'Year', 'vca-asm' ) )
			->setCellValue( 'F2', __( 'as of', 'vca-asm' ) )
			->setCellValue( 'E2', ( ! empty( $month ) ? strftime( '%B', strtotime( '01.' . $month . '.' . $year ) ) : '---') )
			->setCellValue( 'E3', $year )
			->setCellValue( 'G2', strftime( '%d.%m.%Y, %k:%M', time() ) );

		$cur_row = 4;

		$this->template->setCellValue( 'B'.$cur_row, __( 'Receipt No.', 'vca-asm' ) )
			->setCellValue( 'C'.$cur_row, __( 'Cash Account', 'vca-asm' ) )
			->setCellValue( 'D'.$cur_row, __( 'Booking Date', 'vca-asm' ) )
			->setCellValue( 'E'.$cur_row, __( 'Entry Date', 'vca-asm' ) )
			->setCellValue( 'F'.$cur_row, __( 'Receipt Date', 'vca-asm' ) )
			->setCellValue( 'G'.$cur_row, __( 'Source', 'vca-asm' ) . "\n" . __( '(what was bought)', 'vca-asm' ) )
			->setCellValue( 'H'.$cur_row, _x( 'Expense- /', 'Expense-Account', 'vca-asm' ) . "\n" . __( 'Income-Account', 'vca-asm' ) )
			->setCellValue( 'I'.$cur_row, __( 'COST1', 'vca-asm' ) )
			->setCellValue( 'J'.$cur_row, __( 'COST2', 'vca-asm' ) )
			->setCellValue( 'K'.$cur_row, __( 'Receiptfield1', 'vca-asm' ) )
			->setCellValue( 'L'.$cur_row, __( 'BU-Key', 'vca-asm' ) )
			->setCellValue( 'M'.$cur_row, __( 'Deposit / Withdrawal', 'vca-asm' ) )
			->setCellValue( 'N'.$cur_row, __( 'Revenue Tax', 'vca-asm' ) )
			->setCellValue( 'O'.$cur_row, _x( 'Balance', 'Saldo', 'vca-asm' ) );

		$this->top_row_range = $cur_row;

		$this->template->setCellValue( 'A'.($cur_row+1), __( 'Previous Stock', 'vca-asm' ) );
		$this->top_row_range++;
		$this->template_row_range = $this->top_row_range;

		$this->template->setCellValue( 'A'.($cur_row+2), __( 'Sum', 'vca-asm' ) )
			->setCellValue( 'A'.($cur_row+3), _x( 'Balance', 'Saldo', 'vca-asm' ) );
		$this->template_row_range = $this->template_row_range + 2;

		$this->template_col_range = 15;

		$this->template->setShowGridlines( $gridlines );
	}

	/**
	 * Iterates over cities
	 *
	 * @since 1.5
	 * @access public
	 */
	public function cities( $parent = 0 )
	{
		global $vca_asm_finances, $vca_asm_geography;
		extract( $this->args );

		$sheets = array();

		$cities = $vca_asm_geography->get_all( 'name', 'ASC', 'city' );

		foreach ( $cities as $city ) {
			$city_id = $city['id'];
			$current_parent = $vca_asm_geography->has_nation( $city_id );

			if ( 'global' === $scope || empty( $parent ) || $parent == $current_parent ) {
				$nation_name = $current_parent ? $vca_asm_geography->get_name( $current_parent ) : __( 'No Country', 'vca-asm' );

				switch ( $type ) {
					case 'total':
						$key = __( 'Total', 'vca-asm' );
						$id = 0;
					break;

					case 'nation':
						$key = $nation_name;
						$id = $current_parent;
					break;

					case 'city':
					default:
						$key = $city['name'];
						$id = $city_id;
						$city_type = $vca_asm_geography->get_type( $city_id, true, false, true );
					break;
				}

				if ( ! array_key_exists( $key, $sheets ) ) {
					$sheets[$key] = array();

					$sheets[$key]['id'] = $id;
					$sheets[$key]['name'] = $key . ( 'city' === $type ? ' (' . $city_type . ')' : '' );
					$sheets[$key]['city_name'] = 'city' === $type ? $city['name'] . ' (' . $city_type . ')' : '---';
					$sheets[$key]['nation_name'] = 'total' === $type ? '---' : $nation_name;

					if ( ! isset( $sheets[$key]['city_ids'] ) ) {
						$sheets[$key]['city_ids'] = array();
					}
					$sheets[$key]['city_ids'][] = $city_id;

					$sheets[$key]['initial_balance'] = $vca_asm_finances->get_balance( $city_id, 'econ', $this->initial_time );
				}
			}
		}

		ksort( $sheets );

		return $sheets;
	}

	/**
	 * Iterates over ready-prepped sheets
	 *
	 * @since 1.5
	 * @access public
	 */
	public function sheets( $sheets = array() )
	{
		global $vca_asm_finances;
		extract( $this->args );

		$i = 0;

		foreach ( $sheets as $sheet_params ) {

			$transactions = $vca_asm_finances->get_transactions(
				array(
					'id' => $sheet_params['id'],
					'scope' => $type,
					'account_type' => 'econ',
					'transaction_type' => 'all',
					'year' => $year,
					'month' => $month,
					'orderby' => 'transaction_date',
					'order' => 'ASC'
				)
			);

			$sheet = clone $this->template;

			$sheet->setTitle( $sheet_params['name'] )
				->setCellValue( 'C2', $sheet_params['city_name'] )
				->setCellValue( 'C3', $sheet_params['nation_name'] );

			$sum = 0;
			$cur_row = $this->top_row_range + 1;

			foreach ( $transactions as $transaction ) {

				$sum += intval( $transaction['amount'] );

				$sheet->insertNewRowBefore( $cur_row, 1 );

				$tax_rate = ! empty( $transaction['meta_3'] ) ? $vca_asm_finances->get_tax_rate( $transaction['meta_3'] ) : NULL;

				if ( 'revenue' === $transaction['transaction_type'] ) {
					$cost2 = '4';
					if ( 7 == $tax_rate ) {
						$bu_key = '2';
					} elseif ( 19 == $tax_rate ) {
						$bu_key = '3';
					}
				} else {
					$cost2 = '1';
					$bu_key = '';
				}

				$sheet->setCellValue( 'B'.$cur_row, $transaction['receipt_id'] )
					->setCellValue( 'C'.$cur_row, $vca_asm_finances->get_cash_account( $transaction['city_id'] ) )
					->setCellValue( 'D'.$cur_row, strftime( '%d.%m.%Y', intval( $transaction['transaction_date'] ) ) )
					->setCellValue( 'E'.$cur_row, strftime( '%d.%m.%Y', intval( $transaction['entry_time'] ) ) )
					->setCellValue( 'F'.$cur_row, ! empty( $transaction['receipt_date'] ) && is_numeric( $transaction['receipt_date'] ) ? strftime( '%d.%m.%Y', intval( $transaction['receipt_date'] ) ) : '' )
					->setCellValue( 'G'.$cur_row, ! empty( $transaction['meta_4'] ) ? $transaction['meta_4'] : '' )
					->setCellValue( 'H'.$cur_row, ! empty( $transaction['ei_account'] ) ? $vca_asm_finances->get_ei_account( $transaction['ei_account'], true ) : '' )
					->setCellValue( 'I'.$cur_row, '210' )
					->setCellValue( 'J'.$cur_row, $cost2 )
					->setCellValue( 'K'.$cur_row, '' )
					->setCellValue( 'L'.$cur_row, '=IF(AND(J'.$cur_row.'=4,N'.$cur_row.'=19),"3",IF(AND(J'.$cur_row.'=4,N'.$cur_row.'=7),"2",""))' )
					->setCellValue( 'M'.$cur_row, $transaction['amount']/100 )
					->setCellValue( 'N'.$cur_row, is_numeric( $tax_rate ) ? $tax_rate : '' )
					->setCellValue( 'O'.$cur_row, '' );

					$cur_row++;
			}

			$sheet->setCellValue( 'O' . $this->top_row_range, number_format( $sheet_params['initial_balance']/100, 2, '.', ',' ) )
				/* Static Values */
				//->setCellValue( 'O'.($cur_row), number_format( $sum/100, 2, '.', ',' ) )
				//->setCellValue( 'O'.($cur_row+1), number_format( ( $sheet_params['initial_balance'] + $sum )/100, 2, '.', ',' ) );
				/* Excel Formulae */
				->setCellValue( 'O' . $cur_row, '=SUM(M' . $this->top_row_range . ':M' . ( $cur_row - 1 ) . ')' )
				->setCellValue( 'O' . ( $cur_row + 1 ), '=O' . $this->top_row_range . '+O' . $cur_row );

			$sheet->setSelectedCells('A1');

			$this->workbook->addSheet( $sheet );

			$this->style_sheet( $i + 1 );
			$i++;
		}

		return ( 0 < $i );
	}

	/**
	 * A single Worksheet
	 *
	 * @since 1.5
	 * @access public
	 */
	public function style_sheet( $index = 0 )
	{
		$this->workbook->setActiveSheetIndex( $index );

		$this->workbook->getActiveSheet()->getStyle('A1:' . $this->workbook->getActiveSheet()->getHighestColumn() . '3')->applyFromArray( $this->styles['header'] );
		$this->workbook->getActiveSheet()->getStyle('B2:B3')->applyFromArray( $this->styles['bold'] );
		$this->workbook->getActiveSheet()->getStyle('D2:D3')->applyFromArray( $this->styles['bold'] );
		$this->workbook->getActiveSheet()->getStyle('F2:F3')->applyFromArray( $this->styles['bold'] );

		$this->workbook->getActiveSheet()->getRowDimension( '1' )->setRowHeight( 24 );
		$this->workbook->getActiveSheet()->getStyle('A1')->applyFromArray( $this->styles['headline'] );

		$this->workbook->getActiveSheet()->getStyle('A4:' . $this->workbook->getActiveSheet()->getHighestColumn() . '4')->applyFromArray( $this->styles['tableheader'] );
		$this->workbook->getActiveSheet()->getRowDimension( '4' )->setRowHeight( 24 );

		$this->workbook->getActiveSheet()->getStyle(
		    'A4:A' .
			$this->workbook->getActiveSheet()->getHighestRow()
		)->applyFromArray( $this->styles['tableheader'] );
		$this->workbook->getActiveSheet()->getStyle(
		    'A4:A' .
			$this->workbook->getActiveSheet()->getHighestRow()
		)->applyFromArray( $this->styles['leftbound'] );

		$this->workbook->getActiveSheet()->getStyle(
			'M5:M' .
			$this->workbook->getActiveSheet()->getHighestRow()
		)->applyFromArray( $this->styles['rightbound'] )
			->getNumberFormat()->setFormatCode('[Black][>=0]#,##0.00;[Red][<0]-#,##0.00;');

		$this->workbook->getActiveSheet()->getStyle(
			$this->workbook->getActiveSheet()->getHighestColumn() .
		    '5:' .
			$this->workbook->getActiveSheet()->getHighestColumn() .
			$this->workbook->getActiveSheet()->getHighestRow()
		)->applyFromArray( $this->styles['rightbound'] )
			->applyFromArray( $this->styles['bold'] )
			->getNumberFormat()->setFormatCode('[Black][>=0]#,##0.00;[Red][<0]-#,##0.00;');
;
	}

} // class

endif; // class exists

?>