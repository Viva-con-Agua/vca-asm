(function($){ // closure

$(document).ready( function() {
	if ( $('form[name="vca-asm-groupmail-form"] input#sendmail-submit').length !== 0 ) {
		$('form[name="vca-asm-groupmail-form"] input#sendmail-submit').after(
			'<input type="submit" name="mail_submit" id="preview" class="button-secondary" style="margin-left:21px;" value="' + emailParams.btnVal + '">'
		);
	}

	$('form[name="vca-asm-groupmail-form"] input[type=submit]').mouseup(function() {
		tinyMCE.triggerSave();
		var theMessage = $('<div></div>').append($('textarea[name=message]').val());
		theMessage.find('p,:header').each(function() {
			if( $(this).is(':empty') ) {
				$(this).html('&nbsp;');
			} else {
				var elementContent = $(this).html();
				elementContent = elementContent.replace(/(?:\r\n|\r|\n)/g, '<br />');
				$(this).html(elementContent);
			}
		});
		$('textarea[name=message]').val(theMessage.html());
		$('input[type=submit]', $(this).parents('form')).removeAttr('clicked');
	    $(this).attr('clicked', 'true');
		if ( $(this).attr('id') == 'preview' ) {
			$('form[name="vca-asm-groupmail-form"]').attr('target', '_blank');
			$('form[name="vca-asm-groupmail-form"]').attr('action', emailParams.url + '/email');
		} else {
			$('form[name="vca-asm-groupmail-form"]').removeAttr('target');
			$('form[name="vca-asm-groupmail-form"]').attr('action', emailParams.sendingAction);
		}
	});
});

})(jQuery); // closure