jQuery(document).ready( function() {
	if ( jQuery('form[name="vca_asm_groupmail_form"] p.submit').length !== 0 ) {
		jQuery('form[name="vca_asm_groupmail_form"] p.submit').first().append(
			'<input type="submit" name="mail_submit" id="preview" class="button-secondary" style="margin-left:21px;" value="' + emailParams.btnVal + '">'
		);
	}

	jQuery('form[name="vca_asm_groupmail_form"] input[type=submit]').click(function() {
		jQuery('input[type=submit]', jQuery(this).parents('form')).removeAttr('clicked');
	    jQuery(this).attr('clicked', 'true');
	});

	jQuery('form[name="vca_asm_groupmail_form"]').submit( function() {
		if ( jQuery(this).find('input[type=submit][clicked=true]').attr('id') == 'preview' ) {
			jQuery('form[name="vca_asm_groupmail_form"]').attr('target', '_blank');
			jQuery('form[name="vca_asm_groupmail_form"]').attr('action', emailParams.url + '/email');
			jQuery('form[name="vca_asm_groupmail_form"]').submit();
			return false;
		} else {
			jQuery('form[name="vca_asm_groupmail_form"]').removeAttr('target');
			jQuery('form[name="vca_asm_groupmail_form"]').attr('action', 'admin.php?page=vca-asm-emails&todo=send');
		}
		return true;
	});
});