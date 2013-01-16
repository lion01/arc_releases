<?php
/**
 * @package     Arc
 * @subpackage  Behaviour
 * @copyright   Copyright (C) 2005 Punnet. All rights reserved. See COPYRIGHT_ARC.txt
 * @license     http://www.gnu.org/licenses/gpl.html GNU/GPL. See LICENSE_ARC.txt
 * Arc is free software: you can redistribute it and/or modify
 * it under the terms of version 3 of the GNU General Public License
 * as published by the Free Software Foundation.
 * The disclaimer of warranty as stated in the GPL applies to this program
 */

defined( '_JEXEC' ) or die( 'Restricted access' );

// add script to pass text strings to JS form checker
$inputStrings = array(
	'label'=>JText::_( 'Label' ),
	'score'=>JText::_( 'Score' ),
	'has_text'=>JText::_( 'Has Text' ),
	'tag'=>JText::_( 'Related Message Tag' ),
	'parent'=>JText::_( 'Parent' )
);
$encodedInputStrings = 'check_strings = $H( Json.evaluate( \''.json_encode( $inputStrings ).'\' ) );';
$doc = &JFactory::getDocument();
$doc->addScriptDeclaration( $encodedInputStrings );

?>
<form action="index.php" method="post" name="adminForm" id="adminForm">
	<fieldset class="adminform_thinfieldset">
		<legend><?php echo JText::_( 'Message Details' ); ?></legend>
		<table class="admintable">
			<tr>
				<td align="right" class="key">
					<label for="id"><?php echo JText::_( 'ID' ); ?>:</label>
				</td>
				<td>
					<input type="text" name="id" id="id" size="9" maxlength="10" value="<?php echo $this->action->getId(); ?>" readonly="readonly" />
				</td>
			</tr>
			<tr>
				<td align="right" class="key">
					<label for="label"><?php echo JText::_( 'Label' ); ?>:</label>
				</td>
				<td>
					<input type="text" name="label" id="label" size="20" maxlength="100" value="<?php echo $this->action->getLabel(); ?>" />
				</td>
			</tr>
			<tr>
				<td align="right" class="key">
					<label for="score"><?php echo JText::_( 'Score' ); ?>:</label>
				</td>
				<td>
					<input type="text" name="score" id="score" size="5" maxlength="10" value="<?php echo $this->action->getScore(); ?>" />
				</td>
			</tr>
			<tr>
				<td align="right" class="key">
					<label for="has_text"><?php echo JText::_( 'Has text' ); ?>:</label>
				</td>
				<td>
					<input type="checkbox" name="has_text" id="has_text" <?php echo ( $this->action->getHasText() ? 'checked="checked"' : '' ); ?>/>
				</td>
			</tr>
			<tr>
				<td align="right" class="key">
					<label for="incidents"><?php echo JText::_( 'Incidents' ); ?>:</label>
				</td>
				<td>
					<?php echo JHTML::_( 'arc_behaviour.typeList', 'incidents', $this->action->getIncidents(), true ); ?>
				</td>
			</tr>
		</table>
	</fieldset>
	<input type="hidden" name="option" value="com_arc_behaviour" />
	<input type="hidden" name="view" value="actions" />
	<input type="hidden" name="task" value="save" />
</form>