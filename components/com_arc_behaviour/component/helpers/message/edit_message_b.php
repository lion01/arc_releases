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
<div class="row">
	<h4 class="wide">More action required</h4>
	<div><input type="checkbox" name="msg_data[more_action]" <?php echo ( $this->message->getDatum('more_action') ? 'checked="checked"' : '' ); ?>/></div>
</div>

<?php if( $this->incType->getId() == 5 ) : ?>
<div class="row">
	<h4 class="wide">Callout required</h4>
	<div><input type="checkbox" name="msg_data[callout]" <?php echo ( (!isset($this->fixedType) || !$this->fixedType || $this->message->getDatum('callout')) ? 'checked="checked"' : '' ); ?> /></div>
</div>
<?php endif; ?>

<div class="row">
	<h4 class="wide">Brief outline:</h4>
	<div><textarea name="msg_data[outline]"><?php echo $this->message->getDatum('outline'); ?></textarea></div>
</div>

<div class="row">
	<h4 class="wide">Outline of action taken / planned to be taken:</h4>
	<div><?php echo JHTML::_( 'arc_behaviour.action', 'msg_data[action]', 'msg_data[action_text]', $this->incType->getId(), $this->message->getDatum('action'), $this->message->getDatum('action_text') ); ?></div>
</div>

<input type="hidden" name="msg_tags[gen][]" value="<?php echo $this->incType->getTag(); ?>" />
