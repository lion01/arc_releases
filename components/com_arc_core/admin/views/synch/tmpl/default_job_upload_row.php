<?php
/**
 * @package     Arc
 * @subpackage  Core
 * @copyright   Copyright (C) 2005 Punnet. All rights reserved. See COPYRIGHT_ARC.txt
 * @license     http://www.gnu.org/licenses/gpl.html GNU/GPL. See LICENSE_ARC.txt
 * Arc is free software: you can redistribute it and/or modify
 * it under the terms of version 3 of the GNU General Public License
 * as published by the Free Software Foundation.
 * The disclaimer of warranty as stated in the GPL applies to this program
 */

defined( '_JEXEC' ) or die( 'Restricted access' );

$jobCom = $this->batches[$this->job['batch_id']]['component'];
$report = $this->job['call'];
$formatArray = $this->model->getCSVcolumns( $jobCom, $report );
foreach( $formatArray as $column=>$desc ) {
	$formatArray[$column] = '<span class="hasTip h_word" title="'.$column.'::'.$desc.'">'.$column.'</span>';
}
$format = implode( ', ', $formatArray );
$complete = strpos( $this->job['params'], 'complete=1' ) !== false ? true : false;
?>
<tr class="<?php echo 'row'.$this->curIndex % 2; ?>">
	<td><?php echo $this->job['src_name']; ?></td>
	<td><?php echo $this->job['call']; ?></td>
	<td><?php echo nl2br($this->job['params']); ?></td>
	<td><?php echo $format; ?></td>
	<td align="center">
		<img src="images/<?php echo ( $complete ? 'tick.png' : 'publish_x.png' ); ?>" width="16" height="16" border="0" alt="<?php echo ( $complete ) ? JText::_('No') : JText::_('Yes'); ?>" />
	</td>
	<td>
		<input type="file" name="filename_<?php echo $this->job['batch_id'].'_'.$this->job['id']; ?>" size="50" />
	</td>
	<td><?php echo ApotheosisData::_( 'core.getJobFiles', $this->job['id'] ); ?></td>
	<td align="center">
		<img src="images/<?php echo ( $this->job['ready'] ? 'tick.png' : 'publish_x.png' ); ?>" width="16" height="16" border="0" alt="<?php echo ( $this->job['taken'] ) ? JText::_('No') : JText::_('Yes'); ?>" />
	</td>
</tr>