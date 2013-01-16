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

jimport( 'joomla.application.component.view' );

/**
 * Report View Printshare
 *
 * @author     Punnet - Arc Team <arc_developers@pun.net>
 * @package    Arc
 * @subpackage Report
 * @since      1.9.1
 */
class ReportViewPrintshare extends JView
{
	/**
	 * Displays a generic page
	 */
	function display()
	{
		$cycle = $this->get( 'Cycle' );
		$layout = $cycle->getLayout();
		$this->orderedSections = $layout->getOrderedSections();
		
		// Set up PDF, and fonts
		$this->doc = &JFactory::getDocument();
		$this->doc->getInstance( 'apothpdf' );
		
		// Set up some pdf properties
		$this->pdf = &$this->doc->getEngine();
		$this->pdf->setPrintHeader( false );
		$this->pdf->setPrintFooter( false );
		$this->pdf->setPageFormat( $layout->getDatum( 'print_page_size' ) );
		$this->pdf->setFont( $layout->getDatum( 'print_default_font' ), '', $layout->getDatum( 'print_default_font_size' ) );
		$this->pdf->setHeaderMargin( 0 );
		$this->pdf->setFooterMargin( 0 );
		$this->pdf->setMargins( 15, 15, 15, true );
		$this->pdf->setAutoPageBreak( true, 15 );
		
		$this->scaleFactor = $this->pdf->getScaleFactor();
		$this->pageLimit = $layout->getDatum( 'print_page_limit' );
		$this->margins = array(
			'l'=>$layout->getDatum( 'print_margin_left' ),
			't'=>$layout->getDatum( 'print_margin_top' ),
			'r'=>$layout->getDatum( 'print_margin_right' ),
			'b'=>$layout->getDatum( 'print_margin_bottom' ) );
		
		// Begin constructed output
		$this->setLayout( 'pdf' );
		$this->doc->startDoc();
//		dump( memory_get_usage(), 'mem usage at start of doc' );
		parent::display();
		$this->doc->endDoc();
	}
	
	/**
	 * Sets $this->reportee to the next unused reportee,
	 * and $this->subreports to the subreports for them
	 * 
	 * @return bool  false if there are no more reportees, true otherwise
	 */
	function nextReportee()
	{
		if( !isset( $this->reportees ) ) {
			$this->reportees = $this->get( 'Reportees' );
			$this->reportee = reset( $this->reportees );
		}
		else {
			$this->reportee = next( $this->reportees );
		}
		if( $this->reportee === false ) {
			$retVal = false;
		}
		else {
			$retVal = true;
			$m = $this->getModel();
			$subreports = $m->getReportSet( $this->reportee );
			
			// get and sort the subjects for the reports then re-arrange them
			// an a class var in the right order (alphabetic by subject)
			$subreportSubjects = array();
			foreach( $subreports as $sId=>$subreport ) {
				$subreportSubjects[$sId] = ApotheosisData::_( 'course.subject', $subreport->getDatum( 'rpt_group_id' ) );
			}
			$subreportSubjectNames = ApotheosisData::_( 'course.names', $subreportSubjects );
			foreach( $subreportSubjects as $sId=>$subjId ) {
				$subreportSubjects[$sId] = $subreportSubjectNames[$subjId];
			}
			
			asort( $subreportSubjects );
			
			$this->subreports = array();
			foreach( $subreportSubjects as $sId=>$subj ) {
				$this->subreports[$sId] = $subreports[$sId];
			}
			
			$this->section = null;
			$this->subreport = null;
		}
		
		return $retVal;
	}
	
	/**
	 * Sets class vars for section and subreport to be used elsewhere
	 * Goes through all sections in the order given by the layout
	 * Sections which show subreports will then show repeatedly until
	 * all subreports that use that section have been processed
	 */
	function nextSection()
	{
		// ensure the order point is set and has further information
		if( is_null( $this->section ) ) {
			// starting fresh
			$this->orderPoint = reset( $this->orderedSections );
			$this->doingSub = false;
			$this->section = false;
		}
		elseif( $this->section === false ) {
			// ran out of one orderPoint, on to the next
			$this->orderPoint = next( $this->orderedSections );
			$this->doingSub = false;
		}
			// otherwise don't change the orderPoint
		
		if( $this->orderPoint === false) {
			$retVal = false;
		}
		else {
			if( !$this->doingSub ) {
				if( $this->section === false ) {
					$this->section = reset( $this->orderPoint['non'] );
				}
				else {
					$this->section = next( $this->orderPoint['non'] );
				}
				
				if( $this->section === false ) {
					$this->doingSub = true;
				}
			}
			
			// NB this is not an "else" as the previous section may indicate
			// a need to move from 'non' sections to 'sub' sections
			if( $this->doingSub ) {
				if( $this->section === false ) {
					// new order point means new possible subreports
					// Find out which ones should be displayed at this order point
					$this->orderPointSubreports = array(); // viable subreports for this order point
					foreach( $this->orderPoint['sub'] as $section ) {
						$sectionIds[$section->getId()] = $section->getId();
					}
					foreach( $this->subreports as $sId=>$subreport ) {
						if( isset( $sectionIds[$subreport->getSectionId()] ) ) {
							$this->orderPointSubreports[] = $sId;
						}
					}
					$subId = reset( $this->orderPointSubreports );
				}
				else {
					// haven't changed order point, so get the next subreport
					$subId = next( $this->orderPointSubreports );
				}
				
				// Having found the next subreport (if any) set the section (if any)
				if( $subId === false || !isset( $this->subreports[$subId] ) ) {
					$this->subreport = false;
					$this->section = false;
				}
				else {
					$this->subreport = $this->subreports[$subId];
					$secId = $this->subreport->getSectionId();
					$this->section = $this->orderPoint['sub'][$secId];
				}
				
				if( $this->section === false ) {
					// go around again to try to find a valid section
					$this->nextSection();
				}
			}
			$retVal = $this->section !== false; // have we finally managed to find a section?
		}
		
		return $retVal;
	}
}
?>