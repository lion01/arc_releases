<?php
/**
 * @package     Arc
 * @subpackage  Planner
 * @copyright   Copyright (C) 2005 Punnet. All rights reserved. See COPYRIGHT_ARC.txt
 * @license     http://www.gnu.org/licenses/gpl.html GNU/GPL. See LICENSE_ARC.txt
 * Arc is free software: you can redistribute it and/or modify
 * it under the terms of version 3 of the GNU General Public License
 * as published by the Free Software Foundation.
 * The disclaimer of warranty as stated in the GPL applies to this program
 */

defined( '_JEXEC' ) or die( 'Restricted access' );

require_once( JPATH_ADMINISTRATOR.DS.'components'.DS.'com_arc_core'.DS.'libraries'.DS.'apoth_library.php' );

/*
 * Make sure the user is authorized to view this page
 */
$user = &ApotheosisLib::getUser();
if( !$user->authorize('com_installer', 'installer') ) {
	$mainframe->redirect( 'index.php', JText::_('ALERTNOTAUTH') );
}

require_once( JPATH_COMPONENT.DS.'controller.php' );

$taskMap = array(
	'default_task' => 'show'
);

$controller = new PlannerController( $taskMap );
$controller->execute( JRequest::getVar('task') );
$controller->redirect();
?>