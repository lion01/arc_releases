<?php
/**
 * @package     Arc
 * @subpackage  Timetable
 * @copyright   Copyright (C) 2005 Punnet. All rights reserved. See COPYRIGHT_ARC.txt
 * @license     http://www.gnu.org/licenses/gpl.html GNU/GPL. See LICENSE_ARC.txt
 * Arc is free software: you can redistribute it and/or modify
 * it under the terms of version 3 of the GNU General Public License
 * as published by the Free Software Foundation.
 * The disclaimer of warranty as stated in the GPL applies to this program
 */

defined( '_JEXEC' ) or die( 'Restricted access' );

// add default javascript
JHTML::script( 'default.js', $this->addPath, true );
// add default styling
JHTML::stylesheet( 'default.css', $this->addPath );

?>
<form action="index.php" method="post" name="adminForm" id="adminForm">
	<fieldset class="adminform">
		<legend><?php echo JText::_( 'Timetable Enrolments' ); ?></legend>
		<table >
			<tr>
				<td width="25%">
					<button id="admin_form_search_button"><?php echo JText::_( 'Search' ); ?></button>
					<button id="admin_form_reset_button"><?php echo JText::_( 'Reset' ); ?></button>
				</td>
				<td width="25%">
					<label for="search_group">Group</label><br />
					<input type="text" name="search_group" id="search_group" value="<?php echo $this->searchGroup; ?>" class="text_area" />
				</td>
				<td width="25%">
					<label for="search_person">Person</label><br />
					<?php echo JHTML::_( 'arc_people.people', 'search_person', $this->searchPerson, 'teacher OR staff OR pupil' ); ?>
				</td>
				<td width="25%">
					<label for="search_valid">Valid on</label><br />
					<?php echo JHTML::_('calendar', $this->searchValid, 'search_valid', 'search_valid'); ?>
				</td>
			</tr>
		</table>
		
		<?php if( !empty($this->enrolments) ): ?>
		<table class="adminlist" cellspacing="1">
			<thead>
				<tr>
					<th class="title" width="5%"><input type="checkbox" name="toggle" value="" onclick="checkAll(<?php echo count($this->enrolments); ?>);" /></th>
					<th class="title" width="10%" align="center"><?php echo JText::_( 'Group' ); ?></th>
					<th class="title" width="25%" align="center"><?php echo JText::_( 'Person' ); ?></th>
					<th class="title" width="30%" align="center"><?php echo JText::_( 'Role' ); ?></th>
					<th class="title" width="15%" align="center"><?php echo JText::_( 'Valid From' ); ?></th>
					<th class="title" width="15%" align="center"><?php echo JText::_( 'Valid To' ); ?></th>
				</tr>
			</thead>
			<tfoot>
				<tr>
					<td colspan="13">
						<?php echo $this->pagination->getListFooter(); ?>
					</td>
				</tr>
			</tfoot>
			<tbody>
			<?php foreach( $this->enrolments as $this->enrolIndex=>$this->enrolment ): ?>
				<?php echo $this->loadTemplate( 'enrolment_row' ); ?>
			<?php endforeach; ?>
			</tbody>
		</table>
	</fieldset>
	<?php else: ?>
		<?php echo JText::_( 'There are no enrolments to display.' ); ?>
	<?php endif; ?>
	<input type="hidden" name="option" value="com_arc_timetable" />
	<input type="hidden" name="view" value="enrolments" />
	<input type="hidden" name="task" value="" />
	<input type="hidden" name="boxchecked" value="0" />
</form>
