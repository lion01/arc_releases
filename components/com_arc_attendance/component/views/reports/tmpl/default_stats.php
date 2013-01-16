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

$this->_data = $this->model->getPupilStats( $this->sheetId );
$this->statData = isset( $this->_data['statutory'] );
?>
<div class="summary_div" id="summary">
	<div class="totals_table_title_div">Attendance Summary</div>
	<div id="summary_image_div">
		<?php
		$this->setLayout( 'panel' );
		if( $this->statData ) { echo $this->loadTemplate( 'stat_pie' ); }
		echo $this->loadTemplate( 'all_histo' );
		echo $this->loadTemplate( 'all_histo_per' );
		echo $this->loadTemplate( 'att_summary_2' );
		?>
	</div>
	<div id="summary_table_div">
		<?php 
		$this->setLayout( 'default' );
		if( $this->statData ) { echo $this->loadTemplate( 'table_stat' ); }
		echo $this->loadTemplate( 'table_all' );
		echo $this->loadTemplate( 'table_all_per' );
		?>
	</div>
</div>