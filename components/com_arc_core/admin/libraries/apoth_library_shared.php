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

// set up the group selector
JHTML::addIncludePath(JPATH_SITE.DS.'components'.DS.'com_arc_core'.DS.'helpers'.DS.'html');
JHTML::addIncludePath(JPATH_SITE.DS.'components'.DS.'com_arc_report'.DS.'helpers'.DS.'html');
JHTML::addIncludePath(JPATH_SITE.DS.'components'.DS.'com_arc_assessment'.DS.'helpers'.DS.'html');
JHTML::addIncludePath(JPATH_SITE.DS.'components'.DS.'com_arc_attendance'.DS.'helpers'.DS.'html');
JHTML::addIncludePath(JPATH_SITE.DS.'components'.DS.'com_arc_message'.DS.'helpers'.DS.'html');
JHTML::addIncludePath(JPATH_SITE.DS.'components'.DS.'com_arc_behaviour'.DS.'helpers'.DS.'html');
JHTML::addIncludePath(JPATH_SITE.DS.'components'.DS.'com_arc_people'.DS.'helpers'.DS.'html');
JHTML::addIncludePath(JPATH_SITE.DS.'components'.DS.'com_arc_planner'.DS.'helpers'.DS.'html');
JHTML::addIncludePath(JPATH_SITE.DS.'components'.DS.'com_arc_timetable'.DS.'helpers'.DS.'html');
JHTML::addIncludePath(JPATH_SITE.DS.'components'.DS.'com_arc_homepage'.DS.'helpers'.DS.'html');

// These files define several library classes
// They could easily have beeen in this file as the groups of functions below are,
// but having them in separate files makes navigation / edits easier
// and makes separate class definitions a requirement.
require_once( JPATH_SITE.DS.'components'.DS.'com_arc_core'.DS.'libraries'.DS.'apoth_controller.php' );
require_once( JPATH_SITE.DS.'components'.DS.'com_arc_core'.DS.'libraries'.DS.'apoth_factory.php' );
require_once( JPATH_ADMINISTRATOR.DS.'components'.DS.'com_arc_core'.DS.'libraries'.DS.'apoth_library_shared_acl.php' );
require_once( JPATH_ADMINISTRATOR.DS.'components'.DS.'com_arc_core'.DS.'libraries'.DS.'apoth_library_shared_array.php' );
require_once( JPATH_ADMINISTRATOR.DS.'components'.DS.'com_arc_core'.DS.'libraries'.DS.'apoth_library_shared_cycles.php' );
require_once( JPATH_ADMINISTRATOR.DS.'components'.DS.'com_arc_core'.DS.'libraries'.DS.'apoth_library_shared_data.php' );
require_once( JPATH_ADMINISTRATOR.DS.'components'.DS.'com_arc_core'.DS.'libraries'.DS.'apoth_library_shared_db.php' );
require_once( JPATH_ADMINISTRATOR.DS.'components'.DS.'com_arc_core'.DS.'libraries'.DS.'apoth_library_shared_dbtmp.php' );
if ( !function_exists('json_decode') || !function_exists('json_encode') ) {
	require_once( JPATH_ADMINISTRATOR.DS.'components'.DS.'com_arc_core'.DS.'libraries'.DS.'JSON_support.php' );
}

/**
 * Repository of library function common to both the
 * admin and component sides of the Apotheosis core component
 *
 * @author     lightinthedark <code@lightinthedark.org.uk>
 * @package    Apotheosis
 * @subpackage Core
 * @since 0.1
 */
class ApotheosisLibParent
{
	/*
	 * Does all the "... = $this->get(...) and $this->assignRef...
	 * stuff for all the class properties obtained from the model
	 * for the given view.
	 * 
	 * @param object $obj  The view object for which to set variables
	 * @param array $varMap  The array of variable=>property mappings
	 */
	function setViewVars( &$obj, &$varMap, $modelName = false )
	{
		if( $modelName === false ) {
			$modelName = $obj->_defaultModel;
		}
		if( is_array($varMap) ) {
			foreach ($varMap as $var_name=>$property) {
				$$var_name = $obj->get($property, $modelName);
				$obj->assignRef($var_name, $$var_name);
			}
		}
	}
	
	/**
	 * Calls a method in every component (if it exists)
	 * 
	 * @param $file string  The file we're looking for in each component (including subfolder) 
	 * @param $class string  The name of the class to instanciate 
	 * @param $method string  The method to invoke
	 * @param $results array  All results will be stored in $results[component_name_part]
	 * @return boolean  Did all called functions (eventually) complete successfully?
	 */
	function callAll( $file, $class, $method, $args, &$results, $retry )
	{
		// Work out all the files that we'll need to look in
		$dh = opendir( JPATH_SITE.DS.'components' );
		while (false !== ($dir = readdir($dh))) {
			$targetFile = JPATH_SITE.DS.'components'.DS.$dir.DS.$file;
			if( is_dir(JPATH_SITE.DS.'components'.DS.$dir)
			 && (substr($dir, 0, 8) == 'com_arc_')
			 && (file_exists($targetFile)) ) {
				require_once($targetFile);
				$cNamePart = ucfirst(substr( $dir, 8 ));
				$cName = $class.'_'.$cNamePart;
				$cNames[$cName] = $cName;
				$results[$cName] = false;
			}
		}
		
		// Try calling each of those methods in turn, trying again if needed
		$failCount = 999999; // (just a really big number)
		do {
			foreach( $cNames as $cName ) {
				$com = new $cName();
				if( method_exists($com, $method) ) {
					$results[$cName] = call_user_func_array( array($com, $method), $args );
				}
				else {
					unset($cNames[$cName]);
					unset($results[$cName]);
				}
			}
			
			$oldFailCount = $failCount;
			$failCount = 0;
			foreach( $results as $k=>$v ) {
				if( $v === false ) {
					$failCount++;
				}
				else {
					unset($cNames[$k]);
				}
			}
		} while( $retry && ($failCount > 0) && ($failCount < $oldFailCount) );
		
		return ( ($failCount == 0) && isset($oldFailCount) );
	}
	
	
	// #############  String utilities  ############
	
	function mb_unserialize( $serial_str )
	{
		$out = preg_replace('!s:(\d+):"(.*?)";!se', "'s:'.strlen(stripslashes('$2')).':\"'.stripslashes('$2').'\";'", $serial_str );
		return unserialize($out);
	}
	
	function file_get_contents_utf8( $file )
	{
		$encodings = array('UTF-8', 'ISO-8859-1', 'ASCII');
		mb_detect_order( $encodings );
		return mb_convert_encoding( file_get_contents( $file), 'UTF-8', $encodings );
	}
	
	/**
	 * Function to get the ordinal value of a multibyte character
	 * Nicked from php.net/ord
	 * written by kerry@shetline.com
	 *
	 * @param string $c  The string in which the character can be found
	 * @param int $index  The position of the desired character in that string
	 * @param int $bytes  Optional reference to receive byte count of detected character
	 * @return int  The ordinal value of the (possibly multibyte) character
	 */
	function ordUTF8($c, $index, &$bytes)
	{
		$len = strlen($c);
		$bytes = 0;
		
		if ($index >= $len) {
//			echo 'index too high';
			return false;
		}
		
		$h = ord($c{$index});
		
		if ($h <= 0x7F) {
			$bytes = 1;
			return $h;
		}
		else if ($h < 0xC2) {
//			echo 'not multibyte<br />';
			return false;
		}
		else if ($h <= 0xDF && $index < $len - 1) {
			$bytes = 2;
			return ($h & 0x1F) <<  6 | (ord($c{$index + 1}) & 0x3F);
		}
		else if ($h <= 0xEF && $index < $len - 2) {
			$bytes = 3;
			return ($h & 0x0F) << 12 | (ord($c{$index + 1}) & 0x3F) << 6
			                         | (ord($c{$index + 2}) & 0x3F);
		}          
		else if ($h <= 0xF4 && $index < $len - 3) {
			$bytes = 4;
			return ($h & 0x0F) << 18 | (ord($c{$index + 1}) & 0x3F) << 12
			                         | (ord($c{$index + 2}) & 0x3F) << 6
			                         | (ord($c{$index + 3}) & 0x3F);
		}
		else {
//			echo 'fail<br />';
			return false;
		}
	}
	
	/**
	 * Puts a name into the proper case (as determined by $style)
	 * @return string  The formatted name
	 */
	function nameCase( $style, $title = '', $firstname = '', $midnames = '', $surname = '', $tutor = null )
	{
		static $func = false;
		if( $func === false ) { $func = create_function('$matches', 'return $matches[1].ucfirst($matches[2]).strtoupper($matches[3]);'); }
		
		$pattern = '~(\\W|^)(Mc)?(.)~i';
		$title     = preg_replace_callback( $pattern, $func, $title );
		$firstname = preg_replace_callback( $pattern, $func, $firstname );
		$midnames  = preg_replace_callback( $pattern, $func, $midnames );
		$surname   = preg_replace_callback( $pattern, $func, $surname );
		
		switch($style) {
		case( 'pupil' ):
			$retVal = (empty($tutor) ? '' : '('.$tutor.') ')
				.strtoupper($surname)
				.(empty($surname) ? '' : ', ')
				.$firstname
				.( ((empty($firstname) && empty($surname)) || empty($midnames)) ? '' : ' ')
				.strtoupper(substr($midnames, 0, 1));
			break;
		
		case( 'pupil_text' ):
		case( 'person' ):
			$retVal = $firstname
				.(empty($firstname) ? '' : ' ')
				.$surname;
			break;
		
		case( 'teacher' ):
			$retVal = $title
				.(empty($title) ? '' : ' ')
				.strtoupper(substr($firstname, 0, 1))
				.(empty($firstname) ? '' : ' ')
				.$surname;
			break;
		
		default:
			$retVal = $title
				.(empty($title) ? '' : ' ')
				.$firstname
				.(empty($firstname) ? '' : ' ')
				.$surname;
		}
		return $retVal;
	}
	
	/**
	 * Gives the earliest date likely to be of interest to us
	 * (as specified in the component params)
	 * @return string  The date which is set as our earliest point of interest
	 */
	function getEarlyDate()
	{
		$p = &JComponentHelper::getParams( 'com_arc_core' );
		return $p->get( 'early_date' );
	}
	
	/**
	 * Takes an ISO format date/time and returns a more human readable string
	 * @param string $date  ISO standard date/time string
	 * @return string  human readable date/time string
	 */
	function arcDateTime( $date = null )
	{
		return date( 'D jS M Y \(H:i\)', strtotime((!is_null($date) ? $date : date('c'))) );
	}
	
	function gChartEncode( $value, $encoding='simple' )
	{
		switch( $encoding ) {
		case( 'simple' ):
			$chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789';
			$max = strlen( $chars );
			if( !is_numeric($value) || ($value > $max) || ($value < 0) ) {
				$str = '_';
			}
			else {
				$str = substr( $chars, $value, 1 );
			}
			break;
		
		case( 'extended' ):
			$chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789-.';
			$base = strlen( $chars );
			$max = $base * $base;
			if( !is_numeric($value) || ($value > $max) || ($value < 0) ) {
				$str = '__';
			}
			else {
				$v1 = floor( $value / $base );
				$v2 = $value % $base;
				$str = substr( $chars, $v1, 1 ).substr( $chars, $v2, 1 );
			}
			break;
		
		default:
			$str = '_';
			break;
		}
		
		return $str;
	}
	
	// #############  Luhn checknumbers  ############
	
	/**
	 * Strips characters from a string *** maybe should not use regex? ***
	 * 
	 * @param string $inVal  The string to strip characters from
	 * @param array $chars  The characters to strip out
	 * @return string  The original string minus the characters indicated
	 */
	function stripChars(&$inVal, $chars)
	{
		$search = '/'.implode('|', $chars).'/';
		$inVal = preg_replace($search, '', $inVal);
		return $inVal;
	}
	
	/**
	 * Generates a Luhn checknumber of a string, ignoring certain specified characters
	 * 
	 * @param string $inVal  The string for which to generate a checknumber
	 * @param mixed $stripChars  The character or array of characters to ignore when creating the checknumber
	 * @return int  The Luhn checknumber
	 */
	function generateLuhn($inVal, $stripChars = false)
	{
		if (is_string($stripChars)) {
			ApotheosisLib::stripChars($inVal, array($stripChars));
		}
		elseif (is_array($stripChars)) {
			ApotheosisLib::stripChars($inVal, $stripChars);
		}
				
		$inVal = str_replace(array('a', 'b', 'c', 'd', 'e', 'f', 'g', 'h', 'i', 'j', 'k',  'l',  'm',  'n',  'o',  'p',  'q',  'r',  's',  't',  'u',  'v',  'w',  'x',  'y',  'z'),
							 array('0', '1', '2', '3', '4', '5', '6', '7', '8', '9', '10', '11', '12', '13', '14', '15', '16', '17', '18', '19', '20', '21', '22', '23', '24', '25'),
							 strtolower($inVal));

		$numOfDigits = 0 - strlen($inVal); 
		
		$i = -1;
		$total = 0;
		while ($i>=$numOfDigits) {
			if (($i % 2) != 0) {
				$double = 2*(substr($inVal, $i, 1));
				for ($j = 0, $len = strlen($double); $j < $len; $j++) {
					$total += substr($double, $j, 1);
				}
			}
			else {
				$total += substr($inVal, $i, 1);
			}
			$i--;
		}
		
		$num = (10 - ($total % 10)) % 10;
				
		return $num;
	}
	
	/**
	 * Checks the validity of a string with a Luhn checknumber on it
	 * 
	 * @param string $inVal  The string check for validity
	 * @param mixed $stripChars  The character or array of characters to ignore when checking the checknumber
	 * @return boolean  True if the string has a valid Luhn checknumber, false otherwise
	 */
	function checkLuhn($inVal, $stripChars = false)
	{
		
		if (is_string($stripChars)) {
			ApotheosisLib::stripChars($inVal, array($stripChars));
		}
		elseif (is_array($stripChars)) {
			ApotheosisLib::stripChars($inVal, $stripChars);
		}

		$required = ApotheosisLib::generateLuhn(substr($inVal, 0, -1), $stripChars);
		$actual = substr($inVal, -1, 1);

		return ($required == $actual);
	}

	/**
	 * Checks the enrolments for the given class over the last cycle length
	 *
	 * @param string $gId  The group id or array of group ids to check
	 * @return array $pupils  The array of pupils who have left / entered
	 */
	function chkEnrolments( $gId )
	{
		$db = &JFactory::getDBO();
		static $enrolments;
		
		$toCheck = array();
		if( is_array($gId) ) {
			foreach($gId as $v) {
				if( !isset($enrolments[$v]) ) {
					$toCheck[] = $db->Quote($v);
				}
			}
		}
		else {
			if( !isset($enrolments[$gId]) ) {
				$toCheck = array( $db->Quote($gId) );
			}
		}
		
		if( !empty($toCheck) ) {
			// initialise the result arrays
			foreach($toCheck as $gId) {
				$enrolments[$gId]['new'] = array();
				$enrolments[$gId]['old'] = array();
			}
			
			// get the length of the cycle
			$query = 'SELECT LENGTH(format) FROM `#__apoth_tt_patterns`'
					."\n".' WHERE `valid_from` < NOW() AND (`valid_to` > NOW() OR `valid_to` IS NULL)';
			$db->setQuery( $query );
			$cycleLength = $db->loadResult();
			
			// set up the date range
			$fromDate = date('Y-m-d H:i:s', strtotime('-'.$cycleLength.' days'));
			$toDate = date('Y-m-d H:i:s');
	
			// sql to pull out all the enrolments in that period
			$query = 'SELECT `group_id`, `person_id`, `valid_from`, `valid_to` FROM `#__apoth_tt_group_members`'
					."\n".' WHERE '.$db->nameQuote( 'group_id' ).' IN ('.implode(', ', $toCheck).')'
					."\n".' AND `valid_from` BETWEEN '.$db->Quote( $fromDate ).' AND '.$db->Quote( $toDate )
					."\n".' AND '.$db->nameQuote( 'is_student' ).' = 1'; // *** titikaka
			$db->setQuery( $query );
			$newEnrols = $db->loadObjectList();
			
			$query = 'SELECT `person_id`, `valid_from`, `valid_to` FROM `#__apoth_tt_group_members`'
					."\n".' WHERE '.$db->nameQuote( 'group_id' ).' IN ('.implode(', ', $toCheck).')'
					."\n".' AND `valid_to` < '.$db->Quote( $toDate )
					."\n".' AND '.$db->nameQuote( 'is_student' ).' = 1'; // *** titikaka
			$db->setQuery( $query );
			$oldEnrols = $db->loadObjectList();
			
			if( is_array($newEnrols) ) {
				foreach($newEnrols as $row) {
					$enrolments[$row->group_id]['new'][$row->person_id] = $row;
				}
			}
			
			if( is_array($oldEnrols) ) {
				foreach($newEnrols as $row) {
					$enrolments[$row->group_id]['old'][$row->person_id] = $row;
				}
			}
			
			$enrolments[$gId] = $retArr;
		}
		
		// return the array(s)
		if( is_array($gId) ) {
			foreach($gId as $v) {
				$retVal[$v] = $enrolments[$v];
			}
		}
		else {
			$retVal = $enrolments[$gId];
		}
		
		return $retVal;
	}
	
	
	// #############  System-wide Information  ############
	
	/**
	 * Get a list of the defined mark styles, or the named style's details
	 * @param $style string  The (optional) name of the single mark style required
	 * @return array  The details of all (or the specified) mark styles currently defined in the database
	 *                (as name-indexed array of objects to make using JHTML::_('select.genericlist'...) easier
	 */
	function getMarkStyles( $style = false )
	{
		static $styles = false;
		
		if( !is_array($styles)
			|| (($style !== false) && !array_key_exists($style, $styles)) ) {
			$db = &JFactory::getDBO();
			$query = 'SELECT *'
				."\n".' FROM '.$db->nameQuote('#__apoth_sys_markstyles')
				."\n".' WHERE '.ApotheosisLibDb::dateCheckSql( 'valid_from', 'valid_to', date('Y-m-d H:i:s'), date('Y-m-d H:i:s') )
				.(($style === false) ? '' : ' AND `style` = '.$db->Quote($style))
				."\n".' ORDER BY '.$db->nameQuote('style').', '.$db->nameQuote('order');
			$db->setQuery($query);
			$r = $db->loadObjectList();
			
			foreach( $r as $row ) {
				$s = $row->style;
				$m = $row->mark;
				unset( $row->style );
				$styles[$s]->style = $s;
				$styles[$s]->marks[$m] = $row;
			}
		}
		
		return (($style === false) ? $styles : $styles[$style]);
	}
	
	
	function queryStringToArray( $uri = false )
	{
		if( $uri === false ) {
			$uri = $_SERVER['REQUEST_URI'];
		}
		
		$parsed = array();
		$query = parse_url( $uri, PHP_URL_QUERY );
		parse_str( $query, $parsed );
		
		foreach( $parsed as $part=>$val ) {
			if( is_string($val) ) {
				$parsed[$part] = urldecode( $val );
			}
		}
		
		return $parsed;
	}
	
	function getTmpAction()
	{
		return ApotheosisLib::_tmpAction( false, null );
	}
	
	function setTmpAction( $id )
	{
		return ApotheosisLib::_tmpAction( true, $id );
	}
	
	function resetTmpAction()
	{
		return ApotheosisLib::_tmpAction( true, null );
	}
	
	function _tmpAction( $write, $value )
	{
		static $cur = null;
		
		if( $write ) {
			$cur = $value;
		}
		
		return $cur;
	}
	
	/**
	 * Find the id for the named action
	 * 
	 * @param $name string  The name of the action
	 * 
	 * @return int|null  The action id found, or null if none found
	 */
	function getActionIdByName( $name )
	{
		$actions = &ApotheosisLib::getActions( true );
		return ( isset($actions[$name]) ? $actions[$name] : null );
	}
	
	/**
	 * Finds the action id for the page matching the given requirements,
	 * or the current page if no requirements given
	 * If there is no action which matches the requirements, false is returned
	 * (effectively saying "I don't know about that one")
	 * 
	 * @param array  The optional requirements to use (property=>value)
	 * @param array  The optional dependancies to use (property=>value)
	 * @param int  Do we want to generate debugging output? if so, which action id do we focus on
	 * 
	 * @return int|null  The action id found, or null if none found
	 */
	function getActionId( $requirements = false, $dependancies = array(), $debug = false )
	{
		static $ids = array();
		
		if( $debug == 107 ) {
// 			var_dump_pre( $requirements, '$requirements:' ); // *** remove
		}
		
		if( is_string($requirements) ) {
			$requirements = self::queryStringToArray( $requirements );
		}
		
		elseif( !is_array($requirements) ) {
			$tmp = ApotheosisLib::getTmpAction();
			if( !is_null($tmp) ) {
				$requirements = array('tmp');
				$dependancies = array();
				$ids[md5(serialize($requirements))] = $tmp;
			}
			else {
				$requirements = self::queryStringToArray();
			}
		}
		
		
		
		if( !is_array($dependancies) ) { $dependancies = array(); }
		$requirements += $dependancies;
		$key = md5(serialize($requirements));
		if( isset($requirements['permissionsAt']) ) {
			ApotheosisLibAcl::setDatum( 'permissionsAt', $requirements['permissionsAt'] );
			unset( $requirements['permissionsAt'] );
		}
		
$debugId = ( is_bool($debug) ? false : $debug );
$debug = ($debug !== false);
if( $debug ) {
	ob_start();
	var_dump_pre($requirements, 'requirements');
}
		if( !isset($ids[$key]) ) {
			$actions = &ApotheosisLib::getActions();
			$options = array_keys($actions);
			
			// Check each option to see if it has all the correct name=>value pairs
			foreach( $options as $k=>$aId ) {
				$action = &$actions[$aId];
				
if( $debug ) {
	if( $action['id'] == $debugId ) {
		var_dump_pre($action, 'action '.$debugId);
	}
	echo $action['id'].' '; 
}
				$ok = true;
				foreach( $requirements as $prop=>$val ) {
					if( (isset($action['query'][$prop]) && ($val  == $action['query'][$prop]))
					 || (isset($action['dependancies']['fixed'][$prop]) && ($val  == $action['dependancies']['fixed'][$prop]))
					 || (isset($action['dependancies']['variable'][$prop]) ) ) {
					 	//$ok = true;
						// leave it alone. Could negate all that stuff in the if
						// but it's more human-readable this way 
					}
					else {
if( $debug ) {
	echo 'fail 1 for '.$prop.'<br />';
}
						$ok = false;
						break;
					}
				}
				if( $ok ) {
					foreach( $action['query'] as $prop=>$val ) {
						// We don't want to have to specify item id as that kinda negates the point of this function
						if( ($prop != 'Itemid') && ( !isset($requirements[$prop]) || $requirements[$prop] != $val ) ) {
if( $debug ) {
	echo 'fail 2 for '.$prop.'<br />';
}
							$ok = false;
						}
					}
				}
				if( $ok ) {
					foreach( $action['dependancies']['fixed'] as $prop=>$val ) {
						if( !isset($requirements[$prop]) || $requirements[$prop] != $val ) {
if( $debug ) {
	echo 'fail 3 for '.$prop.'<br />';
}
							$ok = false;
						}
					}
				}
				if( $ok ) {
					foreach( $action['dependancies']['variable'] as $prop=>$val ) {
						if( !isset($requirements[$prop]) ) {
if( $debug ) {
	echo 'fail 4 for '.$prop.'<br />';
}
							$ok = false;
							break;
						}
					}
				}
				
				if( !$ok ) {
					unset( $options[$k] );
				}
				unset($action); // unlink this reference ready for next loop
			}
if( $debug ) {
	var_dump_pre($options, 'options before dependancy count check');
//	var_dump_pre($actions, 'actions before dependancy count check');
}
			
			switch( count($options) ) {
			case( 0 ):
				$action = null;
				break;
			
			case( 1 ):
				$action = reset($options);
				break;
			
			default:
				$action = reset($options);
				$maxD = 0;
				foreach( $options as $o ) {
					$numD = $actions[$o]['dependancies']['count'];
					if( $numD > $maxD ) {
						$maxD = $numD;
						$action = $o;
					}
				}
				break;
			}
			$ids[$key] = $action;
		}
		
if( $debug ) {
	$out = ob_get_clean();
	echo $out;
	echo 'returning '.$ids[$key].'<br />';
}
		return $ids[$key];
	}
	
	function getActionLinkByName( $name, $dependancyVals = array() )
	{
		return ApotheosisLib::getActionLink( ApotheosisLib::getActionIdByName( $name ), $dependancyVals );
	}
	 
	/**
	 * Substitutes in the dependancy values for an action's link
	 * @param $actionId
	 * @param $dependancyVals
	 * @return unknown_type
	 */
	function getActionLink( $actionId = null, $dependancyVals = array() )
	{
		if( is_null($actionId) ) {
			$actionId = ApotheosisLib::getActionId();
		}
		if( empty($actionId) ) {
			return '';
		}
		
		$actions = &ApotheosisLib::getActions();
		$action = $actions[$actionId];
		if( !isset($action['link']) ) {
			$params = $action['dependancies']['fixed'] + $action['query'];
			if( !isset($params['Itemid']) ) {
				$params['Itemid'] = '';
			}
			
			foreach( $params as $prop=>$val ) {
				$query[] = $prop.'='.$val;
			}
			$action['link'] = 'index.php?'.implode('&', $query);
		}
		
		$link = $action['link'];
		
		// Having picked out the base link, we must substitute in any dependancy values
		if( !empty($action['dependancies']['variable']) ) {
			foreach( $action['dependancies']['variable'] as $prop=>$val ) {
				if( isset($dependancyVals[$val]) ) {
					if( is_array($dependancyVals[$val]) ) {
						foreach( $dependancyVals[$val] as $v ) {
							$d[] = $prop.'[]='.urlencode( $v );
						}
					}
					else {
						$d[] = $prop.'='.urlencode( $dependancyVals[$val] );
					}
				}
				else {
					$d[] = $prop.'=';
				}
			}
			$link .= '&'.implode( '&', $d );
		}
		return $link;
	}
	
	function &getActions( $nameMap = false )
	{
		static $actions = array();
		static $actionNames = array();
		if( empty($actions) ) {
			$db = &JFactory::getDBO();
			$query = 'SELECT *'
					."\n".' FROM #__apoth_sys_actions';
			$db->setQuery( $query );
			$actions = $db->loadAssocList( 'id' );
			if( !is_array($actions) ) { $actions = array(); }
			
			$query = 'SELECT id, link'
				."\n".'FROM #__menu';
			$db->setQuery( $query );
			$menu = $db->loadAssocList( 'id' );
			
			foreach( $menu as $k=>$v ) {
				$menu[$k] = self::queryStringToArray( $v['link'] );
			}
			
			foreach( $actions as $aId=>$action) {
				$action = &$actions[$aId];
				$actionNames[$action['name']] = $aId; 
				
				if( !is_null(($mId = $action['menu_id'])) ) {
					if( isset( $menu[$mId] ) ) {
						$action['query'] = $menu[$mId];
					}
					else {
						unset($action);
						unset($actions[$aId]);
						continue;
					}
				}
				else {
					$action['query'] = array();
				}
				
				ApotheosisLib::setActionDependancies( $action );
				
				if( !is_null($action['menu_id']) ) { $action['query']['Itemid'] = $action['menu_id']; }
				if( !is_null($action['option']) )  { $action['query']['option'] = $action['option']; }
				if( !is_null($action['task']) )    { $action['query']['task']   = $action['task']; }
				
				$o = ( is_null($action['option']) ? '' : 'option='.$action['option'] );
				$t = ( is_null($action['task'])   ? '' : 'task='.$action['task'] );
				
				unset($action);
			}
		}
		
		if( $nameMap ) {
			return $actionNames;
		}
		else {
			return $actions;
		}
	}
	
	/**
	 * Sets up an array (property=>value) of dependancies (from the params) required by a particular action
	 * 
	 * @param $action array  The action's entry in the user's permissions list (by reference)
	 * @return int  How many dependancies are there?
	 */
	function setActionDependancies( &$action )
	{
		$action['dependancies']['fixed'] = array();
		$action['dependancies']['variable'] = array();
		$action['dependancies']['count'] = 0;
		
		$p = $action['params'];
		$params = preg_split( '~\r|\n~', $p, -1, PREG_SPLIT_NO_EMPTY);
		foreach( $params as $param ) {
			$mid = strpos( $param, '=' );
			$prop = substr( $param, 0, $mid );
			$val = substr( $param, ($mid + 1) );
			$matches = array();
			
			$isVar = preg_match('/~.*~/', $val);
			if( $isVar ) {
				$action['dependancies']['variable'][$prop] = trim( $val, '~' );
			}
			else {
				$action['dependancies']['fixed'][$prop] = $val;
			}
			$action['dependancies']['count']++;
			if( isset($action['query'][$prop]) ) {
				unset($action['query'][$prop]); // ensure we don't have multiple versions of a value
			}
		}
		return $action['dependancies']['count'];
	}
	
	/**
	 * Retrieves the dependancies for a particular action
	 * 
	 * @return array The dependancies of the given action (property=>value)
	 */
	function getActionDependancies( $actionId = null )
	{
		if( is_null($actionId) ) {
			$actionId = ApotheosisLib::getActionId();
			if( is_null($actionId) ) {
				return array();
			}
		}
		
		$actions = &ApotheosisLib::getActions();
		if( !isset($actions[$actionId]['dependancies']) ) {
			ApotheosisLib::setActionDependancies( $actions[$actionId] );
		}
		
		return $actions[$actionId]['dependancies'];
	}
	
	// #############  Users and groups  ############

	/**
	 * Retrieves the data from the people table for the current logged
	 * in user
	 *
	 * @param int|string $id  The user to load - Can be an integer or string - If string, it is converted to ID automatically.
	 * @param boolean $access  Do we want to load the user's access / permission info (if it's missing)?
	 * @return object Person details
	 */
	function &getUser( $id = false )
	{
		static $curUser = false;
		$args = func_get_args();
		
		// This is a bit of special-case logic to deal with flash not using sessions cookies properly when uploading files
		// It may be able to be extended to cover logging in as another user in general?
		// *** needs a check on a hashed and salted shared secret ( md5(date('YmdHi').$config->sitepasswd) ? ) to be anything near secure
		if( ($p = JRequest::getVar('pId', false)) && (ApotheosisLib::getActionId() == 120) ) {
			$db = &JFactory::getDBO();
			$query = 'SELECT juserid FROM #__apoth_ppl_people WHERE id = '.$db->Quote($p);
			$db->setQuery( $query );
			$id = $db->loadResult();
		}
		
		
		if( $id === false || is_null($id) ) {
			$id = null;
		}
		$user = &JFactory::getUser( $id );
		$id = $user->id;
		
		if( ($curUser === false) || ($id !== $curUser->id) ) {
			$db = &JFactory::getDBO();
			$query = 'SELECT `id`, `ext_person_id`, `title`, COALESCE( p.preferred_firstname, p.firstname ) AS `firstname`, COALESCE( p.preferred_surname, p.surname ) AS `surname`, `middlenames`, `dob`, `gender`'
				."\n".' FROM `#__apoth_ppl_people` AS p'
				."\n".' WHERE `juserid` = "'.$user->id.'"';
			$db->setQuery($query);
			$details = $db->loadObject();
			
			// if we found no user for this juserid, use their ext_person_id (from the LDAP)
			//  to find them and pair them up with the juser
			if( is_null($details) ) {
				$apothId = ApotheosisLib::getApothId( $user );
				if( $apothId !== false ) {
					$db->setQuery( 'SELECT '
						     .$db->nameQuote('id')
						.', '.$db->nameQuote('ext_person_id')
						.', '.$db->nameQuote('title')
						.', '.'COALESCE( '.$db->nameQuote('preferred_firstname').', '.$db->nameQuote('firstname').' ) AS '.$db->nameQuote('firstname')
						.', '.'COALESCE( '.$db->nameQuote('preferred_surname').', '.$db->nameQuote('surname').' ) AS '.$db->nameQuote('surname')
						.', '.$db->nameQuote('middlenames')
						.', '.$db->nameQuote('dob')
						.', '.$db->nameQuote('gender')
						."\n".' FROM '.$db->nameQuote('#__apoth_ppl_people')
						."\n".' WHERE '.$db->nameQuote('id').' = '.$db->Quote($apothId) );
					$details = $db->loadObject();
					if( !is_null($details) ) {
						$details->juserid = $user->id;
						$db->updateObject('#__apoth_ppl_people', $details, 'id');
					}
				}
			}
			
			// Now we have the right user object, add any Arc-specific information we can
			if( is_null($details) ) {
				$user->person_id     = null;
				$user->ext_person_id = null;
				$user->title         = null;
				$user->firstname     = null;
				$user->middlenames   = null;
				$user->surname       = null;
				$user->dob           = null;
				$user->gender        = null;
			}
			else {
				$user->person_id     = $details->id;
				$user->ext_person_id = $details->ext_person_id;
				$user->title         = $details->title;
				$user->firstname     = $details->firstname;
				$user->middlenames   = $details->middlenames;
				$user->surname       = $details->surname;
				$user->dob           = $details->dob;
				$user->gender        = $details->gender;
			}
			
			$curUser = $user;
		}
		
		return $curUser;
	}
	
	/**
	 * Retrieves the Joomla user id from the corresponding Arc ID
	 *
	 * @param  $arcId  Arc user id
	 * @return string  Joomla user id
	 */
	function getJUserId( $arcId )
	{
		$db = &JFactory::getDBO();
		$query = 'SELECT '.$db->nameQuote( 'juserid' )
			."\n".' FROM '.$db->nameQuote( '#__apoth_ppl_people' )
			."\n".' WHERE '.$db->nameQuote( 'id' ).' = '.$db->Quote( $arcId );
		$db->setQuery( $query );
		
		return $db->loadResult();
	}
	
	/**
	 * Retrieves a list of the Joomla user ids from the corresponding Arc IDs
	 *
	 * @param  $arcId  Arc user id array
	 * @return string  Joomla user id array (indexed by arc id)
	 */
	function getJUserIds( $arcId )
	{
		if( !is_array($arcId) || empty($arcId) ) { return array(); }
		
		$db = &JFactory::getDBO();
		foreach( $arcId as $k=>$v ) {
			$arcId[$k] = $db->Quote( $v );
		}
		$query = 'SELECT '.$db->nameQuote( 'id' ).', '.$db->nameQuote( 'juserid' )
			."\n".' FROM '.$db->nameQuote( '#__apoth_ppl_people' )
			."\n".' WHERE '.$db->nameQuote( 'id' ).' IN ('.implode( ', ', $arcId ).')';
		$db->setQuery( $query );
		$list = $db->loadAssocList();
		
		$r = array();
		foreach( $list as $row ) {
			$r[$row['id']] = $row['juserid'];
		}
		
		return $r;
	}
	
	/**
	 * Copy of getUserYear but only dealing with pId (so as to avoid problems with users that never logged in
	 */
	function getPersonYear( $pId )
	{
		// check to see if the People Manager component and its data access are available
		$fileName = JPATH_SITE.DS.'components'.DS.'com_arc_people'.DS.'helpers'.DS.'data_access.php';
		if( file_exists($fileName) ) {
			require_once($fileName);
			if( method_exists('ApotheosisPeopleData', 'getUserProfileYear') ) {
				$retVal = ApotheosisPeopleData::getUserProfileYear( $pId );
			}
		}
		// if not do it the hard way
		if( is_null($retVal) ) {
			$db = &JFactory::getDBO();
			$pId = $db->Quote($pId);
			$query = 'SELECT MAX(c.year)'
				."\n".'FROM `#__apoth_ppl_people` AS p'
				."\n".'INNER JOIN `#__apoth_tt_group_members` AS gm'
				."\n".'   ON gm.person_id = p.id'
				."\n".'  AND gm.valid_from <= CURDATE()'
				."\n".'  AND ((gm.valid_to >= CURDATE()) OR (gm.valid_to IS NULL))'
				."\n".'INNER JOIN `#__apoth_cm_courses` AS c'
				."\n".'   ON c.id = gm.group_id'
				."\n".'  AND c.deleted = 0'
				."\n".'WHERE p.id = '.$pId
				."\n".'  AND c.ext_type = "pastoral"'
				."\n".'GROUP BY p.id;';
			$db->setQuery( $query );
			$retVal = $db->loadResult();
			
			if( is_null($retVal) ) {
				$query = 'SELECT MAX(c.year)'
					."\n".'FROM `#__apoth_ppl_people` AS p'
					."\n".'INNER JOIN `#__apoth_ppl_relations` AS r'
					."\n".'   ON r.relation_id = p.id'
					."\n".'  AND r.legal_order != 1'
					."\n".'INNER JOIN `#__apoth_tt_group_members` AS gm'
					."\n".'   ON gm.person_id = r.pupil_id'
					."\n".'  AND gm.valid_from <= CURDATE()'
					."\n".'  AND ((gm.valid_to >= CURDATE()) OR (gm.valid_to IS NULL))'
					."\n".'INNER JOIN `#__apoth_cm_courses` AS c'
					."\n".'   ON c.id = gm.group_id'
					."\n".'  AND c.deleted = 0'
					."\n".'WHERE p.id = '.$pId
					."\n".'  AND c.ext_type = "pastoral"'
					."\n".'GROUP BY p.id;';
				$db->setQuery( $query );
				$retVal = $db->loadResult();
			}
		}
		return $retVal;
	}
	
	/**
	 * Retrieves the year group of a given user
	 * (primarily for Arc RSS Reader Module panel in homepage)
	 *
	 * @param int $uId  Joomla user id
	 * @return mixed $year  year group if found, null otherwise
	 */
	function getUserYear( $uId = null )
	{
		$user = &ApotheosisLib::getUser( $uId );
		
		// check to see if the People Manager component and its data access are available
		$fileName = JPATH_SITE.DS.'components'.DS.'com_arc_people'.DS.'helpers'.DS.'data_access.php';
		if( file_exists($fileName) ) {
			require_once($fileName);
			if( method_exists('ApotheosisPeopleData', 'getUserProfileYear') ) {
				$user->year = ApotheosisPeopleData::getUserProfileYear( $user->person_id );
			}
		}
		// if not do it the hard way
		else {
			$uId = $user->id;
			if( !isset($user->year) || is_null($user->year) ) {
				$db = &JFactory::getDBO();
				$uId = $db->Quote($uId);
				$query = 'SELECT MAX(c.year)'
					."\n".'FROM `#__apoth_ppl_people` AS p'
					."\n".'INNER JOIN `#__apoth_tt_group_members` AS gm'
					."\n".'   ON gm.person_id = p.id'
					."\n".'  AND gm.valid_from <= CURDATE()'
					."\n".'  AND ((gm.valid_to >= CURDATE()) OR (gm.valid_to IS NULL))'
					."\n".'INNER JOIN `#__apoth_cm_courses` AS c'
					."\n".'   ON c.id = gm.group_id'
					."\n".'  AND c.deleted = 0'
					."\n".'WHERE p.juserid = '.$uId
					."\n".'  AND c.ext_type = "pastoral"'
					."\n".'GROUP BY p.id;';
				$db->setQuery( $query );
				$year = $db->loadResult();
				
				if( is_null($year) ) {
					$query = 'SELECT MAX(c.year)'
						."\n".'FROM `#__apoth_ppl_people` AS p'
						."\n".'INNER JOIN `#__apoth_ppl_relations` AS r'
						."\n".'   ON r.relation_id = p.id'
						."\n".'  AND r.legal_order != 1'
						."\n".'INNER JOIN `#__apoth_tt_group_members` AS gm'
						."\n".'   ON gm.person_id = r.pupil_id'
						."\n".'  AND gm.valid_from <= CURDATE()'
						."\n".'  AND ((gm.valid_to >= CURDATE()) OR (gm.valid_to IS NULL))'
						."\n".'INNER JOIN `#__apoth_cm_courses` AS c'
						."\n".'   ON c.id = gm.group_id'
						."\n".'  AND c.deleted = 0'
						."\n".'WHERE p.juserid = '.$uId
						."\n".'  AND c.ext_type = "pastoral"'
						."\n".'GROUP BY p.id;';
					$db->setQuery( $query );
					$year = $db->loadResult();
				}
				$user->year = $year;
			}
		}
		
		return $user->year;
	}
	
	function getPersonTutor( $pId = null )
	{
		$db = &JFactory::getDBO();
		$query = 'SELECT c.'.$db->nameQuote('fullname')
			."\n".'FROM '.$db->nameQuote('#__apoth_tt_group_members').' AS gm'
			."\n".'INNER JOIN '.$db->nameQuote('#__apoth_cm_courses').' AS c'
			."\n".'   ON c.'.$db->nameQuote('id').' = gm.'.$db->nameQuote('group_id')
			."\n".'  AND c.'.$db->nameQuote('type').' = '.$db->Quote('pastoral')
			."\n".'  AND c.'.$db->nameQuote('deleted').' = '.$db->Quote('0')
			."\n".'WHERE gm.'.$db->nameQuote('person_id').' = '.$db->Quote($pId)
			."\n".'  AND '.ApotheosisLibDb::dateCheckSql( 'gm.valid_from', 'gm.valid_to', date('Y-m-d'), date('Y-m-d') );
		$db->setQuery( $query );
		return $db->loadResult();
	}
	
	
	/**
	 * Get a formatted person name
	 * 
	 * @param string||array $data  String of Arc ID or array containing data about the person
	 * @param string $style  The style with which to format the returned string
	 * @param boolean $legal  Should we use legal names
	 * @param boolean $withTutor  Should we look up an associated tutor group
	 * @return string  The formatted person name
	 */
	function getPersonName( $data, $style = '', $legal = false, $withTutor = false )
	{
		// make sure we have title, firstname, middlenames and surname
		if( !is_array($data) ) {
			// we only have an Arc id so use that to get the info we need
			$db = &JFactory::getDBO();
			if( $legal ) {
				$cols = "\n, ".$db->nameQuote('firstname')
					."\n, ".$db->nameQuote('middlenames')
					."\n, ".$db->nameQuote('surname');
			}
			else {
				$cols = "\n, ".'COALESCE( '.$db->nameQuote('preferred_firstname').', '.$db->nameQuote('firstname').' ) AS '.$db->nameQuote('firstname')
					."\n, ".$db->nameQuote('middlenames')
					."\n, ".'COALESCE( '.$db->nameQuote('preferred_surname').', '.$db->nameQuote('surname').' ) AS '.$db->nameQuote('surname');
			}
			
			$query = 'SELECT '.$db->nameQuote('id').', '.$db->nameQuote('title')
				.$cols
				."\n".'FROM '.$db->nameQuote('#__apoth_ppl_people')
				."\n".'WHERE '.$db->nameQuote('id').' = '.$db->Quote($data);
			$db->setQuery( $query );
			
			$data = $db->loadAssoc();
		}
		
		// make sure we have a tutor group name if required
		if( $withTutor ) {
			$tutor = ApotheosisData::_( 'course.name', ApotheosisData::_('timetable.tutorgroup', $data['id']) );
		}
		else {
			$tutor = null;
		}
		
		return ApotheosisLib::nameCase( $style, $data['title'], $data['firstname'], $data['middlenames'], $data['surname'], $tutor );
	}
	
	/**
	 * Find the ext_id of the given user from the LDAP
	 * **** This is reliant on using LDAP for authentication and that LDAP having the APOTH id in a description field for a user
	 * @return string  The external id which should be set
	 */
	function getApothId( $user )
	{
		$apothId = false;
		
		$db = &JFactory::getDBO();
		$db->setQuery( 'SELECT '.$db->nameQuote('params')
			."\n".' FROM '.$db->nameQuote('#__plugins')
			."\n".' WHERE '.$db->nameQuote('element').' = '.$db->Quote('LDAP')
			."\n".'   AND '.$db->nameQuote('folder').' = '.$db->Quote('authentication') );
		$params = new JParameter($db->loadResult());
		
		jimport('joomla.client.ldap');
		$ldap = new JLDAP($params);
		if ($ldap->connect())
		{
			$bindtest = $ldap->anonymous_bind();
			if($bindtest)
			{
				$filter = 'uid='.$user->username;
				$binddata = $ldap->simple_search($filter);
				$desc = ( is_array($binddata) && !empty($binddata) ) ? $binddata[0]['description'] : null;
				
				if( is_array($desc) && (($tmp = preg_grep('~^APOTH:~', $desc)) != false) ) {
					$apothId = substr(reset($tmp), 6);
				}
			}
		}
		
		return $apothId;
	}
	
	/**
	 * Get a list of all users limited by the where clause
	 * Joins to tt_group_members AS gm, and to sys_acl AS acl
	 * so checks on group membership can be performed
	 *
	 * @param string $extraWhere  A string to add to the WHERE clause of the query which gets all the users
	 * @param bool $limited  Should we limit the list to just the user's allowable people (defaults to true)
	 */
	function &getUserList( $where, $limited = true, $style = '' )
	{
		static $lists = array();
		$index = $where.$limited;
		if( !isset($lists[$index]) ) {
			$db = &JFactory::getDBO();
			$query = 'SELECT'
				."\n".' p.id, p.juserid, p.title, COALESCE( p.preferred_firstname, p.firstname ) AS firstname, p.middlenames, COALESCE( p.preferred_surname, p.surname ) AS surname, p.gender'
				."\n".' FROM #__apoth_ppl_people AS p'
				."\n".' LEFT JOIN #__apoth_tt_group_members AS gm'
				."\n".'  ON gm.person_id = p.id'
				.($limited ? "\n".'~LIMITINGJOIN~' : '')
				."\n".$where
				."\n".' GROUP BY p.id'
				."\n".' ORDER BY p.surname, p.firstname, p.title';
			if( $limited ) {
				$db->setQuery( ApotheosisLibAcl::limitQuery($query, 'people.people') );
			}
			else {
				$db->setQuery( $query );
			}
			$lists[$index] = $db->loadObjectList( 'id' );
			if( !is_array($lists[$index]) ) { $lists[$index] = array(); }
			foreach( $lists[$index] as $key=>$row ) {
				$lists[$index][$key]->displayname = ApotheosisLib::nameCase($style, $row->title, $row->firstname, $row->middlenames, $row->surname);
			}
		}
		return $lists[$index];
	}
	
	// #############  Relations (users)  ############
	
	function getRelationTypes()
	{
		static $_types = -1;
		
		if( $_types === -1 ) {
			$db = &JFactory::getDBO();
			$query = 'SELECT `id`, `parent`, `description`'
				."\n".' FROM #__apoth_ppl_relation_tree'
				."\n".' WHERE `parent` IS NOT NULL'
				."\n".' ORDER BY description;';
			$db->setQuery($query);
			$types = $db->loadObjectList( 'id' );
			
			$_types = ApotheosisLibArray::sortTree($types, 'id', 1, 'parent', 'description', 1, true);
			
			foreach($_types AS $k=>$v) {
				$_types[$k]->role = 'rel_'.$v->description;
			}
		}
		
		return $_types;
	}
	
	function getRelationTypeId( $role )
	{
		static $roles = array();
		
		if( !array_key_exists($role, $roles) ) {
			$db = &JFactory::getDBO();
			$query = 'SELECT `id`'
				."\n".' FROM #__apoth_ppl_relation_tree'
				."\n".' WHERE `description` = '.$db->Quote($role)
				."\n".'    OR CONCAT("rel_", `description`) = '.$db->Quote($role)
				."\n".' ORDER BY id;';
			$db->setQuery($query);
			$r = $db->loadResultArray( );
			
			$roles[$role] = end($r);
		}
		
		return $roles[$role];
	}
	
	/**
	 * Retrieves a list of all the relations of the given type / category 
	 *
	 * @param $userId int  The JUserId of the user to check
	 * @param $relationTypeIds mixed  The relation type id or array of type ids to look for
	 * @return mixed  If relationTypeIds is an array, then an array or results, otherwise a single result.
	 *                A result is either an array of pupils with the given relation, or false if none found
	 */
	function getRelations( $userId = false, $relationTypeIds = false )
	{
		$user = &ApotheosisLib::getUser( $userId );
		$userId = $user->id;
		
		// static var to reduce repeated effort
		static $checked = array();
		if( !array_key_exists( $userId, $checked ) ) {
			$checked[$userId] = array();
		}
		
		// construct list of types to check for that we haven't before
		if( !is_array($relationTypeIds) ) {
			if( !array_key_exists($relationType, $checked[$userId]) ) {
				$rToCheck = array($relationType);
			}
		}
		else {
			$rToCheck = array();
			foreach($relationTypeIds as $rt) {
				if( !array_key_exists($rt, $checked[$userId]) ) {
					$rToCheck[] = $rt;
				}
			}
		}
		
		if( !empty($rToCheck) ) {
			$db = &JFactory::getDBO();
			foreach($rToCheck as $k=>$v) {
				$rToCheck[$k] = $db->Quote($v);
			}
			$rStr = 'IN ('.implode(', ', $rToCheck).')';
			
			$query = 'SELECT r.relation_id, r.relation_type_id, r.pupil_id'
				."\n".' FROM #__apoth_ppl_relations AS r'
				."\n".' INNER JOIN #__apoth_ppl_people AS p'
				."\n".'    ON p.id = r.relation_id'
				."\n".' WHERE p.juserid = '.$db->Quote($userId)
				."\n".'   AND r.relation_type_id '.$rStr
				."\n".'   AND r.legal_order != 1;';
			$db->setQuery($query);
			$r = $db->loadObjectList();
			
			foreach( $r as $row ) {
				$checked[$userId][$row->relation_type_id][$row->pupil_id] = $row->pupil_id;
			}
		}
		
		if( is_array($relationTypeIds) ) {
			foreach($relationTypeIds as $rt) {
				$retVal[$rt] = ( array_key_exists($rt, $checked[$userId]) ? $checked[$userId][$rt] : array() );
			}
		}
		else {
			$retVal = ( array_key_exists($relationTypeIds, $checked[$userId]) ? $checked[$userId][$relationTypeIds] : array() );
		}

		return $retVal;
	}
	
	// #############  Dates and days  ############
	
	function isoDayToName( $num )
	{
		return date( 'l', strtotime( '1970-06-'.$num ) ); // June is the first eligible month after the unix epoch
	}
}

// #############  Dev tools  ############
// **** global functions that should probably be removed in a production system

/**
 * Sets or displays timer markers to aid speed testing and debugging
 * 
 * @param string $name  The name to associate with this time marker
 * @param int $time  The time (unix timestamp) to allocate (if not set the current time is used)
 * @param string $action  The action to perform: 'add' to add a new timer marker, 'start' and 'stop' to add cumulative timer markers, 'print' to display all markers and clear all markers
 */
function timer( $name = false, $time = false, $action = 'add' )
{
	static $times;
	static $cumulative;
	
	switch ($action) {
	case('add'):
		if ($time === false) {
			$time = microtime();
		}	
		if ($name === false) {
			$name = count($times);
		}
		$tParts = explode(' ', $time);
		$times[] = array( 'n'=>$name, 't'=>($tParts[0] + $tParts[1]) );
		break;
	
	case('start'):
		if ($name === false) {
			$name = count($cumulative);
		}
		if ($time === false) {
			if( !isset($cumulative[$name]['start']) ) {
				$time = microtime();
			}
			else {
				$time = $cumulative[$name]['start'];
			}
		}	
		$tParts = explode(' ', $time);
		$cumulative[$name]['start'] = ($tParts[0] + $tParts[1]);
		break;
	
	case('stop'):
		if ($time === false) {
			$time = microtime();
		}	
		if ($name === false) {
			$name = count($cumulative);
		}
		if( !isset($cumulative[$name]['n']) ) {
			$cumulative[$name]['n'] = $name;
		}
		if( !isset($cumulative[$name]['start']) ) {
			$cumulative[$name]['start'] = $time;
		}
		if( !isset($cumulative[$name]['total']) ) {
			$cumulative[$name]['total'] = 0;
		}
		
		$tParts = explode(' ', $time);
		$cumulative[$name]['end'] = $tParts[0] + $tParts[1];
		$dif = $cumulative[$name]['end'] - $cumulative[$name]['start'];
		$cumulative[$name]['total'] = $cumulative[$name]['total'] + $dif;
		
		unset( $cumulative[$name]['start'] );
		unset( $cumulative[$name]['end'] );
		break;
	
	case('print'):
		// print regular timer marks
		if( empty($times) ) { $times = array(); }
		echo '<table>';
		echo '<tr><th>name</th><th>time</th><th>Diff.</th></tr>';
		$cur = reset($times);
		$prev = $cur;
		do {
			$dif = ($cur['t'] - $prev['t']);
			echo '<tr><td>'.$cur['n'].': </td><td style="padding: 0px 0.5em">'.number_format($cur['t'], 8).'</td><td>'.number_format($dif, 8).'</td></tr>';
		} while (($prev = $cur) && ($cur = next($times)));
		
		// after doing all the incremental times, show a total start->finish time
		$prev = reset($times);
		$cur = end($times);
		$dif = $cur['t'] - $prev['t'];
		echo '<tr><td><b>Total</b>: </td><td style="padding: 0px 0.5em">'.number_format($cur['t'], 8).'</td><td>'.number_format($dif, 8).'</td></tr>';
		echo '</table>';
		
		// print cumulative timers
		if( is_array($cumulative) && !empty($cumulative) ) {
			echo '<table>';
			echo '<tr><th>name</th><th>Cumulative Diff.</th></tr>';
			$total = 0;
			$cur = reset($cumulative);
			$prev = $cur;
			do {
				echo '<tr><td>'.$cur['n'].': </td><td>'.number_format($cur['total'], 8).'</td></tr>';
				$total += $cur['total'];
			} while (($prev = $cur) && ($cur = next($cumulative)));
			
			// after doing all the incremental times, show a total start->finish time
			echo '<tr><td><b>Total</b>: </td><td>'.number_format($total, 8).'</td></tr>';
			echo '</table>';
		}
		
		// clear the time arrays for the next lot of timers
		$times = array();
		$cumulative = array();
	}
}

/**
 * Wraps a var_dump call with <pre> tags to improve display.
 * 
 * @param mixed $mixed  The variable to dump
 * @param string $text  Any preceeding descriptive output text
 * @param string $children  Xdebugs number of array children and object's properties that is shown
 * @param string $depth  Xdebugs number of nested levels of array elements and object properties that is shown
 * @param string $data  Xdebugs maximum string length that is shown
 */
function var_dump_pre( $mixed = null, $text = null, $depth = 3, $children = 128, $data = 512 )
{
	ini_set('xdebug.var_display_max_depth', $depth );
	ini_set('xdebug.var_display_max_children', $children );
	ini_set('xdebug.var_display_max_data', $data );
	
	echo '<pre>';
	echo $text;
	echo var_dump( $mixed );
	echo '</pre>';
	
	ini_set('xdebug.var_display_max_depth', 3 );
	ini_set('xdebug.var_display_max_children', 128 );
	ini_set('xdebug.var_display_max_data', 512 );
}

function dumpQuery( $db, $result = 'no result set specified' )
{
	if( !is_object($db) ) {
		dump( false, 'no database given<br />' );
	}
	else {
		dump( $db->getQuery(), 'query' );
		dump( $db->getErrorMsg(), 'error' );
		dump( mysql_info(), 'info' );
		dump( $db->getAffectedRows(), 'affected' );
	}
	dump( $result, 'result' );
}

function debugQuery( $db, $result = 'no result set specified' )
{
	if( !is_object($db) ) {
		echo 'no database given<br />'."\n";
	}
	else {
		echo 'query:<pre>'.$db->getQuery().'</pre>'."\n";
		echo 'error: '.$db->getErrorMsg().'<br />'."\n";
		echo 'info: '.mysql_info().', affected: '.$db->getAffectedRows().'<br />'."\n";
	}
	echo 'result: ';var_dump_pre($result);
}
// #############  END of Dev tools  ############


?>