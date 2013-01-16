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
class ApotheosisLibCycles
{
	// #############  Days and cycles  ############
	
	/**
	 * Gets all the info for the current period into an object
	 *
	 * @return string  The id of the current day section within the current pattern
	 */
	function getCurrentPeriod()
	{
		$cycleDay = &ApotheosisLibCycles::dateToCycleday();
		$pattern = &ApotheosisLibCycles::getPatternByDate();
		$day_type = ( is_null($cycleDay) ? NULL : substr($pattern->format, $cycleDay, 1) );
		$time = date('H:i:s');
		$db = &JFactory::getDBO();
		$query = 'SELECT day_type, day_section, start_time, end_time'
			."\n".' FROM #__apoth_tt_daydetails'
			."\n".' WHERE '.ApotheosisLibDb::dateCheckSql('valid_from', 'valid_to', date('Y-m-d'), date('Y-m-d') )
			."\n".'   AND start_time < '.$db->Quote($time)
			."\n".'   AND end_time > '.$db->Quote($time)
			."\n".'   AND day_type = '.$db->Quote($day_type)
			."\n".' GROUP BY day_section'
			."\n".' ORDER BY day_type, start_time';
		
		$db->setQuery( $query );
		$period = $db->loadObject();
		
		return ( is_object($period) ? $period->day_section : null );
	}
	
	/**** NOT SURE THIS FUNCTION IS NEEDED, THINK IT HAS BECOME REDUNDANT
	/**
	 * Loads all possible periods
	 * *** (not the most efficient bit of code in the world, as this is only used by getCurrentPeriod
	 *      to give it a list of periods to search through for the current one. Could prob. just do a
	 *      WHERE clause based on start_time.)
	 */
	function _loadPeriods()
	{
		$day_section = new stdClass();
		$day_section->day_type = '';
		$day_section->day_section = '';
		$day_section->start_time = '';
		$day_sections[''] = $day_section;
		$day_sections = array_merge($day_sections, $db->loadObjectList('day_section'));
		//echo'day sections ';var_dump_pre($day_sections);
		$this->_periods = $day_sections;
	}
	
	/**
	 * Gets the pattern information most relevant to the supplied param
	 * 
	 * @param mixed $param  The search parameter
	 * @return object  The pattern information 
	 */
	function &getPattern($param = false)
	{
		$tmp = &ApotheosisLibCycles::getPatternByDate($param);
		return $tmp;
	}
	
	/**
	 * Gets the pattern information which is relevant to the supplied date
	 * 
	 * @param string $date  The date whose pattern is required
	 * @return object  The pattern information 
	 */
	function &getPatternByDate( $date = false )
	{
		if( $date === false ) {
			$date = date( 'Y-m-d' );
		}
		static $dateInfoCache = array();
		
		if( !array_key_exists($date, $dateInfoCache) ) {
			$db = &JFactory::getDBO();
			$query = 'SELECT *'
				."\n".'FROM #__apoth_tt_patterns'
				."\n".'WHERE `valid_from` <= "'.$date.'"'
				."\n".'  AND (`valid_to` >= "'.$date.'" OR `valid_to` IS NULL)'
				."\n".'ORDER BY `valid_from` DESC';
			$db->setQuery( $query );
			$dateInfoCache[$date] = $db->loadObject();
		}
		
		return $dateInfoCache[$date];
	}
	
	/**
	 * Gets the pattern instance which is relevant to the supplied date
	 * 
	 * @param string $date  The date whose pattern is required
	 * @return object  The pattern instance 
	 */
	function &getPatternInstanceByDate( $date = false )
	{
		if( $date === false ) {
			$date = date( 'Y-m-d' );
		}
		static $dateInfoCache = array();
		
		if( !array_key_exists($date, $dateInfoCache) ) {
			$db = &JFactory::getDBO();
			$query = 'SELECT *'
				."\n".'FROM #__apoth_tt_pattern_instances'
				."\n".'WHERE `start` <= "'.$date.'"'
				."\n".' AND (`end` >= "'.$date.'" OR `end` IS NULL)'
				."\n".' ORDER BY `start` DESC';
			$db->setQuery( $query );
			$dateInfoCache[$date] = $db->loadObject();
		}
		
		return $dateInfoCache[$date];
	}
	
	/**
	 * Gets the the pattern information which is relevant to the supplied date
	 * 
	 * @param string $id  The id of the pattern required
	 * @return object  The pattern information 
	 */
	function &getPatternById($id = false)
	{
		if ($id === false) {
			$id = 0;
			$whereStr = '';
		}
		else {
			$whereStr = "\n".' WHERE `id` = '.$id;
		}
		static $dateInfoCache = array();
		
		if (!array_key_exists($id, $dateInfoCache)) {
			$db = &JFactory::getDBO();
			$query = 'SELECT *'
				."\n".'FROM #__apoth_tt_patterns'
				.$whereStr
				."\n".' ORDER BY `id` DESC';
			$db->setQuery($query);
			$dateInfoCache[$id] = $db->loadObject();
		}
		return $dateInfoCache[$id];
	}
	
	/**
	 * Finds and returns the day type of a given day in the given pattern
	 * 
	 * @param int $day  The offset (starting at 0) in the pattern of the day being considered
	 * @param string $patternId  The id of the pattern being considered (if omitted, the pattern for the current date is used)
	 * @return string  The day type of that particular day
	 */
	function getDayType( $day, $patternId = false )
	{
		if ($patternId === false) {
			$dateInfo = &ApotheosisLibCycles::getPatternByDate();
		}
		else {
			$dateInfo = &ApotheosisLibCycles::getPatternById( $patternId );
		}
		return substr($dateInfo->format, $day, 1);
	}
	
	/**
	 * Finds and returns all offsets where the given day type is found in the pattern
	 *
	 * @param string $dayType  The day type to be searched for
	 * @param string $patternId  The id of the pattern being considered ( if omitted, the pattern for the current date is used)
	 * @return array  All the offsets (starting at 0) where the day type is found
	 */
	function getDayIndex( $dayType, $patternId = false )
	{
		if ($patternId === false) {
			$info = &ApotheosisLibCycles::getPatternByDate();
		}
		else {
			$info = &ApotheosisLibCycles::getPatternById( $patternId );
		}
		$matches = array();
		preg_match_all( '~'.preg_quote($dayType).'~', $info->format, $matches, PREG_OFFSET_CAPTURE );
		
		$retVal = array();
		$match = @reset($matches);
		if( !empty($matches) && !empty($match) ) {
			foreach( $match as $v ) {
				$retVal[] = $v[1];
			}
		}
		return $retVal;
	}
		
	/**
	 * Finds and returns the day type of a given date
	 *
	 * @param string $date  The date to be converted
	 * @return string  The day type of that particular date
	 */
	function dateToDayType( $date = false )
	{
		if ($date === false) {
			$date = date('Y-m-d');
		}
		static $dateDayTypeCache = array();
		
		if (!array_key_exists($date, $dateDayTypeCache)) {
			$dateInfo = &ApotheosisLibCycles::getPatternByDate( $date );
			$cycleDay = ApotheosisLibCycles::dateToCycleDay( $date );
			$dateDayTypeCache[$date] = ( is_null($cycleDay) ? NULL : substr($dateInfo->format, $cycleDay, 1) );
		}
		return $dateDayTypeCache[$date];
	}
	
	/**
	 * A simple lookup of ISO day numbers to names (english)
	 * 
	 * @param dayNum integer  The ISO standard day number to convert into a day name
	 */
	function getDayName( $dayNum )
	{
		$days = array(1 => 'Monday', 2 => 'Tuesday', 3 => 'Wednesday', 4 => 'Thursday', 5 => 'Friday', 6 => 'Saturday', 7 => 'Sunday', );
		return ($days[$dayNum]);
	}
	
	/**
	 * Gets the number of the day in the school timetable pattern we are on
	 * 
	 * @param string $date  A textual representation of the date to convert
	 * @return int  The index in the pattern of the given date or NULL if no date found
	 */
	function dateToCycleDay( $date = false, $pattern = null, $instance = null )
	{
		if( $date === false ) {
			$date = date('Y-m-d');
		}
		if( is_null($pattern) ) {
			$pattern = &ApotheosisLibCycles::getPatternByDate($date);
		}
		if( is_null($instance) ) {
			$instance = &ApotheosisLibCycles::getPatternInstanceByDate($date);
		}
		
		if( empty($pattern) || empty($instance) ) {
			$cycleDay = NULL;
		}
		else {
			// Converting the date to a Julian Day
			$dateParts1 = explode('-', reset(explode(' ', $instance->start)));
			$date_2 = gregoriantojd($dateParts1[1], $dateParts1[2], $dateParts1[0]);
			$dateParts2 = explode('-', reset(explode(' ', $date)));
			$date_1 = gregoriantojd($dateParts2[1], $dateParts2[2], $dateParts2[0]);
			
			// Calculate the difference in the amount of days
			$diff = $date_1 - $date_2;
			
			// Correct the day count, depending on the offset.
			// Calculate the total amount of days difference, with offset included
			$preResult = $diff + $instance->start_index;
			
			// Calculate the modulos of the result, depending on the number of days in the pattern
			$cycleDay = $preResult % strlen($pattern->format);
		}
		
		return $cycleDay;
	}
	
	/**
	 * Converts a cycle day into an ISO day (name or number if $format is 'text' or 'numeric')
	 * 
	 * @param int $cycleDay  The day in the cycle (starting at 0) to be converted
	 * @param int $patternId  The pattern id to be used for the conversion
	 * @param string $format  The expected return format (string or numeric)
	 * @return mixed  A numeric or string representation of the day of the week in the pattern given
	 */
	function cycleDayToDOW($cycleDay, $patternId = false, $format = 'text')
	{
		if ($patternId === false) {
			$dateInfo = &ApotheosisLibCycles::getPatternByDate();
		}
		else {
			$dateInfo = &ApotheosisLibCycles::getPatternById( $patternId );
		}
		$pattern = $dateInfo->format;
		//echo'the cycle day is: '.var_dump_pre($cycleDay, true);
		//echo'the pattern is: '.var_dump_pre($dateInfo, true);
		//echo'<br />';
		
		$dayNum = ($cycleDay + $dateInfo->start_day) % 7; // the iso day number we need
		$start_dow = (($dateInfo->start_day == 7) ? 0 : $dateInfo->start_day);
		$weekNum = ceil(($cycleDay + $start_dow) / 7); // the week number we're on
		
		if($format == 'text') {
			return ApotheosisLibCycles::getDayName($dayNum).' W'.$weekNum;
		}
		elseif($format == 'numeric') {
			return $dayNum;
		}
	}
	
	/**
	 * TODO (if we ever need it)
	 * Converts a numerical ISO standard day of the week (1=monday, 7=sunday) into a cycle day
	 * of the most recent occurence of that day
	 *
	 * @param int $day  The ISO standard day number to convert
	 */
	function dowToCycleDay($day)
	{
		
	}
	
	/**
	 * Converts a day in the pattern (index in the pattern string) into
	 * the date of the most recent occurence of that day
	 * 
	 * @param int $cycleDay  The offset of the cycle day under consideration
	 * @param int $patterhId  The id of the pattern under consideration
	 * @return string  The most recent date of that cycle day in the pattern
	 */
	function cycleDayToDate($cycleDay, $patternId = false)
	{
		$dateInfo = &ApotheosisLibCycles::getPattern();
		$curCycleDay = ApotheosisLibCycles::dateToCycleday();
		
		$diff = $curCycleDay - $cycleDay;
		$diff = (($diff < 0) ? $diff + strlen($dateInfo->format) : $diff);
		
		$date = date('Y-m-d', jdtounix(unixtojd() - $diff) );
		return $date;
	}
	
	/**
	 * Converts a cycle day number into a single-character day type
	 * @param int $daynum  The cycle day number
	 * @return int|string  The day type character
	 */
	function cycleDayToDayType( $daynum )
	{
		if ($daynum < 0) {
			return '.';
		}
		elseif ($daynum < 10) {
			return $daynum;
		}
		elseif ($daynum < 37) {
			return chr(54 + $daynum); // to get uppercase letters (65 -> 90)
		}
		elseif ($daynum < 63) {
			return chr(60 + $daynum); // to get lowercase letters (97 -> 122)
		}
		else {
			return '.';
		}
	}
	
	/**
	 * Finds the day section within the pattern occuring on date
	 *
	 * @param string $date  The date of the day section to be retrieved
	 * @param string $daySection  The id of the day section to be retrieved
	 * @return object  The day section details
	 */
	function getSectionByDate($date, $daySection)
	{
		static $dayDetailCache = array();
		
		if (!array_key_exists($date, $dayDetailCache)) {
			$pattern = &ApotheosisLibCycles::getPatternByDate($date);
			$dayType = ApotheosisLibCycles::dateToDayType($date);
			$db = JFactory::getDBO();
			$db->setQuery('SELECT pattern, day_type, day_section, start_time'
				."\n".' FROM #__apoth_tt_daydetails'
				."\n".' WHERE pattern = "'.$pattern->id.'"'
				."\n".' AND day_type = "'.$dayType.'"');
			$dayDetailCache[$date] = $db->loadObjectList('day_section');
		}
		return $dayDetailCache[$date][$daySection];
	}
}
?>