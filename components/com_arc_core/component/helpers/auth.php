<?php
/**
 * @package     Arc
 * @subpackage  Core
 * @copyright   Copyright (C) 2005 Punnet. All rights reserved. See COPYRIGHT_ARC.txt
 * @license     http://www.gnu.org/licenses/gpl.html GNU/GPL. See LICENSE_ARC.txt
 * Arc is free software: you can redistribute it and/or modify
 * it under the terms of version 3 of the GNU General Public License
 * as published by the Free Software Foundation.
 * The disclaimer of warranty as stated in the GPL applies to this program
 */

defined( '_JEXEC' ) or die( 'Restricted access' );

class ApothAuth_Core
{
	function checkDependancy( $ident, $given, $uId = false, $actionId = false )
	{
		$db = &JFactory::getDBO();
		$user = &ApotheosisLib::getUser( $uId );
		
		switch( $ident ) {
		case( 'treenode' ):
		case( 'action' ):
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
	function limitQuery($givenQuery, $limitOn, $inTable = false, $inCol = false, $uId = false, $actionId = null, $joinSlug = '~LIMITINGJOIN~' )
	{
		$db = &JFactory::getDBO();
		$user = &ApotheosisLib::getUser( $uId );
		
		if( is_null($actionId) ) {
			$actionId = ApotheosisLib::getActionId();
		}
		
		switch( $limitOn ) {
		case( 'sees' ):
			static $pInc = 0;
			if( $inTable === false ) {
				$inTable = 'lim_tbl';
			}
			if( $inCol === false ) {
				$inCol = 'role';
			}
			$inTable = $db->nameQuote( $inTable );
			$inCol   = $db->nameQuote( $inCol );
			$tableName = ApotheosisLibAcl::getUserTable( 'core.permissions', $user->id );
			
			$joinQuery = 'INNER JOIN '.$tableName.' AS lim_core_p_'.$pInc
				."\n".'   ON lim_core_p_'.$pInc.'.sees = '.$inTable.'.'.$inCol
				."\n".'  AND lim_core_p_'.$pInc.'.action = '.$db->Quote($actionId)
				."\n".'  AND lim_core_p_'.$pInc.'.allowed = 1';
			$pInc++;
			break;
			
		case( 'roles' ):
			static $rInc = 1;
			if( $inTable === false ) {
				$inTable = 'lim_tbl';
			}
			if( $inCol === false ) {
				$inCol = 'role';
			}
			$inTable = $db->nameQuote( $inTable );
			$inCol   = $db->nameQuote( $inCol );
			$tableName = ApotheosisLibAcl::getUserTable( 'core.roles', $uId );
			
			$joinQuery = 'INNER JOIN '.$tableName.' AS lim_core_r_'.$rInc
				."\n".'   ON lim_core_r_'.$rInc.'.id = '.$inTable.'.'.$inCol;
			$rInc++;
			break;
		
		default:
			$joinQuery = '';
		}
		
		return str_replace( $joinSlug, $joinQuery, $givenQuery );
	}
	
	
	/**
	 * Creates and populates the user's roles table if it doesn't already exist.
	 * Gves back the name of that table (with minimum overhead if it exists) 
	 * 
	 * @param $tableName string  The name of the short-term table to be created / populated
	 * @param $uId string  The jUserId of the user whose access should be used. Defaults to current user
	 * @return string  The name of the table which now contains this user's roles
	 */
	function createTblUserRoles( $tableName, $uId )
	{
		$user = &ApotheosisLib::getUser( $uId );
		$uId = $user->id;
		$from = ApotheosisLibAcl::getDatum( 'dateFrom' );
		$to = ApotheosisLibAcl::getDatum( 'dateTo' );
		$db = &JFactory::getDBO();
		
		if( !ApotheosisLibDbTmp::getExists( $tableName ) ) {
			$createQuery = 'CREATE TABLE ~TABLE~ ENGINE=MyISAM AS'
				."\n".'SELECT `id`'
				."\n".'FROM #__apoth_sys_roles'
				."\n".'LIMIT 0';
			ApotheosisLibDbTmp::create( $tableName, $createQuery );
		}
		
		// we're using a fresh table, so populate it with data according to the various components
		// *** There are currently no valid from / to data in the acl tables 
		$results = array();
		ApotheosisLib::callAll( 'helpers'.DS.'auth.php', 'ApothAuth', 'setUserRoles', array($tableName, $uId, $from, $to), $results, true );
		
		$alterQuery = 'ALTER TABLE '.$tableName
			."\n".'  ADD PRIMARY KEY('.$db->nameQuote('id').')';
		$db->setQuery($alterQuery);
		$db->Query();
		
		ApotheosisLibDbTmp::setPopulated( $tableName );
		return $tableName;
	}
	
	/**
	 * Creates and populates the user's permissions table if it doesn't already exist.
	 * Gves back the name of that table (with minimum overhead if it exists) 
	 * 
	 * @param $tableName string  The name of the short-term table to be created / populated
	 * @param $uId string  The jUserId of the user whose access should be used. Defaults to current user
	 * @return string  The name of the table which now contains this user's permissions
	 */
	function createTblUserPermissions( $tableName, $uId )
	{
		$user = &ApotheosisLib::getUser( $uId );
		$uId = $user->id;
		$from = ApotheosisLibAcl::getDatum( 'dateFrom' );
		$to = ApotheosisLibAcl::getDatum( 'dateTo' );
		$db = &JFactory::getDBO();
		
		if( !ApotheosisLibDbTmp::getExists( $tableName ) ) {
			// NB the sys_acl table must list all leaf nodes for which an action is allowed (or banned if we build that in later) 
			$createQuery = 'CREATE TABLE ~TABLE~ ENGINE=MyISAM AS SELECT'
				."\n".'  n.`id` AS `action`'
				."\n".', n.`menu_id`'
				."\n".', n.`option`'
				."\n".', n.`task`'
				."\n".', n.`params`'
				."\n".', n.`name`'
				."\n".', a.`sees`'
				."\n".', a.`allowed`'
				."\n".'FROM `jos_apoth_sys_actions` AS n'
				."\n".'LEFT JOIN `jos_apoth_sys_acl` AS a'
				."\n".'  ON a.action = n.id'
				."\n".'LIMIT 0';
			$tmp = ApotheosisLibDbTmp::create( $tableName, $createQuery );
		}
		
		// we're using a fresh table, so populate it with data according to the various components
		// *** There is currently no valid from / to data in the acl table 
		$results = array();
		ApotheosisLib::callAll( 'helpers'.DS.'auth.php', 'ApothAuth', 'setUserPermissions', array($tableName, $uId, $from, $to), $results, true );
		
		$alterQuery = 'ALTER TABLE '.$tableName
			."\n".'  ADD INDEX('.$db->nameQuote('action').')'
			."\n".', ADD INDEX('.$db->nameQuote('sees').')';
		$db->setQuery($alterQuery);
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
		$db = &JFactory::getDBO();
		$selfId = ApotheosisLibAcl::getRoleId( 'any_public' );
		$insertQuery = 'INSERT INTO '.$tableName
			."\n".'VALUES ('.$db->Quote($selfId).')';
		$db->setQuery($insertQuery);
		$db->Query();
		
		// if no user ID then set no more roles
		if( empty($uId) ) { return true; }
		
		$user = &ApotheosisLib::getUser( $uId );
		
		$insertQuery = 'INSERT INTO '.$tableName
			."\n".'SELECT DISTINCT `role`'
			."\n".'FROM jos_apoth_sys_com_roles'
			."\n".'WHERE person_id = '.$db->Quote($user->person_id);
		$db->setQuery($insertQuery);
		$db->Query();
		
		$selfId = ApotheosisLibAcl::getRoleId( 'sys_user' );
		$insertQuery = 'INSERT INTO '.$tableName
			."\n".'VALUES ('.$db->Quote($selfId).')';
		$db->setQuery($insertQuery);
		$db->Query();
		
	}
	
	/**
	 * Inserts permission data into the user table named
	 * Gets user tables: core.roles
	 * 
	 * @param $tableName string  The name of the table into which to insert data
	 * @param $uId string  The jUserId of the user whose access should be used. Defaults to current user
	 * @param $from string  The date from which to consider permissions. Defaults to now. 
	 * @param $to string  The date up to which to consider permissions. Defaults to now.
	 */
	function setUserPermissions( $tableName, $uId, $from, $to )
	{
		$db = &JFactory::getDBO();
		
		$roleTable = ApotheosisLibAcl::getUserTable( 'core.roles', $uId );
		$myAcl = 'my_acl_'.$uId.time();
		
		$insertQuery = 'START TRANSACTION;'
		
			."\n".'CREATE TABLE '.$myAcl.' AS'
			."\n".'SELECT *'
			."\n".'FROM `jos_apoth_sys_acl` AS a'
			."\n".'INNER JOIN `'.$roleTable.'` AS r'
			."\n".'   ON r.id = a.`role`;'
	
			."\n".'INSERT INTO '.$tableName.' SELECT'
			."\n".'  n.`id` AS `action`'
			."\n".', n.`menu_id`'
			."\n".', n.`option`'
			."\n".', n.`task`'
			."\n".', n.`params`'
			."\n".', n.`name`'
			."\n".', a.`sees`'
			."\n".', IF( a.`allowed` IS NULL, 0, a.`allowed` ) AS `allowed`'
			."\n".'FROM `jos_apoth_sys_actions` AS n'
			."\n".'LEFT JOIN `'.$myAcl.'` AS a'
			."\n".'  ON a.action = n.id'
			."\n".'LEFT JOIN `'.$myAcl.'` AS a2'
			."\n".'  ON a2.action = a.action'
			."\n".' AND a2.allowed = a.allowed'
			."\n".' AND a2.sees IS NULL'
			."\n".'WHERE a.action IS NULL'
			."\n".'   OR ((a.sees IS NULL) != (a2.`role` IS NULL));'
			
			."\n".'DROP TABLE '.$myAcl.';'
			
			."\n".'COMMIT;';
		$db->setQuery($insertQuery);
		$db->QueryBatch();
	}
}
?>