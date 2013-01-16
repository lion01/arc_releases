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

// no direct access
defined( '_JEXEC' ) or die( 'Direct Access to this location is not allowed.' );

// Require the com_content helper library
require_once( JPATH_COMPONENT.DS.'libraries'.DS.'apoth_library.php' );
require_once( JPATH_COMPONENT.DS.'controller.php' );

// Require specific controller if requested
if( $controllerEnd = JRequest::getWord( 'view' ) ) {
	$path = JPATH_COMPONENT.DS.'controllers'.DS.$controllerEnd.'.php';
	if( file_exists($path) ) {
		require_once $path;
	}
	else {
		$controllerEnd = 'favourites';
		$path = JPATH_COMPONENT.DS.'controllers'.DS.$controllerEnd.'.php';
		require_once $path;
	}
}

// Create the controller
$classname = 'ApotheosisController'.ucfirst( $controllerEnd );
$controller = new $classname();

// Perform the Request task
$controller->execute( JRequest::getCmd('task') );

// Redirect if set by the controller
$controller->redirect();
?>