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
?>
<h3>Reports</h3>
<?php if ($this->state->search) : ?>
<?php echo JHTML::_( 'arc.searchStart' ); ?>
	<div id="search_container">
		<form action="<?php echo $this->listLink; ?>" name="arc_search" id="arc_search" method="post">
		<div class="search_row">
			<div class="search_field">
				<label for="report_cycle">Cycle:</label><br />
				<?php echo JHTML::_( 'arc_report.cycles', 'report_cycle' );?>
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
<?php endif; ?>
<hr />
<table id="course-list">
	<colgroup>
		<col class="col1"></col>
		<col class="col2"></col>
		<col class="col3"></col>
		<col class="col4"></col>
	</colgroup>
	<?php
	if( isset($this->subject) ) {
		echo $this->loadTemplate('normal_list');
	}
	if (isset($this->pastoral)) {
		echo $this->loadTemplate('pastoral_list');
	}
	?>
</table>
