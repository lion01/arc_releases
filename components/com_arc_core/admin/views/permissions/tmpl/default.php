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

ob_start();
?>
			<tr>
				<th class="title" width="2%"><?php echo JText::_( 'Id' ); ?></th>
				<th class="title" width="2%"><?php echo JText::_( 'Menu id' ); ?></th>
				<th class="title" width="5%"><?php echo JText::_( 'Menu Text' ); ?></th>
				<th class="title" width="5%"><?php echo JText::_( 'Description' ); ?></th>
				<?php 
				$parents = array();
				$depth = -1;
				foreach( $this->roles as $role ) {
					if( !array_key_exists($role->parent, $parents) ) {
						$parents[$role->parent] = $role->parent;
						$depth++;
					}
					else {
						while( (($tmp = end($parents)) !== $role->parent) && ($tmp !== false) ) {
							array_pop($parents);
							$depth--;
						}
					}
					
					echo '<th class="title" width="5%" align="center">'.str_repeat('.<br />', $depth).JText::_( $role->role ).'</th>';
				}
				?>
			</tr>
<?php 
$this->_headings = ob_get_clean();?>

<form action="index.php" method="post" name="adminForm">
	<?php if( count($this->actions) ) : ?>
	<table class="adminlist" cellspacing="1">
		<thead><?php echo $this->_headings; ?></thead>
		<tbody><?php echo $this->loadTemplate( 'permissions' ); ?></tbody>
	</table>
	<?php else : ?>
		<?php echo JText::_( 'There are no actions for which to define permissions' ); ?>
	<?php endif; ?>

	<input type="hidden" name="option" value="com_arc_core" />
	<input type="hidden" name="view" value="permissions" />
	<input type="hidden" name="boxchecked" value="0" />
	<input type="hidden" name="task" value="toggleAllowed" />
</form>