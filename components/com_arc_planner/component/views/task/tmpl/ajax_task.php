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

$n = "\n".str_pad( '', $this->_indent, '	', STR_PAD_LEFT );
$t = $this->task->getTemplate();
$templateTag = ( empty($t) ? '' : $n.' <template>'.htmlspecialchars($t).'</template>' );

$shownChildren = $this->model->getTaskShownChildren( $this->_curTaskId );

echo
	 $n.'<task id="'.$this->task->getId().'">'
	.$n.'	<title>'.$this->task->getTitle().'</title>'
	.$n.'	<progress>'.$this->task->getProgress().'</progress>'
	.$n.'	<evidence_num>'.$this->task->getEvidenceNum().'</evidence_num>'
	.$templateTag
	.$n.'	<groups>';
	foreach( $this->task->getGroups() as $gId=>$group ) {
		echo
			 $n.'		<group id="'.$group->getId().'">'
			.$n.'		<progress>'.$group->getProgress().'</progress>'
			.$n.'		<assignees>';
		foreach( $group->getPeopleInRole('assignee') as $pId=>$person ) {
			echo
				 $n.'			<assignee id="'.$person->id.'">'.$person->displayname.'</assignee>';
		}
		echo
			 $n.'		</assignees>'
			.$n.'		<updates>';
		foreach( $group->getUpdates() as $uId=>$update ) {
			echo
				 $n.'			<update id="'.$update->getId().'">'
				.$n.'			<progress>'.$update->getProgress().'</progress>';
			foreach( $update->getEvidence( 'url' ) as $eId=>$evidenceItem ) {
				$evidenceItem = htmlspecialchars($evidenceItem);
				echo
					 $n.'			<evidence id="'.$eId.'" href="'.$evidenceItem.'">'.$evidenceItem.'</evidence>';
			}
			foreach( $update->getEvidence( 'file' ) as $eId=>$evidenceItem ) {
				$uId = $update->getFileOwner( $eId );
				$evidenceUrl = htmlspecialchars( ApotheosisPeopleData::getFileLink( $uId, $evidenceItem) );
				$evidenceItem = htmlspecialchars($evidenceItem);
				echo
					 $n.'			<evidence id="'.$eId.'" href="'.$evidenceUrl.'">'.$evidenceItem.'</evidence>';
			}
			echo
				 $n.'			</update>';
		}
		echo
			 $n.'		</updates>'
			.$n.'		</group>';
	}
echo
	 $n.'	</groups>'
	.$n.'	<subtasks>';
$this->_indent += 2;
foreach( $shownChildren as $k=>$taskId ) {
	$this->_curTaskId = $taskId;
	echo $this->loadTemplate( 'task' );
}
$this->_indent -= 2;
echo
	 $n.'	</subtasks>'
	.$n.'</task>';
