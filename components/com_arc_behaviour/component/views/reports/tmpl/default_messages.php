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

$showMsg = ApotheosisLibAcl::getUserLinkAllowed( 'apoth_msg_hub_inter' ); /* *** this should probably be more granular */ ?>
<div class="series">
<div class="series_header">
<h4><?php echo $this->data['_meta']['label']; ?></h4>
<div class="info">
<?php echo $this->data['_meta']['init'].' to '.$this->data['_meta']['end'].' in '.array_sum( $this->data['_meta']['tallyThreads'] ).' incidents'; ?>
</div>

<?php if( $showMsg ) : ?>
<div class="messages_clicker_div"><a href="#" class="messages_clicker" id="clicker_<?php echo $this->sId; ?>">Toggle Messages</a></div>
<?php endif; ?>
</div>

<div class="series_graph">
<?php
$graphLink = $this->_getGraphLink( array($this->sId), 50, 15, false );
?>
<img src="<?php echo $graphLink; ?>" title="Graph of behaviour scores" />
</div>

<?php if( $showMsg ) : ?>
<div class="series_messages">
<div class="messages_slider" id="messages_slider_<?php echo $this->sId; ?>">
<?php
$this->messages = array();
foreach( $this->data as $date=>$datum ) {
	if( isset($datum['messages']) && is_array($datum['messages']) ) {
		foreach( $datum['messages'] as $id ) {
			$this->messages[] = $id;
		}
	}
}
?>
<input class="message_id_list" type="hidden" name="<?php echo $this->sId; ?>" value="<?php echo empty($this->messages) ? '-1' : implode( ',', $this->messages ); ?>" />
Not yet loaded
</div>
</div>
<?php endif; ?>
</div>
