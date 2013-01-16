<?php
/**
 * @package     Arc
 * @subpackage  Behaviour
 * @copyright   Copyright (C) 2005 Punnet. All rights reserved. See COPYRIGHT_ARC.txt
 * @license     http://www.gnu.org/licenses/gpl.html GNU/GPL. See LICENSE_ARC.txt
 * Arc is free software: you can redistribute it and/or modify
 * it under the terms of version 3 of the GNU General Public License
 * as published by the Free Software Foundation.
 * The disclaimer of warranty as stated in the GPL applies to this program
 */

defined( '_JEXEC' ) or die( 'Restricted access' );

/**
 * Message Hub Controller
 */
class BehaviourControllerReports extends BehaviourController
{
	function display()
	{
		$model = $this->getModel( 'reports' );
		$view  = $this->getView( 'reports', 'html' );
		
		$view->setModel( $model, true );
		$view->display();
	}
	
	/**
	 * Sets up the data to display on the behaviour report page
	 * Then calls display to show them
	 */
	function search()
	{
		$model = $this->getModel( 'reports' );
		
		$requirements['incidents']     = JRequest::getVar( 'incidents'    , false );
		
		$requirements['start_date']    = JRequest::getVar( 'start_date'   , date( 'Y-m-d' ) ).' 00:00:00';
		$requirements['end_date']      = JRequest::getVar( 'end_date'     , date( 'Y-m-d' ) ).' 23:59:59';
		$requirements['limits']        = JRequest::getVar( 'limits'       , false );
		$requirements['limits_val']    = ( $requirements['limits'] == false ? false : JRequest::getVar( 'limits_val', false ) );
		
		$requirements['author']        = JRequest::getVar( 'author'       , false );
		$requirements['person_id']     = JRequest::getVar( 'person_id'    , false );
		$requirements['truant_id']     = JRequest::getVar( 'truant_id'    , false );
		
		$requirements['tutor']         = JRequest::getVar( 'tutor'        , false );
		$requirements['academic_year'] = JRequest::getVar( 'academic_year', false );
		$requirements['day_section']   = JRequest::getVar( 'day_section'  , false );
		
		$requirements['groups']        = unserialize( JRequest::getVar( 'groups', '\N' ) );
		$series = JRequest::getVar( 'series', 'person_id' );
		
		// merge together students' and truants' ids
		$pupils = is_array($requirements['person_id']) ? $requirements['person_id'] : array();
		$truants = is_array($requirements['truant_id']) ? $requirements['truant_id'] : array();
		$requirements['person_id'] = array_unique( (array_merge($pupils, $truants)) );
		unset($requirements['truant_id']);
		
		// clean out unset requirements
		foreach( $requirements as $k=>$v ) {
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
		
		if( isset( $requirements['incidents'] ) ) {
			$incs = array();
			foreach( $requirements['incidents'] as $inc ) {
				$incs = $incs + ApotheosisLibDb::getDescendants( $inc, '#__apoth_bhv_inc_types' );
			}
			$requirements['incidents'] = array_keys( $incs );
		}
		
		$model->setReport( $requirements, $series );
		$model->cleanTemps();
		$this->display();
		
		$this->saveModel();
	}
	
	/**
	 * Generates a simple graph for the homepage panel
	 */
	function personalSummary()
	{
		echo 'hello';
		$model = $this->getModel( 'reports' );
		$view  = $this->getView( 'reports', 'html' );
		
		$u = &ApotheosisLib::getUser();
		$requirements['person_id']  = array( $u->person_id );
		$requirements['start_date'] = ApotheosisLib::getEarlyDate();
		$requirements['end_date']   = date( 'Y-m-d H:i:s' );
		$series = 'person_id';
		
		$model->setReport( $requirements, $series );
		$model->cleanTemps();
		
		$view->setModel( $model, true );
		$view->display();
		
		$this->saveModel();
	}
	
	/**
	 * Generates a simple graph for the homepage panel
	 */
	function displaypanel()
	{
		$model = $this->getModel( 'reports' );
		$view  = $this->getView( 'reports', 'raw' );
		
		$u = &ApotheosisLib::getUser();
		$requirements['person_id']  = array( JRequest::getVar('pId', $u->person_id) );
		$requirements['highlightDate'] = JRequest::getVar( 'highlightDate', null );
		$urlStart = JRequest::getVar( 'start', false );
		$urlEnd   = JRequest::getVar( 'end', false );
		if( $urlStart && $urlEnd ) {
			$requirements['start_date'] = $urlStart;
			$requirements['end_date']   = $urlEnd;
		}
		else {
			$requirements['start_date'] = ApotheosisLib::getEarlyDate();
			$requirements['end_date']   = date( 'Y-m-d H:i:s' );
		}
		$series = 'person_id';
		
		$model->setReport( $requirements, $series );
		$model->cleanTemps();
		
		$view->setModel( $model, true );
		$view->display();
		
		$this->saveModel();
	}
	
	/*
	 * Generates a pdf report 
	 */
	function generate()
	{
		$model = $this->getModel( 'reports' );
		$view = &$this->getView ( 'reports', 'apothpdf' );
		
		$view->setModel( $model, true );
		$view->display();
		
		$this->saveModel();
	}
}
?>