<?php
/**
 * @package     Arc
 * @subpackage  Message
 * @copyright   Copyright (C) 2005 Punnet. All rights reserved. See COPYRIGHT_ARC.txt
 * @license     http://www.gnu.org/licenses/gpl.html GNU/GPL. See LICENSE_ARC.txt
 * Arc is free software: you can redistribute it and/or modify
 * it under the terms of version 3 of the GNU General Public License
 * as published by the Free Software Foundation.
 * The disclaimer of warranty as stated in the GPL applies to this program
 */

defined( '_JEXEC' ) or die( 'Restricted access' );

?>
<div class="tweet">
	<div class="content"><?php echo $this->tweet->text ?></div>
	<div class="meta">
	 <b><?php echo $this->twit; ?></b>
	 via <?php echo $this->tweet->source; ?>
	 on <?php echo date( 'Y-m-d H:i:s', strtotime( $this->tweet->created_at ) ); ?></div>
</div>