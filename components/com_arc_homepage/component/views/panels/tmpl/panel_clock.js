/**
 * @package     Arc
 * @subpackage  Homepage
 * @copyright   Copyright (C) 2005 Punnet. All rights reserved. See COPYRIGHT_ARC.txt
 * @license     http://www.gnu.org/licenses/gpl.html GNU/GPL. See LICENSE_ARC.txt
 * Arc is free software: you can redistribute it and/or modify
 * it under the terms of version 3 of the GNU General Public License
 * as published by the Free Software Foundation.
 * The disclaimer of warranty as stated in the GPL applies to this program
 */

function panelReady() {
	var flashvars = {
		clockSkin: 'components/com_arc_homepage/clock/media/skins/wildern_clock_face.png',
		arrowSkin: '2',
		arrowScale: '70',
/*		widgetUrl: 'http://isitchristmas.com',*/
/*		UTCTime: <?php echo '\''.date('H:i:s').'\''; ?>,*/
/*		timeOffset: '0000'*/
	};
	swfobject.embedSWF(
		'components/com_arc_homepage/clock/media/devAnalogClock.swf', // path to the widget
		'homepage_clock', //container id where widget will appear 
		'90', // width of the widget
		'90', // height of the widget
		'8', // flash version (it�s recommend not to change this)
		'components/com_arc_homepage/clock/media/expressInstall.swf',
		flashvars,
		{scale: 'noscale', wmode: 'transparent'}
	);
}

//call this function with a slight delay
panelReady.delay(200);