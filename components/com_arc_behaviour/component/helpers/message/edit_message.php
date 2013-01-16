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

JHTML::script( 'edit_message.js', $this->scriptDir, true );

if( $this->message->getId() < 0 ) {
	$date = date( 'Y-m-d' );
	$time = date( 'H:i' );
}
else {
	$tmp = explode( ' ', $this->message->getDate() );
	$date = $tmp[0];
	$time = $tmp[1];
}

?>
<div class="new_msg">
<form method="post" name="add_inc_form" id="add_inc_form">
<input type="hidden" name="data"        value="<?php echo urlencode( json_encode( array('new'=>false) ) ); ?>" />
<input type="hidden" id="formPartUrl"   value="<?php echo ApotheosisLibAcl::getUserLinkAllowed( 'apoth_msg_hub_inter_ajax', array( 'msg.formParts'=>'~formPart~' ) ); ?>" />
<input type="hidden" name="split_on"    value="student_id" />
<input type="hidden" name="msg_id"      value="<?php echo $this->message->getId(); ?>" />
<input type="hidden" name="msg_handler" value="behaviour" />
<input type="hidden" name="msg_author"  value="<?php echo $this->message->getAuthor(); ?>" />
<input type="hidden" name="msg_created" value="<?php echo $this->message->getCreated(); ?>" />
<input type="hidden" name="msg_tags[per][]" value="<?php echo ApotheosisData::_( 'message.tagId', 'folder', 'behaviour' ); ?>" />

<div class="msg_sec1">
<div class="row">
	<h4>Name:</h4>
	<div><p><?php
if( $this->message->getId() < 0 ) {
	echo JHTML::_( 'arc_people.people', 'msg_data[student_id]', $this->message->getDatum('student_id'), 'pupilof.'.$this->message->getDatum('group_id').' OR pupil', true, array('multiple'=>'multiple'));
}
else {
	// only allow modification of the one person the existing message is for - not addition of more people
	echo JHTML::_( 'arc_people.people', 'msg_data[student_id]', $this->message->getDatum('student_id'), 'pupilof.'.$this->message->getDatum('group_id').' OR pupil', true, array() );
} ?></p></div>
</div>

<div class="row">
	<h4>Class:</h4>
	<div><span id="group_name"><?php require( $this->tmplDir.'edit_message_groupname.php' ); ?></span>
		(<a href="#" id="change_grp">change</a>)
		<div id="grp_list"><?php echo JHTML::_( 'groups.grouptree', 'groups', null, false ); ?></div>
		<input type="hidden" name="msg_data[group_id]" id="msg_data_group_id" value="<?php echo $this->message->getDatum('group_id'); ?>" />
	</div>
</div>

<div class="row">
	<h4>Date:</h4>
	<div><span id="now_or_date" style="display:none">
	<input type="radio" name="nowbox" value="now" checked="checked" /> Now<br />
	<input type="radio" name="nowbox" value="at"/>At: </span>
		<?php echo JHTML::_( 'arc.date', 'msg_date[date]', $date ); ?>
		<input type="text" id="msg_date[time]" name="msg_date[time]" value="<?php echo $time ?>" style="width: 4em;" /> (00:00 to 23:59)
	</div>
</div>

<div class="row">
	<h4>Type:</h4>
	<div><?php echo JHTML::_( 'arc_behaviour.type', 'msg_inc_type', 'msg_data[incident]', 'msg_data[incident_text]', true, ( !isset($this->fixedType) || !$this->fixedType ), $this->incType->getId(), $this->inc->getId(), $this->message->getDatum('incident_text') ) ?>
	</div>
</div>

</div><!-- End section 1 - Common fields -->

<div class="msg_sec2" id="msg_sec2">
<?php
$subTemplate = $this->_getIncTemplateName( $this->incType );
if( isset($subTemplate) ) {
	require( $subTemplate ); // for non-ajax display
}?>
</div>

<div id="msg_sec3">
<div class="row">
	<div>
	<input type="submit" class="btn" name="task" value="Save Draft" />
	<input type="submit" class="btn" name="task" value="Send" />
	</div>
</div>
</div> <!-- End section 3 - Submit buttons -->

</form>
</div>