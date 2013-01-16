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

#planner_inter_div {
	padding: 10px;
}

.edit_update_centre {
	text-align: center;
}

form div {
	clear: left;
	display: block;
	margin: 10px 0px;
}

form div label {
	float: left;
	display: block;
	width: 100px;
	text-align: right;
	margin-right: 5px;
}

form input {
	vertical-align: middle;
	margin: 0px;
}

textarea#comment {
	width: 400px;
	height: 100px;
}

input#progress {
	width: 30px;
}

input.evidence {
	width: 400px;
}

</style>
<div id="planner_inter_div">
	<h3 class="edit_update_centre">Add evidence to an update</h3>
	<form enctype="multipart/form-data" method="post" action="<?php echo ApotheosisLib::getActionLinkByName( 'apoth_plan_update_edit_inter2_save', $this->dependancies ) ?>">
		<input type="hidden" name="MAX_FILE_SIZE" value="64000" />
		<?php
			echo $this->loadTemplate( 'evidence_list' );
		?>
		
		<div>
			<label for="submit">&nbsp;</label>
			<input id="submit" type="submit" value="Save">
		</div>
	</form>
</div>
