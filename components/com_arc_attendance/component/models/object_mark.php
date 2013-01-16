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
 * Mark object
 *
 * A single mark is modeled by this class
 *
 * @author		lightinthedark <code@lightinthedark.org.uk>
 * @package		Apotheosis
 * @subpackage	Attendance
 * @since 0.1
 */
class Mark extends JObject
{
	/**
	 * Creates a mark
	 * @param $date string  The date for which the mark applies
	 * @param $day_section string  The day section for which the mark applies
	 * @param $person string  The Arc id of the person to whom the mark applies
	 * @param $group int  The group id for which the mark applies
	 * @param $data array  Optional array of data to initialise the mark with (to avoid querying the db)
	 */
	function __construct( $date, $day_section, $person, $group, $data = null )
	{
		if( is_null($data) ) {
			// *** Query the database to find out stuff
		}
		$this->_date = $date;
		$this->_day_section = $day_section;
		$this->_person = $person;
		$this->_group = $group;
		$this->_att_code = $data['att_code'];
		$this->_last_modified = $data['last_modified'];
		$this->_teacher = ( isset($data['teacher']) ? explode( ',', $data['teacher'] ) : null );
		$this->_subject = ( isset($data['group_parent']) ? $data['group_parent'] : null );
		$this->_group_name = ( isset($data['group_name']) ? $data['group_name'] : null );
		$this->_tutor_name = ( isset($data['tutor_name']) ? $data['tutor_name'] : null );
		$this->_state->error = false;
	}
	
	/**
	 * Gives the HTML required to display the mark in a consistent and well-structured manner
	 * Includes comment / incident highlighting too
	 * @return string  The html to display this attendance mark
	 */
	function render()
	{
		// call in the attendance code objects from the attendance data access
		static $codes = array();
		if( empty($codes) ) {
			$codes = ApotheosisAttendanceData::getCodeObjects( array(), false );
		}
		
		return JHTML::_( 'arc_attendance.marks', $codes[$this->_att_code] );
	}
	
	/**
	 * Retrieves the value (eg "/", "N", "L") of the mark
	 * @return string  The mark value
	 */
	function getValue()
	{
		return $this->_att_code;
	}
	
	function setValue( $code, $uId = null, $commit = true )
	{
		if( is_null($uId) ) {
			$u = ApotheosisLib::getUser();
			$uId = $u->person_id; // useful for last_modified_by
		}
		
		$now = date( 'Y-m-d H:i:s' );
		if( $commit ) {
			$db = &JFactory::getDbo();
			// get the current value and update time to check if we're ok to update this
			$query = 'SELECT'
				."\n".' '.$db->nameQuote( 'att_code' )
				."\n".','.$db->nameQuote( 'last_modified' )
				."\n".'FROM '.$db->nameQuote( '#__apoth_att_dailyatt' )
				."\n".'WHERE '.$db->nameQuote( 'date' ).' = '.$db->Quote( $this->_date )
				."\n".'  AND '.$db->nameQuote( 'day_section' ).' = '.$db->Quote( $this->_day_section )
				."\n".'  AND '.$db->nameQuote( 'person_id' ).' = '.$db->Quote( $this->_person )
				."\n".'  AND '.$db->nameQuote( 'course_id' ).' = '.$db->Quote( $this->_group )
				."\n".'LIMIT 1';
			$db->setQuery( $query );
			$r = $db->loadAssoc();
			
			if( ($r['att_code'] == $code) || ($this->isEmpty($r['att_code']) && $this->isEmpty($code)) ) {
//				echo 'up to date<br />';
				// already up-to-date so do nothing...
				$this->_att_code = $r['att_code'];
				$this->_last_modified = $r['last_modified'];
				$this->_state->error = false;
			}
			elseif( $r['last_modified'] <= $this->_last_modified ) {
				// delete if code is empty or -
				if( $this->isEmpty($code) ) {
//					echo 'deleting<br />';
					$query = 'DELETE FROM '.$db->nameQuote( '#__apoth_att_dailyatt' )
						."\n".'WHERE '.$db->nameQuote( 'date' ).' = '.$db->Quote( $this->_date )
						."\n".'  AND '.$db->nameQuote( 'day_section' ).' = '.$db->Quote( $this->_day_section )
						."\n".'  AND '.$db->nameQuote( 'person_id' ).' = '.$db->Quote( $this->_person )
						."\n".'  AND '.$db->nameQuote( 'course_id' ).' = '.$db->Quote( $this->_group )
						."\n".'LIMIT 1';
					$db->setQuery($query);
					$db->Query();
				}
				// insert new
				elseif( empty($r['att_code']) ) {
//					echo 'inserting<br />';
					$query = 'INSERT INTO '.$db->nameQuote( '#__apoth_att_dailyatt' )
						."\n".'SET '.$db->nameQuote( 'date' ).' = '.$db->Quote( $this->_date )
						."\n".'  , '.$db->nameQuote( 'day_section' ).' = '.$db->Quote( $this->_day_section )
						."\n".'  , '.$db->nameQuote( 'person_id' ).' = '.$db->Quote( $this->_person )
						."\n".'  , '.$db->nameQuote( 'course_id' ).' = '.$db->Quote( $this->_group )
						."\n".'  , '.$db->nameQuote( 'att_code' ).' = '.$db->Quote( $code )
						."\n".'  , '.$db->nameQuote( 'last_modified' ).' = '.$db->Quote( $now );
					$db->setQuery($query);
					$db->Query();
				}
				// update existing
				else {
//					echo 'updating<br />';
					$query = 'UPDATE '.$db->nameQuote( '#__apoth_att_dailyatt' )
						."\n".'SET '.$db->nameQuote( 'att_code' ).' = '.$db->Quote( $code )
						."\n".'  , '.$db->nameQuote( 'last_modified' ).' = '.$db->Quote( $now )
						."\n".'WHERE '.$db->nameQuote( 'date' ).' = '.$db->Quote( $this->_date )
						."\n".'  AND '.$db->nameQuote( 'day_section' ).' = '.$db->Quote( $this->_day_section )
						."\n".'  AND '.$db->nameQuote( 'person_id' ).' = '.$db->Quote( $this->_person )
						."\n".'  AND '.$db->nameQuote( 'course_id' ).' = '.$db->Quote( $this->_group )
						."\n".'LIMIT 1';
					$db->setQuery($query);
					$db->Query();
				}
				if( $db->getAffectedRows() == 0 ) {
					$this->_state->error = 'db';
				}
				else {
					$this->_state->error = false;
					$this->_att_code = $code;
					$this->_last_modified = $now;
				}
			}
			else{
//				echo 'problem<br />';
				// notify
				$this->_state->errInfo = array( 'was'=>$this->_att_code, 'tried'=>$code );
				$this->_state->error = 'conc';
				$this->_att_code = $r['att_code'];
				$this->_last_modified = $r['last_modified'];
			}
		}
		return !(bool)$this->_state->error;
	}
	
	/**
	 * Retrieves the group id to which this mark relates
	 * @return int  The group id
	 */
	function getGroup()
	{
		return $this->_group;
	}
	
	/**
	 * Retrieves the Arc id of the person who recorded this mark
	 * @return string  The recorder's Arc id
	 */
	function getRecorder()
	{
		
	}
	
	/**
	 * Retrieves the day section to which this mark relates
	 * @return string  The day section
	 */
	function getDate()
	{
		return $this->_date;
	}
	
	function getError()
	{
		return $this->_state->error;
	}
	
	function getErrorInfo()
	{
		return $this->_state->errInfo;
	}
	
	function isEmpty( $code )
	{
		return( empty($code) || ($code == '-') || ($code == ' ') );
	}
}
?>