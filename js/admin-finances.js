(function($){ // closure

$(document).ready(function() {
	toggleMetaThree( $('input:radio[name=cash]').val() );
});

$('input:radio[name=cash]').change(function() {
	toggleMetaThree( $(this).val() );
});

function toggleMetaThree( theVal ) {
	if ( 0 == theVal ) {
		$('input#meta_3').prop( 'disabled', false ).closest('tr').show( 400 );
	} else {
		$('input#meta_3').prop( 'disabled', true ).closest('tr').hide( 400 );
	}
}

})(jQuery); // closure