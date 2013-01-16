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

$doc = &JFactory::getDocument();
$doc->addStyleSheet( JURI::Base().'components'.DS.'com_arc_planner'.DS.'views'.DS.'task'.DS.'tmpl'.DS.'task.css' ); ?>
<?php if( JRequest::getVar('modal') != 'true' ) : ?>
<h3>Planner Tasks</h3>
<br />
<form method="post" action="<?php echo $this->link; ?>">
<!-- 	Task: <input type="text" size="5" name="taskId"> -->
	<input type="hidden" name="task" value="search" /t>
	<input type="hidden" name="passthrough" value="general" />
	<input type="submit" value="Show tasks">
</form>
<hr />
<?php endif; ?>
<div style="float: right">
	<span class="task_buttons">(<a href="<?php echo $this->link; ?>&task=refreshList" >Refresh List</a>)</span>
	<span class="task_buttons">(<a href="<?php echo $this->link; ?>&task=toggleManyDetails" >All Details</a>)</span>
	<span class="task_buttons">(<a href="<?php echo $this->link; ?>&task=toggleManySubtasks">All Subtasks</a>)</span>
</div>
<br />
<?php
$categories = $this->model->getCategories();

foreach( $categories as $catId=>$cat ) {
	$this->_curCatId = $catId;
	$this->_curCatTitle = $cat['label'];
	echo $this->loadTemplate( 'categories' );
}
//ini_set('xdebug.var_display_max_depth', 10 );var_dump_pre( $this->model, '<br /><br />$this->model in default template:' ); // *** remove
//ini_set('xdebug.var_display_max_depth', 10 );var_dump_pre( $this, '<br /><br />$this in default template:' ); // *** remove
?>