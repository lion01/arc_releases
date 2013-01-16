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

if( isset($this->_mark['marks']) ) {
	foreach( $this->_mark['marks'] as $markInfo ) {
		$m = $markInfo['mark'];
		if( (!is_null($this->filter) && ($m->getValue() == $this->filter) ) || (is_null($this->filter)) ) {
			echo $m->getValue();
		}
		else {
			echo '--';
		}
	}
}
elseif( isset($this->_mark['count']) ) {
	echo $this->_mark['count'].' / '.$this->_mark['total'];
}
elseif( isset($this->_mark['markList']) ) {
	foreach( $this->_mark['markList'] as $code=>$count ) {
		$output[] = $code.' '.$count;
	}
	echo '"'.implode( ', ', $output ).'"';
}
elseif( is_null($this->_mark) ) {
	echo '';
}

if( isset($this->_mark['percent']) ) {
	if( isset($this->_mark['count']) ) {
		echo ' - ';
	}
	echo number_format( $this->_mark['percent'] ).'%';
}
?>