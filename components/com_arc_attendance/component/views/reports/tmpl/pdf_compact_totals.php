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

$this->codeCol = 28;
$descCol = $this->infoColWidth -$this->codeCol - $this->col3 - $this->col4 - 4;;
?>
<table cellpadding="2" cellspacing="0" border="1">
	<tr>
		<td width="<?php echo $this->codeCol; ?>" align="center"><strong>Code</strong></td>
		<td width="<?php echo $descCol; ?>" align="center"><strong>Description</strong></td>
		<td width="<?php echo $this->col3; ?>" align="center"><strong>Sessions</strong></td>
		<td width="<?php echo $this->col4; ?>" align="center"><strong>%</strong></td>
	</tr>
	<?php $i = 0; ?>
	<?php foreach( $c AS $id=>$info ) : ?>
		<tr>
			<td width="<?php echo $this->codeCol; ?>" align="center"><?php echo JHTML::_( 'arc_attendance.marks', $info['object'], true ); ?></td>
			<td width="<?php echo $descCol; ?>" align="center"><?php echo $info['object']->sc_meaning; ?></td>
			<td width="<?php echo $this->col3; ?>" align="center"><?php echo $info['count']; ?></td>
			<td width="<?php echo $this->col4; ?>" align="center"><?php echo ( ($cTotal['count'] != 0) ? number_format(($info['count']/$cTotal['count'] * 100), 1) : '0' ); ?></td>
		</tr>
		<?php $i++; ?>
	<?php endforeach; ?>
</table>