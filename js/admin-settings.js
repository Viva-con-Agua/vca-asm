jQuery(function() {
	for( var i=0; i<VCAasmAdmin.length; i++ ) {
		jQuery( "#" + VCAasmAdmin[i].id + "-slider" ).slider({
			value: VCAasmAdmin[i].value,
			min: VCAasmAdmin[i].min,
			max: VCAasmAdmin[i].max,
			step: VCAasmAdmin[i].step,
			slide: function( event, ui ) {
				var target = jQuery( ui.handle ).parent();
				jQuery( target ).siblings('input').val( ui.value ).trigger('change');
			}
		});
	}
});

jQuery('input.js-hide').change( function() {
	var allOptions = new Object;
	for( var i=0; i<VCAasmAdmin.length; i++ ) {
		allOptions[VCAasmAdmin[i].id] = VCAasmAdmin[i];
	}
	var optionsObj = allOptions[jQuery(this).attr('id')];
	var newVal = jQuery(this).val();
	if ( 'class_change' === optionsObj.callback ) {
		jQuery(this).siblings('div').not('[class*=slider]').removeClass();
		jQuery(this).siblings('div').not('[class*=slider]').addClass( optionsObj.classes[newVal] );
		jQuery(this).siblings('div').not('[class*=slider]').text( optionsObj.content[newVal] );
	} else if( 0 == newVal ) {
		jQuery(this).siblings('span').text( optionsObj.never );
	} else {
		jQuery(this).siblings('span').text( jQuery(this).val() + optionsObj.append );
	}
});