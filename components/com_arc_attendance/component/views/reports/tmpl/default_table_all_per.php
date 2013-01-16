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
<div id="summary_table_all_per">
	<table class="summary_table">
		<tr>
			<th colspan="2"><?php echo $this->allPerListTitle; ?></th>
			<th>%</th>
		</tr>
		<?php $i = 0; ?>
		<?php foreach( $this->_data['all_totals']['meaning'] as $meaning=>$count ): ?>
			<?php
				$rowClass = array();
				if( !($i%2) ) {
					$rowClass[] = 'oddrow'; 
				}
				if( !empty($this->_data['all_sc'][$meaning]) ) {
					$rowClass[] = 'all_per_sub_row_clicker';
				}
				$rowClass = implode( ' ', $rowClass );
				if( ($count > 0) && ($this->allTotal > 0) ) {
					$count = ($count/$this->allTotal)*100;
				}
			?>
			<tr <?php echo ( $rowClass != '' ) ? 'class="'.$rowClass.'"' : ''; ?>>
				<td rowspan="<?php echo count( $this->_data['all_sc'][$meaning] ) + 1; ?>" class="sum_table_key_cell"><div class="colorbox" style="background: <?php echo '#'.$this->colours[$i]; ?>;"></div></td>
				<td class="all_row_desc"><?php echo $meaning; ?></td>
				<td><?php echo ( (isset($this->_data['all_totals']['meaning_limited'][$meaning])) ? number_format($count, 1) : '-' ); ?></td>
			</tr>
			<?php foreach( $this->_data['all_sc'][$meaning] as $scDesc=>$scCount ): ?>
				<?php
				if( $this->allTotal > 0 ) {
					$scMeaningCount = ( ($scCount > 0) ? (($scCount/$this->allTotal)*100) : 0 );
				}
				else {
					$scMeaningCount = 0;
				}
				?>
				<tr <?php echo ( !($i%2) ? 'class="oddrow all_per_sub_row"' : 'class="all_per_sub_row"' ); ?>>
					<td class="all_per_sub_row_desc"><?php echo $scDesc; ?></td>
					<td><?php echo ( (isset($this->_data['all_totals']['meaning_limited'][$meaning])) ? number_format($scMeaningCount, 1) : '-' ); ?></td>
				</tr>
			<?php endforeach; ?>
			<?php $i++; ?>
		<?php endforeach; ?>
		<tr>
			<td colspan="2">Possible Attendances</td>
			<td><?php echo $this->allTotal; ?></td>
		</tr>
	</table>
</div>