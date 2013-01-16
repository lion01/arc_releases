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

jimport( 'joomla.application.component.model' );
require_once( JPATH_ADMINISTRATOR.DS.'components'.DS.'com_arc_core'.DS.'libraries'.DS.'arc_xml.php' );

/**
 * Extension Manager Summary Model
 *
 * @author      David Swain
 * @package     Arc
 * @subpackage  Core
 * @since       1.3
 */
class ArcModelSynch extends JModel
{
	/* xml parser object for parsing SIMS reports (and maybe others)
	 * The JSimpleXML parser fails on large files so am writing my own lighter version */
	var $_parser;
	
	function __construct()
	{
		parent::__construct();
	}
	
	/**
	 * Function to truncate the tables that are passed to it
	 *
	 * @param string  The name of the table to truncate
	 * @param array  An associative array of column name to value pairs
	 */
	function truncate($tablename, $exceptions = false)
	{
		$db = &JFactory::getDBO();
		if ($exceptions === false) {
			$db->setQuery('TRUNCATE TABLE `'.$tablename.'`;');
		}
		else {
			$clauses = array();
			foreach ( $exceptions as $col=>$vals ) {
				$col = $db->nameQuote($col);
				if( is_array($vals) ) {
					foreach($vals as $k=>$val) {
						$vals[$k] = $db->Quote($val);
					}
				}
				else {
					$vals = array( $db->Quote($vals) );
				}
				$clauses[] = $col.' NOT IN ('.implode(', ', $vals).') OR '.$col.' IS NULL';
			}
			$db->setQuery('DELETE FROM `'.$tablename.'`'
				."\n".' WHERE ('.implode(")\n AND (", $clauses).');');
		}
		$db->query();
	}
	
	
	function _numOfRows( $query )
	{
		$extDb = ApotheosisLibDb::getExternalDb();
		$extDb->setQuery('SELECT COUNT(*) FROM ('.$query.') AS newtbl');
		$count = $extDb->loadResult();
		
/*	// Debug info
		echo 'count query: '.$extDb->getQuery().'<br />';
		if (!is_numeric($count)) { echo 'count was not a number'; $count = 0; }
		else { echo 'count is: '.$count.'<br />'; }
// */
		
		return $count;
	}
	
	/**
	 * For use by the SIMS report based importers
	 * Uses our API to run a report in SIMS and parse the output into a JSimpleXML object
	 */
	// **** remove
	function _loadReport__OLD( $rpt, $params = array(), $method = 'whole', $cache = false )
	{
		$arcParams = &JComponentHelper::getParams('com_arc_core');
		$domain = $arcParams->get('host');
		
		$tmp = new ArcXml();
		$this->_keys[$rpt] = $this->_runReport( $rpt, $params, 'file', $cache );
		$file = $domain.'/get_report.php?task=readReport&report='.$rpt.'&key='.$this->_keys[$rpt];
		
		switch( $method ) {
		case( 'whole' ):
			$xml = $tmp->loadFile($file);
			if( !$cache ) {
				$this->_cleanReport( $rpt );
			}
			break;
		
		case( 'progressive' ):
			$xml = $tmp->loadFileChunks($file);
			break;
		}
		return $xml;
	}
	
	/**
	 * @return string  The unique key to access the generated report
	 */
	// **** remove
	function _runReport__OLD( $rpt, $params, $method, $cache )
	{
		$ch = curl_init();
		
		$post = array('report'=>$rpt, 'task'=>(($method == 'file') ? 'getFile' : 'getReport') );
		if( $cache ) {
			$post['cache'] = 1;
		}
		if( !empty($params) && is_array($params) ) {
			$post['params'] = serialize($params);
		}
		
		$arcParams = &JComponentHelper::getParams('com_arc_core');
		$domain = $arcParams->get('host');
		
		// set URL and other appropriate options
		curl_setopt( $ch, CURLOPT_URL, $domain.'/get_report.php' );
		curl_setopt( $ch, CURLOPT_HEADER, 0 );
		curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true );
		curl_setopt( $ch, CURLOPT_POST, true );
		curl_setopt( $ch, CURLOPT_POSTFIELDS, $post );
		
		$preExecTime = ini_get('max_execution_time'); // get the max_execution_time before we mess with it
		set_time_limit( 1800 );
		
		ob_start();
		// remotely call up the report generation page
		echo curl_exec($ch);
		$key = ob_get_clean();
		
		set_time_limit($preExecTime); // reset the max_execution_time to its original value
		
		// close cURL resource, and free up system resources
		curl_close($ch);
		
		return $key;
	}
	
	/**
	 * Cleans up the files used when loading a report progressively
	 */
	// **** remove
	function _cleanReport__OLD( $rpt )
	{
		$ch = curl_init();
		
		$post = array( 'report'=>$rpt, 'task'=>'cleanup', 'key'=>$this->_keys[$rpt] );
		
		$arcParams = &JComponentHelper::getParams( 'com_arc_core' );
		$domain = $arcParams->get('host');
		
		// set URL and other appropriate options
		curl_setopt( $ch, CURLOPT_URL, $domain.'/get_report.php' );
		curl_setopt( $ch, CURLOPT_HEADER, 0 );
		curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true );
		curl_setopt( $ch, CURLOPT_POST, true );
		curl_setopt( $ch, CURLOPT_POSTFIELDS, $post );
		
		// remotely call up the report generation page
		curl_exec($ch);
		
		// close cURL resource, and free up system resources
		curl_close($ch);
	}
	
	
	function _cleanDate( $date, $timeAlso = false )
	{
		if ( is_null($date) || ($date == 'NULL') || ($date == '') ) {
			// None given so use default
			$date = null;
		}
		elseif( strpos($date, 'T') !== false ) {
			// ISO standard date/time
			$dateArr = explode('T', $date );
			$date = $dateArr[0];
			if( $timeAlso ) {
				$time = explode( '+', $dateArr[1] );
				$date .= ' '.reset( $time );
			}
		}
		elseif( strpos($date, ' ') !== false ) {
			// Textual date (eg 18 June 1981)
			$date = date( 'Y-m-d', strtotime($date) );
		}
		else {
			// Silly back-to-front date courtesy of MS
			$dateArr = explode('-', $date );
			$date = $dobArr[2].'-'.$dateArr[1].'-'.$dateArr[0];
		}
		
		return $date;
	}
}
?>