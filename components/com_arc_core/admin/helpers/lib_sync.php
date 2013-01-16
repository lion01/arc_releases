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

require_once( JPATH_ADMINISTRATOR.DS.'components'.DS.'com_arc_core'.DS.'libraries'.DS.'arc_xml.php' );

class ArcSync extends jObject
{
	function _( $ident )
	{
		$args = func_get_args();
		array_shift($args);
		
		static $cache = array();
		
		$parts = explode( '.', $ident, 3 );
		$cName  = ( empty($parts) ? false : strtolower( array_shift($parts) ) );
		$fName  = ( empty($parts) ? false : array_shift($parts) );
		
//		var_dump_pre($cName, 'cName');
//		var_dump_pre($fName, 'fName');
//		var_dump_pre($params, 'params');
		
		if( empty($cName) || empty($fName) ) {
			return null;
		}
		
		$key = $cName;
		
		$retVal = false;
		if( !isset($cache[$key]) ) {
			$fileName = JPATH_ADMINISTRATOR.DS.'components'.DS.'com_arc_'.$cName.DS.'helpers'.DS.'sync.php';
			if( file_exists($fileName) ) {
				require_once($fileName);
			}
			$cNameFull = 'ArcSync_'.ucfirst($cName);
			if( class_exists($cNameFull) ) {
				$cache[$key] = new $cNameFull();
			}
			
			if( !isset($cache[$key]) ) {
				$cache[$key] = false;
			}
		}
		
		if( is_a($cache[$key], 'ArcSync') && method_exists($cache[$key], $fName) ) {
			$preExecTime = ini_get('max_execution_time'); // get the max_execution_time before we mess with it
			set_time_limit( 600 );
			
			$retVal = call_user_func_array( array($cache[$key], $fName), $args );
			
			set_time_limit( $preExecTime );
		}
		else {
			$retVal = null;
		}
		
		return $retVal;
	}
	
	/**
	 * Searches the job array for a job which matches the given requirements
	 * Enter description here ...
	 * @param array $requirements  Key/value pairs to search for in the jobs array
	 * @param array $jobs  An array of the job arrays
	 * @return int|false  The job id of the matching job or false if no match found
	 */
	function jobSearch( $requirements, $jobs )
	{
		if( empty($requirements) ) {
			reset($jobs);
			return key($jobs);
		}
		
		if( !is_array($requirements) ) {
			return false;
		}
		
		$j = false;
		foreach( $jobs as $jId=>$job ) {
			foreach( $requirements as $k=>$v ) {
				$match = true;
				if( $job[$k] != $v ) {
					$match = false;
					break;
				}
			}
			if( $match ) {
				$j = $jId;
				break;
			}
		}
		return $j;
	}

	/**
	 * For use by the API based importers
	 * Sets up an XML parser for the defined, pre-generated data file
	 * 
	 * @param array $job  The array of job details whose data is sought
	 * @param string $method  The method to use to retrieve the data ('whole'|'progressive')
	 * @return mixed $retVal  The xml object or false if we could not create the required folder
	 */
	function _loadReport( $job, $method = 'whole' )
	{
		$file = ApotheosisData::_( 'core.dataPath', 'core', $job['id'] ).DS.'data.xml';
		
		if( $file ) {
			$tmp = new ArcXml();
			switch( $method ) {
			case( 'whole' ):
				$xml = $tmp->loadFile($file);
				break;
			
			case( 'progressive' ):
				$xml = $tmp->loadFileChunks($file);
				break;
			}
			
			$retVal = $xml;
		}
		else {
			$retVal = false;
		}
		
		return $retVal;
	}
	
	function _cleanDate( $date, $timeAlso = false, $tzCorr = false )
	{
		if ( is_null($date) || ($date == 'NULL') || ($date == '') ) {
			// None given so use default
			$date = null;
		}
		elseif( strpos($date, 'T') !== false ) {
			// ISO standard date/time
			$dateArr = array();
			preg_match( '~(.*)T([^+-]*)([+-].*)?~', $date, $dateArr );
			
			$date = $dateArr[1];
			if( $timeAlso ) {
				$date .= ' '.$dateArr[2];
			}
			if( $tzCorr && isset($dateArr[3]) ) {
				$date .= $dateArr[3];
			}
		}
		elseif( strpos($date, ' ') !== false ) {
			// Textual date (eg 18 June 1981)
			$date = date( 'Y-m-d', strtotime($date) );
		}
		else {
			// Possibly a silly back-to-front date courtesy of MS
			$dateArr = explode('-', $date );
			if( strlen($dateArr[0]) != 4 ) { // note Y10K compliance issue. Oh, and have we colonised the moon?
				$date = $dateArr[2].'-'.$dateArr[1].'-'.$dateArr[0];
			}
		}
		
		return $date;
	}
}
?>