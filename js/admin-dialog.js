(function($){ // closure

	var firstBtn,
		secondBtn,
		args = {
			autoOpen: false,
			resizable: false,
			modal: false,
			width: 500,
			buttons: {}
		};

	if ( 'negative' == dialogParams.btnFirst )
	{
		args.buttons[dialogParams.btnNo] = function() {
			$( this ).dialog( 'close' );
		};
		args.buttons[dialogParams.btnYes] = function() {
			$( this ).dialog( 'close' );
			var action = $( '#' + dialogParams.btnID ).closest( 'form' ).attr('action');
			tinyMCE.triggerSave();
			$.post(
				action,
				$( '#' + dialogParams.btnID ).closest( 'form' ).serialize(),
				function(data){
					$('div#wpbody-content').append(data);
					if ( $('span#processed-url').length ) {
						window.location = $('span#processed-url').first().text();
					} else {
						window.location = action;
					}
				}
			);
			return false;
		};
	}
	else
	{
		args.buttons[dialogParams.btnYes] = function() {
			$( this ).dialog( 'close' );
			var action = $( '#' + dialogParams.btnID ).closest( 'form' ).attr('action');
			tinyMCE.triggerSave();
			$.post(
				action,
				$( '#' + dialogParams.btnID ).closest( 'form' ).serialize(),
				function(data){
					$('div#wpbody-content').append(data);
					if ( $('span#processed-url').length ) {
						window.location = $('span#processed-url').first().text();
					} else {
						window.location = action;
					}
				}
			);
			return false;
		};
		args.buttons[dialogParams.btnNo] = function() {
			$( this ).dialog( 'close' );
		};
	}

	$( 'div#wpbody-content' ).append(
		'<div id="the-dialog">' + dialogParams.text + '</div>'
	);

	$( "div#the-dialog" ).dialog( args );

	$( '#' + dialogParams.btnID ).click( function() {
		$( "div#the-dialog" ).dialog('open');
		return false;
	});

})(jQuery); // closure