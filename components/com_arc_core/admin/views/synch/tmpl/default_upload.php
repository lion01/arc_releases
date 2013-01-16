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

JHTML::script( 'upload.js', $this->addPath, true );
?>
<form enctype="multipart/form-data" action="index.php" method="post" name="adminForm" id="adminForm">
	<?php if( !empty($this->queue) ): ?>
	<table class="adminlist" cellspacing="1">
		<thead>
			<tr>
				<th class="title" width="10%" align="center"><?php echo JText::_( 'Source' ); ?></th>
				<th class="title" width="10%" align="center"><?php echo JText::_( 'Call' ); ?></th>
				<th class="title" width="10%" align="center"><?php echo JText::_( 'Params' ); ?></th>
				<th class="title" width="25%" align="center"><?php echo JText::_( 'Params' ); ?></th>
				<th class="title" width="5%" align="center"><?php echo JText::_( 'Complete' ); ?></th>
				<th class="title" width="25%" align="center"><?php echo JText::_( 'File Upload' ); ?></th>
				<th class="title" width="10%" align="center"><?php echo JText::_( 'File' ); ?></th>
				<th class="title" width="5%" align="center"><?php echo JText::_( 'File Status' ); ?></th>
			</tr>
		</thead>
		<tbody>
		<?php $this->curIndex = 0; ?>
		<?php foreach( $this->queue as $this->job ): ?>
			<?php echo $this->loadTemplate( 'job_upload_row' ); ?>
			<?php $this->curIndex++; ?>
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