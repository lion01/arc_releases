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

JHTML::script( 'statements.js', $this->scriptPath, true );

?>
<style>
body {
	padding: 10px 5% 10px 5%;
}
h1 {
	font-size: 1.5em;
}
textarea {
	width: 100%;
	height: 100px;
	margin-bottom: 10px;
}

.activity_overlay {
	background: darkgrey;
}
.activity_overlay div {
	background: lightgrey;
	width: 100px;
	margin: 50px auto;
	padding: 1em;
	text-align: center;
}

#statement_list {
	max-height: 300px;
	overflow-y: auto;
}
.stmt_row {
	overflow: hidden;
}
.stmt_row div {
	float: left;
}
.stmt_check {
	width: 20px;
	margin-right: -20px;
}
.stmt_text {
	margin-left: 5%;
	width: 95%;
}
.stmt_text div {
	padding: 1px 5px;
}

</style>

<h1>Select Statement(s)</h1>
<p>Select one or more statements from the list below to insert into the report.</p>

<div id="statement_list">

<?php foreach( $this->field->getStatements( $this->subreport, true ) as $s ) : ?>
<div class="stmt_row">
	<div class="stmt_check"><input type="checkbox"/></div>
	<div class="stmt_text"<?php echo ( is_null( $s['color'] ) ? '' : ' style="background: '.$s['color'].'"' ); ?>><div><?php echo $s['text']; ?></div></div>
</div>
<?php endforeach; ?>

</div>

<button id="doInsert">Insert statements</button>