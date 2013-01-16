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
<h3><?php echo $this->groupName.': '.($this->enabled ? 'Select a page style' : 'Pre-set page style'); ?></h3>

<form method="post" action="<?php echo $this->link; ?>">
<?php echo str_replace('<input', '<br /><input', JHTML::_('select.radiolist', $this->styles, 'layout'.$this->group, ($this->enabled ? '' : 'disabled="disabled"'), 'name', 'display', $this->style) ); ?>
<br />
<br />
<?php if( $this->enabled ) : ?>
	<input type="hidden" name="task" value="SetPageStyle" />
	<input type="submit" name="submit" value="Save" />
<?php endif; ?>
</form>