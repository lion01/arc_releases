<?php
/**
 * @package     Arc
 * @subpackage  Course
 * @copyright   Copyright (C) 2005 Punnet. All rights reserved. See COPYRIGHT_ARC.txt
 * @license     http://www.gnu.org/licenses/gpl.html GNU/GPL. See LICENSE_ARC.txt
 * Arc is free software: you can redistribute it and/or modify
 * it under the terms of version 3 of the GNU General Public License
 * as published by the Free Software Foundation.
 * The disclaimer of warranty as stated in the GPL applies to this program
 */

defined( '_JEXEC' ) or die( 'Restricted access' );
?>
<tr class="<?php echo 'row'.$this->curIndex % 2; ?>">
	<td align="center">
		<input type="checkbox" id="cb<?php echo $this->curIndex; ?>" name="eid[<?php echo $this->curIndex; ?>]" onclick="isChecked(this.checked);" />
	</td>
	<td align="center"><?php echo $this->course->getData('id'); ?></td>
	<td><?php echo $this->course->getData('fullname'); ?></td>
	<td><?php echo $this->course->getData('description'); ?></td>
	<td><?php echo $this->course->getData('shortname'); ?></td>
	<td><?php echo $this->course->getData('year'); ?></td>
	<td><?php echo $this->course->getData('type'); ?></td>
	<td><?php echo $this->course->getData('parent'); ?></td>
	<td align="center"><?php echo $this->course->getData('start_date'); ?></td>
	<td align="center"><?php echo $this->course->getData('end_date'); ?></td>
	<td align="center"><?php echo $this->course->getData('sortorder'); ?></td>
	<td align="center">
		<img src="images/<?php echo ( $this->course->getData('reportable') ? 'tick.png' : 'publish_x.png' );?>" width="16" height="16" border="0" alt="<?php echo ( $this->course->getData('reportable') ) ? JText::_('Yes') : JText::_('No'); ?>" />
	</td>
	<td align="center"><?php echo $this->course->getData('ext_course_id_2'); ?></td>
	<td align="center">
		<img src="images/<?php echo ( $this->course->getData('ext_course_id') ? 'tick.png' : 'publish_x.png' );?>" width="16" height="16" border="0" alt="<?php echo ( $this->course->getData('ext_course_id') ) ? JText::_('Yes') : JText::_('No'); ?>" title="<?php echo $this->course->getData('ext_course_id'); ?>" />
	</td>
</tr>