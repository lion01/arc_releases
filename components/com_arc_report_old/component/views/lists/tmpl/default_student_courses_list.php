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

if( $this->layout == 'report' ) : ?>
<?php $template = 'student_courses_report'; ?>
<tr>
	<th>Subject</th>
	<th></th>
	<th>Written</th>
	<th>Checked</th>
	<?php foreach( $this->fields as $k=>$v) { 
		echo '<th>'.$v->titleHtml().'</th>'."\n";
	} ?>
</tr>
<?php else : ?>
<?php $template = 'student_courses_row'; ?>
<tr>
	<th>Subject</th>
	<th>Class</th>
	<th></th>
	<th></th>
	<th>Written</th>
	<th>Checked</th>
	<th>... By</th>
	<th>... On</th>
</tr>
<?php endif; ?>
<?php
if($this->studentCoursesWanted == false) {
	$this->studentCoursesWanted = $this->studentCourses;
}
foreach ( $this->studentCoursesWanted as $this->rowKey=>$this->row ) {
	if( empty($this->row->_children) ) {
		$this->existing = ( is_array($this->written[$this->row->id]) ? $this->written[$this->row->id] : array() );
		$this->_oddrow = !$this->_oddrow;
		echo $this->loadTemplate( $template );
	}
}
?>