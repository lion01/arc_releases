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

$user = ApotheosisLib::getUser();
$name = ApotheosisData::_( 'people.displayName', $user->person_id, 'teacher' );
$date = ApotheosisLibParent::arcDateTime();
?>
<table width="100%" cellpadding="2" cellspacing="0" border="0">
	<tr>
		<td width="167"><h2><?php echo $name; ?></h2></td>
		<td width="166" align="center"><h2>Attendance Reports</h2></td>
		<td width="167" align="right"><h2><?php echo $date; ?></h2></td>
	</tr>
</table>