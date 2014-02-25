(function($){
$(function() {
	$( '.datepicker' ).datepicker({
		dateFormat: 'dd.mm.yy',
		monthNames: jquiParams.monthNames,
		dayNamesMin: jquiParams.dayNamesMin
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