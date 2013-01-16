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

jimport( 'joomla.application.component.model' );
require_once( JPATH_COMPONENT.DS.'models'.DS.'objects.php' );

/**
 * Attendance Manager Model Extension
 *
 * @author		lightinthedark <code@lightinthedark.org.uk>
 * @package		Apotheosis
 * @subpackage	Attendance
 * @since 0.1
 */
class AttendanceModel extends JModel
{
	/** @var state information */
	var $_state;
	
	/** @var array All the current courses */
	var $_courses = false;
	
	/** @var array All the current registers */
	var $_registers = false;
	
	/** @var pupils not enrolled on the selected course */
	var $_all_pupils = array();
	
	/** @var all possible attendance marks */
	var $_all_attendanceMarks = array();
	
	/** @var requirements used for the last search performed */
	var $_requirements = array();
	
	/**
	 * Retrieves the associative array of requirements last used to search for courses / registers
	 *
	 * @access  public
	 */
	function getRequirements()
	{
		return $this->_requirements;
	}
	
	/**
	 * Sets the associative array of requirements to use to search for courses / registers
	 *
	 * @param array $requirements  The associative array of requirements (eg 'day'=>3)
	 */
	function setRequirements( $requirements )
	{
		$this->_requirements = $requirements;
	}
	
	
	/**
	 * Gets all the courses currently selected
	 *
	 * @return array  The course objects in an array indexed on course id
	 */
	function &getCourses()
	{
		if ($this->_courses === false) {
			$this->setCourses(array());
		}
		ApotheosisLibArray::sortObjects($this->_courses, array('most_recent_date', 'most_recent_time', 'shortname'), 1, true);
		return $this->_courses;
	}
	
	/**
	 * Gets the number of courses currently selected
	 *
	 * @return int  The number of courses currently selected
	 */
	function getNoOfCourses()
	{
		if ($this->_courses === false) {
			$this->setCourses(array());
		}
		return $this->_noOfCourses;
	}
	
	/**
	 * Set a property by selecting all the courses that match the given criteria
	 * and returns the count of those courses (also stored in a property)
	 * Courses are not registers, and have no specific date. A date critieria will
	 * find courses that occured on that date, and the result set includes a "most_recent"
	 * attribute giving the date of the most recent occurence of that course.
	 *
	 * @param array $requirements  The associative array of requirements (eg 'day'=>3)
	 * @return int  The number of courses that match the requirements
	 * @access  public
	 */
	function setCourses($requirements = array())
	{
		if ( (empty($this->_requirements)) || (!empty($requirements)) ) {
			$this->_requirements = $requirements;
		}
		
		$this->_courses = Course::getCourses($this->_requirements);
		return $this->_noOfCourses = count($this->_courses);
	}
	
	
	/**
	 * Indicates if the registers have been set yet (ie if any search has run)
	 * without executing a search as getNoOfRegisters() does if there is none
	 *
	 * @return boolean  True if there has been a register search, false otherwise
	 */
	function issetRegisters() {
		return ($this->_registers === false);
	}
	
	/**
	 * Gets all the registers currently selected
	 *
	 * @return array  The register objects in an array indexed by composite id
	 */
	function &getRegisters()
	{
		if ($this->_registers === false) {
			$this->setRegisters(array());
		}
		return $this->_registers;
	}
	
	/**
	 * Gets the number of registers currently stored
	 *
	 * @return int  The number of registers currently selected
	 */
	function getNoOfRegisters()
	{
		if ($this->_registers === false) {
			$this->setRegisters(array());
		}
		return $this->_noOfRegisters;
	}
	
	/**
	 * Set a property by selecting all the registers (taken or pending) that match the given criteria
	 * and returns the count of those registers (also stored in a property)
	 * Registers are not courses, and have a specific date.
	 *
	 * @param array $ids  The array of register or course objects
	 * @return int  The number of registers currently set at the end of the function
	 */
	function setRegisters($ids = array())
	{
		$this->_registers = array();
		if (is_array($ids)) {
			foreach ($ids as $id) {
				if (is_object($id)) {
					switch( strtolower(get_class($id)) ) {
					case('register'):
						$this->_registers[$id->getCompId()] = $id;
						break;
					
					case('course'):
						$dates = $id->getDates();
						foreach ($dates as $date) {
							$times = $id->getTimes($date);
							foreach ($times as $time) {
								$reg = new Register($id, $date, $time);
								$this->_registers[$reg->getCompId()] = $reg;
							}
						}
						break;
					}
				}
			}
		}
		return $this->_noOfRegisters = count($this->_registers);
	}
	
	/**
	 * Removes a register from the internal list
	 */
	function unsetRegister( $regId )
	{
		if ($this->_registers === false) {
			$this->setRegisters();
		}
		if (array_key_exists($regId, $this->_registers)) {
			unset($this->_registers[$regId]);
			return true;
		}
		else {
			return false;
		}
	}
	
	/**
	 * Retrieves all the attendance codes as a 2-D array ( class_type_1=>array(...), class_type_2=>array(...), ... )
	 *
	 * @return array  The 2-D array of all attendance codes
	 */
	function getAllAttendanceMarks()
	{
		if (empty($this->_all_attendanceMarks)) {
			$this->_all_attendanceMarks = array();
			$tmp = ApotheosisAttendanceData::getCodeObjects( array(), false );
			foreach($tmp as $k=>$v) {
				$this->_all_attendanceMarks[$v->type][$v->code] = $v;
			}
		}
		return $this->_all_attendanceMarks;
	}
	
	/**
	 * Accessor to the pupils not already picked out
	 *
	 * @return array  The array or pupil objects indexed by pupil id
	 */
	function getAllPupils()
	{
		if (empty($this->_all_pupils)) {
			$this->_loadAllPupils();
		}
		return $this->_all_pupils;
	}
	
	/**
	 * Gets the list of all pupils not involved in every register
	 */
	function _loadAllPupils()
	{
		if ($this->_registers === false) {
			$this->setRegisters();
		}
		$this->_all_pupils = array();
		foreach ($this->_registers as $k=>$reg) {
			$this->_all_pupils = array_merge($this->_all_pupils, $reg->getOtherPupils());
		}
	}
	
	/**
	 * Removes adhoc pupils from registers
	 * 
	 * @param array $removeArr  2-D array of pupil lists grouped by register id: $arr[regId][pupilId] = pupilId
	 */
	function unsetAdhoc($removeArr = array())
	{
		foreach($removeArr as $key=>$val) {
			if (array_key_exists($key, $this->_registers)) {
				$this->_registers[$key]->unsetAdhocPupils($val);
			}
		}
	}
	
	/**
	 * Adds adhoc pupils to registers
	 * 
	 * @param array $removeArr  2-D array of pupil lists grouped by register id: $arr[regId][pupilId] = pupilId
	 */
	function setAdhocPupils($list = array())
	{
		foreach($list as $key=>$val) {
			if (array_key_exists($key, $this->_registers)) {
				$this->_registers[$key]->setAdhocPupils($val);
			}
		}
	}

}
?>