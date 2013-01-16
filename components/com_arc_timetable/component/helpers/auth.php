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

class ApothAuth_Timetable
{
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
	function limitQuery($givenQuery, $limitOn, $inTable = false, $inCol = false, $uId = false, $actionId = false, $joinSlug = '~LIMITINGJOIN~' )
	{
		$db = &JFactory::getDBO();
		$user = &ApotheosisLib::getUser( $uId );
		
		$tableName = ApotheosisLibAcl::getUserTable( 'timetable.groups', $user->id );
		
		switch( $limitOn ) {
		case( 'groups' ):
			if( $inTable === false ) {
				$inTable = 'g';
			}
			if( $inCol === false ) {
				$inCol = 'id';
			}
			$inTable = $db->nameQuote( $inTable );
			$inCol   = $db->nameQuote( $inCol );
			
			$joinQuery = 'INNER JOIN '.$tableName.' AS lim_tt_g'
				."\n".'   ON lim_tt_g.id = '.$inTable.'.'.$inCol
				."\n".'~LIMITINGJOIN~';
			$joinQuery = ApotheosisLibAcl::limitQuery( $joinQuery, 'core.sees', 'lim_tt_g', 'role', $uId, $actionId );
			break;
		
		default:
			$joinQuery = '';
		}
		
		return str_replace( $joinSlug, $joinQuery, $givenQuery );
	}
	
	/**
	 * Creates and populates the user's groups table if it doesn't already exist.
	 * Gves back the name of that table (with minimum overhead if it exists) 
	 * 
	 * @param $tableName string  The name of the short-term table to be created / populated
	 * @param $uId string  The jUserId of the user whose access should be used. Defaults to current user
	 * @return string  The name of the table which now contains this user's groups
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
				."\n".'SELECT c.`id` AS `id`, r.`id` AS `role`'
				."\n".'FROM #__apoth_cm_courses AS c'
				."\n".'INNER JOIN #__apoth_sys_roles AS r'
				."\n".'LIMIT 0';
			ApotheosisLibDbTmp::create( $tableName, $createQuery );
		}
		
		// we're using a fresh table so populate it with data according to the various components
		$results = array();
		ApotheosisLib::callAll( 'helpers'.DS.'auth.php', 'ApothAuth', 'setUserGroups', array($tableName, $uId, $from, $to), $null, true );
		
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
		
		$db = &JFactory::getDBO();
		
		$query = 'SELECT gm.role'
			."\n".', MAX( IF(cdesc.id IS NULL, 0, 1) ) AS has_descendants'
			."\n".', MAX( IF(casc.parent IS NULL , 0, 1) ) AS has_ascendants'
			."\n".'FROM #__apoth_tt_group_members AS gm'
			."\n".'INNER JOIN #__apoth_ppl_people AS p'
			."\n".'   ON p.id = gm.person_id'
			."\n".'  AND p.juserid = '.$db->Quote( $uId )
			."\n".'LEFT JOIN #__apoth_cm_courses AS cdesc'
			."\n".'  ON cdesc.parent = gm.group_id'
			."\n".' AND cdesc.deleted = '.$db->Quote( '0' )
			."\n".'LEFT JOIN #__apoth_cm_courses AS casc'
			."\n".'  ON casc.id = gm.group_id'
			."\n".' AND casc.deleted = '.$db->Quote( '0' )
			."\n".'WHERE '.ApotheosisLibDb::dateCheckSql( 'gm.valid_from', 'gm.valid_to', date( 'Y-m-d H:i:s' ), date( 'Y-m-d H:i:s' ) )
			."\n".'GROUP BY gm.role';
		$db->setQuery( $query );
		$rolesArr = $db->loadObjectList();
		
		$roles = array();
		if( !is_null($rolesArr) ) {
			$adminId =   ApotheosisLibAcl::getRoleId( 'group_supervisor_admin' );
			$teacherId = ApotheosisLibAcl::getRoleId( 'group_supervisor_teacher' );
			$studentId = ApotheosisLibAcl::getRoleId( 'group_participant_student' );
			$watcherId = ApotheosisLibAcl::getRoleId( 'group_participant_watcher' );
			
			$adminAncId =   ApotheosisLibAcl::getRoleId( 'group_ancestor_admin' );
			$teacherAncId = ApotheosisLibAcl::getRoleId( 'group_ancestor_teacher' );
			$studentAncId = ApotheosisLibAcl::getRoleId( 'group_ancestor_student' );
			$watcherAncId = ApotheosisLibAcl::getRoleId( 'group_ancestor_watcher' );
			
			$adminDescId =   ApotheosisLibAcl::getRoleId( 'group_descendant_admin' );
			$teacherDescId = ApotheosisLibAcl::getRoleId( 'group_descendant_teacher' );
			$studentDescId = ApotheosisLibAcl::getRoleId( 'group_descendant_student' );
			$watcherDescId = ApotheosisLibAcl::getRoleId( 'group_descendant_watcher' );
			
			$peerTeacher = ApotheosisLibAcl::getRoleId( 'group_peer_teacher' );
			
			$adminSucId =   ApotheosisLibAcl::getRoleId( 'group_successor_admin' );
			$teacherSucId = ApotheosisLibAcl::getRoleId( 'group_successor_teacher' );
			$studentSucId = ApotheosisLibAcl::getRoleId( 'group_successor_student' );
			$watcherSucId = ApotheosisLibAcl::getRoleId( 'group_successor_watcher' );
			
			foreach( $rolesArr as $k=>$v ) {
				if( $v->role == $adminId ) {
					$roles[] = $adminId;
					$roles[] = $adminSucId;
					if( $v->has_descendants == 1 ) {
						$roles[] = $adminAncId;
					}
					if( $v->has_ascendants == 1 ) {
						$roles[] = $adminDescId;
					}
				}
				if( $v->role == $teacherId ) {
					$roles[] = $teacherId;
					$roles[] = $teacherSucId;
					$roles[] = $peerTeacher;
					if( $v->has_descendants == 1 ) {
						$roles[] = $teacherAncId;
					}
					if( $v->has_ascendants == 1 ) {
						$roles[] = $teacherDescId;
					}
				}
				if( $v->role == $studentId ) {
					$roles[] = $studentId;
					$roles[] = $studentSucId;
					if( $v->has_descendants == 1 ) {
						$roles[] = $studentAncId;
					}
					if( $v->has_ascendants == 1 ) {
						$roles[] = $studentDescId;
					}
				}
				if( $v->role == $watcherId ) {
					$roles[] = $watcherId;
					$roles[] = $watcherSucId;
					if( $v->has_descendants == 1 ) {
						$roles[] = $watcherAncId;
					}
					if( $v->has_ascendants == 1 ) {
						$roles[] = $watcherDescId;
					}
				}
			}
		}
		
		if( !empty($roles) ) {
			$insertQuery = 'INSERT INTO '.$tableName
				."\n".' VALUES ( '.implode(' ), ( ', $roles).' )';
			$db->setQuery($insertQuery);
			$db->Query();
		}
	}
	
	/**
	 * Inserts allowed-people data into the user table named
	 * 
	 * @param $tableName string  The name of the table into which to insert data
	 * @param $uId string  The jUserId of the user whose access should be used. Defaults to current user
	 * @param $from string  The date from which to consider people. Defaults to now. 
	 * @param $to string  The date up to which to consider people. Defaults to now.
	 */
	function setUserPeople( $tableName, $uId, $from, $to )
	{
		// if no user ID then set no roles
		if( empty($uId) ) { return true; }
		
		$db = &JFactory::getDBO();
		$user = &ApotheosisLib::getUser( $uId );
		
		$insertQuery = 'INSERT IGNORE INTO '.$tableName // *** titikaka fail (need to use new acl)
			."\n".'SELECT DISTINCT gm.person_id AS id'
			."\n".', IF( ca.id = ca.ancestor'
			."\n".', CASE'
			."\n".'   WHEN my_g.is_admin   = 1 THEN '.ApotheosisLibAcl::getRoleId( 'group_supervisor_admin' ) // *** titikaka fail
			."\n".'   WHEN my_g.is_teacher = 1 THEN '.ApotheosisLibAcl::getRoleId( 'group_supervisor_teacher' ) // *** titikaka fail
			."\n".'   WHEN my_g.is_student = 1 THEN '.ApotheosisLibAcl::getRoleId( 'group_participant_student' ) // *** titikaka fail
			."\n".'   WHEN my_g.is_watcher = 1 THEN '.ApotheosisLibAcl::getRoleId( 'group_participant_watcher' ) // *** titikaka fail
			."\n".'  END'
			."\n".', CASE'
			."\n".'   WHEN my_g.is_admin   = 1 THEN '.ApotheosisLibAcl::getRoleId( 'group_ancestor_admin' ) // *** titikaka fail
			."\n".'   WHEN my_g.is_teacher = 1 THEN '.ApotheosisLibAcl::getRoleId( 'group_ancestor_teacher' ) // *** titikaka fail
			."\n".'   WHEN my_g.is_student = 1 THEN '.ApotheosisLibAcl::getRoleId( 'group_ancestor_student' ) // *** titikaka fail
			."\n".'   WHEN my_g.is_watcher = 1 THEN '.ApotheosisLibAcl::getRoleId( 'group_ancestor_watcher' ) // *** titikaka fail
			."\n".'  END'
			."\n".'  ) AS `role`'
			."\n".'FROM #__apoth_tt_group_members AS my_g'
			."\n".'INNER JOIN #__apoth_cm_courses_ancestry AS ca'
			."\n".'   ON ca.ancestor = my_g.group_id'
			."\n".'INNER JOIN #__apoth_tt_group_members AS gm'
			."\n".'   ON gm.group_id = ca.id'
			."\n".'  AND gm.role != my_g.role'
			."\n".'WHERE my_g.person_id = '.$db->Quote($user->person_id)
			."\n".'  AND '.ApotheosisLibDb::dateCheckSql('my_g.valid_from', 'my_g.valid_to', $from, $to)
			."\n".'  AND '.ApotheosisLibDb::dateCheckSql('gm.valid_from',   'gm.valid_to', $from, $to);
		$db->setQuery($insertQuery);
		$db->Query();
		
		// ### add students I am a group peer teacher of
		
		// start by creating temp tables and populate them with:
		$t = time();
		$tmpTableName1 = 'tmp_holding_myenrol_'.$uId.'_'.$t;
		$tmpTableName2 = 'tmp_holding_peer_courses_'.$uId.'_'.$t;
		
		// 1) all the groups I'm currently directly involved in and
		$query = 'CREATE TEMPORARY TABLE '.$tmpTableName1.' AS'
			."\n".'SELECT DISTINCT gm.group_id AS `id`, cm.parent AS `parent`'
			."\n".'FROM `#__apoth_tt_group_members` AS gm'
			."\n".'INNER JOIN `#__apoth_cm_courses` AS cm'
			."\n".'   ON cm.id = gm.group_id'
			."\n".'  AND cm.deleted = 0'
			."\n".'WHERE gm.person_id = '.$db->Quote($user->person_id)
			."\n".'  AND '.ApotheosisLibDb::dateCheckSql( 'gm.valid_from', 'gm.valid_to', $from, $to )
			."\n".'  AND '.ApotheosisLibDb::dateCheckSql( 'cm.start_date', 'cm.end_date', $from, $to ).';'
		
		// 2) all the currently valid peer courses of my own
			."\n"
			."\n".'CREATE TEMPORARY TABLE '.$tmpTableName2.' AS'
			."\n".'SELECT DISTINCT c.id'
			."\n".'FROM `#__apoth_cm_courses` AS c'
			."\n".'INNER JOIN '.$tmpTableName1.' AS c2'
			."\n".'   ON c2.parent = c.parent'
			."\n".'WHERE '.ApotheosisLibDb::dateCheckSql( 'c.start_date', 'c.end_date', $from, $to )
			."\n".'  AND c.deleted = 0;'
		
		// populate ttgroups table with data for which my courses are peers
			."\n"
			."\n".'INSERT IGNORE INTO '.$tableName
			."\n".' SELECT DISTINCT gm.person_id, '.ApotheosisLibAcl::getRoleId( 'group_peer_teacher' )
			."\n".' FROM '.$tmpTableName2.' AS `c`'
			."\n".' INNER JOIN `#__apoth_tt_group_members` AS `gm`'
			."\n".'   ON gm.group_id = c.id'
			."\n".'WHERE '.ApotheosisLibDb::dateCheckSql( 'gm.valid_from', 'gm.valid_to', $from, $to ).';'
			
		// remove temporary tables to avoid name collisions on this connection
			."\n"
			."\n".'DROP TABLE '.$tmpTableName1.';'
			."\n".'DROP TABLE '.$tmpTableName2.';';
		$db->setQuery( $query );
		$db->queryBatch();
	}
	
	/**
	 * Inserts allowed-groups data into the user table named
	 * Gets user tables: people.people
	 * 
	 * @param $tableName string  The name of the table into which to insert data
	 * @param $uId string  The jUserId of the user whose access should be used. Defaults to current user
	 * @param $from string  The date from which to consider groups. Defaults to now. 
	 * @param $to string  The date up to which to consider groups. Defaults to now.
	 */
	function setUserGroups( $tableName, $uId, $from, $to )
	{
		// if no user ID then set no roles
		if( empty($uId) ) { return true; }
		
		$db = &JFactory::getDBO();
		$user = &ApotheosisLib::getUser( $uId );
		
		// create temp tables and populate them with:
		$t = time();
		$tmpTableName1 = 'tmp_holding_myenrol_'.$uId.'_'.$t;
		$tmpTableName2 = 'tmp_holding_courses_'.$uId.'_'.$t;
		
		// 1) all the groups I'm currently directly involved in and
		$query = 'CREATE TEMPORARY TABLE '.$tmpTableName1.' AS'
			."\n".'SELECT DISTINCT gm.group_id AS `id`, gm.role AS `role`, cm.parent AS `parent`'
			."\n".'FROM `#__apoth_tt_group_members` AS gm'
			."\n".'INNER JOIN `#__apoth_cm_courses` AS cm'
			."\n".'   ON cm.id = gm.group_id'
			."\n".'  AND cm.deleted = 0'
			."\n".'WHERE gm.person_id = '.$db->Quote($user->person_id)
			."\n".'  AND '.ApotheosisLibDb::dateCheckSql( 'gm.valid_from', 'gm.valid_to', $from, $to )
			."\n".'  AND '.ApotheosisLibDb::dateCheckSql( 'cm.start_date', 'cm.end_date', $from, $to ).'; '
		
		// 2) all the currently valid courses
			."\n"
			."\n".'CREATE TEMPORARY TABLE '.$tmpTableName2.' AS'
			."\n".'SELECT id, parent'
			."\n".'FROM `#__apoth_cm_courses`'
			."\n".'WHERE '.ApotheosisLibDb::dateCheckSql( 'start_date', 'end_date', $from, $to )
			."\n".'  AND `deleted` = 0;'
		
		// populate ttgroups table with data for courses I am directly involved with
			."\n"
			."\n".'INSERT IGNORE INTO '.$tableName
			."\n".'SELECT id, role'
			."\n".'FROM '.$tmpTableName1.' ;'
		
		// populate ttgroups table with data for which my courses are peers
			."\n"
			."\n".'INSERT IGNORE INTO '.$tableName
			."\n".' SELECT DISTINCT cur.id, '.ApotheosisLibAcl::getRoleId( 'group_peer_teacher' )
			."\n".' FROM '.$tmpTableName1.' AS `my`'
			."\n".' INNER JOIN '.$tmpTableName2.' AS `cur`'
			."\n".'   ON cur.parent = my.parent'
			."\n".'  AND cur.id != my.id'.'; '
		
		// populate ttgroups table with data for which my courses are ancestor
			."\n"
			."\n".'INSERT IGNORE INTO '.$tableName
			."\n".'SELECT DISTINCT ca.id'
			."\n".', CASE'
			."\n".'   WHEN my.role = '.ApotheosisLibAcl::getRoleId( 'group_supervisor_admin' ).'    THEN '.ApotheosisLibAcl::getRoleId( 'group_ancestor_admin' )
			."\n".'   WHEN my.role = '.ApotheosisLibAcl::getRoleId( 'group_supervisor_teacher' ).'  THEN '.ApotheosisLibAcl::getRoleId( 'group_ancestor_teacher' )
			."\n".'   WHEN my.role = '.ApotheosisLibAcl::getRoleId( 'group_participant_student' ).' THEN '.ApotheosisLibAcl::getRoleId( 'group_ancestor_student' )
			."\n".'   WHEN my.role = '.ApotheosisLibAcl::getRoleId( 'group_participant_watcher' ).' THEN '.ApotheosisLibAcl::getRoleId( 'group_ancestor_watcher' )
			."\n".'  END AS role'
			."\n".'FROM '.$tmpTableName2.' AS `cur`'
			."\n".'INNER JOIN `#__apoth_cm_courses_ancestry` AS ca'
			."\n".'   ON cur.id = ca.id'
			."\n".'INNER JOIN '.$tmpTableName1.' AS `my`'
			."\n".'   ON my.id = ca.ancestor'
			."\n".'  AND my.id != ca.id'.'; '
		
		// populate ttgroups table with data for which my courses are descendant
			."\n"
			."\n".'INSERT IGNORE INTO '.$tableName
			."\n".'SELECT DISTINCT ca.ancestor'
			."\n".', CASE'
			."\n".'   WHEN my.role = '.ApotheosisLibAcl::getRoleId( 'group_supervisor_admin' ).'    THEN '.ApotheosisLibAcl::getRoleId( 'group_descendant_admin' )
			."\n".'   WHEN my.role = '.ApotheosisLibAcl::getRoleId( 'group_supervisor_teacher' ).'  THEN '.ApotheosisLibAcl::getRoleId( 'group_descendant_teacher' )
			."\n".'   WHEN my.role = '.ApotheosisLibAcl::getRoleId( 'group_participant_student' ).' THEN '.ApotheosisLibAcl::getRoleId( 'group_descendant_student' )
			."\n".'   WHEN my.role = '.ApotheosisLibAcl::getRoleId( 'group_participant_watcher' ).' THEN '.ApotheosisLibAcl::getRoleId( 'group_descendant_watcher' )
			."\n".'  END AS role'
			."\n".'FROM '.$tmpTableName2.' AS `cur`'
			."\n".'INNER JOIN `#__apoth_cm_courses_ancestry` AS ca'
			."\n".'   ON cur.id = ca.id'
			."\n".'INNER JOIN '.$tmpTableName1.' AS `my`'
			."\n".'   ON my.id = ca.id'
			."\n".'  AND my.id != ca.ancestor;'
			
		// remove temporary tables to avoid name collisions on this connection
			."\n"
			."\n".'DROP TABLE '.$tmpTableName1.';'
			."\n".'DROP TABLE '.$tmpTableName2.';';
		$db->setQuery( $query );
		$db->queryBatch();
		
		// Set up successor groups based on the enrolments of this person's people
		// **** depending on people the user may be
		// a successor_(admin|teacher|student|watcher) of groups
		// currently we only look for / set up successor teacher
		$sTeacherRole = ApotheosisLibAcl::getRoleId( 'group_successor_teacher' );
		
		// get all the people that this user teaches and all their past classes
		$requirements = array( 'valid_from'=>$from, 'valid_to'=>$to, 'teacher'=>$user->person_id );
		$e = ApotheosisData::_( 'timetable.studentEnrolments', $requirements, null, false );
		$enrolments = ApotheosisData::_( 'timetable.enrolmentHistory', $e, $from, $to );
//		var_dump_pre( $e, 'e' );
//		var_dump_pre( $enrolments, 'enrolments' );
		
		$groups = array();
		foreach( $enrolments as $pId=>$cur ) {
			foreach( $cur as $curId=>$hGroups ) {
				foreach( $hGroups as $hId ) {
					$groups[$hId] = '( '.$db->Quote( $hId ).', '.$sTeacherRole.' )';
				}
			}
		}
		if( !empty($groups) ) {
			$query = 'INSERT INTO '.$tableName
				."\n".'VALUES'
				."\n".implode( "\n, ", $groups );
			$db->setQuery( $query );
			$db->Query();
		}
		
	}
	
	/**
	 * Inserts assessment-relevance data into the user table named
	 * Gets user tables: timetable.groups
	 * 
	 * @param $tableName string  The name of the table into which to insert data
	 * @param $uId string  The jUserId of the user whose access should be used. Defaults to current user
	 * @param $from string  The date from which to consider enrolemnts. Defaults to now. 
	 * @param $to string  The date up to which to consider enrolments. Defaults to now.
	 */
	function setUserAssessments( $tableName, $uId, $from, $to )
	{
		// if no user ID then set no roles
		if( empty($uId) ) { return true; }
		
		$user = &ApotheosisLib::getUser( $uId );
		$db = &JFactory::getDBO();
		$groupTable = ApotheosisLibAcl::getUserTable( 'timetable.groups', $uId );
		
		$insertQuery = 'INSERT IGNORE INTO '.$tableName
			."\n".'SELECT DISTINCT cm.`assessment`, tg.role'
			."\n".'FROM '.$groupTable.' AS tg'
			."\n".'INNER JOIN jos_apoth_ass_course_map AS cm'
			."\n".'   ON cm.`group` = tg.id';
		$db->setQuery($insertQuery);
		$db->query();
	}
	
	/**
	 * Inserts assessment-relevance data into the user table named
	 * Gets user tables: timetable.groups
	 * 
	 * @param $tableName string  The name of the table into which to insert data
	 * @param $uId string  The jUserId of the user whose access should be used. Defaults to current user
	 * @param $from string  The date from which to consider enrolemnts. Defaults to now. 
	 * @param $to string  The date up to which to consider enrolments. Defaults to now.
	 */
	function setUserAssessmentsAll( $tableName, $uId, $from, $to )
	{
		// if no user ID then set no roles
		if( empty($uId) ) { return true; }
		
		$user = &ApotheosisLib::getUser( $uId );
		$db = &JFactory::getDBO();
		$groupTable = ApotheosisLibAcl::getUserTable( 'timetable.groups', $uId );
		
		// create temp tables and populate them with:
		$t = time();
		$tmpTableName1 = 'tmp_map_counts_'.$uId.'_'.$t;
		$tmpTableName2 = 'tmp_role_counts_'.$uId.'_'.$t;
		
		$insertQuery = 'CREATE TEMPORARY TABLE '.$tmpTableName1.' AS'
			."\n".'SELECT cm.`assessment`, COUNT(*) AS num'
			."\n".'FROM jos_apoth_ass_course_map AS cm'
			."\n".'GROUP BY cm.assessment;'
			."\n".''
			."\n".'CREATE TEMPORARY TABLE '.$tmpTableName2.' AS'
			."\n".'SELECT cm.`assessment`, tg.role, COUNT(*) AS num'
			."\n".'FROM '.$groupTable.' AS tg'
			."\n".'INNER JOIN jos_apoth_ass_course_map AS cm'
			."\n".'   ON cm.`group` = tg.id'
			."\n".'GROUP BY cm.assessment, tg.role;'
			."\n".''
			."\n".'INSERT IGNORE INTO '.$tableName
			."\n".'SELECT t1.assessment, t1.role'
			."\n".'FROM '.$tmpTableName2.' AS t1'
			."\n".'INNER JOIN '.$tmpTableName1.' AS t2'
			."\n".'   ON t2.assessment = t1.assessment'
			."\n".'  AND t2.num = t1.num;'
			."\n".''
			."\n".'DROP TABLE '.$tmpTableName1.';'
			."\n".'DROP TABLE '.$tmpTableName2.';';
		$db->setQuery($insertQuery);
		$db->queryBatch();
	}
}
?>