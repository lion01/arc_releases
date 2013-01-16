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

while( $this->event = $this->get( 'NextEvent' ) ) {
	$deps = array( 'report.cycle'=>$this->event->getDatum( 'cycle_id' ) );
	$link = ApotheosisLibAcl::getUserLinkAllowed( $this->event->getDatum( 'target_action' ), $deps );
	?>
	<tr>
	<td>
	<?php if( $link ) { echo '<a href="'.$link.'">'; } ?>
	<?php echo htmlspecialchars( $this->event->getDatum( 'title' ) ); ?></td>
	<?php if( $link ) { echo '</a>'; } ?>
	<td>&nbsp;</td>
	<td><?php echo $this->event->getDueDays();?> days (on <?php echo htmlspecialchars( $this->event->getDueDate() ); ?>)</td>
	</tr>
	<?php
}
?>