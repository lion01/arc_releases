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

$headRows = $this->model->getHeadRowGrid( $this->sheetId );
foreach( $headRows as $headRow ) {
	foreach( $headRow as $col ) {
		if( $col['enabled'] ) {
			echo ','.$col['text'];
			for( $i = 1; $i < $col['decendants']; $i++ ) {
				echo ',';
			}
		}
	}
	if( $col['enabled'] ) {
		echo "\n";
	}
}
$heads = $headRow;

while( ($this->row = $this->model->getMarkRow($this->sheetId)) !== false ) {
	echo '"'.str_replace( '"', '""', $this->row['name'] ).'"';
	foreach( $heads as $id=>$info ) {
		$this->colId = $info['colid'];
		$this->_mark = $this->row[$this->colId];
		echo ','.$this->loadTemplate( 'mark' );
	}
	echo "\n";
}
?>