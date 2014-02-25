/*
 * Rather shitty solution to trigger spreadsheet download
 *
 * function called from onClick attribute, hence no closure
 *
 **/

function p1exportExcel() {
	jQuery('#excel-frame').attr('src', excelParams.relpath + 'ajax/export-excel.php?activity=' + excelParams.pID);
	return false;
}

function p1exportExcelMin() {
	jQuery('#excel-frame').attr('src', excelParams.relpath + 'ajax/export-excel-minimal.php?activity=' + excelParams.pID);
	return false;
}