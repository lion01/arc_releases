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
?>
<form action="index.php" method="post" name="adminForm" id="adminForm">
	<table class="adminlist" cellspacing="1">
		<thead>
			<tr>
				<th class="title" width="3%" align="center"><?php echo JText::_( 'id' ); ?></th>
				<th class="title" width="20%" align="center"><?php echo JText::_( 'Title' ); ?></th>
				<th class="title" width="37%" align="center"><?php echo JText::_( 'Description' ); ?></th>
				<th class="title" width="5%" align="center"><?php echo JText::_( 'Code' ); ?></th>
				<th class="title" width="5%" align="center"><?php echo JText::_( 'Year' ); ?></th>
				<th class="title" width="5%" align="center"><?php echo JText::_( 'Type' ); ?></th>
				<th class="title" width="3%" align="center"><?php echo JText::_( 'Parent' ); ?></th>
				<th class="title" width="5%" align="center"><?php echo JText::_( 'Start Date' ); ?></th>
				<th class="title" width="5%" align="center"><?php echo JText::_( 'End Date' ); ?></th>
				<th class="title" width="3%" align="center"><?php echo JText::_( 'Sort Order' ); ?></th>
				<th class="title" width="3%" align="center"><?php echo JText::_( 'Reportable' ); ?></th>
				<th class="title" width="3%" align="center"><?php echo JText::_( 'VLE ID' ); ?></th>
				<th class="title" width="3%" align="center"><?php echo JText::_( 'External' ); ?></th>
			</tr>
		</thead>
		<tfoot>
			<tr>
				<td colspan="13">&nbsp;</td>
			</tr>
		</tfoot>
		<tbody>
		<?php foreach( $this->courses as $this->curIndex=>$this->course ): ?>
			<?php echo $this->loadTemplate( 'course_row_delete' ); ?>
			<?php $courseIds[] = $this->course->getData( 'id' ); ?>
		<?php endforeach; ?>
		</tbody>
	</table>
	<input type="hidden" name="option" value="com_arc_course" />
	<input type="hidden" name="view" value="courses" />
	<input type="hidden" name="task" value="delete" />
	<input type="hidden" name="course_ids" value="<?php echo htmlspecialchars( serialize($courseIds) ); ?>" />
</form>