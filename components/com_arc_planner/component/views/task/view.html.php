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
 * Planner Task View
 */
class PlannerViewTask extends JView
{
	function __construct()
	{
		$document = &JFactory::getDocument();
		$document->setTitle( JText::_( 'Arc Planner Tasks' ) );
		
		$u = &ApotheosisLib::getUser();
		$this->userId = $u->person_id;
		
		parent::__construct();
	}
	
	/**
	 * Shows the main page containing all the tasks and their subtasks
	 * Gets the task trees from the model and puts each under its own category heading
	 */
	function tasks()
	{
		$this->model = &$this->getModel( 'task' );
		
		parent::display();
	}
	
	// #####  Interstitials for adding/editing various things  #####
	
	/**
	 * Edit a tasks properties (title, text1/2, dependancies, etc)
	 */
	function editTask()
	{
		
	}
	
	/**
	 * Edit a micro-task's properties (title, order, duration)
	 */
	function editMicroTask()
	{
		
	}
	
	/**
	 * Edit an assignment group (set due date and role assignments, show update controls if editing
	 */
	function editAssignmentGroup()
	{
		
	}
	
	/**
	 * Set the due date for a bunch of groups
	 */
	function editDates()
	{
		
	}
	
	/**
	 * Set the progress level for a bunch of groups
	 */
	function editProgress()
	{
		
	}
	
	/**
	 * Edit the roles for a bunch of groups (add/remove from all, remove from some)
	 */
	function editRoles()
	{
		
	}
	
	/**
	 * Edit 1 or more updates (set comment, progress, tick off micro-tasks, add evidence)
	 */
	function editUpdate()
	{
		$this->model = &$this->getModel( 'task' );
		$assignee = JRequest::getVar( 'assignee' );
		$pretty = JRequest::getVar( 'pretty' );
		$form = JRequest::getVar( 'form' );
		$this->dependancies = array(
			'planner.scope'=>JRequest::getVar( 'scope' ),
			'planner.tasks'=>$this->pTask->getId(),
			'planner.arc_people'=>$assignee,
			'planner.pretty'=>$pretty,
			'planner.form'=>$form,
			'core.actions'=>JRequest::getVar('invoker', false));
		
		reset( $this->tasks );
		$this->firstTaskObj = &$this->model->getTask( key($this->tasks) );
		if( $pretty && file_exists( $this->prettyTemplate ) ) {
			$tmpl = 'update_pretty';
		}
		else {
			//$form = 'update_rolling';
			$tmpl = 'update';
			switch( $form ) {
			case( 'update_single' ):
				$this->formPartTask = false;
				$this->formPartUpd = true;
				$this->formParentTitle = true;
				$this->formUpdateNumber = 's';
				$this->formUpdateEdit = true;
				$this->formUpdateExisting = ($this->givenUpdate !== false);
				$this->formUpdateNew = ($this->givenUpdate === false);
				$this->formProgress = true;
				$this->formComplete = false;
				$this->formEvidence = true;
				$this->formSaveFreq = '3';
				break;
				
			case( 'update_multi' ):
				$this->formPartTask = false;
				$this->formPartUpd = false;
				$this->formParentTitle = false;
				$this->formUpdateNumber = 'm';
				$this->formUpdateEdit = false;
				$this->formUpdateExisting = true;
				$this->formUpdateNew = false;
				$this->formProgress = false;
				$this->formComplete = false;
				$this->formEvidence = false;
				$this->formSaveFreq = '3';
				break;
				
			case( 'update_rolling' ):
				$this->formPartTask = false;
				$this->formPartUpd = true;
				$this->formParentTitle = true;
				$this->formUpdateNumber = 's';
				$this->formUpdateEdit = true;
				$this->formUpdateExisting = true;
				$this->formUpdateNew = true;
				$this->formProgress = false;
				$this->formComplete = true;
				$this->formEvidence = true;
				$this->formSaveFreq = '3';
				break;
				
			case( 'task_setting' ):
				$this->formPartTask = true;
				$this->formPartUpd = false;
				$this->formParentTitle = true;
				$this->formUpdateNumber = 's';
				$this->formUpdateEdit = true;
				$this->formUpdateExisting = false;
				$this->formUpdateNew = false;
				$this->formProgress = false;
				$this->formComplete = true;
				$this->formEvidence = true;
				$this->formSaveFreq = '3';
				break;
			}
		}
		
		$this->setLayout( 'edit' );
		parent::display( $tmpl );
		
/* *** micro task viewness 
 * not doing micro tasks for now
// set up microtasks (if any)
		$this->microTasks = array();
		foreach( $this->childTasks as $this->childId ) {
			$this->childTask = &$this->model->getTask( $this->childId );
			if( $this->childTask->getMicro() ) {
				$this->childGroup = &$this->childTask->getGroup( $this->groupId );
				if( $this->childGroup->getProgress() != 100 ) {
					$this->microTasks[$this->childTask->getId()] = &$this->childTask;
				}
			}
		}
		
		$this->isMicro = $this->task->getMicro();
// */
	}
	
	/**
	 * Export or delete updates for a bunch of groups
	 */
	function editUpdates()
	{
		
	}
	
	function addEvidence()
	{
		$this->model = &$this->getModel( 'task' );
		$pretty = JRequest::getVar( 'pretty' );
		$form = JRequest::getVar( 'form' );
		$this->dependancies = array(
			'planner.tasks'=>JRequest::getVar( 'parentId', false ),
			'planner.pretty'=>$pretty,
			'planner.form'=>$form);
		
		$this->taskId = JRequest::getVar( 'taskId', false );
		$this->groupId = JRequest::getVar( 'groupId', false );
		$this->updateId = JRequest::getVar( 'updateId', false );
		$this->task = &$this->model->getTask( $this->taskId );
		$this->group = &$this->task->getGroup( $this->groupId );
		$this->labels = $this->task->getLabels();
		
		$this->edit = false;
		if( !empty($this->updateId) ) {
			$this->edit = true;
			$this->update = &$this->group->getUpdate( $this->updateId );
			$this->evidence['file'] = &$this->update->getEvidence( 'file' );
			$this->evidence['url']  = &$this->update->getEvidence( 'url' );
		}
		
		$this->numEvidence = $this->task->getEvidenceNum();
		if( is_null($this->numEvidence) ) {
			$this->numEvidence = 5;
		}
		
		$this->setLayout( 'edit' );
		parent::display( 'evidence' );
	}
}
?>
