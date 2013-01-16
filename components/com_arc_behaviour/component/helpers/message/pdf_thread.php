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

// Prepare the incident section
$incidents = array();
$incidents[] = '<strong>Incident:</strong>';
$incidents[] = $this->incident.( $this->inc->getHasText() ? ': '.$this->message->getDatum('incident_text') : '' );
$incidents[] = $this->message->getDatum( 'outline' );
foreach( $incidents as $k=>$incident ) {
	if( $incident == '' ) {
		unset( $incidents[$k] );
	} 
}
$incText = implode( '<br />', $incidents );

// Prepare action section
$actions = array();
if( $this->first ) {
	$actions[] = '<strong>Outline of action taken:</strong>';
}
else {
	$actions[] = '<strong>Action:</strong>';
}
// Do we actually have an action?
if( $this->message->getDatum('action') != '0' ) {
	$fAct = ApothFactory::_( 'behaviour.Action' );
	$a = $fAct->getInstance( $this->message->getDatum('action') );
	$actions[] = $a->getLabel();
	$actions[] = $this->message->getDatum( 'action_text' );
}
// Initial incident action text only
if( $this->first ) {
	// Was there a callout?
	if( $this->message->getDatum('callout') ) {
		$actions[] = 'A call-out was requested';
	}
	// If not was action a purple which needs some affirmation either way 
	elseif( $this->inc->getParentId() == 5 ) {
		$actions[] = 'No call-out';
	}
	// Was there more action required?
	if( $this->message->getDatum('more_action') ) {
		$actions[] = 'Further action is required';
	}
}
foreach( $actions as $k=>$action ) {
	if( $action == '' ) {
		unset( $actions[$k] );
	} 
}
if( count($actions) == 1 ) {
	$actions[] = 'None';
}
$actionText = implode( '<br />', $actions );
?>
<?php if( $this->first ) : ?>
<?php 
$course = ApotheosisData::_('course.name', $this->message->getDatum('group_id') );
$course = ( ($course != '') ? $course : '--' ); 
?>
<table width="100%" cellpadding="0" cellspacing="0" border="1"><tr><td>
<table width="100%" cellpadding="2"cellspacing="0">
	<tr>
		<td width="24" rowspan="4">
			<?php echo JHTML::_( 'arc.dot', $this->color, $this->color.' incident', true ); ?>
		</td>
		<td width="152">
			<strong>Student(s):</strong><br />
			<?php echo implode( ', ', $this->studentNames ); ?>
		</td>
		<td width="100">
			<strong>Incident:</strong><br />
			<?php echo $this->incident; ?>
		</td>
		<td width="40">
			<strong>Class:</strong><br />
			<?php echo $course; ?>
		</td>
		<td width="100">
			<strong>Teacher:</strong><br />
			<?php echo $this->authorName; ?>
		</td>
		<td width="84">
			<strong>Date:</strong><br />
			<?php echo $this->message->getDate(); ?>
		</td>
	</tr>
	<tr>
		<td colspan="5" width="476">
			<strong>Subject:</strong><br />
			<?php echo $course; ?>
		</td>
	</tr>
	<tr>
		<td colspan="5" width="476"><?php echo $incText; ?></td>
	</tr>
	<?php if( $this->inc->getParentId() != 67 ) : // no actions for clear referals ?>
		<tr>
			<td colspan="5" width="476"><?php echo $actionText; ?></td>
		</tr>
	<?php endif; ?>
</table>
<?php else : ?>
<table width="100%" cellpadding="2"cellspacing="0">
	<tr>
		<td width="24">&nbsp;</td>
		<td colspan="3" width="292">&nbsp;</td>
		<td width="100"><?php echo $this->authorName; ?></td>
		<td width="84"><?php echo $this->message->getDate(); ?></td>
	</tr>
	<tr>
		<td width="24">&nbsp;</td>
		<td width="24">&nbsp;</td>
		<td colspan="4" width="452">
			<strong>Comment:</strong><br />
			<?php echo $this->message->getDatum( 'comment' ); ?>
		</td>
	</tr>
	<tr>
		<td width="24">&nbsp;</td>
		<td width="24">&nbsp;</td>
		<td colspan="4" width="452"><?php echo $actionText; ?></td>
	</tr>
</table>
<?php endif; ?>
<?php $this->first = false; ?>
<?php if( $this->last ) : ?>
</td></tr></table>
<?php endif; ?>