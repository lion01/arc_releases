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

/**
 * Planner Ajax Controller
 */
class PlannerControllerAjax extends PlannerController
{
	function display()
	{
		$view =  &$this->getView( 'ajax', 'xml' );
		
		switch( JRequest::getVar('scope') ) {
		case('tasks') :
			$view->tasks();
		break;
		
		case('pupils') :
			$view->pupils();
		break;
		
		case('staff') :
			$view->staff();
		break;
		
		case('removeMentors') :
			$view->removeMentors();
		break;
		
		case('removeMentees') :
			$view->removeMentees();
		break;
		
		case('assignPeople') :
			$view->assignPeople();
		break;
		
		case('removeTasks') :
			$view->removeTasks();
		break;
		
		case('addTasks') :
			$view->addTasks();
		break;		

		case('mentees') :
			$view->mentees();
		break;
		
		case('allTasks') :
			$view->allTasks();
		break;
		}
		
	}
}	