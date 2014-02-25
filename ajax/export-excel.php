<?php

require_once( $_SERVER['DOCUMENT_ROOT'] . '/wp-load.php' );
require_once( $_SERVER['DOCUMENT_ROOT'] . '/wp-content/plugins/vca-asm/lib/class-php2excel.php' );
global $current_user, $wpdb, $vca_asm_geography, $vca_asm_registrations, $vca_asm_utilities;
get_currentuserinfo();

$id = $_GET['activity'];
$type = ( isset( $_GET['type'] ) && in_array( $_GET['type'], array( 'applicants', 'waiting', 'participants' ) ) ) ? $_GET['type'] : 'participants';
$title = str_replace( ' ', '_', get_the_title( $id ) );

$start_act = get_post_meta( $id, 'start_act', true );
$end_act = get_post_meta( $id, 'end_act', true );
$act_date = date( 'd-m-Y', $start_act );

$act_city = get_post_meta( $id, 'city', true );
$act_nation = get_post_meta( $id, 'nation', true );
$delegation = get_post_meta( $id, 'delegate', true );

if ( 'applicants' === $type ) {
	$type_name = __( 'Applicant_Data', 'vca-asm' );
} elseif ( 'waiting' === $type ) {
	$type_name = __( 'Waiting_List', 'vca-asm' );
} else {
	$type_name = __( 'Participant_Data', 'vca-asm' );
}

$filename = $type_name . '_' . $title . '_' . $act_date;
if (
	( in_array( 'city', $current_user->roles ) || in_array( 'head_of', $current_user->roles ) )
	&&
	'delegate' === $delegation
	&&
	! empty( $act_city )
) {
	$filename .= '_' . str_replace( ' ', '-', $vca_asm_geography->get_type( $act_city ) ) . '-' . $vca_asm_geography->get_name( $act_city );
}
$filename .= '.xls';

$xls = new ExportXLS( $filename );

$header = str_replace( '_', ' ', $type_name ) . ': ' . $title . ' (' . $act_date . ')';
$xls->addHeader($header);

$empty_row = null;
$xls->addHeader( $empty_row );
$xls->addHeader( $empty_row );

$header = array(
	__( 'Running Number', 'vca-asm' ),
	__( 'First Name', 'vca-asm' ),
	__( 'Last Name', 'vca-asm' ),
	__( 'City / Cell / Local Crew', 'vca-asm' ),
	__( 'Age', 'vca-asm' ),
	__( 'Email-Address', 'vca-asm' ),
	__( 'Mobile Phone', 'vca-asm' ),
	__( 'Ticket Received', 'vca-asm' ),
	__( 'Note', 'vca-asm' )
);
$xls->addHeader( $header );

$xls->addHeader( $empty_row );

if ( is_numeric( $end_act ) && time() > $end_act ) {
	if ( 'applicants' === $type ) {
		$the_supporters = $vca_asm_registrations->get_activity_applications_old( $id );
		$the_db_table = $wpdb->prefix . 'vca_asm_applications_old';
	} elseif ( 'waiting' === $type ) {
		$the_supporters = $vca_asm_registrations->get_activity_waiting( $id );
		$the_db_table = $wpdb->prefix . 'vca_asm_applications_old';
	} else {
		$the_supporters = $vca_asm_registrations->get_activity_participants_old( $id );
		$the_db_table = $wpdb->prefix . 'vca_asm_registrations_old';
	}
} else {
	if ( 'applicants' === $type ) {
		$the_supporters = $vca_asm_registrations->get_activity_applications( $id );
		$the_db_table = $wpdb->prefix . 'vca_asm_applications';
	} elseif ( 'waiting' === $type ) {
		$the_supporters = $vca_asm_registrations->get_activity_waiting( $id );
		$the_db_table = $wpdb->prefix . 'vca_asm_applications';
	} else {
		$the_supporters = $vca_asm_registrations->get_activity_registrations( $id );
		$the_db_table = $wpdb->prefix . 'vca_asm_registrations';
	}
}

$rows = array();
$f_names = array();
$i = 0;
foreach( $the_supporters as $supporter ) {
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
		$rows[$i] = array(
			$supp_info->first_name,
			$supp_info->last_name,
			$vca_asm_geography->get_name( get_user_meta( $supporter, 'city', true ) ),
			$supp_age['year'],
			$supp_info->user_email,
			get_user_meta( $supporter, 'mobile', true ),
			'',
			$note
		);
	} else {
		$rows[$i] = array(
			__( 'Not a member of the Pool anymore...', 'vca-asm' )
		);
	}
	if ( is_object( $supp_info ) ) {
		$f_names[$i] = $supp_info->first_name;
	} else {
		$f_names[$i] = 'ZZZZZZ';
	}
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