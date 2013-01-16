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

?>
<table cellpadding="2" cellspacing="0" border="1">
	<tr>
		<td colspan="2" width="~col1+col2~" align="center"><strong>Description</strong></td>
		<td width="<?php echo $this->col3; ?>" align="center"><strong>Sessions</strong></td>
		<td width="<?php echo $this->col4; ?>" align="center"><strong>%</strong></td>
	</tr>
	<?php $i = 0; ?>
	<?php foreach( $this->_data['statutory'] as $desc=>$count ): ?>
		<?php $this->setPdfStrWidth( $desc, 'chart' ); ?>
		<tr>
			<td width="<?php echo $this->col1; ?>" rowspan="<?php echo count( $this->_data['statutory_sc'][$desc] ) + 1; ?>">
				<table>
					<tr>
						<td width="10" bgcolor="<?php echo '#'.$this->colours[$i]; ?>"></td>
					</tr>
				</table>
			</td>
			<td width="~col2~"><?php echo $desc; ?></td>
			<td width="<?php echo $this->col3; ?>" align="center"><?php echo $count; ?></td>
			<td width="<?php echo $this->col4; ?>" align="center"><?php echo ( (isset($this->_data['statutory_limited'][$desc])) ? (($this->statTotal > 0) ? number_format((($count / $this->statTotal) * 100), 1) : 0) : '-' ); ?></td>
		</tr>
		<?php foreach( $this->_data['statutory_sc'][$desc] as $scDesc=>$scCount ): ?>
			<?php $this->setPdfStrWidth( $scDesc, 'chart' ); ?>
			<tr>
				<td width="~col2~" align="right"><i><?php echo $scDesc; ?></i></td>
				<td width="<?php echo $this->col3; ?>" align="center"><i><?php echo $scCount; ?></i></td>
				<td width="<?php echo $this->col4; ?>" align="center"><i><?php echo ( (isset($this->_data['statutory_limited'][$desc])) ? (($this->statTotal > 0) ? number_format((($scCount / $this->statTotal) * 100), 1) : 0) : '-' ); ?></i></td>
			</tr>
		<?php endforeach; ?>
		<?php $i++; ?>
	<?php endforeach; ?>
	<tr>
		<td colspan="2" width="~col1+col2~" align="center">Possible Attendances</td>
		<td colspan="2" width="<?php echo ( $this->col3 + $this->col4 ); ?>" align="center"><?php echo $this->statTotal; ?></td>
	</tr>
</table>