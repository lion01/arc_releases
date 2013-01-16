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

JHTML::_('behavior.mootools');
$doc = &JFactory::getDocument();
$doc->addScript( 'components'.DS.'com_arc_planner'.DS.'views'.DS.'task'.DS.'tmpl'.DS.'evidence_slider.js');
$doc->addScript( 'components'.DS.'com_arc_planner'.DS.'views'.DS.'task'.DS.'tmpl'.DS.'task_slider.js');

?>
<style type="text/css">

#planner_inter_div {
	padding: 10px;
}

.edit_update_centre {
	text-align: center;
}

.edit_update_sub {
	text-align: left;
	font-weight: bold;
	margin-bottom: 0px;
}

.existing_task_title {
	font-weight: bold;
}

form div {
	clear: left;
	display: block;
	margin: 10px 0px;
}

form div label {
	float: left;
	display: block;
	width: 115px;
	text-align: right;
	margin-right: 5px;
}

form div.nolabel {
	display: block;
	margin-left: 120px;
	margin-right: 5px;
}

form div.evidence_clicker_div {
	display: none;
	margin-left: 120px;
	margin-right: 5px;
	margin-bottom: -10px;
}

form input {
	vertical-align: middle;
	margin: 0px;
}

input.text {
	width: 400px;
}

textarea.comment {
	width: 400px;
	height: 100px;
}

input.progress {
	width: 30px;
}

input.evidence {
	width: 400px;
}

.disabled {
	font-style:italic;
	color:grey;
}

</style>
<div id="planner_inter_div">
	<?php $this->inputNum = 1; ?>
	<form enctype="multipart/form-data" method="post" action="<?php echo ApotheosisLib::getActionLinkByName( 'apoth_plan_update_edit_inter2_save', $this->dependancies ) ?>">
		<?php
			if( $this->formParentTitle ) {
				$title = $this->pTask->getTitle();
			}
			else {
				$title = $this->firstTaskObj->getTitle();
			}
			$pTaskId = $this->pTask->getId();
			echo '<input type="hidden" name="parent_task_id" value="'.$pTaskId.'" />';
		?>
		<h3 class="edit_update_centre"><?php echo $title; ?></h3>

		<?php if( $this->formPartTask ) : ?>
			<!-- Task Setting Part -->
			<?php
				if( $this->formParentTitle ) {
					$childTasks = $this->pTask->getLinkedTasks( array( 'type'=>'children' ) );
					$this->labels = $this->pTask->getLabels();
				}
				else {
					$childTasks = $this->firstTaskObj->getLinkedTasks( array( 'type'=>'children' ) );
					$this->labels = $this->firstTaskObj->getLabels();
				}
			?>
			<div class="edit_update_sub"><?php echo $this->labels['task_new']; ?></div>
				<div>
					<label for="<?php echo $this->inputNum; ?>"><?php echo $this->labels['task_title']; ?></label>
					<input id="<?php echo $this->inputNum++; ?>" class="text" type="text" name="tasks[title]" />
				</div>
				
				<div>
					<label for="<?php echo $this->inputNum; ?>"><?php echo $this->labels['task_text_1']; ?></label>
					<textarea id="<?php echo $this->inputNum++; ?>" class="comment" name="tasks[text_1]"></textarea>
				</div>
				<div>
					<label for="<?php echo $this->inputNum; ?>"><?php echo $this->labels['task_text_2']; ?></label>
					<textarea id="<?php echo $this->inputNum++; ?>" class="comment" name="tasks[text_2]"></textarea>
				</div>
				<div>
					<label for="<?php echo $this->inputNum; ?>">&nbsp;</label>
					<input id="<?php echo $this->inputNum++; ?>" class="submit" type="submit" name="submit" value="<?php echo $this->labels['task_add']; ?>">
				</div>
				<div>
					<label for="<?php echo $this->inputNum; ?>">&nbsp;</label>
					<input id="<?php echo $this->inputNum++; ?>" class="submit" type="submit" name="submit" value="<?php echo $this->labels['task_demote']; ?>">
				</div>
			<div class="edit_update_sub"><?php echo $this->labels['task_existing']; ?></div>
			<?php
			$assignee = JRequest::getVar( 'assignee' );
			if( !empty($assignee) ) { $groupReq['members'] = $assignee; }
			foreach( $childTasks as $id ) :
					$childTask = $this->model->getTask( $id );
					$groupReq['taskId'] = $id;
					$childTask->setGroupRequirements( $groupReq );
					
					$g = $childTask->getGroups();
					if( empty($g) ) {
						continue;
					}
					?>
					<div class="nolabel">
						<span class="existing_task_title"><?php echo $childTask->getTitle(); ?></span><br />
						<?php
							echo $childTask->getText1().'<br /><br />';
							echo $childTask->getText2().'<br />';
						?>
					</div>
			<?php endforeach;?>
		<?php endif; ?>

		<?php if( $this->formPartUpd ) : ?>
			<!-- Updating Part -->
			<input type="hidden" name="MAX_FILE_SIZE" value="64000" />
			<?php foreach( $this->tasks as $this->taskId=>$groupIds ) : ?>
				<?php
					$this->task = &$this->model->getTask( $this->taskId );
					$this->labels = $this->task->getLabels();
					$childTasks = $this->task->getLinkedTasks( array( 'type'=>'children' ) );
					$this->taskIsLeaf = ( count($childTasks) == 0 );
					$this->categories = $this->task->getCategories();
					$this->formCount = 1;
				?>
				<div class="edit_update_sub"><?php echo $this->task->getTitle(); ?></div>
				<?php echo $this->task->getText1(); ?>
				<?php foreach( $groupIds as $this->groupId=>$categoryIds ) : ?>
					<?php
						$this->group = &$this->task->getGroup( $this->groupId );
						$this->formCount = 1;
					?>
					<?php if( count($groupIds) > 1 ) : ?>
						<div class="nolabel"><?php echo JHTML::_( 'arc.shortlist', $this->group->getPeopleInRole( 'assignee' ), 'displayname', 'Group Members', 5, 50 ); ?></div>
					<?php endif; ?>
					<?php foreach( $this->categories as $this->categoryId=>$this->category ) :
							$this->categoryText = $this->category['label'];
							$this->curUpdateCat = null;
							$updateIds = $categoryIds[$this->categoryId];
							if( !empty($updateIds) ) {
								if( $this->formUpdateNumber == 'm' ) {
									$updateIds = $updateIds;
								}
								elseif( $this->formUpdateNumber == 's' ) {
									end( $updateIds );
									$updateIds = array( key($updateIds) );
								}
								elseif( $this->formUpdateNumber == '-' ) {
									$updateIds = array();
								}
							}
							else {
								$updateIds = array();
							}
						?>
						
						<!-- display existing updates -->
						<?php if( $this->formUpdateExisting ) : ?>
							<?php foreach( $updateIds as $this->updateId ) : ?>
								<?php echo $this->loadTemplate( 'update_update' ); ?>
							<?php endforeach; ?>
						<?php endif; ?>
						
						<!-- display fields for new updates -->
						<?php if( $this->formUpdateNew && (!$this->formUpdateExisting || empty($updateIds) || $this->formUpdateNumber == 'm')) : ?>
							<?php
								$this->updateId = 'new_'.$this->categoryId;
								echo $this->loadTemplate( 'update_update' );
							?>
						<?php endif; ?>
					<?php endforeach; /* end of categories */ ?>
					<?php echo $this->loadTemplate( 'update_groupfoot' ); ?>
					
				<?php endforeach; /* end of groups */ ?>
			<?php endforeach; /* end of tasks */ ?>
		<?php endif; ?>
	</form>
</div>
