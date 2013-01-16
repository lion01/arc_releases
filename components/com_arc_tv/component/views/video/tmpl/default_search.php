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
<div id="search_div">
	<span class="section_title"><?php echo $this->searchDivTitle; ?></span><br />
	<?php
		if( $this->searchPageCount > 0 ) {
			while( ($this->curPreview = &$this->get('NextSearched')) !== false ) {
				echo $this->loadTemplate( 'preview' );
			}
		} 
		else {
			echo 'Your search returned no results.<br />
					Please search again or check out the videos above that are recommended for you.';
		}
	?>
</div>