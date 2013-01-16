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

$pattern = $this->getPattern( $this->pi->getDatum( 'pattern' ) );
$s = $this->pi->getDatum( 'start_index' );
$format = $pattern->getFormat();
if( $s < 0 || $s >= strlen($format) ) {
	$format = '( err '.$format.')';
}
else {
	$format = '( '.substr( $format, 0, $s ).'<span style="border: 1px solid green;">'.substr( $format, $s, 1 ).'</span>'.substr( $format, $s + 1 ).' )';
}
?>
<tr class="<?php echo 'row'.$this->piIndex % 2; ?>">
	<td align="center">
		<input type="checkbox" id="cb<?php echo $this->piIndex; ?>" name="eid[<?php echo $this->piIndex; ?>]" onclick="isChecked(this.checked);" />
		<input type="hidden" name="piId[<?php echo $this->piIndex; ?>]" value="<?php echo $this->pi->getId(); ?>" />
	</td>
	<td align="center"><?php echo $this->pi->getDatum( 'pattern' );           ?></td>
	<td align="center"><?php echo $this->pi->getDatum( 'start' );             ?></td>
	<td align="center"><?php echo $this->pi->getDatum( 'end' );               ?></td>
	<td align="center"><?php echo $s.' '.$format;                             ?></td>
	<td align="center"><?php echo $this->pi->getDatum( 'description' );       ?></td>
	<td align="center"><?php echo $this->pi->getDatum( 'description_short' ); ?></td>
	<td align="center"><?php echo $this->pi->getDatum( 'holiday' );           ?></td>
</tr>