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

if( !isset($this->firstPass) ) { $this->firstPass = true; }
if( $this->firstPass ) : $this->firstPass = false;
?>
<form class="edit_form" id="edit" method="post" action="<?php echo ApotheosisLib::getActionLinkByName( 'att_reports_edit' ); ?>">
	<div id="edit_form_div">
		<div>
			<label for="edit_find">Find:</label>
			<?php echo JHTML::_( 'arc_attendance.codeList', 'edit_find', null, false ); ?>
			<label for="edit_day_section">at times:</label>
			<?php echo JHTML::_( 'select.genericList', $this->model->getDaySections( $this->sheetId ), 'edit_day_section', '', 'id', 'displayname', JRequest::getVar('edit_day_section') ); ?>
			<label for="edit_replace">and replace it with:</label>
			<?php echo JHTML::_( 'arc_attendance.codeList', 'edit_replace', null, false ); ?>
		</div>
		<div>
			<span>
				<label for="edit_start_date">Within the date range:</label>
				<?php echo JHTML::_( 'arc.date', 'edit_start_date', JRequest::getVar('edit_start_date', JRequest::getVar('start_date')) ); ?>
			</span>
			<span>
				<label for="edit_end_date">to:</label>
				<?php echo JHTML::_( 'arc.date', 'edit_end_date', JRequest::getVar('edit_end_date', JRequest::getVar('end_date')) ); ?>
			</span>
			<input type="hidden" name="sheetId" value="<?php echo $this->sheetId; ?>" />
			<input type="submit" name="submit" value="Apply" />
		</div>
	</div>
<?php else : $this->firstPass = true; ?>
</form>
<?php endif; ?>