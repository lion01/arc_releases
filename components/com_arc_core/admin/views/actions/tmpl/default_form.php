<?php
/**
 * @package     Arc
 * @subpackage  Core
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
		<legend><?php echo JText::_( 'Action Definition' ); ?></legend>
		<table class="admintable">
			<tr>
				<td align="right" class="key">
					<label for="act_id"><?php echo JText::_( 'ID' ); ?>:</label>
				</td>
				<td>
					<input type="text" id="act_id" name="act[id]" size="9" maxlength="10" value="<?php echo $this->action->id; ?>" readonly="readonly" />
				</td>
			</tr>
			<tr>
				<td align="right" class="key">
					<label for="act_menu_id"><?php echo JText::_( 'Menu Item' ); ?>:</label>
				</td>
				<td>
					<?php echo JHTML::_( 'select.genericlist', $this->menus, 'act[menu_id]', '', 'value', 'text', $this->action->menu_id ); ?>
				</td>
			</tr>
			<tr>
				<td align="right" class="key">
					<label for="act_params"><?php echo JText::_( 'Params' ); ?>:</label>
				</td>
				<td>
					<textarea id="act_params" name="act[params]" ><?php echo htmlspecialchars($this->action->params); ?></textarea>
				</td>
			</tr>
			<tr>
				<td align="right" class="key">
					<label for="act_name"><?php echo JText::_( 'Name' ); ?>:</label>
				</td>
				<td>
					<input type="text" id="act_name" name="act[name]" size="30" value="<?php echo $this->action->name; ?>" />
				</td>
			</tr>
			<tr>
				<td align="right" class="key">
					<label for="act_text"><?php echo JText::_( 'Menu Text' ); ?>:</label>
				</td>
				<td>
					<input type="text" id="act_text" name="act[menu_text]" size="30" value="<?php echo $this->action->menu_text; ?>" />
				</td>
			</tr>
			<tr>
				<td align="right" class="key">
					<label for="act_description"><?php echo JText::_( 'Description' ); ?>:</label>
				</td>
				<td>
					<textarea id="act_description" name="act[description]" ><?php echo htmlspecialchars($this->action->description); ?></textarea>
				</td>
			</tr>
			<tr>
				<td align="right" class="key">
					<label for="act_fav"><?php echo JText::_( 'Favourite' ); ?>:</label>
				</td>
				<td>
					<?php echo JHTML::_( 'admin_arc.roleList', 'act[favourite]', null, true ); ?>
				</td>
			</tr>
		</table>
	</fieldset>
	<input type="hidden" name="act[option]" value="" />
	<input type="hidden" name="act[task]" value="" />
	
	<input type="hidden" name="msg_handler" value="behaviour" />
	<input type="hidden" name="option" value="com_arc_core" />
	<input type="hidden" name="view" value="actions" />
	<input type="hidden" name="task" value="save" />
</form>