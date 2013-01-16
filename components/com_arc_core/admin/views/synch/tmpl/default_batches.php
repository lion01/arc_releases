<?php
/**
 * @package     Arc
 * @subpackage  Core
 * @copyright   Copyright (C) 2005 Punnet. All rights reserved. See COPYRIGHT_ARC.txt
 * @license     http://www.gnu.org/licenses/gpl.html GNU/GPL. See LICENSE_ARC.txt
 * Arc is free software: you can redistribute it and/or modify
 * it under the terms of version 3 of the GNU General Public License
 * as published by the Free Software Foundation.
 * The disclaimer of warranty as stated in the GPL applies to this program
 */

defined( '_JEXEC' ) or die( 'Restricted access' );
//JHTML::script( 'default.js', $this->addPath, true );

?>
<form action="index.php" method="post" name="adminForm" id="adminForm">
	<?php if( !empty($this->batches) ): ?>
	<table class="adminlist" cellspacing="1">
		<thead>
			<tr>
				<th class="title" width="5%"><input type="checkbox" name="toggle" value="" onclick="checkAll(<?php echo count($this->batches); ?>);" /></th>
				<th class="title" width="15%" align="center"><?php echo JText::_( 'Created' ); ?></th>
				<th class="title" width="20%" align="center"><?php echo JText::_( 'Callback' ); ?></th>
				<th class="title" width="30%" align="center"><?php echo JText::_( 'Params' ); ?></th>
				<th class="title" width="10%" align="center"><?php echo JText::_( 'Done' ); ?></th>
				<th class="title" width="10%" align="center"><?php echo JText::_( 'Ready' ); ?></th>
				<th class="title" width="10%" align="center"><?php echo JText::_( 'Data Sources' ); ?></th>
			</tr>
		</thead>
		<tbody>
		<?php $this->curIndex = 0; ?>
		<?php foreach( $this->batches as $this->batch ): ?>
			<?php echo $this->loadTemplate( 'batch_row' ); ?>
			<?php $this->curIndex++; ?>
		<?php endforeach; ?>
		</tbody>
	</table>
	<?php else: ?>
		<?php echo JText::_( 'There are no batches to display' ); ?>
	<?php endif; ?>
	<input type="hidden" name="option" value="com_arc_core" />
	<input type="hidden" name="view" value="synch" />
	<input type="hidden" name="task" value="" />
	<input type="hidden" name="boxchecked" value="0" />
</form>