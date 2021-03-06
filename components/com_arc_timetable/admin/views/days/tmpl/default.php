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
		<legend><?php echo JText::_( 'Timetable Days' ); ?></legend>
		<?php if( !empty($this->days) ): ?>
		<table class="adminlist" cellspacing="1">
			<thead>
				<tr>
					<th class="title" width="3%"><input type="checkbox" name="toggle" value="" onclick="checkAll(<?php echo count($this->days); ?>);" /></th>
					<th class="title" width="3%" align="center"><?php echo JText::_( 'Pattern' ); ?></th>
					<th class="title" width="5%" align="center"><?php echo JText::_( 'Day Type' ); ?></th>
					<th class="title" width="5%" align="center"><?php echo JText::_( 'Day Sections' ); ?></th>
					<th class="title" width="5%" align="center"><?php echo JText::_( 'Start' ); ?></th>
					<th class="title" width="5%" align="center"><?php echo JText::_( 'End' ); ?></th>
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
			<?php foreach( $this->days as $this->dayIndex=>$this->day ): ?>
				<?php echo $this->loadTemplate( 'day_row' ); ?>
			<?php endforeach; ?>
			</tbody>
		</table>
	</fieldset>
	<?php else: ?>
		<?php echo JText::_( 'There are no days to display.' ); ?>
	<?php endif; ?>
	<input type="hidden" name="option" value="com_arc_timetable" />
	<input type="hidden" name="view" value="days" />
	<input type="hidden" name="task" value="" />
	<input type="hidden" name="boxchecked" value="0" />
</form>
