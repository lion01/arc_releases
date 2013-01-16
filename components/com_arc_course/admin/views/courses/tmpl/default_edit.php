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

	// add javascript
	JHTML::script( 'edit.js', $this->addPath, true );
	
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
	<fieldset class="adminform_thinfieldset">
		<legend><?php echo JText::_( 'Course Details' ); ?></legend>
		<table class="admintable">
			<tr>
				<td align="right" class="key">
					<label for="id"><?php echo JText::_( 'ID' ); ?>:</label>
				</td>
				<td>
					<input type="text" name="id" id="id" size="9" maxlength="10" value="<?php echo $this->course->getData( 'id' ); ?>" readonly="readonly" />
				</td>
			</tr>
			<tr>
				<td align="right" class="key">
					<label for="fullname"><?php echo JText::_( 'Title' ); ?>:</label>
				</td>
				<td>
					<input type="text" name="fullname" id="fullname" size="32" maxlength="100" value="<?php echo $this->course->getData( 'fullname' ); ?>" />
				</td>
			</tr>
			<tr>
				<td align="right" class="key">
					<label for="description"><?php echo JText::_( 'Description' ); ?>:</label>
				</td>
				<td>
					<input type="text" name="description" id="description" size="32" maxlength="250" value="<?php echo $this->course->getData( 'description' ); ?>" />
				</td>
			</tr>
			<tr>
				<td align="right" class="key">
					<label for="shortname"><?php echo JText::_( 'Code' ); ?>:</label>
				</td>
				<td>
					<input type="text" name="shortname" id="shortname" size="32" maxlength="50" value="<?php echo $this->course->getData( 'shortname' ); ?>" />
				</td>
			</tr>
			<tr>
				<td align="right" class="key">
					<label for="year"><?php echo JText::_( 'Year' ); ?>:</label>
				</td>
				<td>
					<?php echo JHTML::_( 'arc_course.year', 'year', $this->course->getData('year') ); ?>
				</td>
			</tr>
			<tr>
				<td align="right" class="key">
					<label for="type"><?php echo JText::_( 'Type' ); ?>:</label>
				</td>
				<td>
					<?php echo JHTML::_( 'arc_course.filterType', 'type', '', $this->course->getData('type') ); ?>
				</td>
			</tr>
			<tr>
				<td align="right" class="key">
					<label for="parent"><?php echo JText::_( 'Parent' ); ?>:</label>
				</td>
				<td>
					<?php echo JHTML::_( 'admin_groups.grouptree', 'parent', false, ($this->course->getData('parent') ? array($this->course->getData('parent')) : '') ); ?>
				</td>
			</tr>
			<tr>
				<td align="right" class="key">
					<label for="start_date"><?php echo JText::_( 'Start Date' ); ?>:</label>
				</td>
				<td>
					<?php echo JHTML::_( 'calendar', $this->course->getData('start_date'), 'start_date', 'start_date' ); ?>
				</td>
			</tr>
			<tr>
				<td align="right" class="key">
					<label for="end_date"><?php echo JText::_( 'End Date' ); ?>:</label>
				</td>
				<td>
					<?php echo JHTML::_( 'calendar', $this->course->getData('end_date'), 'end_date', 'end_date' ); ?>
				</td>
			</tr>
			<tr>
				<td align="right" class="key">
					<label for="sortorder"><?php echo JText::_( 'Sort Order' ); ?>:</label>
				</td>
				<td>
					<input type="text" name="sortorder" id="sortorder" size="9" maxlength="5" value="<?php echo $this->course->getData( 'sortorder' ); ?>" />
				</td>
			</tr>
			<tr>
				<td align="right" class="key">
					<label for="reportable"><?php echo JText::_( 'Reportable' ); ?>:</label>
				</td>
				<td>
					<input type="checkbox" name="reportable" id="reportable" <?php echo ( $this->course->getData( 'reportable' ) ? 'checked="checked"' : '' ); ?> />
				</td>
			</tr>
			<tr>
				<td align="right" class="key">
					<label for="ext_course_id_2"><?php echo JText::_( 'VLE ID' ); ?>:</label>
				</td>
				<td>
					<input type="text" name="ext_course_id_2" id="ext_course_id_2" size="9" maxlength="10" value="<?php echo $this->course->getData( 'ext_course_id_2' ); ?>" />
				</td>
			</tr>
			<tr>
				<td align="right" class="key">
					<label for="ext_course_id"><?php echo JText::_( 'External ID' ); ?>:</label>
				</td>
				<td>
					<input type="text" name="ext_course_id" id="ext_course_id" size="9" maxlength="10" value="<?php echo $this->course->getData( 'ext_course_id' ); ?>" readonly="readonly" />
				</td>
			</tr>
			<tr>
				<td align="right" class="key">
					<label for="ext_type"><?php echo JText::_( 'External Type' ); ?>:</label>
				</td>
				<td>
					<input type="text" name="ext_type" id="ext_type" size="9" maxlength="10" value="<?php echo $this->course->getData( 'ext_type' ); ?>" readonly="readonly" />
				</td>
			</tr>
		</table>
	</fieldset>
	<input type="hidden" name="option" value="com_arc_course" />
	<input type="hidden" name="view" value="courses" />
	<input type="hidden" name="task" value="save" />
</form>