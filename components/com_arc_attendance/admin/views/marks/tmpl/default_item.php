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
<tr class="<?php echo 'row'.$this->item->index % 2; ?>">
<td><input type="checkbox" id="cb<?php echo $this->item->index; ?>" name="eid[<?php echo $this->item->code; ?>][<?php echo $this->item->type; ?>]"<?php echo ($this->item->favourite == true ? ' checked="checked"' : ''); ?> /></td>
<td><a href="index.php?option=com_arc_attendance&view=<?php echo JRequest::getVar('view'); ?>&task=editmark&code=<?php echo urlencode($this->item->code); ?>&type=<?php echo $this->item->type?>"><?php echo $this->item->code; ?></a></td>
<td><?php echo $this->item->school_meaning; ?></td>
<td><?php echo $this->item->statistical_meaning; ?></td>
<td><?php echo $this->item->physical_meaning; ?></td>
<td align="center">
	<a href="javascript:void(0);" onclick="return listItemTask('cb<?php echo $this->item->index; ?>','toggleCommon')" title="<?php echo $this->item->is_common;?>">
		<img src="images/<?php echo ( $this->item->is_common ) ? 'tick.png' : ( $row->state != -1 ? 'publish_x.png' : 'disabled.png' );?>" width="16" height="16" border="0" alt="<?php echo ( $this->item->is_common ) ? JText::_( 'Yes' ) : JText::_( 'No' );?>" /></a>
</td>
<td><?php echo $this->item->type; ?></td>
<td>
	<a href="javascript:void(0);" onclick="return listItemTask('cb<?php echo $this->item->index; ?>','toggleAllday')" title="<?php echo $this->item->is_common;?>">
		<img src="images/<?php echo ( $this->item->apply_all_day ) ? 'tick.png' : ( $row->state != -1 ? 'publish_x.png' : 'disabled.png' );?>" width="16" height="16" border="0" alt="<?php echo ( $this->item->apply_all_day ) ? JText::_( 'Yes' ) : JText::_( 'No' );?>" /></a>	
</td>
<td><?php echo $this->item->order_id; ?></td>
</tr>