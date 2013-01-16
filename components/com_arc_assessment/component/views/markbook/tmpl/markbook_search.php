<?php
/**
 * @package     Arc
 * @subpackage  Assessment
 * @copyright   Copyright (C) 2005 Punnet. All rights reserved. See COPYRIGHT_ARC.txt
 * @license     http://www.gnu.org/licenses/gpl.html GNU/GPL. See LICENSE_ARC.txt
 * Arc is free software: you can redistribute it and/or modify
 * it under the terms of version 3 of the GNU General Public License
 * as published by the Free Software Foundation.
 * The disclaimer of warranty as stated in the GPL applies to this program
 */

defined( '_JEXEC' ) or die( 'Restricted access' );

ApotheosisLib::setTmpAction( ApotheosisLib::getActionIdByName('apoth_ass_main_search') );
echo JHTML::_( 'arc.searchStart' ); ?>
	<div id="search_container">
		<form action="<?php echo ApotheosisLibAcl::getUserLinkAllowed( 'apoth_ass_main' ); ?>" name="arc_search" id="arc_search" method="post">
		<div id="column_container">
			<div class="search_column">
				<div class="search_row">
					<div class="search_field">
						<label for="start_date">Start Date:</label><br />
						<?php echo JHTML::_( 'arc.dateField', 'start_date', ApotheosisLib::getEarlyDate() ); ?>
					</div>
					<div class="search_field">
						<label for="end_date">End Date:</label><br />
						<?php echo JHTML::_( 'arc.dateField', 'end_date', date('Y-m-d') ); ?>
					</div>
				</div>
				<div class="search_row">
					<div class="search_field">
						<label for="teacher">Teacher:</label><br />
						<?php echo JHTML::_( 'arc_timetable.teacher', 'teacher' );?>
					</div>
					<div class="search_field">
						<label for="academic_year">Year Group:</label><br />
						<?php echo JHTML::_( 'arc.yearGroup', 'academic_year' ); ?>
					</div>
				</div>
				<div class="search_row">
					<div class="search_field">
						<label for="pupil">Pupil:</label><br />
						<?php echo JHTML::_( 'arc_timetable.pupil', 'pupil' );?>
					</div>
				</div>
				<div class="search_row">
					<div class="search_field">
						<label for="aspects">Aspects:</label><br />
						<?php echo JHTML::_( 'arc_assessment.aspects', 'aspect' );?>
					</div>
				</div>
				<div class="search_row">
					<div class="search_field">
						<label for="complete">Complete:</label><br />
						<?php echo JHTML::_( 'arc_assessment.complete', 'complete', 0 );?>
					</div>
				</div>
			</div>
			<div class="search_column">
				<div class="search_row">
					<div class="search_field">
						<label for="groups">Classes:</label><br />
						<?php echo JHTML::_( 'groups.grouptree', 'groups' ); ?>
					</div>
					<div class="search_field">
						<label for="assessments">Assessments:</label><br />
						<?php echo JHTML::_( 'arc_assessment.assessments', 'assessments', null, true );?>
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
<?php ApotheosisLib::resetTmpAction(); ?>