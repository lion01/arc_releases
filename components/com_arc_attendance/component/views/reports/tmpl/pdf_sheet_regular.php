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

// add marks to the array and build array of column string widths
while( ($this->row = $this->model->getMarkRow($this->sheetId)) !== false ) {
	foreach( $this->processed as $id=>$info ) {
		$this->_mark = $this->row[$info['colid']];
		end($this->processed[$id]);
		$this->processed[$id][key($this->processed[$id])]['marks'][] = array(
			'name'=>strip_tags($this->row['name']),
			'mark'=>'<td width="~markColWidth~" align="center">'.$this->loadTemplate('mark').'</td>'
		);
		$this->setPdfStrWidth( strip_tags($this->row['name']), 'sheet' );
	}
}

end( $this->processed );
$finalKey = key( $this->processed );
$newWeekRow = true;
$this->headRowStrings = array();
$this->periodRowCount = 0;
foreach( $this->processed as $k=>$week ) {
	foreach( $week as $headRow=>$value ) {
		if( $headRow != 'colid' ) {
			if( !is_array($value) ) {
				if( !empty($this->periodRow) ) {
					echo $this->loadTemplate( 'write_table' );
				}
				$newWeekRow = true;
				$this->headRowStrings[$headRow] = $headRow.' - <strong>'.$value.'</strong>';
			}
			else {
				$this->periodRow .= '<td width="~markColWidth~" align="center"><strong>'.$value['value'].'</strong></td>';
				$this->periodRowCount++;
				foreach( $value['marks'] as $j=>$row ) {
					$day = $row['name'];
					$mark = $row['mark'];
					if( $newWeekRow ) {
						$this->dayRows[$j] .= '<td width="~firstColWidth~" align="right">'.$day.'</td>'.$mark;
					}
					else {
						$this->dayRows[$j] .= $mark;
					}
				}
				$newWeekRow = false;
				if( $k == $finalKey ) {
					echo $this->loadTemplate( 'write_table' );
				}
			}
		}
	}
}
?>