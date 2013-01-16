/**
 * @package     Arc
 * @subpackage  Assessment
 * @copyright   Copyright (C) 2005 Punnet. All rights reserved. See COPYRIGHT_ARC.txt
 * @license     http://www.gnu.org/licenses/gpl.html GNU/GPL. See LICENSE_ARC.txt
 * Arc is free software: you can redistribute it and/or modify
 * it under the terms of version 3 of the GNU General Public License
 * as published by the Free Software Foundation.
 * The disclaimer of warranty as stated in the GPL applies to this program
 */

var oldVal;

/**
 * Allows moving around inputs/select by Ctrl+arrows
 *
 * @param object event data
 */
function onKeyDownArrowsHandler(e) {
	e = e||window.event;
	var o = (e.srcElement||e.target);
	if (!o) return;
	var tn = o.tagName.toLowerCase();
	if (tn != "textarea" && tn != "input" && tn != "select") return;
	
	if (!e.ctrlKey) return;
	if (!o.id) return;
	
	var pos = o.id.split("_");
	if (pos[0] != "field" || typeof pos[2] == "undefined") return;
	
	var x = pos[1], y=pos[2];
	
	switch(e.keyCode) {
	case 38: x--; break; // up
	case 40: x++; break; // down
	case 37: y--; break; // left
	case 39: y++; break; // right
	default: return;
	}

	var id = "field_" + x + "_" + y;
	var nO = document.getElementById(id);
	
	if (!nO) return;
	
	nO.focus();
	
	if (nO.tagName != 'SELECT') {
		nO.select();
	}
	else {
		oldVal = nO.value;
	}
	
	e.returnValue = false;
}

function onKeyUpArrowsHandler(e) {
	e = e||window.event;
	var o = (e.srcElement||e.target);
	if (oldVal == undefined) return;
	if (!o) return;
	var tn = o.tagName.toLowerCase();
	if (tn != "select") return;
	if (!e.ctrlKey) return;
	
	o.value = oldVal;
}

// Having set up the key handlers let's set the various listeners
window.addEvent('domready', function(){
	document.onkeydown = onKeyDownArrowsHandler;	
	document.onkeyup   = onKeyUpArrowsHandler;	
	
	//do your tips stuff in here...
	var classTip = new Tips($$('.classTip'), {
		className: 'custom', //this is the prefix for the CSS class
		initialize:function(){
			this.fx = new Fx.Style(this.toolTip, 'opacity', {duration: 300, wait: false}).set(0);
		},
		onShow: function(toolTip) {
			this.fx.start(1);
		},
		onHide: function(toolTip) {
			this.fx.start(0);
		}
	});
});
