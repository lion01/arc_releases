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

$requirements = $this->model->getRequirements( $this->sheetId );
$startDate = date( 'd/m/Y', strtotime($requirements['start_date']) );
$endDate = date( 'd/m/Y', strtotime($requirements['end_date']) );
?>
<table cellpadding="2" cellspacing="0">
	<tr>
		<td align="center"><h2>Registration Certificate for <?php echo ApotheosisData::_( 'people.displayname', reset($this->model->getPupilList($this->sheetId)), 'person' ); ?> (<?php echo $startDate; ?> - <?php echo $endDate; ?>)</h2></td>
	</tr>
</table>