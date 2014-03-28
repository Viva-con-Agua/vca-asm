(function($){ // closure

$(document).ready(function() {
	toggleMemSelector();
	if ( 0 == $('.nation-selector select').val() ) {
		$('.city-selector select').prop( 'disabled', true );
	}
});

})(jQuery); // closure