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
<h3>Planner Lists</h3>
<br />
<form method="post" action="<?php echo $this->link; ?>&task=search&passthrough=general">
<!-- 	Task: <input type="text" size="5" name="taskId"> -->
	<input type="submit" value="Show lists">
</form>
<hr />
<?php
$categories = $this->model->getCategories();

foreach( $categories as $catId=>$cat ) {
	$this->_curCatId = $catId;
	$this->_curCatTitle = $cat['label'];
	$this->_curColDefs = $this->model->getTableInfo( $this->_curCatId );
	$this->_maxDepth = $this->model->getMaxDepth( $this->_curCatId );
	echo $this->loadTemplate( 'categories' );
}

//ini_set('xdebug.var_display_max_depth', 10 );var_dump_pre( $this->model, '<br /><br />$this->model in default template:' ); // *** remove
?>