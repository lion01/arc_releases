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
 * Repository of library functions to deal with short-lived tables
 *
 * @author     lightinthedark <code@lightinthedark.org.uk>
 * @package    Apotheosis
 * @subpackage Core
 * @since 0.1
 */
class ApotheosisLibDbTmp
{
	/**
	 * Initialases a table using the given single query to create and populate it
	 * 
	 * @param string $query  The query to execute
	 * @param boolean $refresh  If true the table will be recreated regardless of previous instances' expiry time
	 * @param string $com  The component name
	 * @param string $table  The table name
	 * @param string $userId  The id of the user to which the data relates
	 * @param string $actionId  The id of the action to which the data relates
	 * @param string $from  The start of the range to which the data relates
	 * @param string $to  The end of the range to which the data relates
	 */
	function initTable( $query, $refresh, $com, $table, $userId = false, $actionId = false, $from = false, $to = false )
	{
		// straighten out defaults
		$user = ApotheosisLib::getUser($userId);
		$userId = $user->id;
		
		// get the table
		$tableName = self::getTable( $com, $table, $userId, $actionId, $from, $to );
		if( $refresh || !self::getExists( $tableName ) ) {
			self::create( $tableName, $query );
			self::setPopulated( $tableName );
		}
		
		self::commit( $tableName );
		return $tableName;
	}
	
	function getTable( $com, $table, $userId, $actionId, $from, $to )
	{
		$store = &self::_getStore( $userId );
		static $cache = array();
		$conf = self::_getConfig();
		
//		var_dump_pre( func_get_args(), 'getTable args' );
//		var_dump_pre( $store, 'store' );
//		var_dump_pre( $conf, 'conf' );
		
		if( $from === false ) { $from = null; }
		if( $to   === false ) { $to   = null; }
		if( $actionId === false ) { $actionId = null; }
		
		$key = $com.$table.$userId.$actionId.$from.$to;
		
		if( !isset($store[$key]) ) {
			// hunt through to find the relevant table id
			$found = false;
			$cur = reset( $store );
			do {
				$found = ($cur['expires']   > $conf['now']
					  && ($cur['component'] == $com)
					  && ($cur['table']     == $table)
					  && ($cur['user']      == $userId)
					  && ($cur['action']    == $actionId)
					  && ($cur['from']      == $from)
					  && ($cur['to']        == $to));
			} while( !$found && ($cur = next( $store )) != false );
//			var_dump_pre( $found, 'found' );
			
			if( $found ) {
				$cache[$key] = $cur['id'];
			}
			else {
				// if there is no relevant table make up a name for it and add to the cache and store
				$id = '#__apoth__tmp_'.substr( $com, 0, 3 ).'_'.$table.'_'.$userId.'_'.time();
				$cache[$key] = $id;
				$store[$id] = array(
					'id'=>$id,
					'exists'=>0,
					'populated'=>0,
					'expires'=>date( 'Y-m-d H:i:s', (time() + $conf['ttl']) ),
					'component'=>$com,
					'table'=>$table,
					'user'=>$userId,
					'action'=>$actionId,
					'from'=>$from,
					'to'=>$to );
				self::commit( $id );
			}
		}
		
		return $cache[$key];
	}
	
	function clear( $tableName )
	{
		$store = &self::_getStore();
		if( empty($tableName) || !isset($store[$tableName]) ) { return false; }
		
		$db = &JFactory::getDBO();
		$query = 'TRUNCATE '.$db->nameQuote( $tableName );
		$db->setQuery( $query );
		$db->query();
		
		return $store[$tableName]['populated'] = !($db->errorMsg() == '');
	}
	
	function getExists( $tableName )
	{
		$store = &self::_getStore();
		if( empty($tableName) || !isset($store[$tableName]) ) { return false; }
		
		return $store[$tableName]['exists'];
	}
	
	function getPopulated( $tableName )
	{
		$store = &self::_getStore();
		if( empty($tableName) || !isset($store[$tableName]) || !$store[$tableName]['exists'] ) { return false; }
		
		return $store[$tableName]['populated'];
	}
	
	function setPopulated( $tableName )
	{
		$store = &self::_getStore();
		if( empty($tableName) || !isset($store[$tableName]) || !$store[$tableName]['exists'] ) { return false; }
		$conf = self::_getConfig();
		
		$store[$tableName]['populated'] = 1;
		$store[$tableName]['expires'] = date( 'Y-m-d H:i:s', (time() + $conf['ttl']) );
		return true;
	}
	
	function create( $tableName, $createQuery )
	{
		$store = &self::_getStore();
		if( empty($tableName) || !isset($store[$tableName]) ) { return false; }
		
		$db = &JFactory::getDBO();
		$createQuery = 'DROP TABLE IF EXISTS '.$tableName.';'
			."\n".$createQuery;
		$db->setQuery( str_replace( '~TABLE~', $tableName, $createQuery ) );
		$db->queryBatch();
//		debugQuery($db);
		return $store[$tableName]['exists'] = ($db->errorMsg() == '');
	}
	
	function setTtl( $tableName, $ttl )
	{
		$store = &self::_getStore();
		if( empty($tableName) || !isset($store[$tableName]) ) { return false; }
		
		$store[$tableName]['expires'] = date( 'Y-m-d H:i:s', (time() + $ttl) );
	}
	
	
	function commit( $tableName )
	{
		$store = &self::_getStore();
		if( empty($tableName) || !isset($store[$tableName]) ) { return false; }
		$conf = self::_getConfig();
		
		$tbl = $store[$tableName];
		
		$db = &JFactory::getDBO();
		$query = 'REPLACE INTO '.$conf['metaTable']
			."\n".'VALUES'
			."\n".'('.$db->Quote($tbl['id'])
			     .','.$db->Quote($tbl['exists'])
			     .','.$db->Quote($tbl['populated'])
			     .','.$db->Quote($tbl['expires'])
			     .','.$db->Quote($tbl['component'])
			     .','.$db->Quote($tbl['table'])
			     .','.$db->Quote($tbl['user'])
			     .','.( is_null($tbl['action']) ? 'NULL' : $db->Quote($tbl['action']) )
			     .','.( is_null($tbl['from'])   ? 'NULL' : $db->Quote($tbl['from'])   )
			     .','.( is_null($tbl['to'])     ? 'NULL' : $db->Quote($tbl['to'])     )
			     .')';
		$db->setQuery( $query );
		$db->Query();
//		debugQuery( $db );
		return ($db->errorMsg() == '');
	}
	
	/**
	 * Flushes all privileges by marking all tables as expired
	 * then calls cleanup to do its usual removal of expired tables
	 */
	function flush( $uId = null )
	{
		$conf = self::_getConfig();
		$db = &JFactory::getDBO();
		
		$query = 'UPDATE '.$conf['metaTable']
			."\n".'SET '.$db->nameQuote( 'expires' ).' = '.$db->Quote( $conf['old'] )
			.(is_null($uId) ? '' : "\n".'WHERE '.$db->nameQuote( 'user' ).' = '.$db->Quote( $uId ) );
		$db->setQuery( $query );
		$db->query();
		
		$r1 = ($db->errorMsg() == '');
		$r2 = self::cleanup();
		
		// Remove any tables that got missed (should be none, but let's be sure)
		// ... get the list of semi-temporary tables
		$prefix = $db->replacePrefix('#__apoth__tmp_'); // prefixes don't get replaced when inside quotes, so must do this first
		$query = 'SHOW TABLES LIKE "'.$prefix.'%"';
		$db->setQuery($query);
		$r = $db->loadResultArray();
		// ... check they need to be dropped
		$droppers = array();
		$found = false;
		$oldTime = time() - 10; // in case other processes are making new tables only forcibly remove things more than 10 seconds old
		foreach( $r as $table ) {
			$parts = explode( '_', $table );
			$t = array_pop( $parts ); // the creation timestamp is the last part of the name
			$u = array_pop( $parts ); // the user id is the next-to-last part of the name
			if( $t < $oldTime && (is_null($uId) || $uId == $u) ) {
				$droppers[] = $table;
			}
		}
		// ... drop 'em
		if( !empty($droppers) ) {
			$query = 'DROP TABLE IF EXISTS '.implode(', ', $droppers).';';
			$db->setQuery($query);
			$db->query();
		}
		
		return $r1 && $r2;
	}
	
	/**
	 * Remove tables that are enough past their expiry
	 * that we can be confident nothing is using them
	 * Operates purely in the db (not in the $store)
	 * *** could be modified to truncate some and delete older, if we decide it would gain anything
	 */
	function cleanup()
	{
		$conf = self::_getConfig();
		$db = &JFactory::getDBO();
		
		$t = explode( ' ', microtime() );
		$query = 'START TRANSACTION;'
			."\n"
			."\n".'INSERT INTO '.$conf['deleTable']
			."\n".'SELECT '.$db->nameQuote( 'id' )
			."\n".'FROM '.$conf['metaTable']
			."\n".'WHERE '.$db->nameQuote( 'expires' ).' <= '.$db->Quote( $conf['old'] ).';'
			."\n"
			."\n".'DELETE m.*'
			."\n".'FROM '.$conf['metaTable'].' AS m'
			."\n".'INNER JOIN '.$conf['deleTable'].' AS t'
			."\n".'   ON t.id = m.id;'
			."\n"
			."\n".'COMMIT;';
		$db->setQuery( $query );
		$db->QueryBatch();
		
		$query = 'SELECT *'
			."\n".'FROM '.$conf['deleTable'];
		$db->setQuery( $query );
		$tables = $db->loadResultArray();
		$dbTables = array();
		
		if( empty($tables) ) {
			return true; // nothing to do so return
		}
		
		foreach( $tables as $k=>$v ) {
			$tables[$k] = $db->Quote( $v );
			$dbTables[$k] = $db->nameQuote( $db->replacePrefix( $v ) );
		}
		
		$query = 'START TRANSACTION;'
			."\n".'SET autocommit=0;'
			."\n"
			."\n".'DELETE FROM '.$conf['deleTable']
			."\n".'WHERE '.$db->nameQuote( 'id' ).' IN ('.implode( ', ', $tables ).');'
			."\n"
			."\n".'DROP TABLE IF EXISTS '.implode(', ', $dbTables).';'
			."\n"
			."\n".'COMMIT;'
			."\n".'SET autocommit=1;';
		$db->setQuery($query);
		$db->queryBatch();
		
		return ($db->errorMsg() == '');
	}
	
	function &_getStore( $uId = false )
	{
		static $lastU = null;
		static $store = array();
		
		if( is_null($lastU) || ($uId !== false && $uId != $lastU) ) {
			self::cleanup(); // clean out old tables before we load 'em up
			$conf = self::_getConfig();
			
			if( $uId === false ) {
				$user = &JFactory::getUser();
				$uId = $user->id;
			}
			$uId = (int)$uId;
			$lastU = $uId;
			
			$db = &JFactory::getDBO();
			$query = 'SELECT *'
				."\n".'FROM '.$conf['metaTable']
				."\n".'WHERE '.$db->nameQuote( 'user' ).' = '.$db->Quote( $uId );
			$db->setQuery( $query );
			$store = $db->loadAssocList( 'id' );
			if( !is_array($store) ) { $store = array(); }
		}
		
		return $store;
	}
	
	function &_getConfig()
	{
		static $config = array();
		
		if( empty($config) ) {
			$db = &JFactory::getDBO();
			
			$config['now'] = date('Y-m-d H:i:s');
			$config['old'] = date('Y-m-d H:i:s', time()-300); // leave 5 mins between "no longer used" and "delete" to avoid concurrency issues
			$config['ttl'] = 3600;
			$config['metaTable'] = $db->nameQuote( '#__apoth_sys_tmp_tables' );
			$config['deleTable'] = $db->nameQuote( '#__apoth_sys_tmp_deletables' );
		}
		
		return $config;
	}
}
?>