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
?>
<form action="index.php?option=com_installer&amp;task=manage&amp;type=components" method="post" name="adminForm">
	<?php if( count($this->items) ) : ?>
	<table class="adminlist">
		<thead>
			<tr>
				<th class="title" width="5%" align="center"><?php echo JText::_( 'ID' ); ?></th>
				<th class="title" width="20%" align="center"><?php echo JText::_( 'Title' ); ?></th>
				<th class="title" width="75%" align="center"><?php echo JText::_( 'Description' ); ?></th>
			</tr>
		</thead>
		<tbody>
		<?php for( $i=0, $n=count($this->items), $rc=0; $i < $n; $i++, $rc = 1 - $rc ) : ?>
			<?php
				$this->loadItem( $i );
				echo $this->loadTemplate( 'item' );
			?>
		<?php endfor; ?>
		</tbody>
	</table>
	<?php else : ?>
		<?php echo JText::_( 'There are no custom components installed' ); ?>
	<?php endif; ?>

	<input type="hidden" name="task" value="manage" />
	<input type="hidden" name="boxchecked" value="0" />
	<input type="hidden" name="option" value="com_installer" />
	<input type="hidden" name="type" value="components" />
</form>