<?php
/**
 * @package     Arc
 * @subpackage  Timetable
 * @copyright   Copyright (C) 2005 Punnet. All rights reserved. See COPYRIGHT_ARC.txt
 * @license     http://www.gnu.org/licenses/gpl.html GNU/GPL. See LICENSE_ARC.txt
 * Arc is free software: you can redistribute it and/or modify
 * it under the terms of version 3 of the GNU General Public License
 * as published by the Free Software Foundation.
 * The disclaimer of warranty as stated in the GPL applies to this program
 */

defined( '_JEXEC' ) or die( 'Restricted access' );

?>
<tr class="<?php echo 'row'.$this->patternIndex % 2; ?>">
	<td align="center">
		<input type="checkbox" id="cb<?php echo $this->patternIndex; ?>" name="eid[<?php echo $this->patternIndex; ?>]" onclick="isChecked(this.checked);" />
		<input type="hidden" name="patternId[<?php echo $this->patternIndex; ?>]" value="<?php echo $this->pattern->getId(); ?>" />
	</td>
	<td align="center"><?php echo $this->pattern->getDatum( 'name' );       ?></td>
	<td align="center"><?php echo $this->pattern->getDatum( 'format' );     ?></td>
	<td align="center"><?php echo ApotheosisLibParent::isoDayToName( $this->pattern->getDatum( 'start_day' ) ); ?></td>
	<td align="center"><?php echo $this->pattern->getDatum( 'valid_from' ); ?></td>
	<td align="center"><?php echo $this->pattern->getDatum( 'valid_to' );   ?></td>
</tr>