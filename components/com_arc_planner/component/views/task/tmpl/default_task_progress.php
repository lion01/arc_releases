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

JHTML::_('behavior.modal');
$complete = $this->group->getComplete() == 1 ? true : false;
$progress = $this->group->getProgress();
$due = ApotheosisLibParent::arcDateTime( $this->group->getDue() );
$assignees = $this->group->getPeopleInRole( 'assignee' );
$assistants = $this->group->getPeopleInRole( 'assistant' );
$admins = $this->group->getPeopleInRole( 'admin' );
$numUpdates = $this->group->getUpdatesCount();
$updatesShown = $this->group->getUpdatesShown();
$showUpdatesButton = '(<a href="'.$this->link.'&task=toggleUpdates&taskId='.$this->_curTaskId.'&groupId='.$this->_curGroupId.'">Updates</a>)';
$addUpdatesButton = '(<a class="modal" rel="{handler: \'iframe\', size: {x: 640, y: 480}}" href="'.$this->link.'&scope=interUpdate&taskId='.$this->_curTaskId.'&groupId='.$this->_curGroupId.'&tmpl=component">+</a>)';
$completeButton = '(<a href="'.$this->link.'&task=completeUpdate&taskId='.$this->_curTaskId.'&groupId='.$this->_curGroupId.'">complete</a>)';

?>
<tr>
	<td>
		<?php echo 'Group: '.$this->_curGroupId; /**** dev code */ ?>
	</td>
	<td>
		<input type="checkbox">
	</td>
	<td>
		<?php if( $this->task->getMicro() ) {
			echo ( ($numUpdates > 0) ? '(Yes)' : '(No)' );
		}
		else {
			echo $progress.'%';
		} ?>
		<span class="task_complete"><?php echo $complete ? '(C)' : ''; ?></span>
	</td>
	<td>
		<?php echo $due ?>
	</td>
	<td>
		<?php
			if( !empty($assignees) ) {
				echo JHTML::_( 'arc.shortlist', $assignees, 'displayname', 'Assignees', 10, 35 );
			}
			else {
				echo '--';
			}
		?>
	</td>
	<td>
		<?php
			if( !empty($assistants) ) {
				echo JHTML::_( 'arc.shortlist', $assistants, 'displayname', 'Assistants', 10, 35 );
			}
			else {
				echo '--';
			}
		?>
	</td>
	<td>
		<?php
			if( !empty($admins) ) {
				echo JHTML::_( 'arc.shortlist', $admins, 'displayname', 'Admins', 10, 35 );
			}
			else {
				echo '--';
			}
		?>
	</td>
	<td>
		<span class="task_buttons">
			<?php echo $addUpdatesButton; ?>
			<?php if($numUpdates > 0) {echo $showUpdatesButton;} ?>
			<?php echo $completeButton; ?>
		</span>
	</td>
</tr>
<?php
if( $updatesShown ) {
	$this->categories = $this->task->getCategories();
	$this->updates = &$this->group->getUpdates();
	foreach( $this->updates as $updateId=>$notByRef ) {
		$this->update = &$this->updates[$updateId];
		$this->_curUpdateId = $this->update->getId();
		echo $this->loadTemplate( 'task_update' );
	}
}
?>