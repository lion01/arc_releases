<?php
/**
 * @package     Arc
 * @subpackage  Timetable
 * @copyright   Copyright (C) 2005 Punnet. All rights reserved. See COPYRIGHT_ARC.txt
 * @license     http://www.gnu.org/licenses/gpl.html GNU/GPL. See LICENSE_ARC.txt
 * Arc is free software: you can redistribute it and/or modify
 * it under the terms of version 3 of the GNU General Public License
 * as published by the Free Software Foundation.
 * The disclaimer of warranty as stated in the GPL applies to this program
 */

defined( '_JEXEC' ) or die( 'Restricted access' );
?>
<h3>Timetable</h3>
<?php echo JHTML::_( 'arc.searchStart' ); ?>
	<div id="search_container">
		<form name="arc_search" id="arc_search" method="post">
		<div id="column_container">
			<div class="search_column">
				<div class="search_row">
					<div class="search_field">
						<label for="start_date">Start Date:</label><br />
						<?php echo JHTML::_( 'arc.date', 'start_date' ); ?>
					</div>
				</div>
				<div class="search_row">
					<div class="search_field">
						<label for="end_date">End Date:</label><br />
						<?php echo JHTML::_( 'arc.date', 'end_date' ); ?>
					</div>
				</div>
<!--
				<div class="search_row">
					<div class="search_field">
						<label for="room">Room:</label><br />
						<?php echo JHTML::_( 'arc_timetable.room', 'room' );?>
					</div>
				</div>
				<div class="search_row">
					<div class="search_field">
						<label for="day_section">Time:</label><br />
						<?php echo JHTML::_( 'arc_timetable.day_details', 'day_section' );?>
					</div>
				</div>
-->
			</div>
			<div class="search_column">
				<div class="search_row">
					<div class="search_field">
						<label for="teacher">Teachers (select at least one):</label><br />
						<?php echo JHTML::_( 'arc_timetable.teacher', 'teacher', null, true );?>
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
<?php
echo '<hr />';

if( $this->noSessions < 1 ) {
	echo 'There is no timetable to display';
}
else {
	echo $this->loadTemplate('timetable');
}
?>