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
<form action="index.php" method="post" name="adminForm" id="adminForm">
	<fieldset class="adminform_thinfieldset">
		<legend><?php echo JText::_( 'Profile Management' );?></legend>
		<table width="100%" class="admintable">
			<tr>
				<td class="key" rowspan="2">
					<span class="hasTip" title="Select Task::Select a profile management task"><?php echo JText::_( 'Select Task' ); ?>:</span>
				</td>
				<td class="value">
						<input type="radio" id="template" name="select_task" value="template" />
						<label for="template" class="hasTip" title="Manage Profile Templates::Add, remove or edit the profile templates"><?php echo JText::_( 'Manage Profile Templates' ); ?></label><br />
				</td>
			</tr>
			<tr>
				<td class="value">
						<input type="radio" id="year_groups" name="select_task" value="year_groups" />
						<label for="year" class="hasTip" title="Update Year Groups::Manage year group advancement"><?php echo JText::_( 'Update Year Groups' ); ?></label><br />
				</td>
			</tr>
		</table>
	</fieldset>
	<input type="hidden" name="option" value="com_arc_people" />
	<input type="hidden" name="view" value="profiles" />
	<input type="hidden" name="task" value="" />
</form>