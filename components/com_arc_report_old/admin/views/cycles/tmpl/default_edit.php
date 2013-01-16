<?php
/**
 * @package     Arc
 * @subpackage  Report
 * @copyright   Copyright (C) 2005 Punnet. All rights reserved. See COPYRIGHT_ARC.txt
 * @license     http://www.gnu.org/licenses/gpl.html GNU/GPL. See LICENSE_ARC.txt
 * Arc is free software: you can redistribute it and/or modify
 * it under the terms of version 3 of the GNU General Public License
 * as published by the Free Software Foundation.
 * The disclaimer of warranty as stated in the GPL applies to this program
 */

defined( '_JEXEC' ) or die( 'Restricted access' );

$yearGroupList[]	= '';
$yearGroupList		= array_merge( $yearGroupList, $this->yearGroups );
$lists['year']		= JHTML::_('select.genericlist', $yearGroupList, 'year', '','year', 'year', $this->cycle->year_group );
$lists['recheck'] = JHTML::_('select.genericlist', $this->reCheck, 'recheck', '','recheck', 'recheck', $this->cycle->rechecker );
$multiple         = JHTML::_('select.booleanlist', 'allow_multiple', '', $this->cycle->allow_multiple);
?>
<h1>Edit Report Cycle</h1>
<form action="index.php" method="post" name="adminForm">
	<table class="paramlist admintable" width="100%" cellspacing="1">
		<tr>
			<td class="paramlist_key">Id: </td>
			<td class="paramlist_value"><input type="text" id="_id" name="_id" value="<?php echo $this->cycle->id; ?>" disabled/></td>
		</tr>
		<tr>
			<td class="paramlist_key">Valid From: </td>
			<td><?php echo JHTML::_( 'calendar', date('Y-m-d', strtotime($this->cycle->valid_from)), 'valid_from', 'valid_from' ); ?></td>
		</tr>
		<tr>
			<td class="paramlist_key">Valid To: </td>
			<td><?php echo JHTML::_( 'calendar', date('Y-m-d', strtotime($this->cycle->valid_to)), 'valid_to', 'valid_to' ); ?></td>
		</tr>
		<tr>
			<td class="paramlist_key">YearGroup: </td>
			<td class="paramlist_value"><?php echo $lists['year']; ?> </td>
		</tr>
		<tr>
			<td class="paramlist_key">Allow Multiple: </td>
			<td class="paramlist_value"><?php echo $multiple; ?> </td>
		</tr>
		<tr>
			<td class="paramlist_key">Re-checking done by: </td>
			<td class="paramlist_value"><?php echo $lists['recheck']; ?> </td>
		</tr>
	</table>
<input type="hidden" name="option" value="com_arc_report" />
<input type="hidden" name="view" value="cycles" />
<input type="hidden" name="task" value="" />
<input type="hidden" id="id" name="id" value="<?php echo $this->cycle->id; ?>" />
</form>