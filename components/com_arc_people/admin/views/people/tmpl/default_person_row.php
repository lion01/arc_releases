<?php
/**
 * @package     Arc
 * @subpackage  People
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
	<td align="center"><?php echo $this->person->getDatum('id'); ?></td>
	<td align="center"><?php echo $this->person->getDatum('ext_person_id'); ?></td>
	<td align="center"><?php echo $this->person->getDatum('juserid'); ?></td>
	<td align="center"><?php echo $this->person->getDatum('upn'); ?></td>
	<td align="center"><?php echo $this->person->getDatum('title'); ?></td>
	<td><?php echo $this->person->getDatum('firstname'); ?></td>
	<td><?php echo $this->person->getDatum('middlenames'); ?></td>
	<td><?php echo $this->person->getDatum('surname'); ?></td>
	<td><?php echo $this->person->getDatum('preferred_firstname'); ?></td>
	<td><?php echo $this->person->getDatum('preferred_surname'); ?></td>
	<td align="center"><?php echo $this->person->getDatum('dob'); ?></td>
	<td align="center"><?php echo $this->person->getDatum('gender'); ?></td>
	<td><?php echo $this->person->getDatum('email'); ?></td>
</tr>