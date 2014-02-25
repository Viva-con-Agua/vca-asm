jQuery(document).ready(function(){
  if(jQuery("#pass-strength-result").length > 0){
		jQuery("#pass1").bind("keyup", function(){
		var pass1 = jQuery("#pass1").val();
		var pass2 = jQuery("#pass2").val();
		var username = jQuery("#user_login").val();
		var strength = passwordStrength(pass1, username, pass2);
		updateStrength(strength);
		});
		jQuery("#pass2").bind("keyup", function(){
		var pass1 = jQuery("#pass1").val();
		var pass2 = jQuery("#pass2").val();
		var username = jQuery("#user_login").val();
		var strength = passwordStrength(pass1, username, pass2);
		updateStrength(strength);
		});
	}
});

function updateStrength(strength){
    var dom = jQuery("#pass-strength-result");
    switch(strength){
		case 1:
		  dom.removeClass().addClass(VCAasmMeter.classes[strength]).text(VCAasmMeter.terms[strength]);
		  break;
		case 2:
		  dom.removeClass().addClass(VCAasmMeter.classes[strength]).text(VCAasmMeter.terms[strength]);
		  break;
		case 3:
		  dom.removeClass().addClass(VCAasmMeter.classes[strength]).text(VCAasmMeter.terms[strength]);
		  break;
		case 4:
		 dom.removeClass().addClass(VCAasmMeter.classes[strength]).text(VCAasmMeter.terms[strength]);
		  break;
		case 5:
		  dom.removeClass().addClass(VCAasmMeter.classes[strength]).text(VCAasmMeter.terms[strength]);
		  break;
		default:
    }
}