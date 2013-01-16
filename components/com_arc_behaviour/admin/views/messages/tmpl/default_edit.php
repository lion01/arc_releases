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

// add javascript
JHTML::script( 'edit.js', $this->addPath, true );

// add script to pass text strings to JS form checker
$inputStrings = array(
	'msg_data[student_id]'=>JText::_( 'Student' ),
	'msg_data[incident]'=>JText::_( 'Incident' ),
	'msg_author'=>JText::_( 'Author' ),
	'msg_created'=>JText::_( 'Created' ),
	'msg_applies'=>JText::_( 'Applies On' )
);
if( $this->first ) {
	$inputStrings['msg_data[outline]'] = JText::_( 'Outline' );
}
else {
	$inputStrings['msg_data[comment]'] = JText::_( 'Comment' );
}
$encodedInputStrings = 'check_strings = $H( Json.evaluate( \''.json_encode( $inputStrings ).'\' ) );';
$doc = &JFactory::getDocument();
$doc->addScriptDeclaration( $encodedInputStrings );

// add default css
JHTML::stylesheet( 'default.css', $this->addPath );
?>
<form action="index.php" method="post" name="adminForm" id="adminForm">
	<fieldset class="adminform_thinfieldset">
		<legend><?php echo JText::_( 'Behaviour Message Details' ); ?></legend>
		<table class="admintable">
			<tr>
				<td align="right" class="key">
					<label for="msg_id"><?php echo JText::_( 'ID' ); ?>:</label>
				</td>
				<td>
					<input type="text" name="msg_id" id="msg_id" size="9" maxlength="10" value="<?php echo $this->message->getId(); ?>" readonly="readonly" />
				</td>
			</tr>
			<tr>
				<td align="right" class="key">
					<label for="msg_data[student_id]"><?php echo JText::_( 'Student' ); ?>:</label>
				</td>
				<td>
					<?php echo JHTML::_( 'arc_people.people', 'msg_data[student_id]', $this->message->getDatum('student_id'), 'pupilof.'.$this->message->getDatum('group_id').' OR pupil', true, array(), false ); ?>
				</td>
			</tr>
			<tr>
				<td align="right" class="key">
					<label for="msg_data[group_id]"><?php echo JText::_( 'Group' ); ?>:</label>
				</td>
				<td>
					<?php echo JHTML::_( 'arc_timetable.classes', 'msg_data[group_id]', $this->message->getDatum('group_id') ); ?>
				</td>
			</tr>
			<tr>
				<td align="right" class="key">
					<label for="msg_data[room_id]"><?php echo JText::_( 'Room' ); ?>:</label>
				</td>
				<td>
					<input type="text" name="msg_data[room_id]" id="msg_data[room_id]" size="32" maxlength="100" value="<?php echo $this->message->getDatum('room_id'); ?>" />
				</td>
			</tr>
			<tr>
				<td align="right" class="key">
					<label for="msg_data[incident]"><?php echo JText::_( 'Incident' ); ?>:</label>
				</td>
				<td>
					<?php echo JHTML::_('arc_behaviour.type', 'msg_inc_type', 'msg_data[incident]', 'msg_data[incident_text]', true, true, $this->incType->getId(), $this->inc->getId(), $this->message->getDatum('incident_text')) ?>
				</td>
			</tr>
			<tr>
				<td align="right" class="key">
					<label for="msg_data[more_action]"><?php echo JText::_( 'More Action Required?' ); ?>:</label>
				</td>
				<td>
					<input type="checkbox" name="msg_data[more_action]" <?php echo ( $this->message->getDatum('more_action') ? 'checked="checked"' : '' ); ?>/>
				</td>
			</tr>
			<tr>
				<td align="right" class="key">
					<label for="msg_data[callout]"><?php echo JText::_( 'Callout Required' ); ?>:</label>
				</td>
				<td>
					<input type="checkbox" name="msg_data[callout]" <?php echo ( $this->message->getDatum('callout') ? 'checked="checked"' : '' ); ?>/>
				</td>
			</tr>
			<?php if( $this->first) : ?>
			<tr>
				<td align="right" class="key">
					<label for="msg_data[outline]"><?php echo JText::_( 'Outline' ); ?>:</label>
				</td>
				<td>
					<textarea name="msg_data[outline]"><?php echo $this->message->getDatum('outline'); ?></textarea>
				</td>
			</tr>
			<?php else : ?>
			<tr>
				<td align="right" class="key">
					<label for="msg_data[comment]"><?php echo JText::_( 'Comment' ); ?>:</label>
				</td>
				<td>
					<textarea name="msg_data[comment]"><?php echo $this->message->getDatum('comment'); ?></textarea>
				</td>
			</tr>
			<?php endif; ?>
			<tr>
				<td align="right" class="key">
					<label for="msg_data[action]"><?php echo JText::_( 'Action' ); ?>:</label>
				</td>
				<td>
					<?php echo JHTML::_( 'arc_behaviour.action', 'msg_data[action]', 'msg_data[action_text]', $this->incType->getId(), $this->message->getDatum('action'), $this->message->getDatum('action_text') ); ?><br />
				</td>
			</tr>
			<tr>
				<td align="right" class="key">
					<label for="msg_author"><?php echo JText::_( 'Author' ); ?>:</label>
				</td>
				<td>
					<?php echo JHTML::_( 'arc_people.people', 'msg_author', $this->message->getAuthor(), 'teacher OR staff', true, array(), false ); ?>
				</td>
			</tr>
			<tr>
				<td align="right" class="key">
					<label for="msg_created"><?php echo JText::_( 'Created' ); ?>:</label>
				</td>
				<td>
					<input type="text" name="msg_created" id="msg_created" size="32" maxlength="100" value="<?php echo $this->message->getCreated(); ?>" readonly="readonly" />
				</td>
			</tr>
			<tr>
				<td align="right" class="key">
					<label for="msg_applies"><?php echo JText::_( 'Applies On' ); ?>:</label>
				</td>
				<td>
					<input type="text" name="msg_applies" id="msg_applies" size="32" maxlength="100" value="<?php echo $this->message->getDate() ?>" />
				</td>
			</tr>
		</table>
	</fieldset>
	<input type="hidden" name="msg_handler" value="behaviour" />
	<input type="hidden" name="option" value="com_arc_behaviour" />
	<input type="hidden" name="view" value="messages" />
	<input type="hidden" name="task" value="save" />
</form>