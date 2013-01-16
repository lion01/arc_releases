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

$editUpdatesButton   = '(<a class="modal" rel="{handler: \'iframe\', size: {x: 640, y: 480}}" href="'.$this->link.'&scope=interUpdate&taskId='.$this->_curTaskId.'&groupId='.$this->_curGroupId.'&updateId='.$this->_curUpdateId.'&tmpl=component">Edit</a>)';
$removeUpdatesButton = '(<a href="'.$this->link.'&task=removeUpdate&taskId='.$this->_curTaskId.'&groupId='.$this->_curGroupId.'&updateId='.$this->_curUpdateId.'&tmpl=component">-</a>)';

// build evidence as an html <ul> of hyperlinks
$evidence = '<ul>';
foreach( $this->update->getEvidence( 'url' ) as $eId=>$evidenceItem ) {
	$evidence .= '<li>(<a href="'.$this->link.'&task=removeEvidence&taskId='.$this->_curTaskId.'&groupId='.$this->_curGroupId.'&updateId='.$this->_curUpdateId.'&evidenceId='.$eId.'">-</a>) <a href="'.$evidenceItem.'">'.$evidenceItem.'</a></li>';
}
foreach( $this->update->getEvidence( 'file' ) as $eId=>$evidenceItem ) {
	$evidence .= '<li>(<a href="'.$this->link.'&task=removeEvidence&taskId='.$this->_curTaskId.'&groupId='.$this->_curGroupId.'&updateId='.$this->_curUpdateId.'&evidenceId='.$eId.'">-</a>) <a href="'.ApotheosisPeopleData::getFileLink( $this->userId, $evidenceItem).'">'.$evidenceItem.'</a></li>';
}
$evidence .= '</ul>'
	.'(<a class="modal" rel="{handler: \'iframe\', size: {x: 640, y: 480}}" href="'.$this->link.'&scope=addEvidence&taskId='.$this->_curTaskId.'&groupId='.$this->_curGroupId.'&updateId='.$this->_curUpdateId.'&tmpl=component">+</a>)';

// build list of microtasks completed as a static list of checkboxed microtask titles
$micros = '';
foreach( $this->update->getMicros() as $microId ) {
	$microTask = &$this->model->getTask( $microId );
	$micros .= '<input type="checkbox" checked="checked" disabled="disabled">'.$microTask->getTitle().'<br />';
}
?>
<tr>
	<td>
		<?php echo 'Update: '.$this->_curUpdateId; /**** dev code */ ?><br />
		<?php echo $editUpdatesButton; ?>
		<?php echo $removeUpdatesButton; ?>
	</td>
	<td>
		&nbsp;
	</td>
	<td colspan="3">
		<b><?php echo $this->categories[$this->update->getCategory()]['label']; ?></b><br />
		<?php echo $this->update->getText(); ?><br />
		<?php echo $micros; ?>
	</td>
	<td colspan="3">
		<?php echo $this->update->getAuthor(); ?><br />
		<?php echo ApotheosisLibParent::arcDateTime( $this->update->getDateAdded() ); ?><br />
		<?php echo $this->update->getProgress(); ?>%<br />
		<?php echo $evidence; ?>
	</td>
</tr>