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
 * Assessment Controller Admin 
 */
class AssessmentsControllerAdmin extends AssessmentsController
{
	/**
	 * Constructor
	 */
	function __construct( $config = array() )
	{
		parent::__construct( $config );
		// Register Extra tasks
//		$this->registerTask( 'Add', 'save');
	}
	
	/**
	 * Switches on the scope to display an appropriate admin page
	 * The assessment whose properties are to be displayed and modified
	 * is stored in the model
	 */
	function display()
	{
		$model = &$this->getModel( 'admin' );
		$view =  &$this->getView ( 'admin', 'html' );
		
		switch( JRequest::getVar('scope', 'new') ) {
		case 'new':
			$model->setAss();
			break;
		
		case 'existing':
			$assId = JRequest::getInt( 'aId', false );
			$model->setAss( $assId );
			break;
		}
		$properties = array(
			'assessment'=>array(
				'id', 'parent', 'title', 'short', 'description', 'color',
				'always_show', 'group_specific', 'ext_source', 'ext_id',
				'locked', 'created_by', 'valid_from', 'valid_to', 'deleted'
			),
			'aspect'=>array(
				'id', 'assessment_id', 'parent_aspect_id', 'title', 'short',
				'mark_style', 'display_style', 'boundaries',
				'shown', 'valid_from', 'valid_to', 'deleted'
			)
		);
		$model->setAssProps( $properties );
		
		$view->setModel( $model, true );
		$view->edit();
		$this->saveModel( 'admin' );
	}
	
	
	// #####  Assessment modification  #####
	
	function import()
	{
		$model = &$this->getModel( 'admin' );
		$data = JRequest::get( 'post' );
		$r = $model->update( $data );
		$view = &$this->getView( 'admin', 'html' );
		$view->setModel( $model, true );
		$view->selectAssessmentFile();
		$this->saveModel( 'admin' );
	}
	
	function uploadAssessment()
	{
		ob_start();
		$model = &$this->getModel( 'admin' );
		
		$a = &$model->getAss();
		$aspIds = array_keys( $a->getAspects() );
		$model->removeAspects( $aspIds );
		$data = $this->_parseCsv( 'filename' );
		$model->update( $data );
		
		global $mainframe;
		$mainframe->enqueueMessage( ob_get_clean() );
		$mainframe->enqueueMessage( 'Assessment with '.$this->aspCount.' aspect(s) imported.' );
		$a = &$model->getAss();
		$dependancies = array( 'assessment.assessments'=>$a->getProperty( 'id' ) );
		$link = ApotheosisLib::getActionLink( $actionId, $dependancies );
		
		$this->saveModel( 'admin' );
		$mainframe->redirect( $link );
	}
	
	function export()
	{
		$model = &$this->getModel( 'admin' );
		$view =  &$this->getView( 'admin', 'raw' );
		$data = JRequest::get( 'post' );
		$r = $model->update( $data );
		
		$a = &$model->getAss();
		$model->setAspects( array_keys(JRequest::getVar('aspselect', $a->getAspects())) );
		
		$view->setModel( $model, true );
		$view->exportAssessment();
		$this->saveModel( 'admin' );
		
	}
	
	/**
	 * Initiate a copy of the currently viewed assessment then show the edit page for that new assessment
	 * @see display
	 */
	function copy()
	{
		$model = &$this->getModel( 'admin' );
		$model->copyAss();
		$this->saveModel( 'admin' );
		$a = $model->getAss();
		JRequest::setVar( 'aId', $a->getId() );
		
		global $mainframe;
		$mainframe->enqueueMessage( 'Now working on copy. Make your changes then save this new assessment', 'message' );
		$this->display();
	}
	
	
	function repeat()
	{
		
	}
	
	
	// #####  Aspect modification  #####
	
	/**
	 * adds a new aspect to the current assessment
	 */
	function add()
	{
		$model = &$this->getModel( 'admin' );
		$data = JRequest::get( 'post' );
		$r = $model->update( $data );
		$model->addNewAspect();
		
		global $mainframe;
		$mainframe->enqueueMessage( '1 aspect added. Please fill in its title before saving' );
		$a = &$model->getAss();
		$dependancies = array( 'assessment.assessments'=>$a->getProperty( 'id' ) );
		$link = ApotheosisLib::getActionLinkByName( 'apoth_ass_admin_new', $dependancies );
		
		$this->saveModel( 'admin' );
		$mainframe->redirect( $link );
	}
	
	/**
	 * copies the selected aspects and adds them to the current assessment
	 */
	function copyAspects()
	{
		$model = &$this->getModel( 'admin' );
		$data = JRequest::get( 'post' );
		$r = $model->update( $data );
		if( $r ) {
			$n = $model->copyAspects( array_keys(JRequest::getVar('aspselect', array())) );
		}
		
		global $mainframe;
		$mainframe->enqueueMessage( $n.' aspect(s) copied.' );
		$a = &$model->getAss();
		$dependancies = array( 'assessment.assessments'=>$a->getProperty( 'id' ) );
		$link = ApotheosisLib::getActionLink( $actionId, $dependancies );
		
		$this->saveModel( 'admin' );
		$mainframe->redirect( $link );
	}
		
	/**
	 * adds a new aspect to the current assessment
	 */
	function remove()
	{
		$model = &$this->getModel( 'admin' );
		$data = JRequest::get( 'post' );
		$r = $model->update( $data );
		$n = $model->removeAspects( array_keys(JRequest::getVar('aspselect', array())) );
		
		global $mainframe;
		$mainframe->enqueueMessage( $n.' aspect(s) removed.' );
		$a = &$model->getAss();
		$dependancies = array( 'assessment.assessments'=>$a->getProperty( 'id' ) );
		$link = ApotheosisLib::getActionLink( $actionId, $dependancies );
		
		$this->saveModel( 'admin' );
		$mainframe->redirect( $link );
	}
	
	/**
	 * 
	 */
	function importAspects()
	{
		$model = &$this->getModel( 'admin' );
		$data = JRequest::get( 'post' );
		$r = $model->update( $data );
		$view = &$this->getView( 'admin', 'html' );
		$view->setModel( $model, true );
		$view->selectAspectFile();
		$this->saveModel( 'admin' );
	}
	
	/**
	 * Read in a csv file for its aspect data
	 * and add them to the current assessment
	 */
	function uploadAspects()
	{
		ob_start();
		$model = &$this->getModel( 'admin' );
		
		$data = $this->_parseCsv( 'filename' );
		$model->update( $data );
		
		global $mainframe;
		$mainframe->enqueueMessage( ob_get_clean() );
		$mainframe->enqueueMessage( $this->aspectCount.' aspect(s) imported.' );
		$a = &$model->getAss();
		$dependancies = array( 'assessment.assessments'=>$a->getProperty( 'id' ) );
		$link = ApotheosisLib::getActionLinkByName( 'apoth_ass_admin_existing', $dependancies );
		
		$this->saveModel( 'admin' );
		$mainframe->redirect( $link );
	}
	
	/**
	 * Generate a csv file with all the data for the selected aspects
	 */
	function exportAspects()
	{
		$model = &$this->getModel( 'admin' );
		$data = JRequest::get( 'post' );
		$r = $model->update( $data );
		$view =  &$this->getView( 'admin', 'raw' );
		
		$a = &$model->getAss();
		$model->setAspects( array_keys(JRequest::getVar('aspselect', $a->getAspects())) );
		
		$view->setModel( $model, true );
		$view->exportAspects();
		$this->saveModel( 'admin' );
	}
	
	
	// #####  Form handling  #####
	
	/**
	 * sets the link to use for the redirect, so the newly existing assessment is displayed
	 * calls $this->_save
	 */
	function save()
	{
		ob_start();
		$data = JRequest::get( 'post' );
		$model = &$this->getModel( 'admin' );
		$r = $model->save( $data );
		$msg = ob_get_clean();
		
		global $mainframe;
		if( $r === true ) {
			$mainframe->enqueueMessage( 'Assessment saved', 'message' );
		}
		else {
			$mainframe->enqueueMessage( 'Unable to save the changes to this assessment', 'error' );
		}
		$mainframe->enqueueMessage( $msg );
		
		$a = &$model->getAss();
		$dependancies = array( 'assessment.assessments'=>$a->getProperty( 'id' ) );
		$link = ApotheosisLib::getActionLinkByName( 'apoth_ass_admin_existing', $dependancies );
		
		$this->saveModel( 'admin' );
		$mainframe->redirect( $link );
	}
	
	
	/**
	 * Parses a csv and gives back the data array to use to effect the given changes
	 * @param $fName
	 */
	function _parseCsv( $fName )
	{
		$model = &$this->getModel( 'admin' );
		$rawCSV = $_FILES[$fName]['tmp_name'];
		$rawContents = ApotheosisLib::file_get_contents_utf8( $rawCSV );
		$cleaned = str_replace( array("\r\n", "\r"), "\n", $rawContents );
		$cleanCSV = tmpfile();
		fwrite( $cleanCSV, $cleaned );
		rewind( $cleanCSV );
		$data = array();
		$this->aspCount = 0;
		$u = ApotheosisLib::getUser();
		$uId = $u->person_id;
		$now = date( 'Y-m-d H:i:s' );
		$section = 0; // 0 = nothing, 1 = assessment, 2 = aspect
		$importables = array( 0=>array()
			, 1=>array('title', 'short', 'description', 'color', 'always_show', 'group_specific', 'valid_from', 'valid_to', 'admin_groups', 'access') 
			, 2=>array('title', 'short', 'boundaries', 'shown') );
		while( $row = fgetcsv($cleanCSV, 2048) ) {
			if( implode('', $row) == '' ) {
				$blankRows++;
				continue;
			}
			
			if( strpos($row[0], '~') !== false ) {
				// we've got ourselves a heading row
				switch( $row[0] ) {
				case( '~Assessment~' ):
					$importable = &$importables[1];
					unset( $d );
					$d = &$data;
					break;
				
				case( '~Aspects~' ) :
					$importable = &$importables[0];
					$data['asp'] = array();
					unset( $d );
					$d = array(); // send incorrectly filed data to a black hole;
					break;
				
				case( '~Aspect~' ) :
					$importable = &$importables[2];
					$this->aspCount++;
					$id = $model->addNewAspect();
					$data['asp'][$id] = array(
						  'deleted'=>0
						, 'created_by'=>$uId
						, 'created_on'=>$now );
					unset( $d );
					$d = &$data['asp'][$id];
					break;
				}
			}
			elseif( isset($d) ) {
				if( ($k = array_search($row[0], $importable)) !== false ) {
					switch( $row[0] ) {
					case( 'access' ):
						$r = unserialize( $row[1] );
						if( !is_array($r) ) { $r = array(); }
						foreach( $r as $rk=>$rv ) {
							if( !$rv ) {
								unset($r[$rk]);
							}
						}
						$d[$importable[$k]] = $r;
						break;
					default:
						$d[$importable[$k]] = $row[1];
						break;
					}
				}
			}
		}
		
		return $data;
	}
}
?>