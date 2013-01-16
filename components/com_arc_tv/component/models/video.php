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
	function __construct()
	{
		parent::__construct();
		
		$this->fVideo = &ApothFactory::_( 'tv.video' );
		$this->_params = &JComponentHelper::getParams( 'com_arc_tv' );
		$this->fVideo->setParam( 'video_scripts', $this->_params->get('video_scripts') );
		$this->_coreParams = &JComponentHelper::getParams( 'com_arc_core' );
		$this->_dimensions = null;
		$this->_searchTerms = array();
	}
	
	function __sleep(){
		return parent::getPersistent();
	}
	
	function __wakeup()
	{
		$this->fVideo =                ApothFactory::_( 'tv.video', $this->fVideo );
		$this->recommendedVideoPager = ApothPagination::_( 'tv.video.recommended' );
		$this->sidebarVideoPager =     ApothPagination::_( 'tv.video.sidebar' );
		$this->searchedVideoPager =    ApothPagination::_( 'tv.video.searched' );
	}
	
	
	// #####  Component params  #####
	
	/**
	 * Get various parameters
	 * 
	 * @return mixed The requested parameters
	 */
	function getVotw()          { return (int)$this->_params->get( 'votw' ); }
	function getRecSize()       { return (int)$this->_params->get( 'rec_size' ); }
	function getSidebarSize()   { return (int)$this->_params->get( 'sidebar_size' ); }
	function getSearchedSize()  { return (int)$this->_params->get( 'searched_size' ); }
	function getTagCloudSize()  { return (int)$this->_params->get( 'tagcloud_size' ); }
	function getTagCloudScale() { return (int)$this->_params->get( 'tagcloud_scale' ); }
	function getViewedDays()    { return (int)$this->_params->get( 'viewed_days' ); }
	function getSiteId()        { return (int)$this->_coreParams->get( 'site_id' ); }
	
	
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
	 */
	function setSidebar( $criteria )
	{
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
	 * Get the next tag searched video
	 *
	 * @return mixed  Video object if found, false otherwise
	 */
	function &getNextSearchedTag()
	{
		return $this->_getNextPagedVideo( 'searched_tag' );
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
	 * Get the next video for moderation
	 *
	 * @return mixed  Video object if found, false otherwise
	 */
	function &getNextSearchedMod()
	{
		return $this->_getNextPagedVideo( 'searched_mod' );
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
	 * Get the next paged video of the specified type
	 *
	 * @param string $type  What type of paged video do we want
	 * @return mixed  Video object if found, false otherwise
	 */
	function &_getNextPagedVideo( $type )
	{
		if( !isset($this->_{$type}) ) {
			$this->_{$type} = $this->{$type.'VideoPager'}->getPagedInstances();
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
				$this->_sidebarTitle = 'Related Videos...';
				break;
				
			case( 'viewed' ):
				$days = $this->getViewedDays();
				
				$requirements['status'] = ARC_TV_APPROVED;
				$requirements['viewed_from'] = date( 'Y-m-d', strtotime('-'.$days.' days') );
				$requirements['viewed_to'] = date( 'Y-m-d' ).' 23:59:59';
				$order = array( 'view_count'=>'d' );
				$this->_sidebarTitle = 'Most Viewed...';
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
				break;
			
			case( 'searched_my' ):
				$u = ApotheosisLib::getUser();
				$pId = $u->person_id;
				$requirements['owner'] = $pId;
				break;
			
			case( 'searched_recent' ):
				// **** probably look to see if we have search data from awakened model
				// **** if so use it or fallback to most viewed
				$requirements['status'] = ARC_TV_APPROVED;
				$requirements['id'] = array( 19, 29, 39, 49, 59, 89 ); // **** need more info on this
				$this->_sidebarTitle = 'Searched Videos...';
				break;
		}
		
		$this->{$type.'VideoPager'} = ApothPagination::_( 'tv.video.'.$type );
		$this->{$type.'VideoPager'}->setData( $requirements, $order );
		$this->{$type.'VideoPager'}->setPageSize( $pageSize );
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
			$title = $vidObj->getDatum('title');
			$desc = $vidObj->getDatum('desc');
			$tags = $vidObj->getTags();
			$tagList = array();
			foreach( $tags as $info ) {
				$tagList[] = $info['word'];
			}
			$tagList = implode( ' ', $tagList );
			$searchStrings[] = strtolower( $title.' '.$desc.' '.$tagList );
		}
		
		$searchString = implode( ' ', $searchStrings );
		preg_match_all( '~(?<=^|\W)\w+(\'s)?(?=$|\W)~', $searchString, $searchTerms );
		$searchTerms = array_unique( $searchTerms[0] );
		
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
		return $this->{$type.'VideoPager'}->getPageCount();
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
		//  get tag cloud
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
	 * @param string $vidId  The ID of the video whose source files we wish to remove
	 * @return array  Associative array of success indicator and message
	 */
	function delVidFiles( $vidId )
	{
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
				preg_match_all( '~(?<=^|\W)\w+(\'s)?(?=$|\W)~', $data['title'], $titleWords );
				$titleWords = $titleWords[0];
				
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
				preg_match_all( '~(?<=^|\W)\w+(\'s)?(?=$|\W)~', $data['desc'], $descWords );
				$descWords = $descWords[0];
				
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
					// remove any new tags which aren't actually going to be used and clean up valid new tags
					foreach( $data['tags_new'] as $k=>$tag ) {
						$tag = strtolower( $tag ); // **** further cleaning needed (punctuation etc)
						if( array_search($k, $data['tags']) === false ) {
							unset( $data['tags_new'][$k] );
						}
						elseif( (($existingId = array_search($tag, $data['tags_new'])) !== false ) && ($existingId != $k) ) {
							unset( $data['tags_new'][$k] );
							unset( $data['tags'][array_search($k, $data['tags'])] );
						}
						else {
							$data['tags_new'][$k] = $tag;
						}
					}
					
					// proceed with dealing with new tags only if we have any left
					if( !empty($data['tags_new']) ) {
						// save new tags and get their IDs
						$newTagIds = $this->fVideo->getWordIds( $data['tags_new'], true );
						
						// replace temp IDs in the data with the new permanent ID
						foreach( $data['tags'] as $k=>$tagId ) {
							// update new tag IDs
							if( $tagId < 0 ) {
								$data['tags'][$k] = $newTagIds[$data['tags_new'][$tagId]];
							}
						}
					}
				}
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