(function($){ // closure

$(document).ready( function() {
	if ( $('form[name="vca-asm-groupmail-form"] input#sendmail-submit').length !== 0 ) {
		$('form[name="vca-asm-groupmail-form"] input#sendmail-submit').after(
			'<input type="submit" name="mail_submit" id="preview" class="button-secondary" style="margin-left:21px;" value="' + emailParams.btnVal + '">'
		);
	}

	$('form[name="vca-asm-groupmail-form"] input[type=submit]').click(function() {
		$('input[type=submit]', $(this).parents('form')).removeAttr('clicked');
	    $(this).attr('clicked', 'true');
	});

	$('form[name="vca-asm-groupmail-form"]').submit( function() {
		if ( $(this).find('input[type=submit][clicked=true]').attr('id') == 'preview' ) {
			$('form[name="vca-asm-groupmail-form"]').attr('target', '_blank');
			$('form[name="vca-asm-groupmail-form"]').attr('action', emailParams.url + '/email');
			$('form[name="vca-asm-groupmail-form"]').submit();
			return false;
		} else {
			$('form[name="vca-asm-groupmail-form"]').removeAttr('target');
			$('form[name="vca-asm-groupmail-form"]').attr('action', emailParams.sendingAction);
		}
		return true;
	});
});

})(jQuery); // closure