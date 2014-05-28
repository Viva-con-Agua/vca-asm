<?php

/**
 * VCA_ASM_Workbook class.
 *
 * This class contains properties and methods for creating an Excel File
 * it is a generic class and hence usually extended
 *
 * @package VcA Activity & Supporter Management
 * @since 1.5
 */

if ( ! class_exists( 'VCA_ASM_Finances_Workbook' ) ) :

class VCA_ASM_Workbook
{

	/**
	 * Class Properties
	 *
	 * @since 1.5
	 */
	public $args = array();

	public $title = '';
	public $filename = '';

	public $workbook = object;
	public $template = object;

	public $format = 'xlsx';

	public $styles = array(
		'default' => array(
			'font' => array(
				'bold'  => false,
				'color' => array( 'rgb' => '000000' ),
				'size'  => 9,
				'name'  => 'Museo Sans 300'
			)
		),
		'headline' => array(
			'font' => array(
				'bold'  => false,
				'color' => array( 'rgb' => '009ac7' ),
				'size'  => 14,
				'name'  => 'Museo Slab 500'
			),
			'alignment' => array(
				'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER
			)
		),
		'header' => array(
			'fill' => array(
				'type' => PHPExcel_Style_Fill::FILL_SOLID,
				'color' => array( 'rgb' => 'cccecf' )
			),
			'alignment' => array(
				'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_LEFT
			)
		),
		'tableheader' => array(
			'fill' => array(
				'type' => PHPExcel_Style_Fill::FILL_SOLID,
				'color' => array( 'rgb' => '414042' )
			),
			'font' => array(
				'bold'  => false,
				'color' => array( 'rgb' => 'ffffff' ),
				'size'  => 9,
				'name'  => 'Museo Slab 500'
			),
			'alignment' => array(
				'wrap' => true
			)
		),
		'bold' => array(
			'font' => array(
				'bold'  => true
			)
		),
		'leftbound' => array(
			'alignment' => array(
				'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_LEFT
			)
		),
		'rightbound' => array(
			'alignment' => array(
				'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_RIGHT
			)
		),
		'positive' => array(
			'font' => array(
				'color' => array( 'rgb' => 'ff0000' )
			)
		),
		'negative' => array(
			'font' => array(
				'color' => array( 'rgb' => '00ff00' )
			)
		)
	);

	public $template_col_range = 0;
	public $template_row_range = 0;

	public $col_range = 1;
	public $row_range = 1;

	public $output_method = 'download';

	/**
	 * Constructor
	 *
	 * @since 1.5
	 * @access public
	 */
	public function __construct( $args = array() )
	{
		$this->init( $args );
	}

	/**
	 * Sets up a basic document
	 * "Parent Constructor"
	 *
	 * @since 1.5
	 * @access public
	 */
	public function init( $args = array() )
	{
		$default_args = array(
			'gridlines' => true,
			'creator' => 'Viva con Agua de Sankt Pauli e.V.',
			'title' => 'Document',
			'subject' => __( 'Accounting', 'vca-asm' ),
			'filename' => 'Document'
		);
		$args = wp_parse_args( $args, $default_args );
		$this->args = $args;
		extract( $args );
		$this->title = empty( $this->title ) ? $title : $this->title;
		$this->filename = empty( $this->filename ) ? str_replace( ' ', '_', $filename ) : $this->filename;

		$this->workbook = new PHPExcel();

		$this->workbook->getSecurity()->setLockWindows( false )
										->setLockStructure( false );

		$this->workbook->getProperties()->setCreator( $creator )
										->setLastModifiedBy( $creator )
										->setTitle( $this->title )
										->setSubject( $subject )
										->setDescription( '' )
										->setKeywords( '' )
										->setCategory( '' );

		PHPExcel_Shared_Font::setAutoSizeMethod( PHPExcel_Shared_Font::AUTOSIZE_METHOD_EXACT );

		$valid_locale = PHPExcel_Settings::setLocale( _x( 'en_us', 'Excel Locale', 'vca-asm' ) );

		$this->workbook->getDefaultStyle()->getAlignment()->setVertical( PHPExcel_Style_Alignment::VERTICAL_CENTER )
			->setHorizontal( PHPExcel_Style_Alignment::HORIZONTAL_CENTER );

		$this->workbook->getDefaultStyle()->getFont()
			->setName( 'Museo Sans 300' )
			->setSize( 9 );

		$this->template = $this->template();
	}

	/**
	 * Creates a raw sheet without data
	 *
	 * @since 1.5
	 * @access public
	 */
	private function template( $args = array() )
	{
		$default_args = array(
			'gridlines' => true,
			'orientation' => 'portrait'		// unused
		);
		$args = wp_parse_args( $args, $default_args );
		extract( $args );

		$template = new PHPExcel_Worksheet( $this->workbook, 'Template' );

		$template->getPageSetup()->setOrientation( PHPExcel_Worksheet_PageSetup::ORIENTATION_PORTRAIT )
			->setPaperSize( PHPExcel_Worksheet_PageSetup::PAPERSIZE_A4 )
			->setHorizontalCentered( false )
			->setVerticalCentered( true );

		$template->getPageMargins()->setTop( 0.75 )
					->setRight( 0.75 )
					->setLeft( 0.75 )
					->setBottom( 1 );

		$template->setShowGridlines( $gridlines );

		return $template;
	}

	/**
	 * Takes a number and converts it to a-z,aa-zz,aaa-zzz, etc with uppercase option
	 *
	 * @since 1.5
	 * @access public
	 * @param int number to convert
	 * @param bool upper case the letter on return?
	 * @return string letters from number input
	*/

	public function num_to_letter( $num, $uppercase = true )
	{
		$num -= 1;
		$letter = chr( ( $num % 26 ) + 97 );
		$letter .= ( floor( $num / 26 ) > 0) ? str_repeat( $letter, floor( $num / 26 ) ) : '';
		return ( $uppercase ? strtoupper( $letter ) : $letter );
	}

	/**
	 * Writes the file
	 *
	 * @since 1.5
	 * @access public
	 */
	public function output()
	{
		$this->col_range = $this->col_range < $this->template_col_range ? $this->template_col_range : $this->col_range;
		$this->row_range = $this->row_range < $this->template_row_range ? $this->template_row_range : $this->row_range;
		$iterator = $this->workbook->getWorksheetIterator();
		foreach ( $iterator as $sheet ) {
			$si = $iterator->key();
			$this->workbook->setActiveSheetIndex( $si );
			for ( $c = 1; $c <= $this->col_range; $c++ ) {
				$this->workbook->getActiveSheet()->getColumnDimension( $this->num_to_letter( $c, true ) )->setAutoSize( true );
			}
			$this->workbook->getActiveSheet()->calculateColumnWidths();
			for ( $c = 1; $c <= $this->col_range; $c++ ) {
				$this->workbook->getActiveSheet()->getColumnDimension( $this->num_to_letter( $c, true ) )->setAutoSize( false );
				$width = $this->workbook->getActiveSheet()->getColumnDimension( $this->num_to_letter( $c, true ) )->getWidth();
				$this->workbook->getActiveSheet()->getColumnDimension( $this->num_to_letter( $c, true ) )->setWidth( ( $width + 7 ) * .65 );
			}
			for ( $r = 1; $r <= $this->row_range; $r++ ) {
				if ( ! in_array( $r, array( 1, 4 ) ) ) {
					$this->workbook->getActiveSheet()->getRowDimension( $r )->setRowHeight( -1 );
				}
			}
		}

		$this->workbook->setActiveSheetIndex( 0 );
		$this->workbook->getActiveSheet()->setSelectedCells('A1');

		switch ( $this->format ) {
			case 'xls':
				$writer = new PHPExcel_Writer_Excel5( $this->workbook );
				$extension = '.xls';
				$mime_type = 'application/vnd.ms-excel';
			break;

			case 'xlsx2003':
				$writer = new PHPExcel_Writer_Excel2007( $this->workbook );
				$writer->setOffice2003Compatibility( true );
				$extension = '.xlsx';
				$mime_type = 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet';
			break;

			case 'xlsx':
			default:
				$writer = new PHPExcel_Writer_Excel2007( $this->workbook );
				$extension = '.xlsx';
				$mime_type = 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet';
			break;
		}

		if ( 'download' === $this->output_method ) {
			header( 'Content-Type: ' . $mime_type );
			header( 'Content-Disposition: attachment; filename="' . $this->filename . $extension . '"' );
			header( 'Cache-Control: max-age=0' );
			$save_param = 'php://output';
		} else {
			$save_param = VCA_ASM_ABSPATH . '/' . $this->filename . $extension;
		}

		$writer->save( $save_param );
		exit; // VERY important! (5h of Bug-Searching resulted in this line...)
	}

} // class

endif; // class exists

?>