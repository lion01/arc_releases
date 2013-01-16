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
<form action="index.php" method="post" name="adminForm">
	<table class="paramlist admintable" width="100%" cellspacing="1">
		<tr>
			<td class="paramlist_key">Subject: </td><td class="paramlist_value"><?php echo JHTML::_( 'select.genericlist', $this->courses, 'subjects[]', 'multiple="multiple"', 'id', 'fullname', '' ); ?> </td>
		</tr>
		<tr>
			<td class="paramlist_key">Source Cycle: </td><td class="paramlist_value"><?php echo JHTML::_( 'select.genericlist', $this->items, 'sourceCycle', '', 'id', 'displayName', $this->params->get('current_cycle') ); ?> </td>
		</tr>
		<tr>
			<td class="paramlist_key">Target Cycle: </td><td class="paramlist_value"><?php echo JHTML::_( 'select.genericlist', $this->items, 'targetCycle', '', 'id', 'displayName', '' ); ?> </td>
		</tr>
	</table>
	<input type="hidden" name="option" value="com_arc_report" />
	<input type="hidden" name="view" value="statements" />
	<input type="hidden" name="task" value="" />
</form>