<?php
/**
 * @package     Arc
 * @subpackage  Module_Context
 * @copyright   Copyright (C) 2005 Punnet. All rights reserved. See COPYRIGHT_ARC.txt
 * @license     http://www.gnu.org/licenses/gpl.html GNU/GPL. See LICENSE_ARC.txt
 * Arc is free software: you can redistribute it and/or modify
 * it under the terms of version 3 of the GNU General Public License
 * as published by the Free Software Foundation.
 * The disclaimer of warranty as stated in the GPL applies to this program
 */

defined( '_JEXEC' ) or die( 'Restricted access' );

// Include the context helper file
require_once( JPATH_SITE.DS.'modules'.DS.'mod_arc_context'.DS.'helper.php' );

$action = ApotheosisLib::getActionId();

if( !is_null( $action ) ) {
	$help = modArcContextHelper::getHelp( $action );
	$links = modArcContextHelper::getLinks( $action );
	
	require( JModuleHelper::getLayoutPath('mod_arc_context') );
}
?>