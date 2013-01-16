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

jimport('joomla.application.component.view');

/**
 * Attendance Manager Reports View
 *
 * - Required reports: (though perhaps we should build a more general, user-definable system for this)
 * View historical register
 * Daily breakdown: pupil rows = pupils, cols = periods, select pupils from multi-select list, selectall box (mouseover to show course) (grouped by tutor)
 * Course breakdown: rows = pupils, cols = date/periods for that course, remember adhocs, select date range
 * Individual (or multiple, grouped or separate) pupil summary marks over time (graph) (select period, select marks of concern)
 * Individual (or multiple, grouped or separate) daily summary: total attendance marks for that day (bar graph) (select marks of interest)
 *
 * @author		lightinthedark <code@lightinthedark.org.uk>
 * @package		Apotheosis
 * @subpackage	Attendance
 * @since 0.1
 */
class AttendanceViewReports extends JView 
{
	/**
	 * Constructor
	 */
	function __construct( $config = array() )
	{
		parent::__construct( $config );
		$this->_varMap = array('state'=>'State');
	}
	
	
	/**
	 * Display all the marks that a pupil has received in a given date range / subject
	 */
	function pupilMarks()
	{
		ApotheosisLib::setViewVars($this, $this->_varMap);
		
		JResponse::setHeader('Content-Type', 'application/octet-stream');
		JResponse::setHeader('Content-Disposition', 'attachment; filename="att_data.csv"');
		
		$this->pageTitle = "Pupil attendance";
		$this->report = 'pupil_details';
		
		$model = &$this->getModel('reports');
		$tmp = &$model->getTimetable();
		$p = JRequest::getString( 'pupil', false );
		if( ($p !== false) && @array_key_exists($p, $tmp) ) {
			$this->tt = $tmp[$p];
		}
		else {
			$this->tt = @reset($tmp);
		}
		$this->marks = $model->getRawMarks();
		
		$this->headings = array();
		foreach($this->tt as $date=>$row) {
			foreach($row as $section) {
				$this->headings[$section->day_section] = $section->day_section;
			}
		}
		
		$this->setLayout( 'raw' );
		parent::display( 'pupil_details' );
	}
	
	/**
	 * Display graphical panel summaries of attendance
	 */
	function attSummary( $datasets )
	{
		$this->_datasets = &$datasets;
		$this->model = &$this->getModel( 'reports' );
		$this->colours = array( '00ff00', '66ff66', 'ff6600', 'ff0000', 'aaaa66', 'c0c0c0' );
		
		$this->setLayout( 'panel' );
		parent::display( 'att_summary' );
	}
	
	
	// #####  CSV generation #####
	
	/**
	 * Show a mark sheet
	 */
	function showSheet()
	{
		$this->model = &$this->getModel( 'reports' );
		$this->sheetId = JRequest::getVar( 'sheetId' );
		$this->filter = $this->model->getFilter( $this->sheetId );
		
		JResponse::setHeader('Content-Type', 'application/octet-stream');
		JResponse::setHeader('Content-Disposition', 'attachment; filename="attendance.csv"');
		
		$this->setLayout( 'csv' );
		$this->display();
	}
	
	/**
	 * Show a summary sheet
	 */
	function showSummary()
	{
		$this->model = &$this->getModel( 'reports' );
		$this->sheetId = JRequest::getVar( 'sheetId' );
		$this->filter = $this->model->getFilter( $this->sheetId );
		
		JResponse::setHeader('Content-Type', 'application/octet-stream');
		JResponse::setHeader('Content-Disposition', 'attachment; filename="attendance.csv"');
		
		$this->setLayout( 'csv' );
		$this->display( 'summary' );
	}
}
?>