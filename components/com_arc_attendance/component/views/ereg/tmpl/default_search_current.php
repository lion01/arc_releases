<?php
/**
 * @package     Arc
 * @subpackage  Attendance
 * @copyright   Copyright (C) 2005 Punnet. All rights reserved. See COPYRIGHT_ARC.txt
 * @license     http://www.gnu.org/licenses/gpl.html GNU/GPL. See LICENSE_ARC.txt
 * Arc is free software: you can redistribute it and/or modify
 * it under the terms of version 3 of the GNU General Public License
 * as published by the Free Software Foundation.
 * The disclaimer of warranty as stated in the GPL applies to this program
 */

defined( '_JEXEC' ) or die( 'Restricted access' );
?>
<div id="search_container">
	<form action="<?php echo $this->link; ?>" name="arc_search" id="arc_search" method="post">
	<div class="search_row">
		<div class="search_field">
			<label for="day">Day:</label><br />
			<?php echo JHTML::_( 'arc_attendance.days', 'day' );?>
		</div>
		<div class="search_field">
			<label for="day_section">Time:</label><br />
			<?php echo JHTML::_( 'arc_timetable.day_details', 'day_section' );?>
		</div>
		<div class="search_field">
			<label for="rooms">Room:</label><br />
			<?php echo JHTML::_( 'arc_timetable.room', 'rooms' );?>
		</div>
		<div class="search_field">
			<label for="normal_class">Class:</label><br />
			<?php echo JHTML::_( 'arc_timetable.classes', 'normal_class' );?>
		</div>
		<div class="search_field">
			<label for="pastoral_class">Tutor:</label><br />
			<?php echo JHTML::_( 'arc_timetable.tutorgroups', 'pastoral_class' );?>
		</div>
	</div>
	<div class="search_row">
		<div class="search_field">
			<label for="pupil">Pupil:</label><br />
			<?php echo JHTML::_( 'arc_timetable.pupil', 'pupil' );?>
		</div>
		<div class="search_field">
			<label for="teacher">Teacher:</label><br />
			<?php echo JHTML::_( 'arc_timetable.teacher', 'teacher' );?>
		</div>
	</div>
	<div class="search_row">
		<div class="search_field">
			<?php echo JHTML::_( 'arc.hidden', 'passthrough', 'general' ); ?>
			<?php echo JHTML::_( 'arc.submit' ); ?>
		</div>
	</div>
	</form>
</div>