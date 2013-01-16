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
<div id="tagcloud_div">
	<span class="section_title"><?php echo $this->tagCloudDivTitle; ?></span><br />
	<div id="tags_div">
		<?php foreach( $this->tagCloud as $tag ): ?>
			<span class="tag_span" style="font-size: <?php echo $tag['scale'] * 100; ?>%;">
				<a href="<?php echo ApotheosisLibAcl::getUserLinkAllowed( 'arc_tv_tag', array('tv.tag'=>$tag['word'] ) ); ?>"><?php echo $tag['word']; ?></a>
			</span>
		<?php endforeach; ?>
	</div>
</div>