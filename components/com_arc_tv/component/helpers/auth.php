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

class ApothAuth_TV
{
	function checkDependancy( $ident, $given, $uId = false, $actionId = false )
	{
		$db = &JFactory::getDBO();
		$user = &ApotheosisLib::getUser( $uId );
		
		switch( $ident ) {
		case( 'videoId' ):
			if( empty( $given ) || ($given < 0) ) {
				$retVal = true;
			}
			else {
				$table = ApotheosisLibAcl::getUserTable( 'tv.videos' );
				$dbTmpName = $db->nameQuote( 'tmp_'.$uId.'_givenvid' );
				static $tmpMade = false;
				if( !$tmpMade ) {
					$tmpMade = true;
					$query = 'CREATE TEMPORARY TABLE IF NOT EXISTS '.$dbTmpName.'('
						."\n".$db->nameQuote( 'id' ).' INTEGER UNSIGNED NOT NULL'
						."\n".');'
						."\n".'INSERT INTO '.$dbTmpName.' VALUES ('.$db->Quote( $given ).');';
					$db->setQuery( $query );
					$db->QueryBatch();
				}
				
				$query = 'SELECT v.id'
					."\n".'FROM '.$dbTmpName.' AS v'
					."\n".'~LIMITINGJOIN~'
					."\n".'LIMIT 1';
				$query = ApotheosisLibAcl::limitQuery( $query, 'tv.videos', 'v', 'id', false, $actionId );
				$db->setQuery( $query );
				$r = $db->loadResult();
				$retVal = !empty($r);
			}
			break;
		
		case( 'tag' ):
		default:
			$retVal = true;
			break;
		}
		
		return $retVal;
	}
	
	/**
	 * Limits the people/groups that can be pulled out by the given query
	 * to only those accessible by the given user in the given action 
	 * 
	 * @param $givenQuery string  The query to limit
	 * @param $limitOn string  Either 'people' or 'groups' depending on which the query should be limited by
	 * @param $inTable string  The optional table name to join from. Defaults to 'p' for people, 'g' for groups 
	 * @param $inCol string  The optional column name to join from. Defaults to 'id'
	 * @param $uId string  The jUserId of the user whose access should be used. Defaults to current user  
	 * @param $actionId string  The optional action id to check. Defaults to current action
	 * @param $joinSlug string  The optional text to replace with the limiting JOINs. Defaults to '~LIMITINGJOIN~'
	 * @return string   The original query with additional JOIN clauses to limit the results.
	 */
	function limitQuery( $givenQuery, $limitOn, $inTable = false, $inCol = false, $uId = false, $actionId = false, $joinSlug = '~LIMITINGJOIN~' )
	{
		$db = &JFactory::getDBO();
		$user = &ApotheosisLib::getUser( $uId );
		
		$tableName = ApotheosisLibAcl::getUserTable( 'tv.videos', $user->id );
		
		switch( $limitOn ) {
		case( 'videos' ):
			if( $inTable === false ) {
				$inTable = 'v';
			}
			if( $inCol === false ) {
				$inCol = 'id';
			}
			$inTable = $db->nameQuote( $inTable );
			$inCol   = $db->nameQuote( $inCol );
			
			$joinQuery = 'INNER JOIN '.$tableName.' AS lim_tv_vid'
				."\n".'   ON lim_tv_vid.id = '.$inTable.'.'.$inCol
				."\n".'~LIMITINGJOIN~';
			$joinQuery = ApotheosisLibAcl::limitQuery( $joinQuery, 'core.sees', 'lim_tv_vid', 'role', $uId, $actionId );
			break;
		
		default:
			$joinQuery = '';
		}
		
		return str_replace( $joinSlug, $joinQuery, $givenQuery );
	}
	
	/**
	 * Creates and populates the user's videos table if it doesn't already exist.
	 * Gives back the name of that table (with minimum overhead if it exists) 
	 * 
	 * @param $tableName string  The name of the short-term table to be created / populated
	 * @param $uId string  The jUserId of the user whose access should be used. Defaults to current user
	 * @return string  The name of the table which now contains this user's videos
	 */
	function createTblUserVideos( $tableName, $uId )
	{
		$user = &ApotheosisLib::getUser( $uId );
		$uId = $user->id;
		$from = ApotheosisLibAcl::getDatum( 'dateFrom' );
		$to = ApotheosisLibAcl::getDatum( 'dateTo' );
		$db = &JFactory::getDBO();
		
		if( !ApotheosisLibDbTmp::getExists( $tableName ) ) {
			$createQuery = 'CREATE TABLE ~TABLE~ ('
				."\n".'	'.$db->nameQuote( 'id' ).' INTEGER UNSIGNED NOT NULL,'
				."\n".'	'.$db->nameQuote( 'role' ).' INTEGER UNSIGNED NOT NULL'
				."\n".') ENGINE=MyISAM';
			ApotheosisLibDbTmp::create( $tableName, $createQuery );
		}
		
		// we're using a fresh table so populate it with data according to the various components
		$results = array();
		ApotheosisLib::callAll( 'helpers'.DS.'auth.php', 'ApothAuth', 'setUserVideos', array($tableName, $uId, $from, $to), $null, true );
		
		$alterQuery = 'ALTER TABLE '.$tableName.' ADD INDEX (`id`)'
			."\n".' , ADD INDEX(`role`)';
		$db->setQuery( $alterQuery );
		$db->Query();
		
		ApotheosisLibDbTmp::setPopulated( $tableName );
		
		return $tableName;
	}
	
	/**
	 * Inserts role data into the user table named
	 * 
	 * @param $tableName string  The name of the table into which to insert data
	 * @param $uId string  The jUserId of the user whose access should be used. Defaults to current user
	 * @param $from string  The date from which to consider roles. Defaults to now. 
	 * @param $to string  The date up to which to consider roles. Defaults to now.
	 */
	function setUserRoles( $tableName, $uId, $from, $to )
	{
		// if no user ID then set no roles
		if( empty($uId) ) { return true; }
		
		$ownerId = ApotheosisLibAcl::getRoleId( 'tv_owner' );
		
		$db = &JFactory::getDBO();
		$facInfo = ApothFactory::getFactoryInfo( 'tv.video' );
		$db2 = &$facInfo['className']::getVidDBO();
		
		$coreParams = &JComponentHelper::getParams( 'com_arc_core' );
		$siteId = $coreParams->get( 'site_id' );
		$user = &ApotheosisLib::getUser( $uId );
		$personId = $user->person_id;
		
		$query = 'SELECT id FROM '.$db2->nameQuote( 'videos' )
			."\n".'WHERE '.$db2->nameQuote( 'site_id' ).' = '.$db2->Quote( $siteId )
			."\n".'  AND '.$db2->nameQuote( 'person_id' ).' = '.$db2->Quote( $personId )
			."\n".'LIMIT 1';
		$db2->setQuery( $query );
		$r = $db2->loadAssocList();
		
		if( !empty($r) ) {
			$roles[] = $ownerId;
		}
		
		if( !empty($roles) ) {
			$insertQuery = 'INSERT INTO '.$tableName
				."\n".' VALUES ( '.implode(' ), ( ', $roles).' )';
			$db->setQuery( $insertQuery );
			$db->Query();
		}
	}
	
	/**
	 * Inserts allowed-video data into the user table named
	 * 
	 * @param $tableName string  The name of the table into which to insert data
	 * @param $uId string  The jUserId of the user whose access should be used. Defaults to current user
	 * @param $from string  The date from which to consider videos. Defaults to now. 
	 * @param $to string  The date up to which to consider videos. Defaults to now.
	 */
	function setUserVideos( $tableName, $uId, $from, $to )
	{
		// if no user ID then set no roles
		if( empty($uId) ) { return true; }
		
		$moderatorId = ApotheosisLibAcl::getRoleId( 'tv_moderator' );
		$ownerId = ApotheosisLibAcl::getRoleId( 'tv_owner' );
		
		$db = &JFactory::getDBO();
		$facInfo = ApothFactory::getFactoryInfo( 'tv.video' );
		$db2 = &$facInfo['className']::getVidDBO();
		$values = array();
		
		$coreParams = &JComponentHelper::getParams( 'com_arc_core' );
		$siteId = $coreParams->get( 'site_id' );
		$user = &ApotheosisLib::getUser( $uId );
		$personId = $user->person_id;
		
		// videos submitted for moderation are available to moderators
		$query = 'SELECT '.$db2->nameQuote( 'id' )
			."\n".'FROM '.$db2->nameQuote( 'videos' )
			."\n".'WHERE '.$db2->nameQuote( 'status' ).' = '.$db2->Quote( ARC_TV_PENDING )
			."\n".'  AND ('.$db2->nameQuote( 'site_id' ).' != '.$db2->Quote( $siteId )
			."\n".'    OR '.$db2->nameQuote( 'person_id' ).' != '.$db2->Quote( $personId )
			."\n".'  )';
		$db2->setQuery( $query );
		$idList = $db2->loadResultArray();
		if( !is_array($idList) ) { $idList = array(); }
		
		$dbModeratorId = $db->Quote( $moderatorId );
		foreach( $idList as $id ) {
			$values[] = '('.$db->Quote( $id ).', '.$dbModeratorId.')';
		}
			
		// videos owned by the user are available to them for management
		$query = 'SELECT '.$db2->nameQuote( 'id' )
			."\n".'FROM '.$db2->nameQuote( 'videos' )
			."\n".'WHERE '.$db2->nameQuote( 'site_id' ).' = '.$db2->Quote( $siteId )
			."\n".'  AND '.$db2->nameQuote( 'person_id' ).' = '.$db2->Quote( $personId );
		$db2->setQuery( $query );
		$idList = $db2->loadResultArray();
		if( !is_array($idList) ) { $idList = array(); }
		
		$dbOwnerId = $db->Quote( $ownerId );
		foreach( $idList as $id ) {
			$values[] = '('.$db->Quote( $id ).', '.$dbOwnerId.')';
		}
		
		// insert the vidid / role pairs
		if( !empty($values) ) {
			$query = 'INSERT INTO '.$db->nameQuote( $tableName )
				."\n".'VALUES '.implode( ', ', $values );
			$db->setQuery( $query );
			$db->Query();
		}
	}
}
?>