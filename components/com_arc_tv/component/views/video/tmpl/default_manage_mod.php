<?php
/**
 * @package     Arc
 * @subpackage  TV
 * @copyright   Copyright (C) 2005 Punnet. All rights reserved. See COPYRIGHT_ARC.txt
 * @license     http://www.gnu.org/licenses/gpl.html GNU/GPL. See LICENSE_ARC.txt
 * Arc is free software: you can redistribute it and/or modify
 * it under the terms of version 3 of the GNU General Public License
 * as published by the Free Software Foundation.
 * The disclaimer of warranty as stated in the GPL applies to this program
 */

defined( '_JEXEC' ) or die( 'Restricted access' );
?>
<div id="manage_mod_div">
	<div class="manage_form_input_row">
		<div class="manage_form_label_div">Comments:</div>
		<div class="manage_form_input_div"><textarea id="manage_comments_input" name="manage_comments_input"></textarea></div>
	</div>
	<div class="manage_form_input_row">
		<div class="manage_form_label_div">Approval:</div>
		<div class="manage_form_input_div">
			<input id="manage_form_accept_button" type="submit" value="Accept" name="task" />
			<input id="manage_form_reject_button" type="submit" value="Reject" name="task" />
			<?php echo $this->loadTemplate( 'ajax_spinners' ); ?>
		</div>
	</div>
</div>