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

//JHTML::script( 'new_template.js', JURI::root().'administrator'.DS.'components'.DS.'com_arc_people'.DS.'views'.DS.'profiles'.DS.'tmpl'.DS, true );
?>
<form action="index.php" method="post" name="adminForm" id="adminForm">
	<fieldset class="adminform_thinfieldset" style="vertical-align: top;">
		<legend><?php echo JText::_( 'Year Group Management Tasks' );?></legend>
		<table width="100%" class="admintable">
			<tr>
				<td class="key" rowspan="2">
					<span class="hasTip" title="Select Task::Select a profile year management task"><?php echo JText::_( 'Select Task' ); ?>:</span>
				</td>
				<td class="value">
						<input type="radio" id="match" name="apply_task" value="match" />
						<label for="match" class="hasTip" title="Match Tutor Group Year::Update the current profile year to match the current tutor group year"><?php echo JText::_( 'Match Tutor Group Year' ); ?></label><br />
				</td>
			</tr>
		</table>
	</fieldset>
	<input type="hidden" name="option" value="com_arc_people" />
	<input type="hidden" name="view" value="profiles" />
	<input type="hidden" name="task" value="" />
	<input type="hidden" name="select_task" value="year_groups" />
</form>