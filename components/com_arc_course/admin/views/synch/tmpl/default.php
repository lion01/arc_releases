<?php
/**
 * @package     Arc
 * @subpackage  Course
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
		<legend><?php echo JText::_( 'Import Courses' );?></legend>
		<table>
			<tr>
				<td>
					<?php echo $this->synchParams->render( 'params', 'synch_db' ); ?>
				</td>
			</tr>
		</table>
	</fieldset>
	<input type="hidden" name="option" value="com_arc_course" />
	<input type="hidden" name="view" value="synch" />
	<input type="hidden" name="task" value="" />
</form>