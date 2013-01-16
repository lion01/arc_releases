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
<div class="manage_form_input_row">
	<div class="manage_form_label_div">Status:</div>
	<div class="manage_form_input_div">
		<?php
			$curStatus = $this->curVideo->getStatusInfo();
			echo JHTML::_( 'arc.dot', $curStatus['colour'], $curStatus['status'] );
		?>
	</div>
</div>
<div class="manage_form_input_row">
	<div class="manage_form_label_div">Comments:</div>
	<div class="manage_form_input_div"><?php echo $curStatus['comment']; ?></div>
</div>