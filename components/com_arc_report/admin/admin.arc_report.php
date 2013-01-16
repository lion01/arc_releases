<?php
/**
 * @package     Arc
 * @subpackage  Report
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

// Require specific controller if requested
$controllerEnd = JRequest::getWord( 'view' );
$path = JPATH_COMPONENT_ADMINISTRATOR.DS.'controllers'.DS.$controllerEnd.'.php';
if( file_exists($path) ) {
	require_once( $path );
}
else {
	$controllerEnd = 'settings';
	$path = JPATH_COMPONENT_ADMINISTRATOR.DS.'controllers'.DS.$controllerEnd.'.php';
	require_once( $path );
}

// Create the controller
$classname= 'ReportAdminController'.ucfirst($controllerEnd);
$controller = new $classname();

// Perform the requested task, if any
$controller->execute( JRequest::getCmd('task') );

// Redirect if set by the controller
$controller->redirect();
?>