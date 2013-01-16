<?php
/**
 * @package     Arc
 * @subpackage  TV
 * @copyright   Copyright (C) 2005 Punnet. All rights reserved. See COPYRIGHT_ARC.txt
 * @license     http://www.gnu.org/licenses/gpl.html GNU/GPL. See LICENSE_ARC.txt
 * Arc is free software: you can redistribute it and/or modify
 * it under the terms of version 3 of the GNU General Public License
 * as published by the Free Software Foundation.
 * The disclaimer of warranty as stated in the GPL applies to this program
 */

defined( '_JEXEC' ) or die( 'Restricted access' );

// determine if we are showing status dot overlyas on the previews
if( (isset($this->showOverlay) && $this->showOverlay) || (isset($this->showSidebarOverlay) && $this->showSidebarOverlay) ) {
	$curStatus = $this->curPreview->getStatusInfo();
	$overlayHtml = '<div class="preview_overlay">'.JHTML::_( 'arc.dot', $curStatus['colour'], $curStatus['status'] ).'</div>';
}
else {
	$overlayHtml = '';
}

// determine which preview link to use: manage for moderation search, video page otherwise
if( (isset($this->previewLinkMod) && $this->previewLinkMod) || (isset($this->sidebarPreviewLinkMod) && $this->sidebarPreviewLinkMod) ) {
	$previewLink = ApotheosisLibAcl::getUserLinkAllowed( 'arc_tv_manage', array('tv.videoId'=>$this->curPreview->getId()) );
}
else {
	$previewLink = ApotheosisLibAcl::getUserLinkAllowed( 'arc_tv_video', array('tv.videoId'=>$this->curPreview->getId()) );
}
?>
<div class="preview_div">
	<?php echo $overlayHtml; ?>
	<a href="<?php echo $previewLink; ?>">
		<img src="<?php echo $this->curPreview->getThumbnail(); ?>" width="150" height="84" /><br />
	</a>
	<div class="vid_info">
		<span class="preview_title"><?php echo $this->curPreview->getDatum( 'title' ); ?></span><br />
		<span class="preview_desc"><?php echo $this->curPreview->getDatum( 'desc' ); ?></span>
	</div>
</div>