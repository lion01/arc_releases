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
		<legend><?php echo JText::_( 'Joomla! User Management' );?></legend>
		<table width="100%" class="admintable">
			<tr>
				<td class="key" rowspan="3">
					<span class="hasTip" title="Select Task::Select a Joomla! user management task"><?php echo JText::_( 'Select Task' ); ?>:</span>
				</td>
				<td class="value">
					<input type="radio" id="create" name="select_task" value="create" />
					<label for="create" class="hasTip" title="Create Joomla! Users::Create Joomla! users from Arc people"><?php echo JText::_( 'Create Joomla! Users' ); ?> (<?php echo $this->get( 'PotentialJosUsers' ); ?>)</label>
				</td>
			</tr>
			<tr>
				<td class="value">
					<input type="radio" id="pword" name="select_task" value="pword" />
					<label for="pword" class="hasTip" title="Upload Joomla! Passwords::Upload a list of Joomla! passwords"><?php echo JText::_( 'Upload Joomla! Passwords' ); ?></label>
				</td>
			</tr>
			<tr>
				<td class="value">
					<input type="radio" id="format" name="select_task" value="format" />
					<label for="format" class="hasTip" title="Format Joomla! Variables::Format the required Joomla! user variables"><?php echo JText::_( 'Format Joomla! Variables' ); ?></label>
				</td>
			</tr>
		</table>
	</fieldset>
	<input type="hidden" name="option" value="com_arc_people" />
	<input type="hidden" name="view" value="josuser" />
	<input type="hidden" name="task" value="" />
</form>