(function($){ // closure

$(function() {
	for( var i=0; i<settingsOptions.length; i++ ) {
		$( "#" + settingsOptions[i].id + "-slider" ).slider({
			range: 'min',
			value: settingsOptions[i].value,
			min: settingsOptions[i].min,
			max: settingsOptions[i].max,
			step: settingsOptions[i].step,
			slide: function( event, ui ) {
				var target = $( ui.handle ).parent();
				$( target ).siblings('input').val( ui.value ).trigger('change');
			}
		});
		if ( null != hasCap && hasCap.hasOwnProperty('bool') && 0 == hasCap.bool ) {
			$( "#" + settingsOptions[i].id + "-slider" ).slider( 'option', 'disabled', true );
		}
	}
});

$('input.js-hide').change( function() {
	var allOptions = new Object;
	for( var i=0; i<settingsOptions.length; i++ ) {
		allOptions[settingsOptions[i].id] = settingsOptions[i];
	}
	var optionsObj = allOptions[$(this).attr('id')];
	var newVal = $(this).val();
	if ( 'class_change' === optionsObj.callback ) {
		$(this).siblings('div').not('[class*=slider]').removeClass();
		$(this).siblings('div').not('[class*=slider]').addClass( optionsObj.classes[newVal] );
		$(this).siblings('div').not('[class*=slider]').text( optionsObj.content[newVal] );
	} else if( 0 == newVal ) {
		$(this).siblings('span').text( optionsObj.never );
	} else {
		$(this).siblings('span').text( $(this).val() + optionsObj.append );
	}
});

})(jQuery); // closure