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

require_once( JPATH_SITE.DS.'components'.DS.'com_arc_core'.DS.'libraries'.DS.'apoth_library.php' ); 

 /*
 * TV TV Model
 *
 * @author     Lightinthedark<code@lightinthedark.org.uk>
 * @package    Arc
 * @subpackage TV
 * @since      1.5
 */
class TvModelVideo extends ApothModel
{
	/**
	 * TV model vars
	 */
	var $_dimensions = null;
	var $_searchTerms = array();
	var $_searchedTag = array();
	var $_searchedIds = array();
	var $_showOverlay = false;
	var $_showSidebarOverlay = false;
	var $_previewLinkMod = false;
	var $_sidebarPreviewLinkMod = false;
	var $_tagCloud = array();
	var $_sidebarTitle = '';
	var $_savedSidebarTitle = null;
	var $_savedCriteria = null;
	var $_curVideo = null;
	var $_recommendedVideoPager = null; 
	var $_sidebarVideoPager     = null; 
	var $_searchedVideoPager    = null; 
	
	/**
	 * TV model constructor
	 */
	function __construct()
	{
		parent::__construct();
		
		$this->_coreParams = &JComponentHelper::getParams( 'com_arc_core' );
		$this->_params     = &JComponentHelper::getParams( 'com_arc_tv' );
		$this->fVideo      = &ApothFactory::_( 'tv.video' );
		$this->fVideo->setParam( 'video_scripts', $this->_params->get('video_scripts') );
		$this->fVideo->setPersistent( 'searches' );
		$this->fVideo->setPersistent( 'searchParams' );
	}
	
// 	/**
// 	 * Extending parent Arc model __sleep() here to minify some variables
// 	 * 
// 	 * @see ApothModel::__sleep()
// 	 */
// 	function __sleep()
// 	{
// 		return parent::__sleep();
// 	}
	
	/**
	 * Upon __wakeup() we need to un-minify some vars
	 * or set vars that were originally set in constructor
	 */
	function __wakeup()
	{
		parent::__wakeup();
		
		// setup same vars as the constructor did
		$this->_coreParams = &JComponentHelper::getParams( 'com_arc_core' );
		$this->_params     = &JComponentHelper::getParams( 'com_arc_tv' );
		$this->fVideo      = &ApothFactory::_( 'tv.video' );
		$this->fVideo->setPersistent( 'searches' );
		$this->fVideo->setPersistent( 'searchParams' );
	}
	
	
	// #####  Component params  #####
	
	/**
	 * Get various parameters
	 * 
	 * @return mixed The requested parameters
	 */
	function getAllowRatings()  { return (bool)$this->_params->get( 'video_ratings' ); }
	function getVotw()          { return  (int)$this->_params->get( 'votw' ); }
	function getRecSize()       { return  (int)$this->_params->get( 'rec_size' ); }
	function getSidebarSize()   { return  (int)$this->_params->get( 'sidebar_size' ); }
	function getSearchedSize()  { return  (int)$this->_params->get( 'searched_size' ); }
	function getTagCloudSize()  { return  (int)$this->_params->get( 'tagcloud_size' ); }
	function getTagCloudScale() { return  (int)$this->_params->get( 'tagcloud_scale' ); }
	function getViewedDays()    { return  (int)$this->_params->get( 'viewed_days' ); }
	function getSiteId()        { return  (int)$this->_coreParams->get( 'site_id' ); }
	
	
	// #####  Manipulate videos  #####
	
	/**
	 * Get the current video
	 * 
	 * @return int  The ID of the current video
	 */
	function &getVideo()
	{
		return $this->_curVideo;
	}
	
	/**
	 * Set the current video
	 * 
	 * @param int|null $vidId  The ID of the video to set or null for a new one
	 * @return boolean  Did we manage to find the requested video or get a blank
	 */
	function setVideo( $vidId )
	{
		if( is_null($vidId) || empty($vidId) ) {
			$this->_curVideo = &$this->fVideo->getDummy();
		}
		elseif( $vidId < 0 ) {
			$this->_curVideo = &$this->fVideo->getDummy( $vidId );
		}
		else {
			$this->_curVideo = &$this->fVideo->getInstance( $vidId );
		}
		
		return !is_null( $this->_curVideo->getId() );
	}
	
	/**
	 * Increment the number of views for the current video
	 */
	function addVidView()
	{
		$u = &ApotheosisLib::getUser();
		$this->fVideo->addVidView( $this->_curVideo->getId(), $this->getSiteId(), $u->person_id );
	}
	
	/**
	 * Get the tags associated with the current video
	 * 
	 * @return array  Array of the tags for the current video
	 */
	function getVideoTags()
	{
		return $this->_curVideo->getTags();
	}
	
	/**
	 * Get the IDs of videos owned by the specified people
	 * 
	 * @param array $peopleList  Array containing site and person IDs
	 * @return array $retVal  Array of the video IDs owned by the people supplied indexed on site ID then person ID
	 */
	function getVidsBy( $peopleList )
	{
		$raw = $this->fVideo->getVidsBy( $peopleList );
		
		$retVal = array();
		foreach( $raw as $idInfo ) {
			$retVal[$idInfo['site_id']][$idInfo['person_id']][] = $idInfo['id'];
		}
		
		return $retVal;
	}
	
	/**
	 * Get the next recommended video
	 * 
	 * @return mixed  Video object if found, false otherwise
	 */
	function &getNextRecommended()
	{
		return $this->_getNextPagedVideo( 'recommended' );
	}
	
	/**
	 * Set the recommended videos for the current user
	 * 
	 * @param string $criteria  Which criteria to use to populate the recommended videos
	 */
	function setRecommended( $criteria )
	{
		$this->_setPagedVideos( 'recommended', $criteria );
	}
	
	/**
	 * Get the next sidebar video
	 *
	 * @return mixed  Video object if found, false otherwise
	 */
	function &getNextSidebar()
	{
		return $this->_getNextPagedVideo( 'sidebar' );
	}
	
	/**
	 * Set the sidebar videos for the current user
	 *
	 * @param string $criteria  Which criteria to use to populate the sidebar
	 * @param boolean $idCheck  Should we perform the ID check?
	 */
	function setSidebar( $criteria, $idCheck = true )
	{
		// do we need to extend persistence of the 'searched' paginator
		if( $this->savePaginationCheck('searched', $idCheck) ) {
			// prepare sidebar overlays and manage links
			if( $this->_savedCriteria == 'searched_my' ) {
				$this->_showSidebarOverlay = true;
			}
			if( $this->_savedCriteria == 'searched_mod' ) {
				$this->_showSidebarOverlay = true;
				$this->_sidebarPreviewLinkMod = true;
			}
			
			// we need to intercept the criteria to base the sidebar on
			$criteria = 'searched_recent';
		}
		
		$this->_setPagedVideos( 'sidebar', $criteria );
	}
	
	/**
	 * Get the next searched video
	 *
	 * @return mixed  Video object if found, false otherwise
	 */
	function &getNextSearched()
	{
		return $this->_getNextPagedVideo( 'searched' );
	}
	
	/**
	 * Set the searched videos for the current user
	 *
	 * @param array $searchTerms  The user's search terms 
	 */
	function setSearched( $searchTerms )
	{
		$this->_searchTerms = $searchTerms;
		$this->_setPagedVideos( 'searched', 'searched' );
	}
	
	/**
	 * Set the tag searched videos for the current user
	 *
	 * @param array $searchTerms  The user's search terms 
	 */
	function setSearchedTag( $searchedTag )
	{
		$this->_searchedTag = $searchedTag;
		$this->_setPagedVideos( 'searched', 'searched_tag' );
	}
	
	/**
	 * Set the videos for moderation for the current user
	 */
	function setSearchedMod()
	{
		$this->_setPagedVideos( 'searched', 'searched_mod' );
	}
	
	/**
	 * Set the owner searched videos for the current user
	 */
	function setSearchedMy()
	{
		$this->_setPagedVideos( 'searched', 'searched_my' );
	}
	
	/**
	 * Set the IDs searched videos for the current user
	 * 
	 * @param array $searchedIds  Array of video IDs to perform the search with
	 */
	function setSearchedIds( $searchedIds)
	{
		$this->_searchedIds = $searchedIds;
		$this->_setPagedVideos( 'searched', 'searched_ids' );
	}
	
	/**
	 * Get the next paged video of the specified type
	 *
	 * @param string $type  What type of paged video do we want
	 * @return mixed  Video object if found, false otherwise
	 */
	function &_getNextPagedVideo( $type )
	{
		if( !isset($this->_{$type}) ) {
			$this->_{$type} = $this->{'_'.$type.'VideoPager'}->getPagedInstances();
		}
		
		$next = each( $this->_{$type} );
		
		if( is_array($next) ) {
			$retVal = &$this->fVideo->getInstance( $next['value'] );
		}
		else {
			$retVal = false;
		}
		
		return $retVal;
	}
	
	/**
	 * Set a page of videos of the specified type
	 *
	 * @param string $type  What type of paged video do we want
	 * @param string $criteria  Which criteria to use to populate the recommended videos
	 */
	function _setPagedVideos( $type, $criteria )
	{
		// by default don't save the paginator
		$savePagination = false;
		
		// set the pagination size
		switch( $type ) {
		case( 'recommended' ):
			$pageSize = $this->getRecSize();
			break;
			
		case( 'sidebar' ):
			$pageSize = $this->getSidebarSize();
			break;
			
		case( 'searched' ):
			$pageSize = $this->getSearchedSize();
			break;
		}
		
		// set requirements
		$requirements = array();
		switch( $criteria ) {
			case( 'user' ):
				$u = ApotheosisLib::getUser();
				$pId = $u->person_id;
				$faves = $this->fVideo->getUserFaves( $this->getSiteId(), $pId );
				$searchTermIds = !empty($faves) ? $faves : $this->getVotw();
				$searchTerms = $this->_getSearchTerms( $searchTermIds );
			case( 'related' ):
				if( !isset($searchTerms) ) {
					$searchTerms = $this->_getSearchTerms( $this->_curVideo->getId() );
				}
				$requirements['status'] = ARC_TV_APPROVED;
				$requirements['searched'] = array_unique( $searchTerms[0] );
				$requirements['exclude_ids'] = $searchTerms[1];
				$order = array( 'relevance'=>'d' );
				$this->_sidebarTitle = 'Related videos...';
				break;
				
			case( 'viewed' ):
				$days = $this->getViewedDays();
				
				$requirements['status'] = ARC_TV_APPROVED;
				$requirements['viewed_from'] = date( 'Y-m-d', strtotime('-'.$days.' days') );
				$requirements['viewed_to'] = date( 'Y-m-d' ).' 23:59:59';
				$order = array( 'view_count'=>'d' );
				$this->_sidebarTitle = 'Most viewed...';
				break;
					
			case( 'searched' ):
				$requirements['status'] = ARC_TV_APPROVED;
				$requirements['searched'] = $this->_searchTerms;
				$order = array( 'relevance'=>'d' );
				break;
				
			case( 'searched_tag' ):
				$requirements['status'] = ARC_TV_APPROVED;
				$requirements['searched_tag'] = $this->_searchedTag;
				$order = array( 'relevance'=>'d' );
				break;
				
			case( 'searched_mod' ):
				$requirements['status'] = ARC_TV_PENDING;
				$order = array( 'submitted_date'=>'a' );
				$savePagination = true;
				$this->_savedSidebarTitle = 'Videos for moderation...';
				$this->_savedCriteria = $criteria;
				break;
			
			case( 'searched_my' ):
				$u = ApotheosisLib::getUser();
				$pId = $u->person_id;
				$requirements['owner'] = $pId;
				$order = array( 'creation_date'=>'d' );
				$savePagination = true;
				$this->_savedSidebarTitle = 'My videos...';
				$this->_savedCriteria = $criteria;
				break;
			
			case( 'searched_ids' ):
				$requirements['status'] = ARC_TV_APPROVED;
				$requirements['id'] = $this->_searchedIds;
				break;
			
			case( 'searched_recent' ):
				$this->_sidebarVideoPager = &$this->_searchedVideoPager;
				$this->_sidebarTitle = $this->_savedSidebarTitle;
				break;
		}
		
		// check to see if relevant pagination object is already set (persistent)
		// if not then create it now
		if( !isset($this->{'_'.$type.'VideoPager'}) ) {
			$this->{'_'.$type.'VideoPager'} = &ApothPagination::_( 'tv.video.'.$type );
			$this->{'_'.$type.'VideoPager'}->setData( $requirements, $order );
			$this->{'_'.$type.'VideoPager'}->setPageSize( $pageSize );
		}
		
		// save the pagination object if required
		if( $savePagination ) {
			$this->_savePagination( $type );
		}
	}
	
	/**
	 * Retrieve search terms from given video ids
	 * comprising all words from the respective title, descriptions and tags 
	 * 
	 * @param string|array $vidIds  Video ID or array of video IDs
	 * @return array  2 element array of search terms (array) and the video IDs used to find them (array)
	 */
	function _getSearchTerms( $vidIds )
	{
		$vidIds = $this->fVideo->getInstances( array('id'=>$vidIds), true );
		
		$searchStrings = array();
		foreach( $vidIds as $vidId ) {
			$vidObj = &$this->fVideo->getInstance( $vidId );
			$title = $vidObj->getDatum( 'title' );
			$desc = $vidObj->getDatum( 'desc' );
			$tags = $vidObj->getTags();
			$tagList = array();
			foreach( $tags as $info ) {
				$tagList[] = $info['word'];
			}
			$tagList = implode( ' ', $tagList );
			$searchStrings[] = strtolower( $title.' '.$desc.' '.$tagList );
		}
		
		$searchString = implode( ' ', $searchStrings );
		$searchTerms = $this->cleanUpInput( $searchString );
		$searchTerms = array_unique( $searchTerms );
		
		return array( $searchTerms, $vidIds );
	}
	
	/**
	 * Retrieve the number of pages stored in the specified paginator
	 * 
	 * @param string $type  Which paginator do we want the page count for
	 * @return int  The number of pages in the specified paginator
	 */
	function getPageCount( $type )
	{
		return $this->{'_'.$type.'VideoPager'}->getPageCount();
	}
	
	/**
	 * Retrieve the IDs of the videos in the named paginator
	 * 
	 * @param string $type  Which paginator do we want the video IDs from
	 * @return array $retVal  The video IDs from the named paginator
	 */
	function getPageIds( $type )
	{
		$retVal = array();
		if( isset($this->{'_'.$type.'VideoPager'}) ) {
			$retVal = $this->{'_'.$type.'VideoPager'}->getAllInstances( false );
		}
		
		return $retVal;
	}
	
	/**
	 * Retrieves the tag cloud
	 * 
	 * @return array  Array of tag id, tag, tag count and scale factor
	 */
	function &getTagCloud()
	{
		return $this->_tagCloud;
	}
	
	/**
	 * Set up a list of the most popular tags including scale factor
	 */
	function setTagCloud()
	{
		// get tag cloud
		$tagCloud = &$this->fVideo->getTagCloud( $this->getTagCloudSize() );
		
		// determine scaling
		$scale = $this->getTagCloudScale();
		
		$first = reset( $tagCloud );
		$highCount = (int)$first['count'];
		
		$last = end( $tagCloud );
		$lowCount = (int)$last['count'];
		
		$scaleRange = $highCount - $lowCount;
		if( $scaleRange == 0 ) {
			$scaleRange = 1;
		}
		
		foreach( $tagCloud as $wordId=>$tagInfo ) {
			$tagCloud[$wordId]['scale'] = ((($tagInfo['count'] - $lowCount) / $scaleRange) * ($scale - 1)) + 1;
		}
		
		// sort alphabetically
		uasort( $tagCloud, array($this, '_tagSort') );
		
		$this->_tagCloud = &$tagCloud;
	}
	
	/**
	 * Tag cloud cmp_function sorting funtion
	 */
	function _tagSort( $a, $b )
	{
		if( $a['word'] == $b['word'] ) {
			$retVal = 0;
		}
		else {
			$retVal = ( $a['word'] < $b['word'] ) ? -1 : 1;
		}
		
		return $retVal;
	}
	
	/**
	 * Retrieve the horizontal dimension of the current vertical dimension
	 * 
	 * @return int  The corresponding horizontal video resolution
	 */
	function getHorizontal()
	{
		if( is_null($this->_dimensions) ) {
			$i = 1;
			while( ($resTuple = $this->_params->get('res_'.$i)) != '' ) {
				$resPair = explode( '_', $resTuple );
				$this->_dimensions[$resPair[0]] = $resPair[1];
				$i++;
			}
		}
		
		return $this->_dimensions[$this->_curVideo->getRes()];
	}
	
	/**
	 * Retrieve the title for the sidebar based on what we set for it to contain
	 */
	function getSidebarTitle()
	{
		return $this->_sidebarTitle;
	}
	
	/**
	 * Delete the files for the specified video
	 * 
	 * @return array  Associative array of success indicator and message
	 */
	function delVidFiles()
	{
		// get the video ID in question
		$vidId = $this->_curVideo->getId();
		
		// Remote URL
		$url = $this->_params->get('video_scripts').'/delete_files.php';
		
		// Use cURL to call the remote delete script
		$cHandle = curl_init();
		
		curl_setopt( $cHandle, CURLOPT_URL, $url );
		curl_setopt( $cHandle, CURLOPT_HEADER, false );
		curl_setopt( $cHandle, CURLOPT_RETURNTRANSFER, true );
		curl_setopt( $cHandle, CURLOPT_POST, true );
		curl_setopt( $cHandle, CURLOPT_POSTFIELDS, array('vidId'=>$vidId) );
		$success = json_decode( curl_exec($cHandle), true );
		
		curl_close( $cHandle );
		
		return $success;
	}
	
	/**
	 * Inform the video server that a new video has been approved
	 * and to start encoding its remaining formats
	 * 
	 * @param string $vidId  The ID of the video that has just been approved
	 * @return array  Associative array of success indicator and message
	 */
	function approveVideo( $vidId )
	{
		// Remote URL
		$url = $this->_params->get('video_scripts').'/approve.php';
		
		// Use cURL to call the remote delete script
		$cHandle = curl_init();
		
		curl_setopt( $cHandle, CURLOPT_URL, $url );
		curl_setopt( $cHandle, CURLOPT_HEADER, false );
		curl_setopt( $cHandle, CURLOPT_RETURNTRANSFER, true );
		curl_setopt( $cHandle, CURLOPT_POST, true );
		curl_setopt( $cHandle, CURLOPT_POSTFIELDS, array('vidId'=>$vidId) );
		$success = json_decode( curl_exec($cHandle), true );
		
		curl_close( $cHandle );
		
		return $success;
	}
	
	/**
	 * Retrieve the error messages associated with committing the current video
	 * 
	 * @return array  array of DB error messages
	 */
	function getErrMsg()
	{
		return $this->_curVideo->getErrMsg();
	}
	
	/**
	 * Retrieve the username to display based on site iD and Arc Person ID
	 * 
	 * @param int $siteId  The site ID for the current user
	 * @param string $pId  The person ID for the current user
	 * @return string  The username to display
	 */
	function getDisplayName( $siteId, $pId )
	{
		return ( $siteId == $this->getSiteId() ) ? ApotheosisData::_('people.displayName', $pId, 'person') : 'Person from another school';
	}
	
	/**
	 * Get are we showing mini status icon overlays status
	 * 
	 * @return boolean  True if showing, false if not
	 */
	function getShowOverlay()
	{
		return $this->_showOverlay;
	}
	
	/**
	 * Set are we showing mini status icon overlays status
	 * 
	 * @param boolean $show  True if showing, false if not
	 */
	function setShowOverlay( $show )
	{
		$this->_showOverlay = $show;
	}
	
	/**
	 * Get are we showing mini status icon overlays status in the sidebar
	 *
	 * @return boolean  True if showing, false if not
	 */
	function getShowSidebarOverlay()
	{
		return $this->_showSidebarOverlay;
	}
	
	/**
	 * Get are we using mod link for preview status
	 * 
	 * @return boolean  True if moderation link, false if regular video page link
	 */
	function getPreviewLinkMod()
	{
		return $this->_previewLinkMod;
	}
	
	/**
	 * Set are we using mod link for preview status
	 * 
	 * @param boolean $linkMod  True if moderation link, false if regular video page link
	 */
	function setPreviewLinkMod( $linkMod )
	{
		$this->_previewLinkMod = $linkMod;
	}
	
	/**
	 * Get are we using mod link for preview status in the sidebar
	 *
	 * @return boolean  True if moderation link, false if regular video page link
	 */
	function getSidebarPreviewLinkMod()
	{
		return $this->_sidebarPreviewLinkMod;
	}
	
	/**
	 * Clean up input strings ready for processing
	 * 
	 * @param str $inputString  Input string to clean up
	 * @return array  Array of words from input string
	 */
	function cleanUpInput( $inputString )
	{
		// this regex should match that found in the default_manage template
		preg_match_all( '~(^|\W)(\w+(\'s)?)(?=$|\W)~', $inputString, $targetArray );
		
		return $targetArray[2];
	}
	
	/**
	 * Rate the current video
	 * 
	 * @param int $rating  The rating to set
	 * @return array $retVal  Success indicator and optionally the new global and user ratings
	 */
	function rateVideo( $rating )
	{
		$u = &ApotheosisLib::getUser();
		return $this->_curVideo->setUserRating( $rating, $this->getSiteId(), $u->person_id );
	}
	
	/**
	 * Email the owner of the video with the outcome of the moderation
	 * 
	 * @return bool|object $sent  True if email sent, object otherwise
	 */
	function sendModEmail()
	{
		// get the status of the current video
		$outcome = $this->_curVideo->getStatusInfo();
		
		// from
		$fromAddress = $this->_params->get( 'email_from_address' );
		$fromName = $this->_params->get( 'email_from_name' );
		$from = array( $fromAddress, $fromName );
		
		// to
		$pId = $this->_curVideo->getDatum( 'person_id' );
		
		// subject and body
		switch( strtolower($outcome['status']) ) {
		case( 'approved' ):
			$emailSubject = $this->_params->get( 'email_approved_title' );
			$emailBody = $this->_params->get( 'email_approved_body' );
			break;
		case( 'rejected' ):
			$emailSubject = $this->_params->get( 'email_rejected_title' );
			$emailBody = $this->_params->get( 'email_rejected_body' );
			break;
		}
		
		// keyword substitution
		$link = JURI::base().ApotheosisLibAcl::getUserLinkAllowed( 'arc_tv_video', array('tv.videoId'=>$this->_curVideo->getId()) );
		$emailBody = str_replace( '~LINK~', $link, $emailBody );
		$emailBody = str_replace( '~COMMENT~', $outcome['comment'], $emailBody );
		
		$sent = ApotheosisData::_( 'people.sendEmail', $from, $pId, $emailSubject, $emailBody );
		
		return $sent;
	}
	
	/**
	 * Do we need to maintain persistence of the given pagination type?
	 * Likely to be called by controller when any page load does not include setSideBar()
	 * which usually performs this check
	 * 
	 * @param string $type  What type of paginator are we checking?
	 * @param boolean $idCheck  Should we perform the ID check?
	 * @return boolean $retVal  Did we need to extend persistence of the given paginator
	 */
	function savePaginationCheck( $type, $idCheck = true )
	{
		$retVal = false;
		
		// make comparisons here to simplify the final check
		$sidebarTitle = isset( $this->_savedSidebarTitle ); // saved sidebar title
		$paginator = isset( $this->{'_'.$type.'VideoPager'} ); // saved pagination object
		$curVideo = isset( $this->_curVideo ); // we are looking at a video page
		if( $sidebarTitle && $paginator && $curVideo && $idCheck ) { // only check IDs if we need to and can actually do it
			$id = ( array_search($this->_curVideo->getId(), $this->{'_'.$type.'VideoPager'}->getAllInstances(false)) !== false ); // the video we are looking at is in the saved search
		}
		else {
			$id = true; // don't check if the ID of the current video is in the saved paginator
		}
		
		// should we persist the named paginator?
		if( $sidebarTitle && $paginator && $curVideo && $id) {
			$this->_savePagination( $type );
			$retVal = true;
		}
		
		return $retVal;
	}
	
	/**
	 * Save the specified pagination object
	 *
	 * @param string $type  Which paginator to save
	 */
	function _savePagination( $type )
	{
		$this->setPersistent( '_'.$type.'VideoPager', 'paginator' );
		$this->setPersistent( '_savedSidebarTitle' );
		$this->setPersistent( '_savedCriteria' );
	
	}
	
	/**
	 * Clear the named paginator and saved sidebar title
	 * 
	 * @param string $type  What type of paginator do we want to clear
	 */
	function clearPagination( $type )
	{
		$this->{'_'.$type.'VideoPager'} = null; // this triggers pagination destructor
		unset( $this->{'_'.$type.'VideoPager'} );
		unset( $this->_savedSidebarTitle );
		unset( $this->_savedCriteria );
	}
	
	/**
	 * Save the data for the current video by processing the info colleced by the controller
	 * 
	 * @param array $types  What data subsets are we saving
	 * @param array $data  Array of all the data inputs
	 * @return int|false $retVal  The ID of the video we just saved data for or false on failure
	 */
	function save( $types, $data )
	{
		// status and last modified by
		$u = &ApotheosisLib::getUser();
		$this->_curVideo->setDatum( 'last_modified_by', $u->person_id );
		
		if( $this->_curVideo->getId() < 0 ) {
			$this->_curVideo->setDatum( 'site_id', $this->getSiteId() );
			$this->_curVideo->setDatum( 'person_id', $u->person_id );
		}
		
		// meta
		if( array_search('meta', $types) !== false ) {
			// ### title ###
			$this->_curVideo->setDatum( 'title', $data['title'] );
			
			// check we have some title text to index
			if( $data['title'] != '' ) {
				// get only real words
				$titleWords = $this->cleanUpInput( $data['title'] );
				
				// process title words, make lowercase and check for duplicates
				foreach( $titleWords as $k=>$word ) {
					$word = strtolower( $word );
					if( (($existingId = array_search($word, $titleWords)) !== false ) && ($existingId != $k) ) {
						unset( $titleWords[$k] );
					}
					else {
						$titleWords[$k] = $word;
					}
				}
				
				// proceed with indexing of title if we still have some words to index
				if( !empty($titleWords) ) {
					// save any new words and get all the IDs
					$titleIds = $this->fVideo->getWordIds( $titleWords, true );
				}
			}
			else {
				$titleIds = array();
			}
			
			// set the indexed title words into the video object
			$this->_curVideo->setTitleIndex( $titleIds );
			
			// ### desc ###
			$this->_curVideo->setDatum( 'desc', $data['desc'] );
			
			// check we have some description text to index
			if( $data['desc'] != '' ) {
				// get only real words
				$descWords = $this->cleanUpInput( $data['desc'] );
				
				// process description words, make lowercase and check for duplicates
				foreach( $descWords as $k=>$word ) {
					$word = strtolower( $word );
					if( (($existingId = array_search($word, $descWords)) !== false ) && ($existingId != $k) ) {
						unset( $descWords[$k] );
					}
					else {
						$descWords[$k] = $word;
					}
				}
				
				// proceed with indexing of description if we still have some words to index
				if( !empty($descWords) ) {
					// save any new words and get all the IDs
					$descIds = $this->fVideo->getWordIds( $descWords, true );
				}
			}
			else {
				$descIds = array();
			}
			
			// set the indexed desc words into the video object
			$this->_curVideo->setDescIndex( $descIds );
			
			// ### tags ###
			// check we have some tags to save
			if( !is_null($data['tags']) ) {
				// check to see if we need to save any new tags
				if( !is_null($data['tags_new']) && is_array($data['tags_new']) ) {
					// remove any new tags which aren't actually going to be used
					foreach( $data['tags_new'] as $k=>$tag ) {
						$tag = strtolower( $tag );
						if( array_search($k, $data['tags']) === false ) {
							unset( $data['tags_new'][$k] );
						}
						elseif( (($existingId = array_search($tag, $data['tags_new'])) !== false ) && ($existingId != $k) ) {
							unset( $data['tags_new'][$k] );
						}
						else {
							$data['tags_new'][$k] = $tag;
						}
					}
					
					// proceed with dealing with new tags only if we have any left
					if( !empty($data['tags_new']) ) {
						// clean the remaining new tags
						$data['tags_new'] = implode( ' ', $data['tags_new'] );
						$data['tags_new'] = $this->cleanUpInput( $data['tags_new'] );
						$data['tags_new'] = array_unique( $data['tags_new'] );
						
						// save new tags and get their IDs
						$data['tags_new'] = $this->fVideo->getWordIds( $data['tags_new'], true );
					}
					
					// clean out the negetive word IDs in the $data['tags'] array
					foreach( $data['tags'] as $k=>$wordId ) {
						if( $wordId < 0 ) {
							unset( $data['tags'][$k] );
						}
					}
					
					// merge the 2 arrays
					$data['tags'] = array_merge( $data['tags'], $data['tags_new'] ) ;
				}
				
				// for the purposes of saving the tags we need to approximate the expected structure
				// of the tags in video object
				$data['tags'] = array_flip( $data['tags'] );
			}
			else {
				$data['tags'] = array();
			}
			
			// set the tag data into the video object
			$this->_curVideo->setTags( $data['tags'] );
		}
		
		// credits
		if( array_search('credits', $types) !== false ) {
			// check to see if we need to save any new roles first
			if( !is_null($data['roles_new']) && is_array($data['roles_new']) ) {
				// remove any new roles which aren't actually going to be used
				foreach( $data['roles_new'] as $k=>$v ) {
					$keep = false;
					foreach( $data['roles'] as $roleArray ) {
						if( $k == $roleArray['id'] ) {
							$keep = true;
							break;
						}
					}
					
					if( !$keep ) {
						unset( $data['roles_new'][$k] );
					}
				}
				
				// proceed with dealing with new roles only if we have any left
				if( !empty($data['roles_new']) ) {
					// save the new roles and get their IDs
					$newRoleIds = $this->fVideo->getRoleIds( $data['roles_new'], true );
					
					// replace temp IDs in the data with the new permanent ID
					foreach( $data['roles'] as $k=>$roleArray ) {
						// update new role IDs
						if( $roleArray['id'] < 0 ) {
							$data['roles'][$k]['id'] = $newRoleIds[$data['roles_new'][$roleArray['id']]];
						}
					}
				}
			}
			
			// set the new data into the video object
			$this->_curVideo->setRoles( $data['roles'] );
		}
		
		// filters
		if( array_search('filters', $types) !== false ) {
			// check we have some people to save
			if( is_null($data['people']) ) {
				$data['people'] = array();
			}
			
			// check we have some years to save
			if( is_null($data['years']) ) {
				$data['years'] = array();
			}
			
			// check we have some groups to save
			if( is_null($data['groups']) ) {
				$data['groups'] = array();
			}
			
			// set the new data into the video object
			$this->_curVideo->setFilters( array('people'=>$data['people'], 'years'=>$data['years'], 'groups'=>$data['groups']) );
		}
		
		// moderate
		if( array_search('moderate', $types) !== false ) {
			// check we have some comment to save
			if( $data['comments'] == '' ) {
				$data['comments'] = null;
			}
			
			// set the new data into the video object
			$this->_curVideo->setStatus( $data['status'], $data['comments'] );
		}
		else {
			$this->_curVideo->setStatus( $data['status'] );
		}
		
		// save the data
		$success = $this->_curVideo->commit();
		
		// deal with return values
		if( $success ) {
			$retVal = $this->_curVideo->getId();
		}
		else {
			$retVal = false;
		}
		
		return $retVal;
	}
}
?>