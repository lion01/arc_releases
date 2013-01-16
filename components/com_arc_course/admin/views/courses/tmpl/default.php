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
	<table>
		<tr>
			<td width="100%">
				<?php echo JText::_( 'Name' ); ?>:
				<input type="text" name="search" id="search" value="<?php echo $this->search; ?>" class="text_area" />
				<button id="admin_form_search_button"><?php echo JText::_( 'Search' ); ?></button>
				<button id="admin_form_reset_button"><?php echo JText::_( 'Reset' ); ?></button>
			</td>
			<td nowrap="nowrap">
			<?php echo JText::_( 'Course type' ); ?>:
				<?php echo JHTML::_( 'arc_course.filterType', 'filter_type', '', $this->type ); ?>
			</td>
		</tr>
	</table>

	<?php if( $this->courseCount ): ?>
	<table class="adminlist" cellspacing="1">
		<thead>
			<tr>
				<th class="title" width="3%"><input type="checkbox" name="toggle" value="" onclick="checkAll(<?php echo $this->courseCount; ?>);" /></th>
				<th class="title" width="3%" align="center"><?php echo JText::_( 'id' ); ?></th>
				<th class="title" width="17%" align="center"><?php echo JText::_( 'Title' ); ?></th>
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
				<td colspan="14">
					<?php echo $this->pagination->getListFooter(); ?>
				</td>
			</tr>
		</tfoot>
		<tbody>
		<?php foreach( $this->courses as $this->curIndex=>$this->course ): ?>
			<?php echo $this->loadTemplate( 'course_row' ); ?>
		<?php endforeach; ?>
		</tbody>
	</table>
	<?php else: ?>
		<?php echo JText::_( 'There are no courses to display' ); ?>
	<?php endif; ?>
	<input type="hidden" name="option" value="com_arc_course" />
	<input type="hidden" name="view" value="courses" />
	<input type="hidden" name="task" value="" />
	<input type="hidden" name="boxchecked" value="0" />
</form>