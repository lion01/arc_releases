<?php
/**
 * @package     Arc
 * @subpackage  Attendance
 * @copyright   Copyright (C) 2005 Punnet. All rights reserved. See COPYRIGHT_ARC.txt
 * @license     http://www.gnu.org/licenses/gpl.html GNU/GPL. See LICENSE_ARC.txt
 * Arc is free software: you can redistribute it and/or modify
 * it under the terms of version 3 of the GNU General Public License
 * as published by the Free Software Foundation.
 * The disclaimer of warranty as stated in the GPL applies to this program
 */

defined( '_JEXEC' ) or die( 'Restricted access' );

jimport( 'joomla.application.component.controller' );
jimport( 'joomla.application.helper' );
require_once( JPATH_COMPONENT_ADMINISTRATOR.DS.'helpers'.DS.'synch_settings.php' );
require_once( JPATH_SITE.DS.'components'.DS.'com_arc_attendance'.DS.'helpers'.DS.'data_access.php' );

/**
 * Attendance Controller Ereg
 *
 * @author		lightinthedark <code@lightinthedark.org.uk>
 * @package		Apotheosis
 * @subpackage	Attendance
 * @since 0.1
 */
class AttendanceControllerEreg extends AttendanceController
{
	function __construct( $config = array() )
	{
		parent::__construct( $config );
		$this->registerTask( 'SelectAdhoc', 'selectAdhoc');
		$this->registerTask( 'AddPupils', 'addAdhoc');
		$this->registerTask( 'RemoveAdhoc', 'removeAdhoc');
		$this->registerTask( 'SaveRegister', 'save');
		
		require_once( JPATH_COMPONENT.DS.'models'.DS.'notes.php' );
		$this->noteModel = new AttendanceModelNotes();
	}
	
	/**
	 * Default action.
	 * Calls appropriate display function
	 */
	function display()
	{
		$model = &$this->getModel( 'ereg' );
		$view  = &$this->getView ( 'ereg', 'html' );
		$view->setModel( $model, true );
		
		$scope = JRequest::getWord( 'scope', false );
		switch($scope) {
		case('key'):
			$view->codeKey();
			break;
		
		case('current'):
			$num = $model->getNoOfRegisters();
			if( $num == 1 ) {
				$regs = &$model->getRegisters();
				$reg = reset($regs);
				if( $reg->daySection == ApotheosisLibCycles::getCurrentPeriod() ) {
					$model->setRegType( $reg->course->type );
					$this->noteModel->setGroupNotes($reg->course->id);
					$view->setModel( $this->noteModel, false );
					$view->register();
				}
				else {
					$this->_current();
				}
			}
			else {
				$this->_current();
			}
			break;
		
		default:
			$model->setState( 'search', ($scope != false) );
			
			$view->link = $this->_getLink();

			$num = $model->getNoOfRegisters();
			if ($num > 1) {
				$view->classList();
			}
			elseif ($num < 1) {
				$view->display();
			}
			else {
				// set mark type based on type of single reg
				$regs = &$model->getRegisters();
				$reg = reset($regs);
				$model->setRegType( $reg->course->type );
				$this->noteModel->setGroupNotes($reg->course->id);
				$view->setModel( $this->noteModel, false );
				$view->register();
			}
			break;
		}
		$this->saveModel();
	}
	
	/**
	 * Sets up the course list as specified by the search form.
	 */
	function search()
	{
		$model = &$this->getModel( 'ereg' );
		$view  = &$this->getView ( 'ereg', 'html' );
		
		$scope = JRequest::getCmd( 'scope' );
		$compId = JRequest::getVar( 'composite_id', false );
		if ($compId === false) {
			switch($scope) {
			case('recent'):
				$requirements['day'] = JRequest::getString( 'day' );
				break;
			
			case('history'):
				$requirements['start_date'] = JRequest::getString( 'start_date' );
				$requirements['end_date'] = JRequest::getString( 'end_date' );
				break;
			}
			$requirements['day_section'] = JRequest::getString( 'day_section');
			$requirements['room'] = JRequest::getString( 'room' );
			$requirements['teacher'] = JRequest::getString( 'teacher' );
			$requirements['class'] = JRequest::getString( 'class' );
			$requirements['normal_class'] = JRequest::getString( 'normal_class' );
			$requirements['pastoral_class'] = JRequest::getString( 'pastoral_class' );
			$requirements['pupil'] = JRequest::getString( 'pupil' );
		}
		else {
			$requirements['composite_id'] = $compId;
		}
		$model = $this->_search( $model, $requirements );
		$model->setState( 'search', true );
		
		$view->setModel( $model, true );
		$view->link = $this->_getLink();
		
		$num = $model->getNoOfRegisters();
		if ($num > 1) {
			$view->classList();
		}
		elseif ($num < 1) {
			$view->display();
		}
		else {
			// set mark type based on type of single reg
			$regs = $model->getRegisters();
			$reg = reset($regs);
			$model->setRegType( $reg->course->type );
			$this->noteModel->setGroupNotes($reg->course->id);
			$view->setModel( $this->noteModel, false );
			$view->register();
		}
		
		$this->saveModel();
	}
	
	/**
	 * Sets up the course list as the current user's current course.
	 */
	function _current()
	{
		$model = &$this->getModel( 'ereg' );
		$view  = &$this->getView ( 'ereg', 'html' );
		
		$user = &ApotheosisLib::getUser();
		
		if ($user->id != 0) {
			// get the parameters used to pick the current register...
			$requirements['date'] = date('Y-m-d');
			$requirements['day_section'] = ApotheosisLibCycles::getCurrentPeriod();
			$requirements['user'] = $user->id;
		}
		else {
			$requirements = array();
		}
		$model = $this->_search( $model, $requirements );
		
		$model->setState('search', false);
		$this->saveModel();
		
		$view->session = &$session; // needed to give the templates access to session-stored data **** really? This is in a few places and I think it's an obsolete thing
		$view->setModel( $model, true );
		$view->link = $this->_getLink();
		
		$num = $model->getNoOfRegisters();
		if ($num > 1) {
			$view->classList();
		}
		elseif ($num < 1) {
			$view->display();
		}
		else {
			// set mark type based on type of single reg
			$regs = $model->getRegisters();
			$reg = reset($regs);
			$model->setRegType( $reg->course->type );
			$this->noteModel->setGroupNotes($reg->course->id);
			$view->setModel( $this->noteModel, false );
			$view->register();
		}
	}
	
	/**
	 * Sets up the course list depending on if it's a new search or just looking at a previous one.
	 * Proceeds to view
	 *
	 * @param object $model  The model to use for the search
	 * @param array $requirements  The associative array of requirements to define the search
	 * @return object  The original model, or a fresh one if a new search is being executed
	 */
	function _search( &$model, $requirements = array() )
	{
		// get rid of empty requirements
		foreach ($requirements as $k=>$v) {
			if ($v === '') {
				unset($requirements[$k]);
			}
		}
		$oldReq = $model->getRequirements();
		$new_search = ( $oldReq != $requirements );
		
		if($new_search) {
			$model->setRequirements($requirements);
			// composite key search uses existing registers
			if (isset($requirements['composite_id'])) {
				//echo 'composite key ('.$requirements['composite_id'].') given<br />';
				$regs = &$model->getRegisters();
				$model->setRegisters(array($regs[$requirements['composite_id']]));
			}
			// not used yet, but if we want multiple registers this will be useful
			elseif (isset($requirements['composite_ids'])) {
				$regs = &$model->getRegisters();
				foreach ($requirements['composite_ids'] as $compId) {
					$param[] = $regs[$compId];
				}
				$model->setRegisters($params);
			}
			// parameter based search uses a totally fresh model
			else {
				//echo 'requirements given so re-setting register<br />';
				$this->deleteModel(); // a new search requires removal of the old model...
				$model = &$this->getModel( 'ereg' ); // ... and creation of a new one.
				
				// create courses, then registers from those courses
				$model->setCourses($requirements);
				$courses = &$model->getCourses();
				$model->setRegisters($courses);
				$regs = &$model->getRegisters();
				ApotheosisLibArray::sortObjects( $regs, array('date', 'time', 'shortname'), 1, true );
			}
//$regs = $model->getRegisters();
//echo 'ids:';var_dump_pre($ids);
//echo 'reg count: '.count($regs).'<br />';
//echo 'regs:';var_dump_pre($regs);
//echo 'reg keys: ';var_dump_pre(array_keys($regs));
//echo 'numRegs: '.$model->getNoOfRegisters().'<br />';
		}
		return $model;
	}
	
	/**
	 * Brings up the page to select pupils for adhoc adding
	 */
	function selectAdhoc()
	{
		$model = &$this->getModel( 'ereg' );
		$view  = &$this->getView ( 'ereg', 'html' );
		
		$view->setModel( $model, true );
		$view->link = $this->_getLink();
		$view->adhoc();
		$this->saveModel();
	}
	
	
	// ##########  Actions that redirect  ##########
	
	/**
	 * Adds the passed pupils to the adhoc array in the model, then redirects
	 */
	function addAdhoc()
	{
		$model = &$this->getModel( 'ereg' );
		
		$adhocPupils = JRequest::getVar('adhocPupils', array());
		$model->setAdhocPupils($adhocPupils);
		
		global $mainframe, $Itemid;
		$this->saveModel();
		
		$msg = JText::_( 'Pupils added to register' );
		$mainframe->redirect( $this->_getLink(), $msg );
	}
	
	/**
	 * Remove the passed pupils from the adhoc array in the model, then redirects
	 */
	function removeAdhoc()
	{
		$model = &$this->getModel( 'ereg' );
		
		$removePupils = JRequest::getVar('remove');
		$model->unsetAdhoc($removePupils);
		
		global $mainframe, $Itemid	;
		$this->saveModel();
		
		$msg = JText::_( 'Pupils removed from register' );
		$mainframe->redirect( $this->_getLink(), $msg );
	}
	
	/**
	 * Saves the attendance marks for this register, then redirects
	 */
	function save()
	{
		ob_start();
		
		$model = &$this->getModel( 'ereg' );
		
		$commonMarks = JRequest::getVar('attendance');
		$otherMarks = JRequest::getVar('other');
		foreach ($commonMarks as $regId=>$marks) {
			foreach ($marks as $pId=>$mark) {
				if ($mark == 'Other') {
					$commonMarks[$regId][$pId] = $otherMarks[$regId][$pId];
				}
			}
		}
		$msgChk = false;
		$regs = &$model->getRegisters();
		foreach ($regs as $regId=>$reg) {
			$reg = &$regs[$regId];
			$msgChk = $reg->setMarks($commonMarks[$regId], true);
		}
		global $mainframe, $Itemid	;
		$this->saveModel();
		
		$redirectArr->saved = 0;
		$redirectArr->empty = 0;
		$redirectArr->partial = 0;
		$redirectArr->fail = 0;
		
		if(($msgChk != false) && (empty($msgChk->errorArr))) {
			$redirectArr->saved++;
		}
		elseif(!empty($msgChk->errorArr)) {
			$redirectArr->partial++;
		}
		else {
			$redirectArr->fail++;
		}
		
		//This determines the redirect message based on the checks above
		if( $redirectArr->saved != 0 && ($redirectArr->partial == 0 && $redirectArr->fail == 0)) {
			// SAVED
			$msg = JText::_( 'Register saved' );
			$type = 'message';
		}
		elseif($redirectArr->partial != 0) {
			// PARTIAL
			$msg = JText::_( 'Your register saved, but there were some marks entered while you were taking your register<br />' );
			$count = count($msgChk->errorArr);
			$msg .= 'There were '.$count.' mark(s) taken while you had the register open';
			$type = 'Warning';
		}
		elseif($redirectArr->empty != 0 && ($redirectArr->saved == 0 && $redirectArr->partial == 0 && $redirectArr->fail == 0)) {
			// NONE SAVED
			$msg = 'You did not change any marks';
			$type = 'warning';	
		}
		else {
			// ERROR (no marks received)
			$msg = JText::_( 'There was a problem with saving your register<br />Please try again or fill out a paper copy if it still doesn\'t work' );
			$type = 'error';
		}
		
		$msg .= '<br />'.ob_get_clean();
		$mainframe->redirect( $this->_getLink(array('option'=>'com_arc_attendance', 'view'=>'ereg', 'scope'=>'recent')), $msg, $type );
	}
	
}
?>
