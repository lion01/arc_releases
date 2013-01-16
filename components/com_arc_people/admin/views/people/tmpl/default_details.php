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

// add javascript
JHTML::script( 'default_details.js', $this->addPath, true );

$readOnly = $this->edit ? '' : 'readonly="readonly"';
$disabled = $this->edit ? '' : 'disabled="disabled"';
$address = $this->person->getDatum( 'address' );
$relations = $this->person->getDatum( 'relations' );
$roles = $this->person->getDatum( 'roles' );
?>
<form action="index.php" method="post" name="adminForm" id="adminForm">
	<div>
	<fieldset class="adminform_thinfieldset">
		<legend><?php echo JText::_( 'Person Details' ); ?></legend>
		<table class="admintable">
			<tr><td align="right" class="key">
					<label for="id"><?php echo JText::_( 'Arc ID' ); ?>:</label>
				</td><td>
					<input type="text" name="id" id="id" size="25" maxlength="20" value="<?php echo $this->person->getDatum( 'id' ); ?>" readonly="readonly" />
				</td></tr>
			<tr><td align="right" class="key">
					<label for="ext_person_id"><?php echo JText::_( 'External ID' ); ?>:</label>
				</td><td>
					<input type="text" name="ext_person_id" id="ext_person_id" size="25" maxlength="20" value="<?php echo $this->person->getDatum( 'ext_person_id' ); ?>" readonly="readonly" />
				</td></tr>
			<tr><td align="right" class="key">
					<label for="juserid"><?php echo JText::_( 'Joomla ID' ); ?>:</label>
				</td><td>
					<input type="text" name="juserid" id="juserid" size="25" maxlength="20" value="<?php echo $this->person->getDatum( 'juserid' ); ?>" readonly="readonly" />
				</td></tr>
			<tr><td align="right" class="key">
					<label for="upn"><?php echo JText::_( 'UPN' ); ?>:</label>
				</td><td>
					<input type="text" name="upn" id="upn" size="25" maxlength="20" value="<?php echo $this->person->getDatum( 'upn' ); ?>" <?php echo $readOnly; ?> />
				</td></tr>
			<tr><td align="right" class="key">
					<label for="title"><?php echo JText::_( 'Title' ); ?>:</label>
				</td><td>
					<input type="text" name="title" id="title" size="25" maxlength="20" value="<?php echo $this->person->getDatum( 'title' ); ?>" <?php echo $readOnly; ?> />
				</td></tr>
			<tr><td align="right" class="key">
					<label for="firstname"><?php echo JText::_( 'Legal Firstname' ); ?>:</label>
				</td><td>
					<input type="text" name="firstname" id="firstname" size="25" maxlength="20" value="<?php echo $this->person->getDatum( 'firstname' ); ?>" <?php echo $readOnly; ?> />
				</td></tr>
			<tr><td align="right" class="key">
					<label for="middlenames"><?php echo JText::_( 'Middlename(s)' ); ?>:</label>
				</td><td>
					<input type="text" name="middlenames" id="middlenames" size="25" maxlength="20" value="<?php echo $this->person->getDatum( 'middlenames' ); ?>" <?php echo $readOnly; ?> />
				</td></tr>
			<tr><td align="right" class="key">
					<label for="surname"><?php echo JText::_( 'Legal Surname' ); ?>:</label>
				</td><td>
					<input type="text" name="surname" id="surname" size="25" maxlength="20" value="<?php echo $this->person->getDatum( 'surname' ); ?>" <?php echo $readOnly; ?> />
				</td></tr>
			<tr><td align="right" class="key">
					<label for="preferred_firstname"><?php echo JText::_( 'Preferred Firstname' ); ?>:</label>
				</td><td>
					<input type="text" name="preferred_firstname" id="preferred_firstname" size="25" maxlength="20" value="<?php echo $this->person->getDatum( 'preferred_firstname' ); ?>" <?php echo $readOnly; ?> />
				</td></tr>
			<tr><td align="right" class="key">
					<label for="preferred_surname"><?php echo JText::_( 'Preferred Surname' ); ?>:</label>
				</td><td>
					<input type="text" name="preferred_surname" id="preferred_surname" size="25" maxlength="20" value="<?php echo $this->person->getDatum( 'preferred_surname' ); ?>" <?php echo $readOnly; ?> />
				</td></tr>
			<tr><td align="right" class="key">
					<label for="dob"><?php echo JText::_( 'Date of Birth' ); ?>:</label>
				</td><td>
					<input type="text" name="dob" id="dob" size="25" maxlength="20" value="<?php echo $this->person->getDatum( 'dob' ); ?>" <?php echo $readOnly; ?> />
				</td></tr>
			<tr><td align="right" class="key">
					<label for="gender"><?php echo JText::_( 'Gender' ); ?>:</label>
				</td><td>
					<input type="text" name="gender" id="gender" size="25" maxlength="20" value="<?php echo $this->person->getDatum( 'gender' ); ?>" <?php echo $readOnly; ?> />
				</td></tr>
			<tr><td align="right" class="key">
					<label for="email"><?php echo JText::_( 'Email' ); ?>:</label>
				</td><td>
					<input type="text" name="email" id="email" size="25" maxlength="20" value="<?php echo $this->person->getDatum( 'email' ); ?>" <?php echo $readOnly; ?> />
				</td></tr>
		</table>
	</fieldset>
	<fieldset class="adminform_thinfieldset" style="vertical-align: top;">
		<legend><?php echo JText::_( 'Address Details' ); ?></legend>
		<table class="admintable">
			<tr><td align="right" class="key">
					<label for="apartment"><?php echo JText::_( 'Apartment' ); ?>:</label>
				</td><td>
					<input type="text" name="apartment" id="apartment" size="25" maxlength="20" value="<?php echo $address['apartment']; ?>" <?php echo $readOnly; ?> />
				</td></tr>
			<tr><td align="right" class="key">
					<label for="name"><?php echo JText::_( 'Name' ); ?>:</label>
				</td><td>
					<input type="text" name="name" id="name" size="25" maxlength="20" value="<?php echo $address['name']; ?>" <?php echo $readOnly; ?> />
				</td></tr>
			<tr><td align="right" class="key">
					<label for="number"><?php echo JText::_( 'Number' ); ?>:</label>
				</td><td>
					<input type="text" name="number" id="number" size="25" maxlength="20" value="<?php echo $address['number']; ?>" <?php echo $readOnly; ?> />
				</td></tr>
			<tr><td align="right" class="key">
					<label for="number_range"><?php echo JText::_( 'Number Range' ); ?>:</label>
				</td><td>
					<input type="text" name="number_range" id="number_range" size="25" maxlength="20" value="<?php echo $address['number_range']; ?>" <?php echo $readOnly; ?> />
				</td></tr>
			<tr><td align="right" class="key">
					<label for="number_suffix"><?php echo JText::_( 'Number Suffix' ); ?>:</label>
				</td><td>
					<input type="text" name="number_suffix" id="number_suffix" size="25" maxlength="20" value="<?php echo $address['number_suffix']; ?>" <?php echo $readOnly; ?> />
				</td></tr>
			<tr><td align="right" class="key">
					<label for="street"><?php echo JText::_( 'Street' ); ?>:</label>
				</td><td>
					<input type="text" name="street" id="street" size="25" maxlength="20" value="<?php echo $address['street']; ?>" <?php echo $readOnly; ?> />
				</td></tr>
			<tr><td align="right" class="key">
					<label for="district"><?php echo JText::_( 'District' ); ?>:</label>
				</td><td>
					<input type="text" name="district" id="district" size="25" maxlength="20" value="<?php echo $address['district']; ?>" <?php echo $readOnly; ?> />
				</td></tr>
			<tr><td align="right" class="key">
					<label for="town"><?php echo JText::_( 'Town' ); ?>:</label>
				</td><td>
					<input type="text" name="town" id="town" size="25" maxlength="20" value="<?php echo $address['town']; ?>" <?php echo $readOnly; ?> />
				</td></tr>
			<tr><td align="right" class="key">
					<label for="county"><?php echo JText::_( 'County' ); ?>:</label>
				</td><td>
					<input type="text" name="county" id="county" size="25" maxlength="20" value="<?php echo $address['county']; ?>" <?php echo $readOnly; ?> />
				</td></tr>
			<tr><td align="right" class="key">
					<label for="administrative_area"><?php echo JText::_( 'Administrative Area' ); ?>:</label>
				</td><td>
					<input type="text" name="administrative_area" id="administrative_area" size="25" maxlength="20" value="<?php echo $address['administrative_area']; ?>" <?php echo $readOnly; ?> />
				</td></tr>
			<tr><td align="right" class="key">
					<label for="postcode"><?php echo JText::_( 'Postcode' ); ?>:</label>
				</td><td>
					<input type="text" name="postcode" id="postcode" size="25" maxlength="20" value="<?php echo $address['postcode']; ?>" <?php echo $readOnly; ?> />
				</td></tr>
		</table>
	</fieldset>
	</div>
	<div>
	<?php foreach( $relations as $relation ): ?>
	<fieldset class="adminform_thinfieldset" style="vertical-align: top;">
		<legend><?php echo $relation['description'].' '.JText::_( 'Details' ); ?></legend>
		<table class="admintable">
			<tr><td align="right" class="key">
					<label for="description"><?php echo JText::_( 'Description' ); ?>:</label>
				</td><td>
					<input type="text" name="description" id="description" size="25" maxlength="20" value="<?php echo $relation['description']; ?>" <?php echo $readOnly; ?> />
				</td></tr>
			<tr><td align="right" class="key">
					<label for="firstname"><?php echo JText::_( 'Legal Firstname' ); ?>:</label>
				</td><td>
					<input type="text" name="firstname" id="firstname" size="25" maxlength="20" value="<?php echo $relation['firstname']; ?>" <?php echo $readOnly; ?> />
				</td></tr>
			<tr><td align="right" class="key">
					<label for="surname"><?php echo JText::_( 'Legal Surname' ); ?>:</label>
				</td><td>
					<input type="text" name="surname" id="surname" size="25" maxlength="20" value="<?php echo $relation['surname']; ?>" <?php echo $readOnly; ?> />
				</td></tr>
			<tr><td align="right" class="key">
					<label for="preferred_firstname"><?php echo JText::_( 'Preferred Firstname' ); ?>:</label>
				</td><td>
					<input type="text" name="preferred_firstname" id="preferred_firstname" size="25" maxlength="20" value="<?php echo $relation['preferred_firstname']; ?>" <?php echo $readOnly; ?> />
				</td></tr>
			<tr><td align="right" class="key">
					<label for="preferred_surname"><?php echo JText::_( 'Preferred Surname' ); ?>:</label>
				</td><td>
					<input type="text" name="preferred_surname" id="preferred_surname" size="25" maxlength="20" value="<?php echo $relation['preferred_surname']; ?>" <?php echo $readOnly; ?> />
				</td></tr>
			<tr><td align="right" class="key">
					<label for="parental"><?php echo JText::_( 'Parental' ); ?>:</label>
				</td><td>
					<input type="checkbox" name="parental" id="parental" value="<?php echo $relation['parental']; ?>" <?php echo ( $relation['parental'] == 1 ) ? 'checked="checked"' : ''; ?> <?php echo $disabled; ?> />
				</td></tr>
			<tr><td align="right" class="key">
					<label for="legal_order"><?php echo JText::_( 'Legal Order' ); ?>:</label>
				</td><td>
					<input type="checkbox" name="legal_order" id="legal_order" value="<?php echo $relation['legal_order']; ?>" <?php echo ( $relation['legal_order'] == 1 ) ? 'checked="checked"' : ''; ?> <?php echo $disabled; ?> />
				</td></tr>
			<tr><td align="right" class="key">
					<label for="correspondence"><?php echo JText::_( 'Correspondance' ); ?>:</label>
				</td><td>
					<input type="checkbox" name="correspondence" id="correspondence" value="<?php echo $relation['correspondence']; ?>" <?php echo ( $relation['correspondence'] == 1 ) ? 'checked="checked"' : ''; ?> <?php echo $disabled; ?> />
				</td></tr>
			<tr><td align="right" class="key">
					<label for="reports"><?php echo JText::_( 'Reports' ); ?>:</label>
				</td><td>
					<input type="checkbox" name="reports" id="reports" value="<?php echo $relation['reports']; ?>" <?php echo ( $relation['reports'] == 1 ) ? 'checked="checked"' : ''; ?> <?php echo $disabled; ?> />
				</td></tr>
		</table>
	</fieldset>
	<?php endforeach; ?>
	</div>
	<fieldset class="adminform_thinfieldset" style="vertical-align: top;">
		<legend><?php echo JText::_( 'Global System Roles' ); ?></legend>
		<table class="adminlist">
			<thead>
				<tr>
					<th>Global System Roles</th>
					<th>Remove</th>
				</tr>
			</thead>
			<tbody id="roles_tbody">
				<tr id="arch_role_row">
					<td>_role_text_</td>
					<td style="text-align: center;">
						<?php echo JHTML::_( 'arc.image', 'remove-16', 'border="0" alt="Remove" title="Remove" class="remove_role" style="cursor: pointer;"' ); ?>
						<input type=hidden name="roles[]" value="_role_id_" />
					</td>
				</tr>
			</tbody>
		</table>
		<div id="role_list_div">
			<br />Add:
			<?php echo JHTML::_( 'admin_arc.roleList', 'unused_roles_list' ); ?>
		</div>
		<div style="display: none;">
			<select id="used_roles_list"></select>
		</div>
	</fieldset>
	<div>
	</div>
	<input type="hidden" name="option" value="com_arc_people" />
	<input type="hidden" name="view" value="people" />
	<input type="hidden" name="task" value="" />
	<input type="hidden" name="personIndex" value="<?php echo $this->personIndex; ?>" />
	<input type="hidden" name="existing_roles" value="<?php echo htmlspecialchars( json_encode($roles) ); ?>" />
</form>