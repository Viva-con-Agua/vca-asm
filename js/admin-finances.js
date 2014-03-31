(function($){ // closure

$(document).ready(function() {
	toggleMetaThree( $('input:radio[name=cash]').val() );
});

$('input:radio[name=cash]').change(function() {
	toggleMetaThree( $(this).val() );
});

function toggleMetaThree( theVal ) {
	if ( 0 == theVal ) {
		$('input#meta_3').prop( 'disabled', false ).addClass('required').closest('tr').show( 400 );
	} else {
		$('input#meta_3').prop( 'disabled', true ).removeClass('required').closest('tr').hide( 400 );
	}
}

})(jQuery); // closure