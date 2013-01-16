<?php
/**
 * @package     Arc
 * @subpackage  Behaviour
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
?>
<form action="index.php" method="post" name="adminForm" id="adminForm">
	<table>
		<tr>
			<td width="100%">
				<?php echo JText::_( 'Incident or Outline/Comment or Action' ); ?>:
				<input type="text" name="search" id="search" value="<?php echo $this->search; ?>" class="text_area" />
				<button id="admin_form_search_button"><?php echo JText::_( 'Search' ); ?></button>
				<button id="admin_form_reset_button"><?php echo JText::_( 'Reset' ); ?></button>
			</td>
			<td nowrap="nowrap">
			<?php echo JText::_( 'Sender' ); ?>:
				<?php echo JHTML::_( 'arc_people.people', 'sender', $this->sender, 'teacher', false, array(), false ); ?>
			</td>
			<td nowrap="nowrap">
			<?php echo JText::_( 'Pupil' ); ?>:
				<?php echo JHTML::_( 'arc_people.people', 'pupil', $this->pupil, 'pupil', false, array(), false ); ?>
			</td>
		</tr>
	</table>
	
	<?php if( $this->threadCount ): ?>
	<table class="adminlist" cellspacing="1">
		<thead>
			<tr>
				<th class="title" width="3%"><input type="checkbox" name="toggle" value="" onclick="checkAll(<?php echo $this->messageCount; ?>);" /></th>
				<th class="title" width="3%" align="center"><?php echo JText::_( 'Type' ); ?></th>
				<th class="title" width="5%" align="center"><?php echo JText::_( 'Created' ); ?></th>
				<th class="title" width="5%" align="center"><?php echo JText::_( 'Applies On' ); ?></th>
				<th class="title" width="8%" align="center"><?php echo JText::_( 'Sender' ); ?></th>
				<th class="title" width="8%" align="center"><?php echo JText::_( 'Pupil' ); ?></th>
				<th class="title" width="3%" align="center"><?php echo JText::_( 'Class' ); ?></th>
				<th class="title" width="3%" align="center"><?php echo JText::_( 'Room' ); ?></th>
				<th class="title" width="9%" align="center"><?php echo JText::_( 'Incident' ); ?></th>
				<th class="title" width="10%" align="center"><?php echo JText::_( 'Incident Details' ); ?></th>
				<th class="title" width="23%" align="center"><?php echo JText::_( 'Outline / Comment' ); ?></th>
				<th class="title" width="10%" align="center"><?php echo JText::_( 'Action' ); ?></th>
				<th class="title" width="10%" align="center"><?php echo JText::_( 'Action Details' ); ?></th>
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
		<?php foreach( $this->threads as $this->threadIndex=>$this->thread ): ?>
			<?php echo $this->loadTemplate( 'thread_rows' ); ?>
		<?php endforeach; ?>
		</tbody>
	</table>
	<?php else: ?>
		<?php echo JText::_( 'There are no messages to display.' ); ?>
	<?php endif; ?>
	<input type="hidden" name="option" value="com_arc_behaviour" />
	<input type="hidden" name="view" value="messages" />
	<input type="hidden" name="task" value="" />
	<input type="hidden" name="boxchecked" value="0" />
</form>