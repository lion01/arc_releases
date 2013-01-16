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
class ApotheosisLibDb
{
	// #############  Database utilities  ############

	/**
	 * Gets the current connection to the external database, or a new one according to the parameters previously set
	 * 
	 * @return mixed  The database resource if successful, a JError object otherwise
	 */
	function getExternalDb() 
	{
		static $options = array();
		if (empty($options)) {
			$extDb = false;
			$params = JComponentHelper::getParams('com_arc_core');
			
			if ( $params->get('ext_source', false) ) {
				$options['driver']   = $params->get('driver');
				$options['host']     = $params->get('host');
				$options['user']     = $params->get('user');
				$options['password'] = $params->get('pass');
				$options['db']       = $params->get('db');
				$options['prefix']   = $params->get('prefix');
			}
		}
		
		$extDb = JDatabase::getInstance( $options );
		
		return $extDb;
	}

	function disableDBChecks( $tblArr )
	{
		$db = &JFactory::getDBO();
		$db->setQuery('SET FOREIGN_KEY_CHECKS = 0');
		$db->query();
		foreach ($tblArr as $k=>$v) {
			$db->setQuery('ALTER TABLE `'.$v.'` SET UNIQUE_CHECKS = 0');
			$db->query();
			$db->setQuery('ALTER TABLE `'.$v.'` SET AUTOCOMMIT = 0');
			$db->query();
		}
	
	}
	
	function enableDBChecks( $tblArr )
	{
		$db = &JFactory::getDBO();
		foreach ($tblArr as $k=>$v) {
			$db->setQuery('ALTER TABLE `'.$v.'` SET AUTOCOMMIT = 1');
			$db->query();
			$db->setQuery('COMMIT');
			$db->query();
			$db->setQuery('ALTER TABLE `'.$v.'` SET UNIQUE_CHECKS = 1');
			$db->query();
		}
		$db->setQuery('SET FOREIGN_KEY_CHECKS = 1');
		$db->query();
	}
	
	
	/**
	 * Deletes rows from the given table which match all the values given for each item
	 *
	 * @param $tbl string  The name of the table from which to delete rows
	 * @param $items array  An array of objects defining rows to be deleted
	 * @return int  The number of affected rows
 	 */
	function deleteList( $tbl, &$items )
	{
		if( empty($items) ) {
			return 0;
		}

		$db = &JFactory::getDBO();

		foreach( $items as $k=>$v ) {
			$keys = array_keys( get_object_vars( reset( $items ) ) );
			$vals = array();
			foreach( $keys as $key ) {
				$vals[] = $db->nameQuote($key).' = '.$db->Quote($v->$key);
			}
			$delValsArr[] = '('.implode( ' AND ', $vals).')';
		}
		$delVals = implode( "\n".' OR ', $delValsArr );

		$db->setQuery( 'DELETE FROM #__apoth_tt_group_members WHERE '.$delVals );
		$db->query();
		
		return $db->getAffectedRows();
	}
	
	/**
	 * Inserts a list of items using the delayed / flush insert operations
	 * 
	 * @param string $tbl  The table name into which to insert the objects
	 * @param array $items  The array of objects to be inserted
	 * @return string  The name of the file where the values were stored
	 */
	function insertList( $tbl, &$items )
	{
		if (empty($items)) { return; }
		
		static $tblKeys = array();
		if( !isset( $tblKeys[$tbl] ) ) {
			$tblKeys[$tbl] = array_keys( get_object_vars( reset( $items ) ) );
		}
		$fileName = ApotheosisLibDb::insertDelayed( $tbl, $tblKeys[$tbl], $items, 1000000 ); // set up a delayed write with max file size 1MB
		ApotheosisLibDb::insertFlush( $tbl, $tblKeys[$tbl], $fileName );
		return $fileName;
	}
	
	/**
	 * Stack up rows in a file to be inserted when the file reaches a pre-set size,
	 * or when insertFlush is called
	 * 
	 * @param string $tbl  The table name into which to insert the objects
	 * @param array $items  The array of objects to be inserted
	 * @param int $fileSize  The maximum size to allow the temporary data file to grow to
	 * @param string $fileName  The optional name of the data file to use
	 * @return string  The name of the file where the values were stored
	 */
	function insertDelayed( $tbl, $keys, &$items, $fileSize, $fileName = false )
	{
		if ($fileName === false) {
			// generate a unique filename using the microtime
			$tp = explode(' ', microtime());
			$t = $tp[1].'_'.$tp[0];
			$config = &JFactory::getConfig();
			$fileName = $config->getValue( 'config.tmp_path' ).DS.'datfile'.$t.'.dat';
		}
		$datFile = fopen($fileName, 'a');
		
/* //*** Debugging lines
		echo 'filename: '.$fileName.'<br />';
		echo 'len: '.count( $items ).'<br />';
		var_dump_pre(reset($items), 'first in batch: ');
// */
		
		$i = 0;
		foreach ( $items as $k=>$item ) {
			$row = array();
			foreach( $keys as $prop ) {
				if( !isset( $item->$prop ) || is_null( $item->$prop ) ) {
					$row[$prop] = '\N';
				}
				else {
					$row[$prop] = str_replace(array('\\', "\t"), array('\\\\', '\\'."\t"), $item->$prop); // escape literal escape chars and tabs
				}
			}
			fwrite( $datFile, implode( "\t", $row )."\n" );
			if (($i++) > 100) { // check that we're not too big every few rows
				$i = 0;
				clearstatcache();
				if (filesize($fileName) > $fileSize) {
					fclose( $datFile );
					ApotheosisLibDb::insertFlush( $tbl, $keys, $fileName );
					$datFile = fopen($fileName, 'w');
				}
			}
		}
		fclose($datFile);
		return $fileName;
	}
	
	/**
	 * Flush the temporary data file by loading the data into the required table and deleting the file
	 * 
	 * @param string $tbl  The table name into which to insert the objects
	 * @param array $keys  The array of column names to receive the data
	 * @param string $fileName  The optional name of the data file to use
	 * @return string  The error message (if any) generated by the database call
	 */
	function insertFlush( $tbl, $keys, $fileName )
	{
		$db = &JFactory::getDBO();
		
		$query = 'LOAD DATA LOCAL INFILE \''.$fileName.'\''
			."\n".' INTO TABLE '.$tbl.' (`'.implode('`, `', $keys).'`);';
		$db->setQuery( $query );
		$db->query();
		
/* // *** Debugging lines
		debugQuery($db);
		echo 'keys: <b>`'.implode('`, `', $keys).'`</b><br />';
		echo 'datafile: <pre>'.file_get_contents($fileName).'</pre>';
// */
		
		unlink($fileName);
		return ($db->getErrorMsg() == '');
	}
	
	/**
	 * Updates a table in the database with the given list of rows
	 * 
	 * @param string $tbl  The table name into which to insert the objects
	 * @param array $items  The array of objects to be inserted
	 * @param array $joinCols  The columns to use when pairing up old data and new.
	 * @param array $conditions  The = array of key column names to identify the column to update (unused at this time)
	 * @return string $retVal  The number of affected rows or boolean false on db error
	 */
	function updateList( $tbl, &$items, $joinCols, $conditions = array() )
	{
		if (empty($items)) { return; }
		
		$db = &JFactory::getDBO();
		
		// Create "new values" table name
		$tp = explode(' ', microtime());
		$tbl2 = $tbl.'_tmp_'.$tp[1].'_'.str_replace('.', '', $tp[0]);
		
		// Set up strings for querying (create indices, link columns, etc)
		$keys = array_keys( get_object_vars( reset( $items ) ) );
		foreach($joinCols as $v) {
			$primKeysStrArr[] = 'tbl1.'.$v.' = tbl2.'.$v;
			$indexArr[] = 'ALTER TABLE `'.$tbl2.'` ADD INDEX (`'.$v.'`);';
		}
		foreach($keys as $k) {
			$columnNamesArr[] = '`tbl1`.`'.$k.'` = `tbl2`.`'.$k.'`';
		}
		$joinStr = implode(' AND ', $primKeysStrArr);
		$selectStr = '`'.implode('`, `', $keys).'`';
		$setStr = implode(', ', $columnNamesArr);
		$indexStr = implode("\n", $indexArr);
		
		// Create and populate "new values" table
		$query = 'CREATE TABLE `'.$tbl2.'` AS SELECT '.$selectStr.' FROM `'.$tbl.'` LIMIT 0';
		$db->setQuery( $query );
		$db->Query();
		
		ApotheosisLibDb::insertList( $tbl2, $items ); // set up a delayed write with max file size 1MB
		
		// Do the updating
		$query = $indexStr
			."\n".' UPDATE `'.$tbl.'` AS tbl1'
			."\n".' INNER JOIN `'.$tbl2.'` AS tbl2 ON '.$joinStr.''
			."\n".' SET '.$setStr.';';
		$db->setQuery($query);
		$db->queryBatch();
		
		// Error reporting and return values
		if ($db->getErrorMsg() !== '') {
			echo 'query: '.$db->getQuery().'<br />';
			echo 'error: '.$db->getErrorMsg().'<br />';
			$retVal = false;
		}
		else {
			$retVal = $db->getAffectedRows();
		}
		
		// Clean up
		$query = ' DROP TABLE `'.$tbl2.'`;';
		$db->setQuery( $query );
		$db->Query();
		
		return $retVal;
	}
	
	/**
	 * Creates an sql string for use in the WHERE clause of a query to limit the results to those that
	 * fall within the given date range.
	 * 
	 * @param string $from  The field name of the valid_from field
	 * @param string $to  The field name of the valid_to field
	 * @param string $fromDate  The value of the first valid date
	 * @param string $toDate  The value of the last valid date
	 * @return string  The bracket-encapsulated string to add to the WHERE clause
	 */
	function dateCheckSql($fromField, $toField, $fromDate = false, $toDate = false)
	{
		$db = &JFactory::getDBO();
		$qtmp = array();
		if( ($toDate   !== false) && !is_null($toDate)   ) { $qtmp[] = $fromField.' <= '.$db->quote($toDate); }
		if( ($fromDate !== false) && !is_null($fromDate) ) { $qtmp[] = '('.$toField.' >= '.$db->quote($fromDate).' OR '.$toField.' IS NULL)'; }
		$str = (empty($qtmp) ? '( 1=1 )' : '( '.implode(' AND ', $qtmp).' )');
		return $str;
	}
	
	/**
	 * Searches the given table to find the first row where the item is its own parent
	 * 
	 * @param string $table  The name of the table where the records are stored
	 * @param string $idCol  The column name for ids (defaults to 'id')
	 * @param string $parentCol  The column name for the parent id (defaults to 'parent')
	 * 
	 * @return string $allRootItems[]  The value of the first element in the array of matched ids
	 */
	function getRootItem( $table = '#__apoth_cm_courses', $idCol = 'id', $parentCol = 'parent' )
	{
		$allRootItems = ApotheosisLibDb::getRootItems( $table, $idCol, $parentCol );
		
		return reset( $allRootItems );
	}
	
	/**
	 * Searches the given table to find all rows where the item is its own parent
	 * 
	 * @param string $table  The name of the table where the records are stored
	 * @param string $idCol  The column name for ids (defaults to 'id')
	 * @param string $parentCol  The column name for the parent id (defaults to 'parent')
	 * 
	 * @return array $ids[key]  The auto-indexed array of matched ids
	 */
	function getRootItems( $table, $idCol = 'id', $parentCol = 'parent' )
	{
		static $ids = array();
		$key = $table.'~'.$idCol.'~'.$parentCol;
		
		if( !array_key_exists( $key, $ids ) ) {
			$db = &JFactory::getDBO();
			$query = 'SELECT '.$db->nameQuote('id')
				."\n".' FROM '.$db->nameQuote($table)
				."\n".' WHERE '.$db->nameQuote('id').' = '.$db->nameQuote('parent');
			$db->setQuery($query);
			$ids[$key] = $db->loadResultArray();
		}
		
		return $ids[$key];
	}
	
	/**
	 * Updates the ancestry table for the given table.
	 * Actually truncates and rebuilds it, but the end result is the same.
	 */
	function updateAncestry( $table, $idCol = 'id', $parentCol = 'parent' )
	{
		$db = &JFactory::getDBO();
		
		$anc       = $db->nameQuote( $table.'_ancestry' );
		$table     = $db->nameQuote( $table );
		$idCol     = $db->nameQuote( $idCol );
		$parentCol = $db->nameQuote( $parentCol );
		$delCol    = $db->nameQuote( 'deleted' );
		
		$query = 'SELECT '.$delCol
			."\n".' FROM '.$table;
		$db->setQuery( $query );
		$db->query();
		$hasDel = ( $db->errorMsg() == '' );
		
		$query = 'TRUNCATE '.$anc;
		$db->setQuery( $query );
		$db->query();
		if($db->errorMsg() != '') { return false; }
		
		$query = 'INSERT IGNORE INTO '.$anc
			."\n".' SELECT '.$idCol.', '.$parentCol.' AS `ancestor`'
			."\n".' FROM '.$table
			.( $hasDel ? "\n".' WHERE '.$delCol.' = 0' : '' );
		$db->setQuery($query);
		$db->query();
		if($db->errorMsg() != '') { return false; }
		
		$query = ' INSERT IGNORE INTO '.$anc
			."\n".' SELECT p0.id, p1.ancestor'
			."\n".' FROM '.$anc.' AS p0'
			."\n".' INNER JOIN '.$anc.' AS p1'
			."\n".'   ON p1.id = p0.ancestor;';
		$db->setQuery($query);
		do {
			$db->query();
		} while( ($db->getAffectedRows() != 0) && ($db->errorMsg() == '') );
		
		$query = ' INSERT IGNORE INTO '.$anc
			."\n".' SELECT '.$idCol.', '.$idCol
			."\n".' FROM '.$table
			.( $hasDel ? "\n".' WHERE '.$delCol.' = 0' : '' );
		$db->setQuery($query);
		$db->query();
		if($db->errorMsg() != '') { return false; }
	}
	
	/**
	 * Returns the ancestry of the item's parent, plus the item
	 * 
	 * @param string $id  The id of the item whose ancestors are required
	 * @param string $table  The name of the table where the records are stored
	 * @param string $idCol  The column name for ids (defaults to 'id')
	 * @param string $parentCol  The column name for the parent id (defaults to 'parent')
	 * @param bool $complete  Should we get complete data for each item?
	 * @return array  The parent items down to the item with the given id in highest-first order
	 */
	function getAncestors( $id, $table, $idCol = 'id', $parentCol = 'parent', $complete = false )
	{
		static $ancestors;
		$args = func_get_args();
		$aKey = implode( '~', $args );
		
		// no id means no results
		if( is_null($id) ) {
			$retVal = array();
		}
		elseif( isset($ancestors[$aKey]) ) {
			$retVal = $ancestors[$aKey];
		}
		else {
			$db = &JFactory::getDBO();
			$_tableAnc = $db->nameQuote( $table.'_ancestry' );
			$_table = $db->nameQuote( $table );
			$_idCol = $db->nameQuote( $idCol );
			$_id = $db->Quote( $id );
			$_parentCol = $db->nameQuote( $parentCol );
			
			$query = 'SELECT '.($complete ? 't.*' : 't.'.$_idCol.', t.'.$_parentCol)
				."\n".' FROM '.$_table.' AS t'
				."\n".' INNER JOIN '.$_tableAnc.' AS a'
				."\n".'   ON a.ancestor = t.'.$_idCol
				."\n".' WHERE a.id = '.$_id.';' ;
			$db->setQuery( $query );
			$r = $db->loadObjectList( $idCol );
			
			if( $db->getErrorMsg() == '' ) {
				// we have an ancestry table, so use that
				// re-arrange the results to the right format
				$retVal = array();
				if( !empty( $r ) ) {
					$rootItems = ApotheosisLibDb::getRootItems( $table, $idCol, $parentCol );
					do {
						$curId = ( isset($curId) ? $r[$curId]->$parentCol : $id );
						$obj = $r[$curId];
						$obj->id = $curId;
						$obj->_parents = array();
						$obj->_children = array();
						$tmp = array($curId=>$obj);
						$retVal = $tmp + $retVal;
						if( array_search($curId, $rootItems) !== false )  { $curId = false; }
					} while( $curId !== false );
					
					foreach( $r as $row ) {
						if( array_search($row->$idCol, $rootItems) === false ) {
							$retVal[$row->$idCol]->_parents[] = $row->$parentCol;
							$retVal[$row->$parentCol]->_children[] = $row->$idCol;
						}
					}
				}
			}
			else {
				// we have no ancesty table, so do things the recursive / slow way
				$query = 'SELECT '.($complete ? '*' : $_idCol.', '.$_parentCol)
					."\n".' FROM '.$_table
					."\n".' WHERE '.$_idCol.' = '.$_id;
				$db->setQuery( $query );
				$item = $db->loadObject();
				
				if( is_null($item) ) {
					$retVal = array();
				}
				else {
					$item->_parents = array();
					$item->_children = array();
					if ($item->$idCol == $item->$parentCol) {
						$ancestry = array();
					}
					else {
						$ancestry = ApotheosisLibDb::getAncestors( $item->$parentCol, $table, $idCol, $parentCol, $complete );
						$ancestry[$item->$parentCol]->_children[] = $item->$idCol;
						$item->_parents[] = $ancestry[$item->$parentCol]->$idCol;
					}
					unset($item->$parentCol);
					
					$retVal = $ancestry + array($item->$idCol=>$item);
				}
			}
			$ancestors[$aKey] = $retVal;
		}
		return $retVal;
	}
	
	/**
	 * Returns the item, plus the descendants of the item
	 * 
	 * @param string $id  The id of the item whose descendants are required
	 * @param string $table  The name of the table where the records are stored
	 * @param string $idCol  The column name for ids (defaults to 'id')
	 * @param string $parentCol  The column name for the parent id (defaults to 'parent')
	 * @param string $whereSTr  An additional WHERE string to restrict the children selected
	 * @return array  The parent items down to the item with the given id in highest-first order
	 */
	function getDescendants( $id, $table, $idCol = 'id', $parentCol = 'parent', $whereStr = false )
	{
//timer('getting descendants');
		static $descendants;
		$args = func_get_args();
		$dKey = implode( '~', $args );
		
		// no id means no results
		if( is_null($id) ) {
			$retVal = array();
		}
		elseif( isset($descendants[$dKey]) ) {
			$retVal = $descendants[$dKey];
		}
		else {
//timer('pre-query');
			$db = &JFactory::getDBO();
			$_tableAnc = $db->nameQuote( $table.'_ancestry' );
			$_table = $db->nameQuote( $table );
			$_idCol = $db->nameQuote( $idCol );
			$_parentCol = $db->nameQuote( $parentCol );
			$_id = $db->getEscaped( $id );
			
			$query = 'SELECT t.'.$_idCol.', t.'.$_parentCol
				."\n".' FROM '.$_table.' AS t'
				."\n".' INNER JOIN '.$_tableAnc.' AS a'
				."\n".'   ON a.id = t.'.$_idCol
				."\n".' WHERE a.ancestor = '.$_id.';' ;
			$db->setQuery( $query );
			$r = $db->loadObjectList( $idCol );
//timer('post-query');
			
			if( $db->getErrorMsg() == '' ) {
//timer('start quick path');
				// we have an ancestry table, so use that
				// re-arrange the results to the right format
				$rootItems = ApotheosisLibDb::getRootItems( $table, $idCol, $parentCol );
				$retVal = array();
				$children = array();
				foreach( $r as $row ) {
					if( array_search($row->$idCol, $rootItems) === false ) { // the root item is not a genuine child of anything
						$children[$row->$parentCol][] = $row->$idCol;
					}
				}
//timer('pre-loop 1');
				$queue = array( $id );
				$curId = reset( $queue );
				while( $curId !== false ) {
					$retVal[$curId]->id = $curId;
					$retVal[$curId]->_parents = array();
					$retVal[$curId]->_children = array();
					
					if( isset($children[$curId]) && is_array($children[$curId]) ) {
						foreach($children[$curId] as $childId) {
							$queue[] = $childId;
						}
					}
					
					$curId = next($queue);
				}
//timer('post-loop 1');
				
//timer('pre-loop 2');
				foreach( $r as $row ) {
					$retVal[$row->$idCol]->_parents[] = $row->$parentCol;
					$retVal[$row->$parentCol]->_children[] = $row->$idCol;
				}
				if( array_search($id, $rootItems) === false ) {
					unset($retVal[$r[$id]->$parentCol]);
				}
//timer('post-loop 2');

			}
			else {
//timer('start slow path');
				// we have no ancesty table, so do things the recursive / slow way
				
				// We used to do this all with recursion, but that resulted in literally thousands of queries
				// being sent in succession. This was unbearably slow, so now we use this rather cumbersome-but-quick
				// set of left joins, and the following checks for null-ness.
				$query = 'SELECT c.'.$_idCol.' AS level_1, c1.'.$_idCol.' AS level_2, c2.'.$_idCol.' AS level_3, c3.'.$_idCol.' AS level_4'
					."\n".' FROM '.$_table.' AS c'
					."\n".' LEFT JOIN '.$_table.' AS c1 ON c1.'.$_parentCol.' = c.'.$_idCol.' AND c1.'.$_idCol.' != c.'.$_idCol
					."\n".' LEFT JOIN '.$_table.' AS c2 ON c2.'.$_parentCol.' = c1.'.$_idCol
					."\n".' LEFT JOIN '.$_table.' AS c3 ON c3.'.$_parentCol.' = c2.'.$_idCol
					."\n".' WHERE c.'.$_idCol.' = '.$_id
					."\n". (($whereStr != false) ? ' AND '.$whereStr : '' );
				$db->setQuery( $query );
				$r = $db->loadObjectList();
				
				$retVal = array();
				if( !is_null($r) ) {
					foreach ($r as $key=>$levels) {
						if(!is_null($levels->level_4)) {
							$r += ApotheosisLibDb::getDescendants( $levels->level_4, $table, $idCol, $parentCol );
						}
						
						// level 1:
						if( !array_key_exists( $levels->level_1, $retVal ) ) {
							$retVal[$levels->level_1]->id = $levels->level_1;
							$retVal[$levels->level_1]->_parents  = array();
							$retVal[$levels->level_1]->_children = array();
						}
						
						// level 2:
						if(!is_null($levels->level_2)) {
							if( !array_key_exists( $levels->level_2, $retVal ) ) {
								$retVal[$levels->level_2]->id = $levels->level_2;
								$retVal[$levels->level_2]->_parents  = array();
								$retVal[$levels->level_2]->_children = array();
							}
							if( array_search($levels->level_2, $retVal[$levels->level_1]->_children) === false ) { $retVal[$levels->level_1]->_children[] = $levels->level_2; }
							if( array_search($levels->level_1, $retVal[$levels->level_2]->_parents)  === false ) { $retVal[$levels->level_2]->_parents[]  = $levels->level_1; }
							
							// level 3:
							if(!is_null($levels->level_3)) {
								if( !array_key_exists( $levels->level_3, $retVal ) ) {
									$retVal[$levels->level_3]->id = $levels->level_3;
									$retVal[$levels->level_3]->_parents  = array();
									$retVal[$levels->level_3]->_children = array();
								}
								if( array_search($levels->level_3, $retVal[$levels->level_2]->_children) === false ) { $retVal[$levels->level_2]->_children[] = $levels->level_3; }
								if( array_search($levels->level_2, $retVal[$levels->level_3]->_parents)  === false ) { $retVal[$levels->level_3]->_parents[]  = $levels->level_2; }
								
								// level 4:
								if(!is_null($levels->level_4)) {
									if( !array_key_exists( $levels->level_3, $retVal ) ) {
										$retVal[$levels->level_4]->id = $levels->level_4;
										$retVal[$levels->level_4]->_parents  = array();
										$retVal[$levels->level_4]->_children = array();
									}
									if( array_search($levels->level_4, $retVal[$levels->level_3]->_children) === false ) { $retVal[$levels->level_3]->_children[] = $levels->level_4; }
									if( array_search($levels->level_3, $retVal[$levels->level_4]->_parents)  === false ) { $retVal[$levels->level_4]->_parents[]  = $levels->level_3; }
								}
							}
						}
					} // end of main foreach
				}
			}
			$descendants[$dKey] = $retVal; // remember it for next time
		}
//timer('got descendants');
		return $retVal;
	}
}
?>