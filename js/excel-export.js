/*
 * Rather shitty solution to trigger spreadsheet download
 *
 * function called from onClick attribute, hence no closure
 *
 **/

function p1exportExcel( theType ) {
	if ( 'applicants' !== theType && 'waiting' !== theType ) {
		theType = 'participants';
	}
	alert( theType );
	jQuery('#excel-frame').attr( 'src', excelParams.relpath + 'ajax/export-excel.php?activity=' + excelParams.pID + '&type=' + theType );
	return false;
}

function p1exportExcelMin( theType ) {
	if ( 'applicants' !== theType && 'waiting' !== theType ) {
		theType = 'participants';
	}
	jQuery('#excel-frame').attr( 'src', excelParams.relpath + 'ajax/export-excel-minimal.php?activity=' + excelParams.pID + '&type=' + theType );
	return false;
}