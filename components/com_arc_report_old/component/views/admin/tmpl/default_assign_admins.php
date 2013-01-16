<?php
/**
 * @package     Arc
 * @subpackage  Report
 * @copyright   Copyright (C) 2005 Punnet. All rights reserved. See COPYRIGHT_ARC.txt
 * @license     http://www.gnu.org/licenses/gpl.html GNU/GPL. See LICENSE_ARC.txt
 * Arc is free software: you can redistribute it and/or modify
 * it under the terms of version 3 of the GNU General Public License
 * as published by the Free Software Foundation.
 * The disclaimer of warranty as stated in the GPL applies to this program
 */

defined( '_JEXEC' ) or die( 'Restricted access' );
?>

<h3><?php echo $this->groupName.': '.($this->enabled ? 'Assign administrators' : 'Higher administrators'); ?></h3>

<?php if( $this->enabled ) : ?>
<form method="post" action="<?php echo $this->link; ?>">
<table>
	<tr>
		<td>Current extra administrators:</td>
		<td>&nbsp;</td>
		<td>Potential extra administrators:</td>
	</tr>
	<tr>
		<td>
			<?php	echo JHTML::_('select.genericList', $this->admins, 'admins[]', 'multiple="multiple"', 'id', 'displayname'); ?>
		</td>
		<td>
			<input type="submit" name="task" value="<< Add" />
			<br />
			<input type="submit" name="task" value="Remove >>" />
		</td>
		<td>
			<?php echo JHTML::_('select.genericList', $this->adminCandidates, 'candidates[]', 'multiple="multiple"', 'id', 'displayname'); ?>
		</td>
	</tr>
</table>
</form>

<?php else : ?>
<p>Extra administrators: <?php echo implode(', ', $this->adminsNames); ?></p>
<?php endif; ?>