<?php
/**
 * @package     Arc
 * @subpackage  Report
 * @copyright   Copyright (C) 2005 Punnet. All rights reserved. See COPYRIGHT_ARC.txt
 * @license     http://www.gnu.org/licenses/gpl.html GNU/GPL. See LICENSE_ARC.txt
 * Arc is free software: you can redistribute it and/or modify
 * it under the terms of version 3 of the GNU General Public License
 * as published by the Free Software Foundation.
 * The disclaimer of warranty as stated in the GPL applies to this program
 */

defined( '_JEXEC' ) or die( 'Restricted access' );

$this->_oddrow = false;
$rows = '';
foreach ($this->subject as $this->row) {
	if( count($this->row->teachers) > 3 ) {
		$this->row->teachers = array_slice( $this->row->teachers, 0, 3 );
		$this->row->teachers[] = '...';
	}
	
	$this->_oddrow = !$this->_oddrow;
	$rows .= $this->loadTemplate('normal_row');
}
if( !empty($rows) ) :
?>
<tr>
	<th>&nbsp;</th>
	<th>Course</th>
	<th>Teacher(s)</th>
	<th>Administrator(s)</th>
</tr>
<?php
echo $rows;
endif;
?>