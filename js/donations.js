
jQuery('#finances_donations_cups').change(function () {
	donationsCalc();
});
jQuery('#finances_donations_extra').change(function () {
	donationsCalc();
});

function donationsCalc() {
	var deposit = parseInt(jQuery('#finances_donations_cups').val());
	if( isNaN(deposit) ) {
		deposit = 0;
	}
	var extra = parseInt(jQuery('#finances_donations_extra').val());
	if( isNaN(extra) ) {
		extra = 0;
	}
	var total = Math.floor( deposit + extra );
	jQuery('#finances_donations_total').val(total);
}
