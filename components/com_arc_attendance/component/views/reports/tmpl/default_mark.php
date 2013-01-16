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
?>
<td>
<?php
if( isset($this->_mark['marks']) ) {
	foreach( $this->_mark['marks'] as $markInfo) {
		$m = $markInfo['mark'];
		$filter = $this->model->getFilter( $this->sheetId );
		if( (!is_null($filter) && ($m->getValue() == $filter) )
		 ||  (is_null($filter)) ) {
			if( $this->row['edits'] ) {
				$n = 'marks['.$markInfo['row'].']['.$markInfo['col'].']';
				JRequest::setVar( $n, $m->getValue() );
				echo JHTML::_( 'arc_attendance.codelist', $n );
				if( $m->getError() !== false ) {
					$inf = $m->getErrorInfo();
					echo '<br />Tried changing '.$inf['was'].' to '.$inf['tried'].'.<br /><a href="#" onclick="$(\'marks'.$this->rowId.$this->colId.'\').value=\''.$inf['tried'].'\'; return false;">Try again</a>';
				}
			}
			else {
				echo $m->render();
			}
		}
		else {
			echo '--';
		}
	}
}
elseif( isset($this->_mark['count']) ) {
	if( $this->_mark['count'] < $this->_mark['total'] ) {
		$class = 'few';
	}
	elseif( $this->_mark['count'] > $this->_mark['total'] ) {
		$class = 'many';
	}
	else {
		$class = 'right';
	}
	echo '<span class="'.$class.'">'.$this->_mark['count'].' / '.$this->_mark['total'].'</span>';
}
elseif( isset($this->_mark['markList']) ) {
	foreach( $this->_mark['markList'] as $code=>$count ) {
		$output[] = $code.' '.$count;
	}
	echo implode( ', ', $output );
}
elseif( is_null($this->_mark) ) {
	echo '&nbsp;';
}

if( isset($this->_mark['percent']) ) {
	if( $this->_mark['percent'] < 100 ) {
		$class = 'few';
	}
	elseif( $this->_mark['percent'] > 100 ) {
		$class = 'many';
	}
	else {
		$class = 'right';
	}
	if( isset($this->_mark['count']) ) {
		echo ' - ';
	}
	echo '<span class="'.$class.'">'.number_format( $this->_mark['percent'] ).'%</span>';
}
?>
</td>