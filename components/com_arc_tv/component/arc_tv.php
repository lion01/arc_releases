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

// Require the base controller and the library
require_once( JPATH_SITE.DS.'components'.DS.'com_arc_core'.DS.'libraries'.DS.'apoth_library.php' ); 
require_once( JPATH_COMPONENT.DS.'controller.php' );

// TV component constants
define( 'ARC_TV_APPROVED'  , 1 );
define( 'ARC_TV_REJECTED'  , 2 );
define( 'ARC_TV_PENDING'   , 3 );
define( 'ARC_TV_INCOMPLETE', 4 );

// Require specific controller if requested
if( $controllerEnd = JRequest::getWord('view', 'video') ) {
	$path = JPATH_COMPONENT.DS.'controllers'.DS.$controllerEnd.'.php';
	if( file_exists($path) ) {
		require_once $path;
	}
	else {
		$controllerEnd = '';
	}
}

// Create the controller
$classname = 'TvController'.ucfirst( $controllerEnd );
$controller = new $classname();

// Perform the Request task
$controller->execute( JRequest::getCmd('task') );

// Redirect if set by the controller
$controller->redirect();
?>