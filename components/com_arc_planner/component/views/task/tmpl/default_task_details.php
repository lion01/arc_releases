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
<div class="task_details_wrapper">
	<div class="task_details_block">
		<div class="task_details_block_inner">
			<div class="task_details_block_title">
				<?php echo $this->labels['task_text_1']; ?>
			</div>
			<div class="task_details_block_text">
				<?php echo $this->task->getText1(); ?>
			</div>
		</div>
		<div class="task_details_block_inner">
			<div class="task_details_block_title">
				<?php echo $this->labels['task_text_2']; ?>
			</div>
			<div class="task_details_block_text">
				<?php echo $this->task->getText2(); ?>
			</div>
		</div>
	</div>
	<div class="task_details_line">
		<div class="task_details_line_inner">
			<div class="task_details_line_title">
				Duration
			</div>
			<div class="task_details_line_text">
				<?php echo $this->task->getDuration() ?> hours
			</div>
		</div>
		<div class="task_details_line_inner">
			<div class="task_details_line_title">
				Depends on
			</div>
			<div class="task_details_line_text">
				<?php
					$tmpTaskList = $this->model->getTaskObjList( $this->task->getLinkedTasks( array( 'type'=>'requires' ) ) );
					if( !empty($tmpTaskList) ) {
						echo JHTML::_( 'arc.shortlist', $tmpTaskList, array('_data', 'title'), 'This task depends on', 10, 35 );
					}
					else {
						echo '--';
					}
				?>
			</div>
		</div>
		<div class="task_details_line_inner">
			<div class="task_details_line_title">
				Required by
			</div>
			<div class="task_details_line_text">
				<?php
					$tmpTaskList = $this->model->getTaskObjList( $this->task->getLinkedTasks( array( 'type'=>'requiredBy' ) ) );
					if( !empty($tmpTaskList) ) {
						echo JHTML::_( 'arc.shortlist', $tmpTaskList, array('_data', 'title'), 'This task is required by', 10, 35 );
					}
					else {
						echo '--';
					}
				?>
			</div>
		</div>
		<div class="task_details_line_inner">
			<div class="task_details_line_title">
				Related to
			</div>
			<div class="task_details_line_text">
				<?php
					$tmpTaskList = $this->model->getTaskObjList( $this->task->getLinkedTasks( array( 'type'=>'related' ) ) );
					if( !empty($tmpTaskList) ) {
						echo JHTML::_( 'arc.shortlist', $tmpTaskList, array('_data', 'title'), 'This task is related to', 10, 35 );
					}
					else {
						echo '--';
					}
				?>
			</div>
		</div>
	</div>
	<div class="task_details_progress">
		<div class="task_details_progress_inner">
			<div class="task_details_progress_title">
				Assignments
			</div>
			<div class="task_details_progress_table_outer">
				<div class="task_details_progress_table">
					<table>
						<tr>
							<th>Add / Del</th>
							<th>All / None</th>
							<th>Progress</th>
							<th>Due</th>
							<th>Assignees</th>
							<th>Assistants</th>
							<th>Admins</th>
							<th>Updates</th>
						</tr>
						<?php
							$this->groups = &$this->task->getGroups();
							foreach( $this->groups as $groupId=>$notByRef ) {
								$this->group = &$this->groups[$groupId];
								$this->_curGroupId = $this->group->getId();
								echo $this->loadTemplate( 'task_progress' );
							}
						?>
					</table>
				</div>
			</div>
		</div>
	</div>
</div>