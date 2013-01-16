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
<h3><?php echo $this->groupName.($this->enabled ? ' Set a twin for this group' : ' Pre-set twinnings'); ?></h3>

<form method="post" onSubmit="javascript:treeToId( tree, $('groups<?php echo $this->group; ?>') )" action="<?php echo $this->link; ?>">
<input type="checkbox" id="inherit<?php echo $this->group; ?>" name="inherit"<?php echo ((!isset($this->twin) || is_null($this->twin)) ? '' : ' checked="checked"'); ?> />
<label for="inherit<?php echo $this->group; ?>">Share settings with a twin?</label><br />

<div style="height:200px; background: #ccf0f0; border: solid 1px black; overflow: auto;">
	<div id="groups<?php echo $this->group; ?>_div"><ul id="groups<?php echo $this->group; ?>_list"></ul></div>
</div>
<?php JHTML::_( 'groups.grouptree', false, 'groups'.$this->group.'_div', JHTML::_('groups.nodelink', $this->nodeLink), false ); ?>

<input type="hidden" id="groups<?php echo $this->group; ?>" name="groups<?php echo $this->group; ?>" value="" />

<?php if( $this->enabled ) : ?>
	<input type="hidden" name="task" value="SetTwin" />
	<input type="hidden" name="focus" value="<?php echo $this->group; ?>" />
	<input type="submit" name="submit" value="Save" />
<?php endif; ?>
