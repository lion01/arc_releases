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

$count = 0;
$safety = 0;
$endMessage = '';
$left = false;
$this->setBoundsAndBleeds( $left );
$this->addCropMarks( $left );
$pageBounds = $this->getBounds();
$availableHeight = $pageBounds['h'];
$secOnPage = 0;

while( $this->nextReportee() ) {
//	dump( $this->reportee, '-- reportee' );
	$count++;
	$reporteePageCount = 1;
	$prevBottom = 0;
	$prevMargin = $pageBounds['t'];
	while( $this->nextSection() ) {
//		dump( $this->section->getId(), 'section' );
//		dump( $this->subreport, 'subreport' );
//		if( $safety++ > 7 ) { break; }
		
		// calculate if this section can fit on the current page
		// and if a new page is needed, if it is within the limiting number of pages
		if( $secOnPage > 0 ) {
			$interSectionMargin = max( $prevMargin, $this->section->getDatum( 'print_margin_top' ) );
			$pageBounds['t'] += $interSectionMargin;
			$availableHeight -= $interSectionMargin;
		}
		$availableHeight -= $this->section->getDatum( 'print_height' );
		if( $availableHeight < 0 ) {
			if( $reporteePageCount >= $this->pageLimit ) {
				$endMessage .= 'Report for '.ApotheosisData::_( 'people.displayname', $this->reportee ).' would not fit. Truncated.<br />';
				break; // stop processing sections for this reportee
			}
			$this->pdf->addPage();
			$left = !$left;
			$this->setBoundsAndBleeds( $left );
			$this->addCropMarks( $left );
			$pageBounds = $this->getBounds();
			$availableHeight = $pageBounds['h'] - $this->section->getDatum( 'print_height' );
			$secOnPage = 0;
			
			$reporteePageCount++;
		}
		
		// Get a subreport (either the one to display, or the first one) to use as a context / data source
		$s = ( is_object( $this->subreport ) ? $this->subreport : reset( $this->subreports ) );
		
		// Tell the section where it can render, then render it
		$this->section->setPDFBoundingBox( $pageBounds, $pageBounds['w'], $availableHeight );
		$this->section->renderPDF( $s, $this->pdf );
		$secOnPage++; // keep track of sections on the page;
		
		// Find out how far down the page that section moved things to allow
		// calculation on next itteration of if a new page is needed
		$sectionBounds = $this->section->getPDFBoundingBox();
		$prevMargin = $this->section->getDatum( 'print_margin_bottom' );
		$pageBounds['t'] += $this->section->getDatum( 'print_height' );
	}
	
	// add blank pages to bring up to limit
	while( $reporteePageCount++ < $this->pageLimit ) {
		$endMessage .= 'Report for '.ApotheosisData::_( 'people.displayname', $this->reportee ).' too short. Adding page.<br />';
		$this->pdf->addPage();
		$left = !$left;
		$this->setBoundsAndBleeds( $left );
		$this->addCropMarks( $left );
		$pageBounds = $this->getBounds();
		$availableHeight = $pageBounds['h'];
		$secOnPage = 0;
		
		$bpPosX = $pageBounds['l'] + ( ($pageBounds['w'] - 15) / 2 );
		$bpPosY = $pageBounds['t'] + ( ($pageBounds['h'] - 6 ) / 2 );
		
		// *** these probably need refining in light of bleed size
		$this->pdf->setLeftMargin( $bpPosX );
		$this->pdf->setTopMargin( $bpPosY );
		$this->pdf->setRightMargin( 0 );
		$this->pdf->setXY( $bpPosX, $bpPosY );
		
		$this->pdf->writeHTML( '[[blank page]]' );
	}
//	$this->loadTemplate( 'report' );
	// start next reportee on their own page
	$this->pdf->addPage();
	$left = !$left;
	$this->setBoundsAndBleeds( $left );
	$this->addCropMarks( $left );
	$pageBounds = $this->getBounds();
	$availableHeight = $pageBounds['h'];
	$secOnPage = 0;
	
//	dump( memory_get_usage(), 'mem usage after report '.$count );
}

$this->pdf->SetMargins( $pageBounds['l'], $pageBounds['t'], $pageBounds['r'] );
$this->pdf->SetXY( $pageBounds['l'], $pageBounds['t'] );
?>

<p>Generated <?php echo $count; ?> reports across <?php echo $this->pdf->getNumPages() - 1; ?> pages</p>
<p><?php echo $endMessage; ?></p>