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

echo $this->state->message;?>
<form action="index.php" method="post" name="adminForm">
<table>
	<tr>
		<td>
			<fieldset class="adminform">
				<legend>
					<?php echo JText::_('Settings'); ?>
				</legend>
				<h5>To save these settings, hit the save button</h5>
				<?php
					if (($tmp = $this->params->get('synch_writes', false)) !== false) { $this->items->set('synch_writes', $tmp); }
					if (($tmp = $this->params->get('att_mergeampm', false)) !== false) { $this->items->set('att_mergeampm', $tmp); }
					if (($tmp = $this->params->get('external_am_mark', false)) !== false) { $this->items->set('external_am_mark', $tmp); }
					if (($tmp = $this->params->get('external_pm_mark', false)) !== false) { $this->items->set('external_pm_mark', $tmp); }
					if (($tmp = $this->params->get('internal_mark', false)) !== false) { $this->items->set('internal_mark', $tmp); }
					
					echo $this->items->render('params', 'synch_settings');
				?>
			</fieldset>
		</td>
		<td>
			<fieldset>
				<legend>
					<?php echo JText::_( 'Synchronising the Databases' );?>
				</legend>
				<h5>To synchronise, use the Import button</h5>
				<?php
					if($this->params->synch == 1) {
						$params->set('synch', $this->items->synch);
					}
						
					echo $this->items->render('params', 'synch_db');
				?>
				<br />
				Truncating tables will delete all data...
				<input type="hidden" name="component" value="Attendance Manager" />
				<input type="hidden" name="option" value="com_arc_attendance" />
				<input type="hidden" name="view" value="synch" />
				<input type="hidden" name="task" value="save" />
				<input type="hidden" name="boxchecked" value="0" />
			</fieldset>
		</td>
	</tr>
</table>
</form>