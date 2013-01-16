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

$this->nav->displayNav();
?>
<style>
div.cycle {
	border: 1px solid darkgrey;
	border-radius: 5px;
	margin-bottom: 10px;
	overflow: auto;
}

div.cycle h2 {
	background:-webkit-gradient( linear, left top, left bottom, color-stop(0.05, #ededed), color-stop(1, #dfdfdf) );
	background:-moz-linear-gradient( center top, #ededed 5%, #dfdfdf 100% );
	filter:progid:DXImageTransform.Microsoft.gradient(startColorstr='#ededed', endColorstr='#dfdfdf');
	background-color:#ededed;
	border-bottom: 1px darkgrey;
}

div.written,
div.checked {
	float: left;
	height: 200px;
	width: 47%;
	margin: 1%;
	border: 1px solid darkgrey;
	border-radius: 5px;
}

div.cycle table td {
	padding: 1px 2px;
	text-align: right;
}

</style>

<div id="arc_main_narrow">

<?php
$this->nav->displayBreadcrumbs();

while( $this->cycle = $this->get( 'NextCycle' ) ) {
	$this->written = &$this->allWritten[$this->cycle->getId()];
	$this->checked = &$this->allChecked[$this->cycle->getId()];
	echo $this->loadTemplate( 'cycle_progress' );
}
?>

</div>