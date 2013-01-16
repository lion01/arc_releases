<?php
/**
 * @package     Arc
 * @subpackage  People
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
				<input type="text" name="search" id="search" value="<?php echo $this->search; ?>" class="text_area" />
				<button id="admin_form_search_button"><?php echo JText::_( 'Search' ); ?></button>
				<button id="admin_form_reset_button"><?php echo JText::_( 'Reset' ); ?></button>
			</td>
		</tr>
	</table>

	<?php if( $this->peopleCount ): ?>
	<table class="adminlist" cellspacing="1">
		<thead>
			<tr>
				<th class="title" width="3%"><input type="checkbox" name="toggle" value="" onclick="checkAll(<?php echo $this->peopleCount; ?>);" /></th>
				<th class="title" width="5%" align="center"><?php echo JText::_( 'Arc ID' ); ?></th>
				<th class="title" width="5%" align="center"><?php echo JText::_( 'External ID' ); ?></th>
				<th class="title" width="5%" align="center"><?php echo JText::_( 'Joomla ID' ); ?></th>
				<th class="title" width="5%" align="center"><?php echo JText::_( 'UPN' ); ?></th>
				<th class="title" width="5%" align="center"><?php echo JText::_( 'Title' ); ?></th>
				<th class="title" width="10%" align="center"><?php echo JText::_( 'Firstname' ); ?></th>
				<th class="title" width="10%" align="center"><?php echo JText::_( 'Middlename(s)' ); ?></th>
				<th class="title" width="10%" align="center"><?php echo JText::_( 'Surname' ); ?></th>
				<th class="title" width="10%" align="center"><?php echo JText::_( 'Preferred Firstname' ); ?></th>
				<th class="title" width="10%" align="center"><?php echo JText::_( 'Preferred Surname' ); ?></th>
				<th class="title" width="5%" align="center"><?php echo JText::_( 'DoB' ); ?></th>
				<th class="title" width="3%" align="center"><?php echo JText::_( 'Gender' ); ?></th>
				<th class="title" width="14%" align="center"><?php echo JText::_( 'Email' ); ?></th>
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
		<?php foreach( $this->people as $this->curIndex=>$this->person ): ?>
			<?php echo $this->loadTemplate( 'person_row' ); ?>
		<?php endforeach; ?>
		</tbody>
	</table>
	<?php else: ?>
		<?php echo JText::_( 'There are no people to display' ); ?>
	<?php endif; ?>
	<input type="hidden" name="option" value="com_arc_people" />
	<input type="hidden" name="view" value="people" />
	<input type="hidden" name="task" value="" />
	<input type="hidden" name="boxchecked" value="0" />
</form>