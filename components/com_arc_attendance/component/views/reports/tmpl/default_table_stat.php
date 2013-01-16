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
<div id="summary_table_stat">
	<table class="summary_table">
		<tr>
			<th colspan="2"><?php echo $this->statListTitle; ?></th>
			<th>Sessions</th>
			<th>%</th>
		</tr>
		<?php $i = 0; ?>
		<?php foreach( $this->_data['statutory'] as $desc=>$count ): ?>
			<?php
				$rowClass = array();
				if( !($i%2) ) {
					$rowClass[] = 'oddrow'; 
				}
				if( !empty($this->_data['statutory_sc'][$desc]) ) {
					$rowClass[] = 'stat_sub_row_clicker';
				}
				$rowClass = implode( ' ', $rowClass );
			?>
			<tr <?php echo ( $rowClass != '' ) ? 'class="'.$rowClass.'"' : ''; ?>>
				<td rowspan="<?php echo count( $this->_data['statutory_sc'][$desc] ) + 1; ?>" class="sum_table_key_cell"><div class="colorbox" style="background: <?php echo '#'.$this->colours[$i]; ?>;"></div></td>
				<td class="stat_row_desc"><?php echo $desc; ?></td>
				<td><?php echo $count; ?></td>
				<td><?php echo ( (isset($this->_data['statutory_limited'][$desc])) ? (($this->statTotal > 0) ? number_format((($count / $this->statTotal) * 100), 1) : 0) : '-' ); ?></td>
			</tr>
			<?php foreach( $this->_data['statutory_sc'][$desc] as $scDesc=>$scCount ): ?>
				<tr <?php echo ( !($i%2) ? 'class="oddrow stat_sub_row"' : 'class="stat_sub_row"' ); ?>>
					<td class="stat_sub_row_desc"><?php echo $scDesc; ?></td>
					<td><?php echo $scCount; ?></td>
					<td><?php echo ( (isset($this->_data['statutory_limited'][$desc])) ? (($this->statTotal > 0) ? number_format((($scCount / $this->statTotal) * 100), 1) : 0) : '-' ); ?></td>
				</tr>
			<?php endforeach; ?>
			<?php $i++; ?>
		<?php endforeach; ?>
		<tr>
			<td colspan="2">Possible Attendances</td>
			<td colspan="2"><?php echo $this->statTotal; ?></td>
		</tr>
	</table>
</div>