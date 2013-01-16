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
	<h4>Comment:</h4>
	<div><textarea name="msg_data[outline]"><?php echo $this->message->getDatum('outline'); ?></textarea></div>
</div>

<div class="row">
	<h4>Action:</h4>
	<div><?php echo JHTML::_( 'arc_behaviour.action', 'msg_data[action]', 'msg_data[action_text]', $this->incType->getId(), $this->message->getDatum('action'), $this->message->getDatum('action_text') ); ?></div>
</div>

<input type="hidden" name="msg_tags[gen][]" value="<?php echo $this->incType->getTag(); ?>" />
