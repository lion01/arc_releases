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

foreach( $this->_datasets as $dataset ) {
	$this->_data = ApotheosisData::_( 'attendance.dataSummary', $dataset );
	echo $this->loadTemplate( 'stat_pie' );
	
	if( isset($this->_data['all']) && isset($this->_data['all_totals']) && isset($this->_data['all_possible']) ) {
		echo $this->loadTemplate( 'all_histo' );
		echo $this->loadTemplate( 'all_histo_per' );
	}
}
echo $this->loadTemplate( 'att_summary_2' );
?>