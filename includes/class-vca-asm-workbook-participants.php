<?php

/**
 * VCA_ASM_Workbook_Participants class.
 *
 * This class contains properties and methods for
 * the output of participant data as M$ Excel Spreadsheets
 *
 * @package VcA Activity & Supporter Management
 * @since 1.5
 */

if ( ! class_exists( 'VCA_ASM_Workbook_Participants' ) ) :

class VCA_ASM_Workbook_Participants extends VCA_ASM_Workbook
{

	/**
	 * Class Properties
	 *
	 * @since 1.5
	 */
	public $default_args = array(
		'scope' => 'public',
		'id' => 0,
		'group' => 'participants',
		'format' => 'xlsx'
	);
	public $args = array();

	private $name = 'Some Activity';
	private $name_no_whitespaces = 'Some_Activity';
	private $start = 0;
	private $end = 0;
	private $date = 'd-m-Y';

	private $city_id = 0;
	private $nation_id = 0;
	private $delegation = '';

	public static $local_non_autosized_columns = array();

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

		$this->args = wp_parse_args( $args, $this->default_args );
		extract( $this->args );

		$this->name = get_the_title( $id );
		$this->name_no_whitespaces = str_replace( ' ', '_', $this->title );

		$this->start = get_post_meta( $id, 'start_act', true );
		$this->end = get_post_meta( $id, 'end_act', true );
		$this->date = date( 'd-m-Y', $this->start );

		$this->city_id = get_post_meta( $id, 'city', true );
		$this->nation_id = get_post_meta( $id, 'nation', true );
		$this->delegation = get_post_meta( $id, 'delegate', true );

		$this->format = $this->args['format'];

		$this->args['creator'] = 'Viva con Agua de Sankt Pauli e.V.';

		switch ( $group ) {
			case 'applicants':
				$group_name = __( 'Applicant Data', 'vca-asm' );
				$this->args['subject'] = __( 'List of supporters applying to an activity', 'vca-asm' );
			break;

			case 'waiting':
				$group_name = __( 'Waiting List', 'vca-asm' );
				$this->args['subject'] = __( 'List of supporters on the Waiting List for an activity', 'vca-asm' );
			break;

			case 'participants':
			default:
				$group_name = __( 'Participant Data', 'vca-asm' );
				$this->args['subject'] = __( 'List of supporters participating in an activity', 'vca-asm' );
			break;
		}

		$this->args['title'] = preg_replace( '!\s+!', ' ', $group_name . ': ' . $this->name . ' (' . $this->date . ')' );
		$this->args['filename'] = str_replace( ' ', '_', str_replace( array( ',', ':', ';', '?', '.', '!', '(', ')' ), '', $this->args['title'] ) );

		$this->init( $this->args );

		$cur_row = $this->customize_template();

		$this->workbook->addSheet( $this->supporters( $cur_row ) );
		$this->workbook->removeSheetByIndex( 0 );

		$this->style_sheet();
	}

	/**
	 * Adds to the template worksheet
	 *
	 * @since 1.5
	 * @access public
	 */
	public function customize_template()
	{
		global $vca_asm_geography;

		extract( $this->args );

		$this->template->getPageSetup()->setOrientation( PHPExcel_Worksheet_PageSetup::ORIENTATION_LANDSCAPE );

		$this->template->mergeCells( 'A1:' . ( 'private' === $scope ? 'H' : 'E' ) . '1' )
				->freezePane( 'B5' )
				->setCellValue( 'A1', $title );

		$this->template->setCellValue( 'B2', __( 'City', 'vca-asm' ) )
			->setCellValue( 'B3', __( 'Country', 'vca-asm' ) )
			->setCellValue( 'C2', ( ( ! empty( $this->city_id ) && is_numeric( $this->city_id ) ) ? $vca_asm_geography->get_name( $this->city_id ) : '---' ) )
			->setCellValue( 'C3', ( ( ! empty( $this->nation_id ) && is_numeric( $this->nation_id ) ) ? $vca_asm_geography->get_name( $this->nation_id ) : '---' ) )
			->setCellValue( 'D2', __( 'as of', 'vca-asm' ) )
			->setCellValue( 'E2', strftime( '%d.%m.%Y, %k:%M', time() ) );

		$cur_row = 4;

		$this->template->setCellValue( 'A'.$cur_row, __( 'Running Number', 'vca-asm' ) )
			->setCellValue( 'B'.$cur_row, __( 'First Name', 'vca-asm' ) )
			->setCellValue( 'C'.$cur_row, __( 'Last Name', 'vca-asm' ) )
			->setCellValue( 'D'.$cur_row, __( 'City', 'vca-asm' ) );

		$col = 'E';

		if ( $scope === 'private' ) {
			$this->template->setCellValue( $col.$cur_row, __( 'Age', 'vca-asm' ) );
			$col++;
		}

		$this->template->setCellValue( $col.$cur_row, __( 'Email-Address', 'vca-asm' ) );
		$col++;

		if ( 'private' === $scope ) {
			$this->template->setCellValue( $col.$cur_row, __( 'Mobile Phone', 'vca-asm' ) );
			$col++;
			$this->template->setCellValue( $col.$cur_row, _x( 'Present?', 'Supporter is present at event (Column in spreadsheet)', 'vca-asm' ) );
			$col++;
			$this->template->setCellValue( $col.$cur_row, __( 'Note', 'vca-asm' ) );
		}

		$this->top_row_range = $cur_row;
		$this->template_row_range = $this->top_row_range;

		$this->template_col_range = 9;

		$cur_row++;

		$this->define_non_autosized_columns();

		return $cur_row;
	}

	/**
	 * Iterates over supporters
	 *
	 * @since 1.5
	 * @access public
	 */
	public function supporters( $initial_row = 1 )
	{
		global $wpdb,
			$vca_asm_geography, $vca_asm_registrations, $vca_asm_utilities;

		extract( $this->args );

		$suffix = ( is_numeric( $this->end ) && time() > $this->end ) ? '_old' : '';

		switch ( $group ) {
			case 'applicants':
				$fetching_method = 'get_activity_applications' . $suffix;
				$the_db_table = $wpdb->prefix . 'vca_asm_applications' . $suffix;
			break;

			case 'waiting':
				$fetching_method = 'get_activity_waiting';
				$the_db_table = $wpdb->prefix . 'vca_asm_applications' . $suffix;
			break;

			case 'participants':
			default:
				$fetching_method = 'get_activity_participants' . $suffix;
				$the_db_table = $wpdb->prefix . 'vca_asm_registrations' . $suffix;
			break;
		}

		$supporters = $vca_asm_registrations->$fetching_method( $id );

		$rows = array();
		$f_names = array();
		$i = 0;

		foreach( $supporters as $supporter ) {
			$supp_info = get_userdata( $supporter );
			$supp_bday = get_user_meta( $supporter, 'birthday', true );
			if ( ! empty( $supp_bday ) && is_numeric( $supp_bday ) ) {
				$supp_age = $vca_asm_utilities->date_diff( time(), $supp_bday );
			} else {
				$supp_age = '???';
			}
			$notes = $wpdb->get_results(
				"SELECT notes FROM " .
				$the_db_table . " " .
				"WHERE activity=" . $id . " AND supporter=" . $supporter . ' LIMIT 1', ARRAY_A
			);
			$note = str_replace( '"', '&quot;', str_replace( "'", '&apos;', $notes[0]['notes'] ) );
			if ( is_object( $supp_info ) ) {
				if ( 'private' === $scope ) {
					$rows[$i] = array(
						$supp_info->first_name,
						$supp_info->last_name,
						$vca_asm_geography->get_name( get_user_meta( $supporter, 'city', true ) ),
						$supp_age['year'],
						$supp_info->user_email,
						$vca_asm_utilities->normalize_phone_number(
							get_user_meta( $supporter, 'mobile', true ),
							array( 'nice' => true )
						),
						'',
						$note
					);
				} else {
					$rows[$i] = array(
						$supp_info->first_name,
						$supp_info->last_name,
						$vca_asm_geography->get_name( get_user_meta( $supporter, 'city', true ) ),
						$supp_info->user_email,
					);
				}
			} else {
				$rows[$i] = array(
					__( 'Not a member of the Pool anymore...', 'vca-asm' )
				);
			}
			if ( is_object( $supp_info ) ) {
				$f_names[$i] = strtolower( $supp_info->first_name );
			} else {
				$f_names[$i] = 'zzz';
			}
			$i++;
		}
		array_multisort( $f_names, $rows );

		$sheet = clone $this->template;

		$cur_row = $initial_row;
		$r = 1;
		foreach( $rows as $row ) {
			array_unshift( $row, $r );
			$r++;
			$col = 'A';
			foreach ( $row as $value ) {
				$sheet->setCellValue( $col.$cur_row, $value );
				$col++;
			}
			$cur_row++;
		}

		return $sheet;
	}

	/**
	 * A single Worksheet
	 *
	 * @since 1.5
	 * @access public
	 */
	public function style_sheet()
	{
		$this->workbook->getActiveSheet()->getStyle('A1:' . $this->workbook->getActiveSheet()->getHighestColumn() . '3')->applyFromArray( $this->styles['header'] );
		$this->workbook->getActiveSheet()->getStyle('B2:B3')->applyFromArray( $this->styles['bold'] );
		$this->workbook->getActiveSheet()->getStyle('D2:D3')->applyFromArray( $this->styles['bold'] );

		$this->workbook->getActiveSheet()->getRowDimension( '1' )->setRowHeight( 24 );
		$this->workbook->getActiveSheet()->getStyle('A1')->applyFromArray( $this->styles['headline'] );

		$this->workbook->getActiveSheet()->getStyle('A4:' . $this->workbook->getActiveSheet()->getHighestColumn() . '4')->applyFromArray( $this->styles['tableheader'] );
		$this->workbook->getActiveSheet()->getRowDimension( '4' )->setRowHeight( 18 );

		$this->workbook->getActiveSheet()->getStyle(
		    'A4:A' .
			$this->workbook->getActiveSheet()->getHighestRow()
		)->applyFromArray( $this->styles['tableheader'] );
		$this->workbook->getActiveSheet()->getStyle(
		    'A4:A' .
			$this->workbook->getActiveSheet()->getHighestRow()
		)->applyFromArray( $this->styles['leftbound'] );
	}

	/**
	 * Does what the method name suggests
	 *
	 * @since 1.5
	 * @access public
	 */
	public function define_non_autosized_columns() {
		if ( 'private' === $this->args['scope'] ) {
			self::$local_non_autosized_columns = array(
				'E' => 6
			);
		}
	}
	public static function grab_non_autosized_columns() {
		return self::$local_non_autosized_columns;
    }

} // class

endif; // class exists

?>