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
 * Data Access Helper
 *
 * @author		lightinthedark <code@lightinthedark.org.uk>
 * @package		Apotheosis
 * @subpackage Attendance
 * @since 0.1
 */
class ApotheosisAttendanceData extends JObject
{
	
	/**
	 * Finds the attendance percentage of the given student to the given group in the given period of time
	 * **** Currently pays no attention to the $period, just pulls info from fixed date
	 */
	function getAttendancePercent( $period, $students, $group = NULL, $start = NULL, $end = NULL, $single = NULL )
	{
		$db = &JFactory::getDBO();
		static $checked = array();
		
		// *** all options other than "fixed" are more suggestions than useful options
		switch($period) {
		case('year'):
			$start = $db->Quote( date('Y-m-d H:i:s', strtotime('-1 year')) );
			$end   = $db->Quote( date('Y-m-d H:i:s') );
			break;
		
		default:
			if( is_null($start) ) { $start = date('Y-m-d H:i:s'); }
			if( is_null($end)   ) { $end   = date('Y-m-d H:i:s'); }
			// then continue to make the dates quoted
		
		case('fixed'):
			$start = $db->Quote( date('Y-m-d H:i:s', strtotime($start)) );
			$end   = $db->Quote( date('Y-m-d H:i:s', strtotime($end)) );
			break;
		}
		
		// determine which (if any) students need data loaded
		if( !is_array($students) ) {
			$students = array( $students );
		}
		foreach( $students as $k=>$v ) {
			if( !isset($checked[$period][$v][$group]) ) {
				$studentsQuot[$k] = $db->Quote($v);
				$checked[$period][$v][$group] = '--';
			}
		}
		
		// load data for the students we determined we need
		if( !empty( $studentsQuot ) ) {
			$studentsStr = implode( ', ', $studentsQuot );
			$dateSql = '   AND da.date < '.$end.' AND da.date >= '.$start;
			
			if( is_null($group) ) {
				$joinStr = ' INNER JOIN #__apoth_tt_pattern_instances AS pi'
					."\n".'    ON pi.start < da.`date`'
					."\n".'   AND (pi.end > da.`date` OR pi.end IS NULL)'
					."\n".' INNER JOIN #__apoth_tt_daydetails AS dd'
					."\n".'    ON dd.pattern = pi.pattern'
					."\n".'   AND dd.day_section = da.day_section'
					."\n".'   AND dd.statutory = 1';
			}
			else {
				$joinStr = "\n".'   AND c.id = '.$db->Quote($group);
			}
			
			$query = 'SELECT COUNT(*) AS count, acm.id, acm.meaning, da.person_id'
				."\n".'FROM #__apoth_att_dailyatt AS da'
				."\n".'INNER JOIN #__apoth_tt_group_members AS gm'
				."\n".'   ON gm.group_id = da.course_id'
				."\n".'  AND gm.person_id = da.person_id'
				."\n".'  AND gm.valid_from < da.date'
				."\n".'  AND (gm.valid_to > da.date OR gm.valid_to IS NULL)'
				."\n".'INNER JOIN #__apoth_cm_courses AS c'
				."\n".'   ON c.id = da.course_id'
				."\n".'  AND c.deleted = 0'
				."\n".$joinStr
				."\n".'INNER JOIN #__apoth_att_codes AS ac'
				."\n".'   ON ac.code = da.att_code'
				."\n".'  AND ac.type = c.type'
				."\n".'INNER JOIN #__apoth_att_statistical_meaning AS acm'
				."\n".'   ON acm.id = ac.statistical_meaning'
				."\n".'WHERE da.person_id IN ('.$studentsStr.')'
				."\n".$dateSql
				."\n".'GROUP BY da.person_id, acm.id';
			$db->setQuery($query);
			$r = $db->loadAssocList();
			
			if( !is_array($r) ) { $r = array(); }
			
			$results = array();
			$total = 0;
			$good = 0;
			foreach($r as $row) {
				if( $row['id'] != 5 ) { // don't consider where attendance not required
					if( !isset($results[$row['person_id']]) ) {
						$results[$row['person_id']] = array( 'total'=>0, 'good'=>0 );
					}
					$results[$row['person_id']]['total'] += $row['count'];
					if( ($row['id'] == 1) || ($row['id'] == 2) ) { // Any "Present" code
						$results[$row['person_id']]['good'] += $row['count'];
					}
				}
			}
			
			foreach( $results as $student=>$totals ) {
				if( $totals['total'] == 0 ) {
					$checked[$period][$student][$group] = 'N/A';
				}
				else {
					$checked[$period][$student][$group] = number_format( (($totals['good'] / $totals['total']) * 100), 2 );
				}
			}
			
		}
		
		if( $single ) {
			$retVal = $checked[$period][reset($students)][$group];
		}
		else {
			$retVal = array();
			foreach( $students as $student ) {
				$retVal[$student][$group] = $checked[$period][$student][$group];
			}
		}
		return $retVal;
	}
	
	function getAttendanceCount( $codes, $student, $start, $end )
	{
		$db = &JFactory::getDBO();
		if( !is_array($codes) ) { $codes = array($codes); }
		foreach( $codes as $k=>$code ) {
			$codes[$k] = $db->Quote( $code );
		}
		$codes = implode( ',', $codes );
		
		$query = 'SELECT da.att_code, COUNT(*) AS count'
			."\n".' FROM #__apoth_att_dailyatt AS da'
			."\n".' INNER JOIN #__apoth_tt_daydetails AS dd'
			."\n".'    ON dd.day_section = da.day_section'
			."\n".' WHERE da.person_id = '.$db->Quote( $student )
			."\n".'   AND da.att_code IN ( '.$codes.' )'
			."\n".'   AND dd.statutory = '.$db->Quote( '1' )
			."\n".'   AND '.ApotheosisLibDb::dateCheckSql( 'da.date', 'da.date', $start, $end )
			."\n".' GROUP BY da.att_code';
		$db->setQuery($query);
		$r = $db->loadAssocList( 'att_code' );
		
		if( is_array($r) ) {
			foreach( $r as $k=>$v ) {
				$r[$k] = $r[$k]['count'];
			}
		}
		else {
		 $r = array();
		}
		
		return $r;
	}
	
	/**
	 * Get attendance, observing requirements
	 * 
	 * @param array $requirements  array of requirements to limit query
	 * @param string $index  the db column on which to index the loadAssocList
	 * 
	 * @return array  attendance limited by requirements
	 */
	function getAttendance( $requirements = false, $index = '' )
	{
		static $marks = array();
		static $codeObjects = false;
		if( $codeObjects === false ) {
			$codeObjects = ApotheosisAttendanceData::getCodeObjects( array(), false );
		}
		
		// if no requirements sent, provide defaults
		if( $requirements === false ) {
			$user = Apotheosislib::getUser();
			$requirements = array (
				'person'=>$user->person_id,
				'date'=>date( 'Y-m-d' )
			);
		}
		
		$db = &JFactory::getDBO();
		$cur = array();
		
		// deal with requirements
		$whereStr = array();
		foreach( $requirements as $col=>$val ) {
			$cur[] = $val;
			if( is_array($val) ) {
				foreach( $val as $k=>$v ) {
					$val[$k] = $db->Quote( $v );
				}
				$assignPart = ' IN ('.implode( ', ',$val ).')';
			}
			else {
				$assignPart = ' = '.$db->Quote( $val );
			}
			
			switch( $col ) {
			case( 'person' ):
				$whereStr[] = $db->nameQuote('person_id').$assignPart;
				break;
			case( 'date' ):
				$whereStr[] = $db->nameQuote('date').$assignPart;
				break;
			}
		}
		
		// generate hash for these requirements
		$cur = hash( 'md5', implode($cur) );
		
		if( isset($marks[$cur]) ) {
			$retVal = $marks[$cur];
		}
		else {
			$query = 'SELECT *'
				."\n".' FROM '.$db->nameQuote( '#__apoth_att_dailyatt' )
				.( empty($whereStr) ? '' : "\n".'WHERE '.implode("\n AND ", $whereStr) );
			
			$db->setQuery( $query );
			$tmp = $db->loadAssocList( $index );
			
			foreach( $tmp as $k=>$code ) {
				$tmp[$k]['att_code'] = $codeObjects[$code['att_code']];
			}
			
			$retVal = $marks[$cur] = $tmp;
		}
		
		return $retVal;
	}
	
	/**
	 * Retrieves the first code used to indicate where no mark has been recorded
	 * 
	 * @return string  code for no mark
	 */
	function getNoCode()
	{
		static $noCode = false;
		static $codeObjects = false;
		if( $codeObjects === false ) {
			$codeObjects = ApotheosisAttendanceData::getCodeObjects( array(), false );
		}
		
		if( $noCode === false ) {
			$db = &JFactory::getDBO();
			
			$query = 'SELECT `code`'
				."\n".' FROM #__apoth_att_codes AS c'
				."\n".' INNER JOIN #__apoth_att_statistical_meaning AS s'
				."\n".'  ON s.id = c.statistical_meaning'
				."\n".' WHERE s.meaning = "No Mark"'
				."\n".' LIMIT 1';
				
			$db->setQuery( $query );
			
			$retVal = $noCode = $codeObjects[$db->loadResult()];
		}
		else {
			$retVal = $noCode;
		}
		
		return $retVal;
	}
	
	/**
	 * Retrieves the apoth_att_codes table as an objectified list subject to restrictions
	 * 
	 * @param array $requirements  provides limiting values to put it into a WHERE string
	 * @param bool $restrict  whether or not to limit results based on user level
	 * @return array  array of code objects
	 */
	function getCodeObjects( $requirements = array(), $restrict = true )
	{
		static $codeObjects = array();
		
		$db = &JFactory::getDBO();
		$cur = array();
		
		// deal with requirements
		if( !is_array($requirements) ) {
			$requirements = array();
		}
		$whereStr = array();
		foreach( $requirements as $col=>$val ) {
			if( is_array($val) ) {
				foreach( $val as $k=>$v ) {
					$val[$k] = $db->Quote( $v );
				}
				$assignPart = ' IN ('.implode( ', ',$val ).')';
				$cur[] = implode( ',', $val );
			}
			else {
				$assignPart = ' = '.$db->Quote( $val );
				$cur[] = $val;
			}
			
			switch( $col ) {
			case( 'is_common' ):
				$whereStr[] = $db->nameQuote('is_common').$assignPart;
				break;
			case( 'physical_meaning' ):
				$whereStr[] = 'ph.'.$db->nameQuote('id').$assignPart;
				break;
			case( 'type' ):
				$whereStr[] = $db->nameQuote('type').$assignPart;
				break;
			}
		}
		// deal with restrictions
		$rStr = '';
		if( $restrict ) {
			$rStr = "\n".'~LIMITINGJOIN~';
		}
		$cur[] = $rStr;
		
		// generate hash for these requirements
		$cur = hash( 'md5', implode( ',', $cur) );
		
		if( isset($codeObjects[$cur]) ) {
			$retVal = $codeObjects[$cur];
		}
		else {
			$query = 'SELECT ac.*, sc.'.$db->nameQuote('id').' AS sc_id, sc.'.$db->nameQuote('meaning').' AS sc_meaning, st.'.$db->nameQuote('id').' AS st_id, st.'.$db->nameQuote('meaning').' AS st_meaning, st.'.$db->nameQuote('summary').' AS st_summary, ph.'.$db->nameQuote('id').' AS ph_id, ph.'.$db->nameQuote('meaning').' AS ph_meaning'
				."\n".' FROM '.$db->nameQuote('#__apoth_att_codes').' AS ac'
				."\n".' INNER JOIN '.$db->nameQuote('#__apoth_att_roles').' AS ar'
				."\n".'    ON ar.'.$db->nameQuote( 'att_code_id' ).' = ac.'.$db->nameQuote( 'id' ).''
				."\n".' INNER JOIN '.$db->nameQuote('#__apoth_att_school_meaning').' AS sc'
				."\n".'    ON sc.'.$db->nameQuote('id').' = ac.'.$db->nameQuote('school_meaning')
				."\n".' INNER JOIN '.$db->nameQuote('#__apoth_att_statistical_meaning').' AS st'
				."\n".'    ON st.'.$db->nameQuote('id').' = ac.'.$db->nameQuote('statistical_meaning')
				."\n".' INNER JOIN '.$db->nameQuote('#__apoth_att_physical_meaning').' AS ph'
				."\n".'    ON ph.'.$db->nameQuote('id').' = ac.'.$db->nameQuote('physical_meaning')
				.$rStr
				."\n".' WHERE sc.'.$db->nameQuote('meaning').' != "DO NOT USE"'
				.( empty($whereStr) ? '' : "\n   AND ".implode("\n   AND ", $whereStr) )
				."\n".' ORDER BY '.$db->nameQuote('order_id').' ASC';
			$db->setQuery( ApotheosisLibAcl::limitQuery($query, 'core.roles', 'ar', 'role', false, false) );
			$retVal = $codeObjects[$cur] = $db->loadObjectList( 'code' );
		}
		
		return $retVal;
	}
}

class ApotheosisData_Attendance extends ApotheosisData
{
	function info()
	{
		return 'Attendance component installed';
	}
	
	function attendancePercent( $from, $to, $pIds = null, $courses = null )
	{
		if( is_array( $pIds ) ) {
			$pId = reset( $pIds );
		}
		
		return ApotheosisAttendanceData::getAttendancePercent( 'fixed', $pIds, $courses, $from, $to, true );
	}
	function attendancePercents( $from, $to, $pIds = null, $courses = null )
	{
		if( !is_null( $pIds ) && !is_array( $pIds ) ) {
			$pIds = array( $pIds );
		}
		
		return ApotheosisAttendanceData::getAttendancePercent( 'fixed', $pIds, $courses, $from, $to );
	}
	
	/**
	 * Retrieves attendance data for use in reports
	 * Currently only used by the panels
	 *
	 * @param array $requirements  What parameters should we use to limit our search?
	 */
	function dataSummary( $requirements )
	{
		$params = JComponentHelper::getParams( 'com_arc_attendance' );
		$start = $requirements['start_date'] ? $requirements['start_date'] : $params->get( 'report_from' );
		$end = $requirements['end_date'] ? $requirements['end_date'] : date('Y-m-d');
		$retVal = array();
		
		$db = &JFactory::getDBO();
		$query = 'SELECT COUNT( * ) AS count, dd.statutory, COALESCE( cp2.fullname, cp.fullname ) AS fullname, acm.meaning, acm.summary'
			."\n".'FROM #__apoth_att_dailyatt AS da'
			."\n".'INNER JOIN #__apoth_tt_group_members AS gm'
			."\n".'   ON gm.group_id = da.course_id'
			."\n".'  AND gm.person_id = da.person_id'
			."\n".'  AND gm.valid_from < da.date'
			."\n".'  AND (gm.valid_to > da.date OR gm.valid_to IS NULL)'
			."\n".'INNER JOIN #__apoth_cm_courses AS c'
			."\n".'   ON c.id = da.course_id'
			."\n".'  AND c.deleted = 0'
			."\n".'INNER JOIN #__apoth_cm_courses AS cp'
			."\n".'   ON c.parent = cp.id'
			."\n".'  AND cp.deleted = 0'
			."\n".'LEFT JOIN #__apoth_cm_pastoral_map AS pmap'
			."\n".'  ON c.id = pmap.course'
			."\n".'LEFT JOIN #__apoth_cm_courses AS c2'
			."\n".'  ON c2.id = pmap.pastoral_course'
			."\n".' AND c2.deleted = 0'
			."\n".'LEFT JOIN #__apoth_cm_courses AS cp2'
			."\n".'  ON cp2.id = c2.parent'
			."\n".' AND cp2.deleted = 0'
			."\n".'INNER JOIN #__apoth_att_codes AS ac'
			."\n".'  ON ac.code = da.att_code'
			."\n".' AND ac.type = c.type'
			."\n".'INNER JOIN #__apoth_att_statistical_meaning AS acm'
			."\n".'  ON acm.id = ac.statistical_meaning'
			."\n".'INNER JOIN #__apoth_tt_daydetails AS dd'
			."\n".'  ON dd.day_section = da.day_section'
			."\n".' AND da.date >= dd.valid_from AND (da.date <= dd.valid_to OR dd.valid_to IS NULL)'
			."\n".'~LIMITINGJOIN1~'
			."\n".'~LIMITINGJOIN2~'
			."\n".'WHERE da.date <= '.$db->Quote( $end ).' AND da.date >= '.$db->Quote( $start )
			.(isset( $requirements['pupil'] ) ? "\n".'  AND da.person_id = '.$db->Quote($requirements['pupil']) : '' )
			."\n".'GROUP BY dd.statutory, cp.fullname, acm.meaning';
		$query = ApotheosisLibAcl::limitQuery( $query, 'people.arc_people', 'da', 'person_id', false, null, '~LIMITINGJOIN1~');
		$query = ApotheosisLibAcl::limitQuery( $query, 'timetable.groups',  'da', 'course_id', false, null, '~LIMITINGJOIN2~');
		$db->setQuery( $query );
		$r = $db->loadAssocList();
		
		// get an array of statistical meanings
		$query = 'SELECT *'
			."\n".' FROM #__apoth_att_statistical_meaning';
		$db->setQuery( $query );
		$statMeaning = $db->loadAssocList();
		
		// make an array of empty meaning counters
		foreach( $statMeaning as $v ) {
			$meaningsArray[$v['meaning']] = 0;
		}
		$retVal['statutory'] = $meaningsArray;
		$retVal['statutory_limited'] = $meaningsArray;
		$retVal['statutory_possible'] = 0;
		$retVal['all_possible'] = 0;
		
		// sort data by 'statutory' and 'all'
		foreach( $r as $k=>$v ) {
			// only consider attendance we wish to summarise
			if( !is_null($v['summary']) ) {
				if( $v['statutory'] == 1 ) {
					$retVal['statutory'][$v['summary']] += (int)$v['count'];
					$retVal['statutory_limited'][$v['summary']] += (int)$v['count'];
					$retVal['statutory_possible'] += (int)$v['count'];
				}
				if( !isset($retVal['all'][$v['fullname']]) ) {
					$retVal['all'][$v['fullname']] = $meaningsArray;
				}
				if( !isset($retVal['all'][$v['fullname']][$v['summary']]           ) ) { $retVal['all'][$v['fullname']][$v['summary']]           = 0; }
				if( !isset($retVal['all_totals']['group'][$v['fullname']]          ) ) { $retVal['all_totals']['group'][$v['fullname']]          = 0; }
				if( !isset($retVal['all_totals']['meaning'][$v['summary']]         ) ) { $retVal['all_totals']['meaning'][$v['summary']]         = 0; }
				if( !isset($retVal['all_totals']['meaning_limited'][$v['summary']] ) ) { $retVal['all_totals']['meaning_limited'][$v['summary']] = 0; }
				
				$retVal['all'][$v['fullname']][$v['summary']] += (int)$v['count'];
				$retVal['all_totals']['group'][$v['fullname']] += (int)$v['count'];
				$retVal['all_totals']['meaning'][$v['summary']] += (int)$v['count'];
				$retVal['all_totals']['meaning_limited'][$v['summary']] += (int)$v['count'];
				$retVal['all_possible'] += (int)$v['count'];
			}
		}
		
		return $retVal;
	}
}
?>