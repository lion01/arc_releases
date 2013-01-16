<?php
/**
 * @package     Arc
 * @subpackage  Assessment
 * @copyright   Copyright (C) 2005 Punnet. All rights reserved. See COPYRIGHT_ARC.txt
 * @license     http://www.gnu.org/licenses/gpl.html GNU/GPL. See LICENSE_ARC.txt
 * Arc is free software: you can redistribute it and/or modify
 * it under the terms of version 3 of the GNU General Public License
 * as published by the Free Software Foundation.
 * The disclaimer of warranty as stated in the GPL applies to this program
 */

defined( '_JEXEC' ) or die( 'Restricted access' );

require_once (JPATH_ADMINISTRATOR.DS.'components'.DS.'com_arc_core'.DS.'libraries'.DS.'apoth_library.php');

/*
 * Make sure the user is authorized to view this page
 */
$user = &ApotheosisLib::getUser();
if (!$user->authorize('com_installer', 'installer')) {
	$mainframe->redirect('index.php', JText::_('ALERTNOTAUTH'));
}

$taskMap = array(
	'default_task' => 'show');

$controllerName = JRequest::getCmd( 'view', 'settings' );

switch($controllerName) {
	case'main':
	JSubMenuHelper::addEntry(JText::_('Settings'), 'index.php?option=com_arc_assessment&view=settings');
	break;
}

switch ($controllerName)
{
	default:
		$controllerName = 'settings';
	
	case 'settings' :
	case 'cycles':
	case 'statements':
	case 'mergefields':
	case 'pseudo':

		require_once( JPATH_COMPONENT.DS.'controllers'.DS.$controllerName.'.php' );
		$controllerName = 'AssessmentsController'.$controllerName;

		// Create the controller
		$controller = new $controllerName();

		// Perform the Request task
		$controller->execute( JRequest::getCmd('task') );

		// Redirect if set by the controller
		$controller->redirect();
		break;
}
?>