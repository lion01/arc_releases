<?php
/**
 * @package     Arc
 * @subpackage  Attendance
 * @copyright   Copyright (C) 2005 Punnet. All rights reserved. See COPYRIGHT_ARC.txt
 * @license     http://www.gnu.org/licenses/gpl.html GNU/GPL. See LICENSE_ARC.txt
 * Arc is free software: you can redistribute it and/or modify
 * it under the terms of version 3 of the GNU General Public License
 * as published by the Free Software Foundation.
 * The disclaimer of warranty as stated in the GPL applies to this program
 */

defined( '_JEXEC' ) or die( 'Restricted access' );

// keep first column width to sensible fraction of total width
$firstColFraction = ( 1 / 3 );

// derive width of first column
foreach( $this->strWidths['sheet'] as $strWidth ) {
	if( (($tmpFirstColWidth = (($strWidth * $this->scaleFactor) + 6)) <= ($this->usableWidth * $firstColFraction)) && ($tmpFirstColWidth > $this->firstColWidth) ) {
		// the + 6 includes cellpadding and some needed extra padding
		$this->firstColWidth = $tmpFirstColWidth;
	}
}
$this->strWidths['sheet'] = array();

// derive width of remaining columns
$markColWidth = ( $this->usableWidth - $this->firstColWidth ) / $this->periodRowCount;

// substitute column widths into prepared html
$this->periodRow = str_replace( '~markColWidth~', $markColWidth, $this->periodRow );
$this->dayRows = str_replace( '~firstColWidth~', $this->firstColWidth, $this->dayRows );
$this->dayRows = str_replace( '~markColWidth~', $markColWidth, $this->dayRows );
?>
<?php if( ($headRowString = implode(' | ', $this->headRowStrings)) != '' ) : ?>
<table cellpadding="2" cellspacing="0">
	<tr>
		<td><?php echo $headRowString; ?></td>
	</tr>
</table>
<?php endif; ?>
<table width="100%" cellpadding="2" cellspacing="0" border="1">
	<tr><td width="<?php echo $this->firstColWidth; ?>">&nbsp;</td><?php echo $this->periodRow; ?></tr>
</table>
<?php foreach( $this->dayRows as $j=>$marks ) : ?>
<table width="100%" cellpadding="2" cellspacing="0" border="1">
	<tr><?php echo $marks; ?></tr>
</table>
<?php endforeach; ?>