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
?>
<style type="text/css">

#cat_current_wrapper {
	overflow: auto;
	margin-bottom: 1em;
}

#cat_retired_wrapper {
	overflow: auto;
	margin-bottom: 2px;
}

.cat_category_title {
	font-weight: bold;
	padding: 0px 1px;
}

.cat_category_table {
	padding: 2px 1px;
}

.cat_category_table table {
	border-collapse: collapse;
}

.cat_category_table th {
	padding: 1px 3px;
	border: solid 1px grey;
	vertical-align: middle;
	color: black;
}

.cat_category_table td {
	padding: 1px 3px;
	border: solid 1px grey;
	vertical-align: middle;
	min-width: 6em;
}

.cat_admin_column {
	width: 5em;
}

.cat_checkbox_column {
	width: 5em;
}

.cat_title_column {
	min-width: 15em;
}

.cat_date_column {
	width: 12em;
}

</style>
<h3>Planner Categories</h3>
<div id="cat_current_wrapper">
	<div class="cat_category_title">
		Current
	</div>
	<div class="cat_category_table">
		<table>
			<tr>
				<th class="cat_admin_column">Add / Del</th>
				<th class="cat_checkbox_column">All / None</th>
				<th class="cat_title_column">Title</th>
				<th>Progress</th>
				<th>Complete</th>
				<th>Incomplete</th>
				<th>Overdue</th>
				<th>Due in <?php echo $this->dueDaysAhead; ?> days</th>
			</tr>
			<?php
				$this->currentCats = $this->model->getCurrent();
				foreach( $this->currentCats as $curCatId ) {
					$this->category = &$this->model->getCategory( $curCatId );
					echo $this->loadTemplate( 'current_row' );
				}
			?>
		</table>
	</div>
</div>
<div id="cat_retired_wrapper">
	<div class="cat_category_title">
		Retired
	</div>
	<div class="cat_category_table">
		<table>
			<tr>
				<th class="cat_checkbox_column">Activate</th>
				<th class="cat_title_column">Title</th>
				<th class="cat_date_column">Date Retired</th>
			</tr>
			<?php
				$this->retiredCats = $this->model->getRetired();
				foreach( $this->retiredCats as $retCatId ) {
					$this->category = &$this->model->getCategory( $retCatId );
					echo $this->loadTemplate( 'retired_row' );
				}
			?>
		</table>
	</div>
</div>
<?php
//ini_set('xdebug.var_display_max_depth', 10 );var_dump_pre( $this, '<br /><br />model:' ); // *** remove
?>