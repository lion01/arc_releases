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

jimport( 'joomla.application.component.view' );

/**
 * Planner Ajax View
 */
class PlannerViewAjax extends JView
{
	function pupils()
	{
		$pid = JRequest::getVar( 'personId', null );
		echo '<?xml version="1.0"?>'.JHTML::_( 'arc_people.plannerPeople', 'assignedMentees', 'assignedMentees', $pid, null, true );
	}

	function staff()
	{
		$pid = JRequest::getVar( 'personId', null );
		$name = JRequest::getVar( 'name', 'assignedMentees' );
		if($name == 'assignedMenteesStaff') {
			echo '<?xml version="1.0"?>'.JHTML::_( 'arc_people.plannerPeople', 'assignedMentees', 'assignedMenteesStaff', $pid, null, true );			
		}
		else{
			echo '<?xml version="1.0"?>'.JHTML::_( 'arc_people.plannerPeople', 'assignedMentors', 'assignedMentors', $pid, null, true );
		}
	}
	
	function mentees()
	{
		$list = JRequest::getVar( 'list', null );
		if( $list == 'pm' ) {
			echo '<?xml version="1.0"?>'.JHTML::_( 'arc_people.plannerPeople', 'mentees', 'teachingStaff', null, null, true );
		}
		else {
			echo '<?xml version="1.0"?>'.JHTML::_( 'arc_people.plannerPeople', 'mentees', 'pupils', null, null, true );
		}
	}

	function tasks()
	{
		$taskIds = unserialize( JRequest::getVar('taskIds') );
		if( !is_array($taskIds) ) {
			$taskIds = array();
		}		
		$ids['taskIds'] = $taskIds;
		
		$assIds = unserialize( JRequest::getVar('assigneeIds') );
		if( !is_array($assIds) ) {
			$assIds = array();
		}		
		$ids['assIds'] = $assIds;
		
		$adIds = unserialize( JRequest::getVar('adminIds') );
		if( !is_array($adIds) ) {
			$adIds = array();
		}		
		$ids['adIds'] = $adIds;

		$inputName = JRequest::getVar( 'inputName' );
		echo '<?xml version="1.0"?>'.JHTML::_( 'arc_planner.assignedTasks', $inputName, $ids, null, true );
	}


	function allTasks()
	{		
		$assIds = unserialize( JRequest::getVar('assigneeIds') );
		if( !is_array($assIds) ) {
			$assIds = array();
		}		
		$ids['assIds'] = $assIds;

		$inputName = JRequest::getVar( 'inputName' );

		echo '<?xml version="1.0"?>'.JHTML::_( 'arc_planner.tasks', $inputName, $ids, null, true );
	}


	function removeMentors()
	{		
		$adIds = unserialize( JRequest::getVar( 'adminIds' ) );
		if( !is_array($adIds) ) {
			$adIds = array();
		}
		$ids['adIds'] = $adIds;

		$assIds = unserialize( JRequest::getVar( 'assigneeIds' ) );
		if( !is_array($assIds) ) {
			$assIds = array();
		}		
		$ids['assIds'] = $assIds;

		$inputName = JRequest::getVar( 'inputName' );
		echo '<?xml version="1.0"?>'.JHTML::_( 'arc_planner.removeMentors', $inputName, $ids, null, true );
	}
	
	function removeMentees()
	{
		$assIds = unserialize( JRequest::getVar('assigneeIds') );
		if( !is_array($assIds) ) {
			$assIds = array();
		}		
		$ids['assIds'] = $assIds;
		$ids['adIds'] = JRequest::getVar( 'adminIds', array() );
		$inputName = JRequest::getVar( 'inputName' );
		echo '<?xml version="1.0"?>'.JHTML::_( 'arc_planner.removeMentees', $inputName, $ids, null, true );
	}

	function assignPeople()
	{
		$list = JRequest::getVar('list');

		$taskIds = unserialize( JRequest::getVar('taskIds') );
		if( !is_array($taskIds) ) {
			$taskIds = array();
		}		
		$ids['taskIds'] = $taskIds;
		
		$assIds = unserialize( JRequest::getVar('mentees') );
		if( !is_array($assIds) ) {
			$assIds = array();
		}		
		$ids['assIds'] = $assIds;
		
		$adIds = unserialize( JRequest::getVar('mentors') );
		if( !is_array($adIds) ) {
			$adIds = array();
		}		
		$ids['adIds'] = $adIds;

		$inputName = JRequest::getVar( 'inputName' );
		echo '<?xml version="1.0"?>'.JHTML::_( 'arc_planner.assignPeople', $inputName, $ids, null, true, $list );
	}

	function removeTasks()
	{
		$assIds = unserialize( JRequest::getVar('assigneeIds') );
		if( !is_array($assIds) ) {
			$assIds = array();
		}		
		$ids['assIds'] = $assIds;

		$adIds = unserialize( JRequest::getVar('adminIds') );
		if( !is_array($adIds) ) {
			$adIds = array();
		}		
		$ids['adIds'] = $adIds;

		$taskIds = unserialize( JRequest::getVar('taskIds') );
		if( !is_array($taskIds) ) {
			$taskIds = array();
		}		
		$ids['taskIds'] = $taskIds;
		
		$inputName = JRequest::getVar( 'inputName' );
		echo '<?xml version="1.0"?>'.JHTML::_( 'arc_planner.removeTasks', $inputName, $ids, null, true );
	}
	
	function addTasks()
	{
		$taskIds = unserialize( JRequest::getVar('taskIds') );
		if( !is_array($taskIds) ) {
			$taskIds = array();
		}		
		$ids['taskIds'] = $taskIds;
		
		$assIds = unserialize( JRequest::getVar('mentees') );
		if( !is_array($assIds) ) {
			$assIds = array();
		}		
		$ids['assIds'] = $assIds;
		
		$adIds = unserialize( JRequest::getVar('mentors') );
		if( !is_array($adIds) ) {
			$adIds = array();
		}		
		$ids['adIds'] = $adIds;

		$inputName = JRequest::getVar( 'inputName' );
		echo '<?xml version="1.0"?>'.JHTML::_( 'arc_planner.addTasks', $inputName, $ids, null, true );
	}

}