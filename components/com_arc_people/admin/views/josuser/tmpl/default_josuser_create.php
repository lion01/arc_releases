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
<form action="index.php" method="post" name="adminForm" id="adminForm">
	<fieldset class="adminform_thinfieldset">
		<legend><?php echo JText::_( 'Joomla! User Creation Process' );?></legend>
		<ul>
			<li>Creation of the Joomla! accounts is an automatic process.</li>
			<li>The format of the user variables to be stored is based on the preferences set on the Format Joomla! Variables page.</li>
			<li>Click the Create Joomla! Users button to continue.</li>
		</ul>
	</fieldset>
	<input type="hidden" name="option" value="com_arc_people" />
	<input type="hidden" name="view" value="josuser" />
	<input type="hidden" name="task" value="" />
	<input type="hidden" name="apply_task" value="create" />
</form>