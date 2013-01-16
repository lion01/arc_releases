<?php
/**
 * @package     Arc
 * @subpackage  Timetable
 * @copyright   Copyright (C) 2005 Punnet. All rights reserved. See COPYRIGHT_ARC.txt
 * @license     http://www.gnu.org/licenses/gpl.html GNU/GPL. See LICENSE_ARC.txt
 * Arc is free software: you can redistribute it and/or modify
 * it under the terms of version 3 of the GNU General Public License
 * as published by the Free Software Foundation.
 * The disclaimer of warranty as stated in the GPL applies to this program
 */

defined( '_JEXEC' ) or die( 'Restricted access' );

class TimetableControllerSummary extends TimetableController
{
	function display()
	{
		$model = &$this->getModel( 'summary' );
		$view  = &$this->getView ( 'summary', 'html' );
		
		$view->setModel( $model, true );
		$view->link = $this->_getLink();
		$view->display();
	}
	
	function search()
	{
		$viewName = JRequest::getVar('view', 'summary');
		$model = &$this->getModel( $viewName );
		$view  = &$this->getView ( $viewName, 'html' );
		
		$requirements['startDate']  = JRequest::getVar( 'start_date', false );
		$requirements['endDate']    = JRequest::getVar( 'end_date', false );
		$requirements['room']       = JRequest::getVar( 'room', false );
		$requirements['daySection'] = JRequest::getVar( 'day_section', false );
		$requirements['teachers']   = JRequest::getVar( 'teacher', false );
		
		switch( $viewName ) {
		case('summary'):
			$model->setTimetable( $requirements );
			break;
		
		case( 'enrolments' ):
			$model->setEnrolments( $requirements );
			break;
		}
		$model->setState( 'search', true );
		
		$view->setModel( $model, true );
		$view->display();
	}
	
	// *** Following functions unused right now
	
	/**
	 * Checks to see if a new search is being executed.
	 * If it is, the courses and registers are re-initialised and stored in the model
	 */
	function &_checkNewSearch(&$model)
	{
//echo 'checking for new search<br />';
		$new_search = (bool)JRequest::getVar('new_search', false);
		$composite_id = JRequest::getVar('composite_id');
		
		if($new_search || !is_null($composite_id)) {
//echo 're-setting register<br />';
			unset($this->_model); // a new search requires removal of the old model...
			$this->_session->clear( $this->_session->get( 'modelName' ) );
			$model = &$this->getModel( 'ereg' ); // ... and creation of a new one.
			
			if ($new_search) {
//echo 'new search<br />';
				$requirements['date'] = JRequest::getVar('date', false);
				$requirements['day'] = JRequest::getVar('day', false);
				$requirements['day_section'] = JRequest::getVar('day_section');
				$requirements['room_id'] = JRequest::getVar('room');
				$requirements['user'] = JRequest::getVar('user');
				$requirements['teacher'] = JRequest::getVar('teacher');
				foreach ($requirements as $k=>$v) {
					if ($v === false) {
						unset($requirements[$k]);
					}
				}
//echo 'requirements:';var_dump_pre($requirements);
				
				$model->setCourses($requirements);
				$courses = $model->getCourses();
//echo 'in check: courses:';var_dump_pre($courses);
				
				// create registers from the course info
				$date = (array_key_exists('date', $requirements) ? $requirements['date'] : false);
				foreach ($courses as $course) {
					$ids[] = array('date'=>($date ? $date : $course->most_recent),
						'time'=>$course->day_section,
						'course'=>$course->id,
						'location'=>$course->room_id);
				}
//echo 'ids:';var_dump_pre($ids);
				$model->setRegisters($ids);
				$regs = $model->getRegisters();
//echo 'reg count: '.count($regs).'<br />';
//echo 'regs:';var_dump_pre($regs);
			}
			else {
//echo 'composite key search<br />';
				// break open the composite id to get the search requirements
				$courseArr = Register::splitCompId($composite_id);
				$model->setRegisters(array($courseArr));
			}
		}
		return $model;
	}
	
	/**
	 * Sets up the course list as the current user's current course.
	 * Proceeds to view
	 */
	function showCurrent()
	{
		JRequest::setVar('returnTask', JRequest::getVar('returnTask', JRequest::getVar('task', 'showCurrent')));
		$this->_session->set( 'modelName', 'nowModel');
		
		$model = &$this->getModel( 'ereg' );
		$view = &$this->getView( 'ereg', 'html' );
		
		$user = &ApotheosisLib::getUser();
		
		$regs = $model->getRegisters();
		$reg = reset($regs);
		
		// get the parameters used to pick the current register...
		$requirements['date'] = date('Y-m-d');
		$requirements['time'] = ApotheosisLibCycles::getCurrentPeriod();
		$requirements['user'] = (($user->id == 0) ? NULL : $user->id);
		
		// ... and if the course doesn't match, re-search
		if (!is_object($reg) ||
				($reg->day != $day) ||
				($reg->time != $requirements['time']) ) {
			$model->setRegisters(array($requirements));
		}
		
		$model->setState('search', false);
		
		$this->saveModel();
		$view->session = &$session; // needed to give the templates access to session-stored data
		
		$view->setModel( $model, true );
		$view->register();
	}
}
?>