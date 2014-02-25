function tooltipNewElement(newid) {
	if(document.createElement) {
		var el = document.createElement('div');
		el.id = newid;
		with(el.style) {
			display = 'none';
			position = 'absolute';
		}
		el.innerHTML = '&nbsp;';
		document.body.appendChild(el);
	}
}

function tooltipGetMousePosition(e) {
	var ie5 = (document.getElementById && document.all);
	var ns6 = (document.getElementById && !document.all);
	var ua = navigator.userAgent.toLowerCase();
	var isapple = (ua.indexOf('applewebkit') != -1 ? 1 : 0);
	var offsetx = 12;
	var offsety =  8;
	if(document.getElementById) {
		var iebody = (document.compatMode && document.compatMode != 'BackCompat') ? document.documentElement : document.body;
		var pagex = ( (ie5) ? iebody.scrollLeft : window.pageXOffset );
		var pagey = ( (ie5) ? iebody.scrollTop : window.pageYOffset );
		var mousex = (ie5) ? event.x : (ns6) ? clientX = e.clientX : false;
		var mousey = (ie5) ? event.y : (ns6) ? clientY = e.clientY:false;
		var lixlpixel_tooltip = document.getElementById('tooltip');
		lixlpixel_tooltip.style.left = (mousex+pagex+offsetx) + 'px';
		lixlpixel_tooltip.style.top = (mousey+pagey+offsety) + 'px';
	}
}

function tooltip(tip) {
	if( ! document.getElementById('tooltip') ) tooltipNewElement('tooltip');
	var lixlpixel_tooltip = document.getElementById('tooltip');
	lixlpixel_tooltip.innerHTML = tip;
	lixlpixel_tooltip.style.display = 'block';
	document.onmousemove = tooltipGetMousePosition;
}

function exit() {
	document.getElementById('tooltip').style.display = 'none';
}