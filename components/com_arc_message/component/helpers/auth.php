<?php
/**
 * @package     Arc
 * @subpackage  Timetable
 * @copyright   Copyright (C) 2005 Punnet. All rights reserved. See COPYRIGHT_ARC.txt
 * @license     http://www.gnu.org/licenses/gpl.html GNU/GPL. See LICENSE_ARC.txt
 * Arc is free software: you can redistribute it and/or modify
 * it under the terms of version 3 of the GNU General Public License
 * as published by the Free Software Foundation.
 * The disclaimer of warranty as stated in the GPL applies to this program
 */

defined( '_JEXEC' ) or die( 'Restricted access' );

class ApothAuth_Message
{
	function checkDependancy( $ident, $given, $uId = false, $actionId = false )
	{
		$db = &JFactory::getDBO();
		$user = &ApotheosisLib::getUser( $uId );
		
		switch( $ident ) {
		case( 'threads' ):
			$query = 'SELECT t.id'
				."\n".'FROM #__apoth_msg_threads AS t'
				."\n".'~LIMITINGJOIN~'
				."\n".'WHERE t.id = '.$db->Quote($given)
				."\n".'LIMIT 1';
			$query = ApotheosisLibAcl::limitQuery( $query, 'message.messages', 't', 'msg_id', false, $actionId);
			$db->setQuery( $query );
			$r = $db->loadResult();
//			echo 'thread loading '; debugQuery($db, $r);
			$retVal = !empty($r);
			break;
		
		case( 'messages' ):
			$query = 'SELECT m.id'
				."\n".'FROM #__apoth_msg_messages AS m'
				."\n".'~LIMITINGJOIN~'
				."\n".'WHERE m.id = '.$db->Quote($given)
				."\n".'LIMIT 1';
			$query = ApotheosisLibAcl::limitQuery( $query, 'message.messages', 'm', 'id', false, $actionId);
			$db->setQuery( $query );
			$r = $db->loadResult();
//			echo 'message loading '; debugQuery($db, $r);
			$retVal = !empty($r);
			break;
		
		case( 'channels' ):
			$query = 'SELECT c.id'
				."\n".'FROM #__apoth_msg_channels AS c'
				."\n".'~LIMITINGJOIN~'
				."\n".'WHERE c.id = '.$db->Quote($given)
				."\n".'LIMIT 1';
			$query = ApotheosisLibAcl::limitQuery( $query, 'message.channels', 'c', 'id', false, $actionId);
			$db->setQuery( $query );
			$r = $db->loadResult();
//			echo 'channel loading '; debugQuery($db, $r);
			$retVal = !empty($r);
			break;
		
		case( 'forms' ):
		case( 'scopes' ):
		case( 'tags' ):
		case( 'tasks' ):
			$retVal = true;
			break;
		
		default:
			$retVal = false;
			break;
		}
		
		return $retVal;
	}
	
	/**
	 * Limits the messages/channels that can be pulled out by the given query
	 * to only those accessible by the given user in the given action 
	 * 
	 * @param $givenQuery string  The query to limit
	 * @param $limitOn string  Either 'messages' or 'channels' depending on which the query should be limited by
	 * @param $inTable string  The optional table name to join from. Defaults to 'm' for mesages, 'ch' for channels 
	 * @param $inCol string  The optional column name to join from. Defaults to 'id'
	 * @param $uId string  The jUserId of the user whose access should be used. Defaults to current user
	 * @param $actionId string  The optional action id to check. Defaults to current action
	 * @param $joinSlug string  The optional text to replace with the limiting JOINs. Defaults to '~LIMITINGJOIN~'
	 * @return string   The original query with additional JOIN clauses to limit the results.
	 */
	function limitQuery($givenQuery, $limitOn, $inTable = false, $inCol = false, $uId = false, $actionId = false, $joinSlug = '~LIMITINGJOIN~' )
	{
		$db = &JFactory::getDBO();
		$user = &ApotheosisLib::getUser( $uId );
		
		switch( $limitOn ) {
		case( 'messages' ):
			$tableName = ApotheosisLibAcl::getUserTable( 'message.messages', $user->id );
			if( $inTable === false ) {
				$inTable = 'm';
			}
			if( $inCol === false ) {
				$inCol = 'id';
			}
			$inTable = $db->nameQuote( $inTable );
			$inCol   = $db->nameQuote( $inCol );
			
			$joinQuery = 'INNER JOIN '.$tableName.' AS lim_msg_m'
				."\n".'   ON lim_msg_m.id = '.$inTable.'.'.$inCol;
			break;
		
		case( 'channels' ):
			$tableName = ApotheosisLibAcl::getUserTable( 'message.channels', $user->id );
			if( $inTable === false ) {
				$inTable = 'c';
			}
			if( $inCol === false ) {
				$inCol = 'id';
			}
			$inTable = $db->nameQuote( $inTable );
			$inCol   = $db->nameQuote( $inCol );
			
			$joinQuery = 'INNER JOIN '.$tableName.' AS lim_msg_c'
				."\n".'   ON lim_msg_c.id = '.$inTable.'.'.$inCol
				."\n".'~LIMITINGJOIN~';
			$joinQuery = ApotheosisLibAcl::limitQuery( $joinQuery, 'core.sees', 'lim_msg_c', 'role', $uId, $actionId );
			break;
		
		default:
			$joinQuery = '';
		}
		
		return str_replace( $joinSlug, $joinQuery, $givenQuery );
	}
	
	
	/**
	 * Creates and populates the user's messages table if it doesn't already exist.
	 * Gves back the name of that table (with minimum overhead if it exists) 
	 * 
	 * @param $tableName string  The name of the short-term table to be created / populated
	 * @param $uId string  The jUserId of the user whose access should be used. Defaults to current user
	 * @return string  The name of the table which now contains this user's messages
	 */
	function createTblUserMessages( $tableName, $uId )
	{
		$user = &ApotheosisLib::getUser( $uId );
		$uId = $user->id;
		$from = ApotheosisLibAcl::getDatum( 'dateFrom' );
		$to = ApotheosisLibAcl::getDatum( 'dateTo' );
		$db = &JFactory::getDBO();
		
		if( !ApotheosisLibDbTmp::getExists( $tableName ) ) {
			$createQuery = 'CREATE TABLE ~TABLE~ ENGINE=MyISAM AS'
				."\n".'SELECT tm.`msg_id` AS `id`'
				."\n".'FROM #__apoth_msg_tag_map AS tm'
				."\n".'LIMIT 0';
			ApotheosisLibDbTmp::create( $tableName, $createQuery );
		}
		
		// we're using a fresh table, so populate it with data according to the various components
		$null = null;
		ApotheosisLib::callAll( 'helpers'.DS.'auth.php', 'ApothAuth', 'setUserMessages', array($tableName, $uId, $from, $to), $null, true );
		
		$alterQuery = 'ALTER TABLE '.$tableName.' ADD INDEX (`id`)';
		$db->setQuery( $alterQuery );
		$db->Query();
		
		ApotheosisLibDbTmp::setPopulated( $tableName );
		return $tableName;
	}
	
	/**
	 * Creates and populates the user's channels table if it doesn't already exist.
	 * Gves back the name of that table (with minimum overhead if it exists) 
	 * 
	 * @param $tableName string  The name of the short-term table to be created / populated
	 * @param $uId string  The jUserId of the user whose access should be used. Defaults to current user
	 * @return string  The name of the table which now contains this user's messages
	 */
	function createTblUserChannels( $tableName, $uId  )
	{
		$user = &ApotheosisLib::getUser( $uId );
		$uId = $user->id;
		$from = ApotheosisLibAcl::getDatum( 'dateFrom' );
		$to = ApotheosisLibAcl::getDatum( 'dateTo' );
		$db = &JFactory::getDBO();
		
		if( !ApotheosisLibDbTmp::getExists( $tableName ) ) {
		$createQuery = 'CREATE TABLE ~TABLE~ ENGINE=MyISAM AS'
			."\n".'SELECT c.`id` AS `id`, r.`role` AS `role`'
			."\n".'FROM #__apoth_msg_channels AS c'
			."\n".'INNER JOIN #__apoth_sys_roles AS r'
			."\n".'LIMIT 0';
			ApotheosisLibDbTmp::create( $tableName, $createQuery );
		}
		
		// we're using a fresh table, so populate it with data according to the various components
		$null = null;
		ApotheosisLib::callAll( 'helpers'.DS.'auth.php', 'ApothAuth', 'setUserChannels', array($tableName, $uId, $from, $to), $null, true );
		
		$alterQuery = 'ALTER TABLE '.$tableName.' ADD INDEX (`id`)';
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
		
		$db = &JFactory::getDBO();
		
		// everyone is allowed to access public channels,
		// and it's no harm to assume they'll own one or two too
		$roles = array(
			ApotheosisLibAcl::getRoleId( 'message_channel_owner' ),
			ApotheosisLibAcl::getRoleId( 'message_channel_accessor' )
		);
		
		$insertQuery = 'INSERT INTO '.$tableName
			."\n".' VALUES ( '.implode(' ), ( ', $roles).' )';
		$db->setQuery($insertQuery);
		$db->Query();
	}
	
	
	/**
	 * Inserts message data into the user table named
	 * 
	 * @param $tableName string  The name of the table into which to insert data
	 * @param $uId string  The jUserId of the user whose access should be used. Defaults to current user
	 * @param $from string  The date from which to consider messages 
	 * @param $to string  The date up to which to consider messages
	 */
	function setUserMessages( $tableName, $uId, $from, $to )
	{
		// if no user ID then set no messages
		if( empty($uId) ) { return true; }
		
		$db = &JFactory::getDBO();
		
		// messages in which the user is involved
		$query = 'INSERT INTO '.$tableName
			."\n".'SELECT thr2.msg_id'
			."\n".'FROM #__apoth_msg_tag_map AS tm'
			."\n".'INNER JOIN #__apoth_ppl_people AS p'
			."\n".'   ON p.id = tm.person_id'
			."\n".'INNER JOIN #__apoth_msg_threads AS thr'
			."\n".'   ON thr.msg_id = tm.msg_id'
			."\n".'INNER JOIN #__apoth_msg_threads AS thr2'
			."\n".'   ON thr2.id = thr.id'
			."\n".'WHERE p.juserid = '.$uId
			."\n".'  AND '.ApotheosisLibDb::dateCheckSql( 'tm.valid_from', 'tm.valid_to', $from, $to )
			."\n".'GROUP BY thr2.msg_id';
		$db->setQuery( $query );
		$db->Query();
	}
	
	/**
	 * Inserts channel data into the user table named
	 * 
	 * @param $tableName string  The name of the table into which to insert data
	 * @param $uId string  The jUserId of the user whose access should be used. Defaults to current user
	 * @param $from string  The date from which to consider messages 
	 * @param $to string  The date up to which to consider messages
	 */
	function setUserChannels( $tableName, $uId, $from, $to )
	{
		// if no user ID then set no channels
		if( empty($uId) ) { return true; }
		$user = ApotheosisLib::getUser( $uId );
		
		$db = &JFactory::getDBO();
		
		// channels the user created or which are made available
		$insertQuery = 'INSERT IGNORE INTO '.$tableName
			."\n".'SELECT DISTINCT c.id AS id'
			."\n".', IF( c.created_by = '.$db->Quote( $user->person_id )
			."\n".', '.ApotheosisLibAcl::getRoleId( 'message_channel_owner' )
			."\n".', '.ApotheosisLibAcl::getRoleId( 'message_channel_accessor' )
			."\n".'  ) AS `role`'
			."\n".'FROM #__apoth_msg_channels AS c'
			."\n".'WHERE ( c.created_by = '.$db->Quote( $user->person_id )
			."\n".'        OR'
			."\n".'        c.privacy < 2 )'
			."\n".'  AND '.ApotheosisLibDb::dateCheckSql('c.valid_from', 'c.valid_to', $from, $to);
		$db->setQuery($insertQuery);
		$db->Query();
	}
	
}