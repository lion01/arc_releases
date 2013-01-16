<?php
/**
 * @package     Arc
 * @subpackage  Report
 * @copyright   Copyright (C) 2005 Punnet. All rights reserved. See COPYRIGHT_ARC.txt
 * @license     http://www.gnu.org/licenses/gpl.html GNU/GPL. See LICENSE_ARC.txt
 * Arc is free software: you can redistribute it and/or modify
 * it under the terms of version 3 of the GNU General Public License
 * as published by the Free Software Foundation.
 * The disclaimer of warranty as stated in the GPL applies to this program
 */

defined( '_JEXEC' ) or die( 'Restricted access' );

class ApothAuth_Report
{
	function checkDependancy( $ident, $given, $uId = false, $actionId = false )
	{
		$db = &JFactory::getDBO();
		$user = &ApotheosisLib::getUser( $uId );
		
		switch( $ident ) {
		case( 'cycle' ):
		case( 'subreport' ):
		case( 'listpage' ):
		case( 'commit' ):
		case( 'status' ):
		case( 'field' ):
		default:
			$retVal = true;
			break;
		}
		return $retVal;
	}
	
	/**
	 * Limits the data that can be pulled out by the given query
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
		
		switch( $limitOn ) {
		case( 'subreports' ):
			$tableName = ApotheosisLibAcl::getUserTable( 'report.subreports', $user->id );
			if( $inTable === false ) {
				$inTable = 's';
			}
			if( $inCol === false ) {
				$inCol = 'id';
			}
			$inTable = $db->nameQuote( $inTable );
			$inCol   = $db->nameQuote( $inCol );
			
			$joinQuery = 'INNER JOIN '.$tableName.' AS lim_rpt_sub'
				."\n".'   ON lim_rpt_sub.id = '.$inTable.'.'.$inCol
				."\n".'~LIMITINGJOIN~';
			$joinQuery = ApotheosisLibAcl::limitQuery( $joinQuery, 'core.sees', 'lim_rpt_sub', 'role', $uId, $actionId );
			break;
		
		default:
			$joinQuery = '';
		}
		
		return str_replace( $joinSlug, $joinQuery, $givenQuery );
	}
	
	
	/**
	 * Creates and populates the user's subreports table if it doesn't already exist.
	 * Gves back the name of that table (with minimum overhead if it exists) 
	 * 
	 * @param $tableName string  The name of the short-term table to be created / populated
	 * @param $uId string  The jUserId of the user whose access should be used. Defaults to current user
	 * @return string  The name of the table which now contains this user's subreports
	 */
	function createTblUserSubreports( $tableName, $uId )
	{
		$user = &ApotheosisLib::getUser( $uId );
		$uId = $user->id;
		$from = ApotheosisLibAcl::getDatum( 'dateFrom' );
		$to = ApotheosisLibAcl::getDatum( 'dateTo' );
		$db = &JFactory::getDBO();
		
		if( !ApotheosisLibDbTmp::getExists( $tableName ) ) {
			$createQuery = 'CREATE TABLE ~TABLE~ ENGINE=MyISAM AS'
				."\n".'SELECT s.`id` AS `id`, r.`role` AS `role`'
				."\n".'FROM #__apoth_rpt_subreports AS s'
				."\n".'INNER JOIN #__apoth_sys_roles AS r'
				."\n".'LIMIT 0';
			ApotheosisLibDbTmp::create( $tableName, $createQuery );
//			debugQuery( $db );
		}
		
		// we're using a fresh table, so populate it with data according to the various components
		$results = array();
		ApotheosisLib::callAll( 'helpers'.DS.'auth.php', 'ApothAuth', 'setUserSubreports', array($tableName, $uId, $from, $to), $results, true );
		
		$alterQuery = 'ALTER TABLE '.$tableName
			."\n".'  ADD PRIMARY KEY('.$db->nameQuote('id').', '.$db->nameQuote( 'role' ).')';
		$db->setQuery($alterQuery);
		$db->Query();
//		debugQuery( $db );
		
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
		
		$roleList = array(
			ApotheosisLibAcl::getRoleId( 'report_admin' ),
			ApotheosisLibAcl::getRoleId( 'report_checker' ),
			ApotheosisLibAcl::getRoleId( 'report_reader' ),
			ApotheosisLibAcl::getRoleId( 'report_author' ),
			ApotheosisLibAcl::getRoleId( 'report_reportee' )
		);
		
		$query = 'SELECT gm.role'
			."\n".'FROM #__apoth_tt_group_members AS gm'
			."\n".'INNER JOIN #__apoth_ppl_people AS p'
			."\n".'   ON p.id = gm.person_id'
			."\n".'  AND p.juserid = '.$db->Quote( $uId )
			."\n".'WHERE '.ApotheosisLibDb::dateCheckSql( 'gm.valid_from', 'gm.valid_to', date( 'Y-m-d H:i:s' ), date( 'Y-m-d H:i:s' ) )
			."\n".'  AND '.$db->nameQuote( 'role' ).' IN ( '.implode( ', ', $roleList ).' )' 
			."\n".'GROUP BY gm.role';
		$db->setQuery( $query );
		$roles1 = $db->loadResultArray();
//		debugQuery( $db, $roles1 );
		
		$query = 'SELECT '.ApotheosisLibAcl::getRoleId( 'report_author' ).' AS role'
			."\n".'FROM `jos_apoth_tt_group_members` AS gm'
			."\n".'INNER JOIN #__apoth_ppl_people AS p'
			."\n".'   ON p.id = gm.person_id'
			."\n".'  AND p.juserid = '.$db->Quote( $uId )
			."\n".'INNER JOIN `jos_apoth_rpt_subreports` AS s'
			."\n".'   ON s.rpt_group_id = gm.group_id'
			."\n".'  AND s.reportee_id = gm.person_id'
			."\n".'INNER JOIN `jos_apoth_rpt_cycles` AS c'
			."\n".'   ON c.id = s.cycle_id'
			."\n".'WHERE gm.role = '.ApotheosisLibAcl::getRoleId( 'report_reportee' )
			."\n".'  AND c.self_report = 1'
			."\n".'LIMIT 1';
		$db->setQuery( $query );
		$roles2 = $db->loadResultArray();
//		debugQuery( $db, $roles2 );
		$roles = array_merge( $roles1, $roles2 );
		
		if( !empty($roles) ) {
			$insertQuery = 'INSERT INTO '.$tableName
				."\n".' VALUES ( '.implode(' ), ( ', $roles).' )';
			$db->setQuery($insertQuery);
			$db->Query();
//			debugQuery( $db );
		}
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
	function setUserSubreports( $tableName, $uId, $from, $to )
	{
		// if no user ID then set no roles
		if( empty($uId) ) { return true; }
		
		$db = &JFactory::getDBO();
		$user = &ApotheosisLib::getUser( $uId );
		
		$insertQuery = 'INSERT IGNORE INTO '.$tableName // *** titikaka fail (need to use new acl)
			."\n".'SELECT s.id, gm.role'
			."\n".'FROM `jos_apoth_rpt_subreports` AS s'
			."\n".'INNER JOIN jos_apoth_tt_group_members AS gm'
			."\n".'   ON gm.group_id = s.rpt_group_id'
			."\n".'  AND ('
			."\n".'    (gm.person_id = s.reportee_id AND gm.role = '.ApotheosisLibAcl::getRoleId( 'report_reportee' ).')'
			."\n".'    OR'
			."\n".'    (gm.role IN' 
				.'( '.ApotheosisLibAcl::getRoleId( 'report_admin' )
				.', '.ApotheosisLibAcl::getRoleId( 'report_checker' )
				.', '.ApotheosisLibAcl::getRoleId( 'report_reader' )
				.', '.ApotheosisLibAcl::getRoleId( 'report_author' )
				.') )'
			."\n".'  )'
			."\n".'WHERE gm.person_id = '.$db->Quote($user->person_id)
			."\n".'  AND '.ApotheosisLibDb::dateCheckSql('gm.valid_from', 'gm.valid_to', $from, $to);
		$db->setQuery($insertQuery);
		$db->Query();
//		debugQuery( $db );
		
		$insertQuery = 'INSERT IGNORE INTO '.$tableName // *** titikaka fail (need to use new acl)
			."\n".'SELECT s.id, '.ApotheosisLibAcl::getRoleId( 'report_author' )
			."\n".'FROM `jos_apoth_rpt_subreports` AS s'
			."\n".'INNER JOIN jos_apoth_rpt_cycles AS c'
			."\n".'   ON c.id = s.cycle_id'
			."\n".'WHERE s.reportee_id = '.$db->Quote($user->person_id)
			."\n".'  AND c.self_report = 1';
		$db->setQuery($insertQuery);
		$db->Query();
//		debugQuery( $db );
	}
}
?>