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
?>
<script>
function closeAndRefresh()
{
	window.parent.location.reload();
	window.top.setTimeout('window.parent.document.getElementById(\'sbox-window\').close()', 700);
	window.parent.document.getElementById('sbox-window').close();
}
</script>
<h3><?php echo $this->groupName; ?>: Select statement banks to import</h3>
<br />
<form enctype="multipart/form-data" id="<?php echo $this->formName; ?>" name="<?php echo $this->formName; ?>" method="post" action="<?php echo $this->link; ?>&scope=importStatements">
<table class="nopad">
<?php
foreach( $this->fields as $k=>$v ) :
	$this->field = $this->fields[$k];
	$fieldName = $this->field->getName();
?>
	<tr>
		<td>
			<input type="checkbox" name="fields[<?php echo $fieldName; ?>]" <?php echo (array_key_exists( $fieldName, $this->importFields ) ? 'checked="checked"' : ''); ?> />
		</td>
		<td>
			<?php echo $this->field->getTitle(); ?>
		</td>
	</tr>
<?php endforeach; ?>
</table>
<input type="hidden" name="MAX_FILE_SIZE" value="2000000" />
<input type="file" name="filename" />
<br /><br />
<input type="submit" name="upload" value="Upload" />
<input type="button" name="close" value="Close" onclick="javascript:closeAndRefresh();" />
</form>