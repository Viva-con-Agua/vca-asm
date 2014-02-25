(function($){ // closure

$('.repeatable-cf-add').click(function() {
	field = $(this).closest('td').find('.repeatable-cf li:last').clone(true);
	fieldLocation = $(this).closest('td').find('.repeatable-cf li:last');
	$('input', field).val('').attr('name', function(index, name) {
		return name.replace(/(\d+)/, function(fullMatch, n) {
			return Number(n) + 1;
		});
	})
	field.insertAfter(fieldLocation, $(this).closest('td'));
	var count = $('.repeatable-cf-remove').length;
	if( count != 1 ) {
		$('.repeatable-cf-remove').show();
	}
	return false;
});

$('.repeatable-cf-remove').click(function(){
	$(this).parent().remove();
	var count = $('.repeatable-cf-remove').length;
	if( count == 1 ) {
		$('.repeatable-cf-remove').hide();
	}
	return false;
});

$('.repeatable-cf').sortable({
	opacity: 0.6,
	revert: true,
	cursor: 'move',
	handle: '.sort'
});

$('.contact-cf-add').click(function() {
	field = $(this).closest('table').find('tbody:last').prev().clone(true);
	fieldLocation = $(this).closest('table').find('tbody:last').prev();
	$('input', field).val('').attr('name', function(index, name) {
		return name.replace(/(\d+)/, function(fullMatch, n) {
			return Number(n) + 1;
		});
	})
	$('label', field).attr('for', function(index, forcount) {
		return forcount.replace(/(\d+)/, function(fullMatch, n) {
			return Number(n) + 1;
		});
	})
	field.insertAfter(fieldLocation, $(this).closest('table')).hide().show(400);
	var count = $('.contact-cf-remove').length;
	if( count != 1 ) {
		$('.contact-cf-remove').show(400);
	}
	return false;
});

$('.contact-cf-remove').click(function(){
	$(this).parents('tbody').hide(400).delay(400).remove();
	var count = $('.contact-cf-remove').length;
	if( count == 1 ) {
		$('.contact-cf-remove').hide(400);
	}
	return false;
});

$(document).ready(function() {
	var rcount = $('.repeatable-cf-remove').length;
	var ccount = $('.contact-cf-remove').length;
	if( rcount == 1 ) {
		$('.repeatable-cf-remove').css('display','none');
	}
	if( ccount == 1 ) {
		$('.contact-cf-remove').css('display','none');
	}
});

})(jQuery); // closure