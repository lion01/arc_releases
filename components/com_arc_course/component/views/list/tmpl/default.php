<?php
/**
 * @package     Arc
 * @subpackage  Course
 * @copyright   Copyright (C) 2005 Punnet. All rights reserved. See COPYRIGHT_ARC.txt
 * @license     http://www.gnu.org/licenses/gpl.html GNU/GPL. See LICENSE_ARC.txt
 * Arc is free software: you can redistribute it and/or modify
 * it under the terms of version 3 of the GNU General Public License
 * as published by the Free Software Foundation.
 * The disclaimer of warranty as stated in the GPL applies to this program
 */

defined( '_JEXEC' ) or die( 'Restricted access' );
?>
<h3>All the courses currently stored in the system</h3>
<h4>... that you're allowed to see</h4>

<table><tr>
<?php
	$firstRow = reset($this->courses);
	foreach ($firstRow as $heading=>$v) {
		echo '<th>'.JText::_( $heading ).'</th>';
	}
?>
</tr>

<?php
	$oddrow = false;
	foreach ($this->courses as $row) {
		echo '<tr '.(($oddrow) ? 'class="oddrow"' : '').'>';
		foreach ($row as $col=>$val) {
			echo '<td>'.$val.'</td>';
		}
		echo '</tr>';
		$oddrow = !$oddrow;
	}
?>
</table>