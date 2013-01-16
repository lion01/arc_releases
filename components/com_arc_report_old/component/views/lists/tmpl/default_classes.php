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
<a href="<?php echo ApotheosisLib::getActionLinkByName( 'apoth_rpt_lists' ); ?>">Course List</a>
<table id="course-list">
	<colgroup>
		<col class="col1"></col>
		<col class="col2"></col>
		<col class="col3"></col>
		<col class="col4"></col>
	</colgroup>
	<?php
	if (isset($this->classes)) {
		echo $this->loadTemplate('class_list');
	}
	?>
</table>
