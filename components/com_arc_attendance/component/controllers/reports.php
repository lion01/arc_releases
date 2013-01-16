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

/**
 * Attendance Controller Reg
 *
 * @author		lightinthedark <code@lightinthedark.org.uk>
 * @package		Apotheosis
 * @subpackage	Attendance
 * @since 0.1
 */
class AttendanceControllerReports extends AttendanceController
{
	/**
	 * Create a new attendance reports controller
	 */
	function __construct( $config = array() )
	{
		parent::__construct( $config );
		// Register Extra tasks
		$this->registerTask( 'tab', 'changeTab' );
		$this->registerTask( 'toggle', 'changeGrouping' );
		$this->registerTask( 'filter', 'changeFilter' );
		$this->registerTask( 'aggregate', 'changeAggregate' );
		$this->registerTask( 'expand_dates', 'expandSheet' );
		$this->registerTask( 'expand_criteria', 'expandSheet' );
		$this->registerTask( 'expand_marks', 'expandSheet' );
		$this->registerTask( 'expand_all', 'expandSheet' );
		$this->registerTask( 'show_summaries', 'expandSheet' );
	}
	
	/**
	 * Either show a full mark sheet or a summary page
	 */
	function display()
	{
		$model = &$this->getModel( 'reports' );
		$view  = &$this->getView ( 'reports', JRequest::getVar('format', 'html') );
		$view->setModel( $model, true );
		$scope = JRequest::getVar( 'scope' );
		
		switch( $scope ) {
		case( 'fire' ):
			$this->fireSearch();
			$view->showSheet();
			break;
		
		case( 'attsummary' ):
			$datasets = array();
			$u = ApotheosisLib::getUser();
			$curUserId = $u->id;
			$pId = JRequest::getVar( 'pId', $curUserId );
			$urlStart = JRequest::getVar( 'start', false );
			$urlEnd = JRequest::getVar( 'end', false );
			ApotheosisLibAcl::setDatum( 'dateFrom', $urlStart );
			ApotheosisLibAcl::setDatum( 'dateTo',   $urlEnd );
			$datasets[0] = array( 'pupil'=>$pId, 'start_date'=>$urlStart, 'end_date'=>$urlEnd );
			
			$view->$scope( $datasets );
			break;
		
		case( 'compact' ):
			$view->showCompact();
			break;
		
		default :
			$sheetStatus = $model->getMarkRowCount();
			if( is_null($sheetStatus) ) {
				$view->showInstructions();
			}
			elseif( $sheetStatus > 0 ) {
				$sheetType = $model->getMarkSheetType(); 
				if( $sheetType == 'full' ) {
					$view->showSheet();
				}
				elseif( $sheetType == 'summary' ) {
					$view->showSummary();
				}
			}
			else {
				$view->showNoMarks();
			}
		}
		$this->saveModel();
	}
	
	/**
	 * Find and then show mark(s).
	 */
	function search()
	{
		$model = &$this->getModel( 'reports' );
		
		$requirements = array();
		$requirements['start_date']    = JRequest::getString( 'start_date' , false );
		$requirements['end_date']      = JRequest::getString( 'end_date'   , false );
		$requirements['subject']       = JRequest::getString( 'subject'    , false );
		$requirements['course_id']     = JRequest::getString( 'course_id'  , false );
		$requirements['tutor_grp']     = JRequest::getString( 'tutor'      , false );
		$requirements['period_type']   = JRequest::getString( 'period_type', false );
		
		$requirements['teacher']         = JRequest::getVar( 'teacher'      , false );
		$requirements['person_id']       = JRequest::getVar( 'person_id'    , false );
		$requirements['truant_id']       = JRequest::getVar( 'truant_id'    , false );
		$requirements['day_section']     = JRequest::getVar( 'day_section'  , false );
		$requirements['academic_year']   = JRequest::getVar( 'academic_year', false );
		$requirements['att_code']        = JRequest::getVar( 'att_code'     , false );
		$requirements['toggle']          = JRequest::getVar( 'toggles'      , false );
		
		$requirements['att_percent_com'] = JRequest::getString( 'att_percent_com', false );
		$requirements['att_percent']     = JRequest::getString( 'att_percent',     false );
		
		// att_percent checks
		$badAttPercent = false;
		if( ($requirements['att_percent'] != '') && !is_numeric($requirements['att_percent']) ) {
			$badAttPercent = true;
			global $mainframe;
			$mainframe->enqueueMessage( 'The Attendance % search field is not a number.', 'warning' );
		}
		elseif( ($requirements['att_percent'] > 100) || ($requirements['att_percent'] < 0) ){
			$badAttPercent = true;
			global $mainframe;
			$mainframe->enqueueMessage( 'The Attendance % search field should be a number between 0 and 100.', 'warning' );
		}
		elseif( $requirements['att_percent'] == '' ) {
			unset( $requirements['att_percent'] );
			unset( $requirements['att_percent_com'] );
		}
		else {
			$requirements['att_percent'] = number_format( $requirements['att_percent'], 2 );
		}
		
		// merge students and truants and check for sensible search patterns
		$pupils = is_array($requirements['person_id']) ? $requirements['person_id'] : array();
		$truants = is_array($requirements['truant_id']) ? $requirements['truant_id'] : array();
		$requirements['person_id'] = array_unique( (array_merge($pupils, $truants)) );
		unset($requirements['truant_id']);
		
		// clean out unset requirements and also check it is a useful limiting search
		$limiter = 0;
		$tooBroad = false;
		foreach( $requirements as $k=>$v ) {
			if( is_array($v) ) {
				foreach( $v as $k2=>$v2 ) {
					if( empty($v2) ) {
						unset( $requirements[$k][$k2] );
					}
				}
			}
			if( empty($requirements[$k]) && ($requirements[$k] !== '0') ) {
				unset( $requirements[$k] );
			}
			elseif( count($v) < 10 ) {
				$limiter++;
			}
		}
		
		// are we limiting the search enough?
		if( ($model->getUsersTmpPplNum() > 200) && ($limiter < 3) ) {
			$tooBroad = true;
			global $mainframe;
			$mainframe->enqueueMessage( 'This search is too broad. Please be more specific.', 'warning' );
		}
		
		// check for messages or or proceed
		if( !$badAttPercent && !$tooBroad ) {
			$model->setMarkSheet( $requirements );
		}
		
		$this->display();
	}
	
	function fireSearch()
	{
		$model = &$this->getModel( 'reports' );
		
		require_once( JPATH_SITE.DS.'components'.DS.'com_arc_attendance'.DS.'helpers'.DS.'data_access.php' );
		$absMarks = ApotheosisAttendanceData::getCodeObjects( array('physical_meaning'=>array(3, 4, 5)), false );
		unset($absMarks['-']);
		$absCodes = array_keys($absMarks);
		unset($absMarks);
		
		$requirements = array();
		$requirements['start_date']    = date('Y-m-d H:i:s');
		$requirements['end_date']      = date('Y-m-d H:i:s');
//		$requirements['period_type']   = 1;
		$requirements['att_code']      = $absCodes;
		$requirements['toggle']        = array( 'year', 'pupil' );
		
		$model->setMarkSheet( $requirements );
	}
	
	/**
	 * Change the mark sheet by drilling down into this mark sheet
	 * then display the sheet
	 */
	function drillDown()
	{
		$model = &$this->getModel( 'reports' );
		$model->drillDown( JRequest::getVar( 'sheetId', null ), JRequest::getVar('scope', null) );
		$this->display();
	}
	
	/**
	 * Change the mark sheet(s) by drilling up through the breadcrumbs
	 * then display the sheet(s) at that breadcrumb
	 */
	function drillUp()
	{
		$model = &$this->getModel( 'reports' );
		$model->drillUp( JRequest::getVar('breadcrumb', null) );
		$this->display();
	}
	
	/**
	 * Change the mark sheet display of aggregate data
	 * then display the sheet
	 */
	function changeAggregate()
	{
		$model = &$this->getModel( 'reports' );
		$sheetId = JRequest::getVar( 'sheetId', null );
		$agg = JRequest::getVar( 'aggregate', null );
		
		if( !is_null($sheetId) && !is_null($agg) ) {
			$model->setAggregateType( $sheetId, $agg );
		}
		$this->display();
	}
	
	/**
	 * Change the mark sheet display by showing only the selected mark
	 * then display the sheet
	 */
	function changeFilter()
	{
		$model = &$this->getModel( 'reports' );
		$sheetId = JRequest::getVar( 'sheetId', null );
		$filter = JRequest::getVar( 'mark_filter_select', null );
		
		if( !is_null($sheetId) ) {
			$model->setFilter( $sheetId, $filter );
		}
		$this->display();
	}
	
	/**
	 * Change the mark sheet display by showing more or less rows
	 * then display the sheet
	 */
	function changeGrouping()
	{
		$model = &$this->getModel( 'reports' );
		$sheetId = JRequest::getVar( 'sheetId', null );
		$toggle = JRequest::getVar( 'toggle', null );
		
		if( !is_null($sheetId) and !is_null($toggle) ) {
			$model->setToggle( $sheetId, $toggle );
		}
		$this->display();
	}
	
	/**
	 * Change the mark sheet display by changing which columns should be shown
	 * then display the sheet
	 */
	function changeTab()
	{
		$model = &$this->getModel( 'reports' );
		$sheetId = JRequest::getVar( 'sheetId', null );
		$tab = JRequest::getVar( 'tab', null );
		
		if( !is_null($sheetId) and !is_null($tab) ) {
			$model->setHeadActive( $sheetId, $tab );
		}
		$this->display();
	}
	
	/**
	 * Expand the current marksheet(s) in some way then display it
	 */
	function expandSheet()
	{
		$model = &$this->getModel( 'reports' );
		$model->expandMarkSheet( strtolower(JRequest::getString('task')) );
		$this->display();
	}
	
	function edit()
	{
		$model = &$this->getModel( 'reports' );
		$sheetId = JRequest::getVar( 'sheetId', null );
		switch( strtolower(JRequest::getString('submit')) ) {
		case( 'apply' ):
			$rows = JRequest::getVar( 'rows', array() );
			$rowColTuples = JRequest::getVar( 'rows_matrix' );
			foreach( $rowColTuples as $k=>$serTuple ) {
				if( isset($rows[$k]) ) {
					$rowColTuples[$k] = unserialize($serTuple);
				}
				else {
					unset( $rowColTuples[$k] );
				}
			}
			$num = $model->replaceMarks( $sheetId, JRequest::getVar('edit_find'), JRequest::getVar('edit_replace'), $rowColTuples, JRequest::getVar('edit_day_section'), JRequest::getVar('edit_start_date'), JRequest::getVar('edit_end_date') );
			$model->setRowEdits( $sheetId, array(), true ); // make no rows editable after save
			global $mainframe;
			if( $num == 0 ) {
				$mainframe->enqueueMessage( 'Did not find any marks to change', 'warning' );
			}
			else {
				$mainframe->enqueueMessage( 'Changed '.$num.' marks from '.JRequest::getVar('edit_find').' to '.JRequest::getVar('edit_replace'));
			}
			break;
		
		case( 'edit' ):
			$rows = JRequest::getVar( 'rows' );
			$rows = (empty($rows) ? array() : array_keys($rows) );
			$model->setRowEdits( $sheetId, $rows, true );
			break;
		
		case( 'save' ):
			$marks = JRequest::getVar( 'marks' );
			$marks = (empty($marks) ? array() : $marks );
			$rows = $model->setMarks( $sheetId, $marks );
			$model->setRowEdits( $sheetId, $rows['bad'], true ); // make only rows that didn't fully save editable after save
			global $mainframe;
			if( empty($rows['bad']) ) {
				$mainframe->enqueueMessage( 'All marks saved successfully' );
			}
			else {
				$mainframe->enqueueMessage( 'Some marks could not be changed, probably because someone else editted the mark at the same time as you.', 'warning' );
				$mainframe->enqueueMessage( 'Press "save" again to accept the marks as they are currently listed or re-edit them and then save', 'warning' );
			}
			
			break;
			
		default:
			var_dump_pre(JRequest::getString('submit'), 'could not match task: ');
		}
		$this->display();
	}
}
?>
