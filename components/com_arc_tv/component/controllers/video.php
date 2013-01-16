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

/**
 * TV Video Controller
 *
 * @author     lightinthedark <code@lightinthedark.org.uk>
 * @package    Arc
 * @subpackage TV
 * @since      0.1
 */
class TvControllerVideo extends TvController
{
	/**
	 * TV controller constructor
	 */
	function __construct()
	{
		parent::__construct();
		$this->registerTask( 'savedraft', 'save' );
		$this->registerTask( 'approve', 'approval' );
		$this->registerTask( 'reject', 'approval' );
		$this->registerTask( 'moderatevideo', 'manage' );
	}
	
	/**
	 * Default action
	 * Calls appropriate display function
	 */
	function display()
	{
		// prepare the model and view
		$model = &$this->getModel( 'video' );
		$view  = &$this->getView( 'video', JRequest::getVar('format', 'html') );
		$view->setModel( $model, true );
		
		// clear any saved searches
		$model->clearPagination( 'searched' );
		
		// video of the week
		$model->setVideo( $model->getVotw() );
		
		// wrapper has recommended for you
		$model->setRecommended( 'user' );
		
		// sidebar is most viewed
		$model->setSidebar( 'viewed' );
		
		// tag cloud
		$model->setTagCloud();
		
		// display
		$view->home();
	}
	
	/**
	 * Show the selected video
	 * Calls appropriate display function
	 */
	function video()
	{
		// prepare the model and view
		$model = &$this->getModel( 'video' );
		$view  = &$this->getView( 'video', JRequest::getVar('format', 'html') );
		$view->setModel( $model, true );
		
		// selected video
		$model->setVideo( JRequest::getVar('vidId') );
		
		// increment the views of this video
		$model->addVidView();
		
		// wrapper has recommended for you
		$model->setRecommended( 'user' );
		
		// sidebar is related
		$model->setSidebar( 'related' );
		
		// tag cloud
		$model->setTagCloud();
		
		// display
		$view->video();
	}
	
	/**
	 * Search for a video
	 * Calls appropriate display function
	 */
	function search()
	{
		// prepare the model and view
		$model = &$this->getModel( 'video' );
		$view  = &$this->getView( 'video', JRequest::getVar('format', 'html') );
		$view->setModel( $model, true );
		
		// clear any saved searches
		$model->clearPagination( 'searched' );
		
		// retrieve cleaned search terms
		$searchTerms = $model->cleanUpInput( JRequest::getVar('search') );
		
		// check we have some valid search terms
		if( !empty($searchTerms) ) {
			$model->setSearched( $searchTerms );
			
			// search can have recommended for you if no results found
			$model->setRecommended( 'user' );
			
			// sidebar is most viewed
			$model->setSidebar( 'viewed' );
			
			// tag cloud
			$model->setTagCloud();
			
			// display
			$view->search( 'Search results...' );
		}
		else {
			$this->enqueueMessage( 'Please enter valid search terms', 'notice' );
			$this->display();
		}
	}
	
	/**
	 * Search for videos with a specific tag
	 * Calls appropriate display function
	 */
	function tag()
	{
		// prepare the model and view
		$model = &$this->getModel( 'video' );
		$view  = &$this->getView( 'video', JRequest::getVar('format', 'html') );
		$view->setModel( $model, true );
		
		// clear any saved searches
		$model->clearPagination( 'searched' );
		
		// tag being searched on
		$tag = JRequest::getVar( 'tag' );
		
		// retrieve the tag to search with
		$searchedTag = array( $tag );
		
		// set the searched tag
		$model->setSearchedTag( $searchedTag );
		
		// sidebar is most viewed
		$model->setSidebar( 'viewed' );
		
		// tag cloud
		$model->setTagCloud();
		
		// display
		$view->search( 'Videos containing the tag \''.$tag.'\'...' );
	}
	
	/**
	 * Search for videos to be moderated
	 * Calls appropriate display function
	 */
	function moderate()
	{
		// prepare the model and view
		$model = &$this->getModel( 'video' );
		$view  = &$this->getView( 'video', JRequest::getVar('format', 'html') );
		$view->setModel( $model, true );
		
		// clear any saved searches
		$model->clearPagination( 'searched' );
		
		// set moderation as the search type
		$model->setSearchedMod();
		
		// set the showing of mini status icon preview overlays
		$model->setShowOverlay( true );
		
		// set the video preview link to be the manage page, not the video page
		$model->setPreviewLinkMod( true );
		
		// search can have recommended for you if no results found
		$model->setRecommended( 'user' );
		
		// sidebar is most viewed
		$model->setSidebar( 'viewed' );
		
		// tag cloud
		$model->setTagCloud();
		
		// display
		$view->search( 'Videos for moderation...' );
	}
	
	/**
	 * Search for videos owned by the current user
	 * Calls appropriate display function
	 */
	function myVids()
	{
		// prepare the model and view
		$model = &$this->getModel( 'video' );
		$view  = &$this->getView( 'video', JRequest::getVar('format', 'html') );
		$view->setModel( $model, true );
		
		// clear any saved searches
		$model->clearPagination( 'searched' );
		
		// set myVids as the search type
		$model->setSearchedMy();
		
		// set the showing of mini status icon preview overlays
		$model->setShowOverlay( true );
		
		// search can have recommended for you if no results found
		$model->setRecommended( 'user' );
		
		// sidebar is most viewed
		$model->setSidebar( 'viewed' );
		
		// tag cloud
		$model->setTagCloud();
		
		// display
		$view->search( 'My videos...' );
	}
	
	/**
	 * Search for videos owned by the current user
	 * Calls appropriate display function
	 */
	function idsSearch()
	{
		// prepare the model and view
		$model = &$this->getModel( 'video' );
		$view  = &$this->getView( 'video', JRequest::getVar('format', 'html') );
		$view->setModel( $model, true );
		
		// clear any saved searches
		$model->clearPagination( 'searched' );
		
		// get the user ID
		$siteIdTuple = JRequest::getVar( 'userid' );
		
		// determine user name
		$userNameInfo = explode( '_', $siteIdTuple );
		$userName = $model->getDisplayName( $userNameInfo[0], $userNameInfo[1] );
		
		// get IDs from the session
		$session = &JSession::getInstance( 'none', array() );
		$ids = $session->get( $siteIdTuple, array(), 'userVidIds' );
		
		// set user video IDs as the search type
		$model->setSearchedIds( $ids );
		
		// sidebar is most viewed
		$model->setSidebar( 'viewed' );
		
		// tag cloud
		$model->setTagCloud();
		
		// display
		$view->search( 'Videos owned by '.$userName );
	}
	
	/**
	 * Manage the entire process of uploading a video through to moderating it
	 * Calls appropriate display function
	 */
	function manage()
	{
		// prepare the model and view
		$model = &$this->getModel( 'video' );
		$view  = &$this->getView( 'video', JRequest::getVar('format', 'html') );
		$view->setModel( $model, true );
		
		// selected video
		$model->setVideo( JRequest::getVar('vidId', null) );
		
		// override moderation go to basic management instead?
		$noMod = (bool)JRequest::getVar( 'noMod' );
		
		// sidebar is most viewed
		$model->setSidebar( 'viewed' );
		
		// tag cloud
		$model->setTagCloud();
		
		// display
		$view->manage( $noMod );
	}
	
	/**
	 * Show the "upload a file" form (which gets rendered in an iFrame)
	 */
	function vidFiles()
	{
		// prepare the model and view
		$model = &$this->getModel( 'video' );
		$view  = &$this->getView( 'video', JRequest::getVar('format', 'html') );
		$view->setModel( $model, true );
		
		// selected video
		$model->setVideo( JRequest::getVar('vidId') );
		
		// extend persistence of 'searched' paginator if appropriate
		$model->savePaginationCheck( 'searched' );
		
		// display the "upload a file" form (in an iFrame)
		$view->frameFile();
	}
	
	/**
	 * Delete the files for the video specified in the URL
	 * Output directly if response is to XHR or redirect to manage() with enqueued result message
	 */
	function delVidFiles()
	{
		// prepare the model
		$model = &$this->getModel( 'video' );
		
		// what video are we working with
		$vidId = JRequest::getVar( 'vidId' );
		$model->setVideo( $vidId );
		
		// extend persistence of 'searched' paginator if appropriate
		$model->savePaginationCheck( 'searched' );
		
		// attempt to delete the source video files and set appropriate results / messages
		$delVid = $model->delVidFiles();
		
		if( $delVid['success'] ) {
			$result['success'] = true;
			$result['message'] = 'Video files successfully deleted.';
			$result['type'] = 'message';
		}
		else {
			$result['success'] = false;
			$result['message'] = 'We were unable to delete the video files at this time. Please try again.';
			$result['type'] = 'warning';
		}
		
		// what page format
		$format = JRequest::getVar( 'format', 'html' );
		
		// determine output
		if( $format == 'raw' ) {
			echo json_encode( $result );
		}
		else {
			$link = ApotheosisLibAcl::getUserLinkAllowed( 'arc_tv_manage', array('tv.videoId'=>$vidId) );
			$this->setRedirect( $link, $result['message'], $result['type'] );
		}
	}
	
	/**
	 * Rate a video
	 * Output directly if response is to XHR or redirect to video() with enqueued result message
	 */
	function rateVideo()
	{
		// prepare the model
		$model = &$this->getModel( 'video' );
		
		// what video are we working with
		$vidId = JRequest::getVar( 'vidId' );
		$model->setVideo( $vidId );
		
		// extend persistence of 'searched' paginator if appropriate
		$model->savePaginationCheck( 'searched' );
		
		// attempt to rate the video and set appropriate results / messages
		$rating = JRequest::getVar( 'rating' );
		$rateVid = $model->rateVideo( $rating );
		
		if( $rateVid['success'] ) {
			$result['success'] = true;
			$result['global'] = $rateVid['global'];
			$result['user'] = $rateVid['user'];
			$result['message'] = 'You rated this video '.$rating.' out of 5';
			$result['type'] = 'message';
		}
		else {
			$result['success'] = false;
			$result['message'] = 'Unable to rate the video. Please try again.';
			$result['type'] = 'warning';
		}
		
		// what page format
		$format = JRequest::getVar( 'format', 'html' );
		
		// determine output
		if( $format == 'raw' ) {
			echo json_encode( $result );
		}
		else {
			$link = ApotheosisLibAcl::getUserLinkAllowed( 'arc_tv_video', array('tv.videoId'=>$vidId) );
			$this->setRedirect( $link, $result['message'], $result['type'] );
		}
	}
	
	/**
	 * Save all the data relating to a video
	 * Output directly if response is to XHR or redirect to manage() with enqueued result message
	 * 
	 * @param boolean $submit  As well as saving are we also submitting the video for moderation?
	 */
	function save( $submit = false )
	{
		// prepare the model
		$model = &$this->getModel( 'video' );
		
		// what video are we working with
		$vidId = JRequest::getVar( 'video_id', null );
		$initStatus = JRequest::getVar( 'video_status' );
		$model->setVideo( $vidId );
		
		// extend persistence of 'searched' paginator if appropriate
		$model->savePaginationCheck( 'searched' );
		
		// determine which pane we are viewing
		$meta    = JRequest::getVar( 'manage_meta_pane' );
		$credits = JRequest::getVar( 'manage_credits_pane' );
		$filters = JRequest::getVar( 'manage_filters_pane' );
		$types   = array();
		
		// process the video meta data
		if( $meta ) {
			$data['title'] = JRequest::getVar( 'manage_title_input' );
			$data['desc'] = JRequest::getVar( 'manage_desc_input' );
			$data['tags'] = JRequest::getVar( 'manage_tags_input' );
			$data['tags_new'] = json_decode( JRequest::getVar('manage_tags_input_hidden'), true );
			
			$types[] = 'meta';
		}
		
		// process the video credits data
		if( $credits ) {
			$data['roles'] = json_decode( JRequest::getVar('manage_roles_input'), true );
			$data['roles_new'] = json_decode( JRequest::getVar('manage_roles_roles_input_hidden'), true );
			
			$types[] = 'credits';
		}
		
		// process the video access filters data
		if( $filters ) {
			$data['people'] = JRequest::getVar( 'manage_filter_people_input' );
			
			$data['years'] = JRequest::getVar( 'manage_filter_years_input' );
			if( is_array($data['years']) ) {
				foreach( $data['years'] as $k=>$year ) {
					if( $year == '' ) {
						unset( $data['years'][$k] );
					}
				}
			}
			
			$data['groups'] = unserialize( JRequest::getVar('manage_filter_groups_input') );
			
			$types[] = 'filters';
		}
		
		$data['status'] = ( $submit ? ARC_TV_PENDING : ARC_TV_INCOMPLETE );
		
		// Call the model save method
		$newVidId = $model->save( $types, $data );
		
		if( $newVidId ) {
			// Get any other secondary errors
			$errors = $model->getErrMsg();
			$successMessage = ( $submit ? 'Video data successfully saved and submitted for moderation.' : 'Video data successfully saved.' );
			array_unshift( $errors, $successMessage );
			
			$result['message'] = implode( '<br />', $errors );
			$result['type'] = 'message';
			$result['status'] = $data['status'];
			$vidId = $newVidId;
		}
		else {
			$result['message'] = reset( $model->getErrMsg() );
			$result['type'] = 'warning';
			$result['status'] = $initStatus;
		}
		$result['id'] = $vidId;
		
		// what page format
		$format = JRequest::getVar( 'format', 'html' );
		
		// determine output
		if( $format == 'raw' ) {
			echo json_encode( $result );
		}
		else {
			$link = ApotheosisLibAcl::getUserLinkAllowed( 'arc_tv_manage', array('tv.videoId'=>$vidId) );
			$this->setRedirect( $link, $result['message'], $result['type'] );
		}
	}
	
	/**
	 * Save and submit all the data relating to a video
	 * Calls the save function with submit set as true
	 */
	
	function submit()
	{
		$this->save( true );
	}
	
	/**
	 * Moderator has approved or rejected this video so mark it as such
	 * and tell encoding server to start on the other formats if appropriate
	 */
	function approval()
	{
		// prepare the model and view
		$model = &$this->getModel( 'video' );
		
		// what video are we working with
		$vidId = JRequest::getVar( 'video_id' );
		$model->setVideo( $vidId );
		
		// extend persistence of 'searched' paginator if appropriate
		$model->savePaginationCheck( 'searched' );
		
		// approve or reject?
		$approval = strtolower( JRequest::getVar('task') );
		
		// set the data
		$types[] = 'moderate';
		$data['status'] = ( $approval == 'approve' ) ? ARC_TV_APPROVED : ARC_TV_REJECTED;
		$data['comments'] = JRequest::getVar( 'manage_comments_input' );
		
		// call the model save method
		$savedVidId = $model->save( $types, $data );
		
		// if the approve status was correctly set and we saved the new status correctly
		// we need to tell the video encoder
		$encMessage = '';
		if( ($approval == 'approve') && $savedVidId ) {
			$remote = $model->approveVideo( $savedVidId );
			if( $remote['success'] ) {
				$encMessage = ' The video encoder will now finish the remaining formats.';
			}
		}
		
		if( $savedVidId ) {
			// get the ID of the next moderatable video
			$curModIds = $model->getPageIds( 'searched' );
			$nextModVidId = !empty( $curModIds ) ? reset($curModIds) : null;
			
			// email the owner of the video with the outcome of the moderation
			$sentEmail = $model->sendModEmail();
			if( $sentEmail === true ) {
				$emailMessage = ' Email notification sent to video owner.';
			}
			else {
				$emailMessage = ' Email notification to video owner failed.';
			}
			
			// set message text for approval state
			if( $approval == 'approve' ) {
				$approvalState = 'approved';
			}
			elseif( $approval == 'reject' ) {
				$approvalState = 'rejected';
			}
			
			$result['message'] = 'Video has been '.$approvalState.'.'.$emailMessage.$encMessage;
			$result['type'] = 'message';
			$result['status'] = $data['status'];
		}
		else {
			// next moderatable video ID will the same one as before
			$nextModVidId = $vidId;
			
			$result['message'] = 'We were unable to '.$approval.' the video at this time. Please try again.';
			$result['type'] = 'warning';
			$result['status'] = ARC_TV_PENDING;
		}
		$result['id'] = $savedVidId;
		$result['next_id'] = $nextModVidId;
		
		// what page format
		$format = JRequest::getVar( 'format', 'html' );
		
		// determine output
		if( $format == 'raw' ) {
			echo json_encode( $result );
		}
		else {
			$link = ApotheosisLibAcl::getUserLinkAllowed( 'arc_tv_manage', array('tv.videoId'=>$nextModVidId) );
			$this->setRedirect( $link, $result['message'], $result['type'] );
		}
	}
	
	/**
	 * Homepage panel to show VotW
	 */
	function panel()
	{
		// prepare the model and view
		$model = &$this->getModel( 'video' );
		$view  = &$this->getView( 'video', JRequest::getVar('format', 'html') );
		$view->setModel( $model, true );
		
		// video of the week
		$model->setVideo( $model->getVotw() );
		
		// extend persistence of 'searched' paginator if appropriate
		$model->savePaginationCheck( 'searched' );
		
		// display
		$view->panel();
	}
	
	/**
	 * Prepare a new status div to be displayed via Ajax
	 */
	function updateStatus()
	{
		// prepare the model and view
		$model = &$this->getModel( 'video' );
		$view  = &$this->getView( 'video', JRequest::getVar('format', 'html') );
		$view->setModel( $model, true );
		
		// set the video whose current status we need to update
		$model->setVideo( JRequest::getVar('video_id') );
		
		// are we checking IDs as part of the pagination check?
		$idCheck = (bool)JRequest::getvar( 'idCheck' );
		
		// extend persistence of 'searched' paginator if appropriate
		$model->savePaginationCheck( 'searched', $idCheck );
		
		// display
		$view->updateStatus();
	}
	
	/**
	 * Prepare a sidebar div to be displayed via Ajax
	 */
	function sidebar()
	{
		// prepare the model and view
		$model = &$this->getModel( 'video' );
		$view  = &$this->getView( 'video', JRequest::getVar('format', 'html') );
		$view->setModel( $model, true );
		
		// set the video whose current status we need to update
		$model->setVideo( JRequest::getVar('video_id') );
		
		// are we checking IDs as part of the pagination check that will take place in $model->setSidebar()?
		$idCheck = (bool)JRequest::getvar( 'idCheck' );
		
		// sidebar defaults to 'viewed', will be changed by setSidebar() to 'searched_recent' if appropriate
		$model->setSidebar( 'viewed', $idCheck );
		
		// display
		$view->sidebar();
	}
}
?>