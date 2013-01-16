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

$fInc = ApothFactory::_( 'behaviour.IncidentType' );
$showDetailsLink = '';
$l = ApotheosisLibAcl::getUserLinkAllowed( 'apoth_msg_hub_toggle_thread', array('message.threads'=>$this->thread->getId(), 'message.tasks'=>'toggleThread') );
if( $l !== false ) {
	$showDetailsLink = '<a name="thread_'.$this->thread->getId().'" /><a href="'.$l.'" class="thread_toggle">('.$this->count.')</a>';
}

// show either the first message or all messages depending on if the thread shows details
$td = $this->thread->getDetailsShown();
$ids = ( $td ? $this->thread->getMessageIds() : array( $this->thread->getFirstMessageId() ) );
$first = true;
foreach( $ids as $id ) {
	unset( $this->message );
	$this->message = $this->fMsg->getInstance( $id );
	$this->author = ApotheosisLib::getUser( ApotheosisLib::getJUserId( $this->message->getAuthor() ) );
	$this->authorName = ApotheosisLib::nameCase( 'teacher', $this->author->title, $this->author->firstname, $this->author->middlenames, $this->author->surname );
	$inc = $fInc->getInstance( $this->message->getDatum('incident') );
//	$inc = $inc->getLabel();
//	$this->incident = ( ( strlen( $inc ) > 20 ) ? substr( $inc, 0, 20 ).'...' : $inc );
	$this->incident = $inc->getLabel();
	
	$l = ApotheosisLibAcl::getUserLinkAllowed( 'apoth_msg_hub_toggle_message', array( 'message.messages'=>$this->message->getId(), 'message.tasks'=>'toggleMessage') );
	$detailLink = ( ($td && ($l !== false)) ? '<a name="msg_'.$this->message->getId().'"></a><a class="btn" href="'.$l.'">Details</a>' : '&nbsp;' );
	
	// echo out the message heading
	if( $first ) {
		?>
		<tr>
			<td><input type="checkbox" name="check[<?php echo $this->thread->getId(); ?>]" class="thread_checkbox" /></td>
			<td style="white-space: nowrap"><?php echo JHTML::_('arc.dot', $this->color, $this->color.' incident').$showDetailsLink; ?></td>
			<td><?php echo implode( ', ', $this->studentNames ); ?></td>
			<td><?php echo $this->incident; ?></td>
			<td><?php echo ApotheosisData::_( 'course.name', $this->message->getDatum( 'group_id' ) ); ?>
			<td><?php echo $this->authorName; ?></td>
			<td><?php echo JHTML::_( 'arc.date', $this->message->getDate() ); ?></td>
			<td><?php echo $detailLink ?></td>
		</tr>
		<?php
	}
	else {
		?>
		<tr>
			<td>&nbsp;</td>
			<td>&nbsp;</td>
			<td colspan="6"><hr /></td>
		</tr>
		<tr>
			<td>&nbsp;</td>
			<td>&nbsp;</td>
			<td>&nbsp;</td>
			<td>&nbsp;</td>
			<td>&nbsp;</td>
			<td><?php echo $this->authorName; ?></td>
			<td><?php echo JHTML::_( 'arc.date' , $this->message->getDate() ); ?></td>
			<td><?php echo $detailLink ?></td>
		</tr>
		<?php
	}
	
	// echo out the message body
	$this->fixedType = false;
	if( $td && $this->message->getDetailsShown() ) {
		$attribs = $this->message->getTagLabels('folder');
		$this->folder = strtolower( reset($attribs) );
		?>
		<tr class="contents">
			<td>&nbsp;</td>
			<td>&nbsp;</td>
			<?php
			if( $first ) {
				?>
				<?php
				if( $this->folder == 'drafts' ) {
					$this->fixedType = true;
					?>
					<td colspan="6">
					<?php require( $this->tmplDir.'edit_message.php' ); ?>
					</td>
					<?php
				}
				else {
					?>
					<td>
						<div class="behaviour_graph">
							<div id="extra_inner_<?php echo $this->thread->getId(); ?>">
								<?php echo $this->renderExtra( $this->message ); ?>
							</div>
						</div>
					</td>
					<td colspan="5">
					<?php require( $this->tmplDir.'message.php' ); ?>
					</td>
					<?php
				}
			}
			else {
				?>
				<td colspan="6">
				<?php
				if( ($id < 0) || ($this->folder == 'drafts') ) {
					require( $this->tmplDir.'edit_followup.php' );
				}
				else {
					require( $this->tmplDir.'followup.php' );
				}
				?>
				</td>
				<?php
			}
			?>
		</tr>
		<?php
	}
	
	$first = false;
}

$reply = ApotheosisLibAcl::getUserLinkAllowed( 'apoth_msg_hub_reply', array('message.messages'=>$id, 'message.tasks'=>'replyToMessage') );
if( $td && ($reply !== false) && ($id > 0) && isset($this->folder) && ($this->folder != 'drafts') ) {
	?>
	<tr>
	<td>&nbsp;</td>
	<td>&nbsp;</td>
	<td style="padding-bottom:10px;"><br /><a class="btn" href="<?php echo $reply; ?>">Reply</a></td>
	<td>&nbsp;</td>
	<td>&nbsp;</td>
	<td>&nbsp;</td>
	<td>&nbsp;</td>
	<td>&nbsp;</td>
	</tr>
	<?php
}
?>