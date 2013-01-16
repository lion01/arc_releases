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

// Give us access to the joomla model class
jimport( 'joomla.application.component.model' );

/**
 * Core Admin Permissions Model
 *
 * @author     Punnet - Arc Team
 * @package    Arc
 * @subpackage Core
 * @since      1.6
 */
class CoreAdminModelPermissions extends ArcAdminModel
{
	/** @var array Array of user roles */
	var $_roles = array();
	
	/** @var array Array of actions */
	var $_actions = array();
	
	function &getActions()
	{
		if (empty($this->_actions)) {
			// Load the actions
			$this->_loadActions();
		}
		return $this->_actions;
	}
	
	function _loadActions()
	{
		$this->_actions = array();
		$db = &JFactory::getDBO();
		
		$query = 'SELECT a.id, m.id AS menu_id, m.parent, a.option, a.task, a.params, a.name, a.menu_text, a.description, acl.* '
			."\n".'FROM #__apoth_sys_actions AS a'
			."\n".'LEFT JOIN jos_menu AS m'
			."\n".'  ON m.id = a.menu_id'
			."\n".' AND m.published = 1'
			."\n".'LEFT JOIN #__apoth_sys_acl AS acl ON acl.action = a.id';
		$db->setQuery($query);
		$rows = $db->loadObjectList();
		$rows = ApotheosisLibArray::sortTree( $rows, 'menu_id', 0, 'parent', 'name', 1, true);
		
		foreach($rows as $k=>$v) {
			$this->_actions[$v->id][$v->role] = $v;
		}
	}
	
	function &getRoles()
	{
		if (empty($this->_roles)) {
			// Load the roles
			$this->_loadRoles();
		}
		return $this->_roles;
	}
	
	function _loadRoles()
	{
		$db = &JFactory::getDBO();

		$query = 'SELECT *'
			."\n".' FROM '.$db->nameQuote( '#__apoth_sys_roles' )
			."\n".' ORDER BY '.$db->nameQuote('role').'';
		$db->setQuery($query);
		$this->_roles = $db->loadObjectList('id');
		$this->_roles = ApotheosisLibArray::sortTree( $this->_roles, 'id', 1, 'parent', 'role', 1, true);
	}
	
}
?>