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
 * Data Access Helper
 *
 * @author     Punnet - Arc Team <arc_developers@pun.net>
 * @package    Arc
 * @subpackage Core
 * @since      1.6.1
 */
class ApotheosisData_Core extends ApotheosisData
{
	function info()
	{
		return 'Core component installed';
	}
	
	function dataPath( $component, $subdir = null, $create = true )
	{
		$ok = true;
		
		$params = &JComponentHelper::getParams( 'com_arc_core' );
		$dataDir = $params->get( 'arc_data_dir' );
		
		if( !file_exists($dataDir) && !is_dir($dataDir) ) {
			$ok = !$create || mkdir( $dataDir, 0700, true );
		}
		
		$regex = array('#(\.){2,}#', '#[^A-Za-z0-9\.\_\- ]#', '#^\.#'); // copied from Joomla's file library
		
		if( $ok ) {
			$checkedDir = preg_replace( $regex, '', $component );
			if( $checkedDir !== '' ) {
				$dataDir .= DS.$checkedDir;
				if( !file_exists($dataDir) && !is_dir($dataDir) ) {
					$ok = !$create || mkdir( $dataDir, 0700, true );
				}
			}
			else {
				$ok = false;
			}
		}
		
		if( $ok ) {
			if( !is_null( $subdir ) && ($subdir !== '') ) {
				$checkedDir = preg_replace( $regex, '', $subdir );
				if( $checkedDir !== '' ) {
					$dataDir .= DS.$checkedDir;
					if( !file_exists($dataDir) && !is_dir($dataDir) ) {
						$ok = !$create || mkdir( $dataDir, 0700, true );
					}
				}
				else {
					$ok = false;
				}
			}
			else {
				$ok = false;
			}
		}
		
		return $ok ? $dataDir : false;
	}
	
	/**
	 * Recursively empty a directory and optionally delete the target directory
	 * 
	 * @param string $dir  The directory we wish to empty
	 * @param boolean $delDir  Should we delete the directory itself? Defaults to no.
	 * @return boolean $success  True if completely emptied, false if otherwise
	 */
	function emptyDir( $dir, $delDir = false )
	{
		// check target directory exists before proceeding
		if( $targetDir = @opendir($dir) ) {
			$success = true;
			
			// loop through directory contents and deal with each entry appropriately
			while( (($obj = readdir($targetDir)) !== false) ) {
				
				// do not act on dot directories
				if( ($obj == '.') || ($obj == '..') ) {
					continue;
				}
				
				// links
				if( is_link($dir.DS.$obj) ) {
					$success = ( @unlink($dir.DS.$obj) || @rmdir($dir.DS.$obj) ) && $success;
				}
				// files
				elseif( is_file($dir.DS.$obj) ) {
					$success = unlink( $dir.DS.$obj ) && $success;
				}
				// directories
				elseif( is_dir($dir.DS.$obj) ) {
					$success = $this->emptyDir( $dir.DS.$obj, true ) && $success;
				}
			}
			
			// close target directory resource
			closedir( $targetDir );
			
			// delete target directory if requested
			if( $delDir && $success ) {
				$success = rmdir( $dir ) && $success;
			}
		}
		else {
			$success = false;
		}
		
		return $success;
	}
	
	// #####  Batch handling  #####
	
	function importBatches( $ids = null )
	{
		$db = &JFactory::getDBO();
		
		if( !empty($ids) ) {
			if( !is_array($ids) ) {
				$ids = array($ids);
			}
			foreach( $ids as $k=>$v ) {
				$ids[$k] = $db->Quote( $v );
			}
		}
		
		$query = 'SELECT b.*, MIN( q.id IS NULL ) AS `ready`'
			."\n".'FROM '.$db->nameQuote( '#__apoth_sys_import_batches' ).' AS b'
			."\n".'LEFT JOIN '.$db->nameQuote( '#__apoth_sys_import_queue' ).' AS q'
			."\n".'  ON q.'.$db->nameQuote( 'batch_id' ).' = b.id'
			."\n".' AND q.'.$db->nameQuote( 'ready' ).' = 0'
			."\n".'WHERE '.$db->nameQuote( 'done' ).' = '.$db->Quote( '0' )
			.( empty($ids) ? '' : "\n".'  AND b.'.$db->nameQuote( 'id' ).' IN ('.implode( ', ', $ids ).')')
			."\n".'GROUP BY b.id'
			."\n".'ORDER BY '.$db->nameQuote( 'done' ).' ASC, '.$db->nameQuote( 'created' ).' DESC';
		$db->setQuery( $query );
		$batches = $db->loadAssocList( 'id' );
		return $batches;
	}
	
	function importBatchesReady( $ids = null )
	{
		$batches = self::importBatches( $ids );
		$ids = array();
		foreach( $batches as $bId=>$batch ) {
			if( !$batch['ready'] ) {
				unset( $batches[$bId] );
			}
			$batches[$bId]['jobs'] = array();
		}
		$jobs = self::importQueue( null, null, array_keys( $batches ) );
		
		foreach( $jobs as $job ) {
			$batches[$job['batch_id']]['jobs'][$job['id']] = $job;
		}
		
		return $batches;
	}
	
	function addImportBatch( $component, $call, $params )
	{
		$db = &JFactory::getDBO();
		
		if( empty($params) ) {
			$params = '';
		}
		else if( !is_array($params) ) {
			$params = $db->Quote( $params );
		}
		else {
			foreach( $params as $k=>$v ) {
				$params[$k] = $k.'='.$v;
			}
			$params = implode( "\r\n", $params );
		}
		
		$query = 'INSERT INTO '.$db->nameQuote( '#__apoth_sys_import_batches' )
			."\n".'SET '.$db->nameQuote( 'created' ).' = '.$db->Quote( date( 'Y-m-d H:i:s') )
			."\n".', '.$db->nameQuote( 'component' ).' = '.$db->Quote( $component )
			."\n".', '.$db->nameQuote( 'callback' ).' = '.$db->Quote( $call )
			."\n".', '.$db->nameQuote( 'params' ).' = '.$db->Quote( $params )
			."\n".', '.$db->nameQuote( 'done' ).' = '.$db->Quote( 0 );
		$db->setQuery( $query );
		$db->Query();
		return ( $db->getErrorMsg() == '' ? $db->insertId() : false );
	}
	
	function setBatchDone( $batchId )
	{
		$db = &JFactory::getDBO();
		
		$query = 'UPDATE '.$db->nameQuote( '#__apoth_sys_import_batches' )
			."\n".'SET '.$db->nameQuote( 'done' ).' = 1'
			."\n".'WHERE '.$db->nameQuote( 'id' ).' = '.$db->Quote( $batchId );
		$db->setQuery( $query );
		$retVal = $db->Query();
		
		return $retVal;
	}
	
	
	// #####  Queue handling  #####
	
	function importQueue( $source = null, $taken = null, $batchIds = null )
	{
		$db = &JFactory::getDBO();
		if( !empty($batchIds) ) {
			if( !is_array($batchIds) ) {
				$batchIds = array($batchIds);
			}
			foreach( $batchIds as $k=>$v ) {
				$batchIds[$k] = $db->Quote( $v );
			}
		}
		else if( is_array( $batchIds ) ) {
			return array();
		}
		
		$dbQ = $db->nameQuote( 'q' );
		$dbS = $db->nameQuote( 's' );
		$query = 'SELECT '.$dbQ.'.*, '.$dbS.'.name AS '.$db->nameQuote( 'src_name' )
			."\n".'FROM '.$db->nameQuote( '#__apoth_sys_import_queue' ).' AS '.$dbQ
			."\n".'LEFT JOIN '.$db->nameQuote( '#__apoth_sys_data_sources' ).' AS '.$dbS
			."\n".'  ON '.$dbS.'.'.$db->nameQuote( 'id' ).' = '.$dbQ.'.'.$db->nameQuote( 'src' )
			."\n".'WHERE 1=1'
			.(is_null($source) ? '' : "\n".'  AND '.$db->nameQuote( 'src' ).' = '.$db->Quote($source))
			.(is_null($taken)  ? '' : "\n".'  AND '.$db->nameQuote( 'taken' ).' = '.$db->Quote((int)$taken) )
			.( !is_array($batchIds) ? '' : "\n".'  AND '.$db->nameQuote( 'batch_id' ).' IN ('.implode( ', ', $batchIds ).')');
		$db->setQuery( $query );
		$queues = $db->loadAssocList( 'id' );
		
		return $queues;
	}
	
	function takeImportQueue( $source = null )
	{
		$db = &JFactory::getDBO();
		$query = 'UPDATE '.$db->nameQuote( '#__apoth_sys_import_queue' )
			."\n".'SET '.$db->nameQuote( 'taken' ).' = '.$db->Quote( '1' )
			."\n".'WHERE '.$db->nameQuote( 'taken' ).' = '.$db->Quote( '0' )
			.(is_null($source) ? '' : "\n".'  AND '.$db->nameQuote( 'src' ).' = '.$db->Quote($source));
		$db->setQuery( $query );
		$db->Query();
		
		return $db->getErrorMsg() == '';
	}
	
	function addToImportQueue( $batch, $source, $call, $params )
	{
		$db = &JFactory::getDBO();
		
		if( empty($params) ) {
			$params = '';
		}
		else if( !is_array($params) ) {
			$params = $db->Quote( $params );
		}
		else {
			foreach( $params as $k=>$v ) {
				$params[$k] = $k.'='.$v;
			}
			$params = implode( "\r\n", $params );
		}
		
		$query = 'INSERT INTO '.$db->nameQuote( '#__apoth_sys_import_queue' )
			."\n".'SET '.$db->nameQuote( 'batch_id' ).' = '.$db->Quote( $batch )
			."\n".', '.$db->nameQuote( 'src' ).' = '.$db->Quote( $source )
			."\n".', '.$db->nameQuote( 'call' ).' = '.$db->Quote( $call )
			."\n".', '.$db->nameQuote( 'params' ).' = '.$db->Quote( $params )
			."\n".', '.$db->nameQuote( 'taken' ).' = '.$db->Quote( 0 )
			."\n".', '.$db->nameQuote( 'ready' ).' = '.$db->Quote( 0 );
		$db->setQuery( $query );
		$db->Query();
		return ( $db->getErrorMsg() == '' ? $db->insertId() : false );
	}
	
	function setJobTaken( $jobId )
	{
		$db = &JFactory::getDBO();
		
		$query = 'UPDATE '.$db->nameQuote( '#__apoth_sys_import_queue' )
			."\n".'SET '.$db->nameQuote( 'taken' ).' = 1'
			."\n".'WHERE '.$db->nameQuote( 'id' ).' = '.$db->Quote( $jobId );
		$db->setQuery( $query );
		return $db->Query();
	}
	
	function setJobUntaken( $jobId )
	{
		$db = &JFactory::getDBO();
		
		$query = 'UPDATE '.$db->nameQuote( '#__apoth_sys_import_queue' )
			."\n".'SET '.$db->nameQuote( 'taken' ).' = 0'
			."\n".'WHERE '.$db->nameQuote( 'id' ).' = '.$db->Quote( $jobId );
		$db->setQuery( $query );
		return $db->Query();
	}
	
	function setJobReady( $jobId, $src )
	{
		$db = &JFactory::getDBO();
		
		$db->setQuery( 'SELECT '.$db->nameQuote('id').' FROM #__apoth_sys_data_sources WHERE '.$db->nameQuote( 'name' ).' = '.$db->Quote( $src ) );
		$srcId = $db->loadResult();
		
		$query = 'UPDATE '.$db->nameQuote( '#__apoth_sys_import_queue' )
			."\n".'SET '.$db->nameQuote( 'ready' ).' = 1'
			."\n".'  , '.$db->nameQuote( 'src' ).' = '.$db->Quote( $srcId )
			."\n".'WHERE '.$db->nameQuote( 'id' ).' = '.$db->Quote( $jobId );
		$db->setQuery( $query );
		return $db->Query();
	}
	
	function setJobUnready( $jobId )
	{
		$db = &JFactory::getDBO();
		
		$query = 'UPDATE '.$db->nameQuote( '#__apoth_sys_import_queue' )
			."\n".'SET '.$db->nameQuote( 'ready' ).' = 0'
			."\n".'WHERE '.$db->nameQuote( 'id' ).' = '.$db->Quote( $jobId );
		$db->setQuery( $query );
		return $db->Query();
	}
	
	/**
	 * Gets the names of the files stored in a given job directory
	 * 
	 * @param string $jobId  The job id
	 * @return string $files  Comma separated list of files
	 */
	function getJobFiles( $jobId )
	{
		$files = array();
		$dir = ApotheosisData::_( 'core.dataPath', 'core', $jobId, false );
		
		if( $dir && ($targetDir = @opendir($dir)) ) {
			while( (($obj = readdir($targetDir)) !== false) ) {
				if( is_file($dir.DS.$obj) ) {
					$files[] = $obj;
				}
			}
		}
		$files = implode( ', ', $files );
		
		return $files;
	}
	
	/**
	 * Remove all the data files related to the given job
	 * 
	 * @param array $job  The job info array
	 * @return int|false  The number of files removed, or false on failure
	 */
	function cleanJob( $job )
	{
		$dir = ApotheosisData::_( 'core.dataPath', 'core', $job['id'] );
		if( $dir ) {
			$rv = 0;
			$dirHandle = opendir( $dir );
			while( false !== ( $file = readdir($dirHandle) ) ) {
				if( is_file( $dir.DS.$file) ) {
					$rv++;
					unlink( $dir.DS.$file );
				}
			}
			closedir( $dirHandle );
			rmdir( $dir );
		}
		else {
			$rv = false;
		}
		
		return $rv;
	}
}
?>