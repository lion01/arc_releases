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

$this->marksColWidth = $this->compactWeekCol + ( $this->dayCount * $this->compactMarkCol ) + 4;
$this->spacerColWidth = 16;
$this->infoColWidth = $this->usableWidth - $this->marksColWidth - $this->spacerColWidth;
$statAttendTable = $this->loadTemplate( 'compact_stat_attend' );
$statAttendTableWidth = $this->colTotal + 4;
$this->personalTableWidth = $this->infoColWidth - $statAttendTableWidth - $this->spacerColWidth + 1;
?>
<table cellpadding="2" cellspacing="0">
	<tr>
		<td width="<?php echo $this->marksColWidth; ?>" align="center"><h3>Statutory Attendance by Week</h3></td>
		<td width="<?php echo $this->spacerColWidth; ?>">&nbsp;</td>
		<td width="<?php echo $this->infoColWidth; ?>" align="center"><h3>Attendance Summary</h3></td>
	</tr>
	<tr>
		<td rowspan="7" width="<?php echo $this->marksColWidth; ?>">
			<table cellpadding="2" cellspacing="0" border="1">
				<tr>
					<td width="<?php echo $this->compactWeekCol; ?>" align="center"><strong>Week</strong></td>
					<td colspan="<?php echo $this->dayCount; ?>" width="<?php echo ( $this->compactMarkCol * $this->dayCount ); ?>" align="center"><strong>Marks</strong></td>
				</tr>
			<?php foreach( $this->weekRows as $week ) : ?>
				<tr><?php echo $week ?></tr>
			<?php endforeach; ?>
			</table>
		</td>
		<td rowspan="7" width="<?php echo $this->spacerColWidth; ?>">&nbsp;</td>
		<td width="<?php echo $this->infoColWidth; ?>"><?php echo $this->loadTemplate( 'table_stat_compact' ); ?></td>
	</tr>
	<tr>
		<td width="<?php echo $this->infoColWidth; ?>">&nbsp;</td>
	</tr>
	<tr>
		<td width="<?php echo $this->infoColWidth; ?>" align="center"><h3>Attendance Code Totals</h3></td>
	</tr>
	<tr>
		<td width="<?php echo $this->infoColWidth; ?>"><?php echo $this->loadTemplate( 'compact_totals' ); ?></td>
	</tr>
	<tr>
		<td width="<?php echo $this->infoColWidth; ?>">&nbsp;</td>
	</tr>
	<tr>
		<td width="<?php echo $statAttendTableWidth; ?>" align="center"><h3>Statutory Attendance</h3></td>
		<td width="<?php echo $this->spacerColWidth; ?>">&nbsp;</td>
		<td width="<?php echo $this->personalTableWidth; ?>" align="center"><h3>Personal Details</h3></td>
	</tr>
	<tr>
		<td width="<?php echo $statAttendTableWidth; ?>" align="center"><?php echo $statAttendTable; ?></td>
		<td width="<?php echo $this->spacerColWidth; ?>">&nbsp;</td>
		<td width="<?php echo $this->personalTableWidth; ?>"><?php echo $this->loadTemplate( 'personal_table' ); ?></td>
	</tr>
</table>