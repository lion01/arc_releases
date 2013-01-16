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
foreach( $msgObjects as $msgObj ) :
	$incObj = $this->model->getIncObject( $msgObj->getDatum('incident') );
	$actObj = $this->model->getActObject( $msgObj->getDatum('action') );
	$msgId = $msgObj->getId();
	$arcDotLink = ( ($msgId == $this->thread->getFirstMessageId() && is_object($incObj)) ) ? JHTML::_('arc.dot', strtolower($incObj->colour)) : '';
	$actNum = $msgObj->getDatum( 'action_number' );
	$actNumText = ( ($actNum == '') || ($actNum == 0) ) ? '' : ' ('.$actNum.')';
	$class = in_array( $msgId, $this->rescindMsgIds ) ? 'rescind' : 'row0';
	?>
	<tr class="<?php echo $class; ?>">
		<td align="center"><?php echo $arcDotLink; ?></td>
		<td align="center"><?php echo date( 'd/m/Y H:i', strtotime($msgObj->getCreated()) ); ?></td>
		<td align="center"><?php echo date( 'd/m/Y H:i', strtotime($msgObj->getDate()) ); ?></td>
		<td align="center"><?php echo ApotheosisData::_( 'people.displayName', $msgObj->getAuthor(), 'teacher' ); ?></td>
		<td align="center"><?php echo ApotheosisData::_( 'people.displayName', $msgObj->getDatum('student_id') ); ?></td>
		<td align="center"><?php echo ApotheosisData::_( 'course.name', $msgObj->getDatum('group_id') ); ?></td>
		<td align="center"><?php echo $msgObj->getDatum( 'room' ); ?></td>
		<td align="center"><?php echo (is_object($incObj) ? $incObj->label : ''); ?></td>
		<td align="center"><?php echo $msgObj->getDatum( 'incident_text' ); ?></td>
		<td align="center"><?php echo $msgObj->getDatum( 'outline' ).$msgObj->getDatum( 'comment' ); ?></td>
		<td align="center"><?php echo (is_object($actObj) ? $actObj->label.$actNumText: '' ); ?></td>
		<td align="center"><?php echo $msgObj->getDatum( 'action_text' ); ?></td>
	</tr>
<?php endforeach; ?>