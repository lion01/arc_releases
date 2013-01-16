<?php
/**
 * @package     Arc
 * @subpackage  Planner
 * @copyright   Copyright (C) 2005 Punnet. All rights reserved. See COPYRIGHT_ARC.txt
 * @license     http://www.gnu.org/licenses/gpl.html GNU/GPL. See LICENSE_ARC.txt
 * Arc is free software: you can redistribute it and/or modify
 * it under the terms of version 3 of the GNU General Public License
 * as published by the Free Software Foundation.
 * The disclaimer of warranty as stated in the GPL applies to this program
 */

defined( '_JEXEC' ) or die( 'Restricted access' );

JHTML::script('default.js', 'components'.DS.'com_arc_planner'.DS.'views'.DS.'admin'.DS.'tmpl'.DS, true);
JHTML::script('variable.php_serializer.js', 'administrator'.DS.'components'.DS.'com_arc_core'.DS.'libraries'.DS.'js'.DS );

// add default CSS
$this->cssPath = JURI::base().'components'.DS.'com_arc_planner'.DS.'views'.DS.'admin'.DS.'tmpl'.DS;
$imgPath = '.'.DS.'components'.DS.'com_arc_planner'.DS.'images'.DS;

JHTML::stylesheet( 'default.css', $this->cssPath );
?>
<h3>Planner Admin</h3>

<div style="margin-top:15px;" overflow:auto;>
	<select id="whichPM">
		<option value="plr">PLR</option>
		<option value="pm">Performance Management</option>
	</select>
</div>

<div style="width:700px; overflow:auto;">

	<div id="info1" style="float:left; width:13em; margin-top:1em;">	
	</div>

	<div id="confirm1" style="float:left; width:4em; margin:1.5em; margin-top:1em;">
	</div>

	<div id="info2" style="float:left; width:13em; margin-top:1em;">
	</div>

	<div id="confirm2" style="float:left; width:4em; margin:1.5em; margin-top:1em; margin-right:2em;">
	</div>

	<div id="info3" style="float:left; width:13em; margin-top:1em;">
	</div>

</div>

<div class="top_admin_row">

	<div class="admin_field">
		<label for="staff">Mentors:</label><br />
		<?php echo JHTML::_( 'arc_people.plannerPeople', 'mentors', 'teachingStaff', null, null, true ); ?>
	</div>

	<div class="admin_field">
		<div style="top:50px; position:relative; height:22px; width:58px;">
			<?php echo '<img style="left:7px; position:absolute;" src="'.$imgPath.'addTop.png" id="assign" title="Open this task in a new window" />'; ?>
		</div>
	</div>

	<div class="admin_field">
		<label for="pupils">Mentees:</label><br />
		<div id="menteeDiv">
			<?php echo JHTML::_( 'arc_people.plannerPeople', 'mentees', 'pupils', null, null, true ); ?>
		</div>
	</div>

	<div class="admin_field">
		<label for="allTasksDiv">All Tasks:</label><br />
		<div id="allTasksDiv">	
			<?php echo JHTML::_( 'arc_planner.tasks', 'allTasks', array(), null, true ); ?>
		</div>
	</div>
</div>

<div class="admin_row">

	<div class="admin_field">
		<label for="pupils">Assigned mentees:</label><br />
		<div id="menteesDiv">
			<?php echo JHTML::_( 'arc_people.plannerPeople', 'assignedMentees', 'assignedMentees', null, null, true ); ?>
		</div>
	</div>

	<div class="admin_field">
		<div style="top:50px; position:relative; height:22px; width:24px;">
			<?php echo '<img src="'.$imgPath.'remove.png " style="visibility:hidden;" id="removeMentees" title="Open this task in a new window" />'; ?>
		</div>
	</div>
	<div class="admin_field">
		<div style="top:50px; position:relative; height:22px; width:24px;" >
			<?php echo '<img src="'.$imgPath.'removeL.png " style="visibility:hidden;" title="Open this task in a new window" id="removeMentors" />'; ?>
		</div>
	</div>

	<div class="admin_field">
		<label for="staff">Assigned Mentors:</label><br />
		<div id="assignedMentorsDiv">
			<?php echo JHTML::_( 'arc_people.plannerPeople', 'assignedMentors', 'assignedMentors', null, null, true ); ?>
		</div>
	</div>
</div>

<div class="admin_row">
	<div class="admin_field">
		<label for="tasks1">Assigned tasks:</label><br />
		<div id="tasksDiv">
			<?php echo JHTML::_( 'arc_planner.assignedTasks', 'tasks1', array(), null, true ); ?>
		</div>
	</div>
	
	<div class="admin_field">
		<div style="top:50px; position:relative; height:22px; width:24px;">
			<?php echo '<img src="'.$imgPath.'remove.png" id="removeTasksL" style="visibility:hidden;" title="Open this task in a new window" />'; ?>
		</div>
	</div>
	<div class="admin_field">
		<div style="top:50px; position:relative; height:22px; width:24px;">
			<?php echo '<img src="'.$imgPath.'removeL.png" id="removeTasksR" style="visibility:hidden;" title="Open this task in a new window" />'; ?>
		</div>
	</div>
	
	<div class="admin_field">
		<label for="tasks2">Assigned tasks:</label><br />
		<div id="tasksDiv2">
			<?php echo JHTML::_( 'arc_planner.assignedTasks', 'tasks2', array(), null, true ); ?>
		</div>
	</div>
	<div class="admin_field">
		<div style="top:50px; position:relative; height:22px; width:24px;">
			<?php echo '<img src="'.$imgPath.'removeL.png" id="add_tasks" style="visibility:hidden;" title="Open this task in a new window" />'; ?>
		</div>
	</div>
	
	<div class="admin_field">
		<label for="addTasks">All Tasks:</label><br />
		<div id="addTasksDiv">	
			<?php echo JHTML::_( 'arc_planner.tasks', 'addTasks', array(), null, true ); ?>
		</div>
	</div>

</div>