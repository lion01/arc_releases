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

require_once(JPATH_SITE.DS.'components'.DS.'com_arc_people'.DS.'helpers'.DS.'data_access.php');

/**
 * Planner Task Controller
 */
class PlannerControllerTask extends PlannerController
{
	/**
	 * Constructor
	 */
	function __construct( $config = array() )
	{
		parent::__construct( $config );
		// Register extra tasks
		$this->registerTask( 'toggleDetails',      'toggle');
		$this->registerTask( 'toggleManyDetails',  'toggle');
		$this->registerTask( 'toggleSubtasks',     'toggle');
		$this->registerTask( 'toggleManySubtasks', 'toggle');
	}
	
	/**
	 * Displays any of the single-task views depending on the scope specified
	 * by calling the relevant function in the view
	 */
	function display()
	{
		$model = &$this->getModel( 'task' );
		$view  = &$this->getView ( 'task', JRequest::getWord('format', 'html') );
		$view->setModel( $model );
		$view->link = $this->_getLink( array('option'=>'com_arc_planner', 'view'=>'task') );
		
		$scope = JRequest::getWord('scope');
		
		// Legacy support
		// These 2 cases (interUpdate and pageUpdate) will become obsolete
		// once the editUpdate + flags approach is adopted across the rest of planner
		// maybe that form will even grow to encompass addEvidence too 
		switch( $scope ) {
		case( 'interUpdate' ):
			JRequest::setVar( 'pretty', false );
			JRequest::setVar( 'form', 'update_single' );
			$scope = 'editUpdate';
			break;
			
		case( 'pageUpdate' ):
			JRequest::setVar( 'pretty', true );
			JRequest::setVar( 'form', 'update_single' );
			$scope = 'editUpdate';
			break;
		}
		
		switch( $scope ) {
		case( 'editUpdate' ):
		case( 'editTask' ):
			$u = &JFactory::getUser();
			if( $parentId = JRequest::getVar( 'parentId', false ) ) {
				JRequest::setVar( 'taskId', $parentId, 'post' );
				JRequest::setVar( 'personId', $u->person_id, 'post' );
				// *** note this does not load from cache generated by panel
				// this is because the panel is generated under admin user, this page is by actual user
				$pTask = &$model->getTask( $parentId );
				$pTask->setGroupRequirements( array('taskId'=>$parentId, 'member'=>$u->person_id) );
				$view->prettyTemplate = $pTask->getTemplate( true );
				$tasks = $pTask->getLinkedTasks( array( 'type'=>'children', 'member'=>$u->person_id ) );
				$view->pTask = &$model->getTask( $parentId );
			}
			elseif( $taskId = JRequest::getVar( 'taskId', false ) ) {
				$tasks = array( $taskId );
				$t = &$model->getTask( $taskId );
				$view->pTask = &$model->getTask( $t->getParent() );
				$view->prettyTemplate = $t->getTemplate( true );
			}
			
			$groupReq = array('members'=>$u->person_id );
			$assignee = JRequest::getVar( 'assignee' );
			if( !empty($assignee) ) { $groupReq['assignees'] = $assignee; }
			$view->givenUpdate = $givenUpdate = JRequest::getVar( 'updateId', false );
			$view->tasks = array();
			foreach( $tasks as $tId ) {
				$taskObj = &$model->getTask( $tId );
				$groupReq['taskId'] = $tId;
				$taskObj->setGroupRequirements( $groupReq );
				$groupObjs = $taskObj->getGroups();
				foreach( $groupObjs as $gId=>$gObj ) {
					if( $givenUpdate === false ) {
						$view->tasks[$tId][$gId] = array();
					}
					$updateCats = $gObj->getUpdates(true);
					foreach( $updateCats as $cId=>$updateObjs ) {
						if( $givenUpdate === false ) {
							$view->tasks[$tId][$gId][$cId] = array();
						}
						foreach( $updateObjs as $uId=>$uObj ) {
							if( ($givenUpdate === false) || ($uId == $givenUpdate) ) {
								$view->tasks[$tId][$gId][$cId][$uId] = $uId;
							}
						}
					}
				}
			}
			$view->editUpdate();
			break;
		
		case( 'addEvidence' ):
			$view->addEvidence();
			break;
		
		case( 'panel' ):
			$view->panel();
			break;
		
		default:
			$view->tasks();
		}
		
		$this->saveModel( 'task' );
	}
	
	/**
	 * Uses the data submitted via the search form to set the model's collection of tasks
	 * Then calls display() to show the retrieved tasks
	 */
	function search()
	{
		$model = &$this->getModel( 'task' );
		$taskId = JRequest::getVar( 'taskId', '' );
		
		if( (JRequest::getVar('scope') != 'panel')
		 && (JRequest::getVar('format') != 'xml') ){
			// *** we must be in dev-land then
//			$requirements = array('taskId'=>array(9, 10, 15, 16, 17));
//			$requirements = array('taskId'=>array(6, 7, 8, 9, 10));
			$requirements = array('parentId'=>array(20, 70));
			$model->setTasks( $requirements, true );
			$this->display();
			return;
		}
		
		if( JRequest::getWord('scope') == 'panel' ) {
			$requirements = array();
			$requirements['special'] = 1;
			$requirements['assignees'] = JRequest::getVar( 'pId', null );
			$requirements['asPerson'] = $requirements['assignees'];
		}
		else {
			$requirements['taskId'] = $taskId;
			$requirements['descendants'] = JRequest::getVar( 'descendants', false );
			$requirements['personId'] = JRequest::getVar( 'personId', false );
			
			foreach( $requirements as $k=>$v ) {
				if( $v === false ) {
					unset( $requirements[$k] );
				}
			}
		}
		$model->setTasks( $requirements, true );
		$this->display();
	}
	
	function refreshList()
	{
		$model = &$this->getModel( 'task' );
		$model->reloadAll();
		$this->display();
	}
	
	function toggle()
	{
		// put task mapping stuffs in the constructor so all toggle functions come through to this.
		// all the same stuff
		$model = &$this->getModel( 'task' );
		$task = JRequest::getVar('task');
		$taskId = (int)JRequest::getVar( 'taskId', false );
		
		if( $taskId !== false ) {
			$model->$task( $taskId );
		}
		
		$this->display();
	}
	
	/**
	 * Loads all subtasks (right down to leaf nodes) and expands all subtask sections
	 * Calls display to re-render the page
	 * // *** stub function, never implemented
	 */
	function showAllSubtasks()
	{
		$model = &$this->getModel( 'task' );
		
		$this->display();
	}
	
	/**
	 * Changes whether the parent for a task is displayed
	 * Calls display to re-render the page
	 * // *** stub function, never implemented
	 */
	function toggleParent()
	{
		$model = &$this->getModel( 'task' );
		
		$this->display();
	}
	
	/**
	 * Changes whether the updates section for an assignment group is displayed
	 * Calls display to re-render the page
	 */
	function toggleUpdates()
	{
		$model = &$this->getModel( 'task' );
		$taskId = (int)JRequest::getVar( 'taskId', false );
		$groupId = (int)JRequest::getVar( 'groupId', false );
		
		if( ($taskId !== false) && ($groupId !== false) ) {
			$model->toggleUpdates( $taskId, $groupId );
		}
		$this->display();
	}
	
	// #####  Data update functions  #####
	
	/**
	 * Adds or saves changes to a task which has been defined by the user in the
	 * "add/edit task" interstitial then calls _save to do the donkey work then
	 * redirects to show the task in the interstitial with appropriate message
	 */
	function editTask()
	{
		
	}
	
	/**
	 * Adds or saves changes to an assignment group which has been defined by the user in the
	 * "add/edit group" interstitial calls _save to do the donkey work then
	 * redirects to show the group in the interstitial with appropriate message
	 */
	function editGroup()
	{
		
	}
	
	/**
	 * Redirects to appropriate save function from multi update / task setting form
	 */
	function saveMulti()
	{
		global $mainframe;
		ob_start();
		$model = &$this->getModel( 'task' );
		$ok = true;
		
		// sort updates, if any
		$this->multiUpdates = JRequest::getVar( 'updates', array() );
		if( !empty($this->multiUpdates) ) {
			$ok = $this->saveUpdate() && $ok;
		}
		
		// sort tasks, if any
		$this->multiTasks = JRequest::getVar( 'tasks', array() );
		if( !empty($this->multiTasks) ) {
			if( empty($this->multiTasks['title']) ) {
				$mainframe->enqueueMessage( 'Unable to save task as no title given', 'error' );
			}
			else {
				$ok = $this->saveTask() && $ok;
				if( $ok && ( empty($this->multiTasks['text_1']) ||  empty($this->multiTasks['text_2']) ) ) {
					$mainframe->enqueueMessage( 'Task saved but has no data for one or more fields', 'warning' );
				}
			}
		}
		
		$this->saveModel( 'task' );
		$model = &$this->getModel( 'task' );
		$parentTask = &$model->getTask( JRequest::getVar('parent_task_id', false) );
		$user = &ApotheosisLib::getUser();
		$JUserId = $user->id;
		$personId = $user->person_id;
		$labels = $parentTask->getLabels();
		
		// if 'All targets set' button has been clicked..
		if( JRequest::getVar('submit') == $labels['task_demote'] ) {
			// *** here again we pick out all groups this person is a member for
			// *** not just those they are group leader of
			// *** so this surrenders ALL group leaderships for the parent task
			$parentTaskGroups = $parentTask->getGroups();
			foreach( $parentTaskGroups as $groupId=>$groupObj ) {
				$personRoles = $groupObj->roles( $personId );
				if( $personRoles['leader'] === true) {
					$groupObj->updatePersonRole( $personId, 'leader', 'assignee' );
					$groupObj->commit(); // this refreshes the permission tables
				}
			}
			// ... and its child tasks
			$childTasks = $parentTask->getLinkedTasks( array( 'type'=>'children' ) );
			foreach( $childTasks as $taskId ) {
				$task = &$model->getTask( $taskId );
				$groups = $task->getGroups();
				foreach( $groups as $groupId=>$groupObj ) {
					$personRoles = $groupObj->roles( $personId );
					if( $personRoles['leader'] === true) {
						$groupObj->updatePersonRole( $personId, 'leader', 'assignee' );
						$groupObj->commit(); // this refreshes the permission tables
					}
				}
			}
		}
		
		$assignee = JRequest::getVar( 'assignee' );
		$link = $parentTask->getUrl( JRequest::getVar( 'invoker' ), $JUserId, $assignee, true );
		$msg = ob_get_clean();
		$mainframe->redirect( $link, $msg );
	}
	
	/**
	 * Saves updates from the new multi update / task setting form
	 * Then redirects to the form again (carrying dependancy values through to get same form layout)
	 * @return array $msg  messages to show on subsequent screen
	 */
	function saveUpdate()
	{
		global $mainframe;
		$allFiles = JRequest::get('files');
		$model = &$this->getModel( 'task' );
		$retVal = true;
		
		$p = JRequest::getVar( 'pId', false );
		if( $p !== false ) {
			$personId = $p;
		}
		else {
			$user = &ApotheosisLib::getUser();
			$personId = $user->person_id;
		}
		
		$rolling = (stristr( JRequest::getVar('form'), 'rolling' ) !== false );
		
		foreach($this->multiUpdates as $taskId=>$groupList) {
			$taskObj = &$model->getTask($taskId);
			if( !is_object($taskObj) ) {
				$mainframe->enqueueMessage( 'Could not instanciate task object '.$taskId, 'warning' );
				$retVal = false;
				continue;
			}
			
			foreach($groupList as $groupId=>$updateList) {
				$groupObj = &$taskObj->getGroup($groupId);
				if( !is_object($groupObj) ) {
					$mainframe->enqueueMessage( 'Could not instanciate group object '.$groupId, 'warning' );
					$retVal = false;
					continue;
				}
				
				foreach($updateList as $updateId=>$updateData) {
//					var_dump_pre($updateData, $taskId.'-'.$groupId.'-'.$updateId.'\'s data');
					
					// Prepare core data
					$complete = ( isset($updateData['complete']) );
					
					$data['group_id'] = $groupId;
					$data['category'] = $updateData['category'];
					$data['text'] = $updateData['text'];
					if( is_array($data['text']) ) {
						$data['text'] = implode( ';', $data['text'] );
					}
					$data['progress'] = ( $complete ? 100 : (int)$updateData['progress'] );
					$data['author'] = $personId;
					
					// Prepare files
					$files['evidence_file']['name']     = $allFiles['updates']['name'    ][$taskId][$groupId][$updateId]['evidence_file'];
					$files['evidence_file']['tmp_name'] = $allFiles['updates']['tmp_name'][$taskId][$groupId][$updateId]['evidence_file'];
					$files['evidence_file']['error']    = $allFiles['updates']['error'   ][$taskId][$groupId][$updateId]['evidence_file'];
					JRequest::set( $files, 'files');
					$evidenceUrl = array();
					$evidenceFile = array();
					$evidenceUrlRaw = $updateData['evidence_url'];
					$evidenceFileRaw = JRequest::getVar( 'evidence_file', array('name'=>array()), 'files' );
					$this->_getEvidence( $evidenceUrl, $evidenceFile, $evidenceUrlRaw, $evidenceFileRaw, $personId );
					
					// work out if we need to do anything, then do it
					if( $rolling ) {
						$oldUpdate = $groupObj->getUpdate( $updateId );
						if( !is_object($oldUpdate) ) {
							if( $groupObj->getUpdatesCount() != 0 ) {
								continue;
							}
							else {
								$oldUpdate = new ApothUpdate();
							}
						}
						if( ($oldUpdate->getText() == $data['text'])
						 && ($oldUpdate->getProgress() == $data['progress'])
						 && empty($evidenceUrl)
						 && empty($evidenceFile) ) {
							continue; // don't create new updates if nothing has changed
						}
						$updateObj = new ApothUpdate();
						$new = true;
						$added++;
						$mainframe->enqueueMessage( 'Comments saved' );
					}
					elseif( substr($updateId, 0, 4) == 'new_' ) {
						if( empty($data['text'])
						 && empty($data['progress'])
						 && empty($evidenceUrl)
						 && empty($evidenceFile) ) {
							continue; // don't create new updates if nothing has been set
						}
						$updateObj = new ApothUpdate();
						$new = true;
						$added++;
						$mainframe->enqueueMessage( 'New update saved' );
					}
					else {
						$updateObj = &$groupObj->getUpdate( $updateId );
						if( !is_object($updateObj) ) {
							continue; // don't error if we can't find the update to modify
						}
						$data['id'] = $updateId;
						$new = false;
						$editted++;
						$mainframe->enqueueMessage( 'Update successfully edited' );
					}
					$updateObj->setCoreData( $data );
					$updateObj->addEvidence( $evidenceUrl, $evidenceFile, $personId );
					
					// Commit all changes
					$updateObj->commit();
					
					if( $new ) {
						$groupObj->addUpdate( $updateObj );
					}
					$groupObj->setComplete( $complete );
					$groupObj->refreshProgress();
					$groupObj->commit();
				}
			}
			// Now that we've done the updating of the task given,
			// model must ripple up the changes and refresh task to include this latest change
			$model->checkTask( $taskId );
		}
		return $retVal;
	}
	
	/**
	 * Saves tasks from the new multi update / task setting form
	 * creating/updating groups and their memberships accordingly
	 * Then redirects to the form again (carrying dependancy values through to get same form layout)
	 */
	function saveTask()
	{
		global $mainframe;
		if( empty($this->multiTasks['title']) ) {
			$mainframe->enqueueMessage( 'Could not create a new task as no title given', 'warning' );
			return false;
		}
		
		$model = &$this->getModel( 'task' );
		$retVal = true;
		
		// deal with new task
		// *** currently we only deal with parent task ids...
		$pTaskId = JRequest::getVar( 'parent_task_id', '' );
		$pTask = &$model->getTask( $pTaskId );
		$tData = $pTask->getData();
		
		$tData['id'] = null;
		$tData['parent'] = $pTaskId;
		$tData['title'] = $this->multiTasks['title'];
		$tData['text_1'] = $this->multiTasks['text_1'];
		$tData['text_2'] = $this->multiTasks['text_2'];
		$tData['complete'] = false;
		$tData['progress'] = 0;
		$tData['order'] = null;
		
		$newTask = new ApothTask( false, $tData );
		$newTaskId = $newTask->commit();
		$children = $pTask->getLinkedTasks( array( 'type'=>'children' ) );
		$children[] = $newTaskId;
		$pTask->setLinkedTasks( 'children', $children );
		$mainframe->enqueueMessage( 'New target added' );
		
		// deal with new group
		// *** this bit of logic is copied from the display func.
		// *** once factories are in place we'll be able to use the model more like we should
		$assignee = JRequest::getVar( 'assignee' );
		if( !empty($assignee) ) { $groupReq['members'][] = $assignee; }
		$groupReq['taskId'] = $pTaskId;
		$pTask->setGroupRequirements( $groupReq );
		$groups = $pTask->getgroups();
		foreach( $groups as $groupId=>$groupObj ) {
			$gData = $groupObj->getData();
			
			$gData['id'] = null;
			$gData['task_id'] = $newTaskId;
			$gData['complete'] = false;
			$gData['progress'] = 0;
			unset( $gData['num_updates'] );
			
			$newGroup = new ApothGroup( false, $gData);
			
			$gRoleData = $groupObj->getRoleData();
			foreach( $gRoleData as $role=>$data ) {
				$newGroup->setPeopleInRole( $role, $data );
			}
			
			$newGroup->commit();
			$newTask->addGroup( $newGroup );
			$mainframe->enqueueMessage( 'Participants assigned' );
		}
		$model->checkTask( $pTaskId );
		return $retVal;
	}
	
	/**
	 * Adds or saves changes to an assignment group update which has been defined by the user in the
	 * "add/edit update" interstitial calls _save to do the donkey work then
	 * redirects to show the update in the interstitial with appropriate message
	 */
	function editUpdate()
	{
		$this->_save( 'update' );
	}
	
	function removeUpdate()
	{
		$this->_save( 'removeUpdate' );
	}
	
	function completeUpdate()
	{
		$this->_save( 'completeUpdate' );
	}
	
	function addEvidence()
	{
		$this->_save( 'addEvidence' );
	}
	
	function removeEvidence()
	{
		$this->_save( 'removeEvidence' );
	}
	
	/**
	 * Updates the deadline date for all selected assignment groups
	 * calls _save to do the donkey work then redirects to show the updated list
	 */
	function saveDates()
	{
		
	}
	
	/**
	 * Updates the progress for all selected assignment groups
	 * calls _save to do the donkey work then redirects to show the updated list
	 */
	function saveProgress()
	{
		
	}
	
	/**
	 * Updates the role assignments for all selected assignment groups
	 * calls _save to do the donkey work then redirects to show the updated list
	 */
	function saveRoles()
	{
		
	}
	
	
	function _getEvidence( &$evidenceUrl, &$evidenceFile, &$evidenceUrlRaw, &$evidenceFileRaw, $personId )
	{
		global $mainframe;
		
		// ... retrieve and set evidence array
		if( is_array($evidenceUrlRaw) ) {
			foreach( $evidenceUrlRaw as $k=>$v ) {
				if( !empty($v) ) {
					$evidenceUrl[] = $v;
				}
			}
		}
		
		if( is_array($evidenceFileRaw) ) {
			foreach( $evidenceFileRaw as $k=>$v ) {
				if( !is_array($v) ) {
					$evidenceFileRaw[$k] = array($v);
				}
			}
			// The uploaded files need to have somewhere to live
			foreach( $evidenceFileRaw['name'] as $k=>$fName ) {
				if( !empty($fName) ) {
					$fRet = ApotheosisPeopleData::saveFile( $personId, 'evidence_file', $k );
					if( $fRet !== false ) {
						$evidenceFile[$k] = $fRet;
					}
					else {
						$mainframe->enqueuemessage( 'encountered error while uploading file '.$fName, 'warning' );
					}
				}
			}
		}
	}
	
	/**
	 * Gets data from the submitted form and sets the relevant data in the task / group / update
	 * object then invokes relevant "commit" function to save it
	 */
	function _save( $option )
	{
		ob_start();
		global $mainframe;
		$model = &$this->getModel( 'task' );
		$allok = true;
		$isMicro = false;
		
		$taskId = JRequest::getVar( 'taskId', false );
		$taskObj = &$model->getTask( $taskId );
		
		$groupId = JRequest::getVar( 'groupId', false );
		$groupObj = &$taskObj->getGroup( $groupId );
		
		$msg = '';
		$msgType = 'message';
		
		if( !is_object($groupObj) ) {
			echo 'group object could not be created<br />';
			$msgType = 'warning';
/*
			var_dump_pre($taskId,   'task id');
			var_dump_pre($groupId,  'group id');
			var_dump_pre($groupObj, 'group object');
// */
			$option = 'problem';
		}
		else {
//			echo 'group object was fine<br />';
			// microtask updates are shuffled up to their parent task, and the progress calculated
			// this changes which task we look at, and also sets some calculated values in place of submitted ones
			if( $taskObj->getMicro() ) {
				$isMicro = true; // for later reference
				
				// change the task to be the parent of this microtask
				$p = $taskObj->getParent();
				unset( $taskObj );
				unset( $groupObj );
				$taskObj = &$model->getTask( $p );
				$groupObj = &$taskObj->getGroup( $groupId );
				
				$microId = $taskId;
				$taskId = $p;
				
				// Set values as if they'd been submitted by the user
				JRequest::setVar( 'micro', array($microId) );
			}
			
			$updateId = JRequest::getVar('updateId', false);
			if( $updateId == false ) {
				$updateObj = new ApothUpdate();
			}
			else {
				$updateObj = &$groupObj->getUpdate( $updateId );
			}
			
			$p = JRequest::getVar( 'pId', false );
			if( $p !== false ) {
				$personId = $p;
			}
			else {
				$user = &ApotheosisLib::getUser();
				$personId = $user->person_id;
			}
		}
		
		switch( $option ) {
		case( 'update' ):
			// ensure we have task id and group id for authentication check
			if( !$taskId || !$groupId ) {
				$mainframe->enqueueMessage( JText::_('Unable to proceed, you may have been logged out. Please login and try again.'), 'error' );
				$allok = false;
			}
			
			// if we have valid form data do the update
			if( $allok ) {
				// Find data for the update object, and commit it
				// ... retrieve and set microtasks array
				$microtasks = JRequest::getVar( 'micro', array() );
				
				// ... retrieve and set evidence array
				$evidenceUrlRaw = JRequest::getVar( 'evidence_url', array() );
				$evidenceUrl = array();
				foreach( $evidenceUrlRaw as $k=>$v ) {
					if( !empty($v) ) {
						$evidenceUrl[] = $v;
					}
				}
				
				$evidenceFileRaw = JRequest::getVar( 'evidence_file', array('name'=>array()), 'files' );
				$evidenceFile = array();
				foreach( $evidenceFileRaw as $k=>$v ) {
					if( !is_array($v) ) {
						$evidenceFileRaw[$k] = array($v);
					}
				}
				// The uploaded files need to have somewhere to live
				foreach( $evidenceFileRaw['name'] as $k=>$fName ) {
					if( !empty($fName) ) {
						$fRet = ApotheosisPeopleData::saveFile( $personId, 'evidence_file', $k );
						if( $fRet !== false ) {
							$evidenceFile[$k] = $fRet;
						}
						else {
							$mainframe->enqueuemessage( 'encountered error while uploading file '.$fName, 'warning' );
						}
					}
				}
				
				// ... retrieve and set core object data array
				// (including calculating progress where appropriate
				$data['id'] = JRequest::getVar( 'updateId', false );
				$data['group_id'] = $groupId;
				$data['category'] = JRequest::getVar( 'category', false );
				$data['text'] = JRequest::getVar( 'text', false );
				if( is_array($data['text']) ) {
					$data['text'] = implode( ';', $data['text'] );
				}
				$data['author'] = $personId;
				if( $isMicro || ($taskObj->getSubtasksCount() > 0) ) {
					// to know what progress to put in we must calculate how this micro-update affects the
					// group's overall progress in the parent task
					$prog = $model->getSubtasksGroupProgress( $taskId, $groupId );
					foreach( $microtasks as $mId ) {
						$prog['values'][$mId] = 1; // Mark this microtask's progress as full
					}
					
					$values = $prog['values'];
					$weights = $prog['weights'];
					$avg = 100 * ApotheosisLibArray::weightedAverage( $values, $weights );
					
					
					$data['progress'] = $avg;
				}
				else {
					$data['progress'] = JRequest::getVar( 'progress', false );
				}
				
				// check the entered progress value
				$prog = $data['progress'];
				if( $prog == '' ) {
					$mainframe->enqueuemessage( 'Did you know that you didn\'t enter a progress update?', 'warning');
				}
				elseif( !is_numeric($prog) )  {
					$mainframe->enqueuemessage( 'The progress update has to be a whole number between 0 and 100', 'error');
				}
				
				// update and commit the data
				$updateObj->setCoreData( $data );
				$updateObj->addEvidence( $evidenceUrl, $evidenceFile, $personId );
				$updateObj->setMicros( $microtasks );
				$updateObj->commit();
				
				$pInserted = $updateObj->getProgress();
				if( $prog != $pInserted ) {
					$mainframe->enqueuemessage( 'We had to change your entered progress value from "'.$prog.'" to "'.$pInserted.'" because this has to be a whole number from 0 to 100', 'warning');
				}
				
				if( $updateId == false ) {
//					echo 'adding update then<br />';
					$groupObj->addUpdate( $updateObj );
				}
				$groupObj->refreshProgress();
				$groupObj->commit();
				
				
				$mainframe->enqueueMessage( 'Successfully saved the update' );
			}
			// if form data not valid or user not allowed
			else {
				// *** display previous mainframe error messages
			}
			
			$link = $this->_getLink( array('option'=>'com_arc_planner', 'view'=>'task') ).'&scope=interUpdate&taskId='.$taskId.'&groupId='.$groupId.'&updateId='.$updateId.'&tmpl=component';
			break;
		
		case( 'removeUpdate' ):
			$microtasks = $updateObj->getMicros();
			$groupObj->removeUpdate( $updateId );
			$groupObj->refreshProgress();
			$groupObj->commit();
			$updateObj->delete();
			
			break;
		
		case( 'completeUpdate' ):
			// ensure we have task id and group id for authentication check
			if( !$taskId || !$groupId ) {
				$mainframe->enqueueMessage( JText::_('Unable to proceed, you may have been logged out. Please login and try again.'), 'error' );
				$allok = false;
			}
			
			if( $allok ) {
				$groupObj->setComplete( true );
				$groupObj->commit();
			}
			// if form data not valid or user not allowed
			else {
				// *** display previous mainframe error messages
			}
			
			break;
		
		case( 'addEvidence' ):
			// ... retrieve and set evidence array
			$evidenceUrlRaw = JRequest::getVar( 'evidence_url', array() );
			$evidenceUrl = array();
			foreach( $evidenceUrlRaw as $k=>$v ) {
				if( !empty($v) ) {
					$evidenceUrl[] = $v;
				}
			}
			
			$evidenceFileRaw = JRequest::getVar( 'evidence_file', array('name'=>array()), 'files' );
//			var_dump_pre($evidenceFileRaw, '$evidenceFileRaw');
			$evidenceFile = array();
			foreach( $evidenceFileRaw as $k=>$v ) {
				if( !is_array($v) ) {
					$evidenceFileRaw[$k] = array($v);
				}
			}
			// The uploaded files need to have somewhere to live
			foreach( $evidenceFileRaw['name'] as $k=>$fName ) {
				if( !empty($fName) ) {
					$evidenceFile[$k] = ApotheosisPeopleData::saveFile( $personId, 'evidence_file', $k );
				}
			}
			$updateObj->addEvidence( $evidenceURL, $evidenceFile );
			$updateObj->commit();
			
			break;
		
		case( 'removeEvidence' ):
			$evidenceId = JRequest::getVar( 'evidenceId', false );
			
			if( $evidenceId !== false ) {
				$updateObj->removeEvidence( $evidenceId );
				$updateObj->commit();
			}
			
			break;
		
		case( 'problem' ):
			$mainframe->enqueueMessage( JText::_('Unable to proceed. There was a problem getting the data to update'), 'error' );
		}
		
		// Now that we've done the updating of the task given,
		// model must ripple up the changes and refresh task to include this latest change
		if( empty($microtasks) ) {
			$model->checkTask( $taskId );
		}
		else {
			foreach( $microtasks as $mId ) {
				$model->checkTask( $mId );
			}
		}
		
		$this->saveModel( 'task' );
		$msg = ob_get_clean();

		$redirect = isset($link) ? $link : $this->_getLink( array('option'=>'com_arc_planner', 'view'=>'task') );
		$mainframe->redirect( $redirect, $msg, $msgType );
	}
}
?>