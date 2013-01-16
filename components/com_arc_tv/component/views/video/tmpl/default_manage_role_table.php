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

$roleArray = array();
foreach( $this->curVideo->getRoles() as $roleInfo ) {
	if( !isset($roleInfo['person_name']) ) {
		$roleInfo['person_name'] = $this->model->getDisplayName( $roleInfo['site_id'], $roleInfo['person_id'] );
	}
	$roleArray[] = $roleInfo;
}
?>
<input id="manage_roles_site_id" type="hidden" value="<?php echo $this->siteId; ?>" />
<input id="manage_roles_input" name="manage_roles_input" type="hidden" value="<?php echo htmlspecialchars( json_encode($roleArray) ); ?>" />
<table id="manage_form_roles_table">
	<thead>
		<tr>
			<th>Role</th>
			<th>Name</th>
			<th>&nbsp;</th>
		</tr>
	</thead>
	<tbody id="manage_form_roles_tbody">
	</tbody>
</table>