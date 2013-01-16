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
	<?php if (count($this->courses)) : ?>
	<table class="adminlist" cellspacing="1">
		<thead>
			<tr>
				<th class="title" width="5%"></th>
				<th class="title" width="5%" align="center"><?php echo JText::_( 'id' ); ?></th>
				<th class="title" width="25%" align="center"><?php echo JText::_( 'Fullname' ); ?></th>
				<th class="title" width="15%" align="center"><?php echo JText::_( 'Shortname' ); ?></th>
				<th class="title" width="20%" align="center"><?php echo JText::_( 'Parent' ); ?></th>
				<th class="title" width="10%" align="center"><?php echo JText::_( 'Twin' ); ?></th>
				<th class="title" width="10%" align="center"><?php echo JText::_( 'Start Date' ); ?></th>
				<th class="title" width="10%" align="center"><?php echo JText::_( 'End Date' ); ?></th>
			</tr>
		</thead>
		<tbody>
		<?php for ($i=0, $n=count($this->courses); $i < $n; $i++) : ?>
			<?php
				$this->loadCourse($i);
				echo $this->loadTemplate('item');
			?>
		<?php endfor; ?>
		</tbody>
	</table>
	<?php else : ?>
		<?php echo JText::_( 'There are no pseudo-courses set up' ); ?>
	<?php endif; ?>

	<input type="hidden" name="option" value="com_arc_report" />
	<input type="hidden" name="view" value="pseudo" />
	<input type="hidden" name="task" value="" />
	<input type="hidden" name="boxchecked" value="0" />
</form>