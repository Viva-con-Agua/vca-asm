<?php

/**
 * VCA_ASM_Workbook class.
 *
 * This class contains properties and methods for creating an Excel File
 * it is a generic class and hence usually extended
 *
 * @package VcA Activity & Supporter Management
 * @since 1.5
 *
 * Structure:
 * - Properties
 * - Constructor
 * - Worksheet
 * - Utility
 */

if ( ! class_exists( 'VCA_ASM_Workbook' ) ) :

class VCA_ASM_Workbook
{

	/* ============================= CLASS PROPERTIES ============================= */

	/**
	 * Arguments passed to the object in the constructor
	 *
	 * @var array $args
	 * @see constructor
	 * @since 1.5
	 * @access public
	 */
	public $args = array();

	/**
	 * The title of the workbook
	 *
	 * @var string $title
	 * @see constructor
	 * @since 1.5
	 * @access public
	 */
	public $title = '';

	/**
	 * The filename of the workbook
	 *
	 * @var string $filename
	 * @see constructor
	 * @since 1.5
	 * @access public
	 */
	public $filename = '';

	/**
	 * Holds an instance of the PHPExcel class
	 *
	 * @var object $workbook		PHPExcel object
	 * @see constructor
	 * @since 1.5
	 * @access public
	 */
	public $workbook = object;

	/**
	 * Holds an instance of the PHPExcel_Worksheet class
	 *
	 * @var object $template		PHPExcel_Worksheet object
	 * @see method template
	 * @since 1.5
	 * @access public
	 */
	public $template = object;

	/**
	 * Holds the type of workbook file generated
	 *
	 * @var string $format
	 * @since 1.5
	 * @access public
	 */public $format = 'xlsx';

	/**
	 * Holds all applicable styles for later use
	 *
	 * @var array $styles
	 * @since 1.5
	 * @access public
	 */
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

	/**
	 * Utility Property
	 *
	 * @var int $template_col_range
	 * @since 1.5
	 * @access public
	 */
	public $template_col_range = 0;

	/**
	 * Utility Property
	 *
	 * @var int $template_row_range
	 * @since 1.5
	 * @access public
	 */
	public $template_row_range = 0;

	/**
	 * Utility Property
	 *
	 * @var int $col_range
	 * @since 1.5
	 * @access public
	 */
	public $col_range = 1;

	/**
	 * Utility Property
	 *
	 * @var int $row_range
	 * @since 1.5
	 * @access public
	 */
	public $row_range = 1;

	/**
	 * Holds the columns (key) and sizes (value) of those that are not being autosized
	 *
	 * @var int[] $non_autosized_columns
	 * @see method set_non_autosized_columns
	 * @since 1.5
	 * @access public
	 */
	public static $non_autosized_columns = array();

	/**
	 * Whether to offer the file as a 'download' or 'save' it to the disk
	 *
	 * @var string $output_method
	 * @since 1.5
	 * @access public
	 */
	public $output_method = 'download';

	/* ============================= CONSTRUCTOR ============================= */

	/**
	 * Constructor
	 *
	 * @param array $args		(optional) array of parameters, for options and defaults, see the init method
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
	 * @param array $args		(optional) array of parameters, see code
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

	/* ============================= WORKSHEET ============================= */

	/**
	 * Creates a raw sheet without data
	 *
	 * @param array $args			(optional) parameters, so far never actually used
	 * @return object $template		PHPExcel_Worksheet object
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
	 * Writes the file
	 *
	 * @return void
	 *
	 * @since 1.5
	 * @access public
	 */
	public function output()
	{
		$this->set_non_autosized_columns();
		$non_autosized_columns = self::$non_autosized_columns;

		$this->col_range = $this->col_range < $this->template_col_range ? $this->template_col_range : $this->col_range;
		$this->row_range = $this->row_range < $this->template_row_range ? $this->template_row_range : $this->row_range;

		$iterator = $this->workbook->getWorksheetIterator();

		foreach ( $iterator as $sheet ) {

			$si = $iterator->key();
			$this->workbook->setActiveSheetIndex( $si );

			for ( $c = 1; $c <= $this->col_range; $c++ ) {

				$col = $this->int_to_letter( $c, true );
				if ( ! array_key_exists( $col, $non_autosized_columns ) ) {
					$this->workbook->getActiveSheet()->getColumnDimension( $col )->setAutoSize( true );
				} else {
					$this->workbook->getActiveSheet()->getColumnDimension( $col )->setWidth( $non_autosized_columns[$col] );
				}

			}

			$this->workbook->getActiveSheet()->calculateColumnWidths();
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
				$writer->setPreCalculateFormulas( false );
				$extension = '.xls';
				$mime_type = 'application/vnd.ms-excel';
			break;

			case 'xlsx2003':
				$writer = new PHPExcel_Writer_Excel2007( $this->workbook );
				$writer->setOffice2003Compatibility( true );
				$writer->setPreCalculateFormulas( false );
				$extension = '.xlsx';
				$mime_type = 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet';
			break;

			case 'xlsx':
			default:
				$writer = new PHPExcel_Writer_Excel2007( $this->workbook );
				$writer->setPreCalculateFormulas( false );
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
		exit; // VERY important! (5 hours of Bug-Searching resulted in this line...)
	}

	/* ============================= UTILITY METHODS ============================= */

	/**
	 * Takes a number and converts it to a-z,aa-zz,aaa-zzz
	 * Utility to map integer indexed data matrix to Excel column naming scheme
	 *
	 * @param int $int				the number to convert
	 * @param bool $uppercase		return upper- or lowercase format
	 * @return string $letter		the letter(s) representing the input integer
	 *
	 * @since 1.5
	 * @access public
	*/

	public function int_to_letter( $int, $uppercase = true )
	{
		$int -= 1;
		$letter = chr( ( $int % 26 ) + 97 );
		$letter .= ( floor( $int / 26 ) > 0) ? str_repeat( $letter, floor( $int / 26 ) ) : '';
		return ( $uppercase ? strtoupper( $letter ) : $letter );
	}

	/**
	 * Utility / Dummy Method
	 *
	 * Bit of a hack to pass values between parent and child class
	 * Overwritten as a late static binding
	 *
	 * @return array
	 *
	 * @since 1.5
	 * @access public
	 */
	public static function grab_non_autosized_columns()
	{
        return array();
    }

	/**
	 * Setter for property
	 *
	 * @return void
	 *
	 * @see method grab_non_autosized_columns
	 *
	 * @since 1.5
	 * @access public
	 */
    public static function set_non_autosized_columns()
	{
        $non_autosized_columns = static::grab_non_autosized_columns();
		self::$non_autosized_columns = $non_autosized_columns;
    }

} // class

endif; // class exists

?>