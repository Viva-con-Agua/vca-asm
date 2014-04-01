(function($){
$(function() {
	$('.datepicker').not('.mindate').not('.maxdate').datepicker({
		dateFormat: 'dd.mm.yy',
		monthNames: jquiParams.monthNames,
		dayNamesMin: jquiParams.dayNamesMin
	});
	$('.datepicker.maxdate').not('.mindate').each( function() {
		$(this).datepicker({
			dateFormat: 'dd.mm.yy',
			monthNames: jquiParams.monthNames,
			dayNamesMin: jquiParams.dayNamesMin,
			maxDate: new Date( parseInt( $(this).attr('data-max') ) )
		});
	});
	$('.datepicker.mindate').not('.maxdate').each( function() {
		$(this).datepicker({
			dateFormat: 'dd.mm.yy',
			monthNames: jquiParams.monthNames,
			dayNamesMin: jquiParams.dayNamesMin,
			minDate: new Date( parseInt( $(this).attr('data-min') ) )
		});
	});
	$('.datepicker.mindate.maxdate').each( function() {
		$(this).datepicker({
			dateFormat: 'dd.mm.yy',
			monthNames: jquiParams.monthNames,
			dayNamesMin: jquiParams.dayNamesMin,
			minDate: new Date( parseInt( $(this).attr('data-min') ) ),
			maxDate: new Date( parseInt( $(this).attr('data-max') ) )
		});
	});
});

if ( 'undefined' != typeof(jquiDynamicParams) ) {
	$(function() {
		for( var i=0; i<jquiDynamicParams.sliders.length; i++ ) {
			$( "#" + jquiDynamicParams.sliders[i].id + "-slider" ).slider({
				value: jquiDynamicParams.sliders[i].value,
				min: jquiDynamicParams.sliders[i].min,
				max: jquiDynamicParams.sliders[i].max,
				step: jquiDynamicParams.sliders[i].step,
				slide: function( event, ui ) {
					$( "#" + jquiDynamicParams.sliders[i].id ).val( ui.value );
				}
			});
		}
	});
}

})(jQuery);