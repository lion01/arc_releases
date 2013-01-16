<?php
/**
 * @package     Arc
 * @subpackage  Planner
 * @copyright   Copyright (C) 2005 Punnet. All rights reserved. See COPYRIGHT_ARC.txt
 * @license     http://www.gnu.org/licenses/gpl.html GNU/GPL. See LICENSE_ARC.txt
 * Arc is free software: you can redistribute it and/or modify
 * it under the terms of version 3 of the GNU General Public License
 * as published by the Free Software Foundation.
 * The disclaimer of warranty as stated in the GPL applies to this program
 */

defined( '_JEXEC' ) or die( 'Restricted access' );
?>
<style type="text/css">

.task_category_title {
	font-weight: bold;
	padding: 0px 5px;
}

.task_category_wrapper {
	margin: 0px -3px;
}

</style>
<div class="task_category_title"><?php if( JRequest::getVar('modal') != 'true' ) {echo $this->_curCatTitle.' ('.$this->_curCatId.')';}  ?></div>
<div class="task_category_wrapper">
<?php
$topTasks = $this->model->getTopTasks( $this->_curCatId );
foreach( $topTasks as $k=>$topTaskId ) {
	$this->_curTaskId = $topTaskId;
	echo $this->loadTemplate( 'task' );
}
?>
</div>