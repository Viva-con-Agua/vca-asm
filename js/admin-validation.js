(function($){ // closure

var theErrors = new Object();

$('input#publish').click( function () {
	return clickCallback();
});

$('input#submit-validate').click( function () {
	return clickCallback();
});

function clickCallback() {
	validate();

	var valid = true;
	for ( var key in theErrors ) {
		if (theErrors.hasOwnProperty(key)) {
			valid = false;
		}
	}

	if ( ! valid ) {
		errorMessage();
		theErrors = new Object();
		return false;
	}

	return true;
}

function required(el) {
	var theVal = el.val();
	if ( ! el.val() ) {
		el.closest('tr').find('span.required').addClass('empty');
		el.addClass('warning');
		return false;
	} else {
		el.closest('tr').find('span.required').removeClass('empty');
		el.removeClass('warning');
		return true;
	}
}

function numbers(el) {
	var theVal = el.val();

	if ( isNaN(parseFloat(theVal)) || ! isFinite(theVal) ) {
		el.addClass('warning');
		return false;
	} else {
		el.removeClass('warning');
		return true;
	}
}

function phoneNumber(el, lengthCheck) {
	var theVal = el.val();
	theVal = theVal.replace(/\s/g, '');
	if ( theVal.substring(0,1) === '+' ) {
		theVal = theVal.substring(1);
	} else if ( theVal.substring(0,2) === '00' ) {
		theVal = theVal.substring(2);
	}
	if ( ( null != lengthCheck && true === lengthCheck && 6 > theVal.length && 0 < theVal.length ) || ( ! /^\d+$/.test(theVal) && ( 0 !== theVal.length ) ) ) {
		el.addClass('warning');
		return false;
	} else {
		el.removeClass('warning');
		return true;
	}
}

function dateComp(el) {
	var relevantDates = [ 'start_app', 'end_app', 'start_act', 'end_act' ],
		startApp = '',
		endApp = '',
		startDate = '',
		startHour = 0,
		startMin = 0,
		startAct = 0,
		endDate = '',
		endHour = 0,
		endMin = 0,
		endAct = 0,
		isRelevant = false,
		errorIDs = new Array();

	for ( var i = 0; i < relevantDates.length; i++ ) {
		if ( el.attr('id') === relevantDates[i] ) {
			isRelevant = true;
		}
	}

	if ( isRelevant ) {
		$('input.date').each( function() {
			var $this = $(this);
			if ( 0 <= $.inArray( $this.attr('id'), relevantDates ) ) {
				if ( ! $this.hasClass('required') || 0 < $this.val().length ) {
					$this.removeClass('warning');
					$this.siblings('select.hour').removeClass('warning');
					$this.siblings('select.minutes').removeClass('warning');
				}
			}
			if ( 'start_app' === $this.attr('id') ) {
				startApp = $this.val();
			} else if ( 'end_app' === $this.attr('id') ) {
				endApp = $this.val();
			} else if ( 'start_act' === $this.attr('id') ) {
				startDate = $this.val();
				startHour = $this.siblings('select#start_act_hour').val();
				startMin = $this.siblings('select#start_act_minutes').val();
				startAct = dateToTimestamp( startDate ) + parseFloat( startHour ) / 24 + parseFloat( startMin ) / ( 24 * 60 );
			} else if ( 'end_act' === $this.attr('id') ) {
				endDate = $this.val();
				endHour = $this.siblings('select#end_act_hour').val();
				endMin = $this.siblings('select#end_act_minutes').val();
				endAct = dateToTimestamp( endDate ) + parseFloat( endHour ) / 24 + parseFloat( endMin ) / ( 24 * 60 );
			}
		});
		if ( startApp && endApp && dateToTimestamp( startApp ) > dateToTimestamp( endApp ) ) {
			errorIDs.push('start_app','end_app');
		}
		if ( endApp && startDate && dateToTimestamp( endApp ) > dateToTimestamp( startDate ) ) {
			errorIDs.push('end_app','start_act');
		}
		if ( startDate && endDate && ( startAct > endAct ) ) {
			errorIDs.push('start_act','start_act_hour','start_act_minutes','end_act','end_act_hour','end_act_minutes');
		}
		var hasErrors = false;
		for ( var i = 0; i < errorIDs.length; i++ ) {
			hasErrors = true;
			$('input#'+errorIDs[i]).addClass('warning');
			$('select#'+errorIDs[i]).addClass('warning');
		}
		if ( true === hasErrors ) {
			return false;
		}
	}
	return true;
}

$('input.required').change( function() { required($(this)) } );
$('input.required').keyup( function() { required($(this)) } );
$('input.required').blur( function() { required($(this)) } );
$('input.required').bind( 'paste', function() { required($(this)) } );
$('input.required').bind( 'cut', function() { required($(this)) } );

$('input.numbers').change( function() { numbers($(this)) } );
$('input.numbers').keyup( function() { numbers($(this)) } );
$('input.numbers').blur( function() { numbers($(this)) } );
$('input.numbers').bind( 'paste', function() { numbers($(this)) } );
$('input.numbers').bind( 'cut', function() { numbers($(this)) } );

$('input.phone-number').change( function() { phoneNumber($(this)) } );
$('input.phone-number').keyup( function() { phoneNumber($(this)) } );
$('input.phone-number').blur( function() { phoneNumber( $(this), true ) } );
$('input.phone-number').bind( 'paste', function() { phoneNumber( $(this), true ) } );
$('input.phone-number').bind( 'cut', function() { phoneNumber( $(this), true ) } );

$('input.date').change( function() { dateComp($(this)) } );
$('input.date').bind( 'paste', function() { dateComp($(this)) } );
$('input.date').bind( 'cut', function() { dateComp($(this)) } );
$('select.hour').change( function() { dateComp($(this).prev('input.date')) } );
$('select.minutes').change( function() { dateComp($(this).prev('input.date')) } );

function validate() {
	$('input.required').each( function() {
		if ( ! required( $(this) ) ) {
			theErrors.required = true;
		}
	});

	$('input.numbers').each( function() {
		if ( ! numbers( $(this) ) ) {
			theErrors.numbers = true;
		}
	});

	$('input.phone-number').each( function() {
		if ( ! phoneNumber( $(this), true ) ) {
			theErrors.phone = true;
		}
	});

	var isTested = false;
	$('input.date').each( function() {
		if ( ! isTested ) {
			if ( ! dateComp( $(this) ) ) {
				theErrors.date = true;
			}
			isTested = true;
		}
	});
}

function errorMessage() {
	var theMessage = '';//validationParams.errorHeading + '\r\n';

	for ( var key in theErrors ) {
		if (theErrors.hasOwnProperty(key)) {
			theMessage += '\r\n- ' + validationParams.errors[key];
		}
	}

	alert(theMessage);
}

function dateToTimestamp( theDate ) {
	theDate = theDate.split('.');
	var newDate = parseFloat( ''+theDate[2]+theDate[1]+theDate[0] );
	return newDate;
	//var newDate = theDate[0]+'/'+theDate[1]+'/'+theDate[2];
	//var theStamp = new Date(newDate).getTime();
	//return theStamp;
}

})(jQuery); // closure