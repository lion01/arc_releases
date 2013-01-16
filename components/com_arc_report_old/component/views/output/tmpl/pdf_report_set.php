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

ob_start();
$this->width = $this->pdf->getPageWidth();
$this->pdf->setTopMargin  ( 0 );
$this->pdf->setLeftMargin ( 0 );
$this->pdf->setRightMargin( 0 );
$tmp = reset($this->reports);
$studId = $tmp->getStudent();
$pageBreak = false;
$rptCount = count($this->reports);

$this->pdf->setY( 0 );
//timer( 'apothpdf - before first report' );
while( !is_null($this->report = array_shift($this->reports)) ) {
	$this->report->init( $this->report->getStudent(), $this->report->getGroup() );
	if( $pageBreak || $studId != ($studId = $this->report->getStudent()) ) {
		$this->pdf->AddPage();
		$this->pdf->setY( 0 );
		// *** add per-pupil heading section / page
	}
	$this->reportTop = $this->pdf->getY();
	$this->doc->startSection();
	
	echo $this->loadTemplate( 'single_report' );
	
	$this->pdf->setLeftMargin ( 10 );
	$this->pdf->setRightMargin( 10 );
	$this->pdf->setX( 0 );
	
	$pageBreak = $this->report->breakAfter;
	$this->doc->endSection();
	$this->report->outit(); // save memory
//timer( 'apothpdf - completed report' );
}
$pageCount = count($this->pdf->pages);
$this->pdf->AddPage();
$this->doc->startSection();

$this->pdf->setLeftMargin ( 10 );
$this->pdf->setRightMargin( 10 );
$this->pdf->setX( 0 );
$this->pdf->setY( 10 );

$o = ob_get_clean();
?>
<h3>Summary</h3>
Generated <?php echo $rptCount; ?> reports<br />
On <?php echo $pageCount; ?> pages<br />
<?php
$this->pdf->setTopMargin( 40 );
$this->pdf->writeHtml( $o, true, 0, true );
/*
timer( false, false, 'print' ); // */
?>