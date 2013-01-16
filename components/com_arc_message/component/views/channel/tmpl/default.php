<?php
/**
 * @package     Arc
 * @subpackage  Message
 * @copyright   Copyright (C) 2005 Punnet. All rights reserved. See COPYRIGHT_ARC.txt
 * @license     http://www.gnu.org/licenses/gpl.html GNU/GPL. See LICENSE_ARC.txt
 * Arc is free software: you can redistribute it and/or modify
 * it under the terms of version 3 of the GNU General Public License
 * as published by the Free Software Foundation.
 * The disclaimer of warranty as stated in the GPL applies to this program
 */

defined( '_JEXEC' ) or die( 'Restricted access' );

JHTML::script( 'default.js', JURI::base().'components'.DS.'com_arc_message'.DS.'views'.DS.'channel'.DS.'tmpl'.DS ); ?>
<h3 id="arc_title">Channels</h3>

<div id="arc_main">
<input id="channelSubUrl" name="channelSubUrl" type="hidden" value="<?php echo ApotheosisLib::getActionLinkByName('apoth_msg_channel_ajax', array('message.scopes'=>'subscriptions')) ?>" />
<input id="channelDefUrl" name="channelDefUrl" type="hidden" value="<?php echo ApotheosisLib::getActionLinkByName('apoth_msg_channel_ajax', array('message.scopes'=>'channel')) ?>" />

<div class="channel_bg">
<div id="subs">
<h4>Subscriptions and available channels</h4>

<?php if( ApotheosisLibAcl::getUserLinkAllowed( 'apoth_msg_channel_restricted' ) !== false ) : ?>
<label>Manage subscriptions for:</label><br />
<div class="combo_container">
<?php echo JHTML::_( 'arc_people.people', 'subscribers', null, 'teacher OR staff OR pupil OR parent', true, array('multiple'=>'multiple')); ?>
<?php echo JHTML::_( 'arc_people.lists', 'subscriber_lists', null, false, true, array('multiple'=>'multiple')); ?>
</div>

<input type="button" class="btn" id="rechannel" name="rechannel" value="Update Channels" />
<br />
<br />
<?php endif; ?>

<div id="subs_def">
<?php echo $this->loadTemplate( 'channels' ); ?>
</div>
</div>

<div id="channel">
<h4>Channel details</h4>
<div id="channel_def">
<?php echo $this->loadTemplate( 'definition' ); ?>
</div>
</div>
</div>
</div>