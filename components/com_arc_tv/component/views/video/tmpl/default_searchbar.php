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
<div id="searchbar_div">
	<form method="post" action="<?php echo ApotheosisLibAcl::getUserLinkAllowed( 'arc_tv_search', array() ); ?>">
		<div id="mod_button_div">
			<?php if( ($link = ApotheosisLibAcl::getUserLinkAllowed( 'arc_tv', array() ) ) ): ?>
			<a id="vidhome_button"  class="btn" href="<?php echo $link; ?>">TV home</a>
			<?php endif; ?>
			<?php if( ($link = ApotheosisLibAcl::getUserLinkAllowed( 'arc_tv_myvids', array() ) ) ): ?>
			<a id="myvids_button"   class="btn" href="<?php echo $link; ?>">My Videos</a>
			<?php endif; ?>
			<?php if( ($link = ApotheosisLibAcl::getUserLinkAllowed( 'arc_tv_manage', array('tv.videoId'=>'' ) ) ) ): ?>
			<a id="upload_button"   class="btn" href="<?php echo $link; ?>">Upload</a>
			<?php endif; ?>
			<?php if( ($link = ApotheosisLibAcl::getUserLinkAllowed( 'arc_tv_moderate', array() ) ) ): ?>
			<a id="moderate_button" class="btn" href="<?php echo $link ?>">Moderate</a>
			<?php endif; ?>
		</div>
		<div id="search_input_div">
			<input id="search_input" type="text" name="search" value="" />
			<input type="submit" value="Search" name="submit" />
		</div>
	</form>
</div>