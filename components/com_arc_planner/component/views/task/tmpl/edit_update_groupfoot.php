<?php
/**
 * @package     Arc
 * @subpackage  Planner
 * @copyright   Copyright (C) 2005 Punnet. All rights reserved. See COPYRIGHT_ARC.txt
 * @license     http://www.gnu.org/licenses/gpl.html GNU/GPL. See LICENSE_ARC.txt
 * Arc is free software: you can redistribute it and/or modify
 * it under the terms of version 3 of the GNU General Public License
 * as published by the Free Software Foundation.
 * The disclaimer of warranty as stated in the GPL applies to this program
 */

defined( '_JEXEC' ) or die( 'Restricted access' );
?>
<div class="nolabel">
	<?php echo $this->labels['update_done_intro']; ?>
</div>
<?php if( $this->taskIsLeaf && $this->formComplete ) : ?>
	<?php $prog = ( is_null($this->update) ? 0 : $prog = $this->update->getProgress() ); ?>
	<div>
		<label for="<?php echo $this->inputNum; ?>"><?php echo $this->labels['update_done']; ?></label>
		<input id="<?php echo $this->inputNum++; ?>" class="complete" type="checkbox" name="updates<?php echo '['.$this->taskId.']['.$this->groupId.']['.$this->updateId.']'; ?>[complete]" <?php echo ( ($prog == 100) ? 'checked="checked"' : '' ).'"'.($this->formUpdateEdit ? '' : 'disabled="disabled"'); ?> />
	</div>
<?php endif; ?>
<?php if( ($this->formCount == 1) || ($this->formCount % $this->formSaveFreq) == 0 ) : ?>
	<div class="nolabel">
		<?php echo $this->labels['update_intro']; ?>
	</div>
	<div>
		<label for="<?php echo $this->inputNum; ?>">&nbsp;</label>
		<input id="<?php echo $this->inputNum++; ?>" class="submit" type="submit" value="<?php echo $this->labels['update_save']; ?>">
	</div>
<?php endif; ?>
<?php $this->formCount++; ?>
