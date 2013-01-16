<?php
/**
 * @package     Arc
 * @subpackage  Timetable
 * @copyright   Copyright (C) 2005 Punnet. All rights reserved. See COPYRIGHT_ARC.txt
 * @license     http://www.gnu.org/licenses/gpl.html GNU/GPL. See LICENSE_ARC.txt
 * Arc is free software: you can redistribute it and/or modify
 * it under the terms of version 3 of the GNU General Public License
 * as published by the Free Software Foundation.
 * The disclaimer of warranty as stated in the GPL applies to this program
 */

defined( '_JEXEC' ) or die( 'Restricted access' );

jimport( 'joomla.application.component.model' );

 /*
 * Timetable Manager Summary Model
 */
class TimetableModelSummary extends JModel
{
	function __construct()
	{
		parent::__construct();
	}
	
	function getTimetable()
	{
		if (!isset($this->_timetable)) {
			$this->setTimetable( array() );
		}
		return $this->_timetable;
	}
	
	function setTimetable( $requirements )
	{
//		var_dump_pre($requirements);
		$db = JFactory::getDBO();
		
		// establish the pattern(s) and day(s) under consideration based on the dates given
		$this->_dateFrom = ( (!isset($requirements['startDate']) || $requirements['startDate'] == false) ? date('Y-m-d') : $requirements['startDate'] );
		$this->_dateTo   = ( (!isset($requirements['endDate']  ) || $requirements['endDate']   == false) ? date('Y-m-d') : $requirements['endDate']   );
		$this->_checkDateOrder();
		$cur = unixtojd(strtotime($this->_dateFrom));
		$to = unixtojd(strtotime($this->_dateTo));
		while ($cur <= $to) {
			$date = date('Y-m-d', jdtounix($cur));
			$pattern = ApotheosisLibCycles::getPatternByDate( $date );
			$patterns[$pattern->id] = $pattern->id;
			
			$day = ApotheosisLibCycles::dateToCycleDay( $date );
			$days[$day] = $db->Quote($day);
			$cur++;
		}
		
		$this->_timetable = array();
		$this->_noSessions = 0;
		if( isset( $requirements['teachers'] ) && $requirements['teachers'] != false ) {
			$query = 'SELECT DISTINCT t.course, t.pattern, t.day, t.room_id, dd.day_section, dd.start_time, dd.end_time, c.fullname, ppl.id AS teacher_id, ppl.title, COALESCE( ppl.preferred_firstname, ppl.firstname ) AS firstname, ppl.middlenames, COALESCE( ppl.preferred_surname, ppl.surname ) AS surname'
				."\n".'FROM #__apoth_tt_timetable AS t'
				."\n".'INNER JOIN #__apoth_cm_courses AS c'
				."\n".'   ON c.id = t.course'
				."\n".'  AND c.deleted = 0'
				."\n".'INNER JOIN #__apoth_tt_group_members AS gm'
				."\n".'   ON gm.group_id = t.course'
				."\n".'  AND gm.is_teacher = 1' // *** titikaka
				."\n".'INNER JOIN #__apoth_tt_patterns AS p'
				."\n".'   ON t.pattern = p.id'
				."\n".'INNER JOIN #__apoth_tt_daydetails AS dd'
				."\n".'   ON t.pattern = dd.pattern'
									// t.day must be + 1 because MySQL starts indexing from 1, but we count from 0 in php land so the value in t.day counts from 0
				."\n".'  AND SUBSTRING(p.format, t.day + 1, 1) = dd.day_type'
				."\n".'  AND t.day_section = dd.day_section'
				."\n".'INNER JOIN #__apoth_ppl_people AS ppl'
				."\n".'   ON ppl.id = gm.person_id'
				."\n".'WHERE gm.person_id IN ("'.implode('", "', $requirements['teachers']).'")'
				."\n".'   AND t.pattern IN ("'.implode('", "', $patterns).'")'
				."\n".'   AND t.day IN ('.implode(', ', $days).')'
				."\n".'   AND '.$this->_dateCheck( 't.valid_from', 't.valid_to' )
				."\n".'ORDER BY dd.start_time ASC';
			
			$db->setQuery($query);
			$this->_timetable = $db->loadObjectList();
			$this->_noSessions = count($this->_timetable);
		}
		
		foreach($this->_timetable as $key=>$row) {
			$this->_timetable[$key]->displayname = ApotheosisLib::nameCase('teacher', $row->title, $row->firstname, $row->middlenames, $row->surname);
		}
	}
	
	function getNoSessions()
	{
		if (!isset($this->_noSessions)) {
			$this->_loadNoSessions();
		}
		return $this->_noSessions;
	}
	
	function _loadNoSessions()
	{
		if (!isset($this->_timetable)) {
			$this->setTimetable( array() ); // setTimetable also sets the session count
		}
		else {
			$this->_noSessions = ApotheosisLibArray::countMulti($this->_timetable);
		}
	}
	
	// ####  Accessors  ###
	
	function setDates($start, $end)
	{
		$this->_dateFrom = $start;
		$this->_dateTo = $end;
		$this->_checkDateOrder();
	}
	function setEndDate($end)
	{
		$this->_dateTo = $end;
		$this->_checkDateOrder();
	}
	function setStartDate($start)
	{
		$this->_dateFrom = $start;
		$this->_checkDateOrder();
	}
	
	function _checkDateOrder()
	{
		if (($this->_dateFrom !== false) && ($this->_dateTo !== false) && ($this->_dateFrom > $this->_dateTo)) {
			$tmp = $this->_dateFrom;
			$this->_dateFrom = $this->_dateTo;
			$this->_dateTo = $tmp;
		}
	}
	
	// ####  SQL utility  ###
	
	/**
	 * Creates an sql string for use in the WHERE clause of a query to limit the results to those that
	 * fall within the currently set date range.
	 * @param $from string  The field name of the valid_from field
	 * @param $to string  The field name of the valid_to field
	 * @return string  The bracket-encapsulated string to add to the WHERE clause
	 */
	function _dateCheck($fromField, $toField)
	{
		// select only those allocations which fall at least partially within the given date range
		$qtmp = array();
		if ($this->_dateFrom !== false) { $qtmp[] = "\n".' '.$fromField.' <= "'.$this->_dateTo.'"'; }
		if ($this->_dateTo   !== false) { $qtmp[] = "\n".' ( '.$toField.  ' >= "'.$this->_dateFrom.'" OR '.$toField.' IS NULL )'; }
		$str = (empty($qtmp) ? '(1=1)' : '('.implode("\n".' AND ', $qtmp)."\n".')');
		return $str;
	}
}
?>