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

JHTML::_('behavior.calendar');

$yearGroupList[]	= '';
$parentSubjects[] = '';
$parentClasses[] 	= '';
$parentPseudo[] 	= '';

$parentSubjects = array_merge($parentSubjects, 	$this->parentSubjects);
$parentClasses 	= array_merge($parentClasses, 	$this->parentClasses);
$parentPseudo 	= array_merge($parentPseudo,	 	$this->parentPseudo);
$yearGroupList	= array_merge($yearGroupList, 	$this->yearGroups );

$start_date = JHTML::_( 'calendar', '', 'start_date', 'start_date' );
$end_date 	= JHTML::_( 'calendar', '', 'end_date', 	'end_date' );

$lists['parentSubjects']		= JHTML::_('select.genericlist', $parentSubjects, 'parent_subject', (empty($this->parentSubjects) ? 'disabled=disabled' : ''),	'id', 	'fullname' );
$lists['parentClasses']			= JHTML::_('select.genericlist', $parentClasses,  'parent_class', 	(empty($this->parentClasses) ? 'disabled=disabled' : ''),	'id', 	'fullname' );
$lists['parentPseudo']			= JHTML::_('select.genericlist', $parentPseudo,	  'parent_pseudo', 	(empty($this->parentPseudo) ? 'disabled=disabled' : ''),	'id', 	'fullname' );
$lists['reportable']				= JHTML::_('select.booleanlist', 'reportable', 		'',	1);
$lists['twinSubjects']		= JHTML::_('select.genericlist', $parentSubjects, 'twin_subject', (empty($this->parentSubjects) ? 'disabled=disabled' : ''),	'id', 	'fullname' );
$lists['twinClasses']			= JHTML::_('select.genericlist', $parentClasses,  'twin_class', 	(empty($this->parentClasses) ? 'disabled=disabled' : ''),	'id', 	'fullname' );
$lists['year']							= JHTML::_('select.genericlist', $yearGroupList, 	'year', 					'',	'year', 'year' );
?>

<h1>New Pseudo Course</h1>
<form action="index.php" method="post" name="adminForm">
	<table class="paramlist admintable" width="100%" cellspacing="1">
		<tr>
			<td class="paramlist_key">Fullname: </td>
			<td><input type="text" name="fullname" id="fullname" value="" style="width: 15em;" />&nbsp;
			</td>
		</tr>
		<tr>
			<td class="paramlist_key">Shortname: </td>
			<td><input type="text" name="shortname" id="shortname" value="" style="width: 8em;" /></td>
		</tr>
		<tr>
			<td class="paramlist_key">Start Date: </td>
			<td><?php echo $start_date; ?>
			</td>
		</tr>
		<tr>
			<td class="paramlist_key">End Date: </td>
			<td><?php echo $end_date; ?>
			</td>
		</tr>
		<tr>
			<td class="paramlist_key">Reportable: </td>
			<td class="paramlist_value"><?php echo $lists['reportable']; ?> </td>
		</tr>
		<tr>
			<td class="paramlist_key">Year: </td>
			<td class="paramlist_value"><?php echo $lists['year']; ?> </td>
		</tr>
		<tr>
			<td class="paramlist_key">Parent</td>
			<td>
				<table>
					<tr>
						<td>Subjects</td>
						<td>Classes</td>
						<td>Pseudo Courses</td>
					</tr>
					<tr>
						<td class="paramlist_value"><?php echo $lists['parentSubjects']; ?> </td>
						<td class="paramlist_value"><?php echo $lists['parentClasses']; ?> </td>
						<td class="paramlist_value"><?php echo $lists['parentPseudo']; ?> </td>
					</tr>
				</table>
			</td>
		</tr>
		<tr>
			<td class="paramlist_key">Twin</td>
			<td>
				<table>
					<tr>
						<td>Subjects</td>
						<td>Classes</td>
					</tr>
					<tr>
						<td class="paramlist_value"><?php echo $lists['twinSubjects']; ?> </td>
						<td class="paramlist_value"><?php echo $lists['twinClasses']; ?> </td>
					</tr>
				</table>
			</td>
		</tr>
	</table>
<input type="hidden" name="option" value="com_arc_report" />
<input type="hidden" name="view" value="pseudo" />
<input type="hidden" name="task" value="" />
</form>
