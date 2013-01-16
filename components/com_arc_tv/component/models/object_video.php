<?php
/**
 * @package     Arc
 * @subpackage  Tv
 * @copyright   Copyright (C) 2005 Punnet. All rights reserved. See COPYRIGHT_ARC.txt
 * @license     http://www.gnu.org/licenses/gpl.html GNU/GPL. See LICENSE_ARC.txt
 * Arc is free software: you can redistribute it and/or modify
 * it under the terms of version 3 of the GNU General Public License
 * as published by the Free Software Foundation.
 * The disclaimer of warranty as stated in the GPL applies to this program
 */

defined( '_JEXEC' ) or die( 'Restricted access' );

/**
 * Tv Video Factory
 */
class ApothFactory_Tv_Video extends ApothFactory
{
	/**
	 * Create a Joomla! singleton database object for the video database
	 * 
	 * @return object  Database object
	 */
	function &getVidDBO()
	{
		static $vidDB = null;
		
		// get a referenced database object of the remote video database if needed
		if( is_null($vidDB) ) {
			$params = &JComponentHelper::getParams( 'com_arc_tv' );
			$options = array();
			$options['host'] =     $params->get( 'host' );
			$options['prefix'] =   $params->get( 'prefix' );
			$options['driver'] =   $params->get( 'driver' );
			$options['user'] =     $params->get( 'user' );
			$options['password'] = $params->get( 'password' );
			$options['database'] = $params->get( 'database' );
			$vidDB = &JDatabase::getInstance( $options );
		}
		
		return $vidDB;
	}
	
	/**
	 * Retrieve a blank video object with the given ID
	 * 
	 * @param int $id  The id that should be used for the dummy object. Must be negative if supplied.
	 */
	function &getDummy( $id = null )
	{
		if( is_null($id) ) {
			$id = $this->_getDummyId();
		}
		elseif( $id >= 0 ) {
			$r = null;
			return $r;
		}
		
		$r = &$this->_getInstance( $id );
		if( is_null($r) ) {
			$data = array(
				'id'=>$id,
				'title'=>'',
				'desc'=>'',
				'length'=>null,
				'site_id'=>'',
				'person_id'=>'',
				'status'=>ARC_TV_INCOMPLETE
			);
			$r = new ApothTvVideoVideo( $data );
			$r->setScriptsUrl( $this->getParam('video_scripts') );
			$this->_addInstance( $id, $r );
		}
		
		return $r;
	}
	
	/**
	 * Retrieves the identified video, creating the object if it didn't already exist
	 * 
	 * @param string $id  Video ID
	 */
	function &getInstance( $id )
	{
		$r = &$this->_getInstance( $id );
		if( is_null($r) ) {
			$db = &self::getVidDBO();
			
			$query = 'SELECT *'
				."\n".'FROM '.$db->nameQuote('videos')
				."\n".'WHERE '.$db->nameQuote('id').' = '.$db->Quote($id);
			$db->setQuery( $query );
			$data = $db->loadAssoc();
			
			$r = new ApothTvVideoVideo( $data );
			$r->setScriptsUrl( $this->getParam('video_scripts') );
			$this->_addInstance( $id, $r );
		}
		
		return $r;
	}
	
	/**
	 * Retrieves the videos identified by the given requirements
	 * 
	 * @param array $requirements  Array of col/val pairs to search on
	 * @param boolean $init  Do we want to also initialise and cache the objects
	 * @param array $orders  Do we want to order the results and if so by what criteria
	 */
	function &getInstances( $requirements, $init = true, $orders = null )
	{
		$sId = $this->_getSearchId( $requirements );
		$ids = $this->_getInstances( $sId );
		if( is_null($ids) ) {
			$db = &self::getVidDBO();
			$wheres = array();
			$viewCount = false;
			$whereCounts = array();
			
			foreach( $requirements as $col=>$val ) {
				if( is_array($val) ) {
					if( empty($val) ) {
						continue;
					}
					foreach( $val as $k=>$v ) {
						$val[$k] = $db->Quote( $v );
					}
					$assignPart = ' IN ('.implode( ', ',$val ).')';
				}
				elseif( is_null($val) ) {
					$assignPart = ' IS NULL';
				}
				else {
					$assignPart = ' = '.$db->Quote( $val );
				}
				
				switch( $col ) {
				case( 'id' ):
					$wheres[] = $db->nameQuote('vid').'.'.$db->nameQuote('id').$assignPart;
					break;
					
				case( 'status' ):
					$wheres[] = $db->nameQuote('vid').'.'.$db->nameQuote('status').$assignPart;
					break;
					
				case( 'owner' ):
					$wheres[] = $db->nameQuote('vid').'.'.$db->nameQuote('person_id').$assignPart;
					break;
					
				case( 'exclude_ids' ):
					if( is_array($val) ) {
						$wheres[] = $db->nameQuote('vid').'.'.$db->nameQuote('id').' NOT '.$assignPart;
					}
					break;
				
				case( 'viewed_from' ):
					$viewCount = true;
					$whereCounts[] = $db->nameQuote('views').'.'.$db->nameQuote('viewed_on').' >= '.$db->Quote( $val );
					break;
					
				case( 'viewed_to' ):
					$viewCount = true;
					$whereCounts[] = $db->nameQuote('views').'.'.$db->nameQuote('viewed_on').' <= '.$db->Quote( $val );
					break;
					
				case( 'searched' ):
				case( 'searched_tag' ):
					// get the IDs of the searched words
					$wordIdsQuery = 'SELECT '.$db->nameQuote('id')
						."\n".'FROM '.$db->nameQuote('words')
						."\n".'WHERE '.$db->nameQuote('word').$assignPart;
					$db->setQuery($wordIdsQuery);
					$wordIds = $db->loadResultArray();
					
					if( !empty($wordIds) ) {
						// search words found in the index so process them
						foreach( $wordIds as $k=>$wordId ) {
							$wordIds[$k] = $db->Quote($wordId);
						}
						$wordIdsPart = ' IN ( '.implode( ', ', $wordIds ).' )';
					}
					else {
						// no hits so return early and cache no hits for this search
						$this->_addInstances( $sId, array() );
						return array();
					}
					
					if( $col == 'searched' ) {
						$wheres[] = '( '.$db->nameQuote('vwv').'.'.$db->nameQuote('title').$wordIdsPart
						."\n".'   OR '.$db->nameQuote('vwv').'.'.$db->nameQuote('desc').$wordIdsPart
						."\n".'   OR '.$db->nameQuote('vwv').'.'.$db->nameQuote('tag').$wordIdsPart.' )';
							
						$joins['vwv'] = 'INNER JOIN '.$db->nameQuote( 'video_words_view' ).' AS '.$db->nameQuote('vwv')
						."\n".'   ON '.$db->nameQuote('vwv').'.'.$db->nameQuote('video_id').' = '.$db->nameQuote('vid').'.'.$db->nameQuote('id');
					}
					elseif( $col == 'searched_tag' ) {
						$wheres[] = '( '.$db->nameQuote('vwt').'.'.$db->nameQuote('word_id').$wordIdsPart.' )';
							
						$joins['vwv'] = 'INNER JOIN '.$db->nameQuote( 'video_words_tag' ).' AS '.$db->nameQuote('vwt')
						."\n".'   ON '.$db->nameQuote('vwt').'.'.$db->nameQuote('video_id').' = '.$db->nameQuote('vid').'.'.$db->nameQuote('id');
					}
					break;
					
				case( 'searched_mod' ):
					$wheres[] = $db->nameQuote('vid').'.'.$db->nameQuote('status').$assignPart;
					break;
				}
			}
			
			if( $init ) {
				$selects[] = $db->nameQuote('vid').'.*';
			}
			else {
				$selects[] = $db->nameQuote('vid').'.'.$db->nameQuote('id');
			}
			
			if( !is_null($orders) ) {
				$orderBy = array();
				foreach( $orders as $orderOn=>$orderDir ) {
					if( $orderDir == 'a' ) {
						$orderDir = 'ASC';
					}
					elseif( $orderDir == 'd' ) {
						$orderDir = 'DESC';
					}
					switch( $orderOn ) {
					case( 'view_count'):
						$viewCount = true;
						$orderBy[] = $orderOn.' '.$orderDir;
						break;
					
					case( 'relevance' ):
						// get the weighting for the search
						$params = &JComponentHelper::getParams( 'com_arc_tv' );
						$titleWeight = $params->get( 'title_weight' );
						$descWeight  = $params->get( 'desc_weight' );
						$tagWeight   = $params->get( 'tag_weight' );
						
						if( isset($requirements['searched']) ) {
							$selects[] = $db->nameQuote('vwv').'.'.$db->nameQuote('video_id').','
								.' ( SUM( IF( '.$db->nameQuote('vwv').'.'.$db->nameQuote('title').' IS NULL, 0, '.$titleWeight.') )'
								.' + SUM( IF( '.$db->nameQuote('vwv').'.'.$db->nameQuote('desc').' IS NULL, 0, '.$descWeight.') )'
								.' + SUM( IF( '.$db->nameQuote('vwv').'.'.$db->nameQuote('tag').' IS NULL, 0, '.$tagWeight.') ) ) AS '.$db->nameQuote('score');
							
							$joins['vwv'] = 'INNER JOIN '.$db->nameQuote( 'video_words_view' ).' AS '.$db->nameQuote('vwv')
								."\n".'   ON '.$db->nameQuote('vwv').'.'.$db->nameQuote('video_id').' = '.$db->nameQuote('vid').'.'.$db->nameQuote('id');
							
							$groups[] = $db->nameQuote('vwv').'.'.$db->nameQuote('video_id');
						}
						elseif( isset($requirements['searched_tag']) ) {
							$selects[] = $db->nameQuote('vwt').'.'.$db->nameQuote('video_id').','
								.' SUM( IF( '.$db->nameQuote('vwt').'.'.$db->nameQuote('word_id').' IS NULL, 0, 1) ) AS '.$db->nameQuote('score');
							
							$joins['vwv'] = 'INNER JOIN '.$db->nameQuote( 'video_words_tag' ).' AS '.$db->nameQuote('vwt')
								."\n".'   ON '.$db->nameQuote('vwt').'.'.$db->nameQuote('video_id').' = '.$db->nameQuote('vid').'.'.$db->nameQuote('id');
							
							$groups[] = $db->nameQuote('vwt').'.'.$db->nameQuote('video_id');
						}
						$orderBy[] = $db->nameQuote('score').' '.$orderDir;
						break;
					}
				}
			}
			
			if( $viewCount ) {
				$tmpCountsTable = $db->nameQuote('views_counts');
				$joins['vdate'] = 'INNER JOIN '.$tmpCountsTable
					."\n".'   ON '.$tmpCountsTable.'.'.$db->nameQuote('video_id').' = '.$db->nameQuote('vid').'.'.$db->nameQuote('id');
				
				$countsQuery = 'CREATE TEMPORARY TABLE '.$tmpCountsTable.' AS'
					."\n".'SELECT '.$db->nameQuote('video_id').', COUNT( DISTINCT '.$db->nameQuote( 'person_id' ).' ) AS '.$db->nameQuote('view_count')
					."\n".'FROM '.$db->nameQuote('views')
					.( empty($whereCounts) ? '' : "\n".'WHERE '.implode("\n".'  AND ', $whereCounts) )
					."\n".'GROUP BY '.$db->nameQuote('video_id');
				$db->setQuery( $countsQuery );
				$db->Query();
			}
			
			$query = 'SELECT '.implode(', ', $selects)
				."\n".'FROM '.$db->nameQuote('videos').' AS '.$db->nameQuote('vid')
				.( empty($joins) ? '' : "\n".implode("\n", $joins) )
				.( empty($wheres) ? '' : "\n".'WHERE '.implode("\n".'  AND ', $wheres) )
				.( empty($groups) ? '' : "\n".'GROUP BY '.implode(', ', $groups) )
				.( empty($orderBy) ? '' : "\n".'ORDER BY '.implode(', ', $orderBy) );
			$db->setQuery( $query );
			$data = $db->loadAssocList( 'id' );
			
			$ids = array_keys( $data );
			$this->_addInstances( $sId, $ids );
			
			if( $init ) {
				$existing = $this->_getInstances();
				$newIds = array_diff( $ids, $existing );
				
				// initialise and cache
				foreach( $newIds as $id ) {
					$r = new ApothTvVideoVideo( $data[$id] );
					$r->setScriptsUrl( $this->getParam('video_scripts') );
					$this->_addInstance( $id, $r );
					unset( $r );
				}
			}
		}
		
		return $ids;
	}
	
	/**
	 * Retrieve the most used tags
	 * 
	 *  @param int $tagCloudSize  The number of tags to return
	 *  @return array  Array of tag id, tag and tag count
	 */
	function getTagCloud( $tagCloudSize = null )
	{
		if( is_null($tagCloudSize) ) {
			$tagCloudSize = 20;
		}
		
		$db = &self::getVidDBO();
		$query = 'SELECT '.$db->nameQuote('words').'.'.$db->nameQuote('id').', '.$db->nameQuote('words').'.'.$db->nameQuote('word').', '.$db->nameQuote('tags').'.'.$db->nameQuote('count')
			."\n".'FROM '.$db->nameQuote('tags')
			."\n".'INNER JOIN '.$db->nameQuote('words')
			."\n".'   ON '.$db->nameQuote('words').'.'.$db->nameQuote('id').' = '.$db->nameQuote('tags').'.'.$db->nameQuote('word_id')
			."\n".'WHERE '.$db->nameQuote('count').' > 0'
			."\n".'ORDER BY '.$db->nameQuote('tags').'.'.$db->nameQuote('count').' DESC'
			."\n".'LIMIT '.(int)$tagCloudSize;
		$db->setQuery( $query );
		$data = $db->loadAssocList('id');
		
		return $data;
	}
	
	/**
	 * Retrieve the available formats for the specified video
	 * 
	 * @param int $vidId  The ID of the video
	 * @return array $formats  An array of video formats. Key of encoding, each with sub-array of resolutions
	 */
	function getInstanceFormats( $vidId ) {
		$db = &self::getVidDBO();
		$query = 'SELECT '.$db->nameQuote('enc').', '.$db->nameQuote('res')
			."\n".'FROM '.$db->nameQuote('videos_format')
			."\n".'WHERE '.$db->nameQuote('video_id').' = '.$db->Quote($vidId);
		$db->setQuery( $query );
		$tmp = $db->loadAssocList();
		
		$formats = array();
		foreach( $tmp as $info ) {
			$formats[$info['res']][] = $info['enc'];
		}
		
		return $formats;
	}
	
	/**
	 * Retrieve the tags for the specified video
	 *
	 * @param int $vidId  The ID of the video
	 * @return array $tags  An array of associated tags indexed on word id
	 */
	function getInstanceTags( $vidId )
	{
		$db = &self::getVidDBO();
		$query = 'SELECT '.$db->nameQuote('words').'.'.$db->nameQuote('id').', '.$db->nameQuote('words').'.'.$db->nameQuote('word')
			."\n".'FROM '.$db->nameQuote('video_words_tag').' AS '.$db->nameQuote('vwt')
			."\n".'INNER JOIN '.$db->nameQuote('words')
			."\n".'   ON '.$db->nameQuote('words').'.'.$db->nameQuote('id').' = '.$db->nameQuote('vwt').'.'.$db->nameQuote('word_id')
			."\n".'WHERE '.$db->nameQuote('vwt').'.'.$db->nameQuote('video_id').' = '.$db->Quote($vidId)
			."\n".'ORDER BY '.$db->nameQuote('words').'.'.$db->nameQuote('word').' ASC';
		$db->setQuery( $query );
		$tags = $db->loadAssocList( 'id' );
		
		return $tags;
	}
	
	/**
	 * Retrieve the roles for the specified video
	 *
	 * @param int $vidId  The ID of the video
	 * @return array $roles  An array of associated roles indexed on person
	 */
	function getInstanceRoles( $vidId )
	{
		$db = &self::getVidDBO();
		$query = 'SELECT '.$db->nameQuote('roles').'.'.$db->nameQuote('id').', '.$db->nameQuote('roles').'.'.$db->nameQuote('role').', '.$db->nameQuote('vr').'.'.$db->nameQuote('site_id').', '.$db->nameQuote('vr').'.'.$db->nameQuote('person_id')
			."\n".'FROM '.$db->nameQuote('video_roles').' AS '.$db->nameQuote('vr')
			."\n".'INNER JOIN '.$db->nameQuote('roles')
			."\n".'   ON '.$db->nameQuote('roles').'.'.$db->nameQuote('id').' = '.$db->nameQuote('vr').'.'.$db->nameQuote('role_id')
			."\n".'WHERE '.$db->nameQuote('vr').'.'.$db->nameQuote('video_id').' = '.$db->Quote($vidId)
			."\n".'ORDER BY '.$db->nameQuote('roles').'.'.$db->nameQuote('role').' ASC';
		$db->setQuery( $query );
		$roles = $db->loadAssocList();
		
		return $roles;
	}
	
	/**
	 * Retrieve the filters for the specified video
	 *
	 * @param int $vidId  The ID of the video
	 * @return array $filters  An array of associated filters indexed on filter type
	 */
	function getInstanceFilters( $vidId )
	{
		$db = &JFactory::getDBO();
		$filters = array();
		
		// people
		$query = 'SELECT '.$db->nameQuote('person_id')
			."\n".'FROM '.$db->nameQuote('#__apoth_tv_access_people')
			."\n".'WHERE '.$db->nameQuote('video_id').' = '.$db->Quote($vidId);
		$db->setQuery( $query );
		$filters['people'] = $db->loadResultArray();
		
		// groups
		$query = 'SELECT '.$db->nameQuote('group_id')
			."\n".'FROM '.$db->nameQuote('#__apoth_tv_access_groups')
			."\n".'WHERE '.$db->nameQuote('video_id').' = '.$db->Quote($vidId);
		$db->setQuery( $query );
		$filters['groups'] = $db->loadResultArray();
		
		// years
		$query = 'SELECT '.$db->nameQuote('year')
			."\n".'FROM '.$db->nameQuote('#__apoth_tv_access_years')
			."\n".'WHERE '.$db->nameQuote('video_id').' = '.$db->Quote($vidId);
		$db->setQuery( $query );
		$filters['years'] = $db->loadResultArray();
		
		return $filters;
	}
	
	/**
	 * Retrieve all the tags
	 *
	 * @return array $tags  An array of all tags indexed on word id
	 */
	function getAllTags()
	{
		$db = &self::getVidDBO();
		$query = 'SELECT '.$db->nameQuote('words').'.'.$db->nameQuote('id').', '.$db->nameQuote('words').'.'.$db->nameQuote('word')
			."\n".'FROM '.$db->nameQuote('video_words_tag').' AS '.$db->nameQuote('vwt')
			."\n".'INNER JOIN '.$db->nameQuote('words')
			."\n".'   ON '.$db->nameQuote('words').'.'.$db->nameQuote('id').' = '.$db->nameQuote('vwt').'.'.$db->nameQuote('word_id')
			."\n".'ORDER BY '.$db->nameQuote('words').'.'.$db->nameQuote('word').' ASC';
		$db->setQuery( $query );
		$tags = $db->loadAssocList( 'id' );
		
		return $tags;
	}
	
	/**
	 * Retrieve all the roles
	 *
	 * @return array $roles  An array of all roles indexed on role id
	 */
	function getAllRoles()
	{
		$db = &self::getVidDBO();
		$query = 'SELECT '.$db->nameQuote('id').', '.$db->nameQuote('role')
			."\n".'FROM '.$db->nameQuote('roles')
			."\n".'ORDER BY '.$db->nameQuote('role').' ASC';
		$db->setQuery( $query );
		$roles = $db->loadAssocList( 'id' );
		
		return $roles;
	}
	
	/**
	 * Given a list of words, return the corresponding existing IDs
	 * or save unknown words and return new IDs
	 * 
	 * @param array $words  Array of words whose IDs we want
	 * @param boolean $create  Should we optionally save unknown words?
	 * @return array  Array of corresponding word IDs
	 */
	function getWordIds( $words, $create = false )
	{
		return $this->_getTokenIds( 'word', 'words', $words, $create );
	}
	
	/**
	 * Given a list of roles, return the corresponding existing IDs
	 * or save unknown roles and return new IDs
	 * 
	 * @param array $roles  Array of roles whose IDs we want
	 * @param boolean $create  Should we optionally save unknown roles?
	 * @return array  Array of corresponding role IDs
	 */
	function getRoleIds( $roles, $create = false )
	{
		return $this->_getTokenIds( 'role', 'roles', $roles, $create );
	}
	
	/**
	 * Helper function to find IDs for given tokens and optionally create them if new
	 * 
	 * @param string $field  DB table field name
	 * @param string $table  DB table name
	 * @param array $tokens  Array of token strings whose IDs we want
	 * @param boolean $create  Should we optionally save unknown tokens?
	 * @return array $ids  Array of corresponding token IDs
	 */
	function _getTokenIds( $field, $table, $tokens, $create ) {
		$db = &self::getVidDBO();
		
		// DB quote the tokens
		$quotedTokens = array();
		foreach( $tokens as $token ) {
			$quotedTokens[] = $db->Quote( $token );
		}
		$quotedTokens = implode( ', ', $quotedTokens );
		
		// pull out existing tokens
		$query = 'SELECT '.$db->nameQuote('id').', '.$db->nameQuote($field)
			."\n".'FROM '.$db->nameQuote($table)
			."\n".'WHERE '.$db->nameQuote($field).' IN ('.$quotedTokens.')';
		$db->setQuery( $query );
		$rawIds = $db->loadAssocList($field);
		
		// cross reference found tokens with input list to find new tokens
		// and start building the return data
		$ids = array();
		$newTokens = array();
		foreach( $tokens as $token ) {
			if( isset($rawIds[$token]) ) {
				$ids[$token] = $rawIds[$token]['id'];
			}
			else {
				$ids[$token] = null;
				$newTokens[] = $token;
			}
		}
		
		// check if we are creating new tokens and have any to save
		if( $create && !empty($newTokens) ) {
			// DB quote the new tokens
			$quotedNew = array();
			foreach( $newTokens as $newToken ) {
				$quotedNew[] = '('.$db->Quote( $newToken ).')';
			}
			$quotedNew = implode( ', ', $quotedNew );
			
			// insert new tokens
			$query = 'INSERT INTO '.$db->nameQuote($table).' ('.$db->nameQuote($field).')'
				."\n".'VALUES '.$quotedNew;
			$db->setQuery( $query );
			$db->Query();
			$insertId = $db->insertid();
			
			// update return data with new IDs for the new tokens
			foreach( $newTokens as $newToken ) {
				$ids[$newToken] = $insertId++;
			}
		}
		
		return $ids;
	}
	
	/**
	 * Get a list of the top 10 videos for a given user based on their views record 
	 * 
	 * @param int $siteId  The site ID for the current user
	 * @param string $pId  The site ID for the current user
	 * @return array  Array of video ID, most viewed first
	 */
	function getUserFaves( $siteId, $pId )
	{
	// **** initially, 'recommended for you' for a person will be a 'related' search
	// **** based on the the top 10 videos the person has viewed, we get that top 10 here.
	// **** ultimately it will be besed on the top x videos that they have rated
	
		$db = &self::getVidDBO();
		$query = 'SELECT '.$db->nameQuote('video_id').', COUNT(*) AS '.$db->nameQuote('video_views')
			."\n".'FROM '.$db->nameQuote('views')
			."\n".'WHERE '.$db->nameQuote('site_id').' = '.$db->Quote( $siteId )
			."\n".'  AND '.$db->nameQuote('person_id').' = '.$db->Quote( $pId )
			."\n".'GROUP BY '.$db->nameQuote('video_id')
			."\n".'ORDER BY '.$db->nameQuote('video_views').' DESC'
			."\n".'LIMIT 10';
		$db->setQuery($query);
		$rawViews = $db->loadAssocList('video_id');
		
		return array_keys( $rawViews );
	}
	
	/**
	 * Get the status log and optionally status comments for a given video
	 * 
	 * @param int $id  The ID of the video in question
	 * @param boolean $withComment  Do we want only entries with comments?
	 * @return array $statusLog  Array of status log entries, newest first
	 */
	function getStatusLog( $id, $withComment = false )
	{
		$db = &self::getVidDBO();
		$query = 'SELECT *'
			."\n".'FROM '.$db->nameQuote('video_status_log')
			."\n".'WHERE '.$db->nameQuote('video_id').' = '.$db->Quote($id)
			.( $withComment ? "\n".'  AND '.$db->nameQuote('comment').' IS NOT NULL' : '' )
			."\n".'ORDER BY '.$db->nameQuote( 'date' ).' DESC';
		$db->setQuery( $query );
		$statusLog = $db->loadAssocList();
		
		return $statusLog;
	}
	
	/**
	 * Increment the number of views for the specified video
	 *
	 * @param string $id  The ID the video
	 * @param string $siteId  The site ID for the current installation
	 * @param string $person  The ARC ID of the person viewing the video
	 *
	 * @return mixed  A database resource if successful, otherwise false
	 */
	function addVidView( $id, $siteId, $person )
	{
		$db = &self::getVidDBO();
		$query = 'INSERT INTO '.$db->nameQuote('views')
		."\n".'VALUES'
		."\n".'(NULL, '.$db->Quote($id).', '.$db->Quote($siteId).', '.$db->Quote($person).', NOW() )';
		$db->setQuery($query);
	
		return $db->query();
	}
	
	/**
	 * Get the upload date of the specified video
	 * 
	 * @param int $id  The ID of the video whose upload date we want
	 * @return string  Upload date
	 */
	function getUploadDate( $id )
	{
		$db = &self::getVidDBO();
		$query = 'SELECT '.$db->nameQuote('date')
			."\n".'FROM '.$db->nameQuote('video_status_log')
			."\n".'WHERE '.$db->nameQuote('video_id').' = '.$db->Quote($id)
			."\n".'ORDER BY '.$db->nameQuote('date').' ASC'
			."\n".'LIMIT 1';
		$db->setQuery( $query );
		
		return $db->loadResult();
	}
	
	/**
	 * Commits the instance to the db,
	 * updates the cached instance,
	 * clears the search cache if we've added a new instance
	 *  (the newly created instance may match any of the searches we previously executed)
	 * 
	 * @param int $id  Unique identifer
	 * @return boolean $primary  Indication of the success of the commit
	 */
	function commitInstance( $id )
	{
		$r = &$this->_getInstance( $id );
		if( is_null($r) ) {
			return false;
		}
		$db = &self::getVidDBO();
		$isNew = $r->getId() < 0;
		
		if( $isNew ) {
			$query = 'INSERT INTO '.$db->nameQuote('videos');
			$where = '';
		}
		else {
			$query = 'UPDATE '.$db->nameQuote('videos');
			$where = 'WHERE '.$db->nameQuote('id').' = '.$db->Quote($id);
		}
		
		// primary save
		$query = $query
			."\n".'SET'
			."\n  ".$db->nameQuote('title')            .' = '.$db->Quote( $r->getDatum('title') )
			."\n, ".$db->nameQuote('desc')             .' = '.$db->Quote( $r->getDatum('desc') )
			."\n, ".$db->nameQuote('length')           .' = '.$db->Quote( $r->getDatum('length') )
			."\n, ".$db->nameQuote('site_id')          .' = '.$db->Quote( $r->getDatum('site_id') )
			."\n, ".$db->nameQuote('person_id')        .' = '.$db->Quote( $r->getDatum('person_id') )
			."\n, ".$db->nameQuote('last_modified_by') .' = '.$db->Quote( $r->getDatum('last_modified_by') )
			."\n, ".$db->nameQuote('status')           .' = '.$db->Quote( $r->getDatum('status') )
			."\n  ".$where;
		$db->setQuery( $query );
		$primary = $db->query();
		
		// check the primary save was OK, if not we stop here
		if( !$primary ) {
			$this->setErrMsg( 'There was an error saving the basic video details. Please try again.' );
			return false;
		}
		
		// update the object if necessary
		$oldId = $id;
		if( $isNew ) {
			$id = $db->insertid();
			$r->setId( $id );
		}
		
		// comments
		$c = $r->getAndClearStatusComment();
		if( !is_null($c) ) {
			$query = 'CREATE TEMPORARY TABLE '.$db->nameQuote('tmp_rt').' AS'
				."\n".'SELECT '.$db->nameQuote('video_id').', '.$db->nameQuote('site_id').', '.$db->nameQuote('person_id').', MAX( '.$db->nameQuote('date').' ) AS '.$db->nameQuote('date')
				."\n".'FROM '.$db->nameQuote('video_status_log')
				."\n".'WHERE '.$db->nameQuote('video_id').' = '.$db->Quote($id)
				."\n".'  AND '.$db->nameQuote('site_id').' = '.$db->Quote($r->getDatum('site_id'))
				."\n".'  AND '.$db->nameQuote('person_id').' = '.$db->Quote($r->getDatum('last_modified_by'))
				."\n".'GROUP BY '.$db->nameQuote('video_id').';'
				."\n"
				."\n".'UPDATE '.$db->nameQuote('video_status_log').' AS '.$db->nameQuote('l')
				."\n".'INNER JOIN '.$db->nameQuote('tmp_rt').' AS '.$db->nameQuote('rt')
				."\n".'   ON '.$db->nameQuote('rt').'.'.$db->nameQuote('video_id').' = '.$db->nameQuote('l').'.'.$db->nameQuote('video_id')
				."\n".'  AND '.$db->nameQuote('rt').'.'.$db->nameQuote('site_id').' = '.$db->nameQuote('l').'.'.$db->nameQuote('site_id')
				."\n".'  AND '.$db->nameQuote('rt').'.'.$db->nameQuote('person_id').' = '.$db->nameQuote('l').'.'.$db->nameQuote('person_id')
				."\n".'  AND '.$db->nameQuote('rt').'.'.$db->nameQuote('date').' = '.$db->nameQuote('l').'.'.$db->nameQuote('date')
				."\n".'SET '.$db->nameQuote('l').'.'.$db->nameQuote('comment').' = '.$db->Quote($c).';';
			$db->setQuery( $query );
			$ok = $db->queryBatch();
			
			if( !$ok ) {
				$this->setErrMsg( 'There was an error saving the comments associated with this status change.' );
			}
		}
		
		// update the factory
		$this->_clearCachedInstances( $oldId );
		$this->_clearCachedSearches();
		
		// title indexing
		if( !is_null($r->_titleIndex) ) {
			// delete existing title indices for this video
			$query = 'DELETE FROM '.$db->nameQuote('video_words_title')
				."\n".'WHERE '.$db->nameQuote('video_id').' = '.$db->Quote($id);
			$db->setQuery( $query );
			$ok = $db->query();
			
			// if all ok process title indices if there are any
			if( $ok ) {
				$titleIndex = $r->getTitleIndex();
				if( !empty($titleIndex) ) {
					$quotedTitleValues = array();
					foreach( $titleIndex as $titleIndexId ) {
						$quotedTitleValues[] = '( '.$db->Quote($id).', '.$db->Quote($titleIndexId).' )';
					}
					$quotedTitleValues = implode( ', ', $quotedTitleValues );
					
					$query = 'INSERT INTO '.$db->nameQuote('video_words_title')
						."\n".'VALUES '.$quotedTitleValues;
					$db->setQuery( $query );
					$ok = $db->query();
				}
			}
			
			if( !$ok ) {
				$this->setErrMsg( 'There was an error saving the title indices for this video.' );
			}
		}
		
		// desc indexing
		if( !is_null($r->_descIndex) ) {
			// delete existing desc indices for this video
			$query = 'DELETE FROM '.$db->nameQuote('video_words_desc')
				."\n".'WHERE '.$db->nameQuote('video_id').' = '.$db->Quote($id);
			$db->setQuery( $query );
			$ok = $db->query();
			
			// if all ok process description indices if there are any
			if( $ok ) {
				$descIndex = $r->getDescIndex();
				if( !empty($descIndex) ) {
					$quotedDescValues = array();
					foreach( $descIndex as $descIndexId ) {
						$quotedDescValues[] = '( '.$db->Quote($id).', '.$db->Quote($descIndexId).' )';
					}
					$quotedDescValues = implode( ', ', $quotedDescValues );
					
					$query = 'INSERT INTO '.$db->nameQuote('video_words_desc')
						."\n".'VALUES '.$quotedDescValues;
					$db->setQuery( $query );
					$ok = $db->query();
				}
			}
			
			if( !$ok ) {
				$this->setErrMsg( 'There was an error saving the description indices for this video.' );
			}
		}
		
		// tags
		if( !is_null($r->_tags) ) {
			// delete existing tags for this video
			$query = 'DELETE FROM '.$db->nameQuote('video_words_tag')
				."\n".'WHERE '.$db->nameQuote('video_id').' = '.$db->Quote($id);
			$db->setQuery( $query );
			$ok = $db->query();
			
			// if all ok process tags if there are any
			if( $ok ) {
				$tags = $r->getTags();
				if( !empty($tags) ) {
					$quotedTagValues = array();
					foreach( $tags as $tagId ) {
						$quotedTagValues[] = '( '.$db->Quote($id).', '.$db->Quote($tagId).' )';
					}
					$quotedTagValues = implode( ', ', $quotedTagValues );
					
					$query = 'INSERT INTO '.$db->nameQuote('video_words_tag')
						."\n".'VALUES '.$quotedTagValues;
					$db->setQuery( $query );
					$ok = $db->query();
				}
			}
			
			if( !$ok ) {
				$this->setErrMsg( 'There was an error saving the tags for this video.' );
			}
		}
		
		// roles
		if( !is_null($r->_roles) ) {
			// delete existing roles for this video
			$query = 'DELETE FROM '.$db->nameQuote('video_roles')
			."\n".'WHERE '.$db->nameQuote('video_id').' = '.$db->Quote($id);
			$db->setQuery( $query );
			$ok = $db->query();
			
			// if all ok process roles if there are any
			if( $ok ) {
				$roles = $r->getRoles();
				if( !empty($roles) ) {
					$quotedRoleValues = array();
					foreach( $roles as $roleArray ) {
						$tmpQuotedInfo = array();
						$tmpQuotedInfo['video_id'] = $db->Quote($id);
						$tmpQuotedInfo['role_id'] = $db->Quote($roleArray['id']);
						$tmpQuotedInfo['site_id'] = $db->Quote($roleArray['site_id']);
						$tmpQuotedInfo['person_id'] = $db->Quote($roleArray['person_id']);
						
						$quotedRoleValues[] = '( '.implode( ', ', $tmpQuotedInfo).' )';
					}
					$quotedRoleValues = implode( ', ', $quotedRoleValues );
					
					$query = 'INSERT INTO '.$db->nameQuote('video_roles')
						."\n".'VALUES '.$quotedRoleValues.';';
					$db->setQuery( $query );
					$ok = $db->query();
					
				} 
			}
			
			if( !$ok ) {
				$this->setErrMsg( 'There was an error saving the credits for this video.' );
			}
		}
		
		// filters
		if( !is_null($r->_filters) ) {
			$dbj = &JFactory::getDBO();
			
			// loop through each filter type and process it
			foreach( $r->getFilters() as $filter=>$filterArray ) {
				// delete existing filters
				$query = 'DELETE FROM '.$dbj->nameQuote('#__apoth_tv_access_'.$filter)
					."\n".'WHERE '.$dbj->nameQuote('video_id').' = '.$dbj->Quote($id);
				$dbj->setQuery( $query );
				$ok = $dbj->query();
				
				// if all ok process filters if there are any
				if( $ok ) {
					if( is_array($filterArray) && !empty($filterArray) ) {
						$quotedValues = array();
						foreach( $filterArray as $value ) {
							$quotedValues[] = '( '.$dbj->Quote($id).', '.$dbj->Quote($value).' )';
						}
						$quotedValues = implode( ', ', $quotedValues );
						
						$query = 'INSERT INTO '.$dbj->nameQuote('#__apoth_tv_access_'.$filter)
							."\n".'VALUES '.$quotedValues.';';
						$dbj->setQuery( $query );
						$ok = $dbj->query();
						
					} 
				}
				
				if( !$ok ) {
					$this->setErrMsg( 'There was an error saving the access filters for this video.'.$dbj->getErrorMsg() );
					break;
				}
			}
		}
		
		// refresh the permissions tables
		$u = ApotheosisLib::getUser();
		ApotheosisLibDbTmp::flush( $u->id );
		
		// report status of primary commit
		return $primary;
	}
}


/**
 * Tv Video Object
 */
class ApothTvVideoVideo extends JObject
{
	/**
	 * All the data for this video (equates to a row in the db)
	 * @access protected
	 * @var array
	 */
	var $_core = array();
	
	function __construct( $data )
	{
		$this->_core = $data;
		$this->_scriptsUrl = '';
		$this->_formats = null;
		$this->_titleIndex = null;
		$this->_descIndex = null;
		$this->_tags = null;
		$this->_roles = null;
		$this->_filters = null;
		$this->_res = null;
		$this->_statusComment = null;
		$this->_statusCommentLog = null;
	}
	
	/**
	 * Set the value of the video server scripts url
	 *
	 * @param string $scriptsUrl  The url of the video server scripts
	 */
	function setScriptsUrl( $scriptsUrl )
	{
		$this->_scriptsUrl = $scriptsUrl;
	}
	
	/**
	 * Accessor function to retrieve id
	 * 
	 * @return int  The id for this object
	 */
	function getId()
	{
		return $this->_core['id'];
	}
	
	/**
	 * Set the ID for this video
	 * 
	 * @param int $id
	 */
	function setId( $id )
	{
		$this->_core['id'] = (int)$id;
	}
	
	/**
	 * Accessor function to retrieve core data
	 * 
	 * @param string $key  The name of the value we want
	 * @return mixed  The value requested
	 */
	function getDatum( $key )
	{
		return ( isset($this->_core[$key]) ? $this->_core[$key] : null );
	}
	
	/**
	 * Set the value of a core data field
	 * 
	 * @param string $key  The field whose value we wish to set
	 * @param mixed $val  The value we wish to set
	 */
	function setDatum( $key, $val )
	{
		$this->_core[$key] = $val;
	}
	
	/**
	 * Retrieve the available formats for this video
	 * 
	 * @return array  An array of video formats. Key of resolution, each with sub-array of encodings
	 */
	function getFormats()
	{
		if( is_null($this->_formats) ) {
			$fVideo = &ApothFactory::_( 'tv.video' );
			$this->_formats = $fVideo->getInstanceFormats( $this->getId() );
		}
		
		return $this->_formats;
	}
	
	/**
	 * Retrieve the available formats for this video at the set resolution
	 * 
	 * @return array  An array of video formats
	 */
	function getFormatsAtRes()
	{
		if( is_null($this->_formats) ) {
			$fVideo = &ApothFactory::_( 'tv.video' );
			$this->_formats = $fVideo->getInstanceFormats( $this->getId() );
		}
		
		$res = $this->getRes();
		return $this->_formats[$res];
	}
	
	/**
	 * Retrieve all the title words for indexing for this video
	 * 
	 * @return array  An array of the title words associated with this video
	 */
	function getTitleIndex()
	{
		return $this->_titleIndex;
	}
	
	/**
	 * Set all the title words for indexing for this video
	 * 
	 * @param array $titleIds  The title word IDs for this video
	 */
	function setTitleIndex( $titleIds )
	{
		$this->_titleIndex = $titleIds;
	}
	
	/**
	 * Retrieve all the description words for indexing for this video
	 * 
	 * @return array  An array of the description words associated with this video
	 */
	function getDescIndex()
	{
		return $this->_descIndex;
	}
	
	/**
	 * Set all the description words for indexing for this video
	 * 
	 * @param array $descIds  The description word IDs for this video
	 */
	function setDescIndex( $descIds )
	{
		$this->_descIndex = $descIds;
	}
	
	/**
	 * Retrieve all the tags associated with this video
	 * 
	 * @return array  An array of the tags associated with this video
	 */
	function getTags()
	{
		if( is_null($this->_tags) ) {
			$fVideo = &ApothFactory::_( 'tv.video' );
			$this->_tags = $fVideo->getInstanceTags( $this->getId() );
		}
		
		return $this->_tags;
	}
	
	/**
	 * Set the tags associated with this video
	 * 
	 * @param array $tags  The tag IDs for this video
	 */
	function setTags( $tags )
	{
		$this->_tags = $tags;
	}
	
	/**
	 * Retrieve all the roles associated with this video
	 * 
	 * @return array  An array of the roles associated with this video
	 */
	function getRoles()
	{
		if( is_null($this->_roles) ) {
			$fVideo = &ApothFactory::_( 'tv.video' );
			$this->_roles = $fVideo->getInstanceRoles( $this->getId() );
		}
		
		return $this->_roles;
	}
	
	/**
	 * Set the roles associated with this video
	 * 
	 * @param array $roles  The role IDs for this video
	 */
	function setRoles( $roles )
	{
		$this->_roles = $roles;
	}
	
	/**
	 * Retrieve all the filters associated with this video
	 * 
	 * @return array  An array of the filters associated with this video
	 */
	function getFilters()
	{
		if( is_null($this->_filters) ) {
			$fVideo = &ApothFactory::_( 'tv.video' );
			$this->_filters = $fVideo->getInstanceFilters( $this->getId() );
		}
		
		return $this->_filters;
	}
	
	/**
	 * Set the filters associated with this video
	 * 
	 * @param array $roles  The filters for this video
	 */
	function setFilters( $filters )
	{
		$this->_filters = $filters;
	}
	
	/**
	 * Set the status for the current video
	 * 
	 * @param int $status  The status to set
	 * @param string|null $comment  The associated comment or null if no comment
	 */
	function setStatus( $status, $comment = null )
	{
		// determine result of status changes (or not)
		if( $this->_core['status'] != $status ) {
			$this->_statusChanged = true;
			if( !is_null($comment) ) {
				$this->_statusComment = $comment;
			}
		}
		else {
			$this->_statusChanged = false;
		}
		
		// if status has changed, empty the comment log
		if( $this->_statusChanged ) {
			$this->_statusCommentLog = null;
		}
		
		// set the new status
		$this->_core['status'] = $status;
	}
	
	/**
	 * Return currently set status comment then reset it to null
	 * 
	 * @return string|null $retVal  The currently set comment or null if no comment is set
	 */
	function getAndClearStatusComment()
	{
		$retVal = ( !is_null($this->_statusComment) ? $this->_statusComment : null );
		$this->_statusComment = null;
		
		return $retVal;
	}
	
	/**
	 * Retrieve the video resolution
	 * 
	 * @return int  The current desired resolution
	 */
	function getRes()
	{
		if( is_null($this->_res) ) {
			$this->setRes( 360 );
		}
		
		return $this->_res;
	}
	
	/**
	 * Set the video resolution
	 */
	function setRes( $res )
	{
		$this->_res = $res;
	}
	
	/**
	 * Get the upload date of the video
	 */
	function getUploadDate()
	{
		$fVideo = &ApothFactory::_( 'tv.video' );
		
		return $fVideo->getUploadDate( $this->getId() );
	}
	
	/**
	 * Get a thumbnail image url for this video
	 * 
	 * @param int  $thumbNum  Thumbnail number
	 * @return string   The complete url of a video thumbnail image
	 */
	function getThumbnail( $thumbNum = null )
	{
		return $this->_scriptsUrl.'/thumbnail.php?vidId='.$this->getId().'&thumbId='.$thumbNum;
	}
	
	/**
	 * Get a poster image url for this video
	 * 
	 * @return string  The complete url of a video poster image
	 */
	function getPoster()
	{
		return $this->_scriptsUrl.'/poster.php?vidId='.$this->getId().'&res='.$this->getRes();
	}
	
	/**
	 * Get a poster image url (in URL Encoded format) for this video
	 * 
	 * @return string  The complete encoded url of a video poster image
	 */
	function getURLEncPoster()
	{
		return $this->_scriptsUrl.'/poster.php%3FvidId%3D'.$this->getId().'%26res%3D'.$this->getRes();
	}
	
	/**
	 * Get a video url for this video with specified encoding
	 * 
	 * @param string $enc  The required video encoding
	 * @return string  The complete URL for this video in the requested encoding 
	 */
	function getVideo( $enc )
	{
		return $this->_scriptsUrl.'/video.php?vidId='.$this->getId().'&res='.$this->getRes().'&enc='.$enc;
	}
	
	/**
	 * Get a video url (in URL Encoded Format) for this video with specified encoding
	 * 
	 * @param string $enc  The required video encoding
	 * @return string  The complete encoded URL for this video in the requested encoding 
	 */
	function getURLEncVideo( $enc )
	{
		return $this->_scriptsUrl.'/video.php%3FvidId%3D'.$this->getId().'%26res%3D'.$this->getRes().'%26enc%3D'.$enc;
	}
	
	/**
	 * Get the flash player swf url
	 * 
	 * @return string  The complete url of the flash player swf
	 */
	function getFlashPlayer()
	{
		return JURI::Base().'components'.DS.'com_arc_tv'.DS.'views'.DS.'video'.DS.'tmpl'.DS.'player.swf';
	}
	
	/**
	 * Get the url to receive the file upload
	 * 
	 * @return string  The complete url of a video file receiver
	 */
	function getUpload()
	{
		return $this->_scriptsUrl.'/upload.php';
	}
	
	/**
	 * Get the url to find the file upload progress
	 * 
	 * @return string  The complete url of a video upload progress indicator
	 */
	function getProgress()
	{
		// the progress data script is on another server
		// the script named here wraps around a curl request to that remote script
		return JURI::Base().'components'.DS.'com_arc_tv'.DS.'views'.DS.'video'.DS.'tmpl'.DS.'progress.php?server='.urlencode( $this->_scriptsUrl ).'&vidId='.$this->getId().'&uploadId=~UPLOADID~';
	}
	
	/**
	 * Determine what status info is associated with this video
	 * 
	 * @return array $info  The current status info
	 */
	function getStatusInfo()
	{
		// get status log
		if( is_null($this->_statusCommentLog) ) {
			$fVideo = &ApothFactory::_( 'tv.video' );
			$this->_statusCommentLog = $fVideo->getStatusLog( $this->getId() );
		}
		
		// determine most recent relevant comment
		if( !empty($this->_statusCommentLog) ) {
			$latestLogEntry = reset( $this->_statusCommentLog );
			if( ($latestLogEntry['new_status_id'] == ARC_TV_APPROVED) || ($latestLogEntry['new_status_id'] == ARC_TV_REJECTED) ) {
				$curComment = is_null( $latestLogEntry['comment'] ) ? 'No comments were made during moderation.' : $latestLogEntry['comment'];
			}
		}
		
		// set up return info
		switch( $this->_core['status'] ) {
		case( ARC_TV_APPROVED ):
			$info['colour'] =  'green';
			$info['status'] =  'Approved';
			$info['comment'] = $curComment;
			break;
		case( ARC_TV_INCOMPLETE ):
			$info['colour'] =  'clear';
			$info['status'] =  'Incomplete';
			$info['comment'] = 'This video has not yet been submitted for moderation.';
			break;
		case( ARC_TV_PENDING ):
			$info['colour'] =  'amber';
			$info['status'] =  'Pending Moderation';
			$info['comment'] = 'This video has been uploaded and is awaiting moderation. Please check back soon for any updates!';
			break;
		case( ARC_TV_REJECTED ):
			$info['colour'] =  'red';
			$info['status'] =  'Rejected';
			$info['comment'] = $curComment;
			break;
		}
		
		return $info;
	}
	
	/**
	 * Get the the most recent database error message from the factory
	 * 
	 * @return string  The most recent database error message
	 */
	function getErrMsg()
	{
		$fVideo = &ApothFactory::_( 'tv.video' );
		
		return $fVideo->getErrMsg();
	}
	
	/**
	 * Trigger saving of the object to the database
	 *
	 * @return boolean  An indication of the success of the factory's commit procedure
	 */
	function commit()
	{
		$fVideo = &ApothFactory::_( 'tv.video' );
		return $fVideo->commitInstance( $this->getId() );
	}
}
?>