(function($){ // closure

$(document).ready(function() {
	toggleTimeframeOptions( $('select#timeframe').val(), 0 );
});

$('select#timeframe').change(function() {
	toggleTimeframeOptions( $(this).val(), 400 );
});

function toggleTimeframeOptions( theVal, animationDelay ) {
	if ( 'total' == theVal ) {
		$('select#year').prop( 'disabled', true ).removeClass('required').closest('tr').hide( animationDelay );
		$('select#month').prop( 'disabled', true ).removeClass('required').closest('tr').hide( animationDelay );
	} else if ( 'year' == theVal ) {
		$('select#year').prop( 'disabled', false ).addClass('required').closest('tr').show( animationDelay );
		$('select#month').prop( 'disabled', true ).removeClass('required').closest('tr').hide( animationDelay );
	} else {
		$('select#year').prop( 'disabled', false ).addClass('required').closest('tr').show( animationDelay );
		$('select#month').prop( 'disabled', false ).addClass('required').closest('tr').show( animationDelay );
	}
}

})(jQuery); // closure