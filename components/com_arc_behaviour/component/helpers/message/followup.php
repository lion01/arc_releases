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
<div class="msg_outline">
<h4>Comment:</h4>
<?php echo $this->message->getDatum('comment'); ?>
</div>

<div class="msg_action_outline">
<h4>Action:</h4>
<?php
$fAct = ApothFactory::_( 'behaviour.Action' );
$a = $fAct->getInstance( $this->message->getDatum('action') );
echo $a->getLabel().'<br />'
	.$this->message->getDatum( 'action_text' );
?>
</div>
</div>