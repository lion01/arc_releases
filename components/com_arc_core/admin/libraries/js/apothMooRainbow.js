/**
 * @package     Arc
 * @subpackage  Core
 * @copyright   Copyright (C) 2005 Punnet. All rights reserved. See COPYRIGHT_ARC.txt
 * @license     http://www.gnu.org/licenses/gpl.html GNU/GPL. See LICENSE_ARC.txt
 * Arc is free software: you can redistribute it and/or modify
 * it under the terms of version 3 of the GNU General Public License
 * as published by the Free Software Foundation.
 * The disclaimer of warranty as stated in the GPL applies to this program
 */

function apothMooRainbow(startColor, attachEle, targetEle) {
	var R = 255;
	var G = 255;
	var B = 255;

	if(startColor != 'null') {
		if(this.colorHash.get(startColor) != null) {
			var startColor = this.colorHash.get(startColor);
		}
		if(startColor.charAt(0)=="#") {
			function HexToR(h) {return parseInt((cutHex(h)).substring(0,2),16)}
			function HexToG(h) {return parseInt((cutHex(h)).substring(2,4),16)}
			function HexToB(h) {return parseInt((cutHex(h)).substring(4,6),16)}
			function cutHex(h) {return (h.charAt(0)=="#") ? h.substring(1,7):h}
		
			R = HexToR(startColor);
			G = HexToG(startColor);
			B = HexToB(startColor);	
		}
	}
	var r = new MooRainbow(attachEle, {
			'id': 'mooRainbow_' + attachEle,
			'startColor': [R, G, B],
			'onComplete': function(color) {
				$(targetEle).value = color.hex;
				$(targetEle).style.background = color.hex;
			},
			'imgPath': 'administrator/components/com_arc_core/images/',
			'wheel': true
		}
	);
}