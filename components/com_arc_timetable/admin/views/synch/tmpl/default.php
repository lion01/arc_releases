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
<p>These should be run one at a time.
   Timetable data must be imported before enrolment data can be.
   Leaving the dates blank will import data for yesterday, today and tomorrow</p>
<form action="index.php" method="post" name="adminForm" id="adminForm">
	<fieldset class="adminform_thinfieldset">
		<legend><?php echo JText::_( 'Import Timetable' );?></legend>
		<table>
			<tr>
				<td>
					<?php echo $this->synchParams->render( 'params', 'synch_db' ); ?>
				</td>
			</tr>
		</table>
	</fieldset>
	<input type="hidden" name="option" value="com_arc_timetable" />
	<input type="hidden" name="view" value="synch" />
	<input type="hidden" name="task" value="" />
</form>