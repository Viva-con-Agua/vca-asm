(function($){ // closure

$(document).ready( function() {
	if ( $('form[name="vca_asm_groupmail_form"] p.submit').length !== 0 ) {
		$('form[name="vca_asm_groupmail_form"] p.submit').first().append(
			'<input type="submit" name="mail_submit" id="preview" class="button-secondary" style="margin-left:21px;" value="' + emailParams.btnVal + '">'
		);
	}

	$('form[name="vca_asm_groupmail_form"] input[type=submit]').click(function() {
		$('input[type=submit]', $(this).parents('form')).removeAttr('clicked');
	    $(this).attr('clicked', 'true');
	});

	$('form[name="vca_asm_groupmail_form"]').submit( function() {
		if ( $(this).find('input[type=submit][clicked=true]').attr('id') == 'preview' ) {
			$('form[name="vca_asm_groupmail_form"]').attr('target', '_blank');
			$('form[name="vca_asm_groupmail_form"]').attr('action', emailParams.url + '/email');
			$('form[name="vca_asm_groupmail_form"]').submit();
			return false;
		} else {
			$('form[name="vca_asm_groupmail_form"]').removeAttr('target');
			$('form[name="vca_asm_groupmail_form"]').attr('action', 'admin.php?page=vca-asm-emails&todo=send');
		}
		return true;
	});
});

})(jQuery); // closure