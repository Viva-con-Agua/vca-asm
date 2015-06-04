(function($){ // closure

/* isotope trigger method */

function doTheIsotope( eventSelector, eventType, sortOverride ) {
	var selector = '',
		partialSelectors = {},
		sortTerm = 'date',
		sortItAscending = true,
		sortParamsString = '',
		sortParams = {};

	/* deprecated method via anchors
	partialSelectors['month'] = $('#month-filter').find('a.active-option').attr('data-filter');
	partialSelectors['ctr'] = $('#ctr-filter').find('a.active-option').attr('data-filter');
	partialSelectors['type'] = $('#type-filter').find('a.active-option').attr('data-filter');

	sortTerm = $('#sort-by-selector').find('a.active-option').attr('data-sort');
	sortOrder = $('#sort-by-selector').find('a.active-option').attr('data-order'); */

	curSelector = $('#month-filter-dd').find('option:selected');
	partialSelectors['month'] = curSelector.length > 0 ? curSelector.attr('data-filter') : '*';
	curSelector = $('#ctr-filter-dd').find('option:selected');
	partialSelectors['ctr'] = curSelector.length > 0 ? curSelector.attr('data-filter') : '*';
	curSelector = $('#type-filter-dd').find('option:selected');
	partialSelectors['type'] = curSelector.length > 0 ? curSelector.attr('data-filter') : '*';

	sortTerm = $('#sort-by-selector-dd').find('option:selected').attr('data-sort');
	sortOrder = $('#sort-order-selector-dd').find('option:selected').attr('data-order');

	if ( typeof eventSelector !== 'undefined' && false !== eventSelector && typeof eventType !== 'undefined' && false !== eventType ) {
		partialSelectors[eventType] = eventSelector;
    }
	sortItAscending = typeof sortOverride !== 'undefined' && 'ASC' === sortOverride ? sortOverride : ( 'DESC' === sortOrder ? false : true );

	for ( var curType in partialSelectors ) {
		if ( partialSelectors.hasOwnProperty(curType) ) {
			if ( '*' !== partialSelectors[curType] ) {
				selector += partialSelectors[curType];
			}
		}
	}
	selector = '' === selector ? '*': selector;

	$('div.activities-container').isotope({
		filter: selector,
		sortBy : sortTerm,
		sortAscending : sortItAscending
	});

	if ( $('div.activities-container').find('div.isotope-item').not('.isotope-hidden').length > 0 ) {
		$('div.no-results-row').hide();
	} else {
		$('div.no-results-row').show(400);
	}
}

/* isotope utility */

$('.activities-container').isotope({
	getSortData : {
	    date : function ( $elem ) {
		return $elem.find('.date').text();
		},
		ctr : function ( $elem ) {
		  return $elem.find('.ctr').text();
		},
		type : function ( $elem ) {
		  return $elem.find('.type').text();
		}
	}
});

/* change events */

$('#month-filter-dd').change(function(){
	var selector = $(this).find('option:selected').attr('data-filter');
	$('#month-filter-dd').find('option.active-option').removeClass('active-option');
	$(this).find('option:selected').addClass('active-option');
	doTheIsotope( selector, 'month' );
});
$('#ctr-filter-dd').change(function(){
	var selector = $(this).find('option:selected').attr('data-filter');
	$('#ctr-filter-dd').find('option.active-option').removeClass('active-option');
	$(this).find('option:selected').addClass('active-option');
	doTheIsotope( selector, 'ctr' );
});
$('#type-filter-dd').change(function(){
	var selector = $(this).find('option:selected').attr('data-filter');
	$('#type-filter-dd').find('option.active-option').removeClass('active-option');
	$(this).find('option:selected').addClass('active-option');
	doTheIsotope( selector, 'type' );
});
$('#sort-by-selector-dd').change(function(){
	doTheIsotope();
});
$('#sort-order-selector-dd').change(function(){
	doTheIsotope();
});

/* click events, deprecated

$('#month-filter a').click(function(){
	var selector = $(this).attr('data-filter');
	$('#month-filter').find('a.active-option').removeClass('active-option');
	$(this).addClass('active-option');
	doTheIsotope( selector, 'month' );
	return false;
});
$('#ctr-filter a').click(function(){
	var selector = $(this).attr('data-filter');
	$('#ctr-filter').find('a.active-option').removeClass('active-option');
	$(this).addClass('active-option');
	doTheIsotope( selector, 'ctr' );
	return false;
});
$('#type-filter a').click(function(){
	var selector = $(this).attr('data-filter');
	$('#type-filter').find('a.active-option').removeClass('active-option');
	$(this).addClass('active-option');
	doTheIsotope( selector, 'type' );
	return false;
});
$('#sort-by-selector a').click(function(){
	if ( ! $(this).hasClass('active-option') ) {
		var formerActiveOpt = $('#sort-by-selector').find('a.active-option'),
			thisOrder = $(this).attr('data-order'),
			newOrder = 'ASC' === thisOrder ? 'DESC' : 'ASC';
		formerActiveOpt.removeClass('active-option');
		formerActiveOpt.attr('data-order', 'ASC');
		$(this).addClass('active-option');
	} else {
		var thisOrder = $(this).attr('data-order'),
			newOrder = 'ASC' === thisOrder ? 'DESC' : 'ASC';
	}
	doTheIsotope();
	$(this).attr('data-order', newOrder);
	return false;
}); */

/* window events */

$(window).load(function(){
	doTheIsotope( false, false, 'ASC' );
});

$(window).resize(function(){
	$('div.activities-container').isotope('reLayout');
});

/* sticky filter box */

var theWindow = $(window),
    theStickyEl = $('div.filter-container'),
	elTop = theStickyEl.offset().top,
	elHeight = theStickyEl.height(),
	contentMargin = 0 //parseInt($('div.content-wrap-wrap').css('padding-top'));

$(document).ready( function() {
	elTop = theStickyEl.offset().top;
	elHeight = theStickyEl.height();
	contentMargin = 0 //parseInt($('div.content-wrap-wrap').css('padding-top'));
});

theWindow.resize( function() {
	elTop = theStickyEl.offset().top;
	elHeight = theStickyEl.height();
	setTimeout(
		function() {
			elTop = theStickyEl.offset().top;
		},
		50
	);
});

theWindow.scroll(function() {
	elHeight = theStickyEl.height();
	if ( theWindow.scrollTop() > ( elTop - contentMargin ) ) {
		if ( 0 === $('div#placeholder').length ) {
			theStickyEl.before('<div id="placeholder" style="height:'+elHeight+'px"></div>');
		}
	} else if ( theWindow.scrollTop() <= ( elTop - contentMargin ) ) {
		$('div#placeholder').remove();
	}
	theStickyEl.toggleClass('sticky', theWindow.scrollTop() > ( elTop - contentMargin ));
});

})(jQuery); // closure