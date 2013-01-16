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
$this->codeCol = 28;
?>
<table cellpadding="2" cellspacing="0">
	<tr>
		<td width="<?php echo $this->chartColWidth?>"><h3>Attendance Code Totals</h3></td>
		<td width="<?php echo $this->spacerColWidth?>">&nbsp;</td>
		<td width="<?php echo $this->tableColWidth?>"><h3>Statutory Attendance</h3></td>
	</tr>
	<tr>
		<td width="<?php echo $this->chartColWidth?>">
			<table cellpadding="2" cellspacing="0" border="1">
				<tr>
					<td width="<?php echo $this->codeCol; ?>" align="center"><strong>Code</strong></td>
					<td width="<?php echo $this->col2; ?>" align="center"><strong>Description</strong></td>
					<td width="<?php echo $this->col3; ?>" align="center"><strong>Sessions</strong></td>
					<td width="<?php echo $this->col4; ?>" align="center"><strong>%</strong></td>
				</tr>
				<?php $i = 0; ?>
				<?php foreach( $c AS $id=>$info ) : ?>
					<tr>
						<td width="<?php echo $this->codeCol; ?>" align="center"><?php echo JHTML::_( 'arc_attendance.marks', $info['object'], true ); ?></td>
						<td width="<?php echo $this->col2; ?>" align="center"><?php echo $info['object']->sc_meaning; ?></td>
						<td width="<?php echo $this->col3; ?>" align="center"><?php echo $info['count']; ?></td>
						<td width="<?php echo $this->col4; ?>" align="center"><?php echo ( ($cTotal['count'] != 0) ? number_format(($info['count']/$cTotal['count'] * 100), 1) : '0' ); ?></td>
					</tr>
					<?php $i++; ?>
				<?php endforeach; ?>
			</table>
		</td>
		<td width="<?php echo $this->spacerColWidth?>">&nbsp;</td>
		<td width="<?php echo $this->tableColWidth?>">
			<?php if( $this->statData ) : ?>
			<?php ob_start(); ?>
			<table cellpadding="2" cellspacing="0" border="1">
				<tr>
					<td width="~section~" align="center"><strong>Day</strong></td>
					<?php foreach( $sHeads as $time=>$title ) : ?>
						<?php $this->setPdfStrWidth( $title.' %', 'title' ); ?>
						<td width="~title~" align="center"><strong><?php echo $title; ?> %</strong></td>
					<?php endforeach; ?>
				</tr>
				<?php $i = 0; ?>
				<?php foreach( $s AS $day=>$sections ) : ?>
					<?php $this->setPdfStrWidth( $sections['text'], 'section' ); ?>
					<tr>
						<td width="~section~" align="center"><?php echo $sections['text']; unset($sections['text']); ?></td>
						<?php
						foreach( $sHeads as $time=>$title ) {
							if( is_array($sections[$time]) ) {
								$info = $sections[$time];
								echo '<td width="~title~" align="center">'.( ($info['total'] != 0) ? number_format(($info['count']/$info['total'] * 100), 1) : '0' ).'</td>';
							}
							else {
								echo '<td  width="~title~">&nbsp;</td>';
							}
						}
						?>
					</tr>
					<?php $i++; ?>
				<?php endforeach; ?>
			</table>
			<?php
				$statTable = ob_get_clean();
				echo $this->setStatTableColWidths( $statTable );
			?>
			<?php else : ?>
			&nbsp;
			<?php endif; ?>
		</td>
	</tr>
</table>