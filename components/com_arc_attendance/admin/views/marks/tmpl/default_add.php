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

JHTML::_('behavior.tooltip');
?>

<script language="javascript" type="text/javascript">
	function submitbutton(pressbutton) {
		var form = document.adminForm;
		if (pressbutton == 'cancel') {
			submitform( pressbutton );
			return;
		}
		
		// do field validation
		if (form.schMeaning.value == ""){
			alert( "<?php echo JText::_( 'Code must have a school meaning', true ); ?>" );
		}
		else if (form.statMeaning.value == "0"){
			alert( "<?php echo JText::_( 'Code must have a statistical meaning', true ); ?>" );
		}
		else if (form.physMeaning.value == ""){
			alert( "<?php echo JText::_( 'Code must have a physical meaning', true ); ?>" );
		}
		else {
			submitform( pressbutton );
		}
	}
</script>

<style type="text/css">
	table.paramlist td.paramlist_key {
		width: 92px;
		text-align: left;
		height: 30px;
	}
</style>

<form action="<?php echo JRoute::_($this->request_url) ?>" method="post" name="adminForm" id="adminForm">
<div class="col50">
	<fieldset class="adminform">
		<legend><?php echo JText::_( 'Details' ); ?></legend>

		<table class="admintable">
		<tr>
			<td width="100" align="right" class="key">
				<label for="title">
					<?php echo JText::_( 'Code' ); ?>:
				</label>
			</td>
			<td>
				<input class="text_area" type="text" name="newCode" id="newCode" size="32" maxlength="250" value="<?php echo $this->item->code;?>" />
			</td>
		</tr>
		<tr>
			<td width="100" align="right" class="key">
				<label for="title">
					<?php echo JText::_( 'School Meaning' ); ?>:
				</label>
			</td>
			<td>
				<?php echo JHTML::_('select.genericList', $this->meanings['school'], 'schMeaning', '', 'id', 'school_meaning' ); ?>
			</td>
		</tr>
		<tr>
			<td width="100" align="right" class="key">
				<label for="title">
					<?php echo JText::_( 'Statistical Meaning' ); ?>:
				</label>
			</td>
			<td>
				<?php echo JHTML::_('select.genericList', $this->meanings['statistical'], 'statMeaning', '', 'id', 'statistical_meaning' ); ?>
			</td>
		</tr>
		<tr>
			<td width="100" align="right" class="key">
				<label for="title">
					<?php echo JText::_( 'Physical Meaning' ); ?>:
				</label>
			</td>
			<td>
				<?php echo JHTML::_('select.genericList', $this->meanings['physical'], 'physMeaning', '', 'id', 'physical_meaning' ); ?>
			</td>
		</tr>
		<tr>
			<td width="100" align="right" class="key">
				<label for="title">
					<?php echo JText::_( 'Is Common' ); ?>:
				</label>
			</td>
			<td>
				<?php echo JHTML::_('select.booleanlist', 'is_common', '', $this->item->is_common, 'Yes', 'No'); ?>
			</td>
		</tr>
		<tr>
			<td width="100" align="right" class="key">
				<label for="title">
					<?php echo JText::_( 'Image Link' ); ?>:
				</label>
			</td>
			<td>
				<input class="text_area" type="text" name="image_link" id="image_link" size="32" maxlength="250" />
			</td>
		</tr>
		<tr>
			<td width="100" align="right" class="key">
				<label for="title">
					<?php echo JText::_( 'Type' ); ?>:
				</label>
			</td>
			<td>
				<input class="text_area" type="text" name="type" id="type" size="32" maxlength="25" />
			</td>
		</tr>
	</table>
	</fieldset>
</div>
<?php
/* left in for reference in case admin screens grow in complexity
<div class="col50">
	<fieldset class="adminform">
		<legend><?php echo JText::_( 'Meanings' ); ?></legend>

		<table class="admintable">
		<tr>
			<td>
				<input class="text_area" type="text" name="description" id="description"><?php echo $this->weblink->description; ?></textarea>
			</td>
		</tr>
		</table>
	</fieldset>
</div>

<div class="col50">
	<fieldset class="adminform">
		<legend><?php echo JText::_( 'Description' ); ?></legend>

		<table class="admintable">
		<tr>
			<td>
				<textarea class="text_area" cols="44" rows="9" name="description" id="description"><?php echo $this->weblink->description; ?></textarea>
			</td>
		</tr>
		</table>
	</fieldset>
</div>
// */
?>
<div class="clr"></div>
<input type="hidden" name="view" value="marks" />
<input type="hidden" name="task" value="save" />
<input type="hidden" name="option" value="com_arc_attendance" />
</form>