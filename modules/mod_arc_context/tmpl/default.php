<?php
/**
 * @package     Arc
 * @subpackage  Module_Context
 * @copyright   Copyright (C) 2005 Punnet. All rights reserved. See COPYRIGHT_ARC.txt
 * @license     http://www.gnu.org/licenses/gpl.html GNU/GPL. See LICENSE_ARC.txt
 * Arc is free software: you can redistribute it and/or modify
 * it under the terms of version 3 of the GNU General Public License
 * as published by the Free Software Foundation.
 * The disclaimer of warranty as stated in the GPL applies to this program
 */


JHTML::_( 'behavior.modal' );
?>
<div id="mod_arc_context">

	<?php if( !empty( $help ) ) { ?>
	<div class="button_list" id="context_help">
		<a href="<?php echo $help; ?>" target="_blank" class="btn"><span class="label"></span></a>
	</div>
	<?php
	}
	if( !empty( $links ) ) {
		 foreach( $links as $category=>$links ) {
	?>
	<div class="button_list" id="category_<?php echo strtolower($category); ?>">
		<div class="btn"><span class="label"></span></div>
		<div class="list_wrapper">
			<ul>
			<?php
			foreach( $links as $link ) {
				if( !isset( $link['target'] ) ) { $link['target'] = 'self'; }
				switch( $link['target'] ) {
					case( 'self' ):
						$tgt = '';
						break;
				
					case( 'blank' ):
						$tgt = 'target="_blank"';
						break;
				
					case( 'popup' ):
						$tgt = 'class="modal" rel="{handler: \'iframe\', size: {x: 500, y: 400}}"';
						break;
				}
				echo '<li><a href="'.$link['url'].'" '.$tgt.'>'.$link['text'].'</a></li>';
			}
			?>
			</ul>
		</div>
	</div>
	<?php
		}
	}
	?>
</div>