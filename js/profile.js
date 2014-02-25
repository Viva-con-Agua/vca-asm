(function($){ // closure

$(document).ready(function() {
	toggleMemSelector();
});

$('.region-selector').change(function() {
	toggleMemSelector()
});

function toggleMemSelector() {
	if( $('.region-selector option:selected').hasClass('region')
		|| $('.region-selector option:selected').hasClass('global')
		|| $('.region-selector option:selected').hasClass('please-select') ) {
		$('.membership-selector').css( 'display', 'none' );
		$('#membership').removeAttr('checked');
	} else {
		$('.membership-selector').css( 'display', 'block' );
	}
}

})(jQuery); // closure