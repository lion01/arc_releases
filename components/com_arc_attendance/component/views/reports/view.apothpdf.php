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
	 * Generates a pdf of the attendance report
	 */
	function display()
	{
		// Give access to mark rendering
		require_once( JPATH_SITE.DS.'components'.DS.'com_arc_attendance'.DS.'helpers'.DS.'data_access.php' );
		
		// Set up PDF, and fonts
		$this->doc = &JFactory::getDocument();
		$this->doc->getInstance( 'apothpdf' );
		$this->pdf = &$this->doc->getEngine();
		
		// Set up some pdf properties
		$this->margin = 15;
		$this->scaleFactor = $this->pdf->getScaleFactor();
		$this->pdf->setPrintHeader( false );
		$this->pdf->setPrintFooter( false );
		$this->pdf->setFont( $this->doc->getFont(), '', 7 );
		$this->pdf->setHeaderMargin( 0 );
		$this->pdf->setFooterMargin( 0 );
		$this->pdf->setMargins( $this->margin, $this->margin, $this->margin, true );
		$this->pdf->setAutoPageBreak( true, $this->margin );
		$this->pdf->setImageScale( 1 );
		$this->usableWidth = ( ($this->pdf->getPageWidth() - ($this->margin * 2)) * $this->scaleFactor );
		
		// Begin constructed output
		$this->setLayout( 'pdf' );
		$this->doc->startDoc();
		parent::display();
		$this->doc->endDoc();
	}
	
	/**
	 * Show a mark sheet
	 */
	function showSheet()
	{
		$this->model = &$this->getModel( 'reports' );
		$this->sheetId = JRequest::getVar( 'sheetId' );
		$this->filter = $this->model->getFilter( $this->sheetId );
		$this->perspective = 'sheet';
	
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
		$this->perspective = 'summary';
		$this->colours = array( '00ff00', '66ff66', 'ff6600', 'ff0000', 'aaaa66', 'c0c0c0' );
		
		$this->display();
	}
	
	/**
	 * Show a compact summary sheet
	 */
	function showCompact()
	{
		$this->model = &$this->getModel( 'reports' );
		$this->sheetId = JRequest::getVar( 'sheetId' );
		$this->filter = $this->model->getFilter( $this->sheetId );
		$this->perspective = 'compact';
		
		$this->display();
	}
	
	/**
	 * Store the width of a given string as it will appear on a pdf
	 * 
	 * @param string $str  String whose pdf width we want
	 * @param string $element  Element to track
	 */
	function setPdfStrWidth( $str, $element )
	{
		if( !is_array($this->strWidths[$element]) ) {
			$this->strWidths[$element] = array();
		}
		
		$this->strWidths[$element][] = $this->pdf->getStringWidth( $str );
	}
	
	/**
	 * Substitute in table column widths for main chart tables
	 * 
	 * @param string $table  HTML of table to set column widths in
	 * @param string $element  Element to track
	 * @param boolean $reset  Should we reset the class variable holding the string widths
	 * @return string $table  HTML with substitutions made
	 */
	function setTableColWidths( $table, $element, $reset = true )
	{
		// substitute in all column widths that depend on the newly derived width of column 2
		$this->col2 = ( max($this->strWidths[$element]) * $this->scaleFactor ) + 6;
		if( $reset ) {
			$this->strWidths[$element] = array();
		}
		$table = str_replace( '~col1+col2~', ($this->col1 + $this->col2), $table );
		$table = str_replace( '~col2~', $this->col2, $table );
		
		return $table;
	}
	
	/**
	 * Substitute in table column widths for totals stat attendance table
	 * 
	 * @param string $table  HTML of table to set column widths in
	 * @return string $table  HTML with substitutions made
	 */
	function setStatTableColWidths( $table )
	{
		// substitute in all column widths that depend on the derived widths
		$this->colTitle = max( ((max($this->strWidths['title']) * $this->scaleFactor) + 6), $this->col4 );
		$this->colSection = ( max($this->strWidths['section']) * $this->scaleFactor ) + 6;
		$this->colTotal = ( $this->colSection + (count($this->strWidths['title'])*$this->colTitle) );
		
		$table = str_replace( '~total~', $this->colTotal, $table );
		$table = str_replace( '~section~', $this->colSection, $table );
		$table = str_replace( '~title~', $this->colTitle, $table );
		
		return $table;
	}
	
	/**
	 * Substitute in table column widths for personal details table
	 * 
	 * @param string $table  HTML of table to set column widths in
	 * @param string $element  Element to track
	 * @return string $table  HTML with substitutions made
	 */
	function setPersonalTableColWidths( $table, $element )
	{
		// Derive suitable col width for title column
		$titleWidth = 0;
		$titleMax = $this->personalTableWidth / 2;
		foreach( $this->strWidths[$element] as $strWidth ) {
			$tmp = $strWidth * $this->scaleFactor + 6;
			if( ($tmp > $titleWidth) && ($tmp <= $titleMax) ) {
				$titleWidth = $tmp;
			}
		}
		$valueWidth = $this->personalTableWidth - $titleWidth - 3;
		
		$table = str_replace( '~title~', $titleWidth, $table );
		$table = str_replace( '~value~', $valueWidth, $table );
		
		return $table;
	}
}
?>