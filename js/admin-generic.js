jQuery(document).ready(function() {
	//if( jQuery('li.wp-has-current-submenu[.^=toplevel_page_] div.wp-menu-image img').length > 0) {
	//	var imgSrc = jQuery('li.wp-has-current-submenu[.^=toplevel_page_] div.wp-menu-image img').attr("src").match(/.+(?=\.)/) + "-current.png";
	//	jQuery('li.wp-has-current-submenu[.^=toplevel_page_] div.wp-menu-image img').attr("src", imgSrc);
	//} else if( jQuery('li.current[.^=toplevel_page_] div.wp-menu-image img').length > 0) {
	//	var imgSrc = jQuery('li.current[.^=toplevel_page_] div.wp-menu-image img').attr("src").match(/.+(?=\.)/) + "-current.png";
	//	jQuery('li.current[.^=toplevel_page_] div.wp-menu-image img').attr("src", imgSrc);
	//}

	jQuery('.no-js-hide').each(function() {
		jQuery(this).css( 'display', 'block' );
		jQuery(this).css( 'visibility', 'visible' );
	});

	jQuery('.js-hide').each(function() {
		if( jQuery(this).is('input') ) {
			marker = jQuery('<br class="marker" />').insertBefore(this);
			jQuery(this).detach().attr('type', 'hidden').insertAfter(marker).focus();
			marker.remove();
		} else {
			jQuery(this).css( 'display', 'none' );
			jQuery(this).css( 'visibility', 'hidden' );
		}
	});

	window.vcaASM = { cligger : jQuery('input.do-bulk-action').first().attr('onclick') };
	jQuery('input.do-bulk-action').each(function() {
		jQuery(this).removeAttr('onclick');
	});
});

jQuery('input.do-bulk-action').click(function(e) {
	var actionVal = jQuery(this).siblings('select.bulk-action').first().val();
	if( actionVal == -1 || actionVal == 'please-select' ) {
		e.preventDefault();
		return false;
	}
	var checkedCount = jQuery(this).parents('.bulk-action-form').find('input:checked').length;
	if( checkedCount == 0 ) {
		e.preventDefault();
		return false;
	}
});

jQuery('.simul-select').change(function() {
	var newVal = jQuery(this).val();
	jQuery(this).parents('form').find('.simul-select').each(function() {
		jQuery(this).val( newVal );
	});
});

jQuery('form.bulk-action-form input').change( function(e) {
	var actionVal = jQuery(this).parents('form.bulk-action-form').find('select.bulk-action').first().val();
	var checkedCount = jQuery(this).parents('form.bulk-action-form').find('input:checked').length;
	if( actionVal != -1 && actionVal != 'please-select' && checkedCount > 0 ) {
		jQuery(this).parents('form.bulk-action-form').find('input.do-bulk-action').each(function(){
			jQuery(this).attr('onclick', window.vcaASM.cligger);
		});
	}
});

jQuery('form input.bulk-deselect').click( function() {
	jQuery(this).parent().find('input[type=checkbox]').removeAttr('checked');
	return false;
});