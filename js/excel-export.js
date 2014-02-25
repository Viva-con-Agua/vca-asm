(function($){ // closure

function exportExcel() {
	$("#excel-frame").attr("src", excelParams.relpath + 'ajax/export-excel.php?activity=' + excelParams.pID);
	return false;
}

})(jQuery); // closure