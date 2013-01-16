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
					
					$settings = &$this->items;
					if (($tmp = $this->params->get('recent_marks', false)) !== false) { 
						$settings->set('recent_marks', $tmp);
					}
					if (($tmp = $this->params->get('incidents', false)) !== false) { 
						$settings->set('incidents', $tmp);
					}
					if (($tmp = $this->params->get('no_of_historical_marks', false)) !== false) { 
						$settings->set('no_of_historical_marks', $tmp);
					}
					if (($tmp = $this->params->get('unexplained_code', false)) !== false) { 
						$settings->set('unexplained_code', $tmp);
					}
					echo $settings->render('params', 'settings_settings');
					//var_dump_pre($settings->getGroups());
				?>
			</fieldset>
		</td>
	</tr>
	<input type="hidden" name="component" value="Attendance Manager" />
	<input type="hidden" name="option" value="com_arc_attendance" />
	<input type="hidden" name="view" value="settings" />
	<input type="hidden" name="task" value="save" />
</table>
</form>