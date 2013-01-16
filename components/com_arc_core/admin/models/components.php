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
 * Core Admin Components Model
 *
 * @author     Punnet - Arc Team
 * @package    Arc
 * @subpackage Core
 * @since      1.6
 */
class CoreAdminModelComponents extends ArcAdminModel
{
	/** @var array Array of installed components */
	var $_items = array();
	
	function &getItems()
	{
		if (empty($this->_items)) {
			// Load the items
			$this->_loadItems();
		}
		return $this->_items;
	}
	
	function _loadItems()
	{
		$apothId = ApotheosisLib::getComId();
		
		$db =& JFactory::getDBO();
		$query = 'SELECT *'
			."\n".'FROM '.$db->nameQuote( '#__components' )
			."\n".'WHERE '.$db->nameQuote( 'admin_menu_link' ).' LIKE '.$db->Quote( '%=com_arc_%' )
			."\n".'  AND '.$db->nameQuote( 'parent' ).' = 0'
			."\n".'ORDER BY '.$db->nameQuote( 'name' );
		$db->setQuery($query);
		$rows = $db->loadObjectList();

		$numRows = count($rows);
		$this->setState('pagination.total', $numRows);
		$this->_items = $rows;
	}
	
	/**
	 * Remove (uninstall) an extension
	 * *** Currently not accessible due to lack of checkboxes on component list
	 * *** also not recently tested so not to be blindly re-enabled
	 *
	 * @static
	 * @param	array	An array of identifiers
	 * @return	boolean	True on success
	 * @since 1.0
	 */
	function remove($eid=array())
	{
		global $mainframe;

		// Initialize variables
		$failed = array ();

		/*
		 * Ensure eid is an array of extension ids in the form id => client_id
		 * TODO: If it isn't an array do we want to set an error and fail?
		 */
		if (!is_array($eid)) {
			$eid = array($eid => 0);
		}

		// Get a database connector
		$db =& JFactory::getDBO();

		// Get an installer object for the extension type
		jimport('joomla.installer.installer');
		$installer = & JInstaller::getInstance();

		// Uninstall the chosen extensions
		foreach ($eid as $id => $clientId)
		{
			$id		= trim( $id );
			$result	= $installer->uninstall($this->_type, $id, $clientId );

			// Build an array of extensions that failed to uninstall
			if ($result === false) {
				$failed[] = $id;
			}
		}

		if (count($failed)) {
			// There was an error in uninstalling the package
			$msg = JText::sprintf('UNINSTALLEXT', $this->_type, JText::_('Error'));
			$result = false;
		} else {
			// Package uninstalled sucessfully
			$msg = JText::sprintf('UNINSTALLEXT', $this->_type, JText::_('Success'));
			$result = true;
		}

		$mainframe->enqueueMessage($msg);
		$this->setState('action', 'remove');
		$this->setState('message', $installer->message);

		return $result;
	}
}
?>