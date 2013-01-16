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

jimport( 'joomla.application.component.controller' );
jimport( 'joomla.application.helper' );

/**
 * Reports Controller Lists
 *
 * @author		lightinthedark <code@lightinthedark.org.uk>
 * @package		Apotheosis
 * @subpackage	Reports
 * @since 0.1
 */
class ReportsControllerLists extends ReportsController
{
	function __construct()
	{
		parent::__construct();
		// un-tuple the courseid
		if( !is_null($cycleGroupTuple = JRequest::getVar('courseid')) ) {
			$cgArray = explode( '_', $cycleGroupTuple );
			$courseId = array_pop( $cgArray );
			JRequest::setVar( 'courseid', $courseId );
		}
		
		// un-tuple the pupilid
		if( !is_null($cyclePersonTuple = JRequest::getVar('pupilid')) ) {
			$cgArray = explode( '_', $cyclePersonTuple );
			$pupilId = array_pop( $cgArray );
			JRequest::setVar( 'pupilid', $pupilId );
		}
	}
	
	/**
	 * When we search we'll need to re-set the cycle to the one specified
	 */
	function search()
	{
		// make sure we don't carry settings over from one cycle to another.
		$this->deleteModel( 'lists' );
		$this->deleteModel( 'admin' );
		$this->deleteModel( 'report' );
		
		$model = &$this->getModel( 'lists' );
		
		$model->setCycle( JRequest::getVar('report_cycle') );
		$this->display();
	}
	
	function display()
	{
		$model = &$this->getModel( 'lists' );
		$view  = &$this->getView ( 'lists', 'html', '', array('cycle'=>$model->getCycleId()) );
		
		$oldGrp = $model->getGroup();
		$model->setState( 'search', true);
		$model->setGroup( JRequest::getInt('courseid') );
		$view->setModel( $model, true );
		
		switch($scope = JRequest::getWord('scope')) {
		case('class'):
			$requirements = array( 'getBy'=>'course', 'group'=>array($model->getGroup()), 'cycle'=>array($model->getCycleId()) );
			$rModel = &$this->getModel( 'report' );
			$rModel->setReports( $requirements );
			
			$view->setModel( $rModel, false );
			$view->members();
			break;
		
		case('course'):
			$view->classes();
			break;
		
		case('courses'):
			$view->normal();
			break;
		
		case('tutors'):
			$view->pastoral();
			break;
		
		case('student'):
			$student = JRequest::getVar('pupilid');
			$model->setGroup($student);
			$curGroup = $model->getGroup();
			if(($curGroup != $student) || ($oldGrp != $student)) {
				$model->setSourceGroup($oldGrp);
			}
			$groups = $model->getStudentCourses();
			foreach($groups as $k=>$v) {
				foreach($v->_children as $k=>$v) {
					$classes[] = $v;
				}
			}
			$requirements = array( 'getBy'=>'course', 'pupil'=>array($student), 'group'=>$classes, 'cycle'=>array($model->getCycleId()) );
			$rModel = &$this->getModel( 'report' );
			$rModel->setReports( $requirements );
			
			$view->setModel( $rModel, false );
			$view->someCourses(false, false, 'small');
			break;
		
		default:
			$view->all();
		}
		
		$this->saveModel( 'lists' );
		if( isset($rModel) ) {
			$this->saveModel( 'report' );
		}
	}
	
	function massReport()
	{
		$model = &$this->getModel( 'lists' );
		$view  = &$this->getView ( 'lists', 'html' );
		
		$model->setState( 'search', true);
		$view->setModel( $model, true );
		
		switch( $scope = JRequest::getWord('scope') ) {
		case('class'):
			$group = JRequest::getInt( 'courseid' );
			$model->setGroup( $group );
			
			// get existing reports
			$requirements = array( 'getBy'=>'course', 'group'=>array($group), 'cycle'=>array($model->getCycleId()) );
			timer('getting model');
			$rModel = &$this->getModel( 'report' );
			timer('got model, setting reports');
			$rModel->setReports( $requirements );
			timer('set reports');
			
			$view->setModel( $rModel, false );
			$view->memberReports();
			break;
		
		}
		
		$this->saveModel( 'lists' );
		if( isset($rModel) ) {
			$this->saveModel( 'report' );
		}
	}
	
	function saveMassReport()
	{
		global $mainframe;
		ob_start();
		
		$model = &$this->getModel( 'lists' );
		$rModel = &$this->getModel( 'report' );
		
		if( !ApotheosisLibAcl::checkDependancy('report.groups', $model->getCycleId().'_'.$model->getGroup()) ) {
			$vals = array();
		}
		else {
			$vals = JRequest::get( 'post' );
		}
		
		$good = 0;
		$part = 0;
		$fail = 0;
		$empty = 0;
		foreach($vals as $studentSet) {
			if( is_array($studentSet) ) {
				foreach( $studentSet as $id=>$data ) {
					$hasData = false;
					foreach($data as $k=>$v) {
						if( $k != 'cycle' && $k != 'group' && $k != 'student' && $k != 'author' && $v !== '' ) {
							$hasData = true;
							break;
						}
					}
					if( $hasData ) {
						if( $id == 'new' ) {
							$rpts = &$rModel->getBlankReports( $data['cycle'], $data['group'], $data['student'] );
							$rpt = reset($rpts);
						}
						else {
							$rpt = &$rModel->getReport($id);
						}
						// don't forget to merge text for the statemented fields
						$statFields = &$rpt->getStatementFields();
						foreach($statFields as $fKey=>$field) {
							$val = $data[$fKey];
							if( is_array( $val ) ) {
								$tmpVal = '';
								$bank = &$statFields[$fKey]->getStatementBank();
								$statements = &$bank->getStatements( true );
								foreach( $val as $v ) {
									$model->setMergeDetails($rpt->getStudent(), $rpt->getGroup());
									$tmpVal .= (empty($tmpVal) ? '' : "\n").$model->mergeText($statements[$v]->text);
								}
								$data[$fKey] = $tmpVal;
								$statFields[$fKey]->setValue($tmpVal);
							}
						}
						$rpt->setStatus( 'submitted' );
						$result = $rpt->save( 'data', $data );
						if( empty($result) ) {
							$good++;
							$rModel->setReportExisting( $rpt->getId() );
						}
						elseif( !array_key_exists('errors', $result) ) {
							$part++;
							$rModel->setReportExisting( $rpt->getId() );
						}
						else {
							$fail++;
						}
					}
					else {
						if( $id == 'new' ) {
							$empty++;
						}
						else {
							$part++;
						}
					}
				}
			}
		}
		
		if( $good > 0 ) {
			$mainframe->enqueueMessage( 'Successfully saved '.$good.' complete reports', 'message' );
		}
		if( $part > 0 ) {
			$mainframe->enqueueMessage( 'Successfully saved '.$part.' reports which need further attention', 'warning' );
		}
		if( $fail > 0 ) {
			$mainframe->enqueueMessage( 'Failed to save '.$fail.' reports', 'error' );
		}
		if( $empty > 0 ) {
			$mainframe->enqueueMessage( 'Did not save '.$empty.' reports which had no values set', 'error' );
		}
		
		$this->saveModel();
		
		$msg = ob_get_clean();
		if( $msg != '' ) {
			$mainframe->enqueueMessage( $msg, 'message' );
		}
		$mainframe->redirect( ApotheosisLib::getActionLinkByName('apoth_report_mass', array('report.groups'=>$model->getCycleId().'_'.$model->getGroup())) );
	}
	
	function delete()
	{
		global $mainframe;
		ob_start();
		
		$model = &$this->getModel( 'lists' );
		$rModel = &$this->getModel( 'report' );
		
		if( ApotheosisLibAcl::checkDependancy('report.groups', $model->getCycleId().'_'.$model->getGroup()) ) {
			$reportId = JRequest::getVar( 'reportid', false );
			$rpt = $rModel->getReport( $reportId );
			$child =  htmlspecialchars( $rpt->getStudentFirstName().' '.$rpt->getStudentSurname() );
			$author = htmlspecialchars( $rpt->getAuthorName() );
			$subj =   htmlspecialchars( $rpt->getSubjectName() );
			
			if( $rModel->delete( $reportId, true ) ) {
				$this->deleteModel( 'report' );
				$mainframe->enqueueMessage( 'Report for '.$child.' in '.$subj.' by '.$author.' deleted', 'message' );
			}
			else {
				$mainframe->enqueueMessage( 'Could not delete report for '.$child.' in '.$subj.' by '.$author.'. Please try again.', 'error' );
			}
		}
		
		$msg = ob_get_clean();
		if( $msg != '' ) {
			$mainframe->enqueueMessage( $msg, 'message' );
		}
		$mainframe->redirect( ApotheosisLib::getActionLinkByName('apoth_report_list_pupils', array('report.groups'=>$model->getCycleId().'_'.$model->getGroup())) );
	}
}
?>