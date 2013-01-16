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

// show the recommended div if we are on a video page or a search page with no results
if( ($this->mainView == 'video') || (($this->mainView == 'search') && ($this->searchPageCount == 0)) ) {
	echo '<div id="recommend_div">'.$this->loadTemplate( 'wrapper' ).'</div>';
}
?>
<div id="homepage_left_div">
	<?php
		switch( $this->mainView ) {
		case( 'video' ):
			echo $this->loadTemplate( 'video' );
			break;
			
		case( 'search' ):
			$this->showOverlay = $this->get( 'ShowOverlay' );
			$this->previewLinkMod = $this->get( 'PreviewLinkMod' );
			echo $this->loadTemplate( 'search' );
			$this->showOverlay = false;
			$this->previewLinkMod = false;
			break;
			
		case( 'manage' ):
			echo $this->loadTemplate( 'manage' );
			break;
		}
	?>
</div>
<div id="homepage_right_div">
	<?php echo $this->loadTemplate( 'searchbar' ); ?>
	<?php echo $this->loadTemplate( 'sidebar' ); ?>
	<?php echo $this->loadTemplate( 'tagcloud' ); ?>
</div>