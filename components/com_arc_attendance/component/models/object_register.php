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
 * Register object
 *
 * A single register is modeled by this class
 * Very useful for avoiding repeatedly searching for a register by date, time, course
 *
 * @author		lightinthedark <code@lightinthedark.org.uk>
 * @package		Apotheosis
 * @subpackage	Attendance
 * @since 0.1
 */
class Register extends JObject
{
	var $date;
	var $time;
	var $course;
	var $location;
	var $pattern;
	var $day;
	var $daySection;
	
	/** @var array of attendance marks indexed by person id */
	var $_marks;
	var $_marksRecent;
	var $_pupils;
	var $_adhocPupils;
	var $_otherPupils;
	
	/** @var datetime of last moment the marks /pupils in this object were updated from the database */
	var $_updateTime;
	
	var $_compId;
	var $_compSep;
	
	/**
	 * Creates a register object. A register is fundamentally a course on a date at a time
	 * 
	 * @param array $info  Associative array of defining properties. Must contain date, time and either course or location
	 * @param string $separator  String separator to use internally when creating composite ids. Must not appear in any of the register attributes (default: "~*~")
	 */
	function __construct($course, $date, $time, $separator = '~*~')
	{
		parent::__construct();
		
		$details = $course->getTtSlot($date, $time);
		$this->pattern = $details['pattern'];
		$this->day = $details['day'];
		$this->daySection = $details['day_section'];
		$this->daySectionShort = $details['day_section_short'];
		$timeDetails = $course->getTimeDetails( $this->pattern, $this->day, $this->daySection );
		$this->startTime = $timeDetails['start_time'];
		$this->endTime = $timeDetails['end_time'];
		$this->statutory = $timeDetails['statutory'];
		
		// get the location (and maybe teacher) of this register)
		$db = &JFactory::getDBO();
		$query = 'SELECT `room_id`'
			."\n".' FROM #__apoth_tt_timetable'
			."\n".' WHERE `pattern` = '.$db->Quote($this->pattern)
			."\n".' AND `course` = '.$db->Quote($course->id)
			."\n".' AND `day` = '.$db->Quote($this->day)
			."\n".' AND `day_section` = '.$db->Quote($this->daySection)
			."\n".' AND '.ApotheosisLibDb::dateCheckSQL( 'valid_from', 'valid_to', $timeDetails['date'], $timeDetails['date'] );
		$db->setQuery($query);
		$info = $db->loadObject();
		
		$query = 'SELECT p.id, p.title, COALESCE( p.preferred_firstname, p.firstname ) AS firstname, COALESCE( p.preferred_surname, p.surname ) AS surname'
			."\n".' FROM #__apoth_tt_group_members AS gm'
			."\n".' INNER JOIN #__apoth_ppl_people AS p'
			."\n".'    ON p.id = gm.person_id'
			."\n".' WHERE gm.group_id = '.$db->Quote($course->id)
			."\n".'   AND '.ApotheosisLibDb::dateCheckSql( 'gm.valid_from', 'gm.valid_to', $timeDetails['date'], $timeDetails['date'] )
			."\n".'   AND gm.is_teacher = 1' // *** titikaka
			."\n".' ORDER BY p.surname, p.firstname';
		$db->setQuery($query);
		$this->teachers = $db->loadObjectList( 'id' );
		foreach( $this->teachers as $id=>$t ) {
			$this->teachers[$id]->displayname = ApotheosisLib::nameCase('teacher', $t->title, $t->firstname, '', $t->surname);
		}
		
		$this->date = $date;
		$this->time = $time;
		$this->course = &$course;
		$this->location = $info->room_id;
		$this->shortname = &$course->shortname;
		
		$this->_compSep = $separator;
		$this->_compId = $this->getCompId();
	}
	
	/**
	 * Retrieves the marks recorded for this register
	 *
	 * @return array  All the mark strings received by pupils on this register in an array indexed by pupil id
	 */
	function getMarks()
	{
		if (empty($this->_marks) ||
			($this->_compId != ($this->_compId = $this->getCompId())) ) {
			$this->_loadMarks();
		}
		return $this->_marks;
	}
	
	/**
	 * Loads all the marks recorded for this register
	 */
	function _loadMarks()
	{
		$db = &JFactory::getDBO();
		
		$db->setQuery('SELECT person_id, att_code FROM `#__apoth_att_dailyatt`'
			."\n".' WHERE date = "'.$this->date.'"'
			."\n".' AND day_section = "'.$this->daySection.'"'
			."\n".' AND course_id = "'.$this->course->id.'"');
		$this->_marks = $db->loadObjectList('person_id');
		$this->_updateTime = date('Y-m-d H:i:s');
		
		// de-object-ify the mark data
		foreach ($this->_marks as $k=>$v) {
			$this->_marks[$k] = $v->att_code;
		}
	}
	
	/**
	 * Sets the marks for this register, saving them to the database if required
	 *
	 * @param array $marks  Attendance marks array indexed by person id
	 * @param boolean $save  Whether to save to the db or not. Defaults to true.
	 */
	function setMarks($marks, $save = true, $log = false)
	{
//		var_dump_pre($this->_marksRecent);
		//set up the return arrays
		$insertArr = array();
		$updateArr = array();
		$errorArr = array();
/* Debug lines
			$dUser = &JFactory::getUser();
			if( $dUser->id == 63 ) {
echo 'marks: '.var_dump_pre($marks, true);
echo'update time: '.var_dump_pre($this->_updateTime, true);
echo'this: '.var_dump_pre($this, true);
//die();
			}
// */
		if (is_null($marks)) {
			if( $log ) { echo ', start but null marks, so end'; }
			return false;
		}
		
		$this->_marks = $marks;
		
		if ($save) {
			if( $log ) {
				echo ' start';
			}
			
			$syncher = AttSynch::getInstance();
			$updateCount = 0;
			$db = &JFactory::getDBO();
			$db->setQuery('SELECT *, CONCAT(`code`, " ", `type`) AS `key` FROM `#__apoth_att_codes` WHERE `apply_all_day` = 1');
			$allDayCodes = $db->loadObjectList('key');
			$lockStartStr = 'SET AUTOCOMMIT = 0;';
			$lockEndStr   = 'COMMIT;';
			$lockEndAuto  = ' SET AUTOCOMMIT = 1;';
			$selectStr = 'SELECT `person_id`, `att_code`, `last_modified` FROM `#__apoth_att_dailyatt`'
				."\n".' WHERE `date` = "'.$this->date.'"'
				."\n".' AND `day_section` = "'.$this->daySection.'"'
				."\n".' AND `course_id` = "'.$this->course->id.'"'
				."\n".' FOR UPDATE;';
			$db->setQuery($lockStartStr);
			$db->query();
			$db->setQuery($selectStr);
			$stateBefore = $db->loadObjectList( 'person_id' );
			
			// loop through the marks and update or add new attendance marks
			foreach ($marks as $personId=>$mark) {
				if (empty($stateBefore[$personId])) {
					$db->setQuery('INSERT INTO `#__apoth_att_dailyatt`'
						."\n".' (`date`, `person_id`, `course_id`, `day_section`, `att_code`, `last_modified`)'
						."\n".' VALUES ('.$db->quote($this->date).', '.$db->quote($personId).', '.$db->quote($this->course->id).', '.$db->quote($this->daySection).', '.$db->quote($db->getEscaped($mark)).', '.$db->quote(date('Y-m-d H:i:s')).')');
					//debugQuery($db);
					$db->query();
					$syncherMarks[$personId] = $mark;
				}
				elseif (($stateBefore[$personId]->last_modified > $this->_updateTime) && ($stateBefore[$personId]->att_code != $mark)) {
					//write out the course, time, pupil and mark to a log file
					$logStr = "\t".' mark-error: for - '.$this->course->id.' - last modified: '.$stateBefore[$personId]->last_modified.' - Time of new mark: '.$this->_updateTime.' - '.$personId.' - '.$db->getEscaped($mark)."\n";
				}
				elseif ($stateBefore[$personId]->att_code != $mark) {
					$db->setQuery('UPDATE `#__apoth_att_dailyatt`'
						."\n".' SET `att_code` = '.$db->quote($db->getEscaped($mark)).','
						."\n".' `last_modified` = '.$db->quote(date('Y-m-d H:i:s'))
						."\n".' WHERE `date` = '.$db->quote($this->date)
						."\n".' AND `day_section` = '.$db->quote($this->daySection)
						."\n".' AND `person_id` = '.$db->quote($personId)
						."\n".' AND `course_id` = '.$db->quote($this->course->id) );
					$syncherMarks[$personId] = $mark;
					$updateCount++;
					//debugQuery($db);
					$db->query();
				}
				// otherwise the old and new marks are the same so don't need updating
				if( isset($allDayCodes[$mark.' '.$this->course->type]) ) {
					$allDayMark = $allDayCodes[$mark.' '.$this->course->type];
					$futureArr  = $this->inputFutureAbsenceMark($this->date, $this->day, $this->pattern, $this->daySection, $personId, $allDayMark );
				}
				// *** update external DB regardless of change. (useful for recovering from errors)
				// $syncherMarks[$personId] = $mark;
			}
			
			// read in everything that we just wrote, and see if it's what we were expecting
			$db->setQuery( 'SELECT `person_id`, `att_code`, `last_modified` FROM `#__apoth_att_dailyatt`'
				."\n".' WHERE `date` = '.$db->quote($this->date)
				."\n".' AND `day_section` = '.$db->quote($this->daySection)
				."\n".' AND `course_id` = '.$db->quote($this->course->id) );
			
			$stateAfter = $db->loadObjectList( 'person_id' );
			
			if(($stateAfter === false) || ($stateAfter === NULL)) {
				if( $log ) {
					echo ', error 2';
					echo $logStr;
				}
				return false;
			}
			else {			
				foreach($marks as $personId=>$mark) {
					
					if( $stateAfter[$personId]->att_code != $mark ) {
						$errorArr[$personId] = $marks[$personId];
					}
					elseif( ($stateBefore[$personId]->att_code != $stateAfter[$personId]->att_code) && ($stateAfter[$personId]->att_code == $mark) && (!empty($stateBefore))) {
						$updateArr[$personId] = $marks[$personId];
					}
					elseif( $stateBefore[$personId]->att_code == $mark) {
						//we want to catch marks that don't need updating, inserting or erroring
					}
					else {
						$insertArr[$personId] = $marks[$personId];
					}
					
				}
			}
			
			$db->setQuery( $lockEndStr );
			$db->query();
			$db->setQuery( $lockEndAuto );
			$db->query();
			
			/* disabled until we investigate writing to external applications
			if(!is_null($syncherMarks)) {
				$syncher->setMarks($syncherMarks, $this);
			}
			*/
			
			//setting up the final array to return
			$returnArr->insertArr = $insertArr;
			$returnArr->updateArr = $updateArr;
			$returnArr->errorArr = $errorArr;
			if( isset($futureArr) ) {
				$returnArr->futureArr = $futureArr;
			}
			
			// *** logging
			if( $log ) {
				echo ', success';
				echo $logStr;
			}
			
			$this->_loadMarks();
			$this->_loadMarksRecent();
			$this->_loadIncidents();
			
			return $returnArr;
		}
	}
	
	//input marks for the day the pupil has had an allday mark applied to AM reg
	function inputFutureAbsenceMark($date, $day, $pattern, $day_section, $pupilId, $mark )
	{
		$db = &JFactory::getDBO();
		$syncher = AttSynch::getInstance();
		
		//setup return array
		$returnArr = array();
		$insertArr = array();
		$updateArr = array();
		
		//get the pupil's timetable
		$timetable = $this->getDayTimetable($day, $pattern, $pupilId);
		
		//get any previously recorded marks for that pupilId
		$db->setQuery('SELECT `da`.* FROM `#__apoth_att_dailyatt` AS da'
						."\n".' INNER JOIN `#__apoth_tt_daydetails` AS dd ON `da`.`day_section` = `dd`.`day_section`'
						."\n".' WHERE `da`.`person_id` = '.$db->quote($pupilId)
						."\n".' AND `da`.`date` = '.$db->quote($date)
						."\n".' ORDER BY `dd`.`start_time`');
		
		$previousMarks = $db->loadObjectList('day_section');
		
		//get all the special marks
		$marksResults = $this->getSimilarMarks($mark->physical_meaning);
		foreach($marksResults as $k=>$v) {
			$marks[$v->type][$v->code] = $v;
		}
		
		//trim the array of day_sections to apply the mark to
		reset($timetable);
		
		while( (($tmp = current(array_keys($timetable))) != $day_section) && ($tmp !== false) ){
			unset($timetable[$tmp]);
			next($timetable);
		}
		if( !is_array($timetable) ) { $timetable = array(); }
		
		//go through and find the most appropriate mark to set for each type of period
		$type = $mark->type;
		foreach($timetable as $k=>$period) {
			if($period->type == $type) {
				$markToBeApplied[$k]->mark = $mark->code;
				$markToBeApplied[$k]->course = $period->course;
				$markToBeApplied[$k]->periodType = $period->type;
			}
			else {
				$markToBeApplied[$k]->mark = current(array_keys($marks[$period->type]));
				$markToBeApplied[$k]->course = $period->course;
				$markToBeApplied[$k]->periodType = 'normal';
			}
		}
		
		//set the marks for each period
		foreach($markToBeApplied as $period=>$details) {
			if(array_key_exists($period, $previousMarks)) {
				if($details->mark != $previousMarks[$period]->att_code) {
					$db->setQuery('UPDATE `#__apoth_att_dailyatt` SET `att_code` = '.$db->quote($db->getEscaped($details->mark)).', '
									."\n".' `last_modified` = '.$db->quote(date('Y-m-d H:i:s'))
									."\n".' WHERE `date` = '.$db->quote($date)
									."\n".' AND `day_section` = '.$db->quote($period)
									."\n".' AND `person_id` = '.$db->quote($pupilId)
									."\n".' AND `course_id` = '.$db->quote($details->course));
					$db->query();
					$updateArr[$pupilId] = $date;
				}
			}
			else {
					$db->setQuery('INSERT INTO `#__apoth_att_dailyatt`'
						."\n".' (`date`, `person_id`, `course_id`, `day_section`, `att_code`, `last_modified`)'
						."\n".' VALUES ('.$db->quote($date).', '.$db->quote($pupilId).', '.$db->quote($details->course).', '.$db->quote($period).', '.$db->quote($db->getEscaped($details->mark)).', '.$db->quote(date('Y-m-d H:i:s')).')');
					$db->query();
					$insertArr[$pupilId] = $date;
			}
/*
			//adding tutor marks to the external application if/when we do this
			if($details->periodType == 'pastoral') {
				$syncherMarks[$pupilId] = $mark->code;
				$reg->date = $date;
				$reg->pattern = $pattern;
				$reg->daySection = $period;
				$syncher->setMarks($syncherMarks, $reg);
			}
*/
		}
		
		$returnArr['insertArr'] = $insertArr;
		$returnArr['updateArr'] = $updateArr;
		
		return $returnArr;
	}
	
	function getSimilarMarks($physical_meaning)
	{
		$db = &JFactory::getDBO();
		
		$db->setQuery('SELECT * FROM `#__apoth_att_codes` WHERE `physical_meaning` = '.$db->quote($physical_meaning).' ORDER BY `order_id`');
		
		return $db->loadObjectList();
	}
	
	//get the timetable for a person on a specific day
	function getDayTimetable($cycleDay, $pattern, $pupilId)
	{
		$db = &JFactory::getDBO();
		
		$query = 'SELECT `tt`.`course`, `cm`.`type`, `dd`.`day_section`, `dd`.`start_time`'
				."\n".'FROM `#__apoth_tt_timetable` AS tt'
				."\n".'INNER JOIN `#__apoth_tt_group_members` AS gm'
				."\n".'   ON `tt`.`course` = `gm`.`group_id`'
				."\n".'  AND `gm`.`is_student` = 1' // *** titikaka
				."\n".'  AND '.ApotheosisLibDb::dateCheckSql( 'gm.valid_from', 'gm.valid_to', date('Y-m-d'), date('Y-m-d'))
				."\n".'INNER JOIN `#__apoth_ppl_people` AS p'
				."\n".'   ON `p`.`id` = `gm`.`person_id`'
				."\n".'INNER JOIN `#__apoth_tt_daydetails` AS dd'
				."\n".'   ON `tt`.`pattern` = `dd`.`pattern`'
				."\n".'  AND `tt`.`day_section` = `dd`.`day_section`'
				."\n".'INNER JOIN `#__apoth_cm_courses` AS cm'
				."\n".'   ON `gm`.`group_id` = `cm`.`id`'
				."\n".'  AND `cm`.`deleted` = 0'
				."\n".'~LIMITINGJOIN~'
				."\n".'WHERE `tt`.`pattern` = '.$db->quote($pattern)
				."\n".'  AND `tt`.`day` = '.$db->quote($cycleDay)
				."\n".'  AND `gm`.`person_id` = '.$db->quote($pupilId)
				."\n".'ORDER BY `dd`.`start_time`';
		$db->setQuery( ApotheosisLibAcl::limitQuery($query, 'timetable.groups', 'cm') );
		$r = $db->loadObjectList( 'day_section' );
		
		if( !is_array($r) ) { $r = array(); }
		return $r;
	}
	
	/**
	 * Retrieves the headings for the marks recorded for this register's pupils in previous periods / days
	 * 
	 * @return array  All the headings for previously recorded marks (in an indexed array)
	 */
	function getMarksRecentHeadings()
	{
		if(  empty($this->_marksRecentHeadings)
		 || ($this->_compId != ($this->_compId = $this->getCompId())) ) {
			$this->_loadMarksRecent();
		}
		return $this->_marksRecentHeadings;
		
	}
	
	/**
	 * Retrieves the headings for the marks recorded for this register's pupils in previous periods / days
	 * 
	 * @return array  All the headings for previously recorded marks (in an indexed array)
	 */
	function getMarksHistoryHeadings()
	{
		if (empty($this->_marksHistoryHeadings) ||
			($this->_compId != ($this->_compId = $this->getCompId())) ) {
//			$this->_loadMarksHistory();
		}
		return $this->_marksHistoryHeadings;
		
	}
	
	/**
	 * Retrieves the marks recorded for this register's pupils in previous periods / days
	 *
	 * @return array  All the mark strings received by pupils on this register in an array indexed by pupil id
	 */
	function getMarksRecent()
	{
		if (empty($this->_marksRecent) ||
			($this->_compId != ($this->_compId = $this->getCompId())) ) {
			$this->_loadMarksRecent();
		}
		return $this->_marksRecent;
		
	}
	
	/**
	 * Retrieves the marks recorded for this register's pupils in previous periods / days
	 *
	 * @return array  All the mark strings received by pupils on this register in an array indexed by pupil id
	 */
	function getMarksHistory()
	{
		if (empty($this->_marksHistory) ||
			($this->_compId != ($this->_compId = $this->getCompId())) ) {
//			$this->_loadMarksHistory();
		}
		return $this->_marksHistory;
		
	}
	
	/**
	 * Retrieves the incidents recorded for this register's pupils in previous periods / days
	 *
	 * @return array  All the incident strings received by pupils on this register in an array indexed by pupil id
	 */
	function getIncidents()
	{
//		if (empty($this->_incidents) ||
//			($this->_compId != ($this->_compId = $this->getCompId())) ) {
		if( ($this->__checky++ % 2) == 0) {
			$this->_loadIncidents();
		}
		
		return $this->_incidents;
	}
	
	function _loadIncidents()
	{
		$this->_incidents = array();
		$fMsg = ApothFactory::_( 'message.Message' );
		
		
		// # get day's prior messages
		$pupils = array_merge($this->getPupils(), $this->getAdhocPupils());
		$dayMsgs = $fMsg->getInstances( array('student_id'=>array_keys($pupils), 'date'=>$this->date, 'first'=>true) );
		foreach( $dayMsgs as $mId ) {
			$m = $fMsg->getInstance( $mId );
			$d = explode( ' ', $m->getDate() );
			$d = $d[0];
			$this->_incidents['day'][$m->getDatum( 'student_id' )][$d][$m->getDatum( 'group_id' )][$m->getId()] = $m;
		}
		
		
		// # get course's historical messages (for the preceding <pattern-length> days)
		$start = strtotime( $this->date.' '.$this->time );
		$l = 30;
		for( $i = 0; $i < $l; $i++ ) {
			$recentIncidentsDates[] = date('Y-m-d', strtotime('-'.$i.' days', $start) );
		}
		$courseMsgs = $fMsg->getInstances( array('student_id'=>array_keys($pupils), 'group_id'=>$this->course->id, 'date'=>$recentIncidentsDates, 'first'=>true) );

		foreach( $courseMsgs as $mId ) {
			$m = $fMsg->getInstance( $mId );
			$d = explode( ' ', $m->getDate() );
			$d = $d[0];
			$this->_incidents['course'][$m->getDatum( 'student_id' )][$d][$m->getDatum( 'group_id' )][$m->getId()] = $m;
		}
//		var_dump_pre( $this->_incidents, 'final incident list', 7 );
		
		return true;
	}
	
	/**
	 * Loads all the marks recorded for this register
	 */
//	function _loadMarksHistory()
//	{
//		$db = &JFactory::getDBO();
//		$pupils = array_merge($this->getPupils(), $this->getAdhocPupils());
//	}
	
	/**
	 * Loads all the marks recorded for this register
	 * **** we got part way through removing all the "...history" functions and using "...recent" instead
	 *  that work needs to be finished in the great cleanup.
	 */
	function _loadMarksRecent()
	{
		$db = &JFactory::getDBO();
		$pupils = array_merge($this->getPupils(), $this->getAdhocPupils());
		
		//get the length of recent marks to display
		$paramsObj = JComponentHelper::getParams('com_arc_attendance');
		$noOfHistoricalMarks = $paramsObj->get( 'no_of_historical_marks', 0 );
		
		//get the timetable for the current course
		$query = 'SELECT course, pattern, day, day_section, valid_from FROM #__apoth_tt_timetable'
				."\n".' WHERE course = '.$this->course->id
				."\n".' AND ((`valid_to` >= '.$db->quote($this->date).') OR (`valid_to` IS NULL))'
				."\n".' AND `valid_from` <= '.$db->quote($this->date);
//				."\n".' ORDER BY day DESC';
		$db->setQuery($query);
		$timetable = $db->loadObjectList();
		
		//get the pattern information
		$pattern = ApotheosisLibCycles::getPatternById($this->pattern);
		
		//get the current cycle_day for the register we are looking at
		$curCycleDay = ApotheosisLibCycles::dateToCycleDay($this->date);
		
		reset($timetable);
		$cycleDayArr = array();
		foreach($timetable as $k=>$v) {
			$cycleDayArr[$v->day_section] = $v->day;
		}
		//go to point in array
		$continue = true;
		while( ($continue !== false) && (key($cycleDayArr) != $this->daySection) ) {
			$continue = next($cycleDayArr);
		}
//echo 'Current Position';var_dump_pre(current($cycleDayArr));
//var_dump_pre(prev($cycleDayArr));
		
		//**** Need something to account across multiple patterns.....08y/sc2 doesn't show 14/02/2008 on class history
		// work out the dates that marks for this course should have been recorded on
		$noOfPatterns = 0;
		$recentMarksDates = array();
		if( current($cycleDayArr) === false ) { $noOfHistoricalMarks = 0; }
		while($noOfHistoricalMarks != 0) {
			if(prev($cycleDayArr) == false) {
				$noOfPatterns = $noOfPatterns + 1;
				end($cycleDayArr);
			}
			$dayDiff = current($cycleDayArr) - (strlen($pattern->format) * $noOfPatterns);
			$diff = $curCycleDay - $dayDiff;
			$recentMarksDates[] = date('Y-m-d', jdtounix( unixtojd(strtotime($this->date)) - $diff ));
			$noOfHistoricalMarks = $noOfHistoricalMarks - 1;	
		}
/* **** - commented out for commit so version in repo works
		echo 'cycledays';
		var_dump_pre($cycleDayArr);
		echo 'number of patterns';
		var_dump_pre($noOfPatterns);
		echo 'difference in days';
		var_dump_pre($dayDiff);
		echo 'recentMarksDates';
		var_dump_pre($recentMarksDates);
		die();
// */
		// Get the history for the class register
		$query = 'SELECT a.person_id, a.att_code, a.date, a.course_id AS group_id, d.start_time, CONCAT(a.date, " ", d.start_time) AS `index`, d.day_section_short AS heading, DATE_FORMAT(a.date, "%w") AS daynum, d.start_time'
			."\n".'FROM #__apoth_att_dailyatt AS a'
			."\n".'INNER JOIN #__apoth_tt_daydetails AS d'
			."\n".'   ON d.pattern = '.$this->pattern
			."\n".'  AND d.day_section = a.day_section'
			."\n".'INNER JOIN `#__apoth_tt_group_members` AS m'
			."\n".'   ON m.group_id = a.course_id'
			."\n".'  AND m.person_id = a.person_id'
			."\n".'  AND m.is_student = 1' // *** titikaka
			."\n".'INNER JOIN `#__apoth_cm_courses` AS c'
			."\n".'   ON c.id = a.course_id'
			."\n".'  AND c.deleted = 0'
			."\n".'WHERE'
			."\n".' a.course_id = '.$this->course->id
			.( empty( $pupils )           ? '' : "\n".' AND a.person_id IN ("'.implode( '", "', array_keys($pupils) ).'")' )
			.( empty( $recentMarksDates ) ? '' : "\n".' AND a.date IN ("'.implode('", "',$recentMarksDates).'")' )
			."\n".'ORDER BY a.person_id, a.date';
		
		$db->setQuery( $query );
//		echo $db->getQuery();
		$results = $db->loadObjectList();
		// de-object-ify the mark data
		$this->_marksCourse = array();
		$this->_marksCourseHeadings = array();
		foreach ($results as $k=>$v) {
			if ( !array_key_exists($v->index, $this->_marksCourseHeadings) ) {
				$this->_marksCourseHeadings[$v->date][$v->start_time] = $v->heading;
			}
			$this->_marksCourse[$v->person_id][$v->date][$v->start_time] = array( 'code'=>$v->att_code, 'group'=>$v->group_id );
		}
		
		// Get the day's marks for each pupil
		$dayType = ApotheosisLibCycles::getDayType( $this->day, $this->pattern );
			$query = 'SELECT a.person_id, a.att_code, a.date, a.course_id AS group_id, d.start_time, CONCAT(a.date, " ", d.start_time) AS `index`, d.day_section_short AS heading, -1 AS day_num, d.start_time'
				."\n".'FROM #__apoth_att_dailyatt AS a'
				."\n".'INNER JOIN #__apoth_tt_daydetails AS d'
				."\n".'	ON d.pattern = '.$this->pattern
				."\n".'	AND d.day_type = \''.$dayType.'\''
				."\n".'	AND d.day_section = a.day_section'
				."\n".'INNER JOIN `#__apoth_tt_group_members` AS m'
				."\n".'	ON m.group_id = a.course_id'
				."\n".'	AND m.person_id = a.person_id'
				."\n".'	AND m.is_student = 1' // *** titikaka
				."\n".'WHERE a.date = "'.$this->date.'"'
				."\n".' AND a.person_id IN ("'.implode( '", "', array_keys($pupils) ).'")'
				."\n".'ORDER BY a.person_id, a.date, d.start_time';
		$db->setQuery( $query );
		$results = $db->loadObjectList();
		// de-object-ify the mark data
		$this->_marksDay = array();
		$this->_marksDayHeadings = array();
		foreach ($results as $k=>$v) {
			if ( !array_key_exists($v->index, $this->_marksDayHeadings) ) {
				$this->_marksDayHeadings[$v->date][$v->start_time] = $v->heading;
			}
			$this->_marksDay[$v->person_id][$v->date][$v->start_time] = array( 'code'=>$v->att_code, 'group'=>$v->group_id );
		}
		$this->_marksRecent['course'] = $this->_marksCourse;
		$this->_marksRecent['day'] = $this->_marksDay;
		$this->_marksRecentHeadings['course'] = $this->_marksCourseHeadings;
		$this->_marksRecentHeadings['day'] = $this->_marksDayHeadings;
	}
	
	/**
	 * Retrieves the pupils enrolled in the session for which this register is to be taken
	 *
	 * @return array  The list of pupils indexed by pupil id
	 */
	function getPupils()
	{
		$date = $this->date;
		if (empty($this->_pupils)) {
			$this->_loadPupils('', $date);
		}
		return $this->_pupils;
	}
	
	/**
	 * Retrieves the pupils enrolled in the session for which this register is to be taken
	 * who have been given the specified mark
	 *
	 * @return array  The list of pupils indexed by pupil id
	 */
	function getPupilsByMark( $mark )
	{
		if (!isset($this->_pupils)) {
//			echo'working...<br />';
			$this->_loadMarks();
			$pupils = array_keys( $this->_marks, $mark );
			$this->_loadPupils( 'ppl.id IN ("'.implode('", "', $pupils).'")' );
		}
		
		return $this->_pupils;
	}
	
	/**
	 * Retrieves the pupils enrolled in the session for which this register is to be taken
	 * who have been given any of the specified marks
	 *
	 * @return array  The list of pupils indexed by pupil id
	 */
	function getPupilsByMarks( $marks )
	{
		if (!isset($this->_pupils)) {
			$this->_loadMarks();
			$pupils = array();
			foreach($this->_marks as $pId=>$mark) {
				if (array_search($mark, $marks) !== false) {
					$pupils[] = $pId;
				}
			}
			$this->_loadPupils( 'ppl.id IN ("'.implode('", "', $pupils).'")' );
		}
		return $this->_pupils;
	}
	
	/**
	 * Loads the pupils enrolled in the session for which this register is to be taken
	 */
	function _loadPupils( $whereStr = '', $date = false )
	{
		if($date == false) { $date = $this->date; }
		$db = &JFactory::getDBO();
		$query = 'SELECT DISTINCT ppl.id, COALESCE( ppl.preferred_firstname, ppl.firstname ) AS firstname, ppl.middlenames, COALESCE( ppl.preferred_surname, ppl.surname ) AS surname, c.shortname AS tutorgroup'
			."\n".'FROM #__apoth_ppl_people AS ppl'
			."\n".'INNER JOIN #__apoth_tt_group_members AS gm'
			."\n".'   ON gm.person_id = ppl.id'
			."\n".'INNER JOIN #__apoth_tt_group_members AS gmc'
			."\n".'   ON gmc.person_id = ppl.id'
			."\n".'INNER JOIN #__apoth_cm_courses AS c'
			."\n".'   ON c.id = gmc.group_id'
			."\n".'  AND c.type = "pastoral"'
			."\n".'  AND c.deleted = 0'
			."\n".'~LIMITINGJOIN~'
			."\n".'WHERE `gm`.`is_student` = 1' // *** titikaka
			."\n".'  AND `gmc`.`is_student` = 1' // *** titikaka
			."\n".'  AND `gm`.`group_id` = "'.$this->course->id.'"'
			.(($whereStr == '') ? '' : "\n".' AND '.$whereStr)
			."\n".'  AND '.ApotheosisLibDb::dateCheckSql('gm.valid_from',  'gm.valid_to',  $date, $date)
			."\n".'  AND '.ApotheosisLibDb::dateCheckSql('gmc.valid_from', 'gmc.valid_to', $date, $date)
			."\n".'GROUP BY ppl.id'
			."\n".'ORDER BY surname, firstname';
		
		$db->setQuery( ApotheosisLibAcl::limitQuery($query, 'people.people', 'ppl') );
		$this->_pupils = $db->loadObjectList( 'id' );
//		debugQuery($db, $this->_pupils);
	}
	
	/**
	 * Adds pupils to the adhoc list for this register
	 * Does not limit pupils based on acl as list is already defined
	 *
	 * @param array $pupilList  The array of pupil objects (or ids) to add to this register, indexed by pupil id
	 */
	function setAdhocPupils($pupilList)
	{
		$pupilObjs = array();
		foreach ($pupilList as $id=>$data) {
			if (!is_object($data)) {
				$bareIds[] = $data;
				unset($pupilList[$id]);
			}
			else {
				$pupilObjs[] =& $data;
			}
		}
		if (isset($bareIds)) {
			$db = &JFactory::getDBO();
			$db->setQuery('SELECT DISTINCT ppl.id, COALESCE( ppl.preferred_firstname, ppl.firstname ) AS firstname, COALESCE( ppl.preferred_surname, ppl.surname ) AS surname'
				."\n".' FROM #__apoth_ppl_people AS ppl'
				."\n".' WHERE `ppl`.`id` IN ("'.implode('", "', $bareIds).'")');
			$pupilObjs2 = $db->loadObjectList('id');
		}
		
		$this->_adhocPupils = array_merge($this->_adhocPupils, $pupilObjs, $pupilObjs2);
		ApotheosisLibArray::sortObjects($this->_adhocPupils, array('surname', 'firstname'), 1, true);
	}
	
	/**
	 * Removes pupils from the adhoc list for this register
	 *
	 * @param array $pupilList  The array of pupil ids to remove from this register
	 */
	function unsetAdhocPupils($pupilList)
	{
//		echo 'unsetting: ';
//		var_dump_pre($pupilList);
		foreach ($pupilList as $pId) {
			$adhocs = $this->getAdhocPupils();
			if (array_key_exists($pId, $adhocs)) {
				unset($this->_adhocPupils[$pId]);
			}
		}
	}
	
	/**
	 * Retrieves the list of pupils on the adhoc list for this register
	 *
	 * @return array  The adhoc pupil objects indexed by pupil id
	 */
	function getAdhocPupils()
	{
		if (empty($this->_adhocPupils)) {
			$this->_loadAdhocPupils();
			ApotheosisLibArray::sortObjects($this->_adhocPupils, array('surname', 'firstname'), 1, true);
		}
		return $this->_adhocPupils;
	}
	
	/**
	 * Loads the adhoc pupil list for this register
	 */
	function _loadAdhocPupils()
	{
		if (empty($this->_pupils)) {
			$this->_loadPupils();
		}
		if (empty($this->_marks)) {
			$this->_loadMarks();
		}
		$this->_adhocPupils = array_diff(array_keys($this->_marks), array_keys($this->_pupils));
		if( !is_array($this->_adhocPupils) ) {
			$this->_adhocPupils = array();
		}
		
		$db = &JFactory::getDBO();
		$db->setQuery('SELECT DISTINCT ppl.id, COALESCE( ppl.preferred_firstname, ppl.firstname ) AS firstname, COALESCE( ppl.preferred_surname, ppl.surname ) AS surname'
			."\n".' FROM #__apoth_ppl_people AS ppl'
			."\n".' WHERE id IN ("'.implode('", "', $this->_adhocPupils).'")');
		$this->_adhocPupils = $db->loadObjectList('id');
		
		return count($this->_adhocPupils);
	}
	
	
	/**
	 * Retrieves the pupils not involved in this register
	 *
	 * @return array  The uninvolved pupil objects indexed by pupil id
	 */
	function getOtherPupils()
	{
		if (empty($this->_otherPupils)) {
			$this->_loadOtherPupils();
		}
		
		return $this->_otherPupils; // note, no logic, this function just gets stuff previuosly set by _loadPupils
	}
	
	/**
	 * Loads the pupils not involved in this register
	 */
	function _loadOtherPupils()
	{
		$db = &JFactory::getDBO();
		
		$a1 = array_keys($this->getPupils());
		$a2 = array_keys($this->getAdhocPupils());
		$curPupils = array_merge($a1, $a2);
				
		$db->setQuery('SELECT DISTINCT ppl.id, COALESCE( ppl.preferred_firstname, ppl.firstname ) AS firstname, COALESCE( ppl.preferred_surname, ppl.surname ) AS surname'
			."\n".'FROM #__apoth_ppl_people AS ppl'
			."\n".'INNER JOIN #__apoth_tt_group_members AS gm'
			."\n".'   ON gm.person_id = ppl.id'
			."\n".'WHERE `ppl`.`id` NOT IN ("'.implode('", "', $curPupils).'")'
			."\n".'  AND '.ApotheosisLibDb::dateCheckSql( 'gm.valid_from', 'gm.valid_to', date('Y-m-d'), date('Y-m-d'))
			."\n".'  AND gm.is_student = 1' // *** titikaka
			."\n".'ORDER BY surname, firstname ');
		$this->_otherPupils = $db->loadObjectList();
	}
	
	/**
	 * Retrieves the composite id of this register
	 * This is useful to use as an index when you have an array of register objects
	 *
	 * @return string  The id of this register (derived from a composite of its defining features)
	 */
	function getCompId()
	{
		return base64_encode($this->date.$this->_compSep.$this->time.$this->_compSep.$this->course->id.$this->_compSep.$this->location);
	}
	
	/**
	 * Splits a composite id into its component parts
	 * If an id is provided, can be called statically
	 *
	 * @param string $id  Optional id string to split. If not provided, the current register's id is split
	 * @return array  The defining features of this register (date, time, course, location) in an associative array
	 */
	function splitCompId($id = false)
	{
//		echo 'splitting comp id<br />';
		if ($id === false) {
			$parts = explode($this->_compSep, base64_decode($this->getCompId()));
		}
		else {
			$_compSep = '~*~';
			$parts = explode($_compSep, base64_decode($id));
		}
		return array('date'=>$parts[0], 'time'=>$parts[1], 'course'=>$parts[2], 'location'=>$parts[3]);
	}
	
}
?>