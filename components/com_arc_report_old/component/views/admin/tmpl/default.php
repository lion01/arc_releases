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

if( $this->enabled ) : ?>
	<h3><?php echo $this->groupName; ?>: Admin Options</h3>
	Select an administrative task:
	<ul class="apoth_general_menu">
	 	<li><a href="<?php echo $this->links['assignAdmins']; ?>"><img src="<?php echo 'components'.DS.'com_arc_report'.DS.'images'.DS; ?>spanner-plus.gif" /> Assign group administrators</a></li>
	 	<li><a href="<?php echo $this->links['assignPeers' ]; ?>"><img src="<?php echo 'components'.DS.'com_arc_report'.DS.'images'.DS; ?>spanner-plus.gif" /> Assign group peer checkers</a></li>
	 	<li><a href="<?php echo $this->links['associate'   ]; ?>"><img src="<?php echo 'components'.DS.'com_arc_report'.DS.'images'.DS; ?>spanner-plus.gif" /> Set this group to use settings from another group</a></li>
		<li><a href="<?php echo $this->links['statements'  ]; ?>"><img src="<?php echo 'components'.DS.'com_arc_report'.DS.'images'.DS; ?>list.gif"         /> Manage statement banks</a></li>
		<li><a href="<?php echo $this->links['layout'      ]; ?>"><img src="<?php echo 'components'.DS.'com_arc_report'.DS.'images'.DS; ?>document.gif"     /> Change page layout</a></li>
		<li><a href="<?php echo $this->links['fields'      ]; ?>"><img src="<?php echo 'components'.DS.'com_arc_report'.DS.'images'.DS; ?>document.gif"     /> Change field style</a></li>
		<li><a href="<?php echo $this->links['marks'       ]; ?>"><img src="<?php echo 'components'.DS.'com_arc_report'.DS.'images'.DS; ?>document.gif"     /> Change mark style</a></li>
		<li><a href="<?php echo $this->links['blurb'       ]; ?>"><img src="<?php echo 'components'.DS.'com_arc_report'.DS.'images'.DS; ?>text.gif"         /> Change printed subject name and introduction text</a></li>
		<li><a href="<?php echo $this->links['statistics'  ]; ?>"><img src="<?php echo 'components'.DS.'com_arc_report'.DS.'images'.DS; ?>dots-all.gif"     /> View completion statistics</a></li>
	</ul>
<?php else : ?>
	<h4><?php echo $this->groupName; ?> is above this group</h4>
<?php endif; ?>