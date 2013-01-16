<?php
/**
 * @package     Arc
 * @subpackage  Assessment
 * @copyright   Copyright (C) 2005 Punnet. All rights reserved. See COPYRIGHT_ARC.txt
 * @license     http://www.gnu.org/licenses/gpl.html GNU/GPL. See LICENSE_ARC.txt
 * Arc is free software: you can redistribute it and/or modify
 * it under the terms of version 3 of the GNU General Public License
 * as published by the Free Software Foundation.
 * The disclaimer of warranty as stated in the GPL applies to this program
 */

defined( '_JEXEC' ) or die( 'Restricted access' );
?>
<table>
<?php
echo $this->loadTemplate( 'headings' );

foreach($this->rows as $row) {
	$this->person = $row['person'];
	$this->group = $row['group'];
	
	$show = false;
	while( !$show && !is_null($cur = array_shift($row['historicalGroups'])) ) {
		$show = isset($this->groups[$cur]);
	}
	
	if( $show ) {
		echo $this->loadTemplate('row')."\n";
	}
}
?>
</table>