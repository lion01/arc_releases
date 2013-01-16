<?php
/**
 * @package     Arc
 * @subpackage  Report
 * @copyright   Copyright (C) 2005 Punnet. All rights reserved. See COPYRIGHT_ARC.txt
 * @license     http://www.gnu.org/licenses/gpl.html GNU/GPL. See LICENSE_ARC.txt
 * Arc is free software: you can redistribute it and/or modify
 * it under the terms of version 3 of the GNU General Public License
 * as published by the Free Software Foundation.
 * The disclaimer of warranty as stated in the GPL applies to this program
 */

defined( '_JEXEC' ) or die( 'Restricted access' );
?>
<form action="index.php" method="post" name="adminForm">
	<?php if (count($this->items)) : ?>
	<table class="adminlist" cellspacing="1">
		<thead>
			<tr>
				<th class="title" width="5%"></th>
				<th class="title" width="5%" align="center"><?php echo JText::_( 'id' ); ?></th>
				<th class="title" width="10%" align="center"><?php echo JText::_( 'Year Group' ); ?></th>
				<th class="title" width="20%" align="center"><?php echo JText::_( 'Valid From' ); ?></th>
				<th class="title" width="20%" align="center"><?php echo JText::_( 'Valid To' ); ?></th>
				<th class="title" width="20%" align="center"><?php echo JText::_( 'Multiple?' ); ?></th>
				<th class="title" width="20%" align="center"><?php echo JText::_( 'Rechecker' ); ?></th>
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
	
	<input type="hidden" name="option" value="com_arc_report" />
	<input type="hidden" name="view" value="cycles" />
	<input type="hidden" name="task" value="" />
	<input type="hidden" name="boxchecked" value="0" />
</form>