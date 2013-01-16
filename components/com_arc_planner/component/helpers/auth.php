<?php
/**
 * @package     Arc
 * @subpackage  Planner
 * @copyright   Copyright (C) 2005 Punnet. All rights reserved. See COPYRIGHT_ARC.txt
 * @license     http://www.gnu.org/licenses/gpl.html GNU/GPL. See LICENSE_ARC.txt
 * Arc is free software: you can redistribute it and/or modify
 * it under the terms of version 3 of the GNU General Public License
 * as published by the Free Software Foundation.
 * The disclaimer of warranty as stated in the GPL applies to this program
 */

defined( '_JEXEC' ) or die( 'Restricted access' );

class ApothAuth_Planner
{
	function checkDependancy( $ident, $given, $uId = false, $actionId = false )
	{
		$db = &JFactory::getDBO();
		
		switch( $ident ) {
		case( 'tasks' ):
			$query = 'SELECT t.id'
				."\n".'FROM #__apoth_plan_tasks AS t'
				."\n".'~LIMITINGJOIN~'
				."\n".'WHERE t.id = '.$db->Quote($given);
			$query = ApotheosisLibAcl::limitQuery( $query, 'planner.tasks', 't', 'id', $uId, $actionId);
			$db->setQuery( $query );
			$r = $db->loadResult();
			$retVal = !empty($r);
			break;
			
		case( 'groups' ):
			$query = 'SELECT g.id'
				."\n".'FROM #__apoth_plan_groups AS g'
				."\n".'~LIMITINGJOIN~'
				."\n".'WHERE g.id = '.$db->Quote($given);
			$query = ApotheosisLibAcl::limitQuery( $query, 'planner.groups', 'g', 'id', $uId, $actionId);
			$db->setQuery( $query );
			$r = $db->loadResult();
			$retVal = !empty($r);
			break;
			
		case( 'updates' ):
			$query = 'SELECT u.id'
				."\n".'FROM #__apoth_plan_updates AS u'
				."\n".'~LIMITINGJOIN~'
				."\n".'WHERE u.id = '.$db->Quote($given);
			$query = ApotheosisLibAcl::limitQuery( $query, 'planner.updates', 'u', 'id', $uId, $actionId);
			$db->setQuery( $query );
			$r = $db->loadResult();
			$retVal = !empty($r);
			break;
		
		case( 'arc_people' ):
		case( 'scope' ):
		case( 'pretty' ):
		case( 'form' ):
			$retVal = true;
		}
		return $retVal;
	}
	
	/**
	 * Limits the tasks/groups/updates that can be pulled out by the given query
	 * to only those accessible by the given user in the given action 
	 * 
	 * @param $givenQuery string  The query to limit
	 * @param $limitOn string  Either 'tasks', 'groups' or 'updates' depending on which the query should be limited by
	 * @param $inTable string  The optional table name to join from. Defaults to 't' for tasks, 'g' for groups, 'u' for updates
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
		case( 'tasks' ):
			$tableName = ApotheosisLibAcl::getUserTable( 'planner.tasks', $user->id );
			if( $inTable === false ) {
				$inTable = 't';
			}
			if( $inCol === false ) {
				$inCol = 'id';
			}
			$inTable = $db->nameQuote( $inTable );
			$inCol   = $db->nameQuote( $inCol );
			
			$joinQuery = 'INNER JOIN '.$tableName.' AS lim_plan_t'
				."\n".'  ON lim_plan_t.id = '.$inTable.'.'.$inCol
				."\n".'~LIMITINGJOIN~';
			$joinQuery = ApotheosisLibAcl::limitQuery( $joinQuery, 'core.sees', 'lim_plan_t', 'role', $uId, $actionId );
			break;
		
		case( 'groups' ):
			$tableName = ApotheosisLibAcl::getUserTable( 'planner.groups', $user->id );
			if( $inTable === false ) {
				$inTable = 'g';
			}
			if( $inCol === false ) {
				$inCol = 'id';
			}
			$inTable = $db->nameQuote( $inTable );
			$inCol   = $db->nameQuote( $inCol );
			
			$joinQuery = 'INNER JOIN '.$tableName.' AS lim_plan_g'
				."\n".'  ON lim_plan_g.id = '.$inTable.'.'.$inCol
				."\n".'~LIMITINGJOIN~';
			$joinQuery = ApotheosisLibAcl::limitQuery( $joinQuery, 'core.sees', 'lim_plan_g', 'role', $uId, $actionId );
			break;
		
		case( 'updates' ):
			$tableName = ApotheosisLibAcl::getUserTable( 'planner.updates', $user->id );
			if( $inTable === false ) {
				$inTable = 'u';
			}
			if( $inCol === false ) {
				$inCol = 'id';
			}
			$inTable = $db->nameQuote( $inTable );
			$inCol   = $db->nameQuote( $inCol );
			
			$joinQuery = 'INNER JOIN '.$tableName.' AS lim_plan_u'
				."\n".'  ON lim_plan_u.id = '.$inTable.'.'.$inCol
				."\n".'~LIMITINGJOIN~';
			$joinQuery = ApotheosisLibAcl::limitQuery( $joinQuery, 'core.sees', 'lim_plan_u', 'role', $uId, $actionId );
			break;
		
		default:
			$joinQuery = '';
		}
		
		return str_replace( $joinSlug, $joinQuery, $givenQuery );
	}
	
	/**
	 * Creates and populates the user's planner tasks table if it doesn't already exist.
	 * Gives back the name of that table (with minimum overhead if it exists) 
	 * 
	 * @param $tableName string  The name of the short-term table to be created / populated
	 * @param $uId string  The jUserId of the user whose access should be used. Defaults to current user
	 * @return string  The name of the table which now contains this user's people
	 */
	function createTblUserTasks( $tableName, $uId )
	{
		$user = &ApotheosisLib::getUser( $uId );
		$uId = $user->id;
		$from = ApotheosisLibAcl::getDatum( 'dateFrom' );
		$to = ApotheosisLibAcl::getDatum( 'dateTo' );
		$db = &JFactory::getDBO();
		
		if( !ApotheosisLibDbTmp::getExists( $tableName ) ) {
			$createQuery = 'CREATE TABLE ~TABLE~ ENGINE=MyISAM AS'
				."\n".'SELECT t.`id` AS `id`, r.`id` AS `role`'
				."\n".'FROM #__apoth_plan_tasks AS t'
				."\n".'INNER JOIN #__apoth_sys_roles AS r'
				."\n".'LIMIT 0';
			$tmp = ApotheosisLibDbTmp::create( $tableName, $createQuery );
		}
		
		// we're using a fresh table, so populate it with data according to the various components
		$null = null;
		ApotheosisLib::callAll( 'helpers'.DS.'auth.php', 'ApothAuth', 'setUserPlannerTasks', array($tableName, $uId, $from, $to), $null, true );
		
		$alterQuery = 'ALTER TABLE '.$tableName.' ADD INDEX (`id`)'
			."\n".' , ADD INDEX(`role`)';
		$db->setQuery( $alterQuery );
		$db->Query();
		
		ApotheosisLibDbTmp::setPopulated( $tableName );
		return $tableName;
	}
	
	/**
	 * Creates and populates the user's planner groups table if it doesn't already exist.
	 * Gives back the name of that table (with minimum overhead if it exists) 
	 * 
	 * @param $tableName string  The name of the short-term table to be created / populated
	 * @param $uId string  The jUserId of the user whose access should be used. Defaults to current user
	 * @return string  The name of the table which now contains this user's people
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
				."\n".'SELECT g.`id` AS `id`, r.`id` AS `role`'
				."\n".'FROM #__apoth_plan_groups AS g'
				."\n".'INNER JOIN #__apoth_sys_roles AS r'
				."\n".'LIMIT 0';
			$tmp = ApotheosisLibDbTmp::create( $tableName, $createQuery );
		}
		
		// we're using a fresh table, so populate it with data according to the various components
		$null = null;
		ApotheosisLib::callAll( 'helpers'.DS.'auth.php', 'ApothAuth', 'setUserPlannerGroups', array($tableName, $uId, $from, $to), $null, true );
		
		$alterQuery = 'ALTER TABLE '.$tableName.' ADD INDEX (`id`)'
			."\n".' , ADD INDEX(`role`)';
		$db->setQuery( $alterQuery );
		$db->Query();
		
		ApotheosisLibDbTmp::setPopulated( $tableName );
		return $tableName;
	}
	
	
	/**
	 * Creates and populates the user's planner updates table if it doesn't already exist.
	 * Gives back the name of that table (with minimum overhead if it exists) 
	 * 
	 * @param $tableName string  The name of the short-term table to be created / populated
	 * @param $uId string  The jUserId of the user whose access should be used. Defaults to current user
	 * @return string  The name of the table which now contains this user's people
	 */
	function createTblUserUpdates( $tableName, $uId )
	{
		$user = &ApotheosisLib::getUser( $uId );
		$uId = $user->id;
		$from = ApotheosisLibAcl::getDatum( 'dateFrom' );
		$to = ApotheosisLibAcl::getDatum( 'dateTo' );
		$db = &JFactory::getDBO();
		
		if( !ApotheosisLibDbTmp::getExists( $tableName ) ) {
			$createQuery = 'CREATE TABLE ~TABLE~ ENGINE=MyISAM AS'
				."\n".'SELECT u.`id` AS `id`, r.`id` AS `role`'
				."\n".'FROM #__apoth_plan_updates AS u'
				."\n".'INNER JOIN #__apoth_sys_roles AS r'
				."\n".'LIMIT 0';
			$tmp = ApotheosisLibDbTmp::create( $tableName, $createQuery );
		}
		
		// we're using a fresh table, so populate it with data according to the various components
		$null = null;
		ApotheosisLib::callAll( 'helpers'.DS.'auth.php', 'ApothAuth', 'setUserPlannerUpdates', array($tableName, $uId, $from, $to), $null, true );
		
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
		$user = &ApotheosisLib::getUser( $uId );
		
		$adminId =     ApotheosisLibAcl::getRoleId( 'planner_group_admin' );
		$leaderId    = ApotheosisLibAcl::getRoleId( 'planner_group_leader' );
		$assigneeId =  ApotheosisLibAcl::getRoleId( 'planner_group_assignee' );
		$assistantId = ApotheosisLibAcl::getRoleId( 'planner_group_assistant' );
		
		$insertQuery = 'INSERT IGNORE INTO '.$tableName
			."\n".'SELECT DISTINCT'
			."\n".'CASE m.role'
			."\n".'WHEN '.$db->Quote( 'admin' ).'     THEN '.$db->Quote( $adminId )
			."\n".'WHEN '.$db->Quote( 'leader' ).'    THEN '.$db->Quote( $leaderId )
			."\n".'WHEN '.$db->Quote( 'assignee' ).'  THEN '.$db->Quote( $assigneeId )
			."\n".'WHEN '.$db->Quote( 'assistant' ).' THEN '.$db->Quote( $assistantId )
			."\n".'END AS role_id'
			."\n".'FROM jos_apoth_plan_group_members AS m'
			."\n".'WHERE m.person_id = '.$db->Quote( $user->person_id )
			."\n".'  AND '.ApotheosisLibDb::dateCheckSql('valid_from', 'valid_to', $from, $to).'; ';
		$db->setQuery($insertQuery);
		$db->QueryBatch();
	}
	
	/**
	 * Inserts allowed-tasks into the user table named
	 * 
	 * @param $tableName string  The name of the table into which to insert data
	 * @param $uId string  The jUserId of the user whose access should be used. Defaults to current user
	 * @param $from string  The date from which to consider people. Defaults to now. 
	 * @param $to string  The date up to which to consider people. Defaults to now.
	 */
	function setUserPlannerTasks( $tableName, $uId, $from, $to )
	{
		// if no user ID then set no roles
		if( empty($uId) ) { return true; }
		
		$db = &JFactory::getDBO();
		$user = &ApotheosisLib::getUser( $uId );
		
		$adminId =     ApotheosisLibAcl::getRoleId( 'planner_group_admin' );
		$leaderId    = ApotheosisLibAcl::getRoleId( 'planner_group_leader' );
		$assigneeId =  ApotheosisLibAcl::getRoleId( 'planner_group_assignee' );
		$assistantId = ApotheosisLibAcl::getRoleId( 'planner_group_assistant' );
		
		$insertQuery = 'INSERT IGNORE INTO '.$tableName
			."\n".'SELECT g.task_id,'
			."\n".'CASE m.role'
			."\n".'WHEN '.$db->Quote( 'admin' ).'     THEN '.$db->Quote( $adminId )
			."\n".'WHEN '.$db->Quote( 'leader' ).'    THEN '.$db->Quote( $leaderId )
			."\n".'WHEN '.$db->Quote( 'assignee' ).'  THEN '.$db->Quote( $assigneeId )
			."\n".'WHEN '.$db->Quote( 'assistant' ).' THEN '.$db->Quote( $assistantId )
			."\n".'END AS role_id'
			."\n".'FROM jos_apoth_plan_group_members AS m'
			."\n".'INNER JOIN jos_apoth_plan_groups AS g'
			."\n".' ON g.id = m.group_id'
			."\n".'WHERE m.person_id = '.$db->Quote( $user->person_id )
			."\n".'  AND '.ApotheosisLibDb::dateCheckSql('valid_from', 'valid_to', $from, $to).'; ';
		$db->setQuery($insertQuery);
		$db->QueryBatch();
	}
	
	/**
	 * Inserts allowed-groups data into the user table named
	 * 
	 * @param $tableName string  The name of the table into which to insert data
	 * @param $uId string  The jUserId of the user whose access should be used. Defaults to current user
	 * @param $from string  The date from which to consider groups. Defaults to now. 
	 * @param $to string  The date up to which to consider groups. Defaults to now.
	 */
	function setUserPlannerGroups( $tableName, $uId, $from, $to )
	{
		// if no user ID then set no roles
		if( empty($uId) ) { return true; }
		
		$db = &JFactory::getDBO();
		$user = &ApotheosisLib::getUser( $uId );
		
		$adminId =     ApotheosisLibAcl::getRoleId( 'planner_group_admin' );
		$leaderId    = ApotheosisLibAcl::getRoleId( 'planner_group_leader' );
		$assigneeId =  ApotheosisLibAcl::getRoleId( 'planner_group_assignee' );
		$assistantId = ApotheosisLibAcl::getRoleId( 'planner_group_assistant' );
		
		$insertQuery = 'INSERT IGNORE INTO '.$tableName
			."\n".'SELECT group_id,'
			."\n".'CASE role'
			."\n".'WHEN '.$db->Quote( 'admin' ).'     THEN '.$db->Quote( $adminId )
			."\n".'WHEN '.$db->Quote( 'leader' ).'    THEN '.$db->Quote( $leaderId )
			."\n".'WHEN '.$db->Quote( 'assignee' ).'  THEN '.$db->Quote( $assigneeId )
			."\n".'WHEN '.$db->Quote( 'assistant' ).' THEN '.$db->Quote( $assistantId )
			."\n".'END AS role_id'
			."\n".'FROM jos_apoth_plan_group_members'
			."\n".'WHERE person_id = '.$db->Quote( $user->person_id )
			."\n".'  AND '.ApotheosisLibDb::dateCheckSql('valid_from', 'valid_to', $from, $to).'; ';
		$db->setQuery( $insertQuery );
		$db->Query();
	}
	
	/**
	 * Inserts allowed-updates data into the user table named
	 * 
	 * @param $tableName string  The name of the table into which to insert data
	 * @param $uId string  The jUserId of the user whose access should be used. Defaults to current user
	 * @param $from string  The date from which to consider groups. Defaults to now. 
	 * @param $to string  The date up to which to consider groups. Defaults to now.
	 */
	function setUserPlannerUpdates( $tableName, $uId, $from, $to )
	{
		// if no user ID then set no roles
		if( empty($uId) ) { return true; }
		
		$db = &JFactory::getDBO();
		$user = &ApotheosisLib::getUser( $uId );
		
		$adminId =     ApotheosisLibAcl::getRoleId( 'planner_group_admin' );
		$leaderId    = ApotheosisLibAcl::getRoleId( 'planner_group_leader' );
		$assigneeId =  ApotheosisLibAcl::getRoleId( 'planner_group_assignee' );
		$assistantId = ApotheosisLibAcl::getRoleId( 'planner_group_assistant' );
		
		$insertQuery = 'INSERT IGNORE INTO '.$tableName
			."\n".'SELECT u.id,'
			."\n".'CASE m.role'
			."\n".'WHEN '.$db->Quote( 'admin' ).'     THEN '.$db->Quote( $adminId )
			."\n".'WHEN '.$db->Quote( 'leader' ).'    THEN '.$db->Quote( $leaderId )
			."\n".'WHEN '.$db->Quote( 'assignee' ).'  THEN '.$db->Quote( $assigneeId )
			."\n".'WHEN '.$db->Quote( 'assistant' ).' THEN '.$db->Quote( $assistantId )
			."\n".'END AS role_id'
			."\n".'FROM jos_apoth_plan_group_members AS m'
			."\n".'INNER JOIN jos_apoth_plan_updates AS u'
			."\n".' ON u.group_id = m.group_id'
			."\n".'WHERE m.person_id = '.$db->Quote( $user->person_id )
			."\n".'  AND '.ApotheosisLibDb::dateCheckSql('valid_from', 'valid_to', $from, $to).'; ';
		$db->setQuery($insertQuery);
		$db->QueryBatch();
	}
}
?>