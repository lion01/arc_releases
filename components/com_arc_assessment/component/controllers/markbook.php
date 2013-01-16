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

/**
 * Assessments Controller Admin
 *
 * @author		lightinthedark <code@lightinthedark.org.uk>
 * @package		Apotheosis
 * @subpackage Assessments
 * @since 0.1
 */
class AssessmentsControllerMarkbook extends AssessmentsController
{
	/**
	 * Constructor
	 */
	function __construct( $config = array() )
	{
		parent::__construct( $config );
		// Register Extra tasks
//		$this->registerTask( 'SelectAdhoc', 'selectAdhoc');
	}
	
	function display()
	{
		$model = &$this->getModel( 'markbook' );
		$view =  &$this->getView ( 'markbook', JRequest::getVar('format', 'html') );
		
		$view->setModel( $model, true );
		$view->display();
		$this->saveModel( 'markbook' );
	}
	
	function search()
	{
		$model = &$this->getModel( 'markbook' );
		$view =  &$this->getView ( 'markbook', 'html' );
		
		if( JRequest::getString( 'start_date', false ) == false ) {
			JRequest::setVar( 'start_date', date('Y-m-d') );
		}
		if( JRequest::getString( 'end_date', false ) == false ) {
			JRequest::setVar( 'end_date', date('Y-m-d') );
		}
		$requirements['valid_from']    = JRequest::getString( 'start_date'    , false );
		$requirements['valid_to']      = JRequest::getString( 'end_date'      , false );
		$requirements['academic_year'] = JRequest::getString( 'academic_year' , false );
		$requirements['aspect']        = JRequest::getString( 'aspect'        , false );
		$requirements['current']       = JRequest::getString( 'current'       , false );
		$requirements['complete']      = JRequest::getVar( 'complete'     , false );
		$requirements['assessment']    = JRequest::getVar( 'assessment'   , false );
		$requirements['assessments']   = JRequest::getVar( 'assessments'  , false );
		$requirements['groups']        = JRequest::getVar( 'groups'       , false);
		$requirements['teacher']       = JRequest::getVar( 'teacher'      , false);
		$requirements['pupil']         = JRequest::getVar( 'pupil'        , false);
		$requirements['no_others']     = (bool)JRequest::getVar( 'no_others'  , false);
		
		ApotheosisLibAcl::setDatum( 'dateFrom', $requirements['valid_from'] );
		ApotheosisLibAcl::setDatum( 'dateTo',   $requirements['valid_to'] );
		
		if( !is_array($requirements['assessments']) ) {
			$requirements['assessments'] = unserialize( $requirements['assessments'] );
		}
		if( !is_array($requirements['assessments']) ) {
			$requirements['assessments'] = array();
		}
		
		if( $requirements['groups'] !== false ) {
			$requirements['groups'] = unserialize($requirements['groups']);
		}
		
		foreach ($requirements as $k=>$v) {
			if( is_array($v) ) {
				foreach( $v as $k2=>$v2 ) {
					if( empty($v2) ) {
						unset($requirements[$k][$k2]);
					}
				}
			}
			if( empty($requirements[$k]) ) {
				unset($requirements[$k]);
			}
		}
		
		$model->setAssessments( $requirements );
		$model->sort();
		
		$this->display();
	}
	
	function summary()
	{
		$model = &$this->getModel( 'markbook' );
		$view =  &$this->getView ( 'markbook', 'raw' );
		$u = ApotheosisLib::getUser();
		
		$requirements['assessments']   = JRequest::getVar( 'assessments'  , false );
		$requirements['pupil']         = JRequest::getVar( 'pupil', $u->person_id );
		$requirements['no_others']     = (bool)JRequest::getVar( 'no_others', true );
		
		$requirements['valid_from'] = $requirements['valid_to'] = ApotheosisLibAcl::getDatum( 'permisisonsAt' );
		
		if( !is_array($requirements['assessments']) ) {
			$requirements['assessments'] = array();
		}
		
		$model->setAssessments( $requirements );
		$model->sort();
		
		$view->setModel( $model, true );
		$view->summary();
		$this->saveModel( 'markbook' );
	}
	
	/**
	 * Loads the markbook for the current user's current class(es)
	 * 
	 */
	function current()
	{
		$model = &$this->getModel( 'markbook' );
		$view =  &$this->getView ( 'markbook', 'html' );
		$u = ApotheosisLib::getUser();
		
		$requirements['person'] = $u->person_id;
		$requirements['day_section'] = ApotheosisLibCycles::getCurrentPeriod();
		$groups = ApotheosisData::_( 'timetable.group', $requirements );
		if( !empty($groups) && is_array($groups) ) {
			$requirements = array( 'groups'=>$groups );
			$model->setAssessments( $requirements );
			$model->sort();
			
			$view->setModel( $model, true );
			$view->display();
			$this->saveModel( 'markbook' );
		}
		else {
			$this->display();
		}
	}
	
	function ascending()
	{
		$a = JRequest::getVar( 'aspects', null );
		$a = ( (is_array($a) && !empty($a)) ? reset( $a ) : 'group' );
		$model = &$this->getModel( 'markbook' );
		$model->sort( $a, 1 );
		$this->display();
	}
	
	function decending()
	{
		$a = JRequest::getVar( 'aspects', null );
		$a = ( (is_array($a) && !empty($a)) ? reset( $a ) : 'group' );
		$model = &$this->getModel( 'markbook' );
		$model->sort( $a, -1 );
		$this->display();
	}
	
	function show()
	{
		$aspects = JRequest::getVar( 'aspects', false );
		$model = &$this->getModel( 'markbook' );
		$model->setShown( $aspects, true);
		$this->display();
	}
	
	function hide()
	{
		$aspects = JRequest::getVar( 'aspects', array() );
		$model = &$this->getModel( 'markbook' );
		$model->setShown( $aspects, false);
		$this->display();
	}
	
	function hideOthers()
	{
		$aspects = JRequest::getVar( 'aspects', array() );
		$model = &$this->getModel( 'markbook' );
		$model->setShown( $aspects, false, true );
		$this->display();
	}
	
	function showAll()
	{
		$aspects = array();
		$model = &$this->getModel( 'markbook' );
		$model->setShown( $aspects, true, true );
		$this->display();
	}
	
	function edit()
	{
		$aId = JRequest::getVar( 'aId', null );
		if( !is_null($aId) ) {
			$model = &$this->getModel( 'markbook' );
			$model->setEdits( $aId );
		}
		$this->display();
	}
	
	function save()
	{
		$marks = JRequest::getVar( 'marks' );
		$model = &$this->getModel( 'markbook' );
		$model->saveMarks( $marks );
		global $mainframe;
		$mainframe->enqueueMessage( 'Marks Saved' );
		$mainframe->enqueueMessage( 'Your assessment has been left editable so you can continue to enter marks' );
		$mainframe->enqueueMessage( 'If you press "Search" or click the assessment title, it will go back to being un-editable as normal' );
		$this->display();
	}
}
?>
