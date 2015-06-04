(function($){ // closure

$(document).ready( function() {
	$('tr.receipient-group-id').hide();
	if ( activeTab.name === 'activity' ) {
		populateActivitySelect( $('input[name="phases"]:checked').val(), $('input[name="type"]:checked').val() );
	}
});

// Newsletter //

$('select#receipient-group').change( function() {
	var theVal = $(this).val();
	$('tr.receipient-group-id').not('#row-'+theVal+'-id').hide();
	$('tr#row-'+theVal+'-id.receipient-group-id').show(400);
});

// Activity //

$('input[name="phases"]').change( function() {
	populateActivitySelect( $(this).val(), $('input[name="type"]:checked').val() );
});
$('input[name="type"]').change( function() {
	populateActivitySelect( $('input[name="phases"]:checked').val(), $(this).val() );
});

function populateActivitySelect( actPhase, actType ) {
	$('select#activity').empty();
	if ( 0 < actSelOptions[actType][actPhase].length ) {
		var newSelectHTML = '';
		for ( var i = 0; i < actSelOptions[actType][actPhase].length; i++ ) {
			newSelectHTML += '<option value="' + actSelOptions[actType][actPhase][i].value + '"';
			if ( actSelOptions[actType][actPhase][i].value === selectedActivity ) {
				newSelectHTML += ' selected="selected"';
			}
			newSelectHTML += '>' + actSelOptions[actType][actPhase][i].label + '</option>';
		}
		$('span#no-activity').remove();
		$('select#activity').html(newSelectHTML).val(selectedActivity).show(400);
	} else {
		if ( 1 > $('span#no-activity').length ) {
			$('select#activity').hide().after('<span id="no-activity" style="margin:0;">'+noActivity.string+'</span>').hide();
			$('span#no-activity').hide().show(400)
		}
	}
}

})(jQuery); // closure