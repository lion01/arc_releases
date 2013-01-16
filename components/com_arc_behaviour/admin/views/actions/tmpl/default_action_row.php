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

$this->action;
$tmp = new stdClass();
$tmp->published = (bool)$this->action->getHasText();
?>
<tr class="<?php echo 'row'.(int)($this->oddRow = !$this->oddRow); ?>">
	<td align="center">
		<input type="checkbox" id="cb<?php echo $this->actionIndex; ?>" name="eid[<?php echo $this->actionIndex; ?>]" onclick="isChecked(this.checked);" />
	</td>
	<td align="left"><?php echo $this->action->getLabel(); ?></td>
	<td align="center"><?php echo $this->action->getScore(); ?></td>
	<td align="center"><?php echo JHTML::_('grid.published', $tmp, $this->actionIndex ); ?></td>
	<td align="center"><?php echo implode( ', ', $this->action->getIncidentLabels() ); ?></td>
</tr>
