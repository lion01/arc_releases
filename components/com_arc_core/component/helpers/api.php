<?php
/**
 * @package     Arc
 * @subpackage  API
 * @copyright   Copyright (C) 2005 Punnet. All rights reserved. See COPYRIGHT_ARC.txt
 * @license     http://www.gnu.org/licenses/gpl.html GNU/GPL. See LICENSE_ARC.txt
 * Arc is free software: you can redistribute it and/or modify
 * it under the terms of version 3 of the GNU General Public License
 * as published by the Free Software Foundation.
 * The disclaimer of warranty as stated in the GPL applies to this program
 */

defined( '_JEXEC' ) or die( 'Restricted access' );

/**
 * Core Read Helper
 *
 * @author     Punnet - Arc Team <arc_developers@pun.net>
 * @package    Arc
 * @subpackage API
 * @since      1.6.1
 */
class ArcApiRead_core extends ArcApiRead
{
	/**
	 * Retrieve the import queue
	 */
	function importqueue( $params = array() )
	{
		$s = ( isset($params['source']) ? $params['source'] : null );
		
		$retVal = ApotheosisData::_( 'core.importQueue', $s, false );
		
		// convert the parameters from newline delimited into array
		foreach( $retVal as $jId=>$job ) {
			if( !empty($job['params']) ) {
				$params = array();
				$pList = explode( "\r\n", $job['params'] );
				foreach( $pList as $p ) {
					$parts = explode( '=', $p, 2 );
					$params[$parts[0]] = $parts[1];
				}
				$retVal[$jId]['params'] = $params;
			}
		}
		
		$ok = ApotheosisData::_( 'core.takeImportQueue', $s, false );
		
		return ( $ok ? $retVal : false );
	}
	
}

/**
 * Core Write Helper
 *
 * @author     Punnet - Arc Team <arc_developers@pun.net>
 * @package    Arc
 * @subpackage API
 * @since      1.6.1
 */
class ArcApiWrite_core extends ArcApiWrite
{
	/**
	 * Write changes to the import queue (eg completion of import jobs)
	 */
	function importqueue()
	{
		$retVal = array(
			  'success' => true
			, 'time' => time()
			, 'args' => func_get_args()
			);
		
		return $retVal;
	}
	
	/**
	 * Add a data file to a data source for an import job
	 */
	function dataFile( $data )
	{
		$dir = ApotheosisData::_( 'core.dataPath', 'core', $data['queueId'] );
		
		if( $dir ) {
			$file = $dir.DS.basename( $data['fileName'] );
			if( !file_exists( $file ) ) {
				// the zipped binary data was base64 encoded before being urlencoded as without this
				// the urlencoding roughly triples the length by %-encoding almost every byte. 
				$written = file_put_contents( $file, base64_decode( $data['raw'] ) );
			}
			else {
				$written = 0;
			}
			$status = 0;
		}
		else {
			$status = 1;
		}
		
		return array( 'status'=>$status, 'written'=>$written );
	}
	
	/**
	 * Mark a data source as complete
	 * Uncompress files and write to new single file
	 * 
	 * @param array $data  Assoc array of format: queueId=>{int}, src=>{string}.
	 *                     The queue id is the job's id in the queue.
	 *                     Src indicates the external data source (eg "csv");
	 *                       must be unique per data source app
	 */
	function dataSource( $data )
	{
		$dir = ApotheosisData::_( 'core.dataPath', 'core', $data['queueId'] );
		
		if( $dir ) {
			$files = array();
			$dh = opendir( $dir );
			while( ($file = readdir($dh)) !== false ) {
				if( is_file($dir.DS.$file) ) {
					$files[] = $dir.DS.$file;
				}
			}
			usort( $files, array( 'ArcApiWrite_core', '_filesort' ) );
			
			$newFile = $dir.DS.'data.xml';
			$fh = fopen( $newFile, 'w' );
			foreach( $files as $file ) {
				$gh = gzopen( $file, 'rb' );
				if( !$gh ) {
					$rv = array( 'status'=>'1', 'message'=>'unable to open archive' );
					break;
				}
				while( !gzeof( $gh ) ) {
					fwrite( $fh, gzread( $gh, 1024 ) );
				}
				fclose( $gh );
				unlink( $file );
			}
			fclose( $fh );
			
			$success = ApotheosisData::_( 'core.setJobReady', $data['queueId'], $data['src'] );
		}
		else {
			$success = false;
		}
		$retVal = array( 'status'=>(int)!$success );
		
		return $retVal;
	}
	
	function _filesort( $a, $b )
	{
		$start = strrpos( $a, '_' ) + 1;
		$aNum = (int)substr( $a, $start, strrpos( $a, '.' ) - $start );
		
		$start = strrpos( $b, '_' ) + 1;
		$bNum = (int)substr( $b, $start, strrpos( $b, '.' ) - $start );
		
		if( $aNum == $bNum ) {
			return 0;
		}
		else {
			return ( $aNum < $bNum ) ? -1 : 1;
		}
	}
}
?>