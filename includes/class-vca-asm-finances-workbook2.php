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

	private $name = '';
	private $title = '';

	private $workbook = object;

	private $styles = array(
		array(
			'font' => array(
				'bold'  => false,
				'color' => array( 'rgb' => '000000' ),
				'size'  => 16,
				'name'  => 'Gill Sans MT'
			)
		),
		array(
			'font' => array(
				'bold'  => true,
				'size'  => 14,
				'name'  => 'Calibri'
			)
		)
	);

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

		$this->name = $vca_asm_geography->get_name( $id );

		$this->workbook = new PHPExcel();

		/* Set document properties */
		$this->title = __( 'ASCII_Cells_LC_Accountingbook', 'vca-asm' );
		if ( 'month' === $timeframe ) {
			$this->title .= '_' . iconv( 'UTF-8', 'ASCII//TRANSLIT', strftime( '%B', strtotime( '01.' . $month . '.2014' ) ) );
		}
		$this->title .= '_' . $year;
		if ( 'nation' === $scope ) {
			$this->title .= '_' . $this->name;
		}
		$this->workbook->getProperties()->setCreator( 'Viva con Agua de Sankt Pauli e.V.' )
										->setLastModifiedBy( 'Viva con Agua de Sankt Pauli e.V.' )
										->setTitle( $this->title )
										->setSubject( $this->title )
										->setDescription( '' )
										->setKeywords( '' )
										->setCategory( '' );

		$valid_locale = PHPExcel_Settings::setLocale( _x( 'en_us', 'Excel Locale', 'vca-asm' ) );

		$this->template = $this->template();

		$this->workbook->removeSheetByIndex( 0 );
		$this->cities( $id );
	}

	/**
	 * Creates a raw sheet without data
	 *
	 * @since 1.5
	 * @access public
	 */
	private function template()
	{
		$template = new PHPExcel_Worksheet( $this->workbook, 'Template' );

		$template->getPageSetup()->setOrientation( PHPExcel_Worksheet_PageSetup::ORIENTATION_LANDSCAPE )
									->setPaperSize( PHPExcel_Worksheet_PageSetup::PAPERSIZE_A4 )
									->setHorizontalCentered(false)
									->setVerticalCentered(true);


		$template->getPageMargins()->setTop(0.75)
					->setRight(0.75)
					->setLeft(0.75)
					->getPageMargins()->setBottom(1);


		return $template;
	}

	/**
	 * Iterates over cities
	 *
	 * @since 1.5
	 * @access public
	 */
	public function cities( $parent = 0 )
	{
		global $vca_asm_geography;
		extract( $this->args );

		$cities = $vca_asm_geography->get_all( 'name', 'ASC', 'city' );

		$i = 0;
		foreach ( $cities as $city ) {
			if ( 'global' === $scope || empty( $parent ) || $parent == $vca_asm_geography->has_nation( $city['id'] ) ) {
				$sheet = clone $this->template;
				$sheet->setTitle( $city['name'] );
				$this->workbook->addSheet( $sheet );
				$this->sheet( $i );
				$i++;
			}
		}
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
		//$this->workbook->getStyle('A1:Z99')->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
		$this->workbook->setActiveSheetIndex( $index );
		//this->workbook->getStyle('A1:Z99')->getAlignment()->setVertical(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
		$this->workbook->getActiveSheet()
						->mergeCells( 'B1:J1' )
						->mergeCells( 'B3:J3' )
						->setCellValue( 'B1', 'Für Einnahmen und Ausgaben aus WIRTSCHAFTSGELD' )
						->setCellValue( 'B3', 'Wirtschaftsgeld/Kassenbuch ZELLEN an Hamburg (monatlich)' )
						->setCellValue( 'B7', 'Beleg Nummer' )
						->setCellValue( 'C7', '' )
						->setCellValue( 'D7', '' )
						->setCellValue( 'E7', '' )
						->getStyle( 'B1' )->applyFromArray( $this->styles[0] );
	}

	/**
	 * Writes the file
	 *
	 * @since 1.5
	 * @access public
	 */
	public function output()
	{
		$this->workbook->setActiveSheetIndex(0);

		$writer = PHPExcel_IOFactory::createWriter( $this->workbook, 'Excel5' );
		$writer->save( VCA_ASM_ABSPATH . '/' . $this->title . '.xls' );
	}

} // class

endif; // class exists

?>