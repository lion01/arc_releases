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

JHTML::_( 'arc.tip' );
echo JHTML::_( 'arc.searchStart' ); ?>
<div id="search_container">
	<form action="<?php echo ApotheosisLib::getActionLinkByName( 'apoth_be_reports' ); ?>" name="arc_search" id="arc_search" method="post" >
		<div id="column_container">
			<div class="search_column" id="left_col">
				<div class="search_row">
					<div class="search_field">
						<label for="series" class="arcTip" title="How the data should be grouped">Series:</label><br />
						<?php echo JHTML::_( 'arc_behaviour.seriesTypes', 'series' );?>
					</div>
					<div class="search_field">
						<label for="limits" class="arcTip" title="Which type of results to show.<br />Enter the number of results to display in the next field.">Subset:</label><br />
						<?php echo JHTML::_( 'arc_behaviour.limits', 'limits', null, 5 );?>
					</div>
				</div>
				<div class="search_row">
					<div class="search_field">
						<label for="teacher" class="arcTip" title="Limit the search to certain staff members">Author:</label><br />
						<?php echo JHTML::_( 'arc_people.people', 'author', null, 'teacher OR staff', true, array( 'multiple'=>'multiple' ) );?>
					</div>
				</div>
				<div class="search_row">
					<div class="search_field">
						<label for="person_id" class="arcTip" title="Limit the search to certain students">Student:</label><br />
						<?php echo JHTML::_( 'arc_people.people', 'person_id', null, 'pupil', true, array( 'multiple'=>'multiple' ) );?>
					</div>
					<?php
					$act = ApotheosisLib::getActionIdByName('apoth_att_report_truant_details');
					if( !is_null($act) && ApotheosisLibAcl::getUserPermitted(null, $act) ): ?>
				</div>
				<div class="search_row">
					<div class="search_field">
						<?php
							if( $editTruantLink = ApotheosisLibAcl::getUserLinkAllowed('apoth_att_report_truant_details_edit_inter', array()) ) {
								JHTML::_('behavior.modal');
								echo JHTML::_( 'arc.adminLink', $editTruantLink, 'Edit truants list', 'modal_refresh' );
							}
						?>
						<label for="truant_id" class="arcTip" title="">Truant:</label><br />
						<?php echo JHTML::_( 'arc_people.people', 'truant_id', null, 'truant', true, array( 'multiple'=>'multiple' ) );?>
					</div>
					<?php endif; ?>
				</div>
				<div class="search_row">
					<div class="search_field">
						<label for="incidents" class="arcTip" title="Which type of incidents to include in the search.<br />Enter the number of results to display in the next field.">Incident Type:</label><br />
						<?php echo JHTML::_( 'arc_behaviour.typeList', 'incidents', null, true );?>
					</div>
				</div>
			</div>
			
			<div class="search_column" id="right_col">
				<div class="search_row">
					<div class="search_field">
						<label for="start_date" class="arcTip" title="The earliest date to include in the search">Start Date:</label><br />
						<?php echo JHTML::_( 'arc.date', 'start_date', ApotheosisLib::getEarlyDate() );?>
					</div>
					<div class="search_field">
						<label for="end_date" class="arcTip" title="The latest date to include in the search">End Date:</label><br />
						<?php echo JHTML::_( 'arc.date', 'end_date', date('Y-m-d')  );?>
					</div>
				</div>
				<div class="search_row">
					<div class="search_field">
						<label for="day_section" class="arcTip" title="Limit the search to certain periods">Time:</label><br />
						<?php echo JHTML::_( 'arc_timetable.day_details', 'day_section', null, true );?>
					</div>
					<div class="search_field">
						<label for="academic_year" class="arcTip" title="Limit the search to certain year groups">Year Group:</label><br />
						<?php echo JHTML::_( 'arc.yearGroup', 'academic_year', null, true );?>
					</div>
					<div class="search_field">
						<label for="tutor" class="arcTip" title="Limit the search to certain tutor groups">Tutor:</label><br />
						<?php echo JHTML::_( 'arc_timetable.tutorgroups', 'tutor' );?>
					</div>
				</div>
				<div class="search_row">
					<div class="search_field">
						<label for="groups" class="arcTip" title="Limit the search to certain classes">Classes:</label><br />
						<?php echo JHTML::_( 'groups.grouptree', 'groups' ); ?>
					</div>
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