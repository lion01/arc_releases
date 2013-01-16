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

JHTML::_('behavior.modal'); ?>
<h3><?php echo $this->groupName.': '.($this->enabled ? 'Adjust statement banks' : 'Pre-set statement banks'); ?></h3>

<form id="<?php echo $this->formName; ?>" name="<?php echo $this->formName; ?>" method="post" action="<?php echo $this->link; ?>">
<script>
	var <?php echo $this->formName; ?> = document.getElementById( '<?php echo $this->formName; ?>' );
</script>
<?php
foreach( $this->fields as $k=>$v ) :
	$this->field = $this->fields[$k];
	$this->bank = &$this->field->getStatementBank();
	echo $this->loadTemplate( 'statement' );
endforeach;
if( $this->enabled ) :?>
	<input type="hidden" id="statement" name="statement" value="" />
	<input type="hidden" id="field" name="field" value="" />
	<input type="hidden" id="task"  name="task" value="" />
	<input type="hidden" id="scope" name="scope" value="" />
	<input type="hidden" id="retro" name="retro" value="" />
	<input type="submit" name="submitBtn" value="Save" />
	<a class="modal" rel="{handler: 'iframe', size: {x: 640, y: 480}}" href="<?php echo $this->link; ?>&scope=exportStatements">
		<input type="button" name="exportCsv" value="Export as CSV" /></a>
	<a class="modal" rel="{handler: 'iframe', size: {x: 640, y: 480}}" href="<?php echo $this->link; ?>&scope=importStatements">
		<input type="button" name="importCsv" value="Import a CSV" /></a>
	<br /><hr /><br />
<?php endif; ?>
</form>