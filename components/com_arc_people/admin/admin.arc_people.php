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

// Give us access to the core admin libraries
require_once( JPATH_ADMINISTRATOR.DS.'components'.DS.'com_arc_core'.DS.'libraries'.DS.'apoth_library.php' );

// Give us access to the root admin controller for this component
require_once( JPATH_COMPONENT_ADMINISTRATOR.DS.'controller.php' );

// Give us access to any objects for this component
require_once( JPATH_COMPONENT_ADMINISTRATOR.DS.'models'.DS.'objects.php' );

// Require specific controller if requested
$controllerEnd = JRequest::getWord( 'view' );
$path = JPATH_COMPONENT_ADMINISTRATOR.DS.'controllers'.DS.$controllerEnd.'.php';
if( file_exists($path) ) {
	require_once( $path );
}
else {
	$controllerEnd = 'people';
	$path = JPATH_COMPONENT_ADMINISTRATOR.DS.'controllers'.DS.$controllerEnd.'.php';
	require_once( $path );
}

// Create the controller
$classname= 'PeopleAdminController'.ucfirst($controllerEnd);
$controller = new $classname();

// Perform the requested task, if any
$controller->execute( JRequest::getCmd('task') );

// Redirect if set by the controller
$controller->redirect();
?>