<?php
/**
 * @package     Arc
 * @subpackage  TV
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
	<fieldset class="adminform_thinfieldset" style="vertical-align: top;">
		<legend><?php echo JText::_( 'General Settings' ); ?></legend>
		<table>
			<tr>
				<td>
					<?php echo $this->videoParams->render( 'params', 'general_params' ); ?>
				</td>
			</tr>
		</table>
	</fieldset>
	<fieldset class="adminform_thinfieldset" style="vertical-align: top;">
		<legend><?php echo JText::_( 'Search Weightings' ); ?></legend>
		<table>
			<tr>
				<td>
					<?php echo $this->videoParams->render( 'params', 'search_params' ); ?>
				</td>
			</tr>
		</table>
	</fieldset>
	<fieldset class="adminform_thinfieldset" style="vertical-align: top;">
		<legend><?php echo JText::_( 'Supported Video Resolutions' ); ?></legend>
		<table>
			<tr>
				<td>
					<?php echo $this->videoParams->render( 'params', 'res_params' ); ?>
				</td>
			</tr>
		</table>
	</fieldset>
	<fieldset class="adminform_thinfieldset" style="vertical-align: top;">
		<legend><?php echo JText::_( 'Moderation email options' ); ?></legend>
		<table>
			<tr>
				<td>
					<?php echo $this->videoParams->render( 'params', 'email_params' ); ?>
				</td>
			</tr>
		</table>
	</fieldset>
	<input type="hidden" name="option" value="com_arc_tv" />
	<input type="hidden" name="view" value="video" />
	<input type="hidden" name="task" value="" />
</form>