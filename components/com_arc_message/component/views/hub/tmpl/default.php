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

JHTML::script( 'default.js', JURI::base().'components'.DS.'com_arc_message'.DS.'views'.DS.'hub'.DS.'tmpl'.DS );

echo $this->loadTemplate( 'overrides' );

// set up the path and target folder ids (only runs the first time through)
if( !isset($this->folderPath) ) {
	$this->fTags = ApothFactory::_( 'message.Tag', $this->fTags );
	$folder = $this->get('Folder');
	$this->folder = $this->fTags->getInstance( $folder );
	$this->folderPath = $this->folder->getPath();
	$this->folderSelected = $this->folder->getId();
	$this->folderCur = array_shift($this->folderPath); // start in the "folder" tag
	$this->first = true;
	}
?>
<h3 id="arc_title">Messages</h3>

<div id="arc_nav">
<div id="folders">
	<div id="icon_wrap">
		<?php
		$filename = JPATH_SITE.DS.'components'.DS.'com_arc_message'.DS.'images'.DS.'reports.png';
		$url = JURI::base().DS.'components'.DS.'com_arc_message'.DS.'images'.DS.'reports.png';
		?>
		<?php echo $this->loadTemplate( 'folders' ); ?>
		<?php
		if( ( $l = ApotheosisLibAcl::getUserLinkAllowed( 'apoth_be_reports' ) ) !== false ) {
			$text = 'Behaviour Reports';
			if( file_exists($filename) ) {
				$lnk = '<img class="hasTip" title="" src="'.$url.'" /><br />'.$text;
			}
			else {
				$lnk = $text;
				$text = '';
			}
		}
		?>
	</div>
</div>
</div>

<div id="arc_main_narrow">
	<div id="messages">
<?php echo $this->loadTemplate( 'messages' ); ?>
	</div>
</div>
