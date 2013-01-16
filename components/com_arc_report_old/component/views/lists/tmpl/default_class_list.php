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
foreach ($this->classes as $this->row) {
	$this->_oddrow = !$this->_oddrow;
	$rows .= $this->loadTemplate('class_row');
}
if( !empty($rows) ) :
?>
<tr>
	<th>&nbsp;</th>
	<th>Class</th>
	<th>Teacher(s)</th>
</tr>
<?php
echo $rows;
endif;
?>