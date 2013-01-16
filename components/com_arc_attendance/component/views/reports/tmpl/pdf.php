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

// Column widths for summary tables
$this->col1 = 14;
$this->col3 = 42;
$this->col4 = 30;

switch( $this->perspective ) {
case( 'sheet' ):
	$this->firstSheetPage = true;
	$this->pdf->writeHtml( $this->loadTemplate('sheet_header') );
	$this->loadTemplate( 'sheet' );
	break;

case( 'summary' ):
	$this->firstSheetPage = false;
	$this->pdf->writeHtml( $this->loadTemplate('summary_header') );
	$this->loadTemplate( 'stats' );
	$this->doc->pdfWriteHtmlWithPageBreak( $this->loadTemplate('totals') );
	$this->loadTemplate( 'sheet' );
	break;

case( 'compact' ):
	$this->pdf->writeHtml( $this->loadTemplate('compact_header') );
	$this->loadTemplate( 'sheet' );
	break;
}
?>