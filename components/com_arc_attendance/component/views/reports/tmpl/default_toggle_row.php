<?php
/**
 * @package     Arc
 * @subpackage  Attendance
 * @copyright   Copyright (C) 2005 Punnet. All rights reserved. See COPYRIGHT_ARC.txt
 * @license     http://www.gnu.org/licenses/gpl.html GNU/GPL. See LICENSE_ARC.txt
 * Arc is free software: you can redistribute it and/or modify
 * it under the terms of version 3 of the GNU General Public License
 * as published by the Free Software Foundation.
 * The disclaimer of warranty as stated in the GPL applies to this program
 */

defined( '_JEXEC' ) or die( 'Restricted access' );
?>
<tr>
	<?php if( $this->edits ) : ?>
		<th class="edit_cell">
			<input id="master_edit_input" type="checkbox" />
		</th>
	<?php endif; ?>
		<th><?php echo $this->toggles; ?></th>
	<?php echo $this->sectionRow; ?>
</tr>