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

class VCA_ASM_Finances_Workbook extends VCA_ASM_Workbook
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

	public $nation_name = 'Germany';

	public $top_row_range = 0;

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

		/* Set document properties */
		$this->title = __( 'ASCII_Cells_LC_Accountingbook', 'vca-asm' );
		if ( 'month' === $timeframe ) {
			$this->title .= '_' . iconv( 'UTF-8', 'ASCII//TRANSLIT', strftime( '%B', strtotime( '01.' . $month . '.2014' ) ) );
		}
		$this->title .= '_' . $year;
		if ( 'nation' === $scope ) {
			$this->title .= '_' . $this->nation_name;
		}

		$this->format = $this->args['format'];

		$this->init( $this->args );

		$this->customize_template();
		switch ( $type ) {
			case 'total':
				$added = $this->cities( $id );
			break;

			case 'nation':
				$added = $this->cities( $id );
			break;

			case 'city':
			default:
				$added = $this->cities( $id );
			break;
		}
		if ( $added ) {
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

		$frozen = 4;
		if ( 'month' === $timeframe ) {
			$frozen++;
		}
		if ( in_array( $type, array( 'city', 'nation' ) ) ) {
			$frozen++;
		}

		$this->template->mergeCells( 'A1:N1' )
				->freezePane( 'B' . $frozen )
				->setCellValue( 'A1', 'Kassenbuch: Einnahmen und Ausgaben aus WIRTSCHAFTSGELD' )
				->getStyle('A1');//->applyFromArray($this->styles['header1']);

		if ( 'city' === $type ) {
			$this->template->setCellValue( 'A3', 'Stadt' )
				->setCellValue( 'A4', 'Jahr' )
				->setCellValue( 'B4', $year );
			if ( 'month' === $timeframe ) {
				$this->template->setCellValue( 'A5', 'Monat' )
					->setCellValue( 'B5', strftime( '%B', '1.'.$month.'2014' ) );
			}
		} elseif ( 'nation' === $type ) {
			$this->template->setCellValue( 'A3', 'Land' )
				->setCellValue( 'A4', 'Jahr' )
				->setCellValue( 'B4', $year );
			if ( 'month' === $timeframe ) {
				$this->template->setCellValue( 'A5', 'Monat' )
					->setCellValue( 'B5', strftime( '%B', '1.'.$month.'2014' ) );
			}
		} else {
			$this->template->setCellValue( 'A3', 'Jahr' );
			$this->template->setCellValue( 'B3', $year );
			if ( 'month' === $timeframe ) {
				$this->template->setCellValue( 'A4', 'Monat' )
					->setCellValue( 'B4', strftime( '%B', '1.'.$month.'2014' ) );
			}
		}

		$cur_row = $frozen + 1;

		$this->template->setCellValue( 'A'.$cur_row, 'Beleg Nummer' )
			->setCellValue( 'B'.$cur_row, 'Kassenkonto' )
			->setCellValue( 'C'.$cur_row, 'Datum Buchung' )
			->setCellValue( 'D'.$cur_row, 'Datum Eingabe' )
			->setCellValue( 'E'.$cur_row, 'Datum Beleg' )
			->setCellValue( 'F'.$cur_row, 'Quelle (was gekauft wurde)' )
			->setCellValue( 'G'.$cur_row, 'Aufwands-/Ertragskonto' )
			->setCellValue( 'H'.$cur_row, 'KOST1' )
			->setCellValue( 'J'.$cur_row, 'KOST2' )
			->setCellValue( 'K'.$cur_row, 'Belegfeld1' )
			->setCellValue( 'L'.$cur_row, 'EinZ AusZ' )
			->setCellValue( 'M'.$cur_row, 'BU-Schlüssel' )
			->setCellValue( 'N'.$cur_row, 'Ust Satz' )
			->setCellValue( 'O'.$cur_row, 'Saldo' );

		$this->top_row_range = $cur_row;

		$this->template_col_range = 15;
		$this->template_row_range = $frozen;

		//$this->template->setShowGridlines( false );
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

		$cities = $vca_asm_geography->get_all( 'name', 'ASC', 'city' );

		$i = 0;
		foreach ( $cities as $city ) {
			$cur_row = $this->top_row_range;

			$city_id = $city['id'];
			$the_city_finances = new VCA_ASM_City_Finances( $city_id );

			if ( 'global' === $scope || empty( $parent ) || $parent == $vca_asm_geography->has_nation( $city_id ) ) {

				$name = $city['name'];
				$cash_account = $vca_asm_finances->get_cash_account( $city_id );
				$sum = 0;

				$sheet = clone $this->template;
				$sheet->setTitle( $name );

				$sheet->setCellValue( 'B3', $name );

				$cur_row = $cur_row + 2;
				$sheet->insertNewRowBefore( $cur_row, 1 )
					->setCellValue( 'A'.$cur_row, 'Bestand Vormonat' );
				$pre_month_row = $cur_row;
				$cur_row++;

				$transactions = $vca_asm_finances->get_transactions(
					array(
						'city_id' => $city_id,
						'account_type' => 'econ',
						'transaction_type' => 'all',
						'year' => $year,
						'month' => $month,
						'orderby' => 'transaction_date',
						'order' => 'ASC'
					)
				);

				foreach ( $transactions as $transaction ) {
					$cur_row++;

					$sum += intval( $transaction['amount'] );

					$sheet->insertNewRowBefore( $cur_row+1, 1 )
						->setCellValue( 'A'.$cur_row, $transaction['receipt_id'] )
						->setCellValue( 'B'.$cur_row, $cash_account )
						->setCellValue( 'C'.$cur_row, strftime( '%d.%m.%Y', intval( $transaction['transaction_date'] ) ) )
						->setCellValue( 'D'.$cur_row, strftime( '%d.%m.%Y', intval( $transaction['entry_time'] ) ) )
						->setCellValue( 'E'.$cur_row, ! empty( $transaction['receipt_date'] ) && is_numeric( $transaction['receipt_date'] ) ? strftime( '%d.%m.%Y', intval( $transaction['receipt_date'] ) ) : '' )
						->setCellValue( 'F'.$cur_row, '' )
						->setCellValue( 'G'.$cur_row, ! empty( $transaction['ei_account'] ) ? $vca_asm_finances->get_ei_account( $transaction['ei_account'], true ) : '' )
						->setCellValue( 'H'.$cur_row, '' )
						->setCellValue( 'J'.$cur_row, '' )
						->setCellValue( 'K'.$cur_row, '' )
						->setCellValue( 'L'.$cur_row, number_format( $transaction['amount']/100, 2, ',', '.' ) )
						->setCellValue( 'M'.$cur_row, '' )
						->setCellValue( 'N'.$cur_row, ! empty( $transaction['meta_3'] ) ? $vca_asm_finances->get_tax_rate( $transaction['meta_3'] ) : '' )
						->setCellValue( 'O'.$cur_row, '' );
				}

				$sheet->setCellValue( 'A'.($cur_row+2), 'Summe' )
					->setCellValue( 'O'.($cur_row+2), number_format( $sum/100, 2, ',', '.' ) )
					->setCellValue( 'A'.($cur_row+3), 'Saldo' )
					->setCellValue( 'O'.($cur_row+3), number_format( ( $the_city_finances->balance_econ )/100, 2, ',', '.' ) )
					->setCellValue( 'O'.$pre_month_row, number_format( ( $the_city_finances->balance_econ - $sum )/100, 2, ',', '.' ) );

				$this->workbook->addSheet( $sheet );

				$this->sheet( $i );
				$i++;
			}
		}

		return ( 0 < $i );
	}

	/**
	 * A single Worksheet
	 *
	 * @since 1.5
	 * @access public
	 */
	public function sheet( $index = 0 )
	{
		$this->workbook->setActiveSheetIndex( $index );
	}

} // class

endif; // class exists

?>