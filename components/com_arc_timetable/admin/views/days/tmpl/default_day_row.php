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
<tr class="<?php echo 'row'.$this->dayIndex % 2; ?>">
	<td align="center">
		<input type="checkbox" id="cb<?php echo $this->dayIndex; ?>" name="eid[<?php echo $this->dayIndex; ?>]" onclick="isChecked(this.checked);" />
		<input type="hidden" name="dayId[<?php echo $this->dayIndex; ?>]" value="<?php echo $this->day->getId(); ?>" />
	</td>
	<td align="center"><?php echo $this->day->getDatum( 'pattern' );     ?></td>
	<td align="center"><?php echo $this->day->getDatum( 'day_type' );    ?></td>
	<td align="center"><?php echo count( $this->day->getSections() ); ?> - <a href="index.php?option=com_arc_timetable&view=days&task=displaySections&day=<?php echo $this->dayIndex; ?>">Edit</a></td>
	<td align="center"><?php echo $this->day->getStart();  ?></td>
	<td align="center"><?php echo $this->day->getEnd();    ?></td>
</tr>
