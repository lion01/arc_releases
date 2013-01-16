<?php
/**
 * @package     Arc
 * @subpackage  Assessment
 * @copyright   Copyright (C) 2005 Punnet. All rights reserved. See COPYRIGHT_ARC.txt
 * @license     http://www.gnu.org/licenses/gpl.html GNU/GPL. See LICENSE_ARC.txt
 * Arc is free software: you can redistribute it and/or modify
 * it under the terms of version 3 of the GNU General Public License
 * as published by the Free Software Foundation.
 * The disclaimer of warranty as stated in the GPL applies to this program
 */

defined( '_JEXEC' ) or die( 'Restricted access' );

class ApothAuth_Assessment
{
	function checkDependancy( $ident, $given, $uId = false, $actionId = false )
	{
		$db = &JFactory::getDBO();
		$user = &ApotheosisLib::getUser( $uId );
		
		switch( $ident ) {
		case( 'assessments' ):
			if( $given == -1 ) {
				$retVal == true;
			}
			else {
				if( is_array($given) ) {
					if( empty($given) ) {
						$assignPart = ' = 0';
					}
					else {
						foreach( $given as $aId ) {
							$vals[] = $db->Quote($aId);
						}
						$assignPart = ' IN ('.implode(', ', $vals).')';
					}
				}
				else {
					$assignPart = ' = '.$db->Quote($given);
				}
				$query = 'SELECT a.id'
					."\n".'FROM #__apoth_ass_assessments AS a'
					."\n".'~LIMITINGJOIN~'
					."\n".'WHERE a.id '.$assignPart;
				$db->setQuery( ApotheosisLibAcl::limitQuery($query, 'assessment.assessments', 'a', 'id', $uId, $actionId) );
				$r = $db->loadResult();
				$retVal = !empty($r);
			}
			break;
			
		case( 'ass' ):
			$retVal = true;
			break;
			
		case( 'aspects' ):
			// **** Dirty. should be a more general solution 
			// (like we have in the various $requirements processing sections,
			//  put that sort of thing in ApotheosisLibAcl::CheckDependancy
			if( is_array($given) ) { $given = reset($given); }
			$query = 'SELECT ai.id'
				."\n".'FROM #__apoth_ass_aspect_instances AS ai'
				."\n".'~LIMITINGJOIN~'
				."\n".'WHERE ai.id = '.$db->Quote($given);
			$db->setQuery( ApotheosisLibAcl::limitQuery($query, 'assessment.assessments', 'ai', 'assessment_id', $uId, $actionId) );
			$r = $db->loadResult();
			$retVal = !empty($r);
			break;
		
		case( 'markStyle' ):
		case( 'displayStyle' ):
			$retVal = true;
			break;
			
		default:
			$retVal = false;
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
	function limitQuery($givenQuery, $limitOn, $inTable = false, $inCol = false, $uId = false, $actionId = false, $joinSlug = '~LIMITINGJOIN~' )
	{
		$db = &JFactory::getDBO();
		$user = &ApotheosisLib::getUser( $uId );
		
		switch( $limitOn ) {
		case( 'assessments' ):
			/*
			 * The admin pages need a bit of special treatment in their permission checks.
			 * A user with admin access by virtue of their group memberships must have
			 * membership of _all_ groups to which an assessment is assigned.
			 * This is accomplished by limiting on the assessment.assessments_all
			 * list which is so limited
			 */
			$adminActions = array(
				  ApotheosisLib::getActionIdByName( 'apoth_ass_admin_existing' )=>true
				, ApotheosisLib::getActionIdByName( 'apoth_ass_admin_existing_noid' )=>true
				, ApotheosisLib::getActionIdByName( 'apoth_ass_admin_edit' )=>true
				, ApotheosisLib::getActionIdByName( 'apoth_ass_admin_delete' )=>true
				, ApotheosisLib::getActionIdByName( 'apoth_ass_admin_lock' )=>true
				, ApotheosisLib::getActionIdByName( 'apoth_ass_import_export' )=>true
				, ApotheosisLib::getActionIdByName( 'apoth_ass_import' )=>true
				, ApotheosisLib::getActionIdByName( 'apoth_ass_export' )=>true
			);
			if( isset( $adminActions[$actionId] ) ) {
				$limitTableName = 'assessment.assessments_all';
			}
			else {
				$limitTableName = 'assessment.assessments';
			}
			
			if( $inTable === false ) {
				$inTable = 'a';
			}
			if( $inCol === false ) {
				$inCol = 'id';
			}
			$inTable = $db->nameQuote( $inTable );
			$inCol   = $db->nameQuote( $inCol );
			$tableName = ApotheosisLibAcl::getUserTable( $limitTableName, $user->id );
			
			$joinQuery = 'INNER JOIN '.$tableName.' AS lim_ass_a'
				."\n".'  ON lim_ass_a.id = '.$inTable.'.'.$inCol
				."\n".'~LIMITINGJOIN~';
			$joinQuery = ApotheosisLibAcl::limitQuery( $joinQuery, 'core.sees', 'lim_ass_a', 'role', $uId, $actionId );
			break;
		
		default:
			$joinQuery = '';
		}
		
		return str_replace( $joinSlug, $joinQuery, $givenQuery );
	}
	
	/**
	 * Creates and populates the user's all-groups assessment table if it doesn't already exist.
	 * Gves back the name of that table (with minimum overhead if it exists) 
	 * 
	 * @param $tableName string  The name of the short-term table to be created / populated
	 * @param $uId string  The jUserId of the user whose access should be used. Defaults to current user
	 * @return string  The name of the table which now contains this user's people
	 */
	function createTblUserAssessments_all( $tableName, $uId )
	{
		$user = &ApotheosisLib::getUser( $uId );
		$uId = $user->id;
		$from = ApotheosisLibAcl::getDatum( 'dateFrom' );
		$to = ApotheosisLibAcl::getDatum( 'dateTo' );
		$db = &JFactory::getDBO();
		
		if( !ApotheosisLibDbTmp::getExists( $tableName ) ) {
			$createQuery = 'CREATE TABLE ~TABLE~ ENGINE=MyISAM AS'
				."\n".'SELECT a.`id` AS `id`, r.`id` AS `role`'
				."\n".'FROM #__apoth_ass_assessments AS a'
				."\n".'INNER JOIN #__apoth_sys_roles AS r'
				."\n".'LIMIT 0';
			ApotheosisLibDbTmp::create( $tableName, $createQuery );
		}
		
		// we're using a fresh table, so populate it with data according to the various components
		$null = null;
		ApotheosisLib::callAll( 'helpers'.DS.'auth.php', 'ApothAuth', 'setUserAssessmentsAll', array($tableName, $uId, $from, $to), $null, true );
		
		$alterQuery = 'ALTER TABLE '.$tableName.' ADD INDEX (`id`)'
			."\n".' , ADD INDEX(`role`)';
		$db->setQuery( $alterQuery );
		$db->Query();

		ApotheosisLibDbTmp::setPopulated( $tableName );
		return $tableName;	}
	
	/**
	 * Creates and populates the user's assessment table if it doesn't already exist.
	 * Gves back the name of that table (with minimum overhead if it exists) 
	 * 
	 * @param $tableName string  The name of the short-term table to be created / populated
	 * @param $uId string  The jUserId of the user whose access should be used. Defaults to current user
	 * @return string  The name of the table which now contains this user's people
	 */
	function createTblUserAssessments( $tableName, $uId )
	{
		$user = &ApotheosisLib::getUser( $uId );
		$uId = $user->id;
		$from = ApotheosisLibAcl::getDatum( 'dateFrom' );
		$to = ApotheosisLibAcl::getDatum( 'dateTo' );
		$db = &JFactory::getDBO();
		
		if( !ApotheosisLibDbTmp::getExists( $tableName ) ) {
			$createQuery = 'CREATE TABLE ~TABLE~ ENGINE=MyISAM AS'
				."\n".'SELECT a.`id` AS `id`, r.`id` AS `role`'
				."\n".'FROM #__apoth_ass_assessments AS a'
				."\n".'INNER JOIN #__apoth_sys_roles AS r'
				."\n".'LIMIT 0';
			ApotheosisLibDbTmp::create( $tableName, $createQuery );
		}
		
		// we're using a fresh table, so populate it with data according to the various components
		$null = null;
		ApotheosisLib::callAll( 'helpers'.DS.'auth.php', 'ApothAuth', 'setUserAssessments', array($tableName, $uId, $from, $to), $null, true );
		
		$alterQuery = 'ALTER TABLE '.$tableName.' ADD INDEX (`id`)'
			."\n".' , ADD INDEX(`role`)';
		$db->setQuery( $alterQuery );
		$db->Query();

		ApotheosisLibDbTmp::setPopulated( $tableName );
		return $tableName;
	}
	
	/**
	 * Inserts role data into the user table named
	 * Requires data in: core.roles (as much as possible as this also populates that table)
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
		
		// We don't want to set this info on the first attempt as it relies on
		// everything else having done its stuff
		static $tried = false;
		if( !$tried ) {
			$tried = true;
			return false;
		}
		$tried = false; // reset this flag in case we come through again (eg for a separate check with different date range)
		$db = &JFactory::getDBO();
		$user = &ApotheosisLib::getUser( $uId );
		
		
		$query = 'SELECT *'
			."\n".'FROM '.$db->nameQuote('#__apoth_ass_assessments')
			."\n".'WHERE '.$db->nameQuote('created_by').' = '.$db->Quote($user->person_id)
			."\n".'LIMIT 1';
		$db->setQuery($query);
		$r = $db->loadResult();
		
		if( !empty($r) ) {
			$values[] = '('.$db->Quote( ApotheosisLibAcl::getRoleId('assessment_owner') ).')';
		}
		
		
		$query = 'SELECT *'
			."\n".'FROM '.$db->nameQuote('#__apoth_ass_editors').' AS e'
			."\n".'~LIMITINGJOIN~'
			."\n".'LIMIT 1';
		$db->setQuery( ApotheosisLibAcl::limitQuery($query, 'core.roles', 'e', 'role', $uId, false) );
		$r = $db->loadResult();
		
		if( !empty($r) ) {
			$values[] = '('.$db->Quote( ApotheosisLibAcl::getRoleId('assessment_editor') ).')';
		}
		
		
		$query = 'SELECT *'
			."\n".'FROM '.$db->nameQuote('#__apoth_ass_accessors').' AS ac'
			."\n".'~LIMITINGJOIN~'
			."\n".'LIMIT 1';
		$db->setQuery( ApotheosisLibAcl::limitQuery($query, 'core.roles', 'ac', 'role', $uId, false) );
		$r = $db->loadResult();
		
		if( !empty($r) ) {
			$values[] = '('.$db->Quote( ApotheosisLibAcl::getRoleId('assessment_accessor') ).')';
		}
		
		if( !empty($values) ) {
			$insertQuery = 'INSERT INTO '.$tableName
				."\n".'VALUES '.implode(', ', $values);
			$db->setQuery($insertQuery);
			$db->Query();
		}
	}
	
	/**
	 * Inserts assessment-relevance data into the user table named
	 * Gets user tables: core.roles, timetable.groups
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
		
		$roleTable = ApotheosisLibAcl::getUserTable( 'core.roles', $uId );
		$groupTable = ApotheosisLibAcl::getUserTable( 'timetable.groups', $uId );
		if( is_null($roleTable) || is_null($groupTable) ) {
			return false;
		}
		
		$user = &ApotheosisLib::getUser( $uId );
		$db = &JFactory::getDBO();
		
		// get owned assessments
		$query = 'SELECT a.id'
			."\n".'FROM #__apoth_ass_assessments AS a'
			."\n".'WHERE a.created_by = '.$db->Quote( $user->person_id );
		$db->setQuery( $query );
		$owned = $db->loadResultArray();
		
		if( !empty($owned) ) {
			$ownerRole = $db->Quote( ApotheosisLIbAcl::getRoleId('assessment_owner') );
			foreach($owned as $id) {
				$values[] = '('.$db->Quote($id).', '.$ownerRole.')';
			}
		}
		
		// get other editable assessments
		$query = 'SELECT DISTINCT a.id'
			."\n".'FROM #__apoth_ass_assessments AS a'
			."\n".'INNER JOIN #__apoth_ass_course_map AS cm'
			."\n".'   ON cm.assessment = a.id'
			."\n".'INNER JOIN #__apoth_cm_courses_ancestry AS ca'
			."\n".'   ON ca.ancestor = cm.`group`'
			."\n".'INNER JOIN '.$groupTable.' AS g'
			."\n".'   ON g.id = ca.id'
			."\n".'INNER JOIN #__apoth_ass_editors AS e'
			."\n".'   ON e.assessment = a.id'
			."\n".'  AND e.role = g.role'
			."\n".'INNER JOIN '.$roleTable.' AS r'
			."\n".'   ON r.id = e.role';
		$db->setQuery( $query );
		$editor = $db->loadResultArray();
		
		if( !empty($editor) ) {
			$editorRole = $db->Quote( ApotheosisLibAcl::getRoleId('assessment_editor') );
			foreach($editor as $id) {
				$values[] = '('.$db->Quote($id).', '.$editorRole.')';
			}
		}
		
		// get other accessible assessments
		$query = 'SELECT DISTINCT a.id'
			."\n".'FROM #__apoth_ass_assessments AS a'
			."\n".'INNER JOIN #__apoth_ass_course_map AS cm'
			."\n".'   ON cm.assessment = a.id'
			."\n".'INNER JOIN #__apoth_cm_courses_ancestry AS ca'
			."\n".'   ON ca.ancestor = cm.`group`'
			."\n".'INNER JOIN '.$groupTable.' AS g'
			."\n".'   ON g.id = ca.id'
			."\n".'INNER JOIN #__apoth_ass_accessors AS ac'
			."\n".'   ON ac.assessment = a.id'
			."\n".'  AND ac.role = g.role'
			."\n".'INNER JOIN '.$roleTable.' AS r'
			."\n".'   ON r.id = ac.role';
		$db->setQuery( $query );
		$accessor = $db->loadResultArray();
		
		if( !empty($accessor) ) {
			$accessorRole = $db->Quote( ApotheosisLibAcl::getRoleId('assessment_accessor') );
			foreach($accessor as $id) {
				$values[] = '('.$db->Quote($id).', '.$accessorRole.')';
			}
		}
		
		if( !empty($values) ) {
			$insertQuery = 'INSERT INTO '.$tableName
				."\n".' VALUES'
				."\n".implode("\n, ", $values);
			$db->setQuery($insertQuery);
			$db->Query();
		}
	}
	
	/**
	 * Inserts assessment-relevance data into the user table named
	 * Gets user tables: core.roles, timetable.groups
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
		
		$roleTable = ApotheosisLibAcl::getUserTable( 'core.roles', $uId );
		$groupTable = ApotheosisLibAcl::getUserTable( 'timetable.groups', $uId );
		if( is_null($roleTable) || is_null($groupTable) ) {
			return false;
		}
		
		$user = &ApotheosisLib::getUser( $uId );
		$db = &JFactory::getDBO();
		
		// get owned assessments
		$query = 'SELECT a.id'
			."\n".'FROM #__apoth_ass_assessments AS a'
			."\n".'WHERE a.created_by = '.$db->Quote( $user->person_id );
		$db->setQuery( $query );
		$owned = $db->loadResultArray();
		
		if( !empty($owned) ) {
			$ownerRole = $db->Quote( ApotheosisLIbAcl::getRoleId('assessment_owner') );
			foreach($owned as $id) {
				$values[] = '('.$db->Quote($id).', '.$ownerRole.')';
			}
		}
		
		// non-owner permissions cut to ensure strict access
		
		if( !empty($values) ) {
			$insertQuery = 'INSERT INTO '.$tableName
				."\n".' VALUES'
				."\n".implode("\n, ", $values);
			$db->setQuery($insertQuery);
			$db->Query();
		}
	}
}
?>