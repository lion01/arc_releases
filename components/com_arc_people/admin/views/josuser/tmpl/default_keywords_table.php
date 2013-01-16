<?php
/**
 * @package     Arc
 * @subpackage  People
 * @copyright   Copyright (C) 2005 Punnet. All rights reserved. See COPYRIGHT_ARC.txt
 * @license     http://www.gnu.org/licenses/gpl.html GNU/GPL. See LICENSE_ARC.txt
 * Arc is free software: you can redistribute it and/or modify
 * it under the terms of version 3 of the GNU General Public License
 * as published by the Free Software Foundation.
 * The disclaimer of warranty as stated in the GPL applies to this program
 */

defined( '_JEXEC' ) or die( 'Restricted access' );
?>
<fieldset class="adminform_thinfieldset" style="vertical-align: top;">
	<legend><?php echo JText::_( 'Keywords Legend' );?></legend>
	<table class="adminlist" cellspacing="1">
	<thead>
		<tr>
			<th>Information</th>
			<th>Example</th>
			<th>Keyword</th>
		</tr>
	</thead>
		<tbody>
			<tr class="row0">
				<td rowspan="5">
					Firstname<br />
					<input type="text" id="firstname_in" size="25" value="John" /> 
				</td>
				<td>Firstname</td>
				<td>[[uc_firstname]]</td>
			</tr>
			<tr class="row0">
				<td>firstname</td>
				<td>[[lc_firstname]]</td>
			</tr>
			<tr class="row0">
				<td>as found</td>
				<td>[[as_firstname]]</td>
			</tr>
			<tr class="row0">
				<td>Initial</td>
				<td>[[uc_firstinit]]</td>
			</tr>
			<tr class="row0">
				<td>initial</td>
				<td>[[lc_firstinit]]</td>
			</tr>
			<tr class="row1">
				<td rowspan="5">
					Middlename<br />
					<input type="text" id="middlename_in" size="25" value="Tiberius" /> 
				</td>
				<td>Middlename</td>
				<td>[[uc_middlename]]</td>
			</tr>
			<tr class="row1">
				<td>middlename</td>
				<td>[[lc_middlename]]</td>
			</tr>
			<tr class="row1">
				<td>as found</td>
				<td>[[as_middlename]]</td>
			</tr>
			<tr class="row1">
				<td>Initial</td>
				<td>[[uc_middleinit]]</td>
			</tr>
			<tr class="row1">
				<td>initial</td>
				<td>[[lc_middleinit]]</td>
			</tr>
			<tr class="row0">
				<td rowspan="5">
					Surname<br />
					<input type="text" id="surname_in" size="25" value="Doe" /> 
				</td>
				<td>Surname</td>
				<td>[[uc_surname]]</td>
			</tr>
			<tr class="row0">
				<td>surname</td>
				<td>[[lc_surname]]</td>
			</tr>
			<tr class="row0">
				<td>as found</td>
				<td>[[as_surname]]</td>
			</tr>
			<tr class="row0">
				<td>Initial</td>
				<td>[[uc_surinit]]</td>
			</tr>
			<tr class="row0">
				<td>initial</td>
				<td>[[lc_surinit]]</td>
			</tr>
			<tr class="row1">
				<td class="title">
					E-mail<br />
					<input type="text" id="email_in" size="25" value="johnny@example.com" />
				</td> 
				<td>as found</td>
				<td>[[email]]</td>
			</tr>
			<tr class="row0">
				<td class="title">Current domain</td>
				<td id="cur_domain"><?php echo $this->curDomain; ?></td>
				<td>[[domain]]</td>
			</tr>
		</tbody>
	</table>
</fieldset>