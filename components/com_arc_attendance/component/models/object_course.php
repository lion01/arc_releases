<?php
/**
 * @package     Arc
 * @subpackage  Attendance
 * @copyright   Copyright (C) 2005 Punnet. All rights reserved. See COPYRIGHT_ARC.txt
 * @license     http://www.gnu.org/licenses/gpl.html GNU/GPL. See LICENSE_ARC.txt
 * Arc is free software: you can redistribute it and/or modify
 * it under the terms of version 3 of the GNU General Public License
 * as published by the Free Software Foundation.
 * The disclaimer of warranty as stated in the GPL applies to this program
 */

defined( '_JEXEC' ) or die( 'Restricted access' );

/**
 * Course object
 *
 * A single course is modeled by this class
 *
 * @author		lightinthedark <code@lightinthedark.org.uk>
 * @package		Apotheosis
 * @subpackage	Attendance
 * @since 0.1
 */
class Course extends JObject
{
	/* The various properties of a course object */
	var $id;
	var $type;
	var $shortname;
	var $fullname;
	var $description;
	var $parent;
	var $sortorder;
	var $start_date;
	var $end_date;
	var $num;
	
	var $_compSep;
	var $_dates = array();
	var $_times = array();
	
	/**
	 * Retrieves a course object for all each course that matches the requirements
	 *
	 * @static
	 * @access public
	 */
	function getCourses( $requirements )
	{
		$flag = 0;
		$db = &JFactory::getDBO();
		$whereStrs = array();
		$innerStrs = array();
		if(!isset($requirements['end_date'])) {
			$requirements['end_date'] = date('Y-m-d');
		}
		if(!isset($requirements['start_date'])) {
			$requirements['start_date'] = date('Y-m-d', jdtounix( unixtojd(strtotime($requirements['end_date'])) - 13 ));
		}
		
		// construct common where strings
		foreach ($requirements as $col=>$val) {
			if(($val != NULL) || ($val != '')) {
				if(is_array($val)) {
					foreach($val as $k=>$v) {
						$val[$k] = $db->quote($v);
					}
					$valStr = implode(', ', $val);
					$isArray = 1;
				}
				else {
					$val = $db->quote($val);
					$isArray = 0;
				}
				switch($col) {
				case('date'):
					$day = ApotheosisLibCycles::dateToCycleday( $requirements[$col] );
					if( !is_null($day) ) {
						$whereStrs[] = '`tt`.`day` = '.$day.'';
					}
					break;
				
				case('start_date'):
				case('end_date'):
					if($flag == 0) {
						$df = $requirements['start_date'];
						$dt = $requirements['end_date'];
						$whereStrs[] = ApotheosisLibDb::dateCheckSql( '`tt`.`valid_from`', '`tt`.`valid_to`', $df, $dt );
						$flag = 1;
					}
					break;
				
				case('day'):
				case('day_section'):
					$whereStrs[] = '`tt`.`'.$col.'` = '.$val.'';
					break;
				
				case('room'):
					$whereStrs[] = '`tt`.`room_id` = '.$val.'';
					break;
				
				case('teacher'):
					$innerStrs['gmt'] = 'INNER JOIN #__apoth_tt_group_members AS gmt ON c.id = gmt.group_id';
					$whereStrs['teacher'] = '`gmt`.`is_teacher` = 1 AND '.ApotheosisLibDb::dateCheckSql( '`gmt`.`valid_from`', '`gmt`.`valid_to`', $requirements['start_date'], $requirements['end_date'] ); // *** titikaka
					$whereStrs[] = '`gmt`.`person_id`'.(($isArray == 0) ? ' = '.$val : ' IN ('.$val.')');
					break;
				
				case('pupil'):
					$innerStrs['gmp'] = 'INNER JOIN #__apoth_tt_group_members AS gmp ON c.id = gmp.group_id';
					$whereStrs['pupil'] = '`gmp`.`is_student` = 1 AND '.ApotheosisLibDb::dateCheckSql( '`gmp`.`valid_from`', '`gmp`.`valid_to`', $requirements['start_date'], $requirements['end_date'] ); // *** titikaka
					$whereStrs[] = '`gmp`.`person_id`'.(($isArray == 0) ? ' = '.$val : ' IN ('.$val.')');
					break;
				
				case('user'):
					$innerStrs['gm'] = 'INNER JOIN #__apoth_tt_group_members AS gm ON c.id = gm.group_id';
					$innerStrs['ppl'] = 'INNER JOIN #__apoth_ppl_people AS ppl ON gm.person_id = ppl.id';
					$whereStrs['teacher'] = '`gm`.`is_teacher` = 1 AND '.ApotheosisLibDb::dateCheckSql( '`gm`.`valid_from`', '`gm`.`valid_to`', $requirements['start_date'], $requirements['end_date'] ); // *** titikaka
					$whereStrs[] = '`ppl`.`juserid` = '.$val;
					break;
				
				case('pastoral_class'):
					$innerStrs[] = 'INNER JOIN `#__apoth_cm_pastoral_map` AS pm ON `pm`.`course` = `c`.`id`';
					$whereStrs[] = '`pm`.`pastoral_course` = '.$val;
					break;
				
				case('id'):
				case('course'):
				case('class'):
				case('normal_class'):
					$whereStrs[] = '`c`.`id` = '.$val;
					break;
				
				case('mark'):
					$innerStrs['mark'] = 'INNER JOIN `#__apoth_att_dailyatt` AS da ON `da`.`course_id` = `c`.`id`';
					$whereStrs[] = '`da`.`att_code` = '.$val;
					break;
				}
			}
		}
		
		$whereStr = ((count($whereStrs) > 0) ? 'WHERE '.implode(' AND ', $whereStrs) : '');
		$innerStr = ((count($innerStrs) > 0) ? implode(' ', $innerStrs) : '');
		
		$query = 'SELECT DISTINCT tt.pattern, tt.day, tt.day_section, tt.room_id, c.*'
			."\n".'FROM #__apoth_tt_timetable AS tt'
			."\n".'INNER JOIN #__apoth_cm_courses AS c'
			."\n".'   ON tt.course = c.id'
			."\n".'  AND c.deleted = 0'
			."\n".'INNER JOIN #__apoth_tt_patterns AS tp'
			."\n".'   ON tp.id = tt.pattern'
			."\n".'  AND ( (tp.valid_from <= c.end_date) OR (c.end_date IS NULL) )'
			."\n".'  AND ( (tp.valid_to >= c.start_date) OR (tp.valid_to IS NULL) )'
			."\n".'~LIMITINGJOIN~'
			."\n".$innerStr
			."\n".$whereStr;
		$db->setQuery( ApotheosisLibAcl::limitQuery($query, 'timetable.groups', 'c') );
		$rawCourses = $db->loadAssocList();
		
		$date = $df;
		while( $date <= $dt ) {
			if(!is_null(ApotheosisLibCycles::getPatternByDate( $date ))) {
				$pattern = ApotheosisLibCycles::getPatternByDate( $date );
				$cycleDay = ApotheosisLibCycles::dateToCycleday( $date );
				
				$key = $pattern->id.'~'.$cycleDay;
				$dateDetails[$key]->date = $date;
				$dateDetails[$key]->pattern = $pattern;
				$dateDetails[$key]->cycleDay = $cycleDay;
			}
			$date = date('Y-m-d', jdtounix( unixtojd(strtotime($date)) + 1 ));
		}
		
		$courses = array();
		foreach($dateDetails as $details) {
			foreach ($rawCourses as $c) {
				$id = $c['id'];
				if(($c['pattern'] == $details->pattern->id) && ($c['day'] == $details->cycleDay)) {
					if (isset($courses[$id])) {
						$courses[$id]->addTime($details->date, $c['pattern'], $c['day'], $c['day_section']);
					}
					else {
						$c['date'] = $details->date;
						$courses[$id] = new Course( $c );
					}
				}
			}
		}
		
		return $courses;
	}
	
	
	/**
	 * Construct an individual course object from an associative array of property=>value pairs
	 */
	function __construct($info, $separator = '~*~')
	{
//		echo 'making course from: <pre>';var_dump($info);echo'</pre>';
		parent::__construct();
		$this->_compSep = $separator;
		
		if ( isset($info['pattern'])
			&& isset($info['date'])
			&& isset($info['day'])
			&& isset($info['day_section']) ) {
			$this->addTime($info['date'], $info['pattern'], $info['day'], $info['day_section']);
			unset($info['pattern']);
			unset($info['day']);
			unset($info['day_section']);
		}
		
		$attribs = array_keys(get_class_vars('Course'));
		foreach ($info as $k=>$v) {
			if (array_search($k, $attribs) !== false) {
				$this->$k = $v;
			}
		}
	}
	
	/**
	 * Adds a timetable slot to this course's list of occurences
	 */
	function addTime($date, $pattern, $day, $section)
	{
		$dayType = ApotheosisLibCycles::getDayType($day, $pattern);
		$db = &JFactory::getDBO();
		$query = 'SELECT `start_time`, `end_time`, `day_section_short`, `statutory`'
			."\n".' FROM #__apoth_tt_daydetails'
			."\n".' WHERE `pattern` = "'.$pattern.'"'
			."\n".' AND `day_type` = "'.$dayType.'"'
			."\n".' AND `day_section` = "'.$section.'"';
		$db->setQuery($query);
		$result = $db->loadObject();
		
		$this->_times[$pattern][$day][$section] = array('date'=>$date, 'time'=>$result->start_time, 'start_time'=>$result->start_time, 'end_time'=>$result->end_time, 'statutory'=>$result->statutory);
		$this->_dates[$date][$result->start_time] = array('pattern'=>$pattern, 'day'=>$day, 'day_section'=>$section, 'day_section_short'=>$result->day_section_short);
		$this->most_recent_date = $this->getMostRecentDate();
		$this->most_recent_time = $this->getMostRecentTime($this->most_recent_date);
	}
	
	/**
	 * Returns the date and time of the most recent occurence of this course
	 *
	 * @return array  Associative array with 'date'=>most recent date, 'time'=>most recent time
	 */
	function getMostRecent()
	{
		$mrd = $this->getMostRecentDate();
		return array('date'=>$mrd, 'time'=>$this->getMostRecentTime($mrd));
	}
	
	/**
	 * Returns the date of the most recent occurence of this course
	 */
	function getMostRecentDate()
	{
		ksort( $this->_dates );
		$keys = array_keys( $this->_dates );
		return array_pop( $keys );
	}
	
	/**
	 * Returns the date of the most recent occurence of this course on the given date
	 */
	function getMostRecentTime($date = false)
	{
		if ($date === false) {
			$date = $this->getMostRecentDate();
		}
		$times = $this->_dates[$date];
		
		ksort( $times );
		$keys = array_keys( $times );
		return array_pop( $keys );
	}
	
	/**
	 * Returns an indexed array of all the most recent dates upon which this course occurs
	 */
	function getDates()
	{
		return array_keys( $this->_dates );
	}
	
	/**
	 * Retrieves an array of all the times of periods when this course takes place on the given date
	 */
	function getTimes($date)
	{
		return array_keys( $this->_dates[$date] );
	}
	
	/**
	 * Retrieves an array of details of pattern, day, etc for the timetable slot defined by the given date and time
	 */
	function getTtSlot($date, $time)
	{
		return $this->_dates[$date][$time];
	}
	
	
	function getTimeDetails($pattern, $day, $daySection)
	{
		return $this->_times[$pattern][$day][$daySection];
	}
}
?>
