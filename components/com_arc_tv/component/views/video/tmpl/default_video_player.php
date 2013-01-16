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

// add javascript
JHTML::script( 'default_video_player.js', $this->addPath, true );

$this->curVideo->setRes( 360 );
$formats = $this->curVideo->getFormatsAtRes();
?>
<div id="player_div">
	<?php if( !empty($formats) ): ?>
		<video controls="controls" preload="auto" width="<?php echo $this->get( 'Horizontal' ); ?>" height="<?php echo $this->curVideo->getRes(); ?>" poster="<?php echo $this->curVideo->getPoster(); ?>">
			<?php if( array_search('mp4', $formats) !== false ): ?>
				<source src="<?php echo $this->curVideo->getVideo( 'mp4'  ); ?>" type="video/mp4" />
			<?php endif; ?>
			<?php if( array_search('webm', $formats) !== false ): ?>
				<source src="<?php echo $this->curVideo->getVideo( 'webm' ); ?>" type="video/webm" />
			<?php endif; ?>
			<?php if( array_search('mobile.mp4', $formats) !== false ): ?>
				<source src="<?php echo $this->curVideo->getVideo( 'mobile.mp4' ); ?>" type="video/mp4" />
			<?php endif; ?>
			<embed src="<?php echo $this->curVideo->getFlashPlayer(); ?>"
					flashvars="?&autoplay=false&sound=70&buffer=2&splashscreen=<?php echo $this->curVideo->getURLEncPoster(); ?>&vdo=<?php echo $this->curVideo->getURLEncVideo( 'mp4' ); ?>"
					width="<?php echo $this->get( 'Horizontal' ); ?>"
					height="<?php echo $this->curVideo->getRes(); ?>"
					allowFullScreen="true"
					quality="best"
					wmode="transparent"
					allowScriptAccess="always"
					pluginspage="http://www.macromedia.com/go/getflashplayer"
					type="application/x-shockwave-flash">
			</embed>
		</video>
	<?php else : ?>
		No video available yet... still encoding.
	<?php endif; ?>
</div>