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

/**
 * Repository of library function common to both the
 * admin and component sides of the Apotheosis core component
 *
 * @author     lightinthedark <code@lightinthedark.org.uk>
 * @package    Apotheosis
 * @subpackage Core
 * @since 0.1
 */
class ApotheosisLibAcl
{
	/**
	 * Gives a list of all roles in the system including their possible old-style names
	 * @return array  Array of role info arrays (indexed by role id)
	 */
	function getRoleList()
	{
		static $roles = false;
		
		if( $roles === false ) {
			$db = &JFactory::getDBO();
			$query = 'SELECT r1.*'
				."\n".'FROM `jos_apoth_sys_roles` AS r1';
			$db->setQuery( $query );
			$roles = $db->loadAssocList('id');
		}
		
		return $roles;
	}
	
	/**
	 * To maintain backward compatibility by finding the role id for a given old role name
	 * @param string $role  The name of the role whose id is sought
	 * @return int  The id of the named role
	 */
	function getRoleId( $role )
	{
		static $roles = false;
		
		if( $roles === false ) {
			$roles = array();
			
			$db = &JFactory::getDBO();
			$query = 'SELECT r1.*'
				."\n".', CONCAT( r2.role, "_", r1.role ) AS old_1'
				."\n".', CONCAT( r3.role, "_", r2.role, "_", r1.role ) AS old_3'
				."\n".'FROM `jos_apoth_sys_roles` AS r1'
				."\n".'INNER JOIN `jos_apoth_sys_roles` AS r2'
				."\n".'   ON r2.id = r1.parent'
				."\n".'INNER JOIN `jos_apoth_sys_roles` AS r3'
				."\n".'   ON r3.id = r2.parent';
			$db->setQuery( $query );
			$results = $db->loadAssocList('id');
			if( !is_array($results) ) { $results = array(); }
			
			foreach( $results as $id=>$data ) {
				$roles[$data['old_1']] = $id;
				$roles[$data['old_3']] = $id;
			}
			
			$query = 'SELECT r1.id, CONCAT( "rel_", LOWER(r2.description) ) AS `role`'
				."\n".'FROM `jos_apoth_sys_roles` AS r1'
				."\n".'INNER JOIN `jos_apoth_ppl_relation_tree` AS r2'
				."\n".'   ON r2.`role` = r1.id';
			$db->setQuery( $query );
			$results = $db->loadAssocList();
			if( !is_array($results) ) { $results = array(); }
			
			foreach( $results as $id=>$data ) {
				$roles[$data['role']] = (int)$data['id'];
			}
		}
		return ( isset( $roles[$role] ) ? $roles[$role] : null );
	}
	
	function getRoleName( $id )
	{
		$roles = self::getRoleList();
		if( isset($roles[$id]) ) {
			if( $roles[$id]['id'] == $roles[$id]['parent'] ) {
				$retVal = '';
			}
			else {
				if( isset($roles[$roles[$id]['parent']]) ) {
					$prefix = self::getRoleName( $roles[$id]['parent'] );
					if( !empty($prefix) ) {
						$prefix .= ' &gt; ';
					}
				}
				else {
					$prefix = '';
				}
				$retVal = $prefix.$roles[$id]['role'];
			}
		}
		else {
			$retVal = '';
		}
		return $retVal;
	}
	
	/**
	 * Get a list of global role ID's for specified people
	 * 
	 * @param array $ids  Array of arc ID's
	 * @return array $roles  Array of people's global roles indexed on Arc ID
	 */
	function getPeoplesGlobalRoles( $ids )
	{
		$db = &JFactory::getDBO();
		
		foreach( $ids as $k=>$id ) {
			$ids[$k] = $db->Quote( $id );
		}
		$quotedIds = implode( ', ', $ids );
		
		$query = 'SELECT *'
			."\n".'FROM '.$db->nameQuote( '#__apoth_sys_com_roles' )
			."\n".'WHERE '.$db->nameQuote( 'person_id' ).' IN ('.$quotedIds.')';
		$db->setQuery( $query );
		$result = $db->loadAssocList();
		
		$roles = array();
		foreach( $result as $k=>$dataArray ) {
			$roles[$dataArray['person_id']][] = $dataArray['role'];
		}
		
		return $roles;
	}
	
	/**
	 * Get an array of all the permission flags masked with the user's roles
	 * @param $uId int  The JUserId of the user to check
	 */
	function &getUserPermissions( $uId = false, $short = true )
	{
		static $uPerms = array();
		
		if( empty($uId) ) {
			$user = &ApotheosisLib::getUser();
			$uId = $user->id;
		}
		
		if( !isset($uPerms[$uId][$short]) ) {
			$tableName = ApotheosisLibAcl::getUserTable( 'core.permissions', $uId );
			
			$db = &JFactory::getDBO();
			if( $short ) {
				$public = self::getRoleId( 'any_public' );
				$query = 'CREATE TEMPORARY TABLE tmp AS'
					."\n".'SELECT `action`, IF( `sees` = '.$public.' OR `sees` IS NULL, 0, `sees` ) AS `sees`'
					."\n".'FROM '.$tableName.' AS t'
					."\n".'WHERE `allowed` = 1'
					."\n".'GROUP BY t.`action`';
				$db->setQuery( $query );
				$db->query();
				
				$query = 'SELECT t.`action`'
					."\n".', IF( tmp.action IS NULL, -1, NULLIF( MIN( tmp.`sees` ), 0 ) ) AS `sees`'
					."\n".', IF( tmp.action IS NULL, 0, 1 ) AS `allowed`'
					."\n".'FROM '.$tableName.' AS t'
					."\n".'LEFT JOIN tmp'
					."\n".'  ON tmp.action = t.action'
					."\n".'GROUP BY t.`action`';
			}
			else {
				$query = 'SELECT `action`, `sees`, `allowed`'
					."\n".' FROM '.$tableName;
			}
			$db->setQuery( $query );
			$r = $db->loadAssocList( 'action' );
			if( !is_array($r) ) { $r = array(); }
			
			foreach($r as $rId=>$row) {
				$r[$rId]['allowed'] = (bool)$r[$rId]['allowed'];
			}
			$uPerms[$uId][$short] = $r;
		}
		
		return $uPerms[$uId][$short];
	}
	
	/**
	 * Checks the user's permissions list to determine if the user is allowed
	 * to perform the given action (see the given page). Defaults to current action
	 * 
	 * @param $uId int  The jUserId of the user to check
	 * @param $actionId int  The action id to check their permissions for (defaults to current)
	 * @return boolean|null  True or false depending on the arc permissions. NULL if action not found in permissions
	 */
	function getUserPermitted( $uId, $actionId = null )
	{
		if( is_null($actionId) ) {
			$actionId = ApotheosisLib::getActionId();
		}
		
		$p = &ApotheosisLibAcl::getUserPermissions( $uId, true );
		return ( isset($p[$actionId]) ? $p[$actionId]['allowed'] : null );
	}
	
	/**
	 * Checks the user's permissions list to determine if the user is restricted
	 * for the given action (can see only a limited subset of results). Defaults to current action
	 * 
	 * @param $uId int  The jUserId of the user to check
	 * @param $actionId int  The action id to check their restrictions for (defaults to current)
	 * @return boolean|null  True (restricted), false (unrestricted), or NULL (action not found in permissions)
	 */
	function getUserRestricted( $uId, $actionId = null )
	{
		if( is_null($actionId) ) {
			$actionId = ApotheosisLib::getActionId();
		}
		
		$p = &ApotheosisLibAcl::getUserPermissions( $uId, true );
		return ( isset($p[$actionId]) ? !is_null( $p[$actionId]['sees'] ) : null );
	}
	
	/**
	 * Checks to see if a user can reach the page the link would take them too
	 * and return that link if so otherwise return false
	 * 
	 * @param $actionName string  The action name of the link (defaults to current)
	 * @param $dependancies array  Array of dependancies the targetting page will limit on
	 * @param $uId int  The jUserId of the user to check
	 * @return $allowed string|false  Link if allowed, false otherwise
	 */
	function getUserLinkAllowed( $actionName, $dependancies = null, $uId = null )
	{
		$actionId = ApotheosisLib::getActionIdByName( $actionName );
		if( is_null($actionId) ) {
			return null;
		}
		
		if( empty($uId) ) {
			$user = &ApotheosisLib::getUser();
			$uId = $user->id;
		}
		
		$allowed = ApotheosisLibAcl::getUserPermitted( $uId, $actionId );
		if( $allowed && ApotheosisLibAcl::getUserRestricted($uId, $actionId) ) {
			$allowed = ApotheosisLibAcl::checkDependancies( $uId, $actionId, $dependancies );
		}
		
		if( $allowed ) {
			$allowed = ApotheosisLib::getActionLinkByName( $actionName, $dependancies );
		}
		
		return $allowed;
	}
	
	/**
	 * Checks to see if a user's various attributes fulfil all of the dependancies for the given (or current) action
	 * 
	 * @param $uId  The jUser id of the user to check
	 * @param $actionId  Optional id of the action to check
	 * @param $dependancies  Optional array of dependancy values to use. if omitted the current request's values will be used
	 * @return boolean  true if dependancies met, false otherwise
	 */
	function checkDependancies( $uId, $actionId = null, $dependancies = null, $debug = false )
	{
		if( is_null($actionId) ) {
			$actionId = ApotheosisLib::getActionId();
			if( is_null($actionId) ) {
				return true;
			}
		}
		
		$dependsOn = ApotheosisLib::getActionDependancies( $actionId );
		
		if( is_null($dependancies) ) {
			foreach( $dependsOn['fixed'] as $prop=>$val ) {
				$deps[$prop] = JRequest::getVar( $prop );
			}
			foreach( $dependsOn['variable'] as $prop=>$val ) {
				$deps[$prop] = JRequest::getVar( $prop );
			}
		}
		else {
			foreach( $dependsOn['fixed'] as $prop=>$val ) {
				$deps[$prop] = $val;
			}
			foreach( $dependsOn['variable'] as $prop=>$val ) {
				$deps[$prop] = $dependancies[$val];
			}
		}
		
		if( $debug ) {
			$u = &ApotheosisLib::getUser($uId);
			echo 'checking dependancies for user: '.$u->person_id.' in action '.$actionId.'<br />';
			var_dump_pre($dependsOn, 'action dependancies');
		}
		
		foreach( $dependsOn['fixed'] as $prop=>$val ) {
			if( $deps[$prop] != $val ) {
				if( $debug ) {
					echo 'failed when checking fixed dependancy '.$prop.'<br />';
					var_dump_pre($val, 'expecting:');
					var_dump_pre($deps[$prop], 'found:');
				}
				return false;
			}
		}
		
		foreach( $dependsOn['variable'] as $prop=>$val ) {
			$given = $deps[$prop];
			if( false == ApotheosisLibAcl::checkDependancy( $val, $given, $uId, $actionId ) ) {
				if( $debug ) {
					echo 'failed when checking variable dependancy '.$prop.'<br />';
					var_dump_pre($val, 'expecting:');
					var_dump_pre($given, 'found:');
				}
				return false;
			}
		}
		
		return true;
	}
	
	/**
	 * Checks to see if a user's various attributes fulfil all of the dependancies for the given (or current) action
	 * 
	 * @param $uId  The jUser id of the user to check
	 * @param $actionId  optional id of the action to check
	 * @return boolean  true if dependancies met, false otherwise
	 */
	function checkDependancy( $ident, $given, $uId = false, $actionId = null )
	{
		if( is_null($actionId) ) {
			$actionId = ApotheosisLib::getActionId();
			if( is_null($actionId) ) {
				return true;
			}
		}
		
		$db = &JFactory::getDBO();
		
		$limitParts = explode( '.', $ident );
		$fName = ( empty($limitParts) ? 'permissions' : array_pop($limitParts) );
		$cName = ( empty($limitParts) ? 'core' : array_pop($limitParts) );
		$fileName = JPATH_SITE.DS.'components'.DS.'com_arc_'.$cName.DS.'helpers'.DS.'auth.php';
		
		$retVal = false;
		if( file_exists($fileName) ) {
			require_once($fileName);
			$cName = 'ApothAuth_'.$cName;
			$com = new $cName();
			if( method_exists($com, 'checkDependancy') ) {
				$retVal = $com->checkDependancy( $fName, $given, $uId, $actionId );
			}
			else {
				$retVal = true; // *** kind of glosses over the problem
			}
		}
		else {
			$retVal = true; // *** kind of glosses over the problem
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
		$user = &ApotheosisLib::getUser( $uId );
		
		if( is_null($actionId) ) {
			$actionId = ApotheosisLib::getActionId();
		}
		
		// **** the "true" here is because this is causing us problems
		// pretty sure it should work without it... probably config issue
		if( true || ApotheosisLibAcl::getUserPermitted($user->id, $actionId) ) {
			$doIt = ($actionId === false) || ApotheosisLibAcl::getUserRestricted($user->id, $actionId);
//			var_dump_pre($doIt, 'do it 1');
			
			if( $doIt ) {
				$limitParts = explode( '.', $limitOn );
				$cName = ( empty($limitParts) ? 'core' : array_shift($limitParts) );
				$fName = ( empty($limitParts) ? 'permissions' : implode('.', $limitParts) );
				$fileName = JPATH_SITE.DS.'components'.DS.'com_arc_'.$cName.DS.'helpers'.DS.'auth.php';
				$doIt = file_exists($fileName); 
			}
//			var_dump_pre($doIt, 'do it 2');
			
			if( $doIt ) {
				require_once($fileName);
				$cName = 'ApothAuth_'.$cName;
				$com = new $cName();
				$doIt = method_exists( $com, 'limitQuery' );
			}
			
			if( $doIt ) {
				$query = $com->limitQuery( $givenQuery, $fName, $inTable, $inCol, $uId, $actionId, $joinSlug );
			}
			else {
				$query = str_replace( $joinSlug, '', $givenQuery );
			}
		}
		else {
			$db = &JFactory::getDBO();
			$tbl = $db->nameQuote( 'tmp'.substr( reset( explode( ' ', microtime() ) ), 2 ) );
			$emptiness = 'INNER JOIN ( SELECT 1=0 AS `foo`) AS '.$tbl.' ON '.$tbl.'.foo = '.$inTable.'.'.$inCol;
			$query = str_replace( $joinSlug, $emptiness, $givenQuery );
		}
		
		return $query;
	}
	
	/**
	 * Retrieves the name of the user-data table identified 
	 * Calls the creation function for it if it doesn't already exist.
	 * If a non-current date range is to be used, setParam must be called first to set that up
	 * 
	 * @param string $ident  The identity of the user data table to be retrieved ('component.function') 
	 * @param string $uId  The jUserId of the user whose access should be used. Defaults to current user
	 * @param boolean|null $populate  Should the table be populated? true == clear then populate, null == populate if needed, false == do not populate. Defaults to null
	 * @return string  The name of the table which now contains this user's groups
	 */
	function getUserTable( $ident, $uId = null, $populate = null )
	{
		$conf = &self::_getConfig();
		static $tableNames = array();
		if( is_null($uId) || ($uId === false) ) {
			$user = &JFactory::getUser();
			$uId = $user->id;
		}
		$action = false;
		
		$identParts = explode( '.', strtolower($ident) );
		$com   = ( empty($identParts) ? 'core' : $identParts[0] );
		$table = ( empty($identParts) ? 'permissions' : $identParts[1] );
		$from = ( (isset($conf['_hasFrom']) && $conf['_hasFrom'] !== false) ? $conf['dateFrom'] : false );
		$to   = ( (isset($conf['_hasTo']  ) && $conf['_hasTo']   !== false) ? $conf['dateTo']   : false );
		$key = $com.$table.$uId.$from.$to;
		
		if( !isset($tableNames[$key]) || ($populate === true) ) {
			// get the name to use for this table
			$tableName = ApotheosisLibDbTmp::getTable( $com, $table, $uId, $action, $from, $to );
			$tableNames[$key] = $tableName;
			
			// clear the table out if required
			if( $populate === true ) {
				ApotheosisLibDbTmp::clear( $tableNames[$key] );
			}
			
			// Go to the appropriate auth helper if the table is anything less than pristine
			if( ($populate !== false) && !ApotheosisLibDbTmp::getPopulated( $tableNames[$key] ) ) {
				$fileName = JPATH_SITE.DS.'components'.DS.'com_arc_'.$com.DS.'helpers'.DS.'auth.php';
				if( file_exists($fileName) ) {
					require_once($fileName);
					$cName = 'ApothAuth_'.ucfirst($com);
					$fName = 'createTblUser'.ucfirst($table);
					$obj = new $cName();
					$tName = $obj->$fName( $tableNames[$key], $uId );
				}
			}
			
			ApotheosisLibDbTmp::commit( $tableNames[$key] );
		}
		return $tableNames[$key];
	}
	
	function getDatum( $key )
	{
		$conf = &self::_getConfig();
		
		if( !isset($conf[$key]) ) {
			switch( $key ) {
			case( 'dateFrom' ):
			case( 'dateTo' ):
				$conf[$key] = $conf['_nowDate'];
				break;
			}
		}
		
		return ( isset($conf[$key]) ? $conf[$key] : null );
	}
	
	/**
	 * Set parameters for acl generation / checking.
	 * Acceptable keys: 'permissionsAt', 'dateFrom', 'dateTo'
	 * 'permissionsAt' is shorthand for setting 'dateFrom' and 'dateTo'
	 * It may in the future be used to "pretend" that it's a date other than now
	 * 
	 * @param string $key  The name of the parameter to set
	 * @param string $val  The value to set the parameter to
	 */
	function setDatum( $key, $val )
	{
		$conf = &self::_getConfig();
		$d = date( 'Y-m-d H:i:s', strtotime($val) );
		
		switch( $key ) {
		case( 'permissionsAt' ):
			if( $val === false ) {
				$conf['dateFrom'] = $conf['dateTo'] = $conf['permissionsAt'] = false;
				$conf['_hasFrom'] = $conf['_hasTo'] = false;
			}
			else {
				$conf['dateFrom'] = $conf['dateTo'] = $conf['permissionsAt'] = $d;
				$conf['_hasFrom'] = $conf['_hasTo'] = true;
			}
			break;
		
		case( 'dateFrom' ):
			if( $val === false ) {
				$conf[$key] = false;
				$conf['_hasFrom'] = false;
			}
			else {
				$conf[$key] = $d;
				$conf['_hasFrom'] = true;
			}
			break;
		
		case( 'dateTo' ):
			if( $val === false ) {
				$conf[$key] = false;
				$conf['_hasTo'] = false;
			}
			else {
				$conf[$key] = $d;
				$conf['_hasto'] = true;
			}
			break;
		}
		
		return $conf[$key];
	}
	
	function &_getConfig()
	{
		static $conf = array();
		if( empty( $conf ) ) {
			$conf['_nowTime'] = time();
			$conf['_nowDate'] = date( 'Y-m-d H:i:s', $conf['_nowTime'] );
		}
		return $conf;
	}
	
}
?>