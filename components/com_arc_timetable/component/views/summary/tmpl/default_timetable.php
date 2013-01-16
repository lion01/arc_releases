<?php
/**
 * @package     Arc
 * @subpackage  Timetable
 * @copyright   Copyright (C) 2005 Punnet. All rights reserved. See COPYRIGHT_ARC.txt
 * @license     http://www.gnu.org/licenses/gpl.html GNU/GPL. See LICENSE_ARC.txt
 * Arc is free software: you can redistribute it and/or modify
 * it under the terms of version 3 of the GNU General Public License
 * as published by the Free Software Foundation.
 * The disclaimer of warranty as stated in the GPL applies to this program
 */

defined( '_JEXEC' ) or die( 'Restricted access' );

foreach ($this->timetable as $patternId=>$sections) {
	$pIds[] = $patternId;
	foreach ($sections as $sectionId=>$data) {
		$sIds[$sectionId] = $sectionId;
	}
}
?>
<table>
<?php
foreach ($pIds as $pId) {
	$tmp = explode('-', $pId);
	$pId = $tmp[0];
	$dId = $tmp[1];
	echo '<tr>';
	echo '<td></td><th>'.ApotheosisLibCycles::cycleDayToDow($dId, $pId, 'text').'</th>'."\n";
	echo '</tr>'."\n";
	foreach ($sIds as $sId) {
		$this->loadPeriod($pId, $dId, $sId);
		if (!is_null($this->period)) {
			echo '<tr>'."\n";
			echo '<td>'.$this->period->day_section.'</td>'."\r\n";
			echo $this->loadTemplate('timetable_section');
			echo '</tr>';
		}
	}
}
?>
</table>

<?php /*
<table>
<tr><th></th>
	<?php
	foreach ($pIds as $pId) {
		$tmp = explode('-', $pId);
		$pId = $tmp[0];
		$dId = $tmp[1];
		echo '<th>'.ApotheosisLibCycles::cycleDayToDow($dId, $pId, 'text').'</th>';
	}
foreach ($sIds as $sId) {
	echo '<tr>';
	foreach ($pIds as $pId) {
		$tmp = explode('-', $pId);
		$pId = $tmp[0];
		$dId = $tmp[1];
		$this->loadPeriod($pId, $dId, $sId);
		echo '<td>'.$this->period->day_section.'</td>'."\r\n";
		echo $this->loadTemplate('timetable_section');
	}
	echo '</tr>';
}
echo '</table>';

*/
?>