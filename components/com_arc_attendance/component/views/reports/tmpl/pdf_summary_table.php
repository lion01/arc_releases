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

$this->spacerColWidth = 14;
$this->chartColWidth = ($this->usableWidth / 2);
$this->tableColWidth = ($this->usableWidth / 2) - $this->spacerColWidth;
$this->imageFile = $this->model->getImageFile($this->imageUrl); 
$imageFile = str_replace( JPATH_SITE.'/', JURI::base(), $this->imageFile );
?>
<table cellpadding="2" cellspacing="0">
	<tr>
		<td width="<?php echo $this->usableWidth; ?>" colspan="3" align="center"><h3><?php echo $this->tableTitle; ?></h3></td>
	</tr>
	<tr>
		<td align="center" width="<?php echo $this->chartColWidth?>"><img src="<?php echo $imageFile; ?>" width="200" height="170" /></td>
		<td width="<?php echo $this->spacerColWidth?>">&nbsp;</td>
		<td width="<?php echo $this->tableColWidth?>"><?php echo $this->setTableColWidths($this->curTable, 'chart', false); ?></td>
	</tr>
</table>