<?php
/**
 * @package     Arc
 * @subpackage  Attendance
 * @copyright   Copyright (C) 2005 Punnet. All rights reserved. See COPYRIGHT_ARC.txt
 * @license     http://www.gnu.org/licenses/gpl.html GNU/GPL. See LICENSE_ARC.txt
 * Arc is free software: you can redistribute it and/or modify
 * it under the terms of version 3 of the GNU General Public License
 * as published by the Free Software Foundation.
 * The disclaimer of warranty as stated in the GPL applies to this program
 */

defined( '_JEXEC' ) or die( 'Restricted access' );

// create statutory attendance pie chart definitions for google api
// create statutory attendance colour key
$i = 0;
$statList = '';
$this->statTotal = $this->_data['statutory_possible'];
foreach( $this->_data['statutory'] as $k=>$v ) {
	if( $v > 0 ) {
		$statList .= '<div class="colorbox" style="background: #'.$this->colours[$i].';"></div><span class="colorbox_key">'.$k.': </span><span class="colorbox_value">'.$v.($this->_data['statutory_limited'][$k] > 0 ? ' ('.number_format( (($v/$this->statTotal)*100), 1 ).'%)' : '').'</span><br />'."\n";
	}
	$stat[] = ApotheosisLib::gChartEncode($v, 'extended');
	$i++;
}

// see http://code.google.com/apis/chart/ for documentation
$this->statPieImageUrl = 'https://chart.googleapis.com/chart?cht=p3&chd=e:'.implode($stat).'&chs=400x340&chco='.implode('|', $this->colours);
?>