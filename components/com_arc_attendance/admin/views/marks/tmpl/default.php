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

echo $this->state->message; ?>
<form action="index.php" method="post" name="adminForm">
	<?php if (count($this->items)) : ?>
	<table class="adminlist" cellspacing="1">
		<thead>
			<tr>
				<th class="title" width="5%"><input type="checkbox" name="toggle" value="" onclick="checkAll(<?php echo count($this->items); ?>);" />&nbsp;<?php echo JText::_( 'Edit' ); ?></th>
				<th class="title" width="5%" align="center"><?php echo JText::_( 'Code' ); ?></th>
				<th class="title" width="20%" align="center"><?php echo JText::_( 'School Meaning' ); ?></th>
				<th class="title" width="20%" align="center"><?php echo JText::_( 'Statistical Meaning' ); ?></th>
				<th class="title" width="20%" align="center"><?php echo JText::_( 'Physical Meaning' ); ?></th>
				<th class="title" width="5%" align="center"><?php echo JText::_( 'Common' ); ?></th>
				<th class="title" width="5%" align="center"><?php echo JText::_( 'Type' ); ?></th>
				<th class="title" width="5%" align="center"><?php echo JText::_( 'All Day' ); ?></th>
				<th class="title" width="5%" align="center"><?php echo JText::_( 'Order' ); ?></th>
			</tr>
		</thead>
		<tbody>
		<?php for ($i=0, $n=count($this->items); $i < $n; $i++) : ?>
			<?php
				$this->loadItem($i);
				echo $this->loadTemplate('item');
			?>
		<?php endfor; ?>
		</tbody>
	</table>
	<?php else : ?>
		<?php echo JText::_( 'There are no custom components installed' ); ?>
	<?php endif; ?>

	<input type="hidden" name="option" value="com_arc_attendance" />
	<input type="hidden" name="view" value="marks" />
	<input type="hidden" name="task" value="toggleCommon" />
	<input type="hidden" name="boxchecked" value="0" />
</form>
