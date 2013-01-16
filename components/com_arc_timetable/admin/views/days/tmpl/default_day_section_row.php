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
<tr class="<?php echo 'row'.$this->sectionIndex % 2; ?>">
	<td align="center">
		<input type="checkbox" id="cb<?php echo $this->sectionIndex; ?>" name="eid[<?php echo $this->sectionIndex; ?>]" onclick="isChecked(this.checked);" />
		<input type="hidden" name="sectionId[<?php echo $this->sectionIndex; ?>]" value="<?php echo $this->section->getId(); ?>" />
	</td>
	<td align="center"><?php echo $this->section->getDatum( 'pattern' );     ?></td>
	<td align="center"><?php echo $this->section->getDatum( 'day_type' );    ?></td>
	<td align="center"><?php echo $this->section->getDatum( 'day_section' ); ?></td>
	<td align="center"><?php echo $this->section->getDatum( 'day_section_short' ); ?></td>
	<td align="center"><?php echo $this->section->getDatum( 'start_time' );  ?></td>
	<td align="center"><?php echo $this->section->getDatum( 'end_time' );    ?></td>
	<td align="center"><?php echo $this->section->getDatum( 'has_teacher' )  ?></td>
	<td align="center"><?php echo $this->section->getDatum( 'taught' );      ?></td>
	<td align="center"><?php echo $this->section->getDatum( 'registered' );  ?></td>
	<td align="center">
		<a href="javascript:void(0);" onclick="return listItemTask('cb<?php echo $this->sectionIndex; ?>','toggleStatutory')" title="<?php echo ( $this->section->getDatum( 'statutory' ) ? 'Yes' : 'No' ); ?>">
		<img src="images/<?php echo ( $this->section->getDatum( 'statutory' ) ? 'tick.png' : 'publish_x.png' );?>"
			width="16" height="16" border="0"
			alt="<?php echo ( $this->section->getDatum( 'statutory' ) ) ? JText::_( 'Yes' ) : JText::_( 'No' );?>" />
		</a>
	<td align="center"><?php echo $this->section->getDatum( 'valid_from' );  ?></td>
	<td align="center"><?php echo $this->section->getDatum( 'valid_to' );    ?></td>
</tr>
