(function($){ // closure

$(document).ready(function() {
	toggleMetaThree( $('input:radio[name=cash]:checked').val(), 0 );
});

$('input:radio[name=cash]').change(function() {
	toggleMetaThree( $(this).val(), 400 );
});

function toggleMetaThree( theVal, animationDelay ) {
	if ( 0 == theVal ) {
		$('input#meta_3').prop( 'disabled', false ).addClass('required').closest('tr').show( animationDelay );
	} else {
		$('input#meta_3').prop( 'disabled', true ).removeClass('required').closest('tr').hide( animationDelay );
	}
}

})(jQuery); // closure