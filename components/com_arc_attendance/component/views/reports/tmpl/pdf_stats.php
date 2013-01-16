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

// Setup required data
$this->_data = $this->model->getPupilStats( $this->sheetId );
$this->statData = isset( $this->_data['statutory'] );

// Call the templates in advance to set up the required column widths and various class variables
if( $this->statData ) {
	$this->loadTemplate('stat_pie');
	$tableStat = $this->loadTemplate('table_stat');
}
$this->loadTemplate('all_histo');
$tableAll = $this->loadTemplate('table_all');
$this->loadTemplate('all_histo_per');
$tableAllPer = $this->loadTemplate('table_all_per');

// Output the charts / tables
if( $this->statData ) {
	$this->tableTitle = 'Statutory Attendance';
	$this->imageUrl = $this->statPieImageUrl;
	$this->curTable = $tableStat;
	$this->doc->pdfWriteHtmlWithPageBreak( $this->loadTemplate('summary_table') );
	unlink( $this->imageFile );
}

$this->tableTitle = 'Class Attendance';
$this->imageUrl = $this->allHistoImageUrl;
$this->curTable = $tableAll;
$this->doc->pdfWriteHtmlWithPageBreak( $this->loadTemplate('summary_table') );
unlink( $this->imageFile );

$this->tableTitle = 'Class Attendance as a Percentage of Total';
$this->imageUrl = $this->allHistoPerImageUrl;
$this->curTable = $tableAllPer;
$this->doc->pdfWriteHtmlWithPageBreak( $this->loadTemplate('summary_table') );
unlink( $this->imageFile );
?>