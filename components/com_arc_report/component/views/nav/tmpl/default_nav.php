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

$this->view = 'writecheck';
JHTML::stylesheet( 'nav.css', $this->scriptPath );
?>

<div id="arc_nav">
	<ul>
<?php if( $link = ApotheosisLibAcl::getUserLinkAllowed( 'apoth_report_writecheck', array( 'view'=>'writecheck' ) ) ) : ?>
		<li <?php if( $this->view == 'writecheck' ) { echo 'class="current"'; } ?>><a href="<?php echo $link; ?>"><span id="nav_wc"    class="menu_icon">Write / Check</span></a></li>
<?php endif; ?>
<?php if( $link = ApotheosisLibAcl::getUserLinkAllowed( 'apoth_report_overview', array( 'view'=>'overview' ) ) ) : ?>
		<li <?php if( $this->view == 'overview'   ) { echo 'class="current"'; } ?>><a href="<?php echo $link; ?>"><span id="nav_over"  class="menu_icon">Overview</span></a></li>
<?php endif; ?>
<?php if( $link = ApotheosisLibAcl::getUserLinkAllowed( 'apoth_report_printshare', array( 'view'=>'printshare' ) ) ) : ?>
		<li <?php if( $this->view == 'printshare' ) { echo 'class="current"'; } ?>><a href="<?php echo $link; ?>"><span id="nav_print" class="menu_icon">Print / Share</span></a></li>
<?php endif; ?>
<?php if( $link = ApotheosisLibAcl::getUserLinkAllowed( 'apoth_report_admin', array( 'view'=>'admin' ) ) ) : ?>
		<li <?php if( $this->view == 'admin'      ) { echo 'class="current"'; } ?>><a href="<?php echo $link; ?>"><span id="nav_admin" class="menu_icon">Admin</span></a></li>
<?php endif; ?>
	</ul>
</div>
