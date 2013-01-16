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

JHTML::script( 'new_template.js', JURI::root().'administrator'.DS.'components'.DS.'com_arc_people'.DS.'views'.DS.'profiles'.DS.'tmpl'.DS, true );
?>
<form action="index.php" method="post" name="adminForm" id="adminForm">
	<fieldset class="adminform_thinfieldset" style="vertical-align: top;">
		<legend><?php echo JText::_( 'Profile Templates' );?></legend>
		<table class="adminlist">
			<thead>
				<tr>
					<th><?php echo JText::_( 'Template Type' ); ?></th>
					<th><?php echo JText::_( 'Select' ); ?></th>
					<th><?php echo JText::_( 'Remove' ); ?></th>
				</tr>
			</thead>
			<tbody id="tbody">
				<?php foreach( $this->templateIds as $templateId ): ?>
				<tr class="row">
					<td class="template_id"><?php echo ucfirst( $templateId ); ?></td>
					<td style="text-align: center;">
						<input type="radio" name="ids[]" value="<?php echo $templateId; ?>" />
						<input type="hidden" name="template_types[]" value="<?php echo strtolower( $templateId ); ?>" />
					</td>
					<td style="text-align: center;">
						<?php echo JHTML::_( 'arc.image', 'remove-16', 'border="0" alt="Remove Template Type" title="Remove Template Type" class="remove_'.$templateId.'" style="cursor: pointer;"' ); ?>
					</td>
				</tr>
				<?php endforeach; ?>
				<tr id="template_add_row">
					<td colspan="3">Add a new template type: 
						<input type="text" size="30" id="new_template_id_input" value="" />
						<?php echo JHTML::_( 'arc.image', 'add-16', 'border="0" alt="Add" title="Add" id="add_template" style="cursor: pointer; vertical-align: middle;"' ); ?>
					</td>
				</tr>
				<tr id="arch_row" class="row">
					<td class="template_id">_template_id_</td>
					<td style="text-align: center;">
						<input type="radio" name="ids[]" value="_template_id_" />
						<input type="hidden" name="template_types[]" value="_template_id_" />
					</td>
					<td style="text-align: center;">
						<?php echo JHTML::_( 'arc.image', 'remove-16', 'border="0" alt="Remove Template Type" title="Remove Template Type" class="remove__template_id_" style="cursor: pointer;"' ); ?>
					</td>
				</tr>
			</tbody>
		</table>
	</fieldset>
	<input type="hidden" name="option" value="com_arc_people" />
	<input type="hidden" name="view" value="profiles" />
	<input type="hidden" name="task" value="" />
	<input type="hidden" name="select_task" value="<?php echo $this->curType; ?>" />
</form>