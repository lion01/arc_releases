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

require_once( JPATH_ADMINISTRATOR.DS.'components'.DS.'com_arc_core'.DS.'libraries'.DS.'apoth_library.php' );

/**
 * Post install tidy up
 * 
 * @param string $com  The component we are installing
 */
function arcCleanupInstall( $com = 'com_arc_core' )
{
	$arcPath = JPATH_ADMINISTRATOR.DS.'components'.DS.$com;
	$db = &JFactory::getDBO();

	// get the id of the component being installed
	$query = 'SELECT '.$db->nameQuote('id')
		."\n".'FROM '.$db->nameQuote('#__components')
		."\n".'WHERE '.$db->nameQuote('option').' = '.$db->Quote($com)
		."\n".'  AND '.$db->nameQuote('link').' != '.$db->Quote('');
	$db->setQuery( $query );
	$id = $db->loadResult();
	
	// get the current maximum ordering
	$query = 'SELECT MAX('.$db->nameQuote('ordering').')'
		."\n".'FROM '.$db->nameQuote('#__menu')
		."\n".'WHERE '.$db->nameQuote('menutype').' = '.$db->Quote('ArcMenu')
		."\n".'  AND '.$db->nameQuote('parent').' = '.$db->Quote('0');
	$db->setQuery( $query );
	$order = $db->loadResult();
	$order = !is_null( $order ) ? $order : 0; 
	
	// load the xml file
	$xml = new JSimpleXML();
	$xml->loadFile( $arcPath.DS.'metadata.xml' );
	
	// create a menu entry for every item in the xml
	foreach( $xml->document->item as $item ) {
		$order++;
		$success = arcInstallAddItem( $item, $order, $xml->document->attributes('type'), $id );
		if( $success === false ) {
			echo $db->stderr();
		}
	}
	
	// clean up the ancestry of the sys roles (new roles may have been added by the component)
	ApotheosisLibDb::updateAncestry( '#__apoth_sys_roles' );
}

/**
 * Adds a menu item to jos_menu
 * 
 * @param object $item  Menu object from parsed xml entry
 * @param string $order  Menu order to use
 * @param string $extType  Extension type
 * @param string $extId  ID of the extension
 * @param string $parent  ID of the parent menu we wish to set for this entry
 * @return boolean $retVal  An indication of the ongoing success of the install process
 */
function arcInstallAddItem( &$item, $order, $extType, $extId, $parent = 0 )
{
	// add the menu entry for this item
	$db = &JFactory::getDBO();
	
	$menu = new stdClass;
	$menu->menutype = 'ArcMenu';
	$menu->name = $item->text[0]->data();
	$menu->link = 'index.php?'.$item->link[0]->data();
	$menu->type = $extType;
	$menu->published = 1;
	$menu->parent = $parent;
	$menu->componentid = $extId;
	$menu->ordering = $order;
	if( isset($item->params) ) {
		$menu->params = $item->params[0]->data();
	}
	
	$retVal = $db->insertObject( '#__menu', $menu );
	$id = $db->insertId();
	
	// display the Arc Menu when showing this item
	$modMenu = new stdClass();
	$modMenu->moduleid = ApotheosisLib::getMenuModId();
	$modMenu->menuid = $id;
	
	$db->insertObject( '#__modules_menu', $modMenu );
	
	// add the sys_action(s) for this item if any exist
	if( isset($item->actions) ) {
		$retVal = arcInstallAddActions( $id, $item->actions[0] ) && $retVal;
	}
	
	// deal with submenu items recursively
	if( isset($item->submenu) ) {
		$suborder = 0;
		foreach( $item->submenu[0]->item as $subItem ) {
			$retVal = arcInstallAddItem( $subItem, $suborder, $extType, $extId, $id ) && $retVal;
			$suborder++;
		}
	}
	
	return $retVal;
}

/**
 * Adds any specified actions to the apoth_sys_actions table
 * 
 * @param string $menuId  Menu ID
 * @param array $actions  array of actions for a given item
 * @return boolean $retVal  An indication of the ongoing success of the install process
 */
function arcInstallAddActions( $menuId, $actions )
{
	$db = &JFactory::getDBO();
	
	foreach( $actions->children() as $action ) {
		$sysAction = new stdClass;
		$sysAction->menu_id = $menuId;
		if( isset($action->option) ) {
			$sysAction->option = $action->option[0]->data();
		}
		if( isset($action->task) ) {
			$sysAction->task = $action->task[0]->data();
		}
		if( isset($action->params) ) {
			$sysAction->params = $action->params[0]->data();
		}
		$sysAction->name = $action->name[0]->data();
		$sysAction->menu_text = $action->menu_text[0]->data();
		$sysAction->description = $action->description[0]->data();
		
		$retVal = $db->insertObject( '#__apoth_sys_actions', $sysAction );
		$id = $db->insertId();
	}
	
	return $retVal; 
}
?>