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

require_once (JPATH_ADMINISTRATOR.DS.'components'.DS.'com_arc_core'.DS.'libraries'.DS.'apoth_library.php');
require_once (JPATH_ADMINISTRATOR.DS.'components'.DS.'com_arc_core'.DS.'libraries'.DS.'installation.php');

function com_install()
{
	$db = &JFactory::getDBO();
	
	// set up the Arc Menu type
	$menuType = new stdClass;
	$menuType->menutype = 'ArcMenu';
	$menuType->title = 'Arc';
	$menuType->description = 'The menu for the Arc system';
	
	$db->insertObject( '#__menu_types', $menuType );
	
	// remove the entry that assigns the menu module to every menu item
	$menuModId = ApotheosisLib::getMenuModId();
	$query = 'DELETE FROM '.$db->nameQuote('#__modules_menu')
		."\n".'WHERE '.$db->nameQuote('moduleid').' = '.$db->Quote($menuModId);
	$db->setQuery( $query );
	$db->query();
	
	// add the root Arc Menu entry
	$menu = new stdClass;
	$menu->menutype = 'mainmenu';
	$menu->name = 'Arc';
	$menu->link = 'index.php?option=com_arc_core';
	$menu->type = 'component';
	$menu->published = 1;
	$menu->componentid = ApotheosisLib::getComId();
	
	$db->insertObject( '#__menu', $menu );
	$menuId = $db->insertid();
	
	// display the Arc Menu when showing this item
	$modMenu = new stdClass();
	$modMenu->moduleid = $menuModId;
	$modMenu->menuid = $menuId;
	
	$db->insertObject( '#__modules_menu', $modMenu );
	
	// call cleanup script for the remainder of the component as usual
	arcCleanupInstall( 'com_arc_core' );
}
?>
