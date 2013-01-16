<?php
/**
 * @package     Arc
 * @subpackage  People
 * @copyright   Copyright (C) 2005 Punnet. All rights reserved. See COPYRIGHT_ARC.txt
 * @license     http://www.gnu.org/licenses/gpl.html GNU/GPL. See LICENSE_ARC.txt
 * Arc is free software: you can redistribute it and/or modify
 * it under the terms of version 3 of the GNU General Public License
 * as published by the Free Software Foundation.
 * The disclaimer of warranty as stated in the GPL applies to this program
 */

defined( '_JEXEC' ) or die( 'Restricted access' );

class ApothAuth_People
{
	function checkDependancy( $ident, $given, $uId = false, $actionId = false )
	{
		$db = &JFactory::getDBO();
		$user = &ApotheosisLib::getUser( $uId );
		
		switch( $ident ) {
		case( 'people' ):
		case( 'jusers'):
			$query = 'SELECT p.id'
				."\n".'FROM #__apoth_ppl_people AS p'
				."\n".'~LIMITINGJOIN~'
				."\n".'WHERE p.juserid = '.$db->Quote($given)
				."\n".'LIMIT 1';
			$query = ApotheosisLibAcl::limitQuery( $query, 'people.people', 'p', 'id', false, $actionId);
			$db->setQuery( $query );
			$r = $db->loadResult();
//			echo 'user loading '; debugQuery($db, $r);
			$retVal = !empty($r);
			break;
		
		case( 'arc_people' ):
			$query = 'SELECT p.id'
				."\n".'FROM #__apoth_ppl_people AS p'
				."\n".'~LIMITINGJOIN~'
				."\n".'WHERE p.id = '.$db->Quote($given)
				."\n".'LIMIT 1';
			$query = ApotheosisLibAcl::limitQuery( $query, 'people.people', 'p', 'id', false, $actionId);
			$db->setQuery( $query );
			$r = $db->loadResult();
//			echo 'arc user loading '; debugQuery($db, $r);
			$retVal = !empty($r);
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
	function limitQuery($givenQuery, $limitOn, $inTable = false, $inCol = false, $uId = false, $actionId = false, $joinSlug = '~LIMITINGJOIN~' )
	{
//		dump( func_get_args(), 'limitQuery args' );
		$db = &JFactory::getDBO();
		$user = &ApotheosisLib::getUser( $uId );
		
		$tableName = ApotheosisLibAcl::getUserTable( 'people.people', $user->id );
//		dump( $tableName, 'tableName' );
		
		switch( $limitOn ) {
		case( 'people' ):
		case( 'arc_people' ):
			if( $inTable === false ) {
				$inTable = 'p';
			}
			if( $inCol === false ) {
				$inCol = 'id';
			}
			$inTable = $db->nameQuote( $inTable );
			$inCol   = $db->nameQuote( $inCol );
			
			$joinQuery = 'INNER JOIN '.$tableName.' AS lim_ppl_p'
				."\n".'  ON lim_ppl_p.id = '.$inTable.'.'.$inCol
				."\n".'~LIMITINGJOIN~';
			$joinQuery = ApotheosisLibAcl::limitQuery( $joinQuery, 'core.sees', 'lim_ppl_p', 'role', $uId, $actionId );
			break;
		
		default:
			$joinQuery = '';
		}
		
		return str_replace( $joinSlug, $joinQuery, $givenQuery );
	}
	
	/**
	 * Creates and populates the user's people table if it doesn't already exist.
	 * Gves back the name of that table (with minimum overhead if it exists) 
	 * 
	 * @param $tableName string  The name of the short-term table to be created / populated
	 * @param $uId string  The jUserId of the user whose access should be used. Defaults to current user
	 * @return string  The name of the table which now contains this user's people
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
				."\n".'SELECT p.`id` AS `id`, r.`id` AS `role`'
				."\n".'FROM #__apoth_ppl_people AS p'
				."\n".'INNER JOIN #__apoth_sys_roles AS r'
				."\n".'LIMIT 0';
			ApotheosisLibDbTmp::create( $tableName, $createQuery );
		}
		
		// we're using a fresh table, so populate it with data according to the various components
		$null = null;
		ApotheosisLib::callAll( 'helpers'.DS.'auth.php', 'ApothAuth', 'setUserPeople', array($tableName, $uId, $from, $to), $null, true );
		
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
		
		// roles which result from relationships
		$query = 'SELECT rt.*'
			."\n".' FROM #__apoth_ppl_people AS p'
			."\n".' INNER JOIN #__apoth_ppl_relations AS r ON r.relation_id = p.id'
			."\n".' INNER JOIN #__apoth_ppl_relation_tree AS rt ON r.relation_type_id = rt.id'
			."\n".' WHERE p.juserid = '.$uId;
		$db->setQuery( $query );
		$rolesArr = $db->loadObjectList( 'id' );
		if( !is_array($rolesArr) ) { $rolesArr = array(); }
		$roles = $rolesArr;
		
		foreach($rolesArr as $k=>$v) {
			$roles = $roles + ApotheosisLibDb::getAncestors($v->id, '#__apoth_ppl_relation_tree', 'id', 'parent', true);
		}
		foreach($roles as $id=>$role) {
			if( !is_null($roles[$id]->role) ) {
				$roles[$id] = $roles[$id]->role;
			}
			else {
				unset($roles[$id]);
			}
		}
		$roles[] = ApotheosisLibAcl::getRoleId('rel_self');
		
		// roles which result from pastoral involvement
		$catId = ApotheosisData::_( 'people.profileCatId', 'people', 'ids' );
		$mentor = ApotheosisLibAcl::getRoleId('pastoral_sen_mentor');
		$mentee = ApotheosisLibAcl::getRoleId('pastoral_sen_mentee');
		$mSelf  = ApotheosisLibAcl::getRoleId('pastoral_sen_self');
		$query = 'SELECT 1 AS id'
			."\n".'FROM '.$db->nameQuote('#__apoth_ppl_profiles').' AS pro'
			."\n".'INNER JOIN '.$db->nameQuote('#__apoth_ppl_people').' AS p'
			."\n".'   ON p.id = pro.person_id'
			."\n".'WHERE pro.'.$db->nameQuote('category_id').' = '.$db->Quote($catId)
			."\n".'  AND pro.'.$db->nameQuote('property').' = '.$db->Quote($mentee)
			."\n".'  AND p.'.$db->nameQuote('juserid').' = '.$db->Quote($uId)
			."\n".'LIMIT 1';
		$db->setQuery( $query );
		$rolesArr = $db->loadObjectList( 'id' );
		if( !empty($rolesArr) ) {
			$roles[] = $mentee;
			$roles[] = $mSelf;
		}
		
		$query = 'SELECT 1 AS id'
			."\n".'FROM '.$db->nameQuote('#__apoth_ppl_profiles').' AS pro'
			."\n".'INNER JOIN '.$db->nameQuote('#__apoth_ppl_people').' AS p'
			."\n".'   ON p.id = pro.value'
			."\n".'WHERE pro.'.$db->nameQuote('category_id').' = '.$db->Quote($catId)
			."\n".'  AND pro.'.$db->nameQuote('property').' = '.$db->Quote($mentee)
			."\n".'  AND p.'.$db->nameQuote('juserid').' = '.$db->Quote($uId)
			."\n".'LIMIT 1';
		$db->setQuery( $query );
		$rolesArr = $db->loadObjectList( 'id' );
		if( !empty($rolesArr) ) {
			$roles[] = $mentor;
		}
		
		
		$insertQuery = 'INSERT INTO '.$tableName
			."\n".' VALUES ( '.implode(' ), ( ', $roles).' )';
		$db->setQuery($insertQuery);
		$db->Query();
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
		$catId = ApotheosisData::_( 'people.profileCatId', 'people', 'ids' );
		$user = &ApotheosisLib::getUser( $uId );
		$mentor = ApotheosisLibAcl::getRoleId('pastoral_sen_mentor');
		$mentee = ApotheosisLibAcl::getRoleId('pastoral_sen_mentee');
		$mSelf  = ApotheosisLibAcl::getRoleId('pastoral_sen_self');
		
		$insertQuery = 'INSERT IGNORE INTO '.$tableName
			."\n".'SELECT '.$db->Quote($user->person_id).' AS id, '.ApotheosisLibAcl::getRoleId( 'rel_self' ).';'
			."\n"
			."\n".'INSERT IGNORE INTO '.$tableName
			."\n".'SELECT pupil_id, t.role'
			."\n".'FROM #__apoth_ppl_relations AS r'
			."\n".'INNER JOIN #__apoth_ppl_relation_tree AS t'
			."\n".'   ON t.id = r.relation_type_id'
			."\n".'  AND t.role IS NOT NULL'
			."\n".'WHERE r.relation_id = '.$db->Quote($user->person_id)
			."\n".'  AND r.parental = 1'
			."\n".'  AND r.legal_order = 0'
			."\n".'  AND r.correspondence = 1'
			."\n".'  AND r.reports = 1'
			."\n".'  AND '.ApotheosisLibDb::dateCheckSql('valid_from', 'valid_to', $from, $to).';'
			// roles which result from pastoral involvement
			."\n"
			."\n".'INSERT IGNORE INTO '.$tableName
			."\n".'SELECT value, '.$mentee
			."\n".'FROM '.$db->nameQuote('#__apoth_ppl_profiles').' AS pro'
			."\n".'WHERE pro.'.$db->nameQuote('category_id').' = '.$db->Quote($catId)
			."\n".'  AND pro.'.$db->nameQuote('property').' = '.$db->Quote($mentee)
			."\n".'  AND pro.'.$db->nameQuote('person_id').' = '.$db->Quote($user->person_id).';'
			."\n"
			."\n".'INSERT IGNORE INTO '.$tableName
			."\n".'SELECT '.$db->Quote($user->person_id).', '.$mSelf
			."\n".'FROM '.$db->nameQuote('#__apoth_ppl_profiles').' AS pro'
			."\n".'WHERE pro.'.$db->nameQuote('category_id').' = '.$db->Quote($catId)
			."\n".'  AND pro.'.$db->nameQuote('property').' = '.$db->Quote($mentee)
			."\n".'  AND pro.'.$db->nameQuote('person_id').' = '.$db->Quote($user->person_id).';'
			."\n"
			."\n".'INSERT IGNORE INTO '.$tableName
			."\n".'SELECT person_id, '.$mentor
			."\n".'FROM '.$db->nameQuote('#__apoth_ppl_profiles').' AS pro'
			."\n".'WHERE pro.'.$db->nameQuote('category_id').' = '.$db->Quote($catId)
			."\n".'  AND pro.'.$db->nameQuote('property').' = '.$db->Quote($mentee)
			."\n".'  AND pro.'.$db->nameQuote('value').' = '.$db->Quote($user->person_id).';';
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
	function setUserGroups( $tableName, $uId, $from, $to )
	{
		// if no user ID then set no roles
		if( empty($uId) ) { return true; }
		
		$db = &JFactory::getDBO();
		$user = &ApotheosisLib::getUser( $uId );
		
		$insertQuery = 'INSERT IGNORE INTO '.$tableName
			."\n".'SELECT gm.group_id AS id, t.role'
			."\n".'FROM #__apoth_ppl_relations AS r'
			."\n".'INNER JOIN #__apoth_ppl_relation_tree AS t'
			."\n".'   ON t.id = r.relation_type_id'
			."\n".'  AND t.role IS NOT NULL'
			."\n".'INNER JOIN #__apoth_tt_group_members AS gm'
			."\n".'   ON gm.person_id = r.pupil_id'
			."\n".'WHERE r.relation_id = '.$db->Quote($user->person_id)
			."\n".'  AND '.ApotheosisLibDb::dateCheckSql('r.valid_from',  'r.valid_to',  $from, $to).' '
			."\n".'  AND '.ApotheosisLibDb::dateCheckSql('gm.valid_from', 'gm.valid_to', $from, $to).'; ';
		$db->setQuery( $insertQuery );
		$db->Query();
	}
}
?>