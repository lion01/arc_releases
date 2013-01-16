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
	<td><?php echo $this->consumer->getDatum('name'); ?></td>
	<td><?php echo $this->consumer->getDatum('description'); ?></td>
	<td><?php echo $this->consumer->getDatum('cons_key'); ?></td>
	<td align="center">
		<img src="images/<?php echo ( $this->consumer->getDatum('deleted') ? 'publish_x.png' : 'tick.png' );?>" width="16" height="16" border="0" alt="<?php echo ( $this->consumer->getDatum('deleted') ) ? JText::_('No') : JText::_('Yes'); ?>" />
	</td>
</tr>