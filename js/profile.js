jQuery(document).ready(function() {
	toggleMemSelector();
});

jQuery('.region-selector').change(function() {
	toggleMemSelector()
});

function toggleMemSelector() {
	if( jQuery('.region-selector option:selected').hasClass('region')
		|| jQuery('.region-selector option:selected').hasClass('global')
		|| jQuery('.region-selector option:selected').hasClass('please-select') ) {
		jQuery('.membership-selector').css( 'display', 'none' );
		jQuery('#membership').removeAttr('checked');
	} else {
		jQuery('.membership-selector').css( 'display', 'block' );		
	}
}