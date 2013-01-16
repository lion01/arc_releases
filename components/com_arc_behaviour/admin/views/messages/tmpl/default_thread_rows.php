<?php
/**
 * @package     Arc
 * @subpackage  Behaviour
 * @copyright   Copyright (C) 2005 Punnet. All rights reserved. See COPYRIGHT_ARC.txt
 * @license     http://www.gnu.org/licenses/gpl.html GNU/GPL. See LICENSE_ARC.txt
 * Arc is free software: you can redistribute it and/or modify
 * it under the terms of version 3 of the GNU General Public License
 * as published by the Free Software Foundation.
 * The disclaimer of warranty as stated in the GPL applies to this program
 */

defined( '_JEXEC' ) or die( 'Restricted access' );

$msgObjects = $this->model->getThreadMessages( $this->thread->getMessageIds() );
foreach( $msgObjects as $msgObj ):
	$incObj = &$this->fInc->getInstance( $msgObj->getDatum('incident') );
	$tagObj = &$this->fTag->getInstance( $incObj->getTag() );
	$actObj = &$this->fAct->getInstance( $msgObj->getDatum('action') );
	$msgId = $msgObj->getId();
	$arcDotLink = ( ($msgId == $this->thread->getFirstMessageId() && is_object($incObj)) ) ? JHTML::_('arc.dot', strtolower($tagObj->getLabel())) : '';
	$actNum = $msgObj->getDatum( 'action_number' );
	$actNumText = ( ($actNum == '') || ($actNum == 0) ) ? '' : ' ('.$actNum.')';
	?>
	<tr class="<?php echo 'row'.$this->threadIndex % 2; ?>">
		<td align="center">
			<input type="checkbox" id="cb<?php echo $this->curIndex; ?>" name="eid[<?php echo $this->curIndex; ?>]" onclick="isChecked(this.checked);" />
			<input type="hidden" name="thrId[<?php echo $this->curIndex; ?>]" value="<?php echo $this->thread->getId(); ?>" />
			<input type="hidden" name="msgId[<?php echo $this->curIndex; ?>]" value="<?php echo $msgId; ?>" />
		</td>
		<td align="center"><?php echo $arcDotLink; ?></td>
		<td align="center"><?php echo date( 'd/m/Y H:i', strtotime($msgObj->getCreated()) ); ?></td>
		<td align="center"><?php echo date( 'd/m/Y H:i', strtotime($msgObj->getDate()) ); ?></td>
		<td align="center"><?php echo ApotheosisData::_( 'people.displayName', $msgObj->getAuthor(), 'teacher' ); ?></td>
		<td align="center"><?php echo ApotheosisData::_( 'people.displayName', $msgObj->getDatum('student_id') ); ?></td>
		<td align="center"><?php echo ApotheosisData::_( 'course.name', $msgObj->getDatum('group_id') ); ?></td>
		<td align="center"><?php echo $msgObj->getDatum( 'room' ); ?></td>
		<td align="center"><?php echo (is_object($incObj) ? $incObj->getLabel() : ''); ?></td>
		<td align="center"><?php echo $msgObj->getDatum( 'incident_text' ); ?></td>
		<td align="center"><?php echo $msgObj->getDatum( 'outline' ).$msgObj->getDatum( 'comment' ); ?></td>
		<td align="center"><?php echo (is_object($actObj) ? $actObj->getLabel().$actNumText : '' ); ?></td>
		<td align="center"><?php echo $msgObj->getDatum( 'action_text' ); ?></td>
	</tr>
	<?php $this->curIndex++;
endforeach; ?>