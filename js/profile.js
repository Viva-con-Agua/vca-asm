(function($){ // closure

$(document).ready(function() {
	toggleMemSelector();
	if ( 0 == $('.nation-selector select').val() ) {
		$('.city-selector select').prop( 'disabled', true );
	}
});

$('.nation-selector select').change( function() {
	var curNat = $(this).find('option:selected').val();
	if ( 0 == curNat ) {
		$('.city-selector select').find('option[value!=0]').remove();
		$('.city-selector select').prop( 'disabled', true );
	} else {
		if ( null != nationalHierarchy ) {
			$('.city-selector select').find('option[value!=0]').remove();
			for ( var i = 0; i < nationalHierarchy.length; i++ ) {
				if ( curNat == nationalHierarchy[i].id  ) {
					for ( var j = 0; j < nationalHierarchy[i].cities.length; j++ ) {
						$('.city-selector select').append(
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
		$('.city-selector select').prop( 'disabled', false );
	}
	toggleMemSelector();
});

$('.city-selector select').change( function() {
	toggleMemSelector();
});

function toggleMemSelector() {
	if ( $('.city-selector option:selected').hasClass('city')
		|| $('.city-selector option:selected').hasClass('global')
		|| $('.city-selector option:selected').hasClass('please-select')
	) {
		$('.membership-selector').hide();
		$('#membership').removeAttr('checked');
	} else {
		$('.membership-selector').show();
	}
}

})(jQuery); // closure