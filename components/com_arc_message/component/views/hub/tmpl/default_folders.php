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
JHTML::_('behavior.tooltip');

if( $this->first ) {
	$parentsAreLinks = false;
	$this->first = false;
}
else {
	$parentsAreLinks = true;
}

// get the list of folders at this level
$tmp = $this->fTags->getInstances( array('folder'=>$this->folderCur, 'active'=>true) );
$folders = array();
foreach( $tmp as $id ) {
	$folders[$id] = $this->fTags->getInstance( $id );
}

if( !empty($folders) ) {
		
	echo "<ul>\n";
	foreach($folders as $id=>$info) {
		// recursively generate sublevels of menu
		$this->folderCur = $id; // *** swap this for the commented lines to only show path current folder
		
		$sub = $this->loadTemplate( 'folders' );	


		if( $sub != "" && !$parentsAreLinks ) {
			echo '<h4>'.$info->getLabel().'</h4>';
		}
		else {
			echo '<li'.( ($id == $this->folderSelected) ? ' class="cur"' : '' ).'>'.JHTML::_( 'arc_message.folderIcon', $id, $info->getLabel() ).'</li>';				
		}
		echo $sub;
	}
	echo "</ul>\n";
}

?>