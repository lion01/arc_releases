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

// sort the head rows
$headRows = $this->model->getHeadRowGrid( $this->sheetId );
$i = 0;
$curText = '';
foreach( $headRows as $headRow ) {
	foreach( $headRow as $col ) {
		if( !isset($this->processed) ) {
			$this->processed = array_fill( 0, $maxRows = $col['decendants'], array() );
		}
		if( $col['enabled']) {
			if( $col['decendants'] > 0 ) {
				for( $j = 1; $j <= $col['decendants']; $j++ ) {
					if( $col['text'] != $curText ) {
						$curText = $this->processed[$i][$col['row_label']] = $col['text'];
					}
					$i++;
				}
			}
			else {
				$this->processed[$i]['colid'] = $col['colid'];
				$curText = $this->processed[$i][$col['row_label']]['value'] = $col['text'];
				$i++;
			}
		}
		if( $i == $maxRows ) {
			$i = 0;
		}
	}
}

// build output
switch( $this->perspective ) {
case( 'sheet' ):
case( 'summary' ):
	$this->loadTemplate( 'sheet_regular' );
	break;

case( 'compact' ):
	$this->loadTemplate( 'sheet_compact' );
	break;
}
?>