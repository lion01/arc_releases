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
<div class="biography">
<p><?php echo nl2br(htmlspecialchars($this->text)); ?></p>
</div>
<div style="float: right">
	<a class="panel_modal" target="blank" rel="{handler: 'iframe'}" href="<?php echo $this->link; ?>&pId=<?php echo $this->profile->getId(); ?>&task=edit&scope=biography&tmpl=component">Edit</a>
</div>