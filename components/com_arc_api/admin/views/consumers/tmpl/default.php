<?php
/**
 * @package     Arc
 * @subpackage  Course
 * @copyright   Copyright (C) 2005 Punnet. All rights reserved. See COPYRIGHT_ARC.txt
 * @license     http://www.gnu.org/licenses/gpl.html GNU/GPL. See LICENSE_ARC.txt
 * Arc is free software: you can redistribute it and/or modify
 * it under the terms of version 3 of the GNU General Public License
 * as published by the Free Software Foundation.
 * The disclaimer of warranty as stated in the GPL applies to this program
 */

defined( '_JEXEC' ) or die( 'Restricted access' );

// add default javascript
JHTML::script( 'default.js', $this->addPath, true );

?>
<form action="index.php" method="post" name="adminForm" id="adminForm">
	<?php if( !empty($this->consumers) ): ?>
	<table class="adminlist" cellspacing="1">
		<thead>
			<tr>
				<th class="title" width="5%"><input type="checkbox" name="toggle" value="" onclick="checkAll(<?php echo count($this->consumers); ?>);" /></th>
				<th class="title" width="30%" align="center"><?php echo JText::_( 'Name' ); ?></th>
				<th class="title" width="40%" align="center"><?php echo JText::_( 'Description' ); ?></th>
				<th class="title" width="15%" align="center"><?php echo JText::_( 'Key' ); ?></th>
				<th class="title" width="10%" align="center"><?php echo JText::_( 'Active' ); ?></th>
			</tr>
		</thead>
		<tfoot>
			<tr>
				<td colspan="14">
					<?php echo $this->pagination->getListFooter(); ?>
				</td>
			</tr>
		</tfoot>
		<tbody>
		<?php foreach( $this->consumers as $this->curIndex=>$this->consumer ): ?>
			<?php echo $this->loadTemplate( 'consumer_row' ); ?>
		<?php endforeach; ?>
		</tbody>
	</table>
	<?php else: ?>
		<?php echo JText::_( 'There are no consumers to display' ); ?>
	<?php endif; ?>
	<input type="hidden" name="option" value="com_arc_api" />
	<input type="hidden" name="view" value="consumers" />
	<input type="hidden" name="task" value="" />
	<input type="hidden" name="boxchecked" value="0" />
</form>