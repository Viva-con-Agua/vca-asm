(function($){ // closure

$(document).ready(function() {
	initSlots();
});

function initSlots() {
	if ( null !=  quotasParams.total_slider ) {
		totalSlider();
	}
	if ( 'yay' === quotasParams.ctr_quotas_switch ) {
		var qgParams = new Object();
		qgParams.prefix = 'ctr';
		qgParams.fieldID = 'ctr_quotas';
		qgParams.id = 0;
		qgParams.init = true;
		createQuotaGroup( qgParams );
	}
	if ( null != quotasParams.ctr_cty_switch ) {
		for ( var geoID in quotasParams.ctr_cty_switch ) {
			if ( quotasParams.ctr_cty_switch.hasOwnProperty(geoID) && 'yes' === quotasParams.ctr_cty_switch[geoID] ) {
				var qgParams = new Object();
				qgParams.prefix = 'cty';
				qgParams.fieldID = 'cty_slots';
				qgParams.id = geoID;
				qgParams.init = true;
				createQuotaGroup( qgParams );
			}
		}
	}
}

function totalSlider() {
	if ( quotasParams.total_slider.min < quotasParams.minimum_quotas[0] ) {
		var minVal = quotasParams.minimum_quotas[0];
	} else {
		var minVal = quotasParams.total_slider.min;
	}
	$( "#" + quotasParams.total_slider.id + "-slider" ).slider({
		range: 'min',
		value: quotasParams.total_slots,
		min: minVal,
		max: quotasParams.total_slider.max,
		step: quotasParams.total_slider.step,
		animate: 100,
		slide: function( event, ui ) {
			$( "#" + quotasParams.total_slider.id ).val( ui.value );
			$( "span#" + quotasParams.total_slider.id + "-value").text( ui.value );
			newTotal( ui.value );
		}
	});
}

function createQuotaGroup( qgParams ) {
	$('#'+qgParams.fieldID+'-wrap').show(400);

	var elWrap = $('#'+qgParams.fieldID+'-wrap td'),
		qgHTML = '<div id="quotas-group-'+qgParams.id+'-wrap" class="quotas-group-wrap">',
		theTotal = 0,
		theAvail = 0,
		reservedSlots = 0,
		theCurAvail = 0;

	if ( 'ctr' === qgParams.prefix ) {
		theTotal = $('input#total_slots').val();
	} else {
		$('div#quotas-group-0-wrap').find('select.quotas-geo').each( function() {
			if ( qgParams.id == $(this).val() ) {
				theTotal = $(this).siblings('input.value').val();
			}
			if ( '' === theTotal ) {
				theTotal = 0;
			}
		});
	}
	if ( null != quotasParams.participants_count_by_slots[qgParams.id] ) {
		reservedSlots = quotasParams.participants_count_by_slots[qgParams.id];
	}
	theAvail = theTotal - reservedSlots;
	theCurAvail = theAvail;
	if ( 'ctr' === qgParams.prefix ) {
		for ( var geoID in quotasParams.ctr_quotas ) {
			if ( quotasParams.ctr_quotas.hasOwnProperty(geoID) ) {
				theCurAvail -= quotasParams.ctr_quotas[geoID];
			}
		}
	} else {
		for ( var i = 0; i < quotasParams.national_hierarchy.length; i++ ) {
			if ( qgParams.id === quotasParams.national_hierarchy[i].id ) {
				for ( var j = 0; j < quotasParams.national_hierarchy[i].cities.length; j++ ) {
					if ( null != quotasParams.cty_slots[quotasParams.national_hierarchy[i].cities[j].id] ) {
						theCurAvail -= quotasParams.cty_slots[quotasParams.national_hierarchy[i].cities[j].id];
					}
				}
			}
		}
	}

	if ( 'cty' === qgParams.prefix ) {
		qgHTML += '<h4 class="qg-title">' + quotasParams.countries[qgParams.id] + '</h4>' +
			'<p>' +
				quotasParams.strings.quota_total_slots + ': ' +
				'<span id="total-slots-'+qgParams.id+'" class="total-slots value">' + theTotal + '</span>'+
				'<br />' +
				quotasParams.strings.ctr_available_direct + ': ' +
				'<span id="avail-slots-'+qgParams.id+'" class="avail-slots value">' + theCurAvail + '</span>';
		if ( 0 < quotasParams.participants_count_by_slots[qgParams.id] ) {
			qgHTML += ' (' + quotasParams.participants_count_by_slots[qgParams.id] + ' ' + quotasParams.strings.confirmed_participants + ')';
		}
		qgHTML += '</p>';
	}
	qgHTML += '<input type="hidden" id="available-total-'+qgParams.id+'" class="available-total" value="'+theAvail+'" />' +
			'<ul id="quotas-group-'+qgParams.id+'" class="'+qgParams.prefix+'-quotas-group quotas-group no-margins"></ul>' +
			'<a class="quotas-group-add" href="#">+ '+quotasParams.strings.add+'</a>' +
		'</div>';

	if ( elWrap.find('div#quotas-group-'+qgParams.id+'-wrap').length === 0 ) {
		var QGs = elWrap.find('div.quotas-group-wrap');
		if ( 0 < QGs.length ) {
			var doneSwitch = false;
			var lastCtr = elWrap.find('div.quotas-group-wrap').last().find('h4').first().text();
			elWrap.find('div.quotas-group-wrap').each( function() {
				var curCtr = $(this).find('h4').text();
				if( curCtr > quotasParams.countries[qgParams.id] && ! doneSwitch ) {
					$(this).before( qgHTML );
					doneSwitch = true;
				} else if ( curCtr === lastCtr && ! doneSwitch ) {
					$(this).after( qgHTML );
				}
			});
		} else {
			elWrap.find('span.description').first().before(qgHTML);
		}
	}

	var i = 0,
		highestRunning = 0;

	if ( 'cty' === qgParams.prefix && 0 < $('tr#cty_slots-wrap').find('li.quotas-item').length ) {
		$('tr#cty_slots-wrap').find('li.quotas-item').each( function() {
			var currentRunning = parseInt( $(this).attr('id').split('-').pop() );
			if ( currentRunning > highestRunning ) {
				highestRunning = currentRunning;
			}
		});
		i = highestRunning + 1;
	}

	var groupCnt = 0;

	for ( var geoID in quotasParams[qgParams.fieldID] ) {
		if ( quotasParams[qgParams.fieldID].hasOwnProperty(geoID) ) {
			if ( 'ctr' === qgParams.prefix ) {
				createField( i, geoID, qgParams );
				i++;
				groupCnt++;
			} else {
				for ( var j = 0; j < quotasParams.national_hierarchy.length; j++ ) {
					if (
						qgParams.id === quotasParams.national_hierarchy[j].id &&
						null != quotasParams.national_hierarchy[j].cities
					) {
						groupCnt = 0;
						for ( var k = 0; k < quotasParams.national_hierarchy[j].cities.length; k++ ) {
							if ( geoID === quotasParams.national_hierarchy[j].cities[k].id ) {
								createField( i, geoID, qgParams );
								i++;
								groupCnt++;
							}
						}
					}
				}
			}
		}
	}
	if ( 0 === i || ( ( highestRunning + 1 ) === i && ( true != qgParams.init || 0 === groupCnt ) ) ) {
		var extraParams = new Object;
		extraParams.groupRunning = groupCnt;
		createField( i, null, qgParams, extraParams );
	}

	var geoOptions = $('ul#quotas-group-'+qgParams.id).find('li').last().find('select.quotas-geo').children('option').length;
	if ( 1 >= geoOptions ) {
		$('ul#quotas-group-'+qgParams.id).siblings('a.quotas-group-add').hide(400);
	}

	//if ( 'ctr' === qgParams.prefix ) {
	//	$('input#global_slots').val(theCurAvail);
	//	$('span.global_slots-value').text(theCurAvail);
	//}
	quotaGroupWrapAdjust();
}

function createField( runningNumber, geoID, qgParams, extraParams ) {
	extraParams = extraParams || {};
	var groupRunning = runningNumber;
	if ( null != extraParams.groupRunning ) {
		groupRunning = extraParams.groupRunning;
	}

	var output = '<li id="'+qgParams.prefix+'-quotas-item-'+runningNumber+'" class="quotas-item">' +
			'<select name="quotas-'+qgParams.prefix+'['+runningNumber+']"' +
				' id="quotas-'+qgParams.prefix+'" class="quotas-geo">';

	for ( var i = 0; i < quotasParams.national_hierarchy.length; i++ ) {
		if ( qgParams.prefix === 'ctr' ) {
			if (
				geoID === quotasParams.national_hierarchy[i].id ||
				null == quotasParams[qgParams.fieldID][quotasParams.national_hierarchy[i].id]
			) {
				output += '<option value="' + quotasParams.national_hierarchy[i].id + '"';
				if ( geoID === quotasParams.national_hierarchy[i].id ) {
					output += ' selected="selected"';
				}
				output += '>' + quotasParams.national_hierarchy[i].name + '</option>';
			}
		} else if ( qgParams.id === quotasParams.national_hierarchy[i].id ) {
			for ( var j = 0; j < quotasParams.national_hierarchy[i].cities.length; j++ ) {
				if (
					geoID === quotasParams.national_hierarchy[i].cities[j].id ||
					null == quotasParams[qgParams.fieldID][quotasParams.national_hierarchy[i].cities[j].id]
				) {
					output += '<option value="' + quotasParams.national_hierarchy[i].cities[j].id + '"';
					if ( geoID === quotasParams.national_hierarchy[i].cities[j].id ) {
						output += ' selected="selected"';
					}
					output += '>' + quotasParams.national_hierarchy[i].cities[j].name + '</option>';
				}
			}
		}
	}

	output += '</select>';

	if ( ! quotasParams.participants_count_by_quota.hasOwnProperty(geoID) ||
		1 > quotasParams.participants_count_by_quota[geoID]
	) {
		output += '<a class="quotas-group-remove" href="#">'+quotasParams.strings.remove+'</a>';
	}

	theVal = quotasParams[qgParams.prefix+'_slider'].min;
	if(	quotasParams[qgParams.fieldID].hasOwnProperty(geoID) &&
		quotasParams[qgParams.fieldID][geoID] > quotasParams[qgParams.prefix+'_slider'].min ) {
		theVal = parseInt( quotasParams[qgParams.fieldID][geoID] );
	}
	output+= '<br />' +
		'<div class="slider"></div>' +
		'<input type="hidden" name="'+qgParams.fieldID+'['+runningNumber+']' +
			'" id="'+qgParams.fieldID+'_'+runningNumber+'" class="'+qgParams.fieldID+' value" value="' + theVal + '" />' +
		'<span class="value" id="quotas-value-' + geoID + '">' +
			theVal + '</span>';

	if ( 1 <= quotasParams.participants_count_by_quota[geoID] ) {
		output += ' (' + quotasParams.strings.confirmed_participants + ': ' + quotasParams.participants_count_by_quota[geoID] + ')';
	}

	if ( 'ctr' === qgParams.prefix ) {

		output += '<br />';

		output += '<label>' + quotasParams.strings.split_into_cty + '</label><br />' +

		'<span class="selection-switch selection-disabled">' + quotasParams.strings.cty_quotas_enabled + '<br />' +
			quotasParams.strings.cty_quotas_current.replace(
				/%participants%/,
				quotasParams.participants_count_by_quota[geoID] - quotasParams.participants_count_by_slots[geoID]
			) +
			' ' + quotasParams.strings.cty_quotas_cannot +
			'<input name="ctr_cty_override['+runningNumber+']" id="ctr_cty_override-'+runningNumber+'" class="ctr_cty_override" type="hidden" value="false" /></span><span class="selection-switch selection-enabled">';

		for ( var j = 0; j < quotasParams.ctr_cty_switch_options.length; j++ ) {
			output += '<input type="radio" ';
			if ( 'notify' === quotasParams.ctr_cty_switch_options[j].value ) {
				output += 'disabled="disabled" ';
			}
			output += 'name="ctr_cty_switch['+runningNumber+']" ' +
					'id="ctr_cty_switch-'+ runningNumber + '-' + quotasParams.ctr_cty_switch_options[j].value +
					'"  class="ctr_cty_switch' +
					'" value="' + quotasParams.ctr_cty_switch_options[j].value + '" ' +
					' /><label for="ctr_cty_switch-' + runningNumber + '-' + quotasParams.ctr_cty_switch_options[j].value + '">' +
					quotasParams.ctr_cty_switch_options[j].label + '</label>';

			if ( 'notify' !== quotasParams.ctr_cty_switch_options[j].value ) {
				output += '<br />';
			}
		}

		output += '</span><span class="selection-switch no-selection">' + quotasParams.strings.no_cities +'</span>';
	}

	output += '</li>';

	$( '#quotas-group-'+qgParams.id ).append( output );

	if ( 'ctr' === qgParams.prefix ) {
		$('#'+qgParams.prefix+'-quotas-item-'+runningNumber).find('span.selection-switch').hide();
		if ( quotasParams.participants_count_by_quota[geoID] > quotasParams.participants_count_by_slots[geoID] ) {
			$('#'+qgParams.prefix+'-quotas-item-'+runningNumber).find('span.selection-disabled').show();
			$('#'+qgParams.prefix+'-quotas-item-'+runningNumber).find('input#ctr_cty_override-'+runningNumber).val('true');
		} else {
			var citiesCount = 0;
			var theID = geoID;
			if ( null === geoID ) {
				theID = $('#'+qgParams.prefix+'-quotas-item-'+runningNumber).find('select.quotas-geo').val();
			}
			for ( var i = 0; i < quotasParams.national_hierarchy.length; i++ ) {
				if ( theID == quotasParams.national_hierarchy[i].id ) {
					citiesCount = quotasParams.national_hierarchy[i].cities.length;
				}
			}
			if ( 0 < citiesCount ) {
				$('#'+qgParams.prefix+'-quotas-item-'+runningNumber).find('span.selection-enabled').show();
			} else {
				$('#'+qgParams.prefix+'-quotas-item-'+runningNumber).find('span.no-selection').show();
			}
		}
		if( null != quotasParams.ctr_cty_switch[geoID] ) {
			$('#'+qgParams.prefix+'-quotas-item-'+runningNumber).find('span.selection-enabled').find('input:radio[value='+quotasParams.ctr_cty_switch[geoID]+']').prop( 'checked', true );
		} else {
			$('#'+qgParams.prefix+'-quotas-item-'+runningNumber).find('span.selection-enabled').find('input:radio[value=no]').prop( 'checked', true );
		}
	}

	var minVal = quotasParams[qgParams.prefix+'_slider'].min;
	if( quotasParams[qgParams.prefix+'_slider'].min < quotasParams.participants_count_by_quota[geoID] ) {
		minVal = quotasParams.participants_count_by_quota[geoID];
	}
	var theMax = parseInt( $('div#quotas-group-'+qgParams.id+'-wrap').find('input.available-total').val() );

	$('li#'+qgParams.prefix+'-quotas-item-'+runningNumber).children('.slider').slider({
		range: 'min',
		value: theVal,
		min: minVal,
		max: theMax,
		step: quotasParams[qgParams.prefix+'_slider'].step,
		animate: 100,
		slide: function( event, ui ) {
			$(this).siblings('input.value').val( ui.value );
			$(this).siblings('span.value').text( ui.value );
			updateQuotaGroup( $(this), ui.value );
		}
	});

	if ( groupRunning === 0 ) {
		$('ul#quotas-group-'+qgParams.id).find('.quotas-group-remove').hide(400);
	} else {
		$('ul#quotas-group-'+qgParams.id).find('.quotas-group-remove').show(400);
	}
}

/* click handlers */

$('.quotas-group-add').live( 'click', function() {
	var clickedEl = $(this),
		toBeCloned = $(this).closest('div').find('li.quotas-item:last'),
		tbcSelected = $(toBeCloned).find('select.quotas-geo').first().val(),
		field = toBeCloned.clone(false),
		sliders = $(this).siblings('ul').find('div.slider'),
		theTotalVals = 0,
		theAvailable = clickedEl.siblings('input.available-total').val(),
		theMax = theAvailable;

	sliders.each( function () {
		theTotalVals += $(this).slider( 'option', 'value' );
	});

	$(field).find('select.quotas-geo').first().find('option[value="'+tbcSelected+'"]').remove();
	var clonedSelected = $(field).find('select.quotas-geo option').first().attr('value');
	$(this).closest('div').find('select.quotas-geo option[value="'+clonedSelected+'"]').remove();
	var fieldLocation = $(this).closest('div').find('li.quotas-item:last');
	var highestRunning = 0;
	$(this).closest('td').find('li.quotas-item').each( function() {
		var currentRunning = parseInt( $(this).attr('id').split('-').pop() );
		if ( currentRunning > highestRunning ) {
			highestRunning = currentRunning;
		}
	});
	highestRunning = highestRunning + 1;
	$('input', field).val('').attr('name', function(index, name) {
		return name.replace(/(\d+)/, function(fullMatch, n) {
			return Number(highestRunning) + 1;
		});
	});
	$('input', field).attr('id', function(index, id) {
		return id.replace(/(\d+)/, function(fullMatch, n) {
			return Number(highestRunning) + 1;
		});
	});
	$('label', field).attr('for', function(index, attr) {
		if ( null != attr ) {
			return attr.replace(/(\d+)/, function(fullMatch, n) {
				return Number(highestRunning) + 1;
			});
		} else {
			return '';
		}
	});
	$('select', field).val('').attr('name', function(index, name) {
		return name.replace(/(\d+)/, function(fullMatch, n) {
			return Number(highestRunning) + 1;
		});
	});
	field.attr('id', function(index, id) {
		return id.replace(/(\d+)/, function(fullMatch, n) {
			return Number(highestRunning) + 1;
		});
	});
	field.find('span.value').text(quotasParams.ctr_slider.min);
	field.find('div.slider div.ui-slider-range').remove();
	field.find('input[type=radio]').prop( 'checked', false ).each( function() {
		$(this).val( $(this).attr('id').split('-').pop() );
	});

	if ( $(field).find('select.quotas-geo option').length < 2 ) {
		$(this).hide(400);
	}

	theMax -= theTotalVals;
	var thePrefix = $(this).closest('tr').attr('id').split('_').shift(),
		theMin = quotasParams[thePrefix+'_slider'].min,
		theStep = quotasParams[thePrefix+'_slider'].step;

	if ( 0 < $(this).parents('div#quotas-group-0-wrap').length ) {
		field.find('span.selection-switch').hide();
		if ( quotasParams.participants_count_by_quota[clonedSelected] > quotasParams.participants_count_by_slots[clonedSelected] ) {
			field.find('span.selection-disabled').show();
		} else {
			var citiesCount = 0;
			for ( var i = 0; i < quotasParams.national_hierarchy.length; i++ ) {
				if ( clonedSelected == quotasParams.national_hierarchy[i].id ) {
					citiesCount = quotasParams.national_hierarchy[i].cities.length;
				}
			}
			if ( 0 < citiesCount ) {
				field.find('span.selection-enabled').show();
			} else {
				field.find('span.no-selection').show();
			}
		}
		if ( null != quotasParams.ctr_cty_switch && null != quotasParams.ctr_cty_switch[clonedSelected] ) {
			field.find('input:radio[value='+quotasParams.ctr_cty_switch[clonedSelected]+']').prop( 'checked', true );
			if ( 'yes' === quotasParams.ctr_cty_switch[clonedSelected] ) {
				$('div#quotas-group-'+clonedSelected+'-wrap').show(400);
				quotaGroupWrapAdjust();
			}
		} else {
			field.find('input:radio[value=no]').prop( 'checked', true );
		}
	}

	field.insertAfter(fieldLocation, $(this).closest('div')).hide().show(400);

	$(this).siblings('ul.quotas-group').find('a.quotas-group-remove').show(400);

	$(this).siblings('ul').children('li').last().children('div.slider').slider({
		range: 'min',
		value: theMin,
		min: theMin,
		max: theMax,
		step: theStep,
		animate: 100,
		slide: function( event, ui ) {
			$(this).siblings('input.value').val( ui.value );
			$(this).siblings('span.value').text( ui.value );
			updateQuotaGroup( $(this), ui.value );
		}
	});

	return false;
});


$('.quotas-group-remove').live( 'click', function() {
	var el = $(this);
	var siblingsCount = $(this).parent().siblings().length;
	if ( siblingsCount < 2 ) {
		$(this).parent().siblings().find('.quotas-group-remove').hide(400);
	}
	$(this).closest('ul').siblings('.quotas-group-add').show();
	var previousGeoVal = $(this).siblings('select.quotas-geo').first().val();
	var previousLabel = $(this).siblings('select.quotas-geo').find('option:selected').text();
	var insertHTML = '<option value="' + previousGeoVal + '"' + '>' + previousLabel + '</option>';
	$(this).closest('ul').find('select.quotas-geo').not(this).each( function() {
		var lastLabel = $(this).find('option').last().text();
		var doneSwitch = false;
		$(this).find('option').each( function() {
			var curLabel = $(this).text();
			if ( previousLabel < curLabel && ! doneSwitch ) {
				$(this).before( insertHTML );
				doneSwitch = true;
			} else if ( curLabel === lastLabel && ! doneSwitch ) {
				$(this).after( insertHTML );
			}
		});
	});

	var previousVal = $(this).siblings('div.slider').slider( 'option', 'value' ),
		elGroup = $(this).closest('ul'),
		geoID = $(this).siblings('select.quotas-geo').val();

	$(this).parent().hide(400).delay(400).remove();

	if ( 0 < previousVal ) {
		elGroup.find('div.slider').each( function() {
			var theVal = $(this).slider( 'option', 'value' ),
				oldMax = $(this).slider( 'option', 'max' ),
				newMax = oldMax + previousVal;
			$(this).slider( 'option', 'max', newMax );
			$(this).slider( 'value', theVal );

		});
		var parentQuota = elGroup.attr('id').split('-').pop(),
			reservedSlots = 0;
		if ( null != quotasParams.participants_count_by_slots[parentQuota] ) {
			reservedSlots = quotasParams.participants_count_by_slots[parentQuota];
		}
		if ( parentQuota == 0 ) {
			var oldAvail = parseInt( $('input#global_slots').val() );
			$('input#global_slots').val( oldAvail + previousVal );
			$('span#global_slots-value').text( oldAvail + previousVal );
			$('div#quotas-group-'+geoID+'-wrap').hide(400);
			quotaGroupWrapAdjust();
		} else {
			var oldAvail = parseInt( $('span#avail-slots-'+parentQuota).text() );
			$('span#avail-slots-'+parentQuota).text( oldAvail + previousVal );
		}
	}

	$('div#quotas-group-'+previousGeoVal+'-wrap').hide(400);

	setTimeout(
		function() {
			quotaGroupWrapAdjust();
		},
		405
	);

	return false;
});

/* change handlers */

$('input.ctr_cty_switch').live( 'change' , function () {

	var ctrSel = $(this).closest('span').siblings('select.quotas-geo');

	if( $(this).val() === 'yes' ) {
		$('#cty_slots-wrap').show(400);
		if ( $('div#quotas-group-'+ctrSel.val()+'-wrap').length > 0 ) {
			$('div#quotas-group-'+ctrSel.val()+'-wrap').show(400);
		} else {
			var qgParams = new Object();
			qgParams.prefix = 'cty';
			qgParams.fieldID = 'cty_slots';
			qgParams.id = ctrSel.val();
			createQuotaGroup( qgParams );
		}
	} else {
		$('div#quotas-group-'+ctrSel.val()+'-wrap').hide(400);
	}
	setTimeout(
		function() {
			if ( $('#cty_slots-wrap').find('div.quotas-group-wrap').filter(':visible').length < 1 ) {
				$('#cty_slots-wrap').hide(400);
			}
			quotaGroupWrapAdjust();
		},
		405
	);
});

$('input[name="ctr_quotas_switch"]').change( function() {
	$('input[name="ctr_quotas_switch"]').prop( 'disabled', true );
	if( $('input[name="ctr_quotas_switch"]:checked').val() === 'yay' ) {
		$('#ctr_quotas-wrap').show(400);
		$('input.ctr_quotas').prop( 'disabled', false );
		$('input.cty_slots').prop( 'disabled', false );
		$('input.ctr_cty_switch').prop( 'disabled', false );
		if ( $('#ctr_quotas-wrap').find('div#quotas-group-0-wrap').length < 1 ) {
			var qgParams = new Object();
			qgParams.prefix = 'ctr';
			qgParams.fieldID = 'ctr_quotas';
			qgParams.id = 0;
			createQuotaGroup( qgParams );
		}
		setTimeout(
			function() {
				newTotal( $('input#total_slots').val() );
				$('input[name="ctr_quotas_switch"]').prop( 'disabled', false );
			},
			405
		);
	} else {
		$('#ctr_quotas-wrap').hide(400);
		$('#cty_slots-wrap').hide(400);
		$('input.ctr_quotas').prop( 'disabled', true );
		$('input.cty_slots').prop( 'disabled', true );
		$('input.ctr_cty_switch').prop( 'disabled', true );
		setTimeout(
			function() {
				newTotal( $('input#total_slots').val() );
				$('input[name="ctr_quotas_switch"]').prop( 'disabled', false );
			},
			405
		);
	}
});

(function () {
    var previousVal;
	var previousLabel;
	var insertHTML;

	$('select.quotas-geo').live( 'focus' , function () {
        previousVal = this.value;
        previousLabel = $(this).find('option:selected').text();
		insertHTML = '<option value="' + previousVal + '"' + '>' + previousLabel + '</option>';
    }).live( 'change', function () {
		var newVal = $(this).val();
		$(this).closest('ul').find('select.quotas-geo').not(this).each( function() {
			$(this).find('option[value="'+newVal+'"]').remove();
			var lastLabel = $(this).find('option').last().text();
			var doneSwitch = false;
			$(this).find('option').each( function() {
				var curLabel = $(this).text();
				if ( previousLabel < curLabel && ! doneSwitch ) {
					$(this).before( insertHTML );
					doneSwitch = true;
				} else if ( curLabel === lastLabel && ! doneSwitch ) {
					$(this).after( insertHTML );
				}
			});
		});

		if ( 0 < $(this).parents('div#quotas-group-0-wrap').length ) {
			$(this).siblings('span.selection-switch').hide();
			if ( quotasParams.participants_count_by_quota[this.value] > quotasParams.participants_count_by_slots[this.value] ) {
				$(this).siblings('span.selection-disabled').show(400);
			} else {
				var citiesCount = 0;
				for ( var i = 0; i < quotasParams.national_hierarchy.length; i++ ) {
					if ( this.value == quotasParams.national_hierarchy[i].id ) {
						citiesCount = quotasParams.national_hierarchy[i].cities.length;
					}
				}
				if ( 0 < citiesCount ) {
					$(this).siblings('span.selection-enabled').show(400);
				} else {
					$(this).siblings('span.no-selection').show(400);
				}
			}

			$('div#quotas-group-'+previousVal+'-wrap').hide(400);
			$(this).siblings('span').find('input:radio').prop( 'checked', false );
			if ( null != quotasParams.ctr_cty_switch && null != quotasParams.ctr_cty_switch[this.value] ) {
				$(this).siblings('span').find('input:radio[value='+quotasParams.ctr_cty_switch[this.value]+']').prop( 'checked', true );
				if ( 'yes' === quotasParams.ctr_cty_switch[this.value] ) {
					$('div#quotas-group-'+this.value+'-wrap').show(400);
				}
			} else {
				$(this).siblings('span').find('input:radio[value=no]').prop( 'checked', true );
			}
			quotaGroupWrapAdjust();
		}

		previousVal = this.value;
		previousLabel = $(this).find('option:selected').text();
    });
})();

/* Event Callbacks */

function newTotal( theTotal ) {
	var theGlobal = theTotal,
		reservedSlots = 0,
		sliders = $('.ctr-quotas-group').find('div.slider').filter(':visible');

	if ( null != quotasParams.participants_count_by_slots[0] ) {
		reservedSlots = quotasParams.participants_count_by_slots[0];
	}
	var theAvailable = theGlobal - reservedSlots;

	if ( 1 > sliders.length ) {
		$('input#global_slots').val( theGlobal );
		$('span#global_slots-value').text( theGlobal );
		$('input#available-total-0').val( theAvailable );
	} else {
		var theTotalVals = 0;

		sliders.each( function() {
			theTotalVals += $(this).slider( 'option', 'value' );
		});

		var theDiff = theAvailable - theTotalVals;

		if ( 0 > theDiff ) {
			theGlobal = reservedSlots;
		} else {
			theGlobal = theDiff + reservedSlots;
		}
		$('input#global_slots').val( theGlobal );
		$('span#global_slots-value').text( theGlobal );
		$('input#available-total-0').val( theAvailable );

		sliders.each( function() {
			var theVal = $(this).slider( 'option', 'value' ),
				theMax = theDiff + theVal;
			if ( 0 > theDiff ) {
				var theMin = $(this).slider( 'option', 'min' );
				if ( theMin < theMax ) {
					theVal = theMax;
					theDiff = 0;
				} else {
					theDiff += theVal - theMin;
					theMax = theMin;
					theVal = theMin;
				}
				var ctrID = $(this).siblings('select.quotas-geo').val();
				$('input#available-total-'+ctrID).val( theVal ).change();
			}
			$(this).slider( 'option', 'max', theMax );
			$(this).slider( 'value', theVal );
			$(this).siblings('input.value').val(theVal);
			$(this).siblings('span.value').text(theVal);
		});
	}
}

function updateQuotaGroup( element, newVal ) {
	var theTotal = 0,
		sliders = element.closest('ul').find('div.slider'),
		theAvailable = element.closest('ul').siblings('input.available-total').val();

	sliders.not(element).each( function() {
		theTotal += $(this).slider( 'option', 'value' );
	});
	theTotal += newVal;

	var theMax = theAvailable - theTotal;

	sliders.not(element).each( function() {
		var theVal = $(this).slider( 'option', 'value' );
		$(this).slider( 'option', 'max', theMax + theVal );
		$(this).slider( 'value', theVal );
		$(this).siblings('input.value').val(theVal);
		$(this).siblings('span.value').text(theVal);
	});

	var parentQuota = element.parents('ul.quotas-group').attr('id').split('-').pop(),
		reservedSlots = 0;
	if ( null != quotasParams.participants_count_by_slots[parentQuota] ) {
		reservedSlots = quotasParams.participants_count_by_slots[parentQuota];
	}
	if ( parentQuota == 0 ) {
		$('input#global_slots').val( theMax + reservedSlots );
		$('span#global_slots-value').text( theMax + reservedSlots );
		var ctrID = element.siblings('select.quotas-geo').val(),
			childAvail = newVal;
		if ( null != quotasParams.participants_count_by_slots[ctrID] ) {
			childAvail -= quotasParams.participants_count_by_slots[ctrID];
		}
		$('input#available-total-'+ctrID).val( childAvail ).trigger('change');
	} else {
		$('span#avail-slots-'+parentQuota).text( theMax + reservedSlots );
	}
}

function quotaGroupWrapAdjust() {
	$('tr#cty_slots-wrap').find('h4').css( 'margin-top', '' );
	var visibleEls = $('tr#cty_slots-wrap').find('div.quotas-group-wrap').filter(':visible');
	visibleEls.first().find('h4').css( 'margin-top', '0px' );
	if ( 1 > visibleEls.length ) {
		$('tr#cty_slots-wrap').hide(400);
	}
}

$('input.available-total').live( 'change' , function () {
	var geoID = $(this).attr('id').split('-').pop();
	if ( 0 != geoID ) {
		var theAvailable = parseInt( $(this).val() ),
			reservedSlots = 0,
			sliders = $(this).siblings('.quotas-group').find('div.slider');

		if ( null != quotasParams.participants_count_by_slots[geoID] ) {
			reservedSlots = parseInt( quotasParams.participants_count_by_slots[geoID] );
		}

		var theTotalVals = 0;

		sliders.each( function() {
			theTotalVals += $(this).slider( 'option', 'value' );
		});

		var theDiff = theAvailable - theTotalVals,
			theTotal = theAvailable + reservedSlots;

		if ( 0 > theDiff ) {
			theParent = reservedSlots;
		} else {
			theParent = theDiff + reservedSlots;
		}

		sliders.each( function() {
			var theVal = $(this).slider( 'option', 'value' ),
				theMin = $(this).slider( 'option', 'min' ),
				theMax = theDiff + theVal;
			if ( 0 > theDiff ) {
				if ( theMin < theMax ) {
					theVal = theMax;
					theDiff = 0;
				} else {
					theDiff += theVal - theMin;
					theMax = theMin;
					theVal = theMin;
				}
			}
			$(this).slider( 'option', 'max', theMax );
			$(this).slider( 'value', theVal );
			$(this).siblings('input.value').val(theVal);
			$(this).siblings('span.value').text(theVal);
		});

		$(this).siblings('p').find('span.total-slots').text( theTotal );
		$(this).siblings('p').find('span.avail-slots').text( theParent );
	}
});

/* deprecated  */

$('.slots-cf').sortable( {
	opacity: 0.6,
	revert: true,
	cursor: 'move',
	handle: '.sort'
});

})(jQuery); // closure