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
			$('div#wpbody-content').append(
				'<div id="vca-loading-overlay"><h2 class="vca-loading-message">'+
				dialogParams.loadingText+
				'</h2><img src="" title="Loading..." alt="Loading animation" /></div>'
			);
			$('div#vca-loading-overlay').show();
			$.post(
				action,
				$( '#' + dialogParams.btnID ).closest( 'form' ).serialize(),
				function(data){
					$('div#wpbody-content').append(data);
					$('div#vca-loading-overlay').hide();
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
			$('div#wpbody-content').append(
				'<div id="vca-loading-overlay"><h2 class="vca-loading-message">'+
				dialogParams.loadingText+
				'</h2><img src="" title="Loading..." alt="Loading animation" /></div>'
			);
			$('div#vca-loading-overlay').show();
			$.post(
				action,
				$( '#' + dialogParams.btnID ).closest( 'form' ).serialize(),
				function(data){
					$('div#wpbody-content').append(data);
					$('div#vca-loading-overlay').hide();
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