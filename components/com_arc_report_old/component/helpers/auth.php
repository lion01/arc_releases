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
		
		switch( $ident ) {
		case( 'reports' ):
			if( $given != 'NULL' ) {
				// get cycle directly from reports table
				$cycleQuery = 'SELECT '.$db->nameQuote( 'cycle' )
					."\n".'FROM '.$db->nameQuote( '#__apoth_rpt_reports' )
					."\n".'WHERE '.$db->nameQuote( 'id' ).' = '.$given;
				$db->setQuery( $cycleQuery );
				$cycle = $db->loadResult();
				
				$query = 'SELECT '.$db->nameQuote( 'r' ).'.'.$db->nameQuote( 'id' )
					."\n".'FROM '.$db->nameQuote( '#__apoth_rpt_reports' ).' AS '.$db->nameQuote( 'r' )
					."\n".'~LIMITINGJOIN~'
					."\n".'WHERE '.$db->nameQuote( 'r' ).'.'.$db->nameQuote( 'id' ).' = '.$db->Quote( $given );
				$query = ApotheosisLibAcl::limitQuery( $query, 'report.reports.'.$cycle, 'r', 'id', $uId, $actionId );
				$db->setQuery( $query );
				$r = $db->loadResult();
				
				$retVal = !empty($r);
			}
			else {
				$retVal = true;
			}
			break;
		
		case( 'groups' ):
			$givenParts = explode( '_', $given );
			$cycle = $givenParts[0];
			$group = $givenParts[1];
			
			$query = 'SELECT '.$db->nameQuote( 'cg' ).'.'.$db->nameQuote( 'cycle' )
				."\n".'FROM '.$db->nameQuote( '#__apoth_rpt_cycles_groups' ).' AS '.$db->nameQuote( 'cg' )
				."\n".'~LIMITINGJOIN~'
				."\n".'WHERE '.$db->nameQuote( 'cg' ).'.'.$db->nameQuote( 'group' ).' = '.$db->Quote( $group );
			$query = ApotheosisLibAcl::limitQuery( $query, 'report.groups.'.$cycle, 'cg', 'group', $uId, $actionId );
			$db->setQuery( $query );
			$r = $db->loadResult();
			
			$retVal = !empty($r);
			break;
		
		case( 'people' ):
			$givenParts = explode( '_', $given );
			$cycle = $givenParts[0];
			$person = $givenParts[1];
			
			// get report.people limited result
			$query = 'SELECT '.$db->nameQuote( 'ppl' ).'.'.$db->nameQuote( 'id' )
				."\n".'FROM '.$db->nameQuote( '#__apoth_ppl_people' ).' AS '.$db->nameQuote( 'ppl' )
				."\n".'~LIMITINGJOIN~'
				."\n".'WHERE '.$db->nameQuote( 'ppl' ).'.'.$db->nameQuote( 'id' ).' = '.$db->Quote( $person );
			$query = ApotheosisLibAcl::limitQuery( $query, 'report.people.'.$cycle, 'ppl', 'id', $uId, $actionId );
			$db->setQuery( $query );
			$r = $db->loadResult();
			
			if( empty($r) ) {
				// get people.people limited result
				$query = 'SELECT '.$db->nameQuote( 'ppl' ).'.'.$db->nameQuote( 'id' )
					."\n".'FROM '.$db->nameQuote( '#__apoth_ppl_people' ).' AS '.$db->nameQuote( 'ppl' )
					."\n".'~LIMITINGJOIN~'
					."\n".'WHERE '.$db->nameQuote( 'ppl' ).'.'.$db->nameQuote( 'id' ).' = '.$db->Quote( $person );
				$query = ApotheosisLibAcl::limitQuery( $query, 'people.people', 'ppl', 'id', $uId, $actionId );
				$db->setQuery( $query );
				$r = $db->loadResult();
			}
			
			$retVal = !empty($r);
			break;
		
		case( 'styles' ):
		case( 'fields' ):
		case( 'statements' ):
		case( 'scope' ):
			$retVal = true;
			break;
		}
		return $retVal;
	}
	
	/**
	 * Limits the reports that can be pulled out by the given query
	 * to only those accessible by the given user in the given action 
	 * 
	 * @param $givenQuery string  The query to limit
	 * @param $limitOn string  Limit on 'reports', 'groups' or 'cycles'. Optional extra param of ~ separated cycle IDs
	 * @param $inTable string  The optional table name to join from. Defaults to '#__apoth_rpt_reports' for reports
	 * @param $inCol string  The optional column name to join from. Defaults to 'id'
	 * @param $uId string  The jUserId of the user whose access should be used. Defaults to current user  
	 * @param $actionId string  The optional action id to check. Defaults to current action
	 * @param $joinSlug string  The optional text to replace with the limiting JOINs. Defaults to '~LIMITINGJOIN~'
	 * @return string   The original query with additional JOIN clauses to limit the results.
	 */
	function limitQuery( $givenQuery, $limitOn, $inTable = false, $inCol = false, $uId = false, $actionId = false, $joinSlug = '~LIMITINGJOIN~' )
	{
		$db = &JFactory::getDBO();
		
		if( empty($uId) ) {
			$user = &ApotheosisLib::getUser();
			$uId = $user->id;
		}
		
		$from = ApotheosisLibAcl::getDatum( 'dateFrom' );
		$to = ApotheosisLibAcl::getDatum( 'dateTo' );
		$limitParts = explode( '.', $limitOn );
		
		switch( $limitParts[0] ) {
		case( 'reports' ):
			$groupsTableName = ApotheosisLibAcl::getUserTable( 'report.groups', $uId );
			$reportsTableName = ApotheosisLibAcl::getUserTable( 'report.reports', $uId );
			
			if( !is_null($limitParts[1]) ) {
				$cIdArray = explode( '~', $limitParts[1] );
				foreach( $cIdArray as $k=>$id ) {
					$cIdArray[$k] = $db->Quote( $id );
				}
				$cIds = implode( ',', $cIdArray );
				
				$query = 'SELECT '.$db->nameQuote( 'cycle' )
					."\n".'FROM '.$reportsTableName
					."\n".'WHERE '.$db->nameQuote( 'cycle' ).' IN ('.$cIds.')'
					."\n".'  AND '.$db->nameQuote( 'id' ).' = 0';
				$db->setQuery( $query );
				$r = $db->loadResultArray();
				
				if( !empty($r) ) {
					$null = null;
					ApotheosisLib::callAll( 'helpers'.DS.'auth.php', 'ApothAuth', 'setUserReportGroups', array($groupsTableName, $uId, $from, $to, $r), $null, true );
					ApotheosisLib::callAll( 'helpers'.DS.'auth.php', 'ApothAuth', 'setUserReportReports', array($reportsTableName, $uId, $from, $to, $r), $null, true );
				}
			}
			if( $inTable === false ) {
				$inTable = '#__apoth_rpt_reports';
			}
			if( $inCol === false ) {
				$inCol = 'id';
			}
			$inTable = $db->nameQuote( $inTable );
			$inCol   = $db->nameQuote( $inCol );
			
			$joinQuery = 'INNER JOIN '.$reportsTableName.' AS lim_rpt_rpt'
				."\n".'   ON lim_rpt_rpt.id = '.$inTable.'.'.$inCol
				."\n".'~LIMITINGJOIN~';
			$joinQuery = ApotheosisLibAcl::limitQuery( $joinQuery, 'core.sees', 'lim_rpt_rpt', 'role', $uId, $actionId );
			break;
		
		case( 'groups' ):
			$tableName = ApotheosisLibAcl::getUserTable( 'report.groups', $uId );
			
			if( !is_null($limitParts[1]) ) {
				$cIdArray = explode( '~', $limitParts[1] );
				foreach( $cIdArray as $k=>$id ) {
					$cIdArray[$k] = $db->Quote( $id );
				}
				$cIds = implode( ',', $cIdArray );
				
				$query = 'SELECT '.$db->nameQuote( 'cycle' )
					."\n".'FROM '.$tableName
					."\n".'WHERE '.$db->nameQuote( 'cycle' ).' IN ('.$cIds.')'
					."\n".'  AND '.$db->nameQuote( 'group' ).' = 0';
				$db->setQuery( $query );
				$r = $db->loadResultArray();
				
				if( !empty($r) ) {
					$null = null;
					ApotheosisLib::callAll( 'helpers'.DS.'auth.php', 'ApothAuth', 'setUserReportGroups', array($tableName, $uId, $from, $to, $r), $null, true );
				}
			}
			
			if( $inTable === false ) {
				$inTable = '#__apoth_cm_courses';
			}
			if( $inCol === false ) {
				$inCol = 'id';
			}
			$inTable = $db->nameQuote( $inTable );
			$inCol   = $db->nameQuote( $inCol );
			
			$joinQuery = 'INNER JOIN '.$tableName.' AS lim_rpt_grp'
				."\n".'   ON lim_rpt_grp.group = '.$inTable.'.'.$inCol
				."\n". ' AND lim_rpt_grp.cycle IN ('.$cIds.')'
				."\n".'~LIMITINGJOIN~';
			$joinQuery = ApotheosisLibAcl::limitQuery( $joinQuery, 'core.sees', 'lim_rpt_grp', 'role', $uId, $actionId );
			break;
		
		case( 'people' ):
			$tableName = ApotheosisLibAcl::getUserTable( 'report.people', $uId );
			
			if( !is_null($limitParts[1]) ) {
				$cIdArray = explode( '~', $limitParts[1] );
				foreach( $cIdArray as $k=>$id ) {
					$cIdQuoted[$k] = $db->Quote( $id );
				}
				$cIds = implode( ',', $cIdQuoted );
				
				$query = 'SELECT '.$db->nameQuote( 'cycle' )
					."\n".'FROM '.$tableName
					."\n".'WHERE '.$db->nameQuote( 'cycle' ).' IN ('.$cIds.')'
					."\n".'  AND '.$db->nameQuote( 'person' ).' = '.$db->Quote( '0' );
				$db->setQuery( $query );
				$r = $db->loadResultArray();
				
				if( !empty($r) ) {
					$null = null;
					ApotheosisLib::callAll( 'helpers'.DS.'auth.php', 'ApothAuth', 'setUserReportPeople', array($tableName, $uId, $from, $to, $cIdArray), $null, true );
				}
			}
			
			if( $inTable === false ) {
				$inTable = '#__apoth_ppl_people';
			}
			if( $inCol === false ) {
				$inCol = 'id';
			}
			$inTable = $db->nameQuote( $inTable );
			$inCol   = $db->nameQuote( $inCol );
			
			$joinQuery = 'INNER JOIN '.$tableName.' AS lim_rpt_ppl'
				."\n".'  ON lim_rpt_ppl.person = '.$inTable.'.'.$inCol
				."\n".'~LIMITINGJOIN~';
			$joinQuery = ApotheosisLibAcl::limitQuery( $joinQuery, 'core.sees', 'lim_rpt_ppl', 'role', $uId, $actionId );
			break;
		
		default:
			$joinQuery = '';
		}
		
		return str_replace( $joinSlug, $joinQuery, $givenQuery );
	}
	
	/**
	 * Creates and populates the user's report reports tmp table if it doesn't already exist.
	 * Gives back the name of that table (with minimum overhead if it exists) 
	 * 
	 * @param $tableName string  The name of the short-term table to be created / populated
	 * @param $uId string  The jUserId of the user whose access should be used. Defaults to current user
	 * @return string  The name of the table which now contains this user's reports
	 */
	function createTblUserReports( $tableName, $uId )
	{
		$user = &ApotheosisLib::getUser( $uId );
		$uId = $user->id;
		$from = ApotheosisLibAcl::getDatum( 'dateFrom' );
		$to = ApotheosisLibAcl::getDatum( 'dateTo' );
		$db = &JFactory::getDBO();
		
		if( !ApotheosisLibDbTmp::getExists( $tableName ) ) {
			$createQuery = 'CREATE TABLE ~TABLE~ ENGINE=MyISAM AS'
				."\n".'SELECT '.$db->nameQuote( 'rpts' ).'.'.$db->nameQuote( 'cycle' ).' AS '.$db->nameQuote( 'cycle' ).', '
				               .$db->nameQuote( 'rpts' ).'.'.$db->nameQuote( 'id' ).' AS '.$db->nameQuote( 'id' ).', '
				               .$db->nameQuote( 'r' ).'.'.$db->nameQuote( 'id' ).' AS '.$db->nameQuote( 'role' )
				."\n".'FROM '.$db->nameQuote( '#__apoth_rpt_reports' ).' AS '.$db->nameQuote( 'rpts' )
				."\n".'INNER JOIN '.$db->nameQuote( '#__apoth_sys_roles' ).' AS '.$db->nameQuote( 'r' )
				."\n".'LIMIT 0';
			ApotheosisLibDbTmp::create( $tableName, $createQuery );
		}
		
		// we're using a fresh table, so populate it with data according to the various components
		$null = null;
		ApotheosisLib::callAll( 'helpers'.DS.'auth.php', 'ApothAuth', 'setUserReportReports', array($tableName, $uId, $from, $to), $null, true );
		
		$alterQuery = 'ALTER TABLE '.$tableName
			."\n".' ADD PRIMARY KEY('.$db->nameQuote( 'cycle' ).', '.$db->nameQuote( 'id' ).', '.$db->nameQuote( 'role' ).'),'
			."\n".' ADD INDEX('.$db->nameQuote( 'id' ).'),'
			."\n".' ADD INDEX('.$db->nameQuote( 'role').')';
		$db->setQuery( $alterQuery );
		$db->Query();
		
		ApotheosisLibDbTmp::setPopulated( $tableName );
		return $tableName;
	}
	
	/**
	 * Creates and populates the user's report groups tmp table if it doesn't already exist.
	 * Gives back the name of that table (with minimum overhead if it exists) 
	 * 
	 * @param $tableName string  The name of the short-term table to be created / populated
	 * @param $uId string  The jUserId of the user whose access should be used. Defaults to current user
	 * @return string  The name of the table which now contains this user's report groups
	 */
	function createTblUserGroups( $tableName, $uId )
	{
		$user = &ApotheosisLib::getUser( $uId );
		$uId = $user->id;
		$from = ApotheosisLibAcl::getDatum( 'dateFrom' );
		$to = ApotheosisLibAcl::getDatum( 'dateTo' );
		$db = &JFactory::getDBO();
		
		if( !ApotheosisLibDbTmp::getExists( $tableName ) ) {
			$createQuery = 'CREATE TABLE ~TABLE~ ENGINE=MyISAM AS'
				."\n".'SELECT '.$db->nameQuote( 'ad' ).'.'.$db->nameQuote( 'cycle' ).' AS '.$db->nameQuote( 'cycle' ).', '
				               .$db->nameQuote( 'ad' ).'.'.$db->nameQuote( 'group' ).' AS '.$db->nameQuote( 'group' ).', '
				               .$db->nameQuote( 'r' ).'.'.$db->nameQuote( 'id' ).' AS '.$db->nameQuote( 'role' )
				."\n".'FROM '.$db->nameQuote( '#__apoth_rpt_admins' ).' AS '.$db->nameQuote( 'ad' )
				."\n".'INNER JOIN '.$db->nameQuote( '#__apoth_sys_roles' ).' AS '.$db->nameQuote( 'r' )
				."\n".'LIMIT 0';
			ApotheosisLibDbTmp::create( $tableName, $createQuery );
		}
		
		// we're using a fresh table, so populate it with data according to the various components
		$null = null;
		ApotheosisLib::callAll( 'helpers'.DS.'auth.php', 'ApothAuth', 'setUserReportGroups', array($tableName, $uId, $from, $to), $null, true );
		
		$alterQuery = 'ALTER TABLE '.$tableName
			."\n".' ADD PRIMARY KEY('.$db->nameQuote( 'cycle' ).', '.$db->nameQuote( 'group' ).', '.$db->nameQuote( 'role' ).'),'
			."\n".' ADD INDEX('.$db->nameQuote( 'group' ).'),'
			."\n".' ADD INDEX('.$db->nameQuote( 'role' ).')';
		$db->setQuery( $alterQuery );
		$db->Query();
		
		ApotheosisLibDbTmp::setPopulated( $tableName );
		return $tableName;
	}
	
	/**
	 * Creates and populates the user's report people tmp table if it doesn't already exist.
	 * Gives back the name of that table (with minimum overhead if it exists) 
	 * 
	 * @param $tableName string  The name of the short-term table to be created / populated
	 * @param $uId string  The jUserId of the user whose access should be used. Defaults to current user
	 * @return string  The name of the table which now contains this user's reports
	 */
	function createTblUserPeople( $tableName, $uId )
	{
		$user = &ApotheosisLib::getUser( $uId );
		$uId = $user->id;
		$from = ApotheosisLibAcl::getDatum( 'dateFrom' );
		$to = ApotheosisLibAcl::getDatum( 'dateTo' );
		$db = &JFactory::getDBO();
		
		if( !ApotheosisLibDbTmp::getExists( $tableName ) ) {
				$createQuery = 'CREATE TABLE ~TABLE~ ENGINE=MyISAM AS'
				."\n".'SELECT '.$db->nameQuote( 'ad' ).'.'.$db->nameQuote( 'cycle' ).' AS '.$db->nameQuote( 'cycle' ).', '
				               .$db->nameQuote( 'ad' ).'.'.$db->nameQuote( 'person' ).' AS '.$db->nameQuote( 'person' ).', '
				               .$db->nameQuote( 'r' ).'.'.$db->nameQuote( 'id' ).' AS '.$db->nameQuote( 'role' )
				."\n".'FROM '.$db->nameQuote( '#__apoth_rpt_admins' ).' AS '.$db->nameQuote( 'ad' )
				."\n".'INNER JOIN '.$db->nameQuote( '#__apoth_sys_roles' ).' AS '.$db->nameQuote( 'r' )
				."\n".'LIMIT 0';
			ApotheosisLibDbTmp::create( $tableName, $createQuery );
		}
		
		// we're using a fresh table, so populate it with data according to the various components
		$null = null;
		ApotheosisLib::callAll( 'helpers'.DS.'auth.php', 'ApothAuth', 'setUserReportPeople', array($tableName, $uId, $from, $to), $null, true );
		
		$alterQuery = 'ALTER TABLE '.$tableName
			."\n".' ADD PRIMARY KEY('.$db->nameQuote( 'cycle' ).', '.$db->nameQuote( 'person' ).', '.$db->nameQuote( 'role' ).'),'
			."\n".' ADD INDEX('.$db->nameQuote( 'person' ).'),'
			."\n".' ADD INDEX('.$db->nameQuote( 'role').')';
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
		$db = &JFactory::getDBO();
		
		$user = &ApotheosisLib::getUser( $uId );
		
		// author roles
		$query = 'SELECT '.$db->nameQuote( 'id' )
			."\n".'FROM '.$db->nameQuote( '#__apoth_rpt_reports' )
			."\n".'WHERE '.$db->nameQuote( 'author' ).' = '.$db->Quote( $user->person_id );
		$db->setQuery( $query );
		$r = $db->loadResult();
		
		if( !empty($r) ) {
			$values[] = '('.$db->Quote( ApotheosisLibAcl::getRoleId('report_author') ).')';
		}
		
		// student roles
		$query = 'SELECT '.$db->nameQuote( 'id' )
			."\n".'FROM '.$db->nameQuote( '#__apoth_rpt_reports' )
			."\n".'WHERE '.$db->nameQuote( 'student' ).' = '.$db->Quote( $user->person_id );
		$db->setQuery( $query );
		$r = $db->loadResult();
		
		if( !empty($r) ) {
			$values[] = '('.$db->Quote( ApotheosisLibAcl::getRoleId('report_student') ).')';
		}
		
		// last editor roles
		$query = 'SELECT '.$db->nameQuote( 'id' )
			."\n".'FROM '.$db->nameQuote( '#__apoth_rpt_reports' )
			."\n".'WHERE '.$db->nameQuote( 'last_modified_by' ).' = '.$db->Quote( $user->person_id );
		$db->setQuery( $query );
		$r = $db->loadResult();
		
		if( !empty($r) ) {
			$values[] = '('.$db->Quote( ApotheosisLibAcl::getRoleId('report_last editor') ).')';
		}
		
		// admin roles
		$query = 'SELECT '.$db->nameQuote( 'cycle' )
			."\n".'FROM '.$db->nameQuote( '#__apoth_rpt_admins' )
			."\n".'WHERE '.$db->nameQuote( 'person' ).' = '.$db->Quote( $user->person_id );
		$db->setQuery( $query );
		$r = $db->loadResult();
		
		if( !empty($r) ) {
			$values[] = '('.$db->Quote( ApotheosisLibAcl::getRoleId('report_admin') ).')';
		}
		
		// peer roles
		$query = 'SELECT '.$db->nameQuote( 'cycle' )
			."\n".'FROM '.$db->nameQuote( '#__apoth_rpt_peers' )
			."\n".'WHERE '.$db->nameQuote( 'person' ).' = '.$db->Quote( $user->person_id );
		$db->setQuery( $query );
		$r = $db->loadResult();
		
		// all tutors should have the peer role
		$tg = ApotheosisData::_( 'timetable.tutorgroup', $user->person_id );
		$query = 'SELECT '.$db->nameQuote( 'cycle' )
			."\n".'FROM '.$db->nameQuote( '#__apoth_rpt_cycles_groups' )
			."\n".'WHERE '.$db->nameQuote( 'group' ).' = '.$db->Quote( $tg );
		$db->setQuery( $query );
		$r = $db->loadResult();
		
		if( !empty($r) ) {
			$values[] = '('.$db->Quote( ApotheosisLibAcl::getRoleId('report_peer') ).')';
		}
		
		if( !empty($values) ) {
			$insertQuery = 'INSERT INTO '.$tableName
				."\n".'VALUES '.implode(', ', $values);
			$db->setQuery( $insertQuery );
			$db->Query();
		}
	}
	
	/**
	 * Inserts allowed-reports into the user table named
	 * Gets user tables: report.groups
	 * 
	 * @param $tableName string  The name of the table into which to insert data
	 * @param $uId string  The jUserId of the user whose access should be used. Defaults to current user
	 * @param $from string  The date from which to consider people. Defaults to now. 
	 * @param $to string  The date up to which to consider people. Defaults to now.
	 * @param $cId array|false  An array of cycle IDs or false
	 */
	function setUserReportReports( $tableName, $uId, $from, $to, $cId = false )
	{
		$db = &JFactory::getDBO();
		
		$user = &ApotheosisLib::getUser( $uId );
		
		$authorId       = ApotheosisLibAcl::getRoleId( 'report_author' );
		$studentId      = ApotheosisLibAcl::getRoleId( 'report_student' );
		$lastEditorId   = ApotheosisLibAcl::getRoleId( 'report_last editor' );
		$groupPeerId    = ApotheosisLibAcl::getRoleId( 'group_peer_teacher' );
		$groupStudentId = ApotheosisLibAcl::getRoleId( 'group_participant_student' );
		$groupTeacherId = ApotheosisLibAcl::getRoleId( 'group_supervisor_teacher' );
		
		if( !is_array($cId) ) {
			$blankQuery = 'INSERT IGNORE INTO '.$tableName
				."\n".'SELECT '.$db->nameQuote( 'id' ).', 0, 0'
				."\n".'FROM '.$db->nameQuote( '#__apoth_rpt_cycles' );
			$db->setQuery( $blankQuery );
			$db->Query();
		}
		else {
			foreach( $cId as $k=>$id ) {
				// nameQuote cycle ID's
				$cId[$k] = $db->Quote( $id );
				
				// get reports as author
				$insertQuery = 'INSERT IGNORE INTO '.$tableName
					."\n".'SELECT DISTINCT '.$cId[$k].', '.$db->nameQuote( 'id' ).', '.$db->Quote( $authorId )
					."\n".'FROM '.$db->nameQuote( '#__apoth_rpt_reports' )
					."\n".'WHERE '.$db->nameQuote( 'author' ).' = '.$db->Quote( $user->person_id )
					."\n".'  AND '.$db->nameQuote( 'cycle' ).' = '.$cId[$k].'; '
				
				// get reports as student
					."\n"
					."\n".'INSERT IGNORE INTO '.$tableName
					."\n".'SELECT DISTINCT '.$cId[$k].', '.$db->nameQuote( 'id' ).', '.$db->Quote( $studentId )
					."\n".'FROM '.$db->nameQuote( '#__apoth_rpt_reports' )
					."\n".'WHERE '.$db->nameQuote( 'student' ).' = '.$db->Quote( $user->person_id )
					."\n".'  AND '.$db->nameQuote( 'cycle' ).' = '.$cId[$k].'; '
				
				// get reports as last editor
					."\n"
					."\n".'INSERT IGNORE INTO '.$tableName
					."\n".'SELECT DISTINCT '.$cId[$k].', '.$db->nameQuote( 'id' ).', '.$db->Quote( $lastEditorId )
					."\n".'FROM '.$db->nameQuote( '#__apoth_rpt_reports' )
					."\n".'WHERE '.$db->nameQuote( 'last_modified_by' ).' = '.$db->Quote( $user->person_id )
					."\n".'  AND '.$db->nameQuote( 'cycle' ).' = '.$cId[$k].'; ';
				$db->setQuery( $insertQuery );
				$db->QueryBatch();
				
				// get reports based on group memberships
				$groupsTableName = ApotheosisLibAcl::getUserTable( 'report.groups', $user->id );
				$insertQuery = 'INSERT IGNORE INTO '.$tableName
				."\n".'SELECT DISTINCT '.$cId[$k].', '.$db->nameQuote( 'rpt' ).'.'.$db->nameQuote( 'id' ).', '.$db->nameQuote( 'gtn' ).'.'.$db->nameQuote( 'role' )
					."\n".'FROM '.$db->nameQuote( '#__apoth_rpt_reports' ).' AS '.$db->nameQuote( 'rpt' )
					."\n".'INNER JOIN '.$db->nameQuote( $groupsTableName ).' AS '.$db->nameQuote( 'gtn' )
					."\n".'   ON '.$db->nameQuote( 'rpt' ).'.'.$db->nameQuote( 'group' ).' = '.$db->nameQuote( 'gtn' ).'.'.$db->nameQuote( 'group' )
					."\n".'  AND '.$db->nameQuote( 'rpt' ).'.'.$db->nameQuote( 'cycle' ).' = '.$db->nameQuote( 'gtn' ).'.'.$db->nameQuote( 'cycle' )
					."\n".'WHERE '.$db->nameQuote( 'rpt' ).'.'.$db->nameQuote( 'cycle' ).' = '.$cId[$k];
				$db->setQuery( $insertQuery );
				$db->Query();
				
				$pastoralQuery = 'INSERT IGNORE INTO '.$tableName
					."\n".'SELECT DISTINCT '.$cId[$k].', '.$db->nameQuote( 'rpt' ).'.'.$db->nameQuote( 'id' ).', '.$db->Quote( $groupPeerId )
					."\n".'FROM '.$db->nameQuote( '#__apoth_tt_group_members' ).' AS '.$db->nameQuote( 'gm' )
					."\n".'INNER JOIN '.$db->nameQuote( '#__apoth_cm_courses' ).' AS '.$db->nameQuote( 'cm' )
					."\n".'   ON '.$db->nameQuote( 'cm' ).'.'.$db->nameQuote( 'id').' = '.$db->nameQuote( 'gm').'.'.$db->nameQuote( 'group_id' )
					."\n".'  AND '.$db->nameQuote( 'cm' ).'.'.$db->nameQuote( 'ext_type' ).' = '.$db->Quote( 'pastoral' )
					."\n".'  AND '.$db->nameQuote( 'cm' ).'.'.$db->nameQuote( 'deleted' ).' = '.$db->Quote( '0' )
					."\n".'INNER JOIN '.$db->nameQuote( '#__apoth_tt_group_members' ).' AS '.$db->nameQuote( 'gm2' )
					."\n".'   ON '.$db->nameQuote( 'gm2' ).'.'.$db->nameQuote( 'group_id' ).' = '.$db->nameQuote( 'cm' ).'.'.$db->nameQuote( 'id' )
					."\n".'  AND '.$db->nameQuote( 'gm2' ).'.'.$db->nameQuote( 'role' ).' = '.$db->Quote( $groupStudentId )
					."\n".'INNER JOIN '.$db->nameQuote( '#__apoth_rpt_reports' ).' AS '.$db->nameQuote( 'rpt' )
					."\n".'   ON '.$db->nameQuote( 'rpt' ).'.'.$db->nameQuote( 'student' ).' = '.$db->nameQuote( 'gm2' ).'.'.$db->nameQuote( 'person_id' )
					."\n".'  AND '.$db->nameQuote( 'rpt' ).'.'.$db->nameQuote( 'cycle' ).' = '.$cId[$k]
					."\n".'WHERE '.$db->nameQuote( 'gm' ).'.'.$db->nameQuote( 'person_id' ).' = '.$db->Quote( $user->person_id )
					."\n".'  AND '.$db->nameQuote( 'gm' ).'.'.$db->nameQuote( 'role' ).' = '.$db->Quote( $groupTeacherId )
					."\n".'  AND '.ApotheosisLibDb::dateCheckSql( 'gm.valid_from', 'gm.valid_to', $from, $to )
					."\n".'  AND '.ApotheosisLibDb::dateCheckSql( 'gm2.valid_from', 'gm2.valid_to', $from, $to );
				$db->setQuery( $pastoralQuery );
				$db->Query();
			}
			
			$cIds = implode( ',', $cId );
			
			// delete cycle holding entry
			$deleteQuery = 'DELETE FROM '.$tableName
				."\n".'WHERE '.$db->nameQuote( 'cycle' ).' IN ('.$cIds.')'
				."\n".'  AND '.$db->nameQuote( 'id' ).' = 0';
			$db->setQuery( $deleteQuery );
			$db->Query();
		}
	}
	
	/**
	 * Inserts allowed-groups data into the user table named
	 * 
	 * @param $tableName string  The name of the table into which to insert data
	 * @param $uId string  The jUserId of the user whose access should be used. Defaults to current user
	 * @param $from string  The date from which to consider groups. Defaults to now. 
	 * @param $to string  The date up to which to consider groups. Defaults to now.
	 * @param $cId array|false  An array of cycle IDs or false
	 */
	function setUserReportGroups( $tableName, $uId, $from, $to, $cId = false )
	{
		$db = &JFactory::getDBO();
		
		$user = &ApotheosisLib::getUser( $uId );
		
		$adminId = ApotheosisLibAcl::getRoleId( 'report_admin' );
		$peerId  = ApotheosisLibAcl::getRoleId( 'report_peer' );
		
		if( !is_array($cId) ) {
			$blankQuery = 'INSERT IGNORE INTO '.$tableName
				."\n".'SELECT '.$db->nameQuote( 'id' ).', 0, 0'
				."\n".'FROM '.$db->nameQuote( '#__apoth_rpt_cycles' );
			$db->setQuery( $blankQuery );
			$db->Query();
		}
		else {
			foreach( $cId as $k=>$id ) {
				// nameQuote cycle ID's
				$cId[$k] = $db->Quote( $id );
				
				// get cycle from and to dates
				$cycleQuery = 'SELECT '.$db->nameQuote( 'valid_from' ).', '.$db->nameQuote( 'valid_to' )
					."\n".'FROM '.$db->nameQuote( '#__apoth_rpt_cycles' )
					."\n".'WHERE '.$db->nameQuote( 'id' ).' = '.$cId[$k];
				
				$db->setQuery( $cycleQuery );
				$datesArray = $db->loadAssoc();
				
				$cycFrom = array_shift( $datesArray );
				$cycTo = array_shift( $datesArray );
				
				// ### get group memberships with callAll setUserGroups from tt auth for this cycle
				
				// create holding table for setUserGroups data
				$holdingTableName = ApotheosisLibDbTmp::getTable( 'report', 'holding_groups', $user->id, false, false, false );
				if( !ApotheosisLibDbTmp::getExists( $holdingTableName ) ) {
					$createQuery = 'CREATE TABLE ~TABLE~ ENGINE=MyISAM AS'
						."\n".'SELECT '.$db->nameQuote( 'ad' ).'.'.$db->nameQuote( 'group' ).' AS '.$db->nameQuote( 'group' ).', '
						               .$db->nameQuote( 'r' ).'.'.$db->nameQuote( 'id' ).' AS '.$db->nameQuote( 'role' )
						."\n".'FROM '.$db->nameQuote( '#__apoth_rpt_admins' ).' AS '.$db->nameQuote( 'ad' )
						."\n".'INNER JOIN '.$db->nameQuote( '#__apoth_sys_roles' ).' AS '.$db->nameQuote( 'r' )
						."\n".'LIMIT 0';
					ApotheosisLibDbTmp::create( $holdingTableName, $createQuery );
				}
				
				// populate holding table
				if( !ApotheosisLibDbTmp::getPopulated( $holdingTableName ) ) {
					ApotheosisLib::callAll( 'helpers'.DS.'auth.php', 'ApothAuth', 'setUserGroups', array($holdingTableName, $user->id, $cycFrom, $cycTo), $null, true );
					ApotheosisLibDbTmp::setPopulated( $holdingTableName );
				}
				
				//now populate the TmpReportGroups table and empty holding table
				$copyQuery = 'INSERT IGNORE INTO '.$tableName
					."\n".'SELECT '.$cId[$k].', '
					               .$db->nameQuote( 'htn' ).'.'.$db->nameQuote( 'group' ).', '
					               .$db->nameQuote( 'htn' ).'.'.$db->nameQuote( 'role' )
					."\n".'FROM '.$holdingTableName.' AS '.$db->nameQuote( 'htn' )
					."\n".'INNER JOIN '.$db->nameQuote( '#__apoth_rpt_cycles_groups' ).' AS '.$db->nameQuote( 'cg' )
					."\n".'   ON '.$db->nameQuote( 'cg' ).'.'.$db->nameQuote( 'group' ).' = '.$db->nameQuote( 'htn' ).'.'.$db->nameQuote( 'group' )
					."\n".'  AND '.$db->nameQuote( 'cg' ).'.'.$db->nameQuote( 'cycle' ).' = '.$cId[$k].'; ';
				
				$db->setQuery( $copyQuery );
				$db->queryBatch();
				
				ApotheosisLibDbTmp::clear( $holdingTableName ); // groups will vary between itterations as we're looking based on cycle dates
				
				// ### get report admin groups for this cycle
				$adminsQuery = 'SELECT DISTINCT '.$db->nameQuote( 'group' )
					."\n".'FROM '.$db->nameQuote( '#__apoth_rpt_admins' )
					."\n".'WHERE '.$db->nameQuote( 'person' ).' = '.$db->Quote( $user->person_id )
					."\n".'  AND '.$db->nameQuote( 'cycle' ).' = '.$cId[$k]
					."\n".'  AND '.ApotheosisLibDb::dateCheckSql( 'valid_from', 'valid_to', $cycFrom, $cycTo );
				$db->setQuery( $adminsQuery );
				$adminRaw = $db->loadResultArray();
				
				// only proceed if we pulled out any admin groups
				if( !empty($adminRaw) ) {
					foreach( $adminRaw as $i=>$group ) {
						$adminRaw[$i] = $db->Quote( $group );
					}
					$adminArray = implode( ',', $adminRaw );
					
					// get admin course descandants
					$adminDescQuery = 'INSERT IGNORE INTO '.$tableName
						."\n".'SELECT DISTINCT '.$cId[$k].', '.$db->nameQuote( 'anc' ).'.'.$db->nameQuote( 'id' ).', '.$db->Quote( $adminId )
						."\n".'FROM '.$db->nameQuote( '#__apoth_cm_courses_ancestry' ).' AS '.$db->nameQuote( 'anc' )
						."\n".'INNER JOIN '.$db->nameQuote( '#__apoth_cm_courses' ).' AS '.$db->nameQuote( 'cm' )
						."\n".'   ON '.$db->nameQuote( 'cm' ).'.'.$db->nameQuote( 'id' ).' = '.$db->nameQuote( 'anc' ).'.'.$db->nameQuote( 'id' )
						."\n".'  AND '.$db->nameQuote( 'cm' ).'.'.$db->nameQuote( 'deleted' ).' = '.$db->Quote( '0' )
						."\n".'WHERE '.$db->nameQuote( 'anc' ).'.'.$db->nameQuote( 'ancestor' ).' IN ('.$adminArray.')'
						."\n".'  AND '.ApotheosisLibDb::dateCheckSql( 'cm.start_date', 'cm.end_date', $cycFrom, $cycTo );
					$db->setQuery( $adminDescQuery );
					$db->Query();
				}
				
				// ### get report peer groups for this cycle
				$peersQuery = 'SELECT DISTINCT '.$db->nameQuote( 'group' )
					."\n".'FROM '.$db->nameQuote( '#__apoth_rpt_peers' )
					."\n".'WHERE '.$db->nameQuote( 'person' ).' = '.$db->Quote( $user->person_id )
					."\n".'  AND '.$db->nameQuote( 'cycle' ).' = '.$cId[$k]
					."\n".'  AND '.ApotheosisLibDb::dateCheckSql( 'valid_from', 'valid_to', $cycFrom, $cycTo );
				$db->setQuery( $peersQuery );
				$peerRaw = $db->loadResultArray();
				
				// only proceed if we pulled out any peer groups
				if( !empty($peerRaw) ) {
					foreach( $peerRaw as $i=>$group ) {
						$peerRaw[$i] = $db->Quote( $group );
					}
					$peerArray = implode( ',', $peerRaw );
					
					// get peer course descandants
					$peerDescQuery = 'INSERT IGNORE INTO '.$tableName
						."\n".'SELECT DISTINCT '.$cId[$k].', '.$db->nameQuote( 'anc' ).'.'.$db->nameQuote( 'id' ).', '.$db->Quote( $peerId )
						."\n".'FROM '.$db->nameQuote( '#__apoth_cm_courses_ancestry' ).' AS '.$db->nameQuote( 'anc' )
						."\n".'INNER JOIN '.$db->nameQuote( '#__apoth_cm_courses' ).' AS '.$db->nameQuote( 'cm' )
						."\n".'   ON '.$db->nameQuote( 'cm' ).'.'.$db->nameQuote( 'id' ).' = '.$db->nameQuote( 'anc' ).'.'.$db->nameQuote( 'id' )
						."\n".'  AND '.$db->nameQuote( 'cm' ).'.'.$db->nameQuote( 'deleted' ).' = '.$db->Quote( '0' )
						."\n".'WHERE '.$db->nameQuote( 'anc' ).'.'.$db->nameQuote( 'ancestor' ).' IN ('.$peerArray.')'
						."\n".'  AND '.ApotheosisLibDb::dateCheckSql( 'cm.start_date', 'cm.end_date', $cycFrom, $cycTo );
					$db->setQuery( $peerDescQuery );
					$db->Query();
				}
				
				// ### get tutee groups as a peer
				$gAdminId  = ApotheosisLibAcl::getRoleId( 'group_supervisor_admin' );
				$teacherId = ApotheosisLibAcl::getRoleId( 'group_supervisor_teacher' );
				$studentId = ApotheosisLibAcl::getRoleId( 'group_participant_student' );
				
				$tuteeQuery = 'INSERT IGNORE INTO '.$tableName
					."\n".'SELECT DISTINCT '.$cId[$k].', gm3.group_id, '.$db->Quote( $peerId )
					."\n".'FROM `#__apoth_tt_group_members` AS gm1'
					."\n".'INNER JOIN `#__apoth_rpt_cycles_groups` AS cg1'
					."\n".'   ON cg1.cycle = '.$cId[$k]
					."\n".'  AND cg1.`group` = gm1.group_id'
					."\n".'INNER JOIN `#__apoth_cm_courses` AS c'
					."\n".'   ON c.id = gm1.group_id'
					."\n".'  AND c.type = "pastoral"'
					."\n".'INNER JOIN `#__apoth_tt_group_members` AS gm2'
					."\n".'   ON gm2.group_id = gm1.group_id'
					."\n".'  AND gm2.`role` = '.$studentId
					."\n".'INNER JOIN `#__apoth_tt_group_members` AS gm3'
					."\n".'   ON gm3.person_id = gm2.person_id'
					."\n".'  AND gm3.group_id != gm2.group_id'
					."\n".'  AND gm3.`role` = '.$studentId
					."\n".'INNER JOIN `#__apoth_rpt_cycles_groups` AS cg3'
					."\n".'   ON cg3.cycle = '.$cId[$k]
					."\n".'  AND cg3.`group` = gm3.group_id'
					."\n".'WHERE gm1.`person_id` LIKE '.$db->Quote($user->person_id)
					."\n".'  AND gm1.`role` IN ( '.$gAdminId.','.$teacherId.' )'
					."\n".'  AND '.ApotheosisLibDb::dateCheckSql( 'gm1.valid_from', 'gm1.valid_to', $cycFrom, $cycTo )
					."\n".'  AND '.ApotheosisLibDb::dateCheckSql( 'gm2.valid_from', 'gm2.valid_to', $cycFrom, $cycTo )
					."\n".'  AND '.ApotheosisLibDb::dateCheckSql( 'gm3.valid_from', 'gm3.valid_to', $cycFrom, $cycTo );
				$db->setQuery( $tuteeQuery );
				$db->Query();
			}
			
			$cIds = implode( ',', $cId );
			
			// delete cycle holding entry
			$deleteQuery = 'DELETE FROM '.$tableName
				."\n".'WHERE '.$db->nameQuote( 'cycle' ).' IN ('.$cIds.')'
				."\n".'  AND '.$db->nameQuote( 'group' ).' = 0';
			$db->setQuery( $deleteQuery );
			$db->Query();
		}
	}
	
	/**
	 * Inserts allowed-people data into the user table named
	 * Gets user tables: report.groups
	 * 
	 * @param $tableName string  The name of the table into which to insert data
	 * @param $uId string  The jUserId of the user whose access should be used. Defaults to current user
	 * @param $from string  The date from which to consider people. Defaults to now. 
	 * @param $to string  The date up to which to consider people. Defaults to now.
	 * @param $cId array|false  An array of cycle IDs or false
	 */
	function setUserReportPeople( $tableName, $uId, $from, $to, $cId = false )
	{
		$db = &JFactory::getDBO();
		
		$user = &ApotheosisLib::getUser( $uId );
		
		if( !is_array($cId) ) {
			$blankQuery = 'INSERT IGNORE INTO '.$tableName
				."\n".'SELECT '.$db->nameQuote( 'id' ).', 0, 0'
				."\n".'FROM '.$db->nameQuote( '#__apoth_rpt_cycles' );
			$db->setQuery( $blankQuery );
			$db->Query();
		}
		else {
			foreach( $cId as $k=>$id ) {
				$quotedCycles[$k] = $db->Quote( $id );
				
				// get cycle from and to dates
				$cycleQuery = 'SELECT '.$db->nameQuote( 'valid_from' ).', '.$db->nameQuote( 'valid_to' )
					."\n".'FROM '.$db->nameQuote( '#__apoth_rpt_cycles' )
					."\n".'WHERE '.$db->nameQuote( 'id' ).' = '.$quotedCycles[$k];
				
				$db->setQuery( $cycleQuery );
				$datesArray = $db->loadAssoc();
				
				$cycFrom = array_shift( $datesArray );
				$cycTo = array_shift( $datesArray );
				
				// make sure the report groups table is populated with our cycle's info
				$rgTable = ApotheosisLibAcl::getUserTable( 'report.groups' );
				$dummyQuery = 'SELECT *'
					."\n".'FROM '.$db->nameQuote( '#__apoth_rpt_cycles' )
					."\n".' ~LIMITINGJOIN~';
				$dummyQuery = ApotheosisLibAcl::limitQuery( $dummyQuery, 'report.groups.'.$id );
				$db->setQuery( $dummyQuery );
				$db->Query();
				
				// ### get non-group related people with callAll setUserReportPeople from tt auth for this cycle
				
				// create holding table for setUserReportPeople data
				$holdingTableName = ApotheosisLibDbTmp::getTable( 'report', 'holding_people', $user->id, false, false, false );
				if( !ApotheosisLibDbTmp::getExists( $holdingTableName ) ) {
					$createQuery = 'CREATE TABLE ~TABLE~ ENGINE=MyISAM AS'
						."\n".'SELECT '.$db->nameQuote( 'ppl' ).'.'.$db->nameQuote( 'id' ).' AS '.$db->nameQuote( 'people' ).', '
						               .$db->nameQuote( 'r' ).'.'.$db->nameQuote( 'id' ).' AS '.$db->nameQuote( 'role' )
						."\n".'FROM '.$db->nameQuote( '#__apoth_ppl_people' ).' AS '.$db->nameQuote( 'ppl' )
						."\n".'INNER JOIN '.$db->nameQuote( '#__apoth_sys_roles' ).' AS '.$db->nameQuote( 'r' )
						."\n".'LIMIT 0';
					ApotheosisLibDbTmp::create( $holdingTableName, $createQuery );
				}
				
				
				// populate holding table
				if( !ApotheosisLibDbTmp::getPopulated( $holdingTableName ) ) {
					ApotheosisLib::callAll( 'helpers'.DS.'auth.php', 'ApothAuth', 'setUserReportPeople', array($holdingTableName, $user->id, $cycFrom, $cycTo), $null, true );
					ApotheosisLibDbTmp::setPopulated( $holdingTableName );
				}
				
				// now populate the TmpReportGroups table wih non-group related data
				$groupRole = $db->Quote( ApotheosisLibAcl::getRoleId('any_group') );
				
				// generate temp table of all group related roles
				$insertQuery = 'CREATE TEMPORARY TABLE '.$db->nameQuote( 'tmp_unwanted' ).' AS'
					."\n".'SELECT '.$db->nameQuote( 'id' )
					."\n".'FROM '.$db->nameQuote( '#__apoth_sys_roles_ancestry' )
					."\n".'WHERE '.$db->nameQuote( 'ancestor' ).' = '.$groupRole.';'
					
					// generate temp table of all non-group related roles
					."\n"
					."\n".'CREATE TEMPORARY TABLE '.$db->nameQuote( 'tmp_wanted' ).' AS'
					."\n".'SELECT DISTINCT '.$db->nameQuote( 'r' ).'.'.$db->nameQuote( 'id' )
					."\n".'FROM '.$db->nameQuote( '#__apoth_sys_roles' ).' AS '.$db->nameQuote( 'r' )
					."\n".'LEFT JOIN '.$db->nameQuote( 'tmp_unwanted' ).' AS '.$db->nameQuote( 'u' )
					."\n".'  ON '.$db->nameQuote( 'u' ).'.'.$db->nameQuote( 'id' ).' = '.$db->nameQuote( 'r' ).'.'.$db->nameQuote( 'id' )
					."\n".'WHERE '.$db->nameQuote( 'u' ).'.'.$db->nameQuote( 'id' ).' IS NULL;'
					
					// insert non-group related roles
					."\n"
					."\n".'INSERT IGNORE INTO '.$tableName
					."\n".'SELECT DISTINCT '.$quotedCycles[$k].', '
					                        .$db->nameQuote( 'htn' ).'.'.$db->nameQuote( 'people' ).', '
					                        .$db->nameQuote( 'htn' ).'.'.$db->nameQuote( 'role' )
					."\n".'FROM '.$holdingTableName.' AS '.$db->nameQuote( 'htn' )
					."\n".'INNER JOIN '.$db->nameQuote( 'tmp_wanted' ).' AS '.$db->nameQuote( 'w' )
					."\n".'   ON '.$db->nameQuote( 'w' ).'.'.$db->nameQuote( 'id' ).' = '.$db->nameQuote( 'htn' ).'.'.$db->nameQuote( 'role' ).';'
					
					// ### get group related people with callAll setUserGroups from tt auth for this cycle
					
					// get the members of groups in our cycle
					."\n"
					."\n".'INSERT IGNORE INTO '.$tableName
					."\n".'SELECT DISTINCT '.$quotedCycles[$k].', '
					                        .$db->nameQuote( 'gm' ).'.'.$db->nameQuote( 'person_id' ).', '
					                        .$db->nameQuote( 'rg' ).'.'.$db->nameQuote( 'role' )
					."\n".'FROM '.$db->nameQuote( '#__apoth_tt_group_members' ).' AS '.$db->nameQuote( 'gm' )
					."\n".'INNER JOIN '.$db->nameQuote( $rgTable ).' AS '.$db->nameQuote( 'rg' )
					."\n".'   ON '.$db->nameQuote( 'rg' ).'.'.$db->nameQuote( 'group' ).' = '.$db->nameQuote( 'gm' ).'.'.$db->nameQuote( 'group_id' )
					."\n".'  AND '.$db->nameQuote( 'rg' ).'.'.$db->nameQuote( 'cycle' ).' = '.$quotedCycles[$k]
					."\n".'INNER JOIN '.$db->nameQuote( 'tmp_unwanted' ).' AS '.$db->nameQuote( 'grps' )
					."\n".'   ON '.$db->nameQuote( 'grps' ).'.'.$db->nameQuote( 'id' ).' = '.$db->nameQuote( 'rg' ).'.'.$db->nameQuote( 'role' )
					."\n".'WHERE '.ApotheosisLibDb::dateCheckSql( 'gm.valid_from', 'gm.valid_to', $cycFrom, $cycTo ).';'
					
					// tidy up
					."\n"
					."\n".'DROP TABLE IF EXISTS tmp_unwanted;'
					."\n".'DROP TABLE IF EXISTS tmp_wanted;';
				$db->setQuery( $insertQuery );
				$db->queryBatch();
				ApotheosisLibDbTmp::clear( $holdingTableName ); // groups will vary between itterations as we're looking based on cycle dates
			}
			$quotedCycles = implode( ', ', $quotedCycles );
			
			// delete cycle holding entry
			$deleteQuery = 'DELETE FROM '.$tableName
				."\n".'WHERE '.$db->nameQuote( 'cycle' ).' IN ('.$quotedCycles.')'
				."\n".'  AND '.$db->nameQuote( 'person' ).' = '.$db->Quote( '0' );
			$db->setQuery( $deleteQuery );
			$db->Query();
			
			$reportRoles['admin'] = $db->Quote( ApotheosisLibAcl::getRoleId('report_admin') );
			$reportRoles['peer'] = $db->Quote( ApotheosisLibAcl::getRoleId('report_peer') );
			$reportRoles = implode( ', ', $reportRoles );
			$cycles = implode( '~', $cId );
			
			$insertQuery = 'INSERT IGNORE INTO '.$db->nameQuote( $tableName )
				."\n".'SELECT DISTINCT'.$db->nameQuote( 'lim_rpt_grp' ).'.'.$db->nameQuote( 'cycle' ).', '
					.$db->nameQuote( 'gm' ).'.'.$db->nameQuote( 'person_id' ).', '
					.$db->nameQuote( 'lim_rpt_grp' ).'.'.$db->nameQuote( 'role' )
				."\n".'FROM '.$db->nameQuote( '#__apoth_tt_group_members' ).' AS '.$db->nameQuote( 'gm' )
				."\n".'INNER JOIN '.$db->nameQuote( '#__apoth_rpt_cycles_groups' ).' AS '.$db->nameQuote( 'cg' )
				."\n".'   ON '.$db->nameQuote( 'cg' ).'.'.$db->nameQuote( 'group' ).' = '.$db->nameQuote( 'gm' ).'.'.$db->nameQuote( 'group_id' )
				."\n".'  AND '.$db->nameQuote( 'cg' ).'.'.$db->nameQuote( 'cycle' ).' IN ('.$quotedCycles.')'
				."\n".'~LIMITINGJOIN~'
				."\n".'~LIMITINGJOIN2~'
				."\n".'WHERE '.$db->nameQuote( 'lim_rpt_grp' ).'.'.$db->nameQuote( 'role' ).' IN ('.$reportRoles.')';
			$insertQuery = ApotheosisLibAcl::limitQuery( $insertQuery, 'report.groups.'.$cycles, 'cg', 'group', $user->id, $actionId );
			$insertQuery = ApotheosisLibAcl::limitQuery( $insertQuery, 'report.cycles.'.$cycles, 'cg', 'cycle', $user->id, $actionId, '~LIMITINGJOIN2~' );
			$db->setQuery( $insertQuery );
			$db->Query();
			
		}
	}
}
?>