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

if ($this->registers) { ?>
	<form action="<?php echo $this->link; ?>" method="post" name="register">
	<table>
	<tr>
		<td>Select Class: </td>
	</tr>
	<tr>
		<td><?php
			//var_dump_pre($this->registers);
			echo JHTML::_('select.genericList', $this->registers, 'composite_id', '', 'composite_id', 'display_name'); ?></td>
	</tr>
	<?php  ?>
	<tr><td><input type="submit" name="task" value="Search" /></td></tr>
	</table>
	</form>
<?php }
else { ?>
<h5>No classes that meet that criteria have been found, please try again, by changing one or more of the options</h5>
<?php } ?>
<hr />