(function($){ // closure

$(document).ready( function() {
	$('tr.geo-filter').not('#row-geo-filter-'+filterParams.gfb).hide();
});

$('select#geo-filter-by').change( function() {
	var theVal = $(this).val();
	$('tr.geo-filter').not('#row-geo-filter-'+theVal).hide();
	$('tr#row-geo-filter-'+theVal).show(400);
});

})(jQuery); // closure