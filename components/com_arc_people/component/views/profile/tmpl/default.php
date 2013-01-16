<?php
/**
 * @package     Arc
 * @subpackage  People
 * @copyright   Copyright (C) 2005 Punnet. All rights reserved. See COPYRIGHT_ARC.txt
 * @license     http://www.gnu.org/licenses/gpl.html GNU/GPL. See LICENSE_ARC.txt
 * Arc is free software: you can redistribute it and/or modify
 * it under the terms of version 3 of the GNU General Public License
 * as published by the Free Software Foundation.
 * The disclaimer of warranty as stated in the GPL applies to this program
 */

defined( '_JEXEC' ) or die( 'Restricted access' );
?>
<style>
.row {
	overflow: auto;
}

.label {
	float: left;
	display: block;
	width: 15em;
	text-align: right;
	margin-right: 5px;
}
.value {
	float: left;
}
</style>

<h3>Profile for <?php echo $this->profile->getDisplayName(); ?></h3>
<h4>Ids</h4>
<?php
foreach($this->profile->getIds() as $k=>$v) :
$v = nl2br(htmlspecialchars($v));
?>
<div class="row">
	<div class="label"><?php echo $k; ?></div>
	<div class="value"><?php echo $v; ?></div>
</div>
<?
endforeach;

?>
<h4>person data</h4>
<?php
foreach($this->profile->getPersonData() as $k=>$v) :
$v = nl2br(htmlspecialchars($v));
?>
<div class="row">
	<div class="label"><?php echo $k; ?></div>
	<div class="value"><?php echo $v; ?></div>
</div>
<?
endforeach;
?>
