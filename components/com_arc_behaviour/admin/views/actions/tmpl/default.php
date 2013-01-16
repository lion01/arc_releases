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

$this->oddRow = false;
?>
<form action="index.php" method="post" name="adminForm" id="adminForm">
	<?php if( $this->pagination->total ): ?>
	<table class="adminlist" cellspacing="1">
		<thead>
			<tr>
				<th class="title" width="5%"><input type="checkbox" name="toggle" value="" onclick="checkAll(<?php echo $this->pagination->total; ?>);" /></th>
				<th class="title" width="40%" align="center"><?php echo JText::_( 'Label' ); ?></th>
				<th class="title" width="10%" align="center"><?php echo JText::_( 'Score' ); ?></th>
				<th class="title" width="10%" align="center"><?php echo JText::_( 'Text' ); ?></th>
				<th class="title" width="35%" align="center"><?php echo JText::_( 'Incidents' ); ?></th>
			</tr>
		</thead>
		<tfoot>
			<tr>
				<td colspan="13">
					<?php echo $this->pagination->getListFooter(); ?>
				</td>
			</tr>
		</tfoot>
		<tbody>
		<?php foreach( $this->actions as $this->actionIndex=>$this->action ): ?>
			<?php echo $this->loadTemplate( 'action_row' ); ?>
		<?php endforeach; ?>
		</tbody>
	</table>
	<?php else: ?>
		<?php echo JText::_( 'There are no action types to display' ); ?>
	<?php endif; ?>
	<input type="hidden" name="option" value="com_arc_behaviour" />
	<input type="hidden" name="view" value="actions" />
	<input type="hidden" name="task" value="" />
	<input type="hidden" name="boxchecked" value="0" />
</form>