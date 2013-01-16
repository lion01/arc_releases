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

//working out selected property 
?>
<h1>Edit Merge Field</h1>
<form action="index.php" method="post" name="adminForm">
	<table class="paramlist admintable" width="100%" cellspacing="1">
		<tr>
			<td class="paramlist_key">Id: </td>
			<td class="paramlist_value"><input type="text" id="_id" name="_id" value="<?php echo $this->mergefield->id; ?>" disabled/></td>
		</tr>
		<tr>
			<td class="paramlist_key">Word: </td>
			<td><input type="text" name="word" id="word" value="<?php echo $this->mergefield->word; ?>" style="width: 8em;" />
			</td>
		</tr>
		<tr>
			<td class="paramlist_key">Male Version: </td>
			<td><input type="text" name="male" id="male" value="<?php echo $this->mergefield->male; ?>" style="width: 8em;" />
					e.g. him
			</td>
		</tr>
		<tr>
			<td class="paramlist_key">Female Version: </td>
			<td><input type="text" name="female" id="female" value="<?php echo $this->mergefield->female; ?>" style="width: 8em;" />
					e.g. her
			</td>
		</tr>
		<tr>
			<td class="paramlist_key">Neuter: </td>
			<td><input type="text" name="neuter" id="neuter" value="<?php echo $this->mergefield->neuter; ?>" style="width: 8em;" />
					e.g. themself
			</td>
		</tr>
		<tr>
			<td class="paramlist_key">Property: </td>
			<td><select name="property" id="property" style="width: 8em;" />
						<?php if($this->mergefield->property != '') {
							echo '<option value="'.$this->mergefield->property.'">'.$this->mergefield->property.'</option>';
						} ?>
						<option value=""></option>
						<option value="name" >name</option>
						<option value="subject">subject</option>
						<option value="grade">grade</option>
						<option value="clp">clp</option>
						<option value="clp2">clp2</option>
					</select>
				&nbsp;<input type="checkbox" name="jsconvert" id="jsconvert" onclick="switchCase();" /> Switch Case
			</td>
		</tr>
	</table>
<input type="hidden" name="option" value="com_arc_report" />
<input type="hidden" name="view" value="mergefields" />
<input type="hidden" name="task" value="" />
<input type="hidden" id="id" name="id" value="<?php echo $this->mergefield->id; ?>" />
</form>
