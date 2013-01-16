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
 * Data Access Helper
 *
 * @author		lightinthedark <code@lightinthedark.org.uk>
 * @package		Apotheosis
 * @subpackage Attendance
 * @since 0.1
 */
class ApotheosisTimetableData extends JObject
{
	
	/**
	 * Loads the profile for the specified Arc id
	 *
	 * @param string $uId  The arc user id whose profile is to be retrieved.
	 */
	function getPastEnrolments( $requirements )
	{
		$db = &JFactory::getDBO();
		
		$valid_from = ( array_key_exists('valid_from', $requirements) ? $requirements['valid_from'] : date('Y-m-d H:i:s') );
		$valid_to   = ( array_key_exists('valid_to',   $requirements) ? $requirements['valid_to']   : date('Y-m-d H:i:s') );
		unset( $requirements['valid_from'] );
		unset( $requirements['valid_to'] );
		
		foreach( $requirements as $col=>$val ) {
			// pre-escape/quote arrays of values
			if( is_array($val) ) {
				foreach($val as $k=>$v) {
					$val[$k] = $db->Quote($v);
				}
				$assignPart = ' IN ('.implode(', ', $val).')';
			}
			else {
				$assignPart = ' = '.$db->Quote($val);
			}
			
			switch( $col ) {
			case( 'person_id' ):
			case( 'group_id' ):
				$wheres[] = $db->nameQuote('gm1').'.'.$db->nameQuote($col).' '.$assignPart;
				break;
			
			case( 'is_admin' ): // *** titikaka
			case( 'is_teacher' ): // *** titikaka
			case( 'is_student' ): // *** titikaka
			case( 'is_watcher' ): // *** titikaka
				$wheres[] = $db->nameQuote('gm1').'.'.$db->nameQuote($col).' '.$assignPart;
				$wheres[] = $db->nameQuote('gm2').'.'.$db->nameQuote($col).' '.$assignPart;
				break;
			}
		}
		
		$query = 'CREATE TABLE ~TABLE~ AS'
			."\n".' SELECT DISTINCT gm2.person_id, gm2.group_id'
			."\n".' FROM #__apoth_tt_group_members AS gm1'
			."\n".' INNER JOIN #__apoth_tt_group_members AS gm2'
			."\n".'    ON gm2.person_id = gm1.person_id'
			."\n".' INNER JOIN #__apoth_cm_courses AS c1'
			."\n".'    ON c1.id = gm1.group_id'
			."\n".'   AND c1.deleted = 0'
			."\n".' INNER JOIN #__apoth_cm_courses AS c2'
			."\n".'    ON c2.id = gm2.group_id'
			."\n".'   AND c2.parent = c1.parent'
			."\n".'   AND c2.deleted = 0'
			.( empty($wheres) ? '' : "\n".' WHERE '.implode( "\n".' AND ', $wheres ) )
			."\n".'   AND '.ApotheosisLibDb::dateCheckSql( 'gm1.valid_from', 'gm1.valid_to', $valid_from, $valid_to)
			."\n".'   AND '.ApotheosisLibDb::dateCheckSql( 'gm2.valid_from', 'gm2.valid_to', $valid_from, $valid_to)
			."\n".' ORDER BY gm2.person_id, gm2.group_id';
		$tName = ApotheosisLibDbTmp::initTable( $query, true, 'timetable', 'stud_groups', $user->id );
		ApotheosisLibDbTmp::setTtl( $tName, 10 );
//		debugQuery($db, $tName);
		
		return $tName;
	}
	
	/**
	 * Loads the enrolments for the given requirements
	 * Either puts them in the named table, or returns the result object array
	 * 
	 * @param $requirements array  Associative array of column=>value(s) requirements
	 * @param $tableName string|null  If string the enrolment data will be put into the named table which must be handled by LibDbTmp, if null the result set is returned
	 * @param $withTg bool  Should we load people's tutor groups?
	 */
	function getEnrolments( $requirements, $tableName = null, $withTg = false )
	{
		$db = &JFactory::getDBO();
		
		$valid_from = ( array_key_exists('valid_from', $requirements) ? strtotime($requirements['valid_from']) : time() );
		$valid_to   = ( array_key_exists('valid_to',   $requirements) ? strtotime($requirements['valid_to']  ) : time() );
		unset( $requirements['valid_from'] );
		unset( $requirements['valid_to'] );
		if( $valid_from > $valid_to ) {
			$tmp = $valid_from;
			$valid_from = $valid_to;
			$valid_to = $tmp;
		}
		$dates = array();
		while( $valid_from <= $valid_to ) {
			$dates[] = $db->Quote( date('Y-m-d H:i:s', $valid_from) );
			$valid_from = strtotime( '+1 day', $valid_from );
		}
		
		$gm  = $db->nameQuote( 'gm' );
		$c   = $db->nameQuote( 'c' );
		$dd  = $db->nameQuote( 'dd' );
		foreach( $requirements as $col=>$val ) {
			// pre-escape/quote arrays of values
			if( is_array($val) ) {
				foreach($val as $k=>$v) {
					$val[$k] = $db->Quote($v);
				}
				$assignPart = ' IN ('.implode(', ', $val).')';
			}
			else {
				$assignPart = ' = '.$db->Quote($val);
			}
			
			switch( $col ) {
			case( 'person_id' ):
			case( 'group_id' ):
				$wheres[] = $gm.'.'.$db->nameQuote($col).$assignPart;
				break;
				
			case( 'course_id' ):
				$wheres[] = $gm.'.'.$db->nameQuote('group_id').$assignPart;
				break;
				
			case( 'role' ):
				$wheres[] = $gm.'.'.$db->namequote($col).$assignPart;
				break;
				
			case( 'period_type' ):
				$joins[] = 'INNER JOIN #__apoth_tt_daydetails AS dd'
					."\n".'   ON dd.pattern = d.pattern'
					."\n".'  AND dd.day_type = d.day_type'
					."\n".'  AND dd.day_section = t.day_section';
				$wheres[] = $dd.'.'.$db->nameQuote('statutory').$assignPart;
				break;
			
			case( 'academic_year' ):
				$wheres[] = $c.'.'.$db->nameQuote('year').$assignPart;
				break;
			
			case( 'subject' ):
				$wheres[] = $c.'.'.$db->nameQuote('parent').$assignPart;
				break;
			
			case( 'tutor_grp' ):
				$joins[] = 'INNER JOIN #__apoth_tt_group_members AS gm3'
					."\n".'   ON gm3.person_id = gm.person_id'
					."\n".'  AND gm3.valid_from <= d.`date`'
					."\n".'  AND (gm3.valid_to >= d.`date` OR gm3.valid_to IS NULL)';
				$wheres[] = $db->nameQuote( 'gm3' ).'.'.$db->nameQuote('group_id').$assignPart;
				break;
			
			case( 'day_section' ):
				$wheres[] = $db->nameQuote( 't' ).'.'.$db->nameQuote('day_section').$assignPart;
				break;
				
			case( 'teacher' ):
				$wheres[] = $db->nameQuote( 'gm2' ).'.'.$db->nameQuote('person_id').$assignPart;
				break;
			}
		}
		
		$tmp1 = $db->nameQuote( 'tmp_dlist' .str_replace(array(' ', '.'), '_',microtime()) );
		$tmp2 = $db->nameQuote( 'tmp_enrol' .str_replace(array(' ', '.'), '_',microtime()) );
		$tmp3 = $db->nameQuote( 'tmp_tutors'.str_replace(array(' ', '.'), '_',microtime()) );
		// Create list of enrolments
		$query =  // set up a list of dates in our range, with pattern / day info
			      'CREATE TEMPORARY TABLE '.$tmp1.' ('
			."\n".' `date` DATE PRIMARY KEY,'
			."\n".' `pattern` INT,'
			."\n".' `pattern_instance` INT,'
			."\n".' `day_index` INT,'
			."\n".' `day_type` CHAR(1)'
			."\n".');'
			."\n".''
			."\n".'INSERT INTO '.$tmp1.' (`date`)'
			."\n".'VALUES'
			."\n".'('.implode( '), (', $dates ).');'
			."\n".''
			."\n".'UPDATE '.$tmp1.' AS d'
			."\n".'INNER JOIN #__apoth_tt_patterns AS p'
			."\n".'   ON p.valid_from < d.`date`'
			."\n".'  AND (p.valid_to > d.`date` OR p.valid_to IS NULL)'
			."\n".'INNER JOIN #__apoth_tt_pattern_instances AS pin'
			."\n".'   ON pin.start <= d.`date`'
			."\n".'  AND (pin.end >= d.`date` OR pin.end IS NULL)'
			."\n".'  AND pin.pattern = p.id'
			."\n".'SET d.pattern = p.id'
			."\n".'  , d.pattern_instance = pin.id'
			."\n".'  , d.day_index = arc_dateToCycleDay( d.`date` )'
			."\n".'  , d.day_type = SUBSTRING( p.format, arc_dateToCycleDay( d.`date` ) + 1, 1 );'
			."\n".''
			// in our date range, which enrolments match our criteria
			."\n".'CREATE TABLE '.$tmp2.' AS'
			."\n".'SELECT d.*, t.day_section, t.room_id, gm.group_id, gm.person_id, gm.role, CONCAT_WS(",", gm2.person_id) AS teachers, c.fullname AS group_name, c.parent, c.year'
			."\n".'FROM '.$tmp1.' AS d'
			."\n".'INNER JOIN #__apoth_tt_timetable AS t'
			."\n".'   ON t.pattern = d.pattern'
			."\n".'  AND t.day = d.day_index'
			."\n".'  AND t.valid_from <= d.`date`'
			."\n".'  AND (t.valid_to >= d.`date` OR t.valid_to IS NULL)'
			."\n".'INNER JOIN #__apoth_tt_group_members AS gm'
			."\n".'   ON gm.group_id = t.course'
			."\n".'  AND gm.valid_from <= d.`date`'
			."\n".'  AND (gm.valid_to >= d.`date` OR gm.valid_to IS NULL)'
			."\n".'LEFT JOIN #__apoth_tt_group_members AS gm2'
			."\n".'  ON gm2.group_id = t.course'
			."\n".' AND gm2.valid_from <= d.`date`'
			."\n".' AND (gm2.valid_to >= d.`date` OR gm2.valid_to IS NULL)'
			."\n".' AND gm2.role = '.ApotheosisLibAcl::getRoleId( 'group_supervisor_teacher' )
			."\n".'INNER JOIN #__apoth_cm_courses AS c'
			."\n".'   ON c.id = gm.group_id'
			."\n".'  AND c.deleted = 0'
			."\n".'~LIMITINGJOIN1~'
			."\n".'~LIMITINGJOIN2~'
			.( empty($joins) ? '' : "\n".implode( "\n", $joins) )
			.( empty($wheres) ? '' : "\n".'WHERE '.implode( "\n".'  AND ', $wheres ) )
			."\n".'GROUP BY d.'.$db->nameQuote('date').', t.day_section, gm.id;';
		$query = ApotheosisLibAcl::limitQuery( $query, 'people.arc_people', 'gm', 'person_id', false, null, '~LIMITINGJOIN1~' );
		$query = ApotheosisLibAcl::limitQuery( $query, 'timetable.groups',  'gm', 'group_id' , false, null, '~LIMITINGJOIN2~' );
		$db->setQuery( $query );
		$db->QueryBatch();
		
		// Create list of tutor groups
		if( $withTg ) {
			$queryTg = 'CREATE TABLE '.$tmp3.' AS'
				."\n".'SELECT en.`date`, en.person_id, c2.id AS tg_id, c2.fullname AS tg_fullname, c2.parent AS tg_parent, c2.year AS tg_year'
				."\n".'FROM '.$tmp2.' AS en'
				."\n".'INNER JOIN #__apoth_tt_group_members AS gm2'
				."\n".'   ON gm2.person_id = en.person_id'
				."\n".'  AND gm2.valid_from < en.`date`'
				."\n".'  AND (gm2.valid_to > en.`date` OR gm2.valid_to IS NULL)'
				."\n".'INNER JOIN #__apoth_cm_courses AS c2'
				."\n".'   ON c2.id = gm2.group_id'
				."\n".'  AND c2.type = "pastoral"'
				."\n".'  AND c2.deleted = 0'
				."\n".'GROUP BY en.`date`, en.person_id';
			$db->setQuery( $queryTg );
			$db->QueryBatch();
		}
		
		// Get final result set
		if( $withTg ) {
			$queryPrep = 'ALTER TABLE '.$tmp2.' ADD INDEX ('.$db->nameQuote('date').');'
				."\n".'ALTER TABLE '.$tmp2.' ADD INDEX ('.$db->nameQuote('person_id').');'
				."\n".'ALTER TABLE '.$tmp3.' ADD INDEX ('.$db->nameQuote('date').');'
				."\n".'ALTER TABLE '.$tmp3.' ADD INDEX ('.$db->nameQuote('person_id').');';
			$db->setQuery($queryPrep);
			$db->QueryBatch();
			
			$queryRes = 'SELECT en.*, tg_id, tg_fullname, tg_parent'
				."\n".'FROM '.$tmp2.' AS en'
				."\n".'LEFT JOIN '.$tmp3.' AS tut'
				."\n".'  ON tut.`date` = en.`date`'
				."\n".' AND tut.person_id = en.person_id';
		}
		else {
			$queryRes = 'SELECT *'
				."\n".'FROM '.$tmp2.' AS en';
		}
		
		if( is_null($tableName) ) {
			$db->setQuery( $queryRes );
			$retVal = $db->loadObjectList();
		}
		else {
			ApotheosisLibDbTmp::create( $tableName, 'CREATE TABLE ~TABLE~ AS '.$queryRes );
			ApotheosisLibDbTmp::setPopulated( $tableName );
			ApotheosisLibDbTmp::commit( $tableName );
			$retVal = $tableName;
		}
		
		$queryClean = 'DROP TABLE IF EXISTS '.$tmp1.';'
			."\n".'DROP TABLE IF EXISTS '.$tmp2.';'
			."\n".'DROP TABLE IF EXISTS '.$tmp3.';';
		$db->setQuery( $queryClean );
		$db->QueryBatch();
		
		return $retVal;
	}
	
}

class ApotheosisData_Timetable extends ApotheosisData
{
	function info()
	{
		return 'Timetable component installed';
	}
	
	/**
	 * Find a group id according to requirements
	 * 
	 * @param array $requirements  The requirements as an associative array of col=>val pairs
	 */
	function group( $requirements )
	{
		if( !array($requirements) || empty($requirements) ) {
			return array();
		}
		
		$db = &JFactory::getDBO();
		$dbGm = $db->nameQuote( 'gm' );
		$requirements['valid_from'] = ( array_key_exists('valid_from', $requirements) ? $requirements['valid_from'] : date('Y-m-d H:i:s') );
		$requirements['valid_to']   = ( array_key_exists('valid_to',   $requirements) ? $requirements['valid_to']   : date('Y-m-d H:i:s') );
		$where = array();
		$join = array();
		foreach( $requirements as $col=>$val ) {
			if( is_array($val) ) {
				if( empty($val) ) {
					continue;
				}
				foreach( $val as $k=>$v ) {
					$val[$k] = $db->Quote( $v );
				}
				$assignPart = ' IN ('.implode( ', ',$val ).')';
			}
			else {
				$assignPart = ' = '.$db->Quote( $val );
			}
			switch( $col ) {
			case( 'valid_from' ):
			case( 'valid_to' ):
				if( !isset($where['date']) ) {
					$where['date'] = ApotheosisLibDb::dateCheckSql('gm.valid_from', 'gm.valid_to', $requirements['valid_from'], $requirements['valid_to']);
				}
				break;
			
			case( 'day_section' ):
				$dbTt = $db->nameQuote( 'tt' );
				$join[] = 'INNER JOIN '.$db->nameQuote( '#__apoth_tt_timetable' ).' AS '.$dbTt
					."\n".'   ON '.$dbTt.'.'.$db->nameQuote( 'course' ).' = '.$db->nameQuote('gm').'.'.$db->nameQuote('group_id')
					."\n".'  AND '.$dbTt.'.'.$db->nameQuote( 'day_section' ).$assignPart;
				break;
			
			case( 'person' ):
				$where[] = $dbGm.'.'.$db->nameQuote( 'person_id' ).$assignPart;
				break;
			
			case( 'group' ):
				$where[] = $dbGm.'.'.$db->nameQuote( 'group_id' ).$assignPart;
				break;
			
			case( 'role' ):
				$where[] = $dbGm.'.'.$db->nameQuote( 'role' ).$assignPart;
				break;
			}
		}
		
		$query = 'SELECT DISTINCT '.$dbGm.'.'.$db->nameQuote('group_id')
			."\n".'FROM '.$db->nameQuote('#__apoth_tt_group_members').' AS '.$dbGm
			.( empty($join) ? '' : "\n".implode("\n", $join) )
			.( empty($where) ? '' : "\nWHERE ".implode("\n  AND ", $where) );
		
		$db->setQuery( $query );
		$r = $db->loadResultArray();
		
		return $r;
	}
	
	/**
	 * Find lesson details (location, time, etc) according to requirements
	 */
	function lesson( $requirements )
	{
		if( !array($requirements) || empty($requirements) ) {
			return array();
		}
		
		$db = &JFactory::getDBO();
		$requirements['valid_from'] = ( array_key_exists('valid_from', $requirements) ? $requirements['valid_from'] : date('Y-m-d H:i:s') );
		$requirements['valid_to']   = ( array_key_exists('valid_to',   $requirements) ? $requirements['valid_to']   : date('Y-m-d H:i:s') );
		$where = array();
		$join = array();
		$dbTt = $db->nameQuote( 'tt' );
		foreach( $requirements as $col=>$val ) {
			if( is_array($val) ) {
				if( empty($val) ) {
					continue;
				}
				foreach( $val as $k=>$v ) {
					$val[$k] = $db->Quote( $v );
				}
				$assignPart = ' IN ('.implode( ', ',$val ).')';
			}
			else {
				$assignPart = ' = '.$db->Quote( $val );
			}
			switch( $col ) {
			case( 'valid_from' ):
			case( 'valid_to' ):
				if( !isset($where['date']) ) {
					$where['date'] = ApotheosisLibDb::dateCheckSql($dbTt.'.valid_from', $dbTt.'.valid_to', $requirements['valid_from'], $requirements['valid_to']);
				}
				break;
			
			case( 'group' ):
				$where[] = $dbTt.'.'.$db->nameQuote('course').$assignPart;
				break;
			
			case( 'pattern' ):
			case( 'day' ):
			case( 'day_section' ):
			case( 'room_id' ):
				$where[] = $dbTt.'.'.$db->nameQuote($col).$assignPart;
				break;
			}
		}
		
		$query = 'SELECT * '
			."\n".'FROM '.$db->nameQuote('#__apoth_tt_timetable').' AS '.$dbTt
			.( empty($join) ? '' : "\n".implode("\n", $join) )
			.( empty($where) ? '' : "\nWHERE ".implode("\n  AND ", $where) );
		
		$db->setQuery( $query );
		$r = $db->loadObjectList();
//		debugQuery( $db, $r );
		
		return $r;
	}
	
	/**
	 * Based on a set of requirements return a list of people/group id pairs
	 * This may become part of an enrolments factory at a later date, with this call
	 * simply wrapping around the factory object
	 * 
	 * @param $requirements
	 */
	function studentEnrolments( $requirements, $limPeople = null, $limGroups = null )
	{
		$requirements['role'] = ApotheosisLibAcl::getRoleId( 'group_participant_student' );
		return $this->enrolments( $requirements, $limPeople, $limGroups );
	}
	
	/**
	 * Based on a set of requirements return a list of people/group id pairs
	 * This may become part of an enrolments factory at a later date, with this call
	 * simply wrapping around the factory object
	 * 
	 * @param $requirements
	 */
	function enrolments( $requirements, $limPeople = null, $limGroups = null )
	{
//		global $doDump;
//		if( $doDump ) { dump( func_get_args(), 'args to get enrolments' ); }
//		var_dump_pre($requirements, 'requirements for studentEnrolments');
		if( !array($requirements) || empty($requirements) ) {
			return array();
		}
		
		if( is_null($limPeople) ) { $limPeople = 'people.arc_people'; }
		if( is_null($limGroups) ) { $limGroups = 'timetable.groups'; }
		
		$db = &JFactory::getDBO();
		$requirements['valid_from'] = ( array_key_exists('valid_from', $requirements) ? $requirements['valid_from'] : date('Y-m-d H:i:s') );
		$requirements['valid_to']   = ( array_key_exists('valid_to',   $requirements) ? $requirements['valid_to']   : date('Y-m-d H:i:s') );
		
		$gm = $db->nameQuote('gm');
		$pm = $db->nameQuote('pm');
		$select = $gm.'.'.$db->nameQuote('person_id').', '.$gm.'.'.$db->nameQuote('group_id');
		$where = array();
		$join = array();
		foreach( $requirements as $col=>$val ) {
			if( is_array($val) ) {
				if( empty($val) ) {
					continue;
				}
				foreach( $val as $k=>$v ) {
					$val[$k] = $db->Quote( $v );
				}
				$assignPart = ' IN ('.implode( ', ',$val ).')';
			}
			else {
				$assignPart = ' = '.$db->Quote( $val );
			}
			switch( $col ) {
			case( 'valid_from' ):
			case( 'valid_to' ):
				if( !isset($where['date']) ) {
					$where['date'] = ApotheosisLibDb::dateCheckSql('gm.valid_from', 'gm.valid_to', $requirements['valid_from'], $requirements['valid_to']);
				}
				break;
			
			case( 'academic_year' ):
				$join[] = 'INNER JOIN '.$db->nameQuote('#__apoth_cm_courses').' AS '.$db->nameQuote('c')
					."\n".'   ON '.$db->nameQuote('c').'.'.$db->nameQuote('id').' = '.$db->nameQuote('gm').'.'.$db->nameQuote('group_id')
					."\n".'  AND '.$db->nameQuote('c').'.'.$db->nameQuote('year').$assignPart
					."\n".'  AND '.$db->nameQuote('c').'.'.$db->nameQuote('deleted').' = '.$db->Quote('0');
				break;
			
			case( 'groups' ):
				$select = $gm.'.'.$db->nameQuote('person_id').', COALESCE( '.$pm.'.'.$db->nameQuote('course').', '.$gm.'.'.$db->nameQuote('group_id').' ) AS '.$db->nameQuote('group_id');
				$join[] = 'INNER JOIN '.$db->nameQuote('#__apoth_cm_courses').' AS '.$db->nameQuote('cpm')
					."\n".'LEFT JOIN '.$db->nameQuote('#__apoth_cm_pseudo_map').' AS '.$db->nameQuote('pm')
					."\n".'  ON '.$db->nameQuote('pm').'.'.$db->nameQuote('course').' = '.$db->nameQuote('cpm').'.'.$db->nameQuote('id');
				$where[] = $db->nameQuote('cpm').'.'.$db->nameQuote('id').$assignPart;
				$where[] = 'COALESCE( '.$pm.'.'.$db->nameQuote('twin').', '.$db->nameQuote('cpm').'.'.$db->nameQuote('id').' ) = '.$gm.'.'.$db->nameQuote('group_id');
				break;
			
			case( 'group_type' ):
				$c  = $db->nameQuote('c_type');
				$join[] = 'INNER JOIN '.$db->nameQuote('jos_apoth_cm_courses').' AS '.$c
					."\n".'   ON '.$c.'.'.$db->nameQuote('id').' = '.$gm.'.'.$db->nameQuote('group_id')
					."\n".'  AND '.$c.'.'.$db->nameQuote('type').$assignPart
					."\n".'  AND '.$c.'.'.$db->nameQuote('deleted').' = '.$db->Quote('0');
				break;
			
			case( 'tutor' ):
				$gm2 = $db->nameQuote('gm_tut');
				$c2  = $db->nameQuote('c_tut');
				$join[] = 'INNER JOIN '.$db->nameQuote('jos_apoth_tt_group_members').' AS '.$gm2
					."\n".'   ON '.$gm2.'.'.$db->nameQuote('group_id').$assignPart
					."\n".'  AND '.$gm2.'.'.$db->nameQuote('person_id').' = '.$db->nameQuote('gm').'.'.$db->nameQuote('person_id')
					."\n".'  AND '.ApotheosisLibDb::dateCheckSql('gm_tut.valid_from', 'gm_tut.valid_to', $requirements['valid_from'], $requirements['valid_to'])
					."\n".'INNER JOIN '.$db->nameQuote('jos_apoth_cm_courses').' AS '.$c2
					."\n".'   ON '.$c2.'.'.$db->nameQuote('id').' = '.$gm2.'.'.$db->nameQuote('group_id')
					."\n".'  AND '.$c2.'.'.$db->nameQuote('type').' = '.$db->Quote('pastoral')
					."\n".'  AND '.$c2.'.'.$db->nameQuote('deleted').' = '.$db->Quote('0');
				break;
			
			case( 'teacher' ):
				$gm2 = $db->nameQuote('gm_te');
				$join[] = 'INNER JOIN '.$db->nameQuote('jos_apoth_tt_group_members').' AS '.$gm2
					."\n".'   ON '.$gm2.'.'.$db->nameQuote('group_id').' = '.$db->nameQuote('gm').'.'.$db->nameQuote('group_id')
					."\n".'  AND '.$gm2.'.'.$db->nameQuote('person_id').$assignPart
					."\n".'  AND '.$gm2.'.'.$db->nameQuote('role').' = '.$db->Quote(ApotheosisLibAcl::getRoleId('group_supervisor_teacher') )
					."\n".'  AND '.ApotheosisLibDb::dateCheckSql('gm_te.valid_from', 'gm_te.valid_to', $requirements['valid_from'], $requirements['valid_to']);
				break;
			
			case( 'pupil' ):
			case( 'person_id' ):
				$where[] = $gm.'.'.$db->nameQuote('person_id').$assignPart;
				break;
			
			case( 'role' ):
				$where[] = $gm.'.'.$db->nameQuote( 'role' ).$assignPart;
				break;
			}
		}
		
		$query = 'SELECT '.$select
			."\n".'FROM '.$db->nameQuote('#__apoth_tt_group_members').' AS '.$db->nameQuote('gm')
			.( ( $limPeople === false ) ? '' : "\n".'~LIMITINGJOIN1~' )
			.( ( $limGroups === false ) ? '' : "\n".'~LIMITINGJOIN2~' )
			.( empty($join) ? '' : "\n".implode("\n", $join) )
			.( empty($where) ? '' : "\nWHERE ".implode("\n  AND ", $where) );
		if( $limPeople !== false ) { $query = ApotheosisLibAcl::limitQuery( $query, $limPeople, 'gm', 'person_id', false, null, '~LIMITINGJOIN1~' ); }
		if( $limGroups !== false ) { $query = ApotheosisLibAcl::limitQuery( $query, $limGroups, 'gm', 'group_id' , false, null, '~LIMITINGJOIN2~' ); }
		
		$db->setQuery( $query );
		$r = $db->loadAssocList();
//		if( $doDump ) { dumpQuery( $db, $r ); }
//		debugQuery($db, $r);
		
		return $r;
	}
	
	/**
	 * Works out all the past enrolments of the given pupils before the given groups
	 * , staying within the same subject
	 * , trying not to get confused by concurrent enrolments
	 * @param $pg array  List of person_id / group_id pairs (assoc arrays)
	 * @param $from string  The date to search from (oldest)
	 * @param $to string  The date to search up to (newest)
	 */
	function enrolmentHistory( $pg, $from, $to )
	{
//		var_dump_pre( func_get_args(), 'history args' );
		if( empty($pg) || !is_array($pg) ) {
			return array();
		}
		
		// I think this is the best way to get someone's enrolment history 
		$db = &JFactory::getDBO();
		$where = array();
		$pCol = $db->nameQuote('gm1').'.'.$db->nameQuote('person_id');
		$gCol = $db->nameQuote('gm1').'.'.$db->nameQuote('group_id');
		$groups = array();
		foreach( $pg as $tuple ) {
			$groups[] = $tuple['group_id'];
		}
		
		$groups = ApotheosisData::_( 'course.toReal', $groups );
		$pseudo = ApotheosisData::_( 'course.toPseudo', $groups );
		
		foreach( $pg as $tuple ) {
			$gId = $tuple['group_id'];
			$rId = $groups[$gId];
			
			if( $rId == $gId ) {
				$whereReal[]   = '('.$pCol.' = '.$db->Quote($tuple['person_id']).' AND '.$gCol.' = '.$db->Quote($gId).')';
			}
			else {
				$wherePseudo[] = '('.$pCol.' = '.$db->Quote($tuple['person_id']).' AND '.$gCol.' = '.$db->Quote($rId).')';
			}
		}
		
		if( !empty($whereReal) ) {
			// get the history from real groups
			$query = 'SELECT gm1.group_id AS cur, c_2.fullname, c_2.id AS group_id, gm2.group_id, gm2.person_id, gm2.valid_from, IFNULL(gm2.valid_to, "9999-12-30 23:59:59") AS valid_to'
				."\n".'FROM `jos_apoth_tt_group_members` AS gm1'
				."\n"
				."\n".'INNER JOIN jos_apoth_cm_courses AS c1'
				."\n".'   ON c1.id = gm1.group_id'
				."\n".'  AND c1.deleted = 0'
				."\n"
				."\n".'INNER JOIN `jos_apoth_cm_courses` AS c_2'
				."\n".'   ON c_2.parent = c1.parent'
				."\n".'  AND c_2.deleted = 0'
				."\n".'LEFT JOIN jos_apoth_cm_pseudo_map AS pm2'
				."\n".'  ON pm2.course = c_2.id'
				."\n"
				."\n".'INNER JOIN `jos_apoth_tt_group_members` AS gm2'
				."\n".'   ON gm2.group_id = COALESCE( pm2.twin, c_2.id )'
				."\n".'  AND gm2.person_id = gm1.person_id'
				."\n".'  AND gm2.`role` = gm1.`role`'
				."\n"
				."\n".'WHERE gm1.`role` = '.ApotheosisLibAcl::getRoleId( 'group_participant_student' )
				."\n".'  AND ('.implode( "\n   OR ", $whereReal ).')'
				."\n".'  AND '.ApotheosisLibDb::dateCheckSql( 'gm1.valid_from', 'gm1.valid_to', $from, $to)
				."\n".'  AND '.ApotheosisLibDb::dateCheckSql( 'gm2.valid_from', 'gm2.valid_to', $from, $to)
				."\n".'ORDER BY (gm2.id = gm1.id) DESC, gm2.valid_to DESC, gm2.valid_from DESC';
			$db->setQuery($query);
			$rawEnrolments = $db->loadAssocList();
//			debugQuery( $db, $rawEnrolments );
		}
		else {
			$rawEnrolments = array();
		}
		
		if( !empty($wherePseudo) ) {
			// get the history from pseudo groups
			$query = 'SELECT c_1.id AS cur, c_2.fullname, c_2.id AS group_id, gm2.person_id, gm2.valid_from, IFNULL(gm2.valid_to, "9999-12-30 23:59:59") AS valid_to'
				."\n".'FROM `jos_apoth_tt_group_members` AS gm1'
				."\n"
				."\n".'INNER JOIN jos_apoth_cm_courses AS c1'
				."\n".'   ON c1.id = gm1.group_id'
				."\n".'  AND c1.deleted = 0'
				."\n".'INNER JOIN jos_apoth_cm_pseudo_map AS pm1'
				."\n".'   ON pm1.twin = c1.id'
				."\n".'INNER JOIN jos_apoth_cm_courses AS c_1'
				."\n".'   ON c_1.id = pm1.course'
				."\n".'  AND c_1.deleted = 0'
				."\n"
				."\n".'INNER JOIN `jos_apoth_cm_courses` AS c_2'
				."\n".'   ON c_2.parent = c_1.parent'
				."\n".'  AND c_2.deleted = 0'
				."\n".'LEFT JOIN jos_apoth_cm_pseudo_map AS pm2'
				."\n".'  ON pm2.course = c_2.id'
				."\n"
				."\n".'INNER JOIN `jos_apoth_tt_group_members` AS gm2'
				."\n".'   ON gm2.group_id = COALESCE( pm2.twin, c_2.id )'
				."\n".'  AND gm2.person_id = gm1.person_id'
				."\n".'  AND gm2.`role` = gm1.`role`'
				."\n"
				."\n".'WHERE gm1.`role` = '.ApotheosisLibAcl::getRoleId( 'group_participant_student' )
				."\n".'  AND ('.implode( "\n   OR ", $wherePseudo ).')'
				."\n".'  AND '.ApotheosisLibDb::dateCheckSql( 'gm1.valid_from', 'gm1.valid_to', $from, $to)
				."\n".'  AND '.ApotheosisLibDb::dateCheckSql( 'gm2.valid_from', 'gm2.valid_to', $from, $to)
				."\n".'ORDER BY (gm2.id = gm1.id) DESC, gm2.valid_to DESC, gm2.valid_from DESC';
			$db->setQuery($query);
			$rawEnrolments = array_merge( $rawEnrolments, $db->loadAssocList() );
//			debugQuery( $db, $rawEnrolments );
		}
		
		if( !is_array($rawEnrolments) ) { $rawEnrolments = array(); }
		$sortedEnrolments = array();
		foreach($rawEnrolments as $row) {
			$sortedEnrolments[$row['person_id']][$row['cur']][$row['valid_to']][$row['group_id']] = $row;
		}
		
		// Go through each student's enrolment history and clean things up a bit
		$retVal = array();
		foreach( $sortedEnrolments as $pId=>$curGroups ) {
			foreach( $curGroups as $cur=>$histGroups ) {
				$first = reset( $histGroups );
				$current = $first[$cur];
				if( is_null($current) ) {
					var_dump_pre( $first, 'could not find '.$cur.' in ' );
					continue;
				}
				$start = $current['valid_to'];
				foreach( $histGroups as $hDate=>$hGroups ) {
					// try to avoid being confused by concurrent enrolments, instead discard them
					if( $hDate <= $start ) {
						// use the course names to resolve ties
						if( count($hGroups) > 1 ) {
							$bestLev = 9999;
							foreach( $hGroups as $hId=>$hGroup ) {
								$lev = levenshtein( $current['fullname'], $hGroup['fullname'] );
								if( $lev < $bestLev ) {
									$bestLev = $lev;
									$h = $hGroup;
								}
							}
						}
						else {
							$h = reset( $hGroups );
						}
						$start = $h['valid_from'];
						$retVal[$pId][$cur][] = $h['group_id'];
					}
				}
				unset( $sortedEnrolments[$pId][$cur] );
			}
		}
		
//		var_dump_pre($sortedEnrolments, 's.e.', true);
//		var_dump_pre($retVal, 'retval', true);
		return $retVal;
	}
	
	function tutorgroup( $pId )
	{
		if( !isset($this->_tutorgroup[$pId]) ) {
			$db = &JFactory::getDBO();
			$query = 'SELECT c.id'
				."\n".'FROM #__apoth_tt_group_members AS gm'
				."\n".'INNER JOIN #__apoth_cm_courses AS c'
				."\n".'   ON c.id = gm.group_id'
				."\n".'  AND c.type = '.$db->Quote( 'pastoral' )
				."\n".'  AND c.deleted = '.$db->Quote( '0' )
				."\n".'WHERE gm.person_id = '.$db->Quote( $pId )
				."\n".'  AND '.ApotheosisLibDb::dateCheckSql( 'gm.valid_from', 'gm.valid_to', date('Y-m-d H:i:s'), date('Y-m-d H:i:s') );
			$db->setQuery( $query );
			
			$this->_tutorgroup[$pId] = $db->loadResult();
		}
		
		return $this->_tutorgroup[$pId];
	}
	
	function tutorgroups( $pIds )
	{
		if( !isset($this->_tutorgroup) ) {
			$this->_tutorgroup = array();
		}
		
		if( is_array($pIds) && !empty($pIds) ) {
			$db = &JFactory::getDBO();
			foreach( $pIds as $k=>$pId ) {
				$pIdsQuot[$k] = $db->Quote( $pId );
			}
			$pIdsQuot = implode( ',', $pIdsQuot );
			
			$query = 'SELECT gm.person_id, c.id'
				."\n".'FROM #__apoth_tt_group_members AS gm'
				."\n".'INNER JOIN #__apoth_cm_courses AS c'
				."\n".'   ON c.id = gm.group_id'
				."\n".'  AND c.type = '.$db->Quote( 'pastoral' )
				."\n".'  AND c.deleted = '.$db->Quote( '0' )
				."\n".'WHERE gm.person_id IN ('.$pIdsQuot.')'
				."\n".'  AND '.ApotheosisLibDb::dateCheckSql( 'gm.valid_from', 'gm.valid_to', date('Y-m-d H:i:s'), date('Y-m-d H:i:s') );
			$db->setQuery( $query );
			$this->_tutorgroup = $db->loadAssocList( 'person_id' );
			
			if( !is_array($this->_tutorgroup) ) {
				$this->_tutorgroup = array();
			}
			
			foreach( $pIds as $pId ) {
				if( isset( $this->_tutorgroup[$pId] ) ) {
					$this->_tutorgroup[$pId] = $this->_tutorgroup[$pId]['id'];
				}
				else {
					$this->_tutorgroup[$pId] = false;
				}
			}
		}
		
		return $this->_tutorgroup;
	}
	
	function teachers( $gId = null )
	{
		return $this->members( $gId, ApotheosisLibAcl::getRoleId( 'group_supervisor_teacher' ) );
	}
	
	function students( $gId = null )
	{
		return $this->members( $gId, ApotheosisLibAcl::getRoleId( 'group_participant_student' ) );
	}
	
	function members( $gId, $rId )
	{
		static $checked = array();
		
		if( !is_null($gId) ) {
			if( empty( $gId ) ) {
				return array();
			}
			if( !is_array( $gId ) ) {
				$gId = array( $gId );
			}
		}
		
		$gIdHash = md5( serialize($gId) );
		
		if( !isset($checked[$gIdHash][$rId]) ) {
			$db = &JFactory::getDBO();
			if( is_null( $gId ) ) {
				$clause = '';
			}
			else {
				foreach( $gId as $k=>$v ) {
					$gId[$k] = $db->Quote( $v );
				}
				$clause = "\n".'  AND gm.group_id IN ( '.implode( ', ', $gId ).' )';
			}
			
			$query = 'SELECT DISTINCT person_id'
				."\n".'FROM #__apoth_tt_group_members AS gm'
				."\n".'WHERE gm.role = '.$rId
				."\n".'  AND '.ApotheosisLibDb::dateCheckSql( 'gm.valid_from', 'gm.valid_to', date('Y-m-d H:i:s'), date('Y-m-d H:i:s') )
				.$clause;
			$db->setQuery( $query );
			$checked[$gIdHash][$rId] = $db->loadResultArray();
			if( !is_array($checked[$gIdHash][$rId]) ) { $checked[$gIdHash][$rId] = array(); }
		}
		return $checked[$gIdHash][$rId];
	}

}
?>