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
$this->allListTitle = 'Class Attendance';
$allList = '';
foreach( reset($this->_data['all']) as $meaning=>$v ) {
	$meaningCount = ( isset($this->_data['all_totals']['meaning'][$meaning]) ? $this->_data['all_totals']['meaning'][$meaning] : 0 );
	if( $meaningCount > 0 ) {
		$allList .= '<div class="colorbox" style="background: #'.$this->colours[$i].';"></div><span class="colorbox_key">'.$meaning.': </span><span class="colorbox_value">'.$meaningCount.'</span><br />'."\n";
	}
	$i++;
}
?>
<!-- html to display statutory attendance pie chart -->
<div id="att_panel_all">
	<div class="att_panel_image">
		<!-- see http://code.google.com/apis/chart/ for documentation -->
		<img src="https://chart.googleapis.com/chart
				?cht=bhs
				&amp;chd=e:<?php echo implode( ',', $all); ?>
				&amp;chs=200x170
				&amp;chxt=x,y
				&amp;chxs=0,000000,0,1,__|1,000000,10,1,lt
				&amp;chxl=1:|<?php echo urlencode( utf8_encode(implode('|', array_reverse(array_keys($this->_data['all_totals']['group'])))) ) ?>|
				&amp;chco=<?php echo implode(',', $this->colours); ?>
				&amp;chbh=a"
			class="att_panel_img"
			alt="<?php echo $this->allListTitle; ?>"
			title="<?php echo $this->allListTitle; ?> :: <?php echo htmlspecialchars($allList); ?>" />
	</div>
</div>