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

echo JHTML::_( 'arc.searchStart' ); ?>
<div id="search_container">
	<form action="<?php echo ApotheosisLib::getActionLinkByName( 'apoth_att_reports' ); ?>" name="arc_search" id="arc_search" method="post" >
		<div class="search_row">
			<div class="search_field">
				<label for="start_date">Start Date:</label><br />
				<?php echo JHTML::_( 'arc.date', 'start_date', date('Y-m-d') );?>
			</div>
			<div class="search_field">
				<label for="end_date">End Date:</label><br />
				<?php echo JHTML::_( 'arc.date', 'end_date', date('Y-m-d')  );?>
			</div>
			<div class="search_field">
				<label for="subject">Subject:</label><br />
				<?php echo JHTML::_( 'arc_attendance.subjects', 'subject' );?>
			</div>
			<div class="search_field">
				<label for="course_id">Class:</label><br />
				<?php echo JHTML::_( 'arc_timetable.classes', 'course_id' );?>
			</div>
			<div class="search_field">
				<label for="tutor">Tutor:</label><br />
				<?php echo JHTML::_( 'arc_timetable.tutorgroups', 'tutor' );?>
			</div>
			<div class="search_field">
				<label for="period_type">Period Type:</label><br />
				<?php echo JHTML::_( 'arc_attendance.typeOfPeriod', 'period_type' );?>
			</div>
		</div>
		<div class="search_row">
			<div class="search_field">
				<label for="teacher">Teacher:</label><br />
				<?php echo JHTML::_( 'arc_timetable.teacher', 'teacher', null, true );?>
			</div>
			<div class="search_field">
				<label for="person_id">Student:</label><br />
				<?php echo JHTML::_( 'arc_timetable.pupil', 'person_id', null, true );?>
			</div>
			<?php if( ApotheosisLibAcl::getUserPermitted(null, ApotheosisLib::getActionIdByName('apoth_att_report_truant_details')) ): ?>
			<div class="search_field">
				<?php
					if( $editTruantLink = ApotheosisLibAcl::getUserLinkAllowed('apoth_att_report_truant_details_edit_inter', array()) ) {
						JHTML::_('behavior.modal');
						echo JHTML::_( 'arc.adminLink', $editTruantLink, 'Edit truants list', 'modal_refresh' );
					}
				?>
				<label for="truant_id">Truant:</label><br />
				<?php echo JHTML::_( 'arc_attendance.truant', 'truant_id', null, true );?>
			</div>
			<?php endif; ?>
			<div class="search_field">
				<label for="day_section">Time:</label><br />
				<?php echo JHTML::_( 'arc_timetable.day_details', 'day_section', null, true );?>
			</div>
			<div class="search_field">
				<label for="academic_year">Year Group:</label><br />
				<?php echo JHTML::_( 'arc.yearGroup', 'academic_year', null, true );?>
			</div>
			<div class="search_field">
				<label for="att_code">Mark Type:</label><br />
				<?php echo JHTML::_( 'arc_attendance.codeList', 'att_code', null, true, false ); ?>
			</div>
			<div class="search_field">
				<label for="toggle">Toggles:</label><br />
				<?php echo JHTML::_( 'arc_attendance.toggle', 'toggles', null, true ); ?>
			</div>
			<div class="search_field">
				<div class="search_div_nowrap">
					<label for="att_percent_com">Attendance %:</label><br />
					<?php echo JHTML::_( 'arc_attendance.percentCom', 'att_percent_com', 'less_than' ); ?>
				</div>
				<div class="search_div_nowrap">
					<label for="att_percent"></label><br />
					<?php echo JHTML::_( 'arc_attendance.percent', 'att_percent' ); ?>
				</div>
			</div>
		</div>
		<div class="search_row">
			<div class="search_field">
				<?php echo JHTML::_( 'arc.hidden', 'passthrough', 'general' ); ?>
				<?php echo JHTML::_( 'arc.submit' ); ?>
				<?php echo JHTML::_( 'arc.reset' ); ?>
			</div>
		</div>
	</form>
</div>