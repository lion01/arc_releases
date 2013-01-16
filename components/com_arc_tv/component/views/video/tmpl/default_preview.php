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
?>
<div class="preview_div">
	<a href="<?php echo ApotheosisLibAcl::getUserLinkAllowed( 'arc_tv_video', array( 'tv.videoId'=>$this->curPreview->getId() ) ); ?>">
		<img src="<?php echo $this->curPreview->getThumbnail(); ?>" width="150" height="84" /><br />
	</a>
	<div class="vid_info">
		<span class="preview_title"><?php echo $this->curPreview->getDatum( 'title' ); ?></span><br />
		<span class="preview_desc"><?php echo $this->curPreview->getDatum( 'desc' ); ?></span>
	</div>
</div>