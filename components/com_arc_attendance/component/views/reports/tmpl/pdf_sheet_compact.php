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

// add marks to the array
while( ($this->row = $this->model->getMarkRow($this->sheetId)) !== false ) {
	foreach( $this->processed as $id=>$info ) {
		$this->_mark = $this->row[$info['colid']];
		end($this->processed[$id]);
		$this->processed[$id][key($this->processed[$id])]['marks'][] = array(
			'mark'=>$this->loadTemplate('mark')
		);
	}
}

// column widths for the compact pdf marks table
$this->compactWeekCol = 50;
$this->compactMarkCol = 20;

end( $this->processed );
$finalKey = key( $this->processed );
$marks = array();
$headRowStrings = array();
foreach( $this->processed as $k=>$v ) {
	foreach( $v as $headRow=>$value ) {
		if( $headRow != 'colid' ) {
			if( !is_array($value) ) {
				if( !empty($marks) ) {
					$week = date( 'd/m/Y', strtotime($headRowStrings['Week'].' '.$headRowStrings['Year']) );
					$curWeek = '';
					foreach( $marks as $day=>$markArray ) {
						$markStr = implode( ' ', $markArray );
						if( $curWeek == '' ) {
							$curWeek .= '<td width="'.$this->compactWeekCol.'" align="center">'.$week.'</td>';
						}
						$curWeek .= '<td width="'.$this->compactMarkCol.'" align="center">'.$markStr.'</td>';
					}
					$marks = array();
					$this->weekRows[] = $curWeek;
				}
				$headRowStrings[$headRow] = $value;
			}
			else {
				if( !isset($this->dayCount) ) {
					$this->dayCount = count( $value['marks'] );
				}
				foreach( $value['marks'] as $day=>$row ) {
					$marks[$day][] = $row['mark'];
				}
				if( $k == $finalKey ) {
					$week = date( 'd/m/Y', strtotime($headRowStrings['Week'].' '.$headRowStrings['Year']) );
					$curWeek = '';
					foreach( $marks as $day=>$markArray ) {
						$markStr = implode( ' ', $markArray );
						if( $curWeek == '' ) {
							$curWeek .= '<td width="'.$this->compactWeekCol.'" align="center">'.$week.'</td>';
						}
						$curWeek .= '<td width="'.$this->compactMarkCol.'" align="center">'.$markStr.'</td>';
					}
					$marks = array();
					$this->weekRows[] = $curWeek;
				}
			}
		}
	}
}

// Setup required data for the attendance breakdown table
$this->_data = $this->model->getPupilStats( $this->sheetId );
$this->loadTemplate( 'stat_pie' );

$this->pdf->writeHtml( $this->loadTemplate('table_compact') );
?>