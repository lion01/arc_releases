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

/**
 * Uninstall the given component
 * 
 * @param string $com  The component to uninstall
 */
function arcPrepareUninstall( $com = 'com_arc_core' )
{
	// get the id of the component being uninstalled
	$componentId = ApotheosisLib::getComId( $com );
	
	// remove menu items for the given component
	arcUninstallRemoveItem( $componentId );
}

/**
 * Remove menu items and sys actions for the given component
 * 
 * @param string $comId  ID of the component whose menu items we want to uninstall
 * @return boolean $retVal  An indication of the ongoing success of the uninstall process
 */
function arcUninstallRemoveItem( $comId )
{
	$db = &JFactory::getDBO();
	
	// get all the menu ids for items in the given component
	$query = 'SELECT '.$db->nameQuote('id')
		."\n".'FROM '.$db->nameQuote('#__menu')
		."\n".'WHERE '.$db->nameQuote('componentid').' = '.$db->Quote($comId);
	$db->setQuery( $query );
	$menuIds = $db->loadResultArray();
	
	// db quote the list of menu ids
	foreach( $menuIds as $k=>$menuId ) {
		$menuIds[$k] = $db->Quote( $menuId );
	}
	$menuIds = implode( ', ', $menuIds );
	
	// remove all relevant modules_menu assignments
	$query = 'DELETE FROM '.$db->nameQuote('#__modules_menu')
		."\n".'WHERE '.$db->nameQuote('menuid').' IN ('.$menuIds.')';
	$db->setQuery( $query );
	$db->query();
	
	// remove all relevant menu items
	$query = 'DELETE FROM '.$db->nameQuote('#__menu')
		."\n".'WHERE '.$db->nameQuote('id').' IN ('.$menuIds.')';
	$db->setQuery( $query );
	$db->query();
	
	// remove all apoth_sys_actions entries
	$query = 'DELETE FROM '.$db->nameQuote('#__apoth_sys_actions')
		."\n".' WHERE '.$db->nameQuote('menu_id').' IN ('.$menuIds.')';
	$db->setQuery( $query );
	$db->query();
}
?>