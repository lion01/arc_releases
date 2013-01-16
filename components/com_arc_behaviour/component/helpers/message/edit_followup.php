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

JHTML::script( 'edit_followup.js', $this->scriptDir, true );
?>
<form method="post" name="add_inc_form" id="add_inc_form">
<input type="hidden" name="msg_id"     value="<?php echo $this->message->getId(); ?>" />
<input type="hidden" name="msg_handler" value="behaviour" />
<input type="hidden" name="msg_author" value="<?php echo $this->message->getAuthor(); ?>" />
<input type="hidden" name="msg_date"   value="<?php echo $this->message->getDate(); ?>" />
<?php
foreach( $this->message->getTagIds(true) as $tag ) {
	echo '<input type="hidden" name="msg_tags[per][]" value="'.$tag.'" />'."\n";
}
foreach( $this->message->getTagIds(false) as $tag ) {
	echo '<input type="hidden" name="msg_tags[gen][]" value="'.$tag.'" />'."\n";
}
?>

<div class="msg_outline">
<h4>Comment:</h4>
<textarea class="rnd" name="msg_data[comment]"><?php echo $this->message->getDatum('comment'); ?></textarea>
</div>

<div class="msg_action_outline">
<h4>Action:</h4>
<?php echo JHTML::_( 'arc_behaviour.action', 'msg_data[action]', 'msg_data[action_text]', $this->incType->getId(), $this->message->getDatum('action'), $this->message->getDatum('action_text') ); ?><br />
</div>

<div id="msg_sec3">
	<input class="btn" type="submit" name="task" value="Save Draft" />
	<input class="btn" type="submit" name="task" value="Send" />
</div>

<div id="msg_sec4" style="display: none">
<p>Saving...</p>
</div>

</form>