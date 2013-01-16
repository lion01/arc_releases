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
 * Mark Factory object
 *
 * Each mark should be retrieved through this class so that duplicate objects are avoided
 *
 * @author		lightinthedark <code@lightinthedark.org.uk>
 * @package		Apotheosis
 * @subpackage	Attendance
 * @since 0.1
 */
class MarkFactory extends JObject
{
	/**
	 * Constructor
	 * Creates an instance of the mark factory
	 */
	function __construct()
	{
		$this->_marks = array();
	}
	
	/**
	 * Gets the singleton mark factory creating it if it doesn't already exist
	 */
	function &getFactory()
	{
		static $instance = null;
		if( is_null($instance) ) {
			$instance = new MarkFactory();
		}
		return $instance;
	}
	
	/**
	 * Adds attendance data to an enrolment table
	 * @param $table
	 */
	function addAtt( $table )
	{
		$db = &JFactory::getDBO();
		$query = 'ALTER TABLE '.$table.' ADD column `att_code` VARCHAR(1), ADD COLUMN `last_modified` DATETIME;'
			."\n".'UPDATE '.$table.' AS e'
			."\n".'INNER JOIN #__apoth_att_dailyatt AS da'
			."\n".'   ON da.`date` = e.`date`'
			."\n".'  AND da.`day_section` = e.day_section'
			."\n".'  AND da.`person_id` = e.person_id'
			."\n".'  AND da.`course_id` = e.group_id'
			."\n".'SET e.att_code = da.att_code'
			."\n".'  , e.last_modified = da.last_modified;'
			."\n".''
			."\n".'UPDATE '.$table
			."\n".'SET att_code = "-"'
			."\n".'WHERE att_code IS NULL';
		$db->setQuery( $query );
		$db->QueryBatch();
	}
	
	/**
	 * Adds attendance percent data to an enrolment table
	 * @param $table
	 * @param $requirements
	 */
	function addAttPercent( $table, $requirements )
	{
		$db = &JFactory::getDBO();
		
		// get list of pupils whose % att we need
		$peopleQuery = 'SELECT DISTINCT '.$db->nameQuote('person_id')
			."\n".'FROM '.$db->nameQuote($table);
		$db->setQuery( $peopleQuery );
		$pupils = $db->loadResultArray();
		
		// get raw data from att data access
		require_once(JPATH_SITE.DS.'components'.DS.'com_arc_attendance'.DS.'helpers'.DS.'data_access.php');
		$rawData = ApotheosisAttendanceData::getAttendancePercent( 'fixed', $pupils, NULL, $requirements['valid_from'], $requirements['valid_to'] );
		
		if( is_array($rawData) && !empty($rawData) ) {
			//prepare data for query
			$updates = array();
			foreach( $rawData as $pId=>$percentArray ) {
				$updates[] = '( \''.$pId.'\', '.reset( $percentArray ).' )';
			}
			
			$tmpTable = 'arc_tmp_att_rep_percent_add__'.time();
			
			$insStr1 = 'CREATE TEMPORARY TABLE '.$db->nameQuote($tmpTable).' ( '.$db->nameQuote('id').' VARCHAR(20), '.$db->nameQuote('percent').' DECIMAL(5,2) );'
				."\n"
				."\n".'INSERT INTO '.$db->nameQuote($tmpTable).' ( '.$db->nameQuote('id').', '.$db->nameQuote('percent').' )'
				."\n".'VALUES '.implode( ', ', $updates ).';';
				
			$insStr2 = 'UPDATE '.$db->nameQuote($table).' AS '.$db->nameQuote('data')
				."\n".'INNER JOIN '.$db->nameQuote($tmpTable).' AS '.$db->nameQuote('tmp')
				."\n".'   ON '.$db->nameQuote('tmp').'.'.$db->nameQuote('id').' = '.$db->nameQuote('data').'.'.$db->nameQuote('person_id')
				."\n".'SET '.$db->nameQuote('data').'.'.$db->nameQuote('att_percent').' = '.$db->nameQuote('tmp').'.'.$db->nameQuote('percent').';';
			
		}
		else {
			$insStr1 = '';
			$insStr2 = '';
		}
		
		$query = $insStr1
			."\n"
			."\n".'ALTER TABLE '.$db->nameQuote($table).' ADD column '.$db->nameQuote('att_percent').' DECIMAL(5,2) NULL;'
			."\n"
			."\n".$insStr2;
		$db->setQuery( $query );
		$db->QueryBatch();
	}

	/**
	 * Gets a specific mark.
	 * Only one mark object instance is created per person/group/day/day_section
	 * If there is no mark instance for the given params a new one is created
	 * @param $date string  The date for which the mark applies
	 * @param $day_section string  The day section for which the mark applies
	 * @param $person string  The Arc id of the person to whom the mark applies
	 * @param $group int  The group id for which the mark applies
	 * @param $data array  Optional array of data to initialise the mark with (to avoid querying the db)
	 */
	function &getMark( $date, $day_section, $person, $group, $data = null )
	{
		if( !isset($this->_marks[$date][$day_section][$person][$group]) ) {
			$this->_marks[$date][$day_section][$person][$group] = new Mark( $date, $day_section, $person, $group, $data );
		}
		return $this->_marks[$date][$day_section][$person][$group];
	}
}
?>