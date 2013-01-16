<?php
/**
 * @package     Arc
 * @subpackage  Course
 * @copyright   Copyright (C) 2005 Punnet. All rights reserved. See COPYRIGHT_ARC.txt
 * @license     http://www.gnu.org/licenses/gpl.html GNU/GPL. See LICENSE_ARC.txt
 * Arc is free software: you can redistribute it and/or modify
 * it under the terms of version 3 of the GNU General Public License
 * as published by the Free Software Foundation.
 * The disclaimer of warranty as stated in the GPL applies to this program
 */

defined( '_JEXEC' ) or die( 'Restricted access' );


// add script to pass text strings to JS form checker
$inputStrings = array(
	'fullname'=>JText::_( 'Title' ),
	'description'=>JText::_( 'Description' ),
	'shortname'=>JText::_( 'Code' ),
	'type'=>JText::_( 'Type' ),
	'parent'=>JText::_( 'Parent' ),
	'start_date'=>JText::_( 'Start Date' )
);
$encodedInputStrings = 'check_strings = $H( Json.evaluate( \''.json_encode( $inputStrings ).'\' ) );';
$doc = &JFactory::getDocument();
$doc->addScriptDeclaration( $encodedInputStrings );
?>
<form action="index.php" method="post" name="adminForm" id="adminForm">
	<fieldset class="adminform">
		<legend><?php echo JText::_( 'Enrolment Details' ); ?></legend>
		<table class="admintable">
			<tr>
				<td align="right" class="key">
					<label for="id"><?php echo JText::_( 'ID' ); ?>:</label>
				</td>
				<td>
					<input type="text" name="id" id="id" size="9" maxlength="10" value="<?php echo $this->enrolment->getId(); ?>" readonly="readonly" />
				</td>
			</tr>
			<tr>
				<td align="right" class="key">
					<label for="group_id"><?php echo JText::_( 'Group' ); ?>:</label>
				</td>
				<td>
					<?php $g = $this->enrolment->getDatum( 'group_id' ); ?>
					<?php echo JHTML::_( 'admin_groups.grouptree', 'group_id', false, ( is_null($g) ? null : array( $g ) ) ); ?>
				</td>
			</tr>
			<tr>
				<td align="right" class="key">
					<label for="person_id"><?php echo JText::_( 'Person' ); ?>:</label>
				</td>
				<td>
					<?php echo JHTML::_( 'arc_people.people', 'person_id', $this->enrolment->getDatum( 'person_id' ), 'teacher OR staff OR pupil' ); ?>
				</td>
			</tr>
			<tr>
				<td align="right" class="key">
					<label for="role"><?php echo JText::_( 'Role' ); ?>:</label>
				</td>
				<td>
					<?php echo JHTML::_( 'admin_arc.roleList', 'role', $this->enrolment->getDatum( 'role' ) ); ?>
				</td>
			</tr>
			<tr>
				<td align="right" class="key">
					<label for="valid_from"><?php echo JText::_( 'Valid From' ); ?>:</label>
				</td>
				<td>
					<?php echo JHTML::_( 'calendar', $this->enrolment->getDatum('valid_from'), 'valid_from', 'valid_from' ); ?>
				</td>
			</tr>
			<tr>
				<td align="right" class="key">
					<label for="valid_to"><?php echo JText::_( 'Valid To' ); ?>:</label>
				</td>
				<td>
					<?php echo JHTML::_( 'calendar', $this->enrolment->getDatum('valid_to'), 'valid_to', 'valid_to' ); ?>
				</td>
			</tr>
		</table>
	</fieldset>
	<div class="clr"></div>
	<input type="hidden" name="option" value="com_arc_timetable" />
	<input type="hidden" name="view" value="enrolments" />
	<input type="hidden" name="task" value="save" />
	<input type="hidden" name="eid" value="<?php echo JRequest::getVar( 'eid' ); ?>" />
</form>