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

// Require the base controller and the library
require_once( JPATH_SITE.DS.'components'.DS.'com_arc_core'.DS.'libraries'.DS.'apoth_library.php' ); 
require_once( JPATH_COMPONENT.DS.'controller.php' );

/*
echo 'view: "'.  JRequest::getCmd('view')  .'"<br />'."\n";
echo 'task: "'.  JRequest::getCmd('task')  .'"<br />'."\n";
echo 'scope: "'. JRequest::getCmd('scope') .'"<br />'."\n";
echo 'format: "'.JRequest::getCmd('format').'"<br />'."\n";
// */

define( 'ARC_REPORT_CRUMB_TRAIL', 'com_arc_report' );
define( 'ARC_REPORT_STATUS_NASCENT',    1 ); // These correspond to preset db values
define( 'ARC_REPORT_STATUS_INCOMPLETE', 2 );
define( 'ARC_REPORT_STATUS_SUBMITTED',  3 );
define( 'ARC_REPORT_STATUS_REJECTED',   4 );
define( 'ARC_REPORT_STATUS_APPROVED',   5 );

// Require specific controller
$controllerEnd = JRequest::getWord('view', 'home');
$path = JPATH_COMPONENT.DS.'controllers'.DS.$controllerEnd.'.php';
if( file_exists($path) ) {
	require_once $path;
}
else {
	$controllerEnd = '';
}

// Create the controller
$classname= 'ReportController'.ucfirst($controllerEnd);
$controller = new $classname();

// Perform the Request task
$controller->execute( JRequest::getCmd('task') );

// Redirect if set by the controller
$controller->redirect();
?>