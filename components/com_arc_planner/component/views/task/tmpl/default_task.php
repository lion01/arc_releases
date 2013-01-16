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

$this->task = &$this->model->getTask( $this->_curTaskId );
$taskColor = $this->task->getColor();
$progressStyle = 'style="background-color: '.$taskColor.'; width: '.$this->task->getProgress().'%;"';
$matched = $this->model->getTaskMatched( $this->_curTaskId );
$complete = $this->task->getComplete();
$micro = $this->task->getMicro();
$microDuration = ( $micro ? $this->task->getDuration() : null );
$detailsShown = $this->task->getDetailsShown();
$showDetailsButton = '(<a href="'.$this->link.'&task=toggleDetails&taskId='.$this->_curTaskId.'">Details</a>)';
$subtasksShown = $this->task->getSubtasksShown() ? true : false;
$showSubtasksButton = '(<a href="'.$this->link.'&task=toggleSubtasks&taskId='.$this->_curTaskId.'">Subtasks</a>)';
$shownChildren = $this->model->getTaskShownChildren( $this->task->getId() );
$subtasksCount = $this->task->getSubtasksCount();
?>
<div class="task_div_wrapper">
	<b class="b1f" style="background: <?php echo $taskColor; ?>;"></b>
	<b class="b2f" style="background: <?php echo $taskColor; ?>;"></b>
	<b class="b3f" style="background: <?php echo $taskColor; ?>;"></b>
	<b class="b4f" style="background: <?php echo $taskColor; ?>;"></b>
	<div class="task_edge_div" style="border-color: <?php echo $taskColor; ?>;">
		<div class="task_color_div" <?php echo $progressStyle; ?>></div>
		<div class="task_inner_div">
			<div class="task_inner_left_div">
				<span class="task_matched"><?php echo $matched ? '(M)' : ''; ?></span>
				<span class="task_micro"><?php echo $micro ? '(u)' : ''; ?></span>
				<span class="task_complete"><?php echo $complete ? '(C)' : ''; ?></span>
				<span class="task_title"><?php echo $this->task->getTitle(); ?> (<?php echo $this->task->getId(); ?>)</span>
			</div>
			<div class="task_inner_right_div">
				<span class="task_buttons"><?php if( $subtasksCount > 0 ) {echo $showSubtasksButton;} ?></span>
				<span class="task_buttons"><?php echo $showDetailsButton; ?></span>
			</div>
			<div class="task_inner_centre_div">
				<span class="task_micro_duration"><?php echo !is_null($microDuration) ? $microDuration.' hours' : '&nbsp;'; ?></span>
			</div>
		</div>
		<?php
		if( $detailsShown ) {
			echo '<div class="task_top_edge_div" style="border-color: '.$taskColor.';"></div>';
			if( !$micro ) {
				$this->labels = $this->task->getLabels();
				echo $this->loadTemplate( 'task_details' );
			}
			else {
				echo $this->loadTemplate( 'micro_details' );
			}
		}
		if( $subtasksShown && (!empty($shownChildren)) ) {
			echo '<div class="task_top_edge_div" style="border-color: '.$taskColor.';"></div>';
			foreach( $shownChildren as $k=>$taskId ) {
				$this->_curTaskId = $taskId;
				echo $this->loadTemplate( 'task' );
			}
		}
		?>
	</div>
	<b class="b4f" style="background: <?php echo $taskColor; ?>;"></b>
	<b class="b3f" style="background: <?php echo $taskColor; ?>;"></b>
	<b class="b2f" style="background: <?php echo $taskColor; ?>;"></b>
	<b class="b1f" style="background: <?php echo $taskColor; ?>;"></b>
</div>