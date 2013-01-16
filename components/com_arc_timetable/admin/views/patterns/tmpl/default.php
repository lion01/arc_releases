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
		<legend><?php echo JText::_( 'Timetable Patterns' ); ?></legend>
		<?php if( !empty($this->patterns) ): ?>
		<table class="adminlist" cellspacing="1">
			<thead>
				<tr>
					<th class="title" width="3%"><input type="checkbox" name="toggle" value="" onclick="checkAll(<?php echo count($this->patterns); ?>);" /></th>
					<th class="title" width="3%" align="center"><?php echo JText::_( 'Name' ); ?></th>
					<th class="title" width="5%" align="center"><?php echo JText::_( 'Format' ); ?></th>
					<th class="title" width="5%" align="center"><?php echo JText::_( 'Start Day' ); ?></th>
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
			<?php foreach( $this->patterns as $this->patternIndex=>$this->pattern ): ?>
				<?php echo $this->loadTemplate( 'pattern_row' ); ?>
			<?php endforeach; ?>
			</tbody>
		</table>
	</fieldset>
	<?php else: ?>
		<?php echo JText::_( 'There are no patterns to display.' ); ?>
	<?php endif; ?>
	<input type="hidden" name="option" value="com_arc_timetable" />
	<input type="hidden" name="view" value="patterns" />
	<input type="hidden" name="task" value="" />
	<input type="hidden" name="boxchecked" value="0" />
</form>
