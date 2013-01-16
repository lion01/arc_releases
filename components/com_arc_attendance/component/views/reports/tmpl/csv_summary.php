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
		if( !isset($processed) ) {
			$processed = array_fill( 0, $maxRows = $col['decendants'], array() );
		}
		if( $col['enabled']) {
			if( $col['decendants'] > 0 ) {
				for( $j = 1; $j <= $col['decendants']; $j++ ) {
					if( $col['text'] != $curText ) {
						$curText = $processed[$i][$col['row_label']] = $col['text'];
					}
					$i++;
				}
			}
			else {
				$processed[$i]['colid'] = $col['colid'];
				$curText = $processed[$i][$col['row_label']]['value'] = $col['text'];
				$i++;
			}
		}
		if( $i == $maxRows ) {
			$i = 0;
		}
	}
}

// add marks to the array
while( ($this->row = $this->model->getMarkRow($this->sheetId)) !== false ) {
	foreach( $processed as $id=>$info ) {
		$this->_mark = $this->row[$info['colid']];
		end($processed[$id]);
		$processed[$id][key($processed[$id])]['marks'][strip_tags($this->row['name'])] = $this->loadTemplate( 'mark' );
	}
}

// get just the headrows we need
$headRows = array();
$headRow = array();
$indent = 0;
foreach( reset($processed) as $headRow=>$value ) {
	if( $headRow != 'colid' ) {
		$headRows[$headRow]['value'] = array();
		$headRows[$headRow]['indent'] = $indent++;;
	}
	else {
		break;
	}
}

// build output
end($processed);
$finalKey = key($processed);
$newWeekRow = true;
foreach( $processed as $k=>$week ) {
	foreach( $week as $headRow=>$value ) {
		if( ($headRow != $headRows[$headRow]['value']) && ($headRow != 'colid') ) {
			if( !is_array($value) ) {
				if( !empty($periodRow) ) {
					$output .= "\n".$periodRow;
					$periodRow = '';
					foreach( $dayRows as $day=>$marks ) {
						$output .= "\n".$marks;
					}
					$dayRows = array();
				}
				$output .= "\n";
				for( $i = 0; $i < $headRows[$headRow]['indent']; $i++ ) {
					$output .= ',';
				}
				$output .= $value;
				$newWeekRow = true;
			}
			else {
				if( !isset($finalIndent) ) {
					$curIndent = end( $headRows );
					$curIndent = $curIndent['indent'];
					for( $i = 0; $i < ($curIndent + 1); $i++ ) {
						$finalIndent .= ',';
					}
				}
				if( $newWeekRow ) {
					$indent = $finalIndent;
				}
				else {
					$indent = '';
				}
				$periodRow .= $indent.','.$value['value'];
				foreach( $value['marks'] as $day=>$mark ) {
					if( $newWeekRow ) {
						$dayRows[$day] .= $finalIndent.$day.','.$mark;
					}
					else {
						$dayRows[$day] .= ','.$mark;
					}
				}
				$newWeekRow = false;
				if( $k == $finalKey ) {
					$output .= "\n".$periodRow;
					$periodRow = '';
					foreach( $dayRows as $day=>$marks ) {
						$output .= "\n".$marks;
					}
					$dayRows = array();
				}
			}
		}
	}
}

echo ApotheosisData::_( 'people.displayname', reset( $this->model->getPupilList($this->sheetId) ) );
echo $output;
?>