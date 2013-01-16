<?php
/**
 * @package     Arc
 * @subpackage  People
 * @copyright   Copyright (C) 2005 Punnet. All rights reserved. See COPYRIGHT_ARC.txt
 * @license     http://www.gnu.org/licenses/gpl.html GNU/GPL. See LICENSE_ARC.txt
 * Arc is free software: you can redistribute it and/or modify
 * it under the terms of version 3 of the GNU General Public License
 * as published by the Free Software Foundation.
 * The disclaimer of warranty as stated in the GPL applies to this program
 */

defined( '_JEXEC' ) or die( 'Restricted access' );
?>
<form enctype="multipart/form-data" action="index.php" method="post" name="adminForm" id="adminForm">
	<ul>
		<li>The required file format is a CSV with 2 columns.</li>
		<li>Each column should be given a suitable title for the sake of readability.</li>
		<li>One column should be user Arc ID's, the second the associated password</li>
	</ul>
	<fieldset class="adminform_thinfieldset">
		<legend><?php echo JText::_( 'Password File Upload' ); ?></legend>
		<input type="file" name="filename" size="50" />
	</fieldset>
	<input type="hidden" name="MAX_FILE_SIZE" value="2000000" />
	<input type="hidden" name="option" value="com_arc_people" />
	<input type="hidden" name="view" value="josuser" />
	<input type="hidden" name="task" value="" />
	<input type="hidden" name="apply_task" value="pword_upload" />
</form>