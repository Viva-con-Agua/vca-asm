<?php

require_once( $_SERVER['DOCUMENT_ROOT'] . '/wp-load.php' );
require_once( $_SERVER['DOCUMENT_ROOT'] . '/wp-content/plugins/vca-asm/includes/class-php2excel.php' );
global $wpdb, $vca_asm_regions, $vca_asm_registrations, $vca_asm_utilities;

$id = $_GET['activity'];
$title = str_replace( ' ', '_', get_the_title( $id ) );
$year = date( 'Y', get_post_meta( $id, 'end_date', true ) );
$filename = __( 'Participant_Data', 'vca-asm' ) . '_' . $title . '_' . $year . '.xls';

$xls = new ExportXLS( $filename );

$header = __( 'Participant Data', 'vca-asm' ) . ': ' . $title . ' (' . $year . ')';
$xls->addHeader($header);

$empty_row = null;
$xls->addHeader( $empty_row );
$xls->addHeader( $empty_row );

$header = array(
	__( 'Running Number', 'vca-asm' ),
	__( 'First Name', 'vca-asm' ),
	__( 'Last Name', 'vca-asm' ),
	__( 'Region', 'vca-asm' ),
	__( 'Age', 'vca-asm' ),
	__( 'Email-Address', 'vca-asm' ),
	__( 'Mobile Phone', 'vca-asm' ),
	__( 'Ticket Received', 'vca-asm' ),
	__( 'Note', 'vca-asm' )
);
$xls->addHeader( $header );

$xls->addHeader( $empty_row );

$registered_supporters = $vca_asm_registrations->get_activity_registrations( $id );

$rows = array();
$f_names = array();
$i = 0;
foreach( $registered_supporters as $supporter ) {
	$supp_info = get_userdata( $supporter );
	$supp_age = $vca_asm_utilities->date_diff( time(), get_user_meta( $supporter, 'birthday', true ) );
	$notes = $wpdb->get_results(
		"SELECT notes FROM " .
		$wpdb->prefix . "vca_asm_registrations " .
		"WHERE activity=" . $id . " AND supporter=" . $supporter . ' LIMIT 1', ARRAY_A
	);
	$note = str_replace( '"', '&quot;', str_replace( "'", '&apos;', $notes[0]['notes'] ) );
	$rows[$i] = array(
		$supp_info->first_name,
		$supp_info->last_name,
		$vca_asm_regions->get_name( get_user_meta( $supporter, 'region', true ) ),
		$supp_age['year'],
		$supp_info->user_email,
		get_user_meta( $supporter, 'mobile', true ),
		'',
		$note
	);
	$f_names[$i] = $supp_info->first_name;
	$i++;
}

array_multisort( $f_names, $rows );

$i = 1;
foreach( $rows as $row ) {
	array_unshift( $row, $i );
	$i++;
	$xls->addRow( $row );
}

$xls->sendFile();

?>