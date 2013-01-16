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

// add default CSS
$this->addPath = JURI::base().'components'.DS.'com_arc_attendance'.DS.'views'.DS.'truants'.DS.'tmpl'.DS;
JHTML::stylesheet( 'att_truants.css', $this->addPath );
?>
<div class="truants_inter_div">
	<h3>Edit Truants List</h3>
	<form method="post">
		<table>
			<tr>
				<td>Current truants:</td>
				<td>&nbsp;</td>
				<td>Potential truants:</td>
			</tr>
			<tr>
				<td>
					<?php echo JHTML::_( 'select.genericList', $this->truants, 'truants[]', 'multiple="multiple"', 'id', 'displayname' ); ?>
				</td>
				<td class="truants_buttons">
					<input type="submit" name="task" value="<< Add" /><br />
					<input type="submit" name="task" value="Remove >>" />
				</td>
				<td>
					<?php echo JHTML::_( 'select.genericList', $this->nonTruants, 'non_truants[]', 'multiple="multiple"', 'id', 'displayname' ); ?>
				</td>
			</tr>
		</table>
	</form>
</div>