<?php
/**
 * @package     Arc
 * @subpackage  Report
 * @copyright   Copyright (C) 2005 Punnet. All rights reserved. See COPYRIGHT_ARC.txt
 * @license     http://www.gnu.org/licenses/gpl.html GNU/GPL. See LICENSE_ARC.txt
 * Arc is free software: you can redistribute it and/or modify
 * it under the terms of version 3 of the GNU General Public License
 * as published by the Free Software Foundation.
 * The disclaimer of warranty as stated in the GPL applies to this program
 */

defined( '_JEXEC' ) or die( 'Restricted access' );
?>
<tr class="<?php echo 'row'.$this->course->index % 2; ?>">
	<td>
		<input type="checkbox" id="cb<?php echo $this->course->index; ?>" onclick="isChecked(this.checked);"value="<?php echo $this->course->id; ?>" name="eid[<?php echo $this->course->id; ?>]"<?php echo ($this->course->favourite == true ? ' checked="checked"' : ''); ?> />
	</td>
	<td>
		<?php echo $this->course->id; ?>
	</td>
	<td>
		<?php echo $this->course->fullname; ?>
	</td>
	<td>
		<?php echo $this->course->shortname; ?>
	</td>
	<td>
		<?php echo $this->course->parent_name; ?>
	</td>
	<td>
		<?php echo $this->course->twin_name; ?>
	</td>
	<td>
		<?php echo $this->course->start_date; ?>
	</td>
	<td>
		<?php echo $this->course->end_date; ?>
	</td>
</tr>