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
	function __construct()
	{
		parent::__construct();
		$this->registerTask( 'accept', 'approval' );
		$this->registerTask( 'reject', 'approval' );
		
		$this->model = &$this->getModel( 'video' );
		$this->view =  &$this->getView( 'video', JRequest::getVar('format', 'html') );
		$this->view->setModel( $this->model, true );
	}
	
	/**
	 * Default action
	 * Calls appropriate display function
	 */
	function display()
	{
		// video of the week
		$this->model->setVideo( $this->model->getVotw() );
		
		// wrapper has recommended for you
		$this->model->setRecommended( 'user' );
		
		// sidebar is most viewed
		$this->model->setSidebar( 'viewed' );
		
		// tag cloud
		$this->model->setTagCloud();
		
		// display
		$this->view->home();
	}
	
	/**
	 * Show the selected video
	 * Calls appropriate display function
	 */
	function video()
	{
		// selected video
		$this->model->setVideo( JRequest::getVar('vidId') );
		
		// increment the views of this video
		$this->model->addVidView();
		
		// wrapper has recommended for you
		$this->model->setRecommended( 'user' );
		
		// sidebar is related
		$this->model->setSidebar( 'related' );
		
		// tag cloud
		$this->model->setTagCloud();
		
		// display
		$this->view->video();
	}
	
	/**
	 * Search for a video
	 * Calls appropriate display function
	 */
	function search()
	{
		// retrieve search terms
		preg_match_all( '~(?<=^|\W)\w+(\'s)?(?=$|\W)~', JRequest::getVar('search'), $searchTerms );
		
		// check we have some valid search terms
		if( !empty($searchTerms[0]) ) {
			$this->model->setSearched( $searchTerms[0] );
			
			// search can have recommended for you if no results found
			$this->model->setRecommended( 'user' );
			
			// sidebar is most viewed
			$this->model->setSidebar( 'viewed' );
			
			// tag cloud
			$this->model->setTagCloud();
			
			// display
			$this->view->search( 'Search results...' );
		}
		else {
			global $mainframe;
			$mainframe->enqueueMessage( 'Please enter valid search terms', 'notice' );
			$this->display();
		}
	}
	
	/**
	 * Search for videos with a specific tag
	 * Calls appropriate display function
	 */
	function tag()
	{
		// tag being searched on
		$tag = JRequest::getVar( 'tag' );
		
		// retrieve the tag to search with
		$searchedTag = array( $tag );
		
		// set the searched tag
		$this->model->setSearchedTag( $searchedTag );
		
		// sidebar is most viewed
		$this->model->setSidebar( 'viewed' );
		
		// tag cloud
		$this->model->setTagCloud();
		
		// display
		$this->view->search( 'Videos containing the tag \''.$tag.'\'...' );
	}
	
	/**
	 * Search for videos to be moderated
	 * Calls appropriate display function
	 */
	function moderate()
	{
		// set the showing of mini status icon preview overlays
		$this->model->setShowOverlay( true );
		
		// set the video preview link to be the manage page, not the video page
		$this->model->setPreviewLinkMod( true );
		
		// set moderation as the search type
		$this->model->setSearchedMod();
		
		// search can have recommended for you if no results found
		$this->model->setRecommended( 'user' );
		
		// sidebar is most viewed
		$this->model->setSidebar( 'viewed' ); // **** recent searches stuff needs to be an option here when we have a moderate button to go to moderation queue
		
		// tag cloud
		$this->model->setTagCloud();
		
		// display
		$this->view->search( 'Videos for moderation...' );
	}
	
	/**
	 * Search for videos owned by the current user
	 * Calls appropriate display function
	 */
	function myVids()
	{
		// set the showing of mini status icon preview overlays
		$this->model->setShowOverlay( true );
		
		// set myVids as the search type
		$this->model->setSearchedMy();
		
		// search can have recommended for you if no results found
		$this->model->setRecommended( 'user' );
		
		// sidebar is most viewed
		$this->model->setSidebar( 'viewed' ); // **** recent searches stuff needs to be an option here
		
		// tag cloud
		$this->model->setTagCloud();
		
		// display
		$this->view->search( 'My videos...' );
	}
	
	/**
	 * Search for videos owned by the current user
	 * Calls appropriate display function
	 */
	function idsSearch()
	{
		// get the user ID
		$siteIdTuple = JRequest::getVar( 'userid' );
		
		// determine user name
		$userNameInfo = explode( '_', $siteIdTuple );
		$userName = $this->model->getDisplayName( $userNameInfo[0], $userNameInfo[1] );
		
		// get IDs from the session
		$session = &JSession::getInstance( 'none', array() );
		$ids = $session->get( $siteIdTuple, array(), 'userVidIds' );
		
		// set user video IDs as the search type
		$this->model->setSearchedIds( $ids );
		
		// sidebar is most viewed
		$this->model->setSidebar( 'viewed' ); // **** recent searches stuff needs to be an option here
		
		// tag cloud
		$this->model->setTagCloud();
		
		// display
		$this->view->search( 'Videos owned by '.$userName );
	}
	
	/**
	 * Manage the entire process of uploading a video through to moderating it
	 * Calls appropriate display function
	 */
	function manage()
	{
		// selected video
		$this->model->setVideo( JRequest::getVar('vidId', null) );
		
		// sidebar is most viewed
		$this->model->setSidebar( 'viewed' ); // **** recent searches stuff needs to be an option here when we have a moderate button to go to moderation queue
		
		// tag cloud
		$this->model->setTagCloud();
		
		// display
		$this->view->manage();
	}
	
	/**
	 * Show the "upload a file" form (which gets rendered in an frame)
	 */
	function vidFiles()
	{
		// selected video
		$this->model->setVideo( JRequest::getVar('vidId', null) );
		$this->view->frameFile();
	}
	
	/**
	 * Delete the files for the video specified in the URL
	 * Output directly if response is to XHR or redirect to manage() with enqueued result message
	 */
	function delVidFiles()
	{
		// what video are we working with
		$vidId = JRequest::getVar( 'vidId' );
		
		// attempt to delete the source video files and set appropriate results / messages
		$delVid = $this->model->delVidFiles( $vidId );
		
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
			global $mainframe;
			$link = ApotheosisLibAcl::getUserLinkAllowed( 'arc_tv_manage', array( 'tv.videoId'=>$vidId ) );
			
			$mainframe->enqueueMessage( $result['message'], $result['type'] );
			$mainframe->redirect( $link );
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
		// what video are we working with
		$vidId = JRequest::getVar( 'video_id', null );
		$initStatus = JRequest::getVar( 'video_status' );
		$this->model->setVideo( $vidId );
		
		// determine which pane we are viewing
		$meta =     JRequest::getVar( 'manage_meta_pane' );
		$credits =  JRequest::getVar( 'manage_credits_pane' );
		$filters =  JRequest::getVar( 'manage_filters_pane' );
		$types = array();
		
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
		$newVidId = $this->model->save( $types, $data );
		
		if( $newVidId ) {
			// Get any other secondary errors
			$errors = $this->model->getErrMsg();
			$successMessage = ( $submit ? 'Video data successfully saved and submitted for moderation.' : 'Video data successfully saved.' );
			array_unshift( $errors, $successMessage );
			
			$result['message'] = implode( '<br />', $errors );
			$result['type'] = 'message';
			$result['status'] = $data['status'];
			$vidId = $newVidId;
		}
		else {
			$result['message'] = reset( $this->model->getErrMsg() );
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
			global $mainframe;
			$link = ApotheosisLibAcl::getUserLinkAllowed( 'arc_tv_manage', array( 'tv.videoId'=>$vidId ) );
			$mainframe->enqueueMessage( $result['message'], $result['type'] );
			$mainframe->redirect( $link );
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
	 * Moderator has accepted or rejected this video so mark it as such
	 * and tell encoding server to start on the other formats if appropriate
	 */
	function approval()
	{
		// what video are we working with
		$vidId = JRequest::getVar( 'video_id', null );
		$this->model->setVideo( $vidId );
		
		// accept or reject?
		$approval = strtolower( JRequest::getVar('task') );
		
		// set the data
		$types[] = 'moderate';
		$data['status'] = ( $approval == 'accept' ) ? ARC_TV_APPROVED : ARC_TV_REJECTED;
		$data['comments'] = JRequest::getVar( 'manage_comments_input' );
		
		// call the model save method
		$vidId = $this->model->save( $types, $data );
		
		// if the approve status was correctly set and we saved the new status correctly
		// we need to tell the video encoder
		$encMessage = '';
		if( ($approval == 'accept') && $vidId ) {
			$remote = $this->model->approveVideo( $vidId );
			if( $remote['success'] ) {
				$encMessage = ' The video encoder will now finish the remaining formats.';
			}
		}
		
		if( $vidId ) {
			$result['success'] = true;
			$result['message'] = 'Video has been '.$approval.'ed.'.$encMessage;
			$result['type'] = 'message';
		}
		else {
			$result['success'] = false;
			$result['message'] = 'We were unable to '.$approval.' the video at this time. Please try again.';
			$result['type'] = 'warning';
		}
		$result['id'] = $vidId;
		
		// what page format
		$format = JRequest::getVar( 'format', 'html' );
		
		// determine output
		if( $format == 'raw' ) {
			echo json_encode( $result );
		}
		else {
			global $mainframe;
			$link = ApotheosisLibAcl::getUserLinkAllowed( 'arc_tv_moderate', array() );
				
			$mainframe->enqueueMessage( $result['message'], $result['type'] );
			$mainframe->redirect( $link );
		}
	}
	
	/**
	 * Homepage panel to show VotW
	 */
	function panel()
	{
		// video of the week
		$this->model->setVideo( $this->model->getVotw() );
		
		// display
		$this->view->panel();
	}
	
	/**
	 * Prepare a new status div to be displayed via Ajax
	 */
	function updateStatus()
	{
		// set the video whose current status we need to update
		$this->model->setVideo( JRequest::getVar('video_id') );
		
		// display
		$this->view->updateStatus();
	}
}
?>