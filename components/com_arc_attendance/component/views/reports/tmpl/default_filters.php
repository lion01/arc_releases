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
<div id="filter_forms_div">
	<?php if( $this->edits ) { echo $this->loadTemplate( 'edit_marks' ); } ?>
	<form class="filter_form" action="<?php echo ApotheosisLib::getActionLinkByName( 'apoth_att_reports' ); ?>" name="arc_filter" id="mark_filter_form" method="post">
		<label for="mark_filter_select">Filter marks:</label>
		<?php echo JHTML::_( 'arc_attendance.codeList', 'mark_filter_select', null, false, false ); ?>
		<?php echo JHTML::_( 'arc.hidden', 'passthrough', 'general' ); ?>
		<input type="hidden" name="task" value="filter" />
		<input type="hidden" name="sheetId" value="<?php echo $this->sheetId; ?>" />
		<input class="filter_submit" type="submit" value="Update" />
	</form>
	<form class="filter_form" action="<?php echo ApotheosisLib::getActionLinkByName( 'apoth_att_reports' ); ?>" name="arc_filter" id="agg_filter_form" method="post">
		<label for="agg_filter_select">Filter aggregates:</label>
		<select id="agg_filter_select" class="agg_filter_select" name="aggregate">
		<?php $a = $this->get('AggregateTypes');
		$f = $this->model->getAggregateType( $this->sheetId );
		foreach( $a as $id=>$txt ) : ?>
			<option value="<?php echo $id.(($id == $f) ? '" selected="selected' : ''); ?>"><?php echo $txt; ?></option>
		<?php endforeach; ?>
		</select>
		<input type="hidden" name="task" value="aggregate" />
		<input type="hidden" name="sheetId" value="<?php echo $this->sheetId; ?>" />
		<input class="filter_submit" type="submit" value="Update" />
	</form>
</div>