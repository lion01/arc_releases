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
?>
<br />
<form action="<?php echo $this->link; ?>" method="post" name="addhoc">
<table class="register">
<tr>
<td><h4>Add children</h4></td>
<td><h4>List of Current Pupils in the class (<?php echo $this->courses[0]->fullname; ?>):-</h4></td>
</tr>
	<tr><td><h6>Hold Ctrl to select multiple</h6></td></tr>
	<tr>
	<?php
		foreach ($this->allPupils as $k=>$v) {
			$this->allPupils[$k]->name = $v->surname.' '.$v->firstname;
		}
	?>
	<td><?php echo JHTML::_('select.genericList', $this->allPupils, 'adhocPupils['.$this->regId.'][]', 'multiple="multiple" class="multi_large" size="10"', 'id', 'name')?></td>
	
	<td valign="top">
	<?php foreach($this->pupils as $curPupil) :?>
		<?php echo $curPupil->surname.', '.$curPupil->firstname; ?><br />
	<?php endforeach; ?>
	
	<br /><h4>Adhoc pupils</h4>
	<?php foreach($this->adhocPupils as $curPupil) : ?>
		<?php echo $curPupil->surname.', '.$curPupil->firstname; ?><br />
		<input type="hidden" name="adhocPupils[<?php echo $this->regId; ?>][]" value="<?php echo $curPupil->id; ?>" />
	<?php endforeach; ?>
	
	</td>
	</tr>
<tr><td>
	<input type="submit" name="task" value="Add Pupils" /></td></tr>
</table>
</form>
