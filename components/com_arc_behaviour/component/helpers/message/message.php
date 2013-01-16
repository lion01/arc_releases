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
?>
<div class="existing_msg">
<!--
<div class="msg_summary">
<div><h4>Subject:</h4><?php // echo ApotheosisData::_( 'course.name', $this->message->getDatum('group_id') ); ?></div>
</div>-->


<div class="msg_outline">
<h4>Incident:</h4>
<?php
$fInc = ApothFactory::_( 'behaviour.IncidentType' );
$inc = $fInc->getinstance( $this->message->getDatum('incident') );
echo $inc->getLabel().( $inc->getHasText() ? ': '.$this->message->getDatum('incident_text') : '' ).'<br />'
	.$this->message->getDatum('outline'); ?>
</div>

<?php if( $inc->getParentId() != 67 ) : // no actions for clear referals ?>
<div class="msg_action_outline">
<h4>Outline of action taken:</h4>
<?php
$fAct = ApothFactory::_( 'behaviour.Action' );
$a = $fAct->getInstance( $this->message->getDatum('action') );
echo $a->getLabel().'<br />'
	.$this->message->getDatum( 'action_text' );
if( $this->message->getDatum('callout') ) {
	echo '<br />A call-out was requested';
}
elseif( $inc->getParentId() == 5 ) { // purples need some affirmation either way
	echo '<br />No call-out';
}
if( $this->message->getDatum('more_action') ) {
	echo '<br />Further action is required';
}
?>
</div>
<?php endif; ?>
</div>