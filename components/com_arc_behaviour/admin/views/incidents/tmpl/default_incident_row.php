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

$incObj = $this->incident['obj'];
$tmp = new stdClass();
$tmp->published = (bool)$incObj->getHasText();
?>
<tr class="<?php echo 'row'.(int)($this->oddRow = !$this->oddRow); ?>">
	<td align="center">
		<input type="checkbox" id="cb<?php echo $this->incidentIndex; ?>" name="eid[<?php echo $this->incidentIndex; ?>]" onclick="isChecked(this.checked);" />
	</td>
	<td align="left"><?php echo str_repeat( '.&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;', $this->incident['level'] ).( empty($this->incident['level']) ? '' : '<sup>|_</sup>&nbsp;').$incObj->getLabel(); ?></td>
	<td align="center"><?php echo ( $incObj->hasOwnScore() ? $incObj->getScore() : '('.$incObj->getScore().')' ); ?></td>
	<td align="center"><?php $t = ApotheosisData::_( 'message.tag', $incObj->getTag() ); echo $t->getLabel(); ?></td>
	<td align="center"><?php echo JHTML::_('grid.published', $tmp, $this->incidentIndex ); ?></td>
</tr>
