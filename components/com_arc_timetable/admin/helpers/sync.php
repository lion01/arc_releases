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

/**
 * Timetable Sync Helper
 *
 * @author     Punnet - Arc Team <arc_developers@pun.net>
 * @package    Arc
 * @subpackage Timetable
 * @since      1.6.5
 */
class ArcSync_Timetable extends ArcSync
{
	/** @var int The most rows to import at a time - memory issues occur if this is too high*/
	var $_maxLimit = 5000;
	
	/**
	 * Import all data about timetable structure
	 *
	 * @param array $params  Values from the form used to originally add the job
	 * @param array $jobs  Array of jobs. Each job is an array with all that job's settings
	 */
	function importTimetable( $params, $jobs )
	{
		$maxTime = ini_get( 'maximum_execution_time' );
		set_time_limit( 1800 );
		
		$tablesArray = array( '#__apoth_tt_timetable', '#__apoth_tt_daydetails', '#__apoth_tt_pattern_instances', '#__apoth_tt_patterns' );
		ApotheosisLibDb::disableDBChecks( $tablesArray );
		
		$this->_complete = (bool)$params['complete'];
		
		timer( 'importing timetable' );
		
		// Patterns
		$j = $this->jobSearch( array( 'call'=>'arc_timetable_patterns' ), $jobs );
		$xml = $this->_loadReport( $jobs[$j], 'progressive' );
		$this->srcId = $jobs[$j]['src'];
		$this->_importPatterns( $xml );
		
		// Day details
		$this->_setExternalPatterns( true );
		$xml = $this->_loadReport( $jobs[$j], 'progressive' );
		$this->_importDayDetails( $xml );
		
		// Pattern instances
		$j = $this->jobSearch( array( 'call'=>'arc_timetable_instances' ), $jobs );
		$xml = $this->_loadReport( $jobs[$j], 'progressive' );
		$this->srcId = $jobs[$j]['src'];
		$this->_importInstances( $xml );
		
		// Timetable (groups at times in places)
		$j = $this->jobSearch( array( 'call'=>'arc_timetable_classes' ), $jobs );
		$xml = $this->_loadReport( $jobs[$j], 'progressive' );
		$this->srcId = $jobs[$j]['src'];
		$ttParams = array();
		$pList = explode( "\r\n", $jobs[$j]['params'] );
		foreach( $pList as $p ) {
			$parts = explode( '=', $p, 2 );
			$ttParams[$parts[0]] = $parts[1];
		}
		$this->start = $ttParams['effective'];
		$this->end   = $ttParams['effective'];
		$this->_importTimetable( $xml );
		
		ApotheosisLibDb::enableDBChecks( $tablesArray );
		set_time_limit( $maxTime );
		return true;
	}
	
	/**
	 * Import all data about enrolments
	 *
	 * @param array $params  Values from the form used to originally add the job
	 * @param array $jobs  Array of jobs. Each job is an array with all that job's settings
	 */
	function importEnrolments( $params, $jobs )
	{
		$tablesArray = array( '#__apoth_tt_group_members' );
		ApotheosisLibDb::disableDBChecks( $tablesArray );
		
		$this->_enrolments = array();
		$this->_teacherRole = ApotheosisLibAcl::getRoleId( 'group_supervisor_teacher' );
		$this->_studentRole = ApotheosisLibAcl::getRoleId( 'group_participant_student' );
		$this->_complete = (bool)$params['complete'];
		
		timer( 'importing enrolments' );
		
		$j = $this->jobSearch( array( 'call'=>'arc_timetable_members' ), $jobs );
		$xml = $this->_loadReport( $jobs[$j], 'progressive' );
		$this->srcId = $jobs[$j]['src'];
		$this->_setExistingClasses( true, false, 'ext_course_id' );
		$this->_setExternalClasses( true );
		
		timer( 'got handle on data' );
		$job = $jobs[$j];
		$jParams = array();
		$pList = explode( "\r\n", $job['params'] );
		foreach( $pList as $p ) {
			$parts = explode( '=', $p, 2 );
			$jParams[$parts[0]] = $parts[1];
		}
		
		// do importing
		$this->_importEnrolments( $xml, $jParams['active_start'], $jParams['active_end'] );
		
		// clean up
		$xml->free();
		
		ApotheosisLibDb::enableDBChecks( $tablesArray );
		return true;
	}
	
	function _rawToObjects( $rpt, $r )
	{
		switch($rpt) {
		case( 'arc_timetable_patterns' ):
			$this->_setExternalPatterns();
			$this->_setExternalPeriods();
			
			$obj = new stdClass();
			$obj2 = new stdClass();
			
			// get the pattern
			$startDate = $this->_cleanDate( $r->childData('startdate'), true );
			$endDate =   $this->_cleanDate( $r->childData('enddate'),   true );
			
			$obj->id           = $r->childData( 'arc_pattern_id' );
			$obj->src          = $this->srcId;
			$obj->ext_model_id = $this->_cleanDate( $r->childData('startdate') );
			$obj->name         = $r->childData( 'modelname' );
			$obj->valid_from   = $startDate;
			$obj->valid_to     = $endDate;
			$obj->_newId       = false;
			
			// no Arc id and an unknown ext id means a new entry
			if( is_null($obj->id) ) {
				if( !isset($this->externalPatterns[$obj->ext_model_id]) ) {
					$obj->_newId = true;
				}
				else {
					$obj->id = $this->externalPatterns[$obj->ext_model_id]->id;
				}
			}
			
			// get the day/periods in the pattern
			$obj2->pattern           = $obj->id;
			$obj2->day_type          = $r->childData( 'arc_day_type' );
			$obj2->day_section       = $r->childData( 'period' );
			$obj2->day_section_short = end( preg_split('~\\W~', $obj2->day_section, -1, PREG_SPLIT_NO_EMPTY) );
			$obj2->src               = $this->srcId;
			$obj2->ext_period_id     = $r->childData( 'primary_id' );
			$obj2->start_time        = date( 'H:i:s', strtotime($r->childData('starttime')) );
			$obj2->end_time          = date( 'H:i:s', strtotime($r->childData('endtime')) );
			$obj2->has_teacher       = 1;
			$obj2->taught            = 1;
			$obj2->registered        = 1;
			$obj2->valid_from        = $startDate;
			$obj2->valid_to          = $endDate;
			$obj2->_newId            = false;
			
			if( is_null($obj2->pattern) || is_null($obj2->day_type) || is_null($obj2->day_section) ) {
				if( !isset($this->externalPeriods[$obj2->ext_period_id]) ) {
					$obj2->_newId = true;
				}
				else {
					$obj2->pattern = $this->externalPeriods[$obj2->ext_period_id]->pattern;
					$obj2->day_type = $this->externalPeriods[$obj2->ext_period_id]->day_type;
					$obj2->day_section = $this->externalPeriods[$obj2->ext_period_id]->day_section;
				}
			}
			$obj2->_id               = $obj2->pattern.'~'.$obj2->day_type.'~'.$obj2->day_section;
			
			// deal with return values
			if( isset($this->markedPatterns[$obj->ext_model_id]) ) {
				$retVal['pattern'] = null;
			}
			else {
				$this->markedPatterns[$obj->ext_model_id] = true;
				$retVal['pattern'] = $obj;
			}
			
			if( is_null($obj2->pattern) || is_null($obj2->day_section) ) {
				$retVal['period'] = null;
			}
			else {
				$retVal['period'] = $obj2;
			}
			break;
		
		case( 'arc_timetable_instances' ):
			$this->_setExistingInstances();
			
			$obj = new stdClass();
			$obj->id          = $r->childData( 'arc_instance_id' );
			$obj->pattern     = $r->childData( 'pattern' );
			$obj->start       = $this->_cleanDate( $r->childData('start') ).' 00:00:00';
			$obj->end         = $this->_cleanDate( $r->childData('end') ).' 23:59:59';
			$obj->start_index = $r->childData( 'start_index' );
			$obj->description = $r->childData( 'description' );
			$obj->description_short = $r->childData( 'description_short' );
			$obj->holiday     = $r->childData( 'holiday' );
			$obj->_newId      = false;
			$obj->_type       = $r->childData( 'event_type' );
			$obj->_category   = $r->childData( 'category' );
			
			// fill in any missing pattern id if possible
			if( is_null($obj->pattern) ) {
				$this->_setExistingPatterns();
				// Find pattern in which this event starts
				$startTime = strtotime( $obj->start );
				$cur = end( $this->existingPatterns );
				do {
					if( $cur->startTime <= $startTime ) {
						$obj->pattern = $cur->id;
						reset($this->existingPatterns);
					}
				} while( ($cur = prev($this->existingPatterns)) !== false );
			}
			
			if( is_null($obj->id) ) {
				// derive missing id from existing based on start time rather than ext_id
				foreach( $this->existingInstances as $id=>$vals ) {
					if( ($vals->start == $obj->start)
					 && ($vals->pattern == $obj->pattern) ) {
						$obj->id = $id;
						break;
					}
				}
			}
			else {
				// check id and remove invalid
				if( !isset($this->existingInstances[$obj->id]) ) {
					$obj->id = null;
				}
			}
			
			if( is_null($obj->id) ) {
				$obj->_newId = true;
			}
			
			$retVal = $obj;
			break;
		
		case( 'arc_timetable_classes' ):
			
			$ids = explode( ',', $r->childData('multiple_id') );
			if( !empty($ids) ) {
				$this->_setExternalClasses();
				$this->_setExistingClasses( false, false, 'shortname' );
				$this->_setExistingLessons();
				
				$obj = new stdClass();
				$obj->id          = $r->childData( 'arc_lesson_id' );
				$obj->course      = $r->childData( 'arc_group_id' );
				$obj->pattern     = null;
				$obj->day         = null;
				$obj->day_section = $r->childData( 'period' );
				$obj->room_id     = $r->childData( 'room' );
				$obj->valid_from  = $this->_cleanDate( $r->childData('startdate'), true );
				$obj->valid_to    = $this->_cleanDate( $r->childData('enddate'),   true );
				$obj->_newId      = false;
				
				// with the basic data in, it's time to work out the calculated values ...
				// ... the class id
				$name = $r->childData('class');
				$now = time();
				if( is_null($obj->course) ) {
					if( isset($this->externalClasses[$ids[0]]) ) {
						$obj->course = $this->externalClasses[$ids[0]];
					}
					elseif( !is_null($name) ) {
						if( isset($this->existingClasses[$name]) ) {
							foreach( $this->existingClasses[$name] as $c ) {
								if( is_null($c->end_date) || (strtotime($c->end_date) > $now) ) {
									$obj->course = $c->id;
									break;
								}
							}
						}
					}
				}
				
				// ... the pattern id
				if( is_null($obj->pattern) ) {
					$tmp = ApotheosisLibCycles::getPatternByDate( $obj->valid_from );
					$obj->pattern = $tmp->id;
				}
				
				// ... the day index
				if( is_null($obj->day) ) {
					$this->_setExistingDayPeriods();
					if( isset( $this->existingDayPeriods[$obj->pattern][$r->childData('period')] ) ) {
						$d = $this->existingDayPeriods[$obj->pattern][$r->childData('period')];
						$obj->day = reset( ApotheosisLibCycles::getDayIndex($d, $obj->pattern) );
					}
				}
				
				// ... the Arc id (search based on pattern, day, day_section, valid_from)
				if( is_null($obj->id) ) {
					$tmp = new stdClass();
					$tmp->pattern = $obj->pattern;
					$tmp->day = $obj->day;
					$tmp->day_section = $obj->day_section;
					$tmp->course = $obj->course;
					$tmp->valid_from = $obj->valid_from;
					
					$matches = ApotheosisLibArray::array_search_partial( $tmp, $this->existingLessons, false, -1, array('pattern', 'day', 'day_section', 'course', 'valid_from') );
					
					if( ($matches === false) || empty($matches) ) {
						$obj->_newId = true;
					}
					else {
						$k = reset( $matches );
						$obj->id = reset( $matches );
					}
				}
				
				$retVal = $obj;
			}
			else {
				$retVal = null;
			}
			break;
			
		case( 'arc_timetable_members' ):
			$this->_setExistingClasses( false, false, 'ext_course_id' );
			$this->_setExternalClasses();
			$this->_setExternalPeople( false, false );
			$this->_setExistingPeopleMash( false, false );
			
			$retVal = array();
			$ids = explode( ',', $r->childData( 'multiple_id' ) );
			$obj = new stdClass();
			$obj2 = new stdClass();
			$studentOk = $teacherOk = true;
			
			// clean up the dates
			$from = $this->_cleanDate( $r->childData('start'), true );
			$to   = $this->_cleanDate( $r->childData('end'),   true );
			// $to   = date( 'Y-m-d H:i:s', (strtotime($to) + 59) ); // *** With CSV imports this can make things unclear
			
			// discover the group id from the incoming data
			$groupId = $r->childData( 'arc_group_id' );
			if( is_null($groupId) ) {
				$multi = substr( $ids[1], strlen($ids[0]) );
				$group = $r->childData( 'group' );
				$id = '';
				$len = strlen( $multi );
				$count = 0;
				// ... look through all courses for the given ext_id
				// (timetable info could come from a source other than the source of course info)
				while( $count < $len ) {
					$id = substr( $multi, 0, ++$count );
					
					if( isset($this->existingClasses[$id]) ) {
						foreach( $this->existingClasses[$id] as $c ) {
							if( ($c->fullname == $group)
							 && ($c->start_date <= $to)
							 && (($c->end_date >= $from) || is_null($c->end_date)) ) {
								$groupId = $c->id;
								break 2;
							}
						}
					}
				}
				// ... mark failure if appropriate
				if( !isset($groupId) || is_null($groupId) ) {
					$groupId = null;
				}
			}
			
			// only worth proceeding if we found a valid group id
			if( !is_null($groupId) ) {
				
				// student
				$studentId = $r->childData( 'arc_person_id' );
				if( is_null($studentId) ) {
					if( isset($this->externalPeople[$ids[0]]) ) {
						$studentId = $this->externalPeople[$ids[0]];
					}
					else {
						$studentOk = false;
					}
				}
				
				$obj->group_id = $groupId;
				$obj->person_id = $studentId;
				$obj->role = $this->_studentRole;
				$obj->is_admin   = 0; // *** titikaka
				$obj->is_teacher = 0; // *** titikaka
				$obj->is_student = 1; // *** titikaka
				$obj->is_watcher = 0; // *** titikaka
				$obj->valid_from = $from;
				$obj->valid_to   = $to;
				
				// teacher
				$mash = $r->childData( 'title' )
					.'~'.$r->childData( 'surname' )
					.'~'.$r->childData( 'forename' )
					.'~'.$this->_cleanDate( $r->childData('dob') )
					.'~'.$r->childData( 'postcode' );
				$mash = strtolower( $mash );
				
				if( isset($this->existingPeopleMash[$mash]) ) {
					$teacherId = $this->existingPeopleMash[$mash]->id;
				}
				else {
					$teacherId = null;
					$teacherOk = false;
				}
				
				$obj2->group_id = $groupId;
				$obj2->person_id = $teacherId;
				$obj2->role = $this->_teacherRole;
				$obj2->is_admin   = 0; // *** titikaka
				$obj2->is_teacher = 1; // *** titikaka
				$obj2->is_student = 0; // *** titikaka
				$obj2->is_watcher = 0; // *** titikaka
				$obj2->valid_from = $from;
				$obj2->valid_to   = $to;
				
				if( $studentOk ) {
					$retVal[] = $obj;
				}
				if( $teacherOk ) {
					$retVal[] = $obj2;
				}
			}
			else {
				$retVal = array();
			}
			break;
		}
		
		return $retVal;
	}
	
	
	function _setExternalPatterns( $refresh = false)
	{
		if( $refresh ) {
			unset( $this->externalPatterns );
			$this->markedPatterns = array();
		}
		if( !isset($this->externalPatterns) ) {
			$db = &JFactory::getDBO();
			$query = 'SELECT * FROM '.$db->nameQuote('#__apoth_tt_patterns')
				."\n".'WHERE '.$db->nameQuote('ext_model_id').' IS NOT NULL'
				."\n".'  AND '.$db->nameQuote('src').' = '.$db->Quote($this->srcId)
				."\n".'ORDER BY '.$db->nameQuote( 'valid_from' );
			$db->setQuery( $query );
			$this->externalPatterns = $db->loadObjectList( 'ext_model_id' );
			
			if( is_null($this->externalPatterns) ) { $this->externalPatterns = array(); } // to avoid errors
		}
	}
	
	function _setExistingPatterns( $refresh = false, $onlyOurs = true )
	{
		if( $refresh ) {
			unset( $this->existingPatterns );
		}
		if( !isset($this->existingPatterns) ) {
			$db = &JFactory::getDBO();
			$query = 'SELECT * FROM '.$db->nameQuote('#__apoth_tt_patterns');
			if( $onlyOurs ) {
				$query .= "\n".'WHERE '.$db->nameQuote('src').' = '.$db->Quote($this->srcId);
			}
			$query .= "\n".'ORDER BY '.$db->nameQuote( 'valid_from' );
			
			$db->setQuery( $query );
			$this->existingPatterns = $db->loadObjectList( 'id' );
			
			if( is_null($this->existingPatterns) ) { $this->existingPatterns = array(); } // to avoid errors
		}
	}
	
	function _setExternalPeriods( $refresh = false )
	{
		if( $refresh ) {
			unset( $this->externalPeriods );
		}
		if( !isset($this->externalPeriods) ) {
			$db = &JFactory::getDBO();
			$query = 'SELECT * FROM '.$db->nameQuote('#__apoth_tt_daydetails')
				."\n".'WHERE '.$db->nameQuote('ext_period_id').' IS NOT NULL'
				."\n".'  AND '.$db->nameQuote('src').' = '.$db->Quote($this->srcId);
			$db->setQuery( $query );
			$this->externalPeriods = $db->loadObjectList( 'ext_period_id' );
			
			if( is_null($this->externalPeriods) ) { $this->externalPeriods = array(); } // to avoid errors
		}
	}
	
	function _setExistingPeriods( $refresh = false, $onlyOurs = true )
	{
		if( $refresh ) {
			unset( $this->existingPeriods );
		}
		if( !isset($this->existingPeriods) ) {
			$db = &JFactory::getDBO();
			$query = 'SELECT * FROM '.$db->nameQuote('#__apoth_tt_daydetails');
			if( $onlyOurs ) {
				$query .= "\n".'WHERE '.$db->nameQuote('src').' = '.$db->Quote($this->srcId);
			}
			$db->setQuery( $query );
			$tmp = $db->loadObjectList();
			
			if( is_null($tmp) ) { $tmp = array(); } // to avoid errors
			while( !is_null(($v = array_shift($tmp))) ) {
				$id = $v->pattern.'~'.$v->day_type.'~'.$v->day_section;
				$this->existingPeriods[$id] = $v;
			}
			if( !isset($this->existingPeriods) ) { $this->existingPeriods = array(); } // to avoid errors
		}
	}
	
	function _setExistingDayPeriods( $refresh = false )
	{
		if( $refresh ) {
			unset( $this->existingPeriods );
			unset( $this->existingDayPeriods );
		}
		if( !isset($this->existingDayPeriods) ) {
			$this->_setExistingPeriods( true );
			$this->existingDayPeriods = array();
			foreach( $this->existingPeriods as $p ) {
				$this->existingDayPeriods[$p->pattern][$p->day_section] = $p->day_type;
			}
		}
	}
	
	function _setExistingInstances( $refresh = false )
	{
		if( $refresh ) {
			unset( $this->existingInstances );
		}
		if( !isset( $this->existingInstances ) ) {
			$db = &JFactory::getDBO();
			$query = 'SELECT *'
				."\n".' FROM '.$db->nameQuote( '#__apoth_tt_pattern_instances' )
				."\n".' ORDER BY '.$db->nameQuote( 'start' );
			$db->setQuery($query);
			$this->existingInstances = $db->loadObjectList( 'id' );
			
			if( is_null($this->existingInstances) ) { $this->existingInstances = array(); }
		}
	}
	
	function _setExistingClasses( $refresh = false, $onlyOurs = true, $index = 'id' )
	{
		if( $refresh ) {
			unset( $this->existingClasses );
		}
		if( !isset( $this->existingClasses ) ) {
			$db = &JFactory::getDBO();
			$query = 'SELECT '.$db->nameQuote('id').', '.$db->nameQuote( 'ext_course_id' ).', '.$db->nameQuote( 'shortname' ).', '.$db->nameQuote( 'fullname' ).', '.$db->nameQuote('start_date').', '.$db->nameQuote('end_date')
				."\n".'FROM '.$db->nameQuote('#__apoth_cm_courses')
				."\n".'WHERE '.$db->nameQuote('type').' IN ( '.$db->Quote('normal').', '.$db->Quote('pastoral').' )';
			if( $onlyOurs ) {
				$query .= "\n".' AND '.$db->nameQuote('src').' = '.$db->Quote($this->srcId);
			}
			$db->setQuery($query);
			$tmp = $db->loadObjectList();
			
			$this->existingClasses = array();
			if( is_null($tmp) ) { $tmp = array(); }
			while( ($cur = array_shift( $tmp )) && !is_null( $cur ) ) {
				$this->existingClasses[$cur->$index][] = $cur;
			}
		}
	}
	
	function _setExternalClasses( $refresh = false )
	{
		if( $refresh ) {
			unset( $this->externalClasses );
		}
		if( !isset( $this->externalClasses ) ) {
			$db = &JFactory::getDBO();
			$query = 'SELECT '.$db->nameQuote('id').', '.$db->nameQuote('ext_course_id')
				."\n".'FROM '.$db->nameQuote('#__apoth_cm_courses')
				."\n".'WHERE '.$db->nameQuote('type').' = '.$db->Quote('normal')
				."\n".'  AND '.$db->nameQuote( 'ext_course_id' ).' IS NOT NULL'
				."\n".'  AND '.$db->nameQuote( 'src' ).' = '.$db->Quote($this->srcId);
			$db->setQuery($query);
			$this->externalClasses = $db->loadObjectList('ext_course_id');
			
			if( is_null($this->externalClasses) ) { $this->externalClasses = array(); }
			foreach( $this->externalClasses as $k=>$v ) {
				$this->externalClasses[$k] = $v->id;
			}
		}
	}
	
	function _setExistingLessons( $refresh = false )
	{
		if( $refresh ) {
			unset( $this->existingLessons );
		}
		if( !isset( $this->existingLessons ) ) {
			$db = &JFactory::getDBO();
			$query = 'SELECT *'
				."\n".'FROM '.$db->nameQuote('#__apoth_tt_timetable')
				."\n".'WHERE '.ApotheosisLibDb::dateCheckSql( 'valid_from', 'valid_to', $this->start, $this->end )
				."\n".'ORDER BY pattern, day, day_section, course';
			$db->setQuery($query);
			$this->existingLessons = $db->loadObjectList('id');
			
			if( is_null($this->existingLessons) ) { $this->existingLessons = array(); }
		}
	}
	
	function _setExistingPeople( $refresh = false, $onlyOurs = true )
	{
		if( $refresh ) {
			unset( $this->existingPeople );
		}
		if( !isset( $this->existingPeople ) ) {
			$db = &JFactory::getDBO();
			$query = 'SELECT '.$db->nameQuote('p').'.'.$db->nameQuote('id')
				.', '.$db->nameQuote( 'ext_person_id' )
				."\n".'FROM '.$db->nameQuote('#__apoth_ppl_people').' AS '.$db->nameQuote('p')
				."\n".'LEFT JOIN '.$db->nameQuote( '#__apoth_ppl_addresses' ).' AS '.$db->nameQuote('a')
				."\n".'  ON '.$db->nameQuote('a').'.'.$db->nameQuote('id').' = '.$db->nameQuote('p').'.'.$db->nameQuote('address_id');
			if( $onlyOurs ) {
				$query .= "\n".'WHERE '.$db->nameQuote( 'p' ).'.'.$db->nameQuote('src').' = '.$db->Quote( $this->srcId );
			}
			$query .= "\n".'ORDER BY '.$db->nameQuote('surname').', '.$db->nameQuote('firstname').', '.$db->nameQuote('dob');
			$db->setQuery( $query );
			$this->existingPeople = $db->loadObjectList( 'ext_person_id' );
			
			if( is_null($this->existingPeople) ) { $this->existingPeople = array(); }
		}
	}
	
	function _setExternalPeople( $refresh = false, $onlyOurs = true )
	{
		if( $refresh ) {
			unset( $this->externalPeople );
		}
		if( !isset( $this->externalPeople ) ) {
			$db = &JFactory::getDBO();
			$query = 'SELECT '.$db->nameQuote('p').'.'.$db->nameQuote('id')
				.', '.$db->nameQuote( 'ext_person_id' )
				."\n".'FROM '.$db->nameQuote('#__apoth_ppl_people').' AS '.$db->nameQuote('p');
			if( $onlyOurs ) {
				$query .= "\n".'WHERE '.$db->nameQuote( 'p' ).'.'.$db->nameQuote('src').' = '.$db->Quote( $this->srcId );
			}
			$db->setQuery( $query );
			$tmp = $db->loadObjectList( 'ext_person_id' );
			
			$this->externalPeople = array();
			if( is_null($tmp) ) { $tmp = array(); }
			while( ($cur = array_shift( $tmp ) ) && !is_null($cur) ) {
				$this->externalPeople[$cur->ext_person_id] = $cur->id;
			}
		}
	}
	
	function _setExistingPeopleMash( $refresh = false, $onlyOurs = true )
	{
		if( $refresh ) {
			unset( $this->existingPeopleMash );
		}
		if( !isset( $this->existingPeopleMash ) ) {
			$db = &JFactory::getDBO();
			if( $db->getVersion() < '4.3' ) {
				$qa = 'LOWER( CONCAT( '.$db->nameQuote( 'title' );
				$qb = ') ) AS '.$db->nameQuote('id_mash');
			}
			else {
				$qa = 'LOWER( CONVERT (CONCAT( '.$db->nameQuote( 'title' );
				$qb = ') USING latin1 ) ) AS '.$db->nameQuote('id_mash');
			}
			$query = 'SELECT '.$db->nameQuote('p').'.'.$db->nameQuote('id')
				.', '.$qa
				.', "~", '.$db->nameQuote( 'surname' )
				.', "~", '.$db->nameQuote( 'firstname' )
				.', "~", '.$db->nameQuote( 'dob' )
				.', "~", '.$db->nameQuote( 'postcode' )
				.' '.$qb
				."\n".'FROM '.$db->nameQuote('#__apoth_ppl_people').' AS '.$db->nameQuote('p')
				."\n".'LEFT JOIN '.$db->nameQuote('#__apoth_ppl_addresses').' AS '.$db->nameQuote('a')
				."\n".'  ON '.$db->nameQuote('a').'.'.$db->nameQuote('id').' = '.$db->nameQuote('p').'.'.$db->nameQuote('address_id');
			if( $onlyOurs ) {
				$query .= "\n".'WHERE '.$db->nameQuote( 'p' ).'.'.$db->nameQuote('src').' = '.$db->Quote( $this->srcId );
			}
			$query .= "\n".'ORDER BY '.$db->nameQuote('surname').', '.$db->nameQuote('firstname').', '.$db->nameQuote('dob');
			$db->setQuery( $query );
			$this->existingPeopleMash = $db->loadObjectList( 'id_mash' );
			
			if( is_null($this->existingPeopleMash) ) { $this->existingPeopleMash = array(); }
		}
	}
	
	/**
	 * Columns required for SIMS XML report creation from an uploaded CSV
	 * 
	 * @param string $report  The report name
	 * @return array $columns  Array of column names as keys with description as value
	 */
	function CSVcolumns( $report )
	{
		$columns = array();
		
		switch( $report ) {
		case( 'arc_timetable_patterns' ):
			$columns['Arc Pattern ID'] = 'Arc Pattern ID or blank for a new pattern';
			$columns['Arc Day Type'] = 'Type of day in the pattern. If omitted will be derived.';
			$columns['Unique ID'] = 'User generated unique pattern ID if available';
			$columns['Period'] = 'Day period';
			$columns['StartTime'] = 'Period start time as hh:mm (24 hour clock)';
			$columns['EndTime'] = 'Period end time as hh:mm (24 hour clock)';
			$columns['ModelName'] = 'The timetable pattern name';
			$columns['StartDate'] = 'Start date and time of the pattern as yyyy-mm-ddThh:mm:ss for example 2011-07-28T14:02:00';
			$columns['EndDate'] = 'End date and time of the pattern as yyyy-mm-ddThh:mm:ss for example 2011-07-28T14:02:00';
			break;
			
		case( 'arc_timetable_instances' ):
			$columns['Arc Instance ID'] = 'Arc Instance ID or blank for a new pattern';
			$columns['Unique ID'] = 'User generated unique instance ID if available';
			$columns['Description'] = 'Full description of event';
			$columns['Event type'] = 'Type of event';
			$columns['Category'] = 'Short description of event';
			$columns['Start'] = 'Start date and time as yyyy-mm-ddThh:mm:ss for example 2011-07-28T14:02:00';
			$columns['End'] = 'End date and time as yyyy-mm-ddThh:mm:ss for example 2011-07-28T14:02:00';
			break;
			
		case( 'arc_timetable_classes' ):
			$columns['Arc Lesson ID'] = 'Arc Lesson ID for a given class or group at a given time';
			$columns['Arc Group ID'] = 'Arc Group ID of existing class';
			$columns['Unique ID'] = 'User generated unique class ID if available';
			$columns['Class'] = 'Class short name';
			$columns['Period'] = 'Day period';
			$columns['StartDate'] = 'Start date and time as yyyy-mm-ddThh:mm:ss for example 2011-07-28T14:02:00';
			$columns['EndDate'] = 'End date and time as yyyy-mm-ddThh:mm:ss for example 2011-07-28T14:02:00';
			$columns['Room'] = 'Room name or number';
			break;
			
		case( 'arc_timetable_members' ):
			$columns['Arc Person ID'] = 'Arc Person ID for the student';
			$columns['Arc Group ID'] = 'Arc Group ID';
			$columns['Unique Person ID'] = 'User generated unique person ID';
			$columns['Unique Class ID'] = 'User generated unique class ID';
			$columns['Group'] = 'Short class name';
			$columns['Start'] = 'Start date and time as yyyy-mm-ddThh:mm:ss for example 2011-07-28T14:02:00';
			$columns['End'] = 'End date and time as yyyy-mm-ddThh:mm:ss for example 2011-07-28T14:02:00';
			$columns['Title'] = 'Teacher\'s title (Mr, Mrs, Dr etc)';
			$columns['Forename'] = 'Teacher\'s Forename';
			$columns['Surname'] = 'Teacher\'s Surname';
			$columns['dob'] = 'Teacher\'s Date of birth as yyyy-mm-dd';
			$columns['Postcode'] = 'Teacher\'s Postcode';
			break;
		}
		
		return $columns;
	}
	
	
	// #####  Private functions to achieve the primary goals (the public functions above)  #####
	
	/**
	 * Import the timetable patterns.
	 * Won't work out the format string or start day. These are worked out
	 * at the point the day details have been imported.
	 */
	function _importPatterns( $xml )
	{
		$patterns = array();
		$insertVals = array();
		$updateVals = array();
		
		while( ($data = $xml->next('record')) !== false ) {
			$data = $this->_rawToObjects( 'arc_timetable_patterns', $data );
			if( !is_null($data['pattern']) && !isset($patterns[$data['pattern']->ext_model_id]) ) {
				$patterns[$data['pattern']->ext_model_id] = true; // avoid repetition
				$pattern = $data['pattern'];
				$isNewPattern = $pattern->_newId;
				unset( $pattern->_newId );
				
				if( $isNewPattern ) {
					$insertVals[] = $pattern;
				}
				else {
					$updateVals[] = $pattern;
				}
			}
		}
		
		ApotheosisLibDb::insertList( '#__apoth_tt_patterns', $insertVals );
		ApotheosisLibDb::updateList( '#__apoth_tt_patterns', $updateVals, array('id') );
		
		timer( 'imported timetable patterns ('.count($insertVals).' inserts, '.count($updateVals).' updates)' );
	}
	
	/**
	 * Imports the day details.
	 * Works out the format string and the start day for the patterns that don't have them too.
	 */
	function _importDayDetails( $xml )
	{
		$this->_setExistingPeriods(); // the existing periods, Arc-indexed
		$newPeriods = array(); // will hold any new periods, grouped by pattern id
		$updatePeriodVals = array();
		$updatePeriodVals2 = array();
		
		while( ($data = $xml->next('record')) !== false ) {
			$data = $this->_rawToObjects( 'arc_timetable_patterns', $data );
			if( !is_null($data['period']) ) {
				$period = $data['period'];
				$isNewPeriod = $period->_newId;
				$pdId = $period->_id;
				unset( $period->_id );
				
				if( $isNewPeriod ) {
					$newPeriods[$period->pattern][] = clone $period;
				}
				else {
					$this->existingPeriods[$pdId]->used = true;
					unset( $period->_newId );
					$updatePeriodVals[] = $period;
				}
			}
		}
		
		if( $this->_complete ) {
			$now = date( 'Y-m-d H:i:s' );
			foreach( $this->existingPeriods as $k=>$v ) {
				if( !isset($v->used) || !$v->used ) {
					$t = new stdClass();
					$t->pattern = $v->pattern;
					$t->day_type = $v->day_type;
					$t->day_section = $v->day_section;
					$t->src = $this->srcId;
					$t->valid_to = $now;
					$updatePeriodVals2[] = $t;
					
					// indicate that patterns having truncated periods need re-initialising (as a few lines below)
					if( !isset($newPeriods[$t->pattern]) ) {
						$newPeriods[$t->pattern] = array();
					}
				}
			}
		}
		
		ApotheosisLibDb::updateList( '#__apoth_tt_daydetails', $updatePeriodVals, array('pattern', 'day_type', 'day_section') );
		ApotheosisLibDb::updateList( '#__apoth_tt_daydetails', $updatePeriodVals2, array('pattern', 'day_type', 'day_section') );
		
		// if new period added anywhere work out day types and recalculate pattern format
		if( !empty($newPeriods) ) {
			$this->_setExistingPeriods( true, false ); // ALL the existing periods, Arc-indexed
			$this->_setExistingPatterns();
			$updatePatterns = array();
			$unscheduledDayType = ApotheosisLibCycles::cycleDayToDayType( -1 );
			$insertVals = array();
			$updateVals = array();
			
			foreach( $newPeriods as $patId=>$periods ) {
				$formatStr = '';
				$dupeSections = 0;
				$maxWeeksNeeded = 0;
				$usedDayTypes = array();
				
				// amalgamate the existing and new periods for this pattern into one list
				// grouped by day number (ISO), start time, then section(period) name
				$patternPeriods = array();
				foreach( $this->existingPeriods as $exPdId=>$exPeriod ) {
					if( $exPeriod->pattern == $patId ) {
						$dayNum = $this->_getDayNum( $exPeriod->day_section );
						$patternPeriods[$dayNum][$exPeriod->start_time][$exPeriod->day_section][] = $exPeriod;
					}
				}
				foreach( $periods as $period ) {
					$dayNum = $this->_getDayNum( $period->day_section );
					$patternPeriods[$dayNum][$period->start_time][$period->day_section][] = $period;
				}
				
				// put everything in order and see how many weeks long the pattern needs to be
				// also note which day types are used so we can avoid conflicts when creating new ones later
				$maxWeeksNeeded = 1;
				ksort( $patternPeriods );
				foreach( $patternPeriods as $dayNum=>$times ) {
					ksort( $patternPeriods[$dayNum] );
					foreach( $times as $time=>$sections) {
						ksort( $patternPeriods[$dayNum][$time] );
						// numCandidates is a count of how many things are down to happen
						// at the same time on the same day (so must be in different weeks)
						$numCandidates = 0;
						foreach( $sections as $section=>$candidates ) {
							foreach( $candidates as $candidate ) {
								$numCandidates++;
								if( !empty($candidate->day_type) ) {
									$usedDayTypes[$candidate->day_type] = true;
								}
							}
						}
						$maxWeeksNeeded = max( $maxWeeksNeeded, $numCandidates );
					}
				}
				
				/* // the following describes (roughly) the plan for working out format strings and day types
				for as many weeks as are needed:
				{
					each day(ISO) contributes a set of times each with one day_section
					if there are mutiple day sections they are used in priority order:
						section sharing a day_type(arc) with the first encountered this day
						section with non-conflicting day_type (existing if no prior, empty if prior with no match)
						section without day_type (newbie)
						first section in list
					if any section has a day type, the first one is applied to all, else a new one is created (avoiding conflicts) and applied to all
					Empty days (ones with no remaining times) contribute a "-1" day (non-scheduled day)
					
					the day type character for that day is added to the format string
				}
				*/
				
				$nextDayTypeNum = 1;
				// generate format string and assign day types to day sections
				// - format to be as many weeks long as indicated
				for( $weekNum = 0; $weekNum < $maxWeeksNeeded; $weekNum++ ) {
					// - each week has 7 days, each of which may contribute
					for( $dayNum = 1; $dayNum <= 7; $dayNum++ ) {
						$dayType = null; // no day type determined so far
						$dayPeriods = array();
						// - each day either has a bunch of times or is unscheduled
						if( isset($patternPeriods[$dayNum]) ) {
							foreach( $patternPeriods[$dayNum] as $time=>$sections ) {
								$opt = 9999; // just a big number to indicate no match found yet
								// find a period that has the best-matching day type
								foreach( $sections as $section=>$candidates ) {
									foreach( $candidates as $cId=>$candidate ) {
										// - exists and matches the current
										if( !is_null($candidate->day_type) && !is_null($dayType) && $candidate->day_type == $dayType ) {
											$opt1_section = $section;
											$opt1_candidateId = $cId;
											$opt = 1;
											break 2; // found best match so stop looking
										}
										// - exists and can define current
										if( $opt > 2 && !is_null($candidate->day_type) && is_null($dayType) ) {
											$opt2_section = $section;
											$opt2_candidateId = $cId;
											$opt = 2;
											$dayType = $candidate->day_type; // define current day type for match checks
										}
										// - doesn't exist
										if( $opt > 3 && !isset($candidate->day_type) ) {
											$opt3_section = $section;
											$opt3_candidateId = $cId;
											$opt = 3;
										}
										// - doesn't matter just use the first period
										if( !isset($opt) ) {
											$opt4_section = $section;
											$opt4_candidateId = $cId;
											$opt = 4;
										}
									}
								}
								if( $opt < 9999 ) {
									$dayPeriods[] = $patternPeriods[$dayNum][$time][${'opt'.$opt.'_section'}][${'opt'.$opt.'_candidateId'}];
									unset( $patternPeriods[$dayNum][$time][${'opt'.$opt.'_section'}][${'opt'.$opt.'_candidateId'}] );
								}
							}
							
							// having got a bunch of periods for the day, apply the same day type to them all
							if( !empty($dayPeriods) ) {
								if( is_null($dayType) ) {
									// ... take a moment to find out what day_type should be applied if not yet defined
									// will get here if all periods for the day are type-less, eg on initial import
									do{
										$dayType = ApotheosisLibCycles::cycleDayToDayType($nextDayTypeNum++);
									} while( isset($usedDayTypes[$dayType]) && ($dayType != '.') );
									if( $dayType == '.' ) { break 3; }
									$usedDayTypes[$dayType] = true;
								}
								foreach( $dayPeriods as $dpK=>$dpV ) {
									$prevDayType = $dpV->day_type;
									$dpV->day_type = $dayType;
									if( isset( $dpV->_newId ) ) {
										unset( $dpV->_newId );
										$insertVals[] = clone $dpV;
									}
									elseif( ($dpV->day_type != $prevDayType) && ($dpV->src == $this->srcId) ) {
										$updateVals[] = clone $dpV;
									}
								}
							}
						}
						
						// empty / un-fillable days contribute an unscheduled day
						if( is_null($dayType) ) {
							$dayType = $unscheduledDayType;
						}
						
						// add the day_type character to the format string
						$formatStr .= $dayType;
					} // end of per-day "for" loop
				} // end of weekly loop
				
				// with all the weeks parsed, and the format string made, compare / update the pattern
				$pattern = $this->existingPatterns[$patId];
				if( ($pattern->format != $formatStr) || ($pattern->start_day != 1) ) {
					$pattern->format = $formatStr;
					$pattern->start_day = 1;
					$updatePatterns[] = $pattern;
				}
				
			} // end of per-pattern loop
			
			// Having recalculated the format, and updated any day types that needed it, write to db
			ApotheosisLibDb::updateList( '#__apoth_tt_patterns', $updatePatterns, array('id') );
			
			ApotheosisLibDb::insertList( '#__apoth_tt_daydetails', $insertVals );
			ApotheosisLibDb::updateList( '#__apoth_tt_daydetails', $updateVals, array('pattern', 'day_type', 'day_section') );
			
			timer( 'imported new periods ('.count($insertVals).' inserts, '.count($updateVals).' updates)' );
		}
		
		timer( 'imported day details' );
	}
	
	/**
	 * Import pattern instances.
	 * Calculates the appropriate start dates for our patterns
	 * Years always have a full pattern start in the first full week
	 * Holidays effectively pause the cycling of multi-week patterns. (leave in week A, come back in week B)
	 */
	function _importInstances( $xml )
	{
		$state      = array();
		$insertVals = array();
		$updateVals = array();
		$updateKeys = array();
		$instances  = array();
		$years      = array();
		$nextNewId  = -1;
		
		// get existing patterns
		$this->_setExistingPatterns( true, false );
		// add a numeric start time
		foreach( $this->existingPatterns as $k=>$v ) {
			$this->existingPatterns[$k]->startTime = strtotime( $v->valid_from );
		}
		
		// get existing instances
		$this->_setExistingInstances( true );
		// create a model of the current state of the instances
		foreach( $this->existingInstances as $k=>$v ) {
			$state[$v->start.'~'.$v->pattern] = $v;
		}
		ksort( $state );
		
		// Work out the years first so we have context for terms / holidays
		while( ($data = $xml->next('record')) !== false ) {
			$instance = $this->_rawToObjects( 'arc_timetable_instances', $data );
			
			// note any year patterns
			if( $instance->_type == 'Academic Year' ) {
				$years[$instance->start] = 0;
			}
			
			// only process the instance if we found a pattern for this event.
			if( !is_null($instance->pattern) ) {
				$instances[] = $instance;
			}
		}
		
		// Do all instances in order so we progressively build context (in "state") for later instances
		$instance = reset( $instances );
		while( $instance !== false ) {
			$pattern = $this->existingPatterns[$instance->pattern];
			$isNew = $instance->_newId;
			$type = $instance->_type;
			$category = $instance->_category;
			if( $isNew ) {
				$instance->id = $nextNewId--;
			}
			
			unset( $instance->_newId );
			unset( $instance->_type );
			unset( $instance->_category );
			
			switch( $type ) {
			case( 'Term' ):
				// Fill in missing data
				if( is_null($instance->start_index) ) {
					// find which academic year this falls in
					reset( $years );
					$best = key( $years );
					foreach( $years as $date=>$count ) {
						if( $date > $instance->start ) {
							break;
						}
						else {
							$best = $date;
						}
					}
					$years[$best]++;
					
					// is this the first term we know of in this academic year?
					if( $years[$best] == 1 ) {
						// Start of term coincides with start of year
						$instance->start_index = $this->_getYearStartIndex( $instance->start, $pattern );
					}
					else {
						$instance->start_index = $this->_getInstanceStartIndex( $instance->start, $pattern, $state );
					}
				}
				if( is_null( $instance->description ) ) {
					$instance->description = $category;
				}
				if( is_null( $instance->description_short ) ) {
					$instance->description_short = substr( $instance->description, 0, 3 );
				}
				break;
			
			case( 'Holiday' ):
			case( 'Half-Term Holiday' ):
				$instance->holiday = 1;
				
				// need to split terms which fully encompass the holiday event
				// try to find a term instance which completely encompasses the holiday
				
				$cur = end( $state );
				$term = null;
				do {
					if( is_object($cur)
					 && ($cur->start < $instance->start)
					 && (($cur->end  >= $instance->end) || (is_null($cur->end))) ) {
						$term = $cur;
						reset($state);
					}
				} while( ($cur = prev($state)) !== false );
				unset( $cur );
				
				if( is_null($term) ) {
					// This is therefore a holiday not in the middle of a term
					// so gets very much the same treatment as a new year
					
					// Fill in missing data
					if( is_null( $instance->start_index ) ) {
						$instance->start_index = $this->_getYearStartIndex( $instance->start, $pattern );
					}
					if( is_null( $instance->description ) ) {
						$instance->description = $category;
					}
					if( is_null( $instance->description_short ) ) {
						$instance->description_short = reset( explode(' ', $category) );
					}
				}
				else {
					// This is therefore a holiday during a term
					// which (if it's new) will necessitate splitting the term and starting it again
					// after the holiday but without breaking the pattern (as if holiday never existed) 
					
					// Fill in missing data on the holiday instance we have here
					if( is_null($instance->start_index) ) {
						$instance->start_index = $this->_getInstanceStartIndex( $instance->start, $pattern, $state );
					}
					$instance->description = $term->description.' '.$type;
					if( is_null( $instance->description_short ) ) {
						$instance->description_short = $term->description_short.' ('.substr($category, 0, 1).')';
					}
					
					// Only new mid-term holidays necessitate term split
					if( $isNew ) {
						// create a new term instance based on the old one...
						$new = clone( $term );
						
						// ... and modify the one we've now split
						$term->end = date( 'Y-m-d H:i:s', (strtotime($instance->start) - 1) );
						$term->description .= ' (pt 1)';
						$term->description_short .= ' (1)';
						
						$oldKey = $term->start.'~'.$term->pattern;
						if( isset( $state[$oldKey] ) && ( $term->end != $state[$oldKey]->end ) ) {
							if( $term->id < 0 ) {
								$insertVals[$term->id] = $term;
							}
							else {
								$updateKeys[$term->id] = $oldKey;
							}
						}
						$state[$oldKey] = $term;
						
						// ... then fill in the new one with appropriate start index etc
						// (must be done after the old term's end date is updated)
						$new->_newId = true;
						$new->_type = 'Term';
						$new->_category = $new->description;
						$new->start = date( 'Y-m-d H:i:s', (strtotime($instance->end) + 1) );
						$new->start_index = $this->_getInstanceStartIndex( $new->start, $pattern, $state );
						$new->description .= ' (pt 2)';
						$new->description_short .= ' (2)';
						
						$newKey = $new->start.'~'.$new->pattern;
						$state[$newKey] = $new;
						
						$instances[] = $new; // add the new bit of term to the instances
					}
				}
				break;
			
			default:
				$instance = next( $instances );
				continue 2;
				break;
			}
			
			// add to insert / update as appropriate
			if( $isNew ) {
				$instance->id = $nextNewId--;
				$insertVals[$instance->id] = $instance;
			}
			else {
				$cur = $this->existingInstances[$instance->id];
				if( $instance->description == $cur->description ) {
					// only update if the instance hasn't been changed by other parts of this process
					// (those parts change the description)
					$updateVals[$instance->id] = $instance;
				}
				if( $instance->start != $cur->start) {
					unset( $state[$cur->start.'~'.$cur->pattern] );
				}
			}
			// update state tracking
			$state[$instance->start.'~'.$instance->pattern] = $instance;
			ksort( $state );
			
			$instance = next( $instances );
		}
		
		ApotheosisLibDb::insertList( '#__apoth_tt_pattern_instances', $insertVals );
		ApotheosisLibDb::updateList( '#__apoth_tt_pattern_instances', $updateVals, array('id') );
		
		timer( 'imported term and holiday pattern instances ('.count($insertVals).' inserts, '.count($updateVals).' updates)' );
	}
	
	/**
	 * Find the ISO standard day number for a day / period name.
	 * 
	 * @param string $name  The day / period name to be parsed for day information
	 * @return int|null  1 = monday -> 7 = sunday, null = "could not find a day"
	 */
	function _getDayNum( $name )
	{
		// have pattern and day_section, need to work out day_type
		// start by finding the ISO standard day number for this day if it has one
		if(     stristr( $name, 'mo' ) ) { $retVal = 1; }
		elseif( stristr( $name, 'tu' ) ) { $retVal = 2; }
		elseif( stristr( $name, 'we' ) ) { $retVal = 3; }
		elseif( stristr( $name, 'th' ) ) { $retVal = 4; }
		elseif( stristr( $name, 'fr' ) ) { $retVal = 5; }
		elseif( stristr( $name, 'sa' ) ) { $retVal = 6; }
		elseif( stristr( $name, 'su' ) ) { $retVal = 7; }
		else{ $retVal = null; }
		return $retVal;
	}
	
	function _getYearStartIndex( $start, $pattern )
	{
		// The first monday in the instance should correspond to the first monday in the pattern format
		$startDay = date( 'N', strtotime($start) );
		
		// find position of first monday in the pattern format and the instance
		$patMon = ( ($pattern->start_day == 1) ? 0 : (8 - $pattern->start_day) ); // remember 1 == monday
		$insMon = ( ($startDay == 1) ? 0 : (8 - $startDay) );
		
		// by comparing the distance to first monday for pattern and instance we see
		// how far into the pattern our instance starts
		$retVal = $patMon - $insMon;
		
		// if we start before the start then wrap around to the end
		if( $retVal < 0 ) {
			$retVal += strlen( $pattern->format );
		}
		
		return $retVal;
	}
	
	/**
	 * make the start index for a non-year instance be for:
	 * the first DOW matching our start date a week after
	 * the last time we had that DOW in the previous non-holiday instance
	 * 
	 * @param string $start  The start date of the instance in question
	 * @param object $pattern  The pattern this is an instance of
	 * @param array $exists  The existing pattern instances in which to try to find some context
	 */
	function _getInstanceStartIndex( $start, $pattern, $exists )
	{
		$pLen = strlen( $pattern->format );
		
		// find out our DOW
		$d = date( 'N', strtotime($start) );
		$adjust = 0;
		
		// fudge it so that weekends don't count (else a 5-day 1/2 term really is ignored a little too much)
		if( $d == 7 ) { // term starts on a sunday
			$d = 1;
			$adjust = 1;
		}
		elseif( $d == 6 ) {// term starts on a saturday
			$d = 1;
			$adjust = 2;
		}
		else {
			$adjust = 0;
		}
		
		// find our previous non-holiday instance
		$cur = end( $exists );
		while( (($cur->start >= $start) || ($cur->holiday == 1) || ($cur->pattern != $pattern->id)) && ($cur !== false) ) {
			$cur = prev( $exists );
		}
		
		// if we have no previous cycle bodge some defaults in there
		if( $cur === false ) {
			$cur = new stdClass();
			$cur->end = $start;
			$cur->start = $start;
		}
		
		// find the date of the last occurence of our DOW in that instance
		$final = date( 'N', strtotime($cur->end) );
		$dif = $final - $d;
		if( $dif < 0 ) { $dif += 7; }
		$mostRecentD = date( 'Y-m-d H:i:s', strtotime($cur->end.' - '.$dif.' days') );
		
		// add 7 to that date's pattern index (wrapping if necessary) so we're in the next week
		$out = ApotheosisLibCycles::dateToCycleDay( $mostRecentD, $pattern, $cur ) + 7 - $adjust;
		unset( $cur->format );
		if( $out >= $pLen ) { $out -= $pLen; }
		
		return $out;
	}
	
	/**
	 * Import the timetable
	 */
	function _importTimetable( $xml )
	{
		$insertVals  = array();
		$updateVals  = array();
		$updateVals2 = array();
		
		$this->_setExistingClasses( true, false, 'shortname' );
		while( ($data = $xml->next('record')) !== false ) {
			$lesson = $this->_rawToObjects( 'arc_timetable_classes', $data );
			
			if( !is_null($lesson) ) {
				$isNew = $lesson->_newId;
				unset( $lesson->_newId );
				
				if( $isNew ) {
					$insertVals[] = $lesson;
				}
				else {
					$this->existingLessons[$lesson->id]->used = true;
					$updateVals[] = $lesson;
				}
			}
		}
		
		$this->_setExistingClasses( true );
		if( $this->_complete ) {
			$date = date( 'Y-m-d H:i:s' );
			$time = time();
			foreach( $this->existingLessons as $k=>$v ) {
				if( !isset($v->used) || ($v->used == false) && is_null($v->valid_to) ) {
					$cDate = ( isset( $this->existingClasses[$v->course] ) ? $this->existingClasses[$v->course][0]->end_date : null );
					
					if( is_null($cDate) ) {
						$v->valid_to = $date;
					}
					else {
						$cTime = strtotime( $cDate );
						$v->valid_to = ( ($cTime < $time) ? $cDate : $date );
					}
					$updateVals2[] = $v;
				}
			}
		}
		
		ApotheosisLibDb::insertList( '#__apoth_tt_timetable', $insertVals );
		ApotheosisLibDb::updateList( '#__apoth_tt_timetable', $updateVals,  array('id') );
		ApotheosisLibDb::updateList( '#__apoth_tt_timetable', $updateVals2, array('id') );
		
		timer( 'imported class instances ('.count($insertVals).' inserts, '.count($updateVals).' updates, '.count($updateVals2).' terminations)' );
	}
	
	/**
	 * Imports all the group membership data into our database
	 */
	function _importEnrolments( &$xml, $fromDate, $toDate )
	{
		$db = &JFactory::getDBO();
		$query = 'SELECT *'
			."\n".'FROM '.$db->nameQuote('#__apoth_cm_pastoral_map');
		$db->setQuery( $query );
		$existsPastoralMaps = $db->loadObjectList( 'course' );
		
		timer( 'before pre enrolment' );
		
		$this->_preEnrolment();
		
		timer( 'parsing data' );
		
		$first = true;
		while( ($enrolment = $xml->next('record')) !== false ) {
			$enrolments = $this->_rawToObjects( 'arc_timetable_members', $enrolment );
			
			foreach( $enrolments as $k=>$enrolment ) {
				$key = $enrolment->group_id.'~'.md5( serialize($enrolment) );
				if( !isset($this->_enrolments[$key]) ) {
					$this->_enrolments[$key] = clone $enrolment;
				}
				
				// if this is a pastoral group's class we need to do the pastoral enrolment too
				if( isset($existsPastoralMaps[$enrolment->group_id]) ) {
					$enrolment->group_id = $existsPastoralMaps[$enrolment->group_id]->pastoral_course;
					
					$pKey = $enrolment->group_id.'~'.md5( serialize($enrolment) );
					if( !isset($this->_enrolments[$pKey]) ) {
						$this->_enrolments[$pKey] = clone $enrolment;
					}
				}
			}
			
			if( count($this->_enrolments) >= $this->_maxLimit ) {
				$this->_mid_enrolment_a( $fromDate, $toDate, $first );
				$first = false;
				$this->_enrolments = array();
			}
		}
		
		timer( 'parsed data' );
		
		$this->_mid_enrolment_a( $fromDate, $toDate, $first );
		
		timer( 'after mid a' );
		
		$this->_mid_enrolment_b( $fromDate, $toDate );
		
		timer( 'after mid b' );
		
		$this->_postEnrolment();
		
		timer( 'after post' );
		// timer( false, false, 'print' );
	}
	
	
	// #####  Inserting enrolments involves ensuring that overlapping enrolments are merged  #####
	
	/**
	 * Do some database adjustments to ease the enrolment importing
	 */
	function _preEnrolment()
	{
		$db = &JFactory::getDBO();
		$db->setQuery( 'UPDATE jos_apoth_tt_group_members'
			."\n".'SET valid_to = "9999-01-01 23:59:00"'
			."\n".'WHERE valid_to IS NULL');
		$db->Query();
	}
	
	/**
	 * Undo the database adjustments performed above
	 */
	function _postEnrolment()
	{
		$db = &JFactory::getDBO();
		$db->setQuery( 'UPDATE jos_apoth_tt_group_members'
			."\n".'SET valid_to = NULL'
			."\n".'WHERE valid_to = "9999-01-01 23:59:00"');
		$db->Query();
	}
	
	function _mid_enrolment_a( $fromDate, $toDate, $fresh = true )
	{
		// put all the enrolments we've been given (valid for the specified date range) into a table
		if( $fresh ) {
			$db = &JFactory::getDBO();
			
			$db->setQuery( 'DROP TABLE IF EXISTS #__apoth_tmp_enrol_import;'
				."\n".'CREATE TABLE #__apoth_tmp_enrol_import LIKE #__apoth_tt_group_members;' );
			$db->queryBatch();
		}
		
		ApotheosisLibDb::insertList( '#__apoth_tmp_enrol_import', $this->_enrolments );
	}
		
	function _mid_enrolment_b( $fromDate, $toDate )
	{
		$db = &JFactory::getDBO();
		
		// Remove any duplicated enrolments from the incoming data, then add 'used' column
		$db->setQuery( 'CREATE TEMPORARY TABLE #__tmp_u AS'
			."\n".'SELECT *, COUNT(*) AS dupes'
			."\n".'FROM `jos_apoth_tmp_enrol_import`'
			."\n".'GROUP BY `group_id`, `person_id`, `role`, `valid_from`, `valid_to`;'
			."\n".''
			."\n".'ALTER TABLE #__tmp_u ADD INDEX (`id`);'
			."\n".''
			."\n".'DELETE i.*'
			."\n".'FROM #__tmp_u AS u'
			."\n".'INNER JOIN jos_apoth_tmp_enrol_import AS i'
			."\n".'   ON i.id != u.id'
			."\n".'  AND i.`group_id` = u.`group_id`'
			."\n".'  AND i.`person_id` = u.`person_id`'
			."\n".'  AND i.`role` = u.`role`'
			."\n".'  AND i.`valid_from` = u.`valid_from`'
			."\n".'  AND i.`valid_to` = u.`valid_to`;'
			."\n"
			."\n".'DROP TABLE #__tmp_u;'
			."\n"
			."\n".'ALTER TABLE #__apoth_tmp_enrol_import ADD `used` TINYINT( 1 ) NOT NULL DEFAULT \'0\';' );
		$db->queryBatch();
		
		$db->setQuery( 'ALTER TABLE #__apoth_tmp_enrol_import ADD `used` TINYINT( 1 ) NOT NULL DEFAULT \'0\';' );
		$db->queryBatch();
		
		// Mark as finished any student enrolments that aren't in our list to insert/update
		if( $this->_complete ) {
			$db->setQuery('CREATE TABLE tmp_enrol_missing AS'
				."\n".'SELECT gm.*, i.id AS id2'
				."\n".'FROM `jos_apoth_tmp_enrol_import` AS i'
				."\n".'RIGHT JOIN `jos_apoth_tt_group_members` AS gm'
				."\n".'   ON gm.group_id = i.group_id'
				."\n".'  AND gm.person_id = i.person_id'
				."\n".'  AND gm.is_admin = i.is_admin' // *** titikaka
				."\n".'  AND gm.is_teacher = i.is_teacher' // *** titikaka
				."\n".'  AND gm.is_student = i.is_student' // *** titikaka
				."\n".'  AND gm.is_watcher = i.is_watcher' // *** titikaka
				."\n".'WHERE gm.valid_from <= '.$db->Quote($toDate)
				."\n".'  AND gm.valid_to >= '.$db->Quote($fromDate).';'
				."\n".''
				."\n".'UPDATE `tmp_enrol_missing` AS m'
				."\n".'INNER JOIN jos_apoth_tt_group_members AS gm'
				."\n".'   ON gm.id = m.id'
				."\n".'SET gm.valid_to = '.$db->Quote( date('Y-m-d H:i:s', strtotime($fromDate) - 1) )
				."\n".'WHERE id2 IS NULL'
				."\n".'  AND gm.is_student = 1;' // *** titikaka
				."\n"
				."\n".'DROP TABLE tmp_enrol_missing;');
			$db->queryBatch();
		}
		
		// update any existing entries that overlap one of the given ones to have the same end date
		// flag the imported groups that lead to this change
		$db->setQuery( 'UPDATE `jos_apoth_tmp_enrol_import` AS i'
			."\n".'INNER JOIN `jos_apoth_tt_group_members` AS gm'
			."\n".'   ON gm.group_id = i.group_id'
			."\n".'  AND gm.person_id = i.person_id'
			."\n".'  AND gm.is_admin = i.is_admin' // *** titikaka
			."\n".'  AND gm.is_teacher = i.is_teacher' // *** titikaka
			."\n".'  AND gm.is_student = i.is_student' // *** titikaka
			."\n".'  AND gm.is_watcher = i.is_watcher' // *** titikaka
			."\n".'  AND gm.valid_from <= i.valid_to'
			."\n".'  AND gm.valid_to >= i.valid_from'
			."\n".'SET gm.valid_to = i.valid_to,'
			."\n".'    gm.valid_from = i.valid_from,'
			."\n".'    i.used = 1;');
		$db->query();
		timer( 'updated '.$db->getAffectedRows().' rows:');
		
		// remove any flagged entries from the imported groups
		$db->setQuery( 'DELETE FROM `jos_apoth_tmp_enrol_import`'
			."\n".'WHERE used = 1;'
			."\n"
			."\n".'ALTER TABLE `jos_apoth_tmp_enrol_import` DROP used;');
		$db->queryBatch();
		
		// insert all the remaining enrolment rows
		$db->setQuery( 'INSERT INTO `jos_apoth_tt_group_members`' // *** titikaka (sort of, see above queries)
			."\n".'SELECT'
			."\n".'null,'
			."\n".'group_id,'
			."\n".'person_id,'
			."\n".'role,'
			."\n".'is_admin,'
			."\n".'is_teacher,'
			."\n".'is_student,'
			."\n".'is_watcher,'
			."\n".'valid_from,'
			."\n".'valid_to'
			."\n".'FROM `jos_apoth_tmp_enrol_import`;');
		$db->query();
		timer( 'inserting '.$db->getAffectedRows().' rows:' );
		
		// work out overlaps so that each person/group is valid in only one row at any one time
		
		// We're only looking at things over the given date range, so to avoid massive joins let's work on a subset of the group memberships
		$query = 'DROP TABLE IF EXISTS `#__apoth_tmp_group_members`;'
			."\n".'CREATE TABLE `#__apoth_tmp_group_members` AS'
			."\n".'SELECT *'
			."\n".'FROM `jos_apoth_tt_group_members`'
			."\n".'WHERE valid_to >= '.$db->Quote($fromDate)
			."\n".'  AND valid_from <= '.$db->Quote($toDate).';'
			."\n"
			."\n".'ALTER TABLE `#__apoth_tmp_group_members`'
			."\n".'ADD PRIMARY KEY ( `id` ) ,'
			."\n".'ADD INDEX (`group_id`) ,'
			."\n".'ADD INDEX (`person_id`) ,'
			."\n".'ADD INDEX (`valid_from`) ,'
			."\n".'ADD INDEX (`valid_to`);';
		$db->setQuery($query);
		$db->queryBatch();
		
		$safety = 0;
		do {
			$affected = 0;
			
			// - set the valid_to date for a pupil's enrolment to the maximum valid_to date
			// - where one row ends later than another
			$query = 'DROP TABLE IF EXISTS tmp_gm_extended;'
				."\n".'CREATE TEMPORARY TABLE tmp_gm_extended AS'
				."\n".'SELECT gm.id, MAX( gm2.valid_to ) AS valid_to'
				."\n".'FROM `#__apoth_tmp_group_members` AS gm'
				."\n".'INNER JOIN `#__apoth_tmp_group_members` AS gm2'
				."\n".'   ON gm2.group_id = gm.group_id'
				."\n".'  AND gm2.person_id = gm.person_id'
				."\n".'  AND gm2.valid_from BETWEEN gm.valid_from AND gm.valid_to'
				."\n".'  AND gm2.valid_to > gm.valid_to'
				."\n".'WHERE gm.valid_to >= '.$db->Quote($fromDate)
				."\n".'  AND gm.valid_from <= '.$db->Quote($toDate)
				."\n".'GROUP BY gm.group_id, gm.person_id, gm.is_admin, gm.is_teacher, gm.is_student, gm.is_watcher, gm.valid_from, gm.valid_to;' // *** titikaka
				."\n".''
				."\n".'ALTER TABLE `tmp_gm_extended` ADD INDEX ( `id` );'
				."\n".''
				."\n".'UPDATE #__apoth_tmp_group_members AS gm'
				."\n".'INNER JOIN tmp_gm_extended AS e'
				."\n".'   ON e.id = gm.id'
				."\n".'SET gm.valid_to = e.valid_to;';
			$db->setQuery($query);
			$db->queryBatch();
			$affected += $db->getAffectedRows();
			
			// - set the valid_from date for a pupil's enrolment to the minimum valid_from date
			// - where one row starts earler than another
			$query = 'DROP TABLE IF EXISTS tmp_gm_extended;'
				."\n".'CREATE TEMPORARY TABLE tmp_gm_extended AS'
				."\n".'SELECT gm.id, MIN(gm2.valid_from) AS valid_from'
				."\n".'FROM `#__apoth_tmp_group_members` AS gm'
				."\n".'INNER JOIN `#__apoth_tmp_group_members` AS gm2'
				."\n".'   ON gm2.group_id = gm.group_id'
				."\n".'  AND gm2.person_id = gm.person_id'
				."\n".'  AND gm2.valid_from < gm.valid_from'
				."\n".'  AND gm2.valid_to BETWEEN gm.valid_from AND gm.valid_to'
				."\n".'WHERE gm.valid_to >= '.$db->Quote($fromDate)
				."\n".'  AND gm.valid_from <= '.$db->Quote($toDate)
				."\n".'GROUP BY gm.group_id, gm.person_id, gm.is_admin, gm.is_teacher, gm.is_student, gm.is_watcher, gm.valid_from, gm.valid_to;' // *** titikaka
				."\n".''
				."\n".'ALTER TABLE `tmp_gm_extended` ADD INDEX ( `id` );'
				."\n".''
				."\n".'UPDATE #__apoth_tmp_group_members AS gm'
				."\n".'INNER JOIN tmp_gm_extended AS e'
				."\n".'   ON e.id = gm.id'
				."\n".'SET gm.valid_from = e.valid_from;';
			$db->setQuery($query);
			$db->queryBatch();
			$affected += $db->getAffectedRows();
			
			// - set the valid_from and valid_to date for a pupil's enrolment to the minimum and maximum possible
			// - where one row completely encompasses another'
			$query = 'DROP TABLE IF EXISTS tmp_gm_extended;'
				."\n".'CREATE TEMPORARY TABLE tmp_gm_extended AS'
				."\n".'SELECT gm.id, MIN(gm2.valid_from) AS valid_from, MAX(gm2.valid_to) AS valid_to'
				."\n".'FROM `#__apoth_tmp_group_members` AS gm'
				."\n".'INNER JOIN `#__apoth_tmp_group_members` AS gm2'
				."\n".'   ON gm2.group_id = gm.group_id'
				."\n".'  AND gm2.person_id = gm.person_id'
				."\n".'  AND gm2.valid_from < gm.valid_from'
				."\n".'  AND gm2.valid_to > gm.valid_to'
				."\n".'WHERE gm.valid_to >= '.$db->Quote($fromDate)
				."\n".'  AND gm.valid_from <= '.$db->Quote($toDate)
				."\n".'GROUP BY gm.group_id, gm.person_id, gm.is_admin, gm.is_teacher, gm.is_student, gm.is_watcher, gm.valid_from, gm.valid_to;' // *** titikaka
				."\n".''
				."\n".'ALTER TABLE `tmp_gm_extended` ADD INDEX ( `id` );'
				."\n".''
				."\n".'UPDATE #__apoth_tmp_group_members AS gm'
				."\n".'INNER JOIN tmp_gm_extended AS e'
				."\n".'   ON e.id = gm.id'
				."\n".'SET gm.valid_from = e.valid_from, gm.valid_to = e.valid_to;';
			$db->setQuery($query);
			$db->queryBatch();
			$affected += $db->getAffectedRows();
			
			timer('stretch loop '.$safety.' finished with '.$affected.' affected rows');
			$safety ++;
		} while ( ($affected > 0) && ($safety < 5) );
		
		$query ='UPDATE #__apoth_tt_group_members AS gm' // *** titikaka (sort of, see above queries)
			."\n".'INNER JOIN #__apoth_tmp_group_members AS tmp'
			."\n".'   ON tmp.id = gm.id'
			."\n".'SET gm.valid_from = tmp.valid_from'
			."\n".'  , gm.valid_to = tmp.valid_to;'
			."\n"
			."\n".'DROP TABLE #__apoth_tmp_group_members;';
		$db->setQuery($query);
		$db->queryBatch();
		
		// delete duplicates
		$query = 'CREATE TEMPORARY TABLE tmp_enrol_dupes AS' // find duplicates within our working date range
			."\n".'SELECT gm1.*'
			."\n".'FROM `jos_apoth_tt_group_members` AS gm1'
			."\n".'INNER JOIN jos_apoth_tt_group_members AS gm2'
			."\n".'   ON gm2.`group_id`   = gm1.`group_id`'
			."\n".'  AND gm2.`person_id`  = gm1.`person_id`'
			."\n".'  AND gm2.`is_admin`   = gm1.`is_admin`' // *** titikaka
			."\n".'  AND gm2.`is_teacher` = gm1.`is_teacher`' // *** titikaka
			."\n".'  AND gm2.`is_student` = gm1.`is_student`' // *** titikaka
			."\n".'  AND gm2.`is_watcher` = gm1.`is_watcher`' // *** titikaka
			."\n".'  AND gm2.`valid_from` = gm1.`valid_from`'
			."\n".'  AND gm2.`valid_to`   = gm1.`valid_to`'
			."\n".'  AND gm2.`id` != gm1.`id`'
			."\n".'WHERE gm1.valid_to >= '.$db->Quote($fromDate)
			."\n".'  AND gm1.valid_from <= '.$db->Quote($toDate)
			."\n".'GROUP BY gm1.id;'
			."\n"
			."\n".'ALTER TABLE `tmp_enrol_dupes` ADD PRIMARY KEY ( `id` );'
			."\n"
			."\n".'CREATE TEMPORARY TABLE tmp_enrol_firsts AS' // set aside the first of each batch
			."\n".'SELECT MIN(`id`) AS id'
			."\n".'FROM `tmp_enrol_dupes`'
			."\n".'GROUP BY `group_id`, `person_id`, `is_admin`, `is_teacher`, `is_student`, `is_watcher`, `valid_from`, `valid_to` ASC;' // *** titikaka
			."\n"
			."\n".'ALTER TABLE `tmp_enrol_firsts` ADD PRIMARY KEY ( `id` );'
			."\n"
			."\n".'DELETE `tmp_enrol_dupes`.*'  // remove those firsts from the list (of ones to delete)
			."\n".'FROM `tmp_enrol_dupes`'
			."\n".'INNER JOIN `tmp_enrol_firsts` AS f'
			."\n".'   ON f.id = `tmp_enrol_dupes`.id;'
			."\n"
			."\n".'DELETE `jos_apoth_tt_group_members`.*'  // delete all remaining duplicates
			."\n".'FROM `jos_apoth_tt_group_members`'
			."\n".'INNER JOIN `tmp_enrol_dupes` AS d'
			."\n".'   ON d.id = `jos_apoth_tt_group_members`.id;';
		$db->setQuery( $query );
		$db->queryBatch();
		
		$db->setQuery( 'DROP TABLE IF EXISTS #__apoth_tmp_enrol_import;' );
		$db->query();
		$db->setQuery( 'DROP TABLE IF EXISTS #__apoth_tmp_enrol_import_updated;' );
		$db->query();
	}
}
?>