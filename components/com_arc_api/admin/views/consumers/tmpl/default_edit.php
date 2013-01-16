<?php
/**
 * @package     Arc
 * @subpackage  API
 * @copyright   Copyright (C) 2005 Punnet. All rights reserved. See COPYRIGHT_ARC.txt
 * @license     http://www.gnu.org/licenses/gpl.html GNU/GPL. See LICENSE_ARC.txt
 * Arc is free software: you can redistribute it and/or modify
 * it under the terms of version 3 of the GNU General Public License
 * as published by the Free Software Foundation.
 * The disclaimer of warranty as stated in the GPL applies to this program
 */

defined( '_JEXEC' ) or die( 'Restricted access' );

// add javascript
JHTML::script( 'edit.js', $this->addPath, true );

// add script to pass text strings to JS form checker
$inputStrings = array(
	'name'=>JText::_( 'Name' ),
	'description'=>JText::_( 'Description' ),
	'key'=>JText::_( 'Key' ),
	'secret'=>JText::_( 'Secret' ),
);
$encodedInputStrings = 'check_strings = $H( Json.evaluate( \''.json_encode( $inputStrings ).'\' ) );';
$doc = &JFactory::getDocument();
$doc->addScriptDeclaration( $encodedInputStrings );
?>
<form action="index.php" method="post" name="adminForm" id="adminForm">
	<fieldset class="adminform">
		<legend><?php echo JText::_( 'Consumer Details' ); ?></legend>
		<table class="admintable">
			<tr>
				<td align="right" class="key">
					<label for="id"><?php echo JText::_( 'ID' ); ?>:</label>
				</td>
				<td>
					<input type="text" name="id" id="id" size="9" maxlength="10" value="<?php echo $this->consumer->getId(); ?>" readonly="readonly" />
				</td>
			</tr>
			<tr>
				<td align="right" class="key">
					<label for="name"><?php echo JText::_( 'Name' ); ?>:</label>
				</td>
				<td>
					<input type="text" name="name" id="name" size="32" maxlength="100" value="<?php echo $this->consumer->getDatum( 'name' ); ?>" />
				</td>
			</tr>
			<tr>
				<td align="right" class="key">
					<label for="description"><?php echo JText::_( 'Description' ); ?>:</label>
				</td>
				<td>
					<input type="text" name="description" id="description" size="32" maxlength="250" value="<?php echo $this->consumer->getDatum( 'description' ); ?>" />
				</td>
			</tr>
			<tr>
				<td align="right" class="key">
					<label for="key"><?php echo JText::_( 'Key' ); ?>:</label>
				</td>
				<td>
					<input type="text" name="key" id="key" size="32" maxlength="50" value="<?php echo $this->consumer->getDatum( 'cons_key' ); ?>" />
				</td>
			</tr>
			<tr>
				<td align="right" class="key">
					<label for="secret"><?php echo JText::_( 'Secret' ); ?>:</label>
				</td>
				<td>
					<input type="text" name="secret" id="secret" size="32" maxlength="50" value="<?php echo $this->consumer->getDatum( 'cons_secret' ); ?>" readonly="readonly" />
				</td>
			</tr>
			
		</table>
	</fieldset>
	<div class="clr"></div>
	<input type="hidden" name="option" value="com_arc_api" />
	<input type="hidden" name="view" value="consumers" />
	<input type="hidden" name="task" value="save" />
</form>