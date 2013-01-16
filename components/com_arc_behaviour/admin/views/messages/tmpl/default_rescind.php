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

// add default css
JHTML::stylesheet( 'default.css', $this->addPath );
?>
<form action="index.php" method="post" name="adminForm" id="adminForm">
	<fieldset class="adminform_thinfieldset">
		<legend><?php echo JText::_( 'Behaviour message(s) to be rescinded' ); ?></legend>
		<table class="adminlist" cellspacing="1">
			<thead>
				<tr>
					<th class="title" width="3%" align="center"><?php echo JText::_( 'Type' ); ?></th>
					<th class="title" width="5%" align="center"><?php echo JText::_( 'Created' ); ?></th>
					<th class="title" width="5%" align="center"><?php echo JText::_( 'Applies On' ); ?></th>
					<th class="title" width="8%" align="center"><?php echo JText::_( 'Sender' ); ?></th>
					<th class="title" width="8%" align="center"><?php echo JText::_( 'Pupil' ); ?></th>
					<th class="title" width="3%" align="center"><?php echo JText::_( 'Class' ); ?></th>
					<th class="title" width="3%" align="center"><?php echo JText::_( 'Room' ); ?></th>
					<th class="title" width="9%" align="center"><?php echo JText::_( 'Incident' ); ?></th>
					<th class="title" width="10%" align="center"><?php echo JText::_( 'Incident Details' ); ?></th>
					<th class="title" width="26%" align="center"><?php echo JText::_( 'Outline / Comment' ); ?></th>
					<th class="title" width="10%" align="center"><?php echo JText::_( 'Action' ); ?></th>
					<th class="title" width="10%" align="center"><?php echo JText::_( 'Action Details' ); ?></th>
				</tr>
			</thead>
			<tfoot>
				<tr>
					<td colspan="12">&nbsp;</td>
				</tr>
			</tfoot>
			<tbody>
			<?php echo $this->loadTemplate( 'thread_row_rescind' ); ?>
			</tbody>
		</table>
	</fieldset>
	<fieldset class="adminform_thinfieldset">
		<legend><?php echo JText::_( 'Optional rescind message' ); ?></legend>
		<textarea rows="3" cols="100" name="rescind_message" >Rescinded messages in this thread. Please contact support if you need to see them.</textarea>
	</fieldset>
	<input type="hidden" name="option" value="com_arc_behaviour" />
	<input type="hidden" name="view" value="messages" />
	<input type="hidden" name="task" value="rescind" />
	<input type="hidden" name="rescind_thr_id" value="<?php echo $this->thread->getId(); ?>" />
	<input type="hidden" name="rescind_msg_ids" value="<?php echo htmlspecialchars( serialize($this->rescindMsgIds) ); ?>" />
</form>