<?php
/**
 * @package     Arc
 * @subpackage  Module_RSS
 * @copyright   Copyright (C) 2005 Punnet. All rights reserved. See COPYRIGHT_ARC.txt
 * @license     http://www.gnu.org/licenses/gpl.html GNU/GPL. See LICENSE_ARC.txt
 * Arc is free software: you can redistribute it and/or modify
 * it under the terms of version 3 of the GNU General Public License
 * as published by the Free Software Foundation.
 * The disclaimer of warranty as stated in the GPL applies to this program
 */

defined( '_JEXEC' ) or die( 'Restricted access' );

// store the module params for each feed
foreach( $data['feeds'] as $kFeed=>$feed ) {
	$numItems[$kFeed] =       $params->get('rss_items_'.$kFeed);
	$showFavicon[$kFeed] = ( ($params->get('rss_favicon_'.$kFeed) == 1) ? true : false );
	$numShown[$kFeed] = 0;
}
?>
<div class="item_body<?php echo $modSuffix; ?>">
	<?php
		// loop through super array of items and display according to params
		foreach( $data['allItems'] as $kItem=>$currItem ) {
			$feed = substr(strrchr($kItem, '~'), 1);
			
			// check we haven't already output this feeds specified number of items
			if( $numShown[$feed] < $numItems[$feed] ) {
				
				// item title
				if( !is_null($currItem->get_link()) ) {
					echo '<div class="item'.$modSuffix.'">';
					echo '<div class="favicon'.$modSuffix.'">';
					if( $showFavicon[$feed] ) {
						echo '<img src="'.$data['feeds'][$feed]->favicon.'" height="16" width="16">';
					}
					else {
						echo '&#149;';
					}
					echo '</div>';
					echo '<div class="item_link'.$modSuffix.'">';
					echo '<span class="'.($showSummary ? 'item_link_text'.$modSuffix : '').' item_slide'.$modSuffix.'">'.$currItem->get_title().'</span> [<a href="'.$currItem->get_link().'" target="_blank">read more</a>]';
					echo '</div>';
					
					// item description
					if ( $showSummary ) {
						$text = $currItem->get_description();
						$text = str_replace( '&apos;', "'", $text );
						
						// word limit check
						if( $summaryWords ) {
							$words = explode( ' ', $text );
							$count = count( $words );
							if( $count > $summaryWords ) {
								$text = '';
								for( $i = 0; $i < $summaryWords; $i++ ) {
									$text .= ' '.$words[$i];
								}
								$text .= '...';
							}
						}
						?>
						<div class="item_data<?php echo $modSuffix; ?>">
							<?php echo $text; ?>
						</div>
						<?php
					}
					echo '</div>';
					$numShown[$feed]++;
				}
			}
		}
	?>
</div>