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
<div id="video_div">
	<span class="section_title"><?php echo $this->vidDivTitle; ?></span><br />
	<?php echo $this->loadTemplate( 'video_player' ); ?>
	<div id="info_div">
		<span class="video_title"><?php echo $this->curVideo->getDatum( 'title' ); ?></span>
		<?php if( ($link = ApotheosisLibAcl::getUserLinkAllowed( 'arc_tv_manage', array('tv.videoId'=>$this->curVideo->getId() ) ) ) ): ?>
		<a class="btn" href="<?php echo $link; ?>">manage</a>
		<?php endif; ?><br />
		<?php echo $this->curVideo->getDatum( 'desc' ); ?><br />
		<br />
		<span class="credit_title">Length</span>: <?php echo JHTML::_( 'arc.secsToTime', $this->curVideo->getDatum('length') ); ?><br />
		<span class="credit_title">Uploaded</span>: <?php echo ApotheosisLibParent::arcDateTime( $this->curVideo->getUploadDate() ); ?><br />
		<br />
		<?php
			$roles = $this->curVideo->getRoles();
			$ownerArray = array('role'=>'Owner', 'site_id'=>$this->curVideo->getDatum('site_id'), 'person_id'=>$this->curVideo->getDatum('person_id') );
			array_unshift( $roles, $ownerArray );
			
			foreach( $roles as $k=>$roleInfo ) {
				unset( $roles[$k] );
				$roles[$roleInfo['role']][] = ($roleInfo['site_id'] == $this->siteId) ? ApotheosisData::_('people.displayName', $roleInfo['person_id'], 'person') : 'Person from another school';
			}
			
			foreach( $roles as $role=>$people ) {
				echo '<span class="credit_title">'.$role.'</span>: '.implode( ', ', $people ).'<br />';
			}
		?>
	</div>
</div>