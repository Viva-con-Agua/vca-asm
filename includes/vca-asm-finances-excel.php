<?php

require_once dirname(__FILE__) . '/../lib/PHPExcel.php';

$style_array = array(
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

/* New Document */
$theExcel = new PHPExcel();

/* Set document properties */
$theExcel->getProperties()->setCreator( "Viva con Agua de Sankt Pauli e.V." )
	->setLastModifiedBy( "Viva con Agua de Sankt Pauli e.V." )
	->setTitle( "ASCII_Zellen_LC_Kassenbuch_MAERZ_2014" )
	->setSubject( "ASCII_Zellen_LC_Kassenbuch_MAERZ_2014" )
	->setDescription( "" )
	->setKeywords( "" )
	->setCategory( "" );

/* Prep first sheet */
$theExcel->setActiveSheetIndex(0)
	->getStyle('A1:Z99')->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
$theExcel->setActiveSheetIndex(0)
	->getStyle('A1:Z99')->getAlignment()->setVertical(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
$theExcel->getActiveSheet()
	->mergeCells( 'B1:J1' )
	->mergeCells( 'B3:J3' )
	->setCellValue( 'B1', 'Für Einnahmen und Ausgaben aus WIRTSCHAFTSGELD' )
	->setCellValue( 'B3', 'Wirtschaftsgeld/Kassenbuch ZELLEN an Hamburg (monatlich)' )
	->setCellValue( 'B7', 'Beleg Nummer' )
	->setCellValue( 'C7', '' )
	->setCellValue( 'D7', '' )
	->setCellValue( 'E7', '' )
	->setCellValue( '7', '' )
	->setCellValue( '7', '' )
	->setCellValue( '7', '' )
	->setCellValue( '7', '' )
	->setCellValue( '7', '' )
	->setCellValue( '7', '' )
	->getStyle( 'B1' )->applyFromArray( $style_array[0] );

$i = 0;
foreach ( array( 'Hamburg', 'Kiel', 'Kassel' ) as $city ) {

	if ( 0 < $i ) {
		$theExcel->createSheet( NULL, $i );
	}

	$theExcel->setActiveSheetIndex($i);
	$theExcel->getActiveSheet()
		->setTitle( $city );

	$i++;
}

$theExcel->setActiveSheetIndex(0);

$theWriter = PHPExcel_IOFactory::createWriter( $theExcel, 'Excel5' );
$theWriter->save( str_replace( '.php', '.xls', __FILE__) );

?>