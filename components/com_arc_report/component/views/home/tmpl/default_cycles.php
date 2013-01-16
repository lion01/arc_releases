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

$this->nav->displayNav();
?>
<div id="arc_main_narrow">
<table>

<?php
while( $this->cycle = $this->get( 'NextCycle' ) ) {
	?>
	<tr><td><h2><?php echo htmlspecialchars( $this->cycle->getDatum( 'name' ) ); ?></h2></td><td>&nbsp;</td></tr>
	<?php
	echo $this->loadTemplate( 'cycle_events' );
}
?>
</table>
</div>