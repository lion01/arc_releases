<?php
/**
 * @package     Arc
 * @subpackage  Report
 * @copyright   Copyright (C) 2005 Punnet. All rights reserved. See COPYRIGHT_ARC.txt
 * @license     http://www.gnu.org/licenses/gpl.html GNU/GPL. See LICENSE_ARC.txt
 * Arc is free software: you can redistribute it and/or modify
 * it under the terms of version 3 of the GNU General Public License
 * as published by the Free Software Foundation.
 * The disclaimer of warranty as stated in the GPL applies to this program
 */

defined( '_JEXEC' ) or die( 'Restricted access' );

$enrolSubjects[] = '';
$enrolClasses[] = '';
$enrolSubjects = array_merge($enrolSubjects, $this->enrolSubjects);
$enrolClasses = array_merge($enrolClasses, $this->enrolClasses);
var_dump_pre($this->course);
$lists['enrolSubjects']		= JHTML::_('select.genericlist', $enrolSubjects, 'enrolment_subject', '',		'id', 'fullname', 		$this->course->enrolment_range );
$lists['enrolClasses']		= JHTML::_('select.genericlist', $enrolClasses,  'enrolment_class', 	'',		'id', 'fullname', 		$this->course->enrolment_range );
$lists['parents']					= JHTML::_('select.genericlist', $parents, 			 'parent', 						'',		'id', 'fullname', 		$this->course->parent );
?>
<h1>Edit Pseudo Course</h1>
<form action="index.php" method="post" name="adminForm">
	<table class="paramlist admintable" width="100%" cellspacing="1">
		<tr>
			<td class="paramlist_key">Id: </td>
			<td class="paramlist_value"><input type="text" id="_id" name="_id" value="<?php echo $this->course->id; ?>" disabled/></td>
		</tr>
		<tr>
			<td class="paramlist_key">Fullname: </td>
			<td><input type="text" name="fullname" id="fullname" value="<?php echo $this->course->fullname; ?>" style="width: 15em;" />&nbsp;
			</td>
		</tr>
		<tr>
			<td class="paramlist_key">Shortname: </td>
			<td><input type="text" name="shortname" id="shortname" value="<?php echo $this->course->shortname; ?>" style="width: 8em;" />&nbsp;
			</td>
		</tr>
		<tr>
			<td class="paramlist_key">Parent: </td><td class="paramlist_value"><?php echo $lists['parents']; ?> </td>
		</tr>
		<tr>
			<td class="paramlist_key">Enrolment Range</td>
			<td>
				<table>
					<tr>
						<td>Subjects</td>
						<td>Classes</td>
					</tr>
					<tr>
						<td class="paramlist_value"><?php echo $lists['enrolSubjects']; ?> </td>
						<td class="paramlist_value"><?php echo $lists['enrolClasses']; ?> </td>
					</tr>
				</table>
			</td>
		</tr>
	</table>
<input type="hidden" name="option" value="com_arc_report" />
<input type="hidden" name="view" value="pseudo" />
<input type="hidden" name="task" value="" />
<input type="hidden" id="id" name="id" value="<?php echo $this->course->id; ?>" />
</form>
