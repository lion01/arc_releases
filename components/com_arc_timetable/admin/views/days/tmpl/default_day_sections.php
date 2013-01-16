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

//// add default javascript
//JHTML::script( 'default.js', $this->addPath, true );

?>
<form action="index.php" method="post" name="adminForm" id="adminForm">
	<fieldset class="adminform">
		<legend><?php echo JText::_( 'Timetable Day Sections' ); ?></legend>
		<?php if( !empty($this->sections) ): ?>
		<table class="adminlist" cellspacing="1">
			<thead>
				<tr>
					<th class="title" width="3%"><input type="checkbox" name="toggle" value="" onclick="checkAll(<?php echo count($this->sections); ?>);" /></th>
					<th class="title" width="3%" align="center"><?php echo JText::_( 'Pattern' ); ?></th>
					<th class="title" width="5%" align="center"><?php echo JText::_( 'Day Type' ); ?></th>
					<th class="title" width="5%" align="center"><?php echo JText::_( 'Day Section' ); ?></th>
					<th class="title" width="5%" align="center"><?php echo JText::_( '-Short' ); ?></th>
					<th class="title" width="5%" align="center"><?php echo JText::_( 'Start' ); ?></th>
					<th class="title" width="5%" align="center"><?php echo JText::_( 'End' ); ?></th>
					<th class="title" width="5%" align="center"><?php echo JText::_( 'Has Teacher' ); ?></th>
					<th class="title" width="5%" align="center"><?php echo JText::_( 'Taught' ); ?></th>
					<th class="title" width="5%" align="center"><?php echo JText::_( 'Registered' ); ?></th>
					<th class="title" width="5%" align="center"><?php echo JText::_( 'Statutory' ); ?></th>
					<th class="title" width="8%" align="center"><?php echo JText::_( 'Valid From' ); ?></th>
					<th class="title" width="8%" align="center"><?php echo JText::_( 'Valid To' ); ?></th>
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
			<?php foreach( $this->sections as $this->sectionIndex=>$this->section ): ?>
				<?php echo $this->loadTemplate( 'day_section_row' ); ?>
			<?php endforeach; ?>
			</tbody>
		</table>
	</fieldset>
	<?php else: ?>
		<?php echo JText::_( 'There are no sections to display.' ); ?>
	<?php endif; ?>
	<input type="hidden" name="option" value="com_arc_timetable" />
	<input type="hidden" name="view" value="days" />
	<input type="hidden" name="task" value="displaySections" />
	<input type="hidden" name="day" value="<?php reset($this->days); echo key( $this->days ); ?>" />
	<input type="hidden" name="boxchecked" value="0" />
</form>
