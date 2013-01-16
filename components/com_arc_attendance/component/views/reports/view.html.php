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
	 * Creates a new attendance reporting view
	 */
	function __construct( $config = array() )
	{
		parent::__construct( $config );
	}
	
	/**
	 * Show instructions on how to use these pages
	 */
	function showInstructions()
	{
		$this->perspective = 'instructions';
		$this->display();
	}
	
	/**
	 * Indicate that no marks were found
	 */
	function showNoMarks()
	{
		$this->perspective = 'no_marks_found';
		$this->display();
	}
	
	/**
	 * Show a mark sheet
	 */
	function showSheet()
	{
		$this->perspective = 'sheet';
		$this->display();
	}
	
	/**
	 * Show a summary sheet
	 */
	function showSummary()
	{
		$this->colours = array( '00ff00', '66ff66', 'ff6600', 'ff0000', 'aaaa66', 'c0c0c0' );
		$this->perspective = 'summary';
		$this->display();
	}
	
	/**
	 * Generate and return all the "expand" links for a given marksheet(s)
	 */
	function getExpandLinks()
	{
		$links = array();
		
		if( $this->perspective == 'summary' ) {
			if( ($l = ApotheosisLibAcl::getUserLinkAllowed('att_reports_dates', array())) && $this->get('expanddates') ) {
				$links[] = '<a href="'.$l.'">Expand dates</a>';
			}
			if( ($l = ApotheosisLibAcl::getUserLinkAllowed('att_reports_criteria', array())) && $this->get('expandsearch') ) {
				$links[] = '<a href="'.$l.'">Expand search</a>';
			}
			if( ($l = ApotheosisLibAcl::getUserLinkAllowed('att_reports_all', array())) && $this->get('expandcompact') ) {
				$links[] = '<a href="'.$l.'">Expand all</a>';
			}
		}
		elseif( $this->perspective == 'sheet' ) {
			if( ($l = ApotheosisLibAcl::getUserLinkAllowed('att_reports_marks', array())) && $this->get('expandmarks') ) {
				$links[] = '<a href="'.$l.'">Expand marks</a>';
			}
			if( ($l = ApotheosisLibAcl::getUserLinkAllowed('att_reports_show_summaries', array())) ) {
				$links[] = '<a href="'.$l.'">Expand into summaries</a>';
			}
		}
		
		return implode( ' | ', $links );
	}
}
?>
