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

// ### ratings ###
$globalRating = $this->curVideo->getGlobalRating();

$user = &ApotheosisLib::getUser();
$userRating = $this->curVideo->getUserRating( $this->get('SiteId'), $user->person_id );

// ### tags ###
$tags = $this->curVideo->getTags();
if( !empty($tags) ) {
	foreach( $tags as $tag ) {
		$tagsHtml[] = '<a href="'.ApotheosisLibAcl::getUserLinkAllowed( 'arc_tv_tag', array('tv.tag'=>$tag['word'] ) ).'">'.$tag['word'].'</a>'; 
	}
	
	$tagsHtml = implode( ', ', $tagsHtml );
}

// ### roles ###
$roles = $this->curVideo->getRoles();
// add owner to roles array
$ownerArray = array('role'=>'Owner', 'site_id'=>$this->curVideo->getDatum('site_id'), 'person_id'=>$this->curVideo->getDatum('person_id') );
array_unshift( $roles, $ownerArray );

// determine if each person in a role has their own approved videos
$vidsById = $this->model->getVidsBy( $roles );

// determine what name to display for each person in a role
foreach( $roles as $k=>$roleInfo ) {
	unset( $roles[$k] );
	$name = $this->model->getDisplayName( $roleInfo['site_id'], $roleInfo['person_id'] );
	
	// determine if we should show a name as a link because the person has their own approved videos
	if( array_key_exists($roleInfo['site_id'], $vidsById) && array_key_exists($roleInfo['person_id'], $vidsById[$roleInfo['site_id']]) ) {
		// save current persons video IDs as an array in the session
		$siteIdTuple = $roleInfo['site_id'].'_'.$roleInfo['person_id'];
		if( ($nameLink = ApotheosisLibAcl::getUserLinkAllowed('arc_tv_uservids', array('tv.userid'=>$siteIdTuple))) ) {
			$session = &JSession::getInstance( 'none', array() );
			$curIds = $vidsById[$roleInfo['site_id']][$roleInfo['person_id']];
			$session->set( $siteIdTuple, $curIds, 'userVidIds' );
			
			$name = '<a href="'.$nameLink.'">'.$name.'</a>';
		}
	}
	
	$roles[$roleInfo['role']][] = $name;
}

foreach( $roles as $role=>$people ) {
	$rolesHtml[] = '<span class="credit_title">'.$role.'</span>: '.implode( ', ', $people );
}
$rolesHtml = implode( '<br />', $rolesHtml );
?>
<div id="video_div">
	<span class="section_title"><?php echo $this->vidDivTitle; ?></span><br />
	<?php echo $this->loadTemplate( 'video_player' ); ?>
	<?php if( $this->get('AllowRatings') ): ?>
		<div id="ratings_div_outer" style="width: <?php echo $this->get( 'Horizontal' ); ?>px;">
			<?php echo JHTML::_( 'arc_tv.ratings', 'ratings', $globalRating, $userRating, ApotheosisLibAcl::getUserLinkAllowed('arc_tv_rate_video', array('tv.videoId'=>$this->curVideo->getId())) ); ?>
			<div id="ajax_ratings_spinner_div"><?php echo JHTML::_( 'arc.loading', 'Rating...' ); ?></div>
			<div id="ajax_ratings_message_div"></div>
		</div>
	<?php endif; ?>
	<div id="info_div">
		<span class="video_title"><?php echo $this->curVideo->getDatum( 'title' ); ?></span>
		<?php if( ($link = ApotheosisLibAcl::getUserLinkAllowed('arc_tv_manage', array('tv.videoId'=>$this->curVideo->getId()))) ): ?>
			<a class="btn" href="<?php echo $link; ?>">manage</a>
		<?php endif; ?><br />
		<?php echo $this->curVideo->getDatum( 'desc' ); ?><br />
		<br />
		<?php if( $tagsHtml != '' ): ?>
			<span class="credit_title">Tags</span>: <?php echo $tagsHtml; ?><br />
		<?php endif; ?>
		<span class="credit_title">Length</span>: <?php echo JHTML::_( 'arc.secsToTime', $this->curVideo->getDatum('length') ); ?><br />
		<span class="credit_title">Uploaded</span>: <?php echo ApotheosisLibParent::arcDateTime( $this->curVideo->getUploadDate() ); ?><br />
		<br />
		<?php echo $rolesHtml; ?>
	</div>
</div>