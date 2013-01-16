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
<tr class="<?php echo 'row'.($this->enrolIndex % 2).( $this->enrolment->isCurrent() ? '' : ' old' ); ?>">
	<td align="center">
		<input type="checkbox" id="cb<?php echo $this->enrolIndex; ?>" name="eid[<?php echo $this->enrolIndex; ?>]" onclick="isChecked(this.checked);" />
		<input type="hidden" name="enrolId[<?php echo $this->enrolIndex; ?>]" value="<?php echo $this->enrolment->getId(); ?>" />
	</td>
	<td align="left"><?php echo ApotheosisData::_( 'course.name', $this->enrolment->getDatum( 'group_id' ) ); ?></td>
	<td align="left"><?php echo ApotheosisData::_( 'people.displayname', $this->enrolment->getDatum( 'person_id' ) ); ?></td>
	<td align="left"><?php echo ApotheosisLibAcl::getRoleName( $this->enrolment->getDatum( 'role' ) ); ?></td>
	<td align="center"><?php echo $this->enrolment->getDatum( 'valid_from' ); ?></td>
	<td align="center"><?php echo $this->enrolment->getDatum( 'valid_to' );   ?></td>
</tr>