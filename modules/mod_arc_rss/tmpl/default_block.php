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

// loop through each feed and render its details / feeds
$firstFeed = true;
foreach( $data['feeds'] as $k=>$feed ) {
	// get the module params for this feed
	$showTitle =   ( ($params->get('rss_title_'.$k) == 1) ? true : false );
	$showDesc =    ( ($params->get('rss_desc_'.$k) == 1) ? true : false );
	$showImage =   ( ($params->get('rss_image_'.$k) == 1) ? true : false );
	$showFavicon = ( ($params->get('rss_favicon_'.$k) == 1) ? true : false );
	$numItems =       $params->get('rss_items_'.$k);
	?>
	<div class="feed<?php echo $modSuffix; ?>">
		<?php
			if( $firstFeed ) {
				$firstFeed = false;
			}
			else {
				echo '<hr />';
			}
		?>
		<div class="feed_body<?php echo $modSuffix; ?>">
			<?php
				// feed image
				if( $showImage && !is_null($feed->image->url) ) {
					echo '<div class="feed_image'.$modSuffix.'">';
					echo '<img src="'.$feed->image->url.'" alt="'.$feed->image->title.'" title="'.$feed->image->title.'" />';
					echo '</div>';
				}
			?>
			<div class="feed_title_desc<?php echo $modSuffix; ?>">
				<?php
					// feed title
					if( $showTitle && !is_null($feed->title) ) {
						echo '<div>';
						echo '<a href="'.str_replace( '&', '&amp', $feed->link ).'" target="_blank">'.$feed->title.'</a>';
						echo '</div>';
					}
					// feed description
					if( $showDesc && !is_null($feed->description) ) {
						echo '<div>';
						echo $feed->description;
						echo '</div>';
					}
				?>
			</div>
		</div>
		<div class="item_body<?php echo $modSuffix; ?>">
			<?php
				$itemsShown = 0;
				foreach( $data['allItems'] as $kItem=>$currItem ) {
					if( substr(strrchr($kItem, '~'), 1) == $k ) {
						
						// item title
						if( !is_null($currItem->get_link()) ) {
							echo '<div class="item'.$modSuffix.'">';
							echo '<div class="favicon'.$modSuffix.'">';
							if( $showFavicon ) {
								echo '<img src="'.$feed->favicon.'" height="16" width="16">';
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
						}
						if( $itemsShown++ == $numItems ) {
							break;
						}
					}
				}
			?>
		</div>
	</div>
<?php
}
?>