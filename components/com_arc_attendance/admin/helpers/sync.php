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
 * Attendance Sync Helper
 *
 * @author     Punnet - Arc Team <arc_developers@pun.net>
 * @package    Arc
 * @subpackage Attendance
 * @since      1.6.5
 */
class ArcSync_Attendance extends ArcSync
{
	/** @var int The most rows to import at a time - memory issues occur if this is too high*/
	var $_maxLimit = 5000;
	
	/**
	 * Import all data about attendance codes
	 * **** This appears to be slightly broken (brings in "\", duplicates a few marks)
	 *
	 * @param array $params  Values from the form used to originally add the job
	 * @param array $jobs  Array of jobs. Each job is an array with all that job's settings
	 */
	function importCodes( $params, $jobs )
	{
		// **** we may need to set and be sensitive to
		// **** $this->_complete = (bool)$params['complete'];
		// **** if not and same in other importer here then remove option from controller
		// **** and radio button from view
		
		$tablesArray = array( '#__apoth_att_physical_meaning', '#__apoth_att_school_meaning', '#__apoth_att_statistical_meaning', '#__apoth_att_codes' );
		ApotheosisLibDb::disableDBChecks( $tablesArray );
		
		timer( 'importing attendance codes' );
		
		// grab the data
		$j = $this->jobSearch( array( 'call'=>'arc_student_attendance' ), $jobs );
		$xml = $this->_loadReport( $jobs[$j], 'progressive' );
		
		$job = $jobs[$j];
		$attParams = array();
		$pList = explode( "\r\n", $job['params'] );
		foreach( $pList as $p ) {
			$parts = explode( '=', $p, 2 );
			$attParams[$parts[0]] = $parts[1];
		}
		timer( 'got handle on data' );
		
		// do importing
		$this->_importCodes( $xml, $attParams['Start'], $attParams['End'] );
		
		// clean up
		$xml->free();
		
		ApotheosisLibDb::enableDBChecks( $tablesArray );
		return true;
	}
	
	/**
	 * Import all data about attendance for pupils
	 *
	 * @param array $params  Values from the form used to originally add the job
	 * @param array $jobs  Array of jobs. Each job is an array with all that job's settings
	 */
	function importAttendance( $params, $jobs )
	{
		// **** we may need to set and be sensitive to
		// **** $this->_complete = (bool)$params['complete'];
		// **** if not and same in other importer here then remove option from controller
		// **** and radio button from view
		
		$tablesArray = array( '#__apoth_att_dailyatt', '#__apoth_att_dailyincidents' );
		ApotheosisLibDb::disableDBChecks( $tablesArray );
		
		timer( 'importing attendance data' );
		
		// grab the data
		$j = $this->jobSearch( array( 'call'=>'arc_student_attendance' ), $jobs );
		$xml = $this->_loadReport( $jobs[$j], 'progressive' );
		
		$job = $jobs[$j];
		$attParams = array();
		$pList = explode( "\r\n", $job['params'] );
		foreach( $pList as $p ) {
			$parts = explode( '=', $p, 2 );
			$attParams[$parts[0]] = $parts[1];
		}
		timer( 'got handle on data' );
		
		// do importing
		$this->_importAttendance( $xml, $attParams['Start'], $attParams['End'] );
		
		// clean up
		$xml->free();
		
		ApotheosisLibDb::enableDBChecks( $tablesArray );
		return true;
	}
	
	function _rawToObjects( $rpt, $r )
	{
		switch($rpt) {
		case( 'codes' ):
			$multi = $r->childData('multiple_id');
			$ids = explode( ',', $multi );
			if( $ids[1] != '0' ) {
				$obj = new stdClass();
				$obj->code   = $r->childData('mark');
				$obj->school_meaning      = $r->childData('school');
				$obj->statistical_meaning = $r->childData('statistical');
				
				// work out physical meaning
				if( (stristr($obj->school_meaning, 'present') != '')
				 && ($obj->statistical_meaning == 'Present') ) {
					$obj->physical_meaning = 'In for whole session';
				}
				elseif( stristr($obj->school_meaning, 'late') != '' ) {
					$obj->physical_meaning = 'Late for session';
				}
				elseif( false ) { // though this appears in our sample data, it isn't assigned to any mark
					$obj->physical_meaning = 'Left session early';
				}
				elseif( $obj->statistical_meaning == 'No mark' ) {
					$obj->physical_meaning = 'No mark for session';
				}
				else {
					$obj->physical_meaning = 'Out for whole session';
				}
				
			}
			break;
		
		case( 'attendance' ):
			$multi = $r->childData('multiple_id');
			$ids = explode( ',', $multi );
			if( $ids[1] != '0' ) {
				$obj = new stdClass();
				$obj->date          = $r->childData( 'date' );
				$obj->day_section   = $r->childData('am_pm');
				$obj->person_id     = $ids[0];
				$obj->course_id     = false;
				$obj->att_code      = $r->childData( 'mark' );
				$obj->last_modified = date( 'Y-m-d H:i:s' );
			}
			break;
		}
		return $obj;
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
		case( 'arc_student_attendance' ):
			$columns['Arc Person ID'] = 'Arc Person ID';
			$columns['Arc Group ID'] = 'Arc Group ID ';
			$columns['Unique ID'] = 'User generated unique person ID';
			$columns['Mark'] = 'The attendance mark';
			$columns['school'] = 'Mark description';
			$columns['statistical'] = 'Statistical meaning of the mark';
			$columns['date'] = 'Mark date and time as yyyy-mm-ddThh:mm:ss for example 2011-07-28T14:02:00';
			$columns['AM/PM'] = 'AM or PM';
			break;
		}
		
		return $columns;
	}
	
	/**
	 * Imports attendance codes
	 */
	function _importCodes( &$xml, $startDate, $endDate )
	{
		// get pre-existing data
		$db = &JFactory::getDBO();
		$query = 'SELECT CONCAT('.$db->nameQuote('code').', "~", '.$db->nameQuote('type').') AS '.$db->nameQuote('unique').', c.*'
			."\n".' FROM #__apoth_att_codes AS c'
			."\n".' WHERE '.ApotheosisLibDb::dateCheckSql( 'valid_from', 'valid_to', $startDate, $endDate );
		$db->setQuery( $query );
		$exists = $db->loadObjectList( 'unique' );
		
		$query = 'SELECT id, LOWER(meaning) AS meaning'
			."\n".' FROM #__apoth_att_physical_meaning'
			."\n".' ORDER BY id';
		$db->setQuery( $query );
		$physical = $db->loadObjectList( 'id' );
		foreach($physical as $k=>$v) {
			$physical[$k] = $v->meaning;
		}
		$maxPhysical = ( empty($physical) ? 0 : max(array_keys($physical)) );
		
		$query = 'SELECT id, LOWER(meaning) AS meaning'
			."\n".' FROM #__apoth_att_school_meaning'
			."\n".' ORDER BY id';
		$db->setQuery( $query );
		$school = $db->loadObjectList( 'id' );
		foreach($school as $k=>$v) {
			$school[$k] = $v->meaning;
		}
		$maxSchool = ( empty($school) ? 0 : max(array_keys($school)) );
		
		$query = 'SELECT id, LOWER(meaning) AS meaning'
			."\n".' FROM #__apoth_att_statistical_meaning'
			."\n".' ORDER BY id';
		$db->setQuery( $query );
		$statistical = $db->loadObjectList( 'id' );
		foreach($statistical as $k=>$v) {
			$statistical[$k] = $v->meaning;
		}
		$maxStatistical = ( empty($statistical) ? 0 : max(array_keys($statistical)) );
		
		// prepare data to be inserted / updated
		$params = JComponentHelper::getParams( 'com_arc_attendance' );
		$merge  = $params->get( 'att_mergeampm' );
		$mergeExtAm = $params->get( 'external_am_mark' );
		$mergeExtPm = $params->get( 'external_pm_mark' );
		$mergeInt   = $params->get( 'internal_mark' );
		
		$newPhysical    = array();
		$newSchool      = array();
		$newStatistical = array();
		$codes          = array();
		$order = 1;
		while( ($attendance = $xml->next('record')) !== false ) {
			$attendance = $this->_rawToObjects( 'codes', $attendance );
			
			if( !is_null($attendance) && !isset($codes[$attendance->code]) ) {
				// process it
				
				if( $merge ) {
					if( $attendance->code == $mergeInt ) {
						$mk = $mergeInt.'~pastoral';
						$attendance->school_meaning = ( isset($exists[$mk]) ? $school[$exists[$mk]->school_meaning] : 'Present' );
					}
					elseif( ($attendance->code == $mergeExtAm)
					     || ($attendance->code == $mergeExtPm) ) {
						$attendance->school_meaning = 'DO NOT USE';
					}
				}
				
				// physical
				$lPhys = strtolower($attendance->physical_meaning);
				if( (($k = array_search($lPhys, $physical)) === false)
				 && (($k = array_search($lPhys, $newPhysical)) === false) ) {
					$tmp = new stdClass();
					$tmp->id = ++$maxPhysical;
					$tmp->meaning = $attendance->physical_meaning;
					$newPhysical[] = $tmp;
					$physical[$tmp->id] = $lPhys;
					$attendance->physical_meaning = $tmp->id;
				}
				else {
					$attendance->physical_meaning = $k;
				}
				
				// school
				$lScho = strtolower($attendance->school_meaning);
				if( (($k = array_search($lScho, $school)) === false)
				 && (($k = array_search($lScho, $newSchool)) === false) ) {
					$tmp = new stdClass();
					$tmp->id = ++$maxSchool;
					$tmp->meaning = $attendance->school_meaning;
					$newSchool[] = $tmp;
					$school[$tmp->id] = $lScho;
					$attendance->school_meaning = $tmp->id;
				}
				else {
					$attendance->school_meaning = $k;
				}
				
				// statistical
				$lStat = strtolower($attendance->statistical_meaning);
				if( (($k = array_search($lStat, $statistical)) === false)
				 && (($k = array_search($lStat, $newStatistical)) === false) ) {
					$tmp = new stdClass();
					$tmp->id = ++$maxStatistical;
					$tmp->meaning = $attendance->statistical_meaning;
					$newStatistical[] = $tmp;
					$statistical[$tmp->id] = $lStat;
					$attendance->statistical_meaning = $tmp->id;
				}
				else {
					$attendance->statistical_meaning = $k;
				}
				
				$codes[$attendance->code] = $attendance;
				$unique = $attendance->code.'~normal';
				
				// are we going to update, insert, or ignore this attendance code?
				$end = date( 'Y-m-d H:i:s', (strtotime($startDate) - 1) );
				if( isset($exists[$unique]) ) {
					$e = &$exists[$unique];
					unset( $e->unique );
					if( ($e->school_meaning      != $attendance->school_meaning)
					 || ($e->statistical_meaning != $attendance->statistical_meaning)
					 || ($e->physical_meaning    != $attendance->physical_meaning) ) {
						$e->valid_to = $end;
						$updateVals[] = $e;
						
						$attendance->is_common        = $e->is_common;
						$attendance->apply_all_day    = $e->apply_all_day;
						$attendance->order_id         = $e->order_id;
						$attendance->image_link       = $e->image_link;
						$attendance->type             = $e->type;
						$attendance->valid_from       = $startDate;
						$attendance->valid_to         = null;
						$insertVals[] = $attendance;
					}
					elseif( $e->valid_from > $startDate ) {
						// use direct queries rather than updatelist as am updating PK col in second,
						// and affecting potentially several rows (none of which are already fetched) in first
						$query = 'UPDATE '.$db->nameQuote( '#__apoth_att_codes' )
							."\n".' SET '.$db->nameQuote( 'valid_to' ).' = '.$db->Quote($end)
							."\n".' WHERE '.$db->nameQuote( 'code' ).' = '.$db->Quote($e->code)
							."\n".'   AND '.$db->nameQuote( 'type' ).' = '.$db->Quote($e->type)
							."\n".'   AND '.$db->nameQuote( 'valid_to' ).' IS NOT NULL'
							."\n".'   AND '.$db->nameQuote( 'valid_to' ).' > '.$db->Quote($end);
						$db->setQuery( $query );
						$db->query();
						
						$query = 'UPDATE '.$db->nameQuote( '#__apoth_att_codes' )
							."\n".' SET '.$db->nameQuote( 'valid_from' ).' = '.$db->Quote($startDate)
							."\n".' WHERE '.$db->nameQuote( 'code' ).' = '.$db->Quote($e->code)
							."\n".'   AND '.$db->nameQuote( 'type' ).' = '.$db->Quote($e->type)
							."\n".'   AND '.$db->nameQuote( 'valid_from').' = '.$db->Quote($e->valid_from);
						$db->setQuery( $query );
						$db->query();
					}
					unset($e);
				}
				else {
					$attendance->is_common        = 0;
					$attendance->apply_all_day    = 0;
					$attendance->order_id         = $order++;
					$attendance->image_link       = null;
					$attendance->type             = 'normal';
					$attendance->valid_from       = $startDate;
					$attendance->valid_to         = null;
					$insertVals[] = $attendance;
				}
			}
		}
		
		ApotheosisLibDb::insertList( '#__apoth_att_physical_meaning', $newPhysical );
		ApotheosisLibDb::insertList( '#__apoth_att_school_meaning', $newSchool );
		ApotheosisLibDb::insertList( '#__apoth_att_statistical_meaning', $newStatistical );
		
		ApotheosisLibDb::insertList( '#__apoth_att_codes', $insertVals );
		ApotheosisLibDb::updateList( '#__apoth_att_codes', $updateVals, array('id') );
	}
	
	/**
	 * Import the attendance data for each student
	 */
	function _importAttendance( $xml, $startDate, $endDate )
	{
//		timer( 'starting _importAttendance');
		$db = &JFactory::getDBO();
		$query = 'SELECT CONCAT( '.$db->nameQuote('pattern').', "~", '.$db->nameQuote('day_type').', "~", IF(('.$db->nameQuote('start_time').' < "12:00:00"), "AM", "PM") ) AS '.$db->nameQuote('unique')
			.', d.'.$db->nameQuote('pattern')
			.', d.'.$db->nameQuote('day_type')
			.', d.'.$db->nameQuote('day_section')
			."\n".' FROM #__apoth_tt_daydetails AS d'
			."\n".' WHERE '.ApotheosisLibDb::dateCheckSql( 'valid_from', 'valid_to', $startDate, $endDate )
			."\n".'   AND '.$db->nameQuote('statutory').' = '.$db->Quote(1);
		$db->setQuery( $query );
		$sections = $db->loadObjectList( 'unique' );
//		timer( 'got sections' );
		
		$query = 'SELECT '.$db->nameQuote('id')
			.', '.$db->nameQuote( 'ext_person_id' )
			."\n".' FROM '.$db->nameQuote( '#__apoth_ppl_people' ).'';
		$db->setQuery( $query );
		$existsPeople = $db->loadObjectList( 'ext_person_id' );
		
		$params = JComponentHelper::getParams( 'com_arc_attendance' );
		$merge  = $params->get( 'att_mergeampm' );
		$mergeExtAm = $params->get( 'external_am_mark' );
		$mergeExtPm = $params->get( 'external_pm_mark' );
		$mergeInt   = $params->get( 'internal_mark' );
//		timer( 'got params' );
		$this->_importAttendance_data_pre();
		
		$count = 0;
		$insertVals = array();
		
		while( ($attendance = $xml->next('record')) !== false ) {
			$attendance = $this->_rawToObjects( 'attendance', $attendance );
			$attendance->date = $this->_cleanDate( $attendance->date );
			$attendance->person_id = $existsPeople[$attendance->person_id]->id;
			
			if( $merge
			 && (($attendance->att_code == $mergeExtAm) || ($attendance->att_code == $mergeExtPm)) ) {
				$attendance->att_code = $mergeInt;
			}
			
			$p = ApotheosisLibCycles::getPatternByDate( $attendance->date );
			$attendance->day = ApotheosisLibCycles::dateToCycleDay( $attendance->date );
			$attendance->day_type = ApotheosisLibCycles::dateToDayType( $attendance->date );
			$attendance->pattern = $p->id;
			$key = $attendance->pattern.'~'.$attendance->day_type.'~'.$attendance->day_section;
			
			if( isset($sections[$key]) && !is_null($attendance->person_id) ) {
				$attendance->day_section = $sections[$key]->day_section;
				
				$insertVals[] = $attendance;
				$count++;
			}
			
			if( $count >= $this->_maxLimit ) {
				$count = 0;
				ApotheosisLibDb::insertList( '#__apoth_tmp_simsatt', $insertVals );
				$insertVals = array();
			}
		}
//		timer( 'read xml file' );
		
		ApotheosisLibDb::insertList( '#__apoth_tmp_simsatt', $insertVals );
//		timer( 'inserted list' );
		
		$query = 'DELETE FROM #__apoth_tmp_simsatt'
			."\n".' WHERE att_code IN (\'*\', \'-\', \'#\')';
		$db->setQuery( $query );
		$db->query();

		$query = 'ALTER IGNORE TABLE `#__apoth_tmp_simsatt`'
			."\n".' ADD PRIMARY KEY ( `date` , `day_section` , `person_id` , `course_id` )';
		$db->setQuery( $query );
		$db->query();
		
		$query = 'UPDATE `#__apoth_tmp_simsatt` AS sa'
			."\n".' INNER JOIN `jos_apoth_ppl_people` AS p'
			."\n".'    ON p.id = sa.person_id'
			."\n".' INNER JOIN `jos_apoth_tt_timetable` AS t'
			."\n".'    ON t.pattern = sa.pattern'
			."\n".'   AND t.day = sa.day'
			."\n".'   AND t.day_section = sa.day_section'
			."\n".'   AND t.valid_from < sa.date'
			."\n".'   AND ((t.valid_to > sa.date) OR (t.valid_to IS NULL))'
			."\n".' INNER JOIN `jos_apoth_tt_group_members` AS gm'
			."\n".'    ON gm.group_id = t.course'
			."\n".'   AND gm.person_id = p.id'
			."\n".'   AND gm.is_student = 1' // *** titikaka
			."\n".'   AND gm.valid_from <= sa.date'
			."\n".'   AND ((gm.valid_to >= sa.date) OR (gm.valid_to IS NULL))'
			."\n".' SET sa.course_id = gm.group_id';
		$db->setQuery( $query );
		$db->query();
//		timer( 'updated tmp_simsatt' );
		
		$query = 'DELETE FROM #__apoth_tmp_simsatt'
			."\n".' WHERE course_id = 0';
		$db->setQuery( $query );
		$db->query();
		
		// Finally remove entries with no change, then do the updates and insertions to the dailyatt table
		// Deletes
		$query = 'DELETE #__apoth_tmp_simsatt'
			."\n".' FROM #__apoth_att_dailyatt AS da'
			."\n".' INNER JOIN #__apoth_tmp_simsatt'
			."\n".'    ON da.date = #__apoth_tmp_simsatt.date'
			."\n".'   AND da.day_section = #__apoth_tmp_simsatt.day_section'
			."\n".'   AND da.person_id = #__apoth_tmp_simsatt.person_id'
			."\n".'   AND da.course_id = #__apoth_tmp_simsatt.course_id'
			."\n".'   AND da.att_code = #__apoth_tmp_simsatt.att_code';
		$db->setQuery( $query );
		$db->query();
//		timer( 'ignored '.$db->getAffectedRows().' unchanged entries' );
		
		// Updates
		$query = 'UPDATE #__apoth_att_dailyatt AS da'
			."\n".' INNER JOIN #__apoth_tmp_simsatt AS tmp'
			."\n".'    ON da.date = tmp.date'
			."\n".'   AND da.day_section = tmp.day_section'
			."\n".'   AND da.person_id = tmp.person_id'
			."\n".'   AND da.course_id = tmp.course_id'
			."\n".'   AND da.att_code != tmp.att_code'
			."\n".' SET da.att_code = tmp.att_code,'
			."\n".'     da.last_modified = tmp.last_modified,'
			."\n".'     tmp.is_new = 0;';
		$db->setQuery( $query );
		$db->query();
//		timer( 'updated '.$db->getAffectedRows().' modified entries' );
		
		// Inserts
		$query = 'INSERT INTO #__apoth_att_dailyatt'
			."\n".' SELECT `date`, `day_section`, `person_id`, `course_id`, `att_code`, `last_modified`'
			."\n".' FROM #__apoth_tmp_simsatt AS da'
			."\n".' WHERE is_new = 1';
		$db->setQuery( $query );
		$db->query();
//		timer( 'inserted '.$db->getAffectedRows().' new entries' );
		
		$this->_importAttendance_data_post();
//		timer( false, false, 'print' );
	}
	
	/**
	 * Setup required before doing attendance data import
	 * creates a tmp table for temporary storage of the (fairly) raw data from SIMS,
	 */
	function _importAttendance_data_pre()
	{
		$db = &JFactory::getDBO();
		$query = 'DROP TABLE IF EXISTS #__apoth_tmp_simsatt;'
			."\n".'CREATE TABLE `#__apoth_tmp_simsatt` ('
			."\n".' `date` DATE NOT NULL ,'
			."\n".' `pattern` INT NOT NULL,'
			."\n".' `day` TINYINT( 3 ) NOT NULL,'
			."\n".' `day_type` CHAR( 1 ) NOT NULL,'
			."\n".' `day_section` VARCHAR( 30 ),'
			."\n".' `person_id` VARCHAR( 30 ) NOT NULL,'
			."\n".' `course_id` INT,'
			."\n".' `att_code` VARCHAR( 1 ) NOT NULL, '
			."\n".' `last_modified` DATETIME NOT NULL ,'
			."\n".' `is_new` TINYINT( 1 ) DEFAULT 1,'
			."\n".' INDEX ( `pattern` ),'
			."\n".' INDEX ( `day_type` ),'
			."\n".' INDEX ( `day_section` ),'
			."\n".' INDEX ( `course_id` ),'
			."\n".' INDEX ( `person_id` )'
			."\n".' ) ENGINE = MyISAM;';
		$db->setQuery( $query );
		$db->queryBatch();
	}
	
	/**
	 * Clear up all the temporary and holding tables we just used
	 */
	function _importAttendance_data_post()
	{
		$db = JFactory::getDBO();
		$query = 'DROP TABLE IF EXISTS #__apoth_tmp_simsatt';
		$db->setQuery( $query );
		$db->query();
	}
}
?>