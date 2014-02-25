(function($){ // closure

$(document).ready(function() {
	if ( 0 == $('select#nation').val() ) {
		$('select#city').prop( 'disabled', true );
	}
});

$('select#nation').change( function() {
	var curNat = $(this).find('option:selected').val();
	if ( 0 == curNat ) {
		$('select#city').find('option[value!=0]').remove();
		$('select#city').prop( 'disabled', true );
	} else {
		if ( null != nationalHierarchy ) {
			$('select#city').find('option[value!=0]').remove();
			for ( var i = 0; i < nationalHierarchy.length; i++ ) {
				if ( curNat == nationalHierarchy[i].id  ) {
					for ( var j = 0; j < nationalHierarchy[i].cities.length; j++ ) {
						$('select#city').append(
							'<option value=' +
							nationalHierarchy[i].cities[j].id +
							'>' +
							nationalHierarchy[i].cities[j].name +
							'</option>'
						);
					}
				}
			}
		}
		$('select#city').prop( 'disabled', false );
	}
});

})(jQuery); // closure