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

// create class attendance stacked histogram definitions for google api
// create class attendance colour key
$allMax = max( $this->_data['all_totals']['group'] );
$this->allTotal = $this->_data['all_possible'];
foreach( $this->_data['all'] as $group=>$meaningArray ) {
	foreach( $meaningArray as $meaning=>$count ) {
		$all[$meaning][] = ApotheosisLib::gChartEncode( (int)(($allMax == 0) ? 0 : ($count/$allMax)*4095), 'extended' );
	}
}
foreach( $all as $meaning=>$values ) {
	$all[$meaning] = implode( $values );
}
$i = 0;
$allList = '';
foreach( reset($this->_data['all']) as $meaning=>$v ) {
	$meaningCount = $this->_data['all_totals']['meaning'][$meaning];
	if( $meaningCount > 0 ) {
		$allList .= '<div class="colorbox" style="background: #'.$this->colours[$i].';"></div><span class="colorbox_key">'.$meaning.': </span><span class="colorbox_value">'.$meaningCount.'</span><br />'."\n";
	}
	$i++;
}

// see http://code.google.com/apis/chart/ for documentation
$this->allHistoImageUrl = 'https://chart.googleapis.com/chart?cht=bhs&chd=e:'.implode( ',', $all).'&chs=400x340&chxt=x,y&chxs=0,000000,0,1,_|1,000000,18,1,lt&chxl=1:|'.urlencode( utf8_encode(implode('|', array_reverse(array_keys($this->_data['all_totals']['group'])))) ).'|&chco='.implode(',', $this->colours).'&chbh=a';
?>