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
<tr class="<?php echo 'row'.$this->item->index % 2; ?>">
	<td>
		<input type="checkbox" id="cb<?php echo $this->item->index; ?>" onclick="isChecked(this.checked);"value="<?php echo $this->item->id; ?>" name="eid[<?php echo $this->item->id; ?>]"<?php echo ($this->item->favourite == true ? ' checked="checked"' : ''); ?> />
	</td>
	<td>
		<?php echo $this->item->id; ?>
	</td>
	<td>
		<?php echo $this->item->word; ?>
	</td>
	<td>
		<?php echo $this->item->male; ?>
	</td>
	<td>
		<?php echo $this->item->female; ?>
	</td>
	<td>
		<?php echo $this->item->neuter; ?>
	</td>
	<td>
		<?php echo $this->item->property; ?>
	</td>
</tr>