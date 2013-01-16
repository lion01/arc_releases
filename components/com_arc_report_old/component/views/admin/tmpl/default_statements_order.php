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
<?php
$this->bank = &$this->field->getStatementBank();
$statements = $this->bank->getStatements();
?>
<form id="<?php echo $this->formName; ?>" name="<?php echo $this->formName; ?>" method="post" action="<?php echo $this->link; ?>">
<table>
<input type="submit" name="task" value="Save Order" />
<input type="button" name="close" value="Close" onclick="javascript:closeAndRefresh();" />
<?php
if( is_array($statements) ) :
	$i = 1;
	foreach( $statements as $k=>$v ) : ?>
		<tr>
			<td><input name="order[<?php echo $k; ?>]" type="text" size="4" value="<?php echo $i++; ?>" />
				  <input name="oldOrder[<?php echo $k; ?>]" type="hidden" value="<?php echo $v->order; ?>" /></td>
			<td style="background: <?php echo htmlspecialchars($v->color); ?>;"><?php echo $v->text; ?></td>
		</tr>
	<?php endforeach;
endif; ?>
</table>
<input type="submit" name="task" value="Save Order" />
<input type="hidden" name="field" value="<?php echo $this->field->getName(); ?>" />
<input type="button" name="close" value="Close" onclick="javascript:closeAndRefresh();" />
</form>