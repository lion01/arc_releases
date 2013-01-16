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
<?php $template = 'student_report'; ?>
<tr>
	<th>Student</th>
	<th></th>
	<th>Written</th>
	<th>Checked</th>
	<?php foreach( $this->fields as $k=>$v) { 
		echo '<th>'.$v->titleHtml().'</th>'."\n";
	} ?>
</tr>
<?php else : ?>
<?php $template = 'student_row'; ?>
<tr>
	<th>Student</th>
	<th></th>
	<th></th>
	<th>Written</th>
	<th>Checked</th>
	<th>... By</th>
	<th>... On</th>
</tr>
<?php endif; ?>
<?php
$this->_oddrow = false;
foreach ($this->students as $this->row) {
	$this->existing = ( ( isset($this->written[$this->row->pupilid]) && is_array($this->written[$this->row->pupilid]) ) ? $this->written[$this->row->pupilid] : array() );
	echo $this->loadTemplate($template);
	$this->_oddrow = !$this->_oddrow;
}
?>
