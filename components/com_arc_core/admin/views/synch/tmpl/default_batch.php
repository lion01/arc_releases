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
	<?php if( !empty($this->queue) ): ?>
	<table class="adminlist" cellspacing="1">
		<thead>
			<tr>
				<th class="title" width="5%"><input type="checkbox" name="toggle" value="" onclick="checkAll(<?php echo count($this->queue); ?>);" /></th>
				<th class="title" width="15%" align="center"><?php echo JText::_( 'Source' ); ?></th>
				<th class="title" width="20%" align="center"><?php echo JText::_( 'Call' ); ?></th>
				<th class="title" width="40%" align="center"><?php echo JText::_( 'Params' ); ?></th>
				<th class="title" width="10%" align="center"><?php echo JText::_( 'Taken' ); ?></th>
				<th class="title" width="10%" align="center"><?php echo JText::_( 'Ready' ); ?></th>
			</tr>
		</thead>
		<tbody>
		<?php foreach( $this->queue as $this->curIndex=>$this->job ): ?>
			<?php echo $this->loadTemplate( 'job_row' ); ?>
		<?php endforeach; ?>
		</tbody>
	</table>
	<?php else: ?>
		<?php echo JText::_( 'There are no jobs to display' ); ?>
	<?php endif; ?>
	<input type="hidden" name="option" value="com_arc_core" />
	<input type="hidden" name="view" value="synch" />
	<input type="hidden" name="task" value="" />
	<input type="hidden" name="boxchecked" value="0" />
</form>