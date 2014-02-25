(function($){ // closure

$(document).ready(function(){
  if($("#pass-strength-result").length > 0){
		$("#pass1").bind("keyup", function(){
		var pass1 = $("#pass1").val();
		var pass2 = $("#pass2").val();
		var username = $("#user_login").val();
		var strength = passwordStrength(pass1, username, pass2);
		updateStrength(strength);
		});
		$("#pass2").bind("keyup", function(){
		var pass1 = $("#pass1").val();
		var pass2 = $("#pass2").val();
		var username = $("#user_login").val();
		var strength = passwordStrength(pass1, username, pass2);
		updateStrength(strength);
		});
	}
});

function updateStrength(strength){
    var dom = $("#pass-strength-result");
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

})(jQuery); // closure