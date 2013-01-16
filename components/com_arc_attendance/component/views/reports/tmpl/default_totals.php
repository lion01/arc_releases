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

$c = $this->model->getCodeTotals( $this->sheetId );
$cTotal = $c['total'];
unset( $c['total'] );
$s = $this->model->getSessionTotals( $this->sheetId );
$sHeads = ($s['heads']);
unset( $s['heads'] );
?>
<div class="summary_div">
	<div id="summary_code_div">
		<div class="totals_table_title_div">Attendance Code Totals</div>
		<table class="summary_table">
			<tr>
				<th>Code</th>
				<th>Description</th>
				<th>Sessions</th>
				<th>%</th>
			</tr>
			<?php $i = 0; ?>
			<?php foreach( $c AS $id=>$info ) : ?>
				<tr <?php echo ( !($i%2) ? 'class="oddrow"' : '' ); ?>>
					<td class="sum_table_key_cell"><?php echo JHTML::_( 'arc_attendance.marks', $info['object'] ); ?></td>
					<td><?php echo $info['object']->sc_meaning; ?></td>
					<td><?php echo $info['count']; ?></td>
					<td><?php echo ( ($cTotal['count'] != 0) ? number_format(($info['count']/$cTotal['count'] * 100), 1) : '0' ); ?></td>
				</tr>
				<?php $i++; ?>
			<?php endforeach; ?>
		</table>
	</div>
	<?php if( $this->statData ) : ?>
	<div id="summary_sessions_div">
		<div class="totals_table_title_div">Statutory Attendance</div>
		<table class="summary_table">
			<tr>
				<th>Day</th>
				<?php foreach( $sHeads as $time=>$title ) : ?>
				<th><?php echo $title; ?> %</th>
				<?php endforeach; ?>
			</tr>
			<?php $i = 0; ?>
			<?php foreach( $s AS $day=>$sections ) : ?>
			<tr <?php echo ( !($i%2) ? 'class="oddrow"' : '' ); ?>>
				<td><?php echo $sections['text']; unset($sections['text']); ?></td>
				<?php
				foreach( $sHeads as $time=>$title ) {
					if( is_array($sections[$time]) ) {
						$info = $sections[$time];
						echo '<td>'.( ($info['total'] != 0) ? number_format(($info['count']/$info['total'] * 100), 1) : '0' ).'</td>';
					}
					else {
						echo '<td>&nbsp;</td>';
					}
				}
				?>
			</tr>
			<?php $i++; ?>
			<?php endforeach; ?>
		</table>
	</div>
	<?php endif; ?>
</div>