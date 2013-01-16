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

/**
 * Timetable Manager Enrolments Model
 *
 * @author     lightinthedark
 * @package    Apotheosis
 * @subpackage Timetable
 * @since		1.5
 */
class TimetableModelEnrolments extends JModel
{
	function __construct()
	{
		parent::__construct();
	}
	
	function getEnrolments()
	{
		if (!isset($this->_enrolments)) {
			$this->setEnrolments( array() );
		}
		return $this->_enrolments;
	}
	
	function setEnrolments( $requirements )
	{
		if (!isset($this->_courses)) {
			$this->setCourses( $requirements );
		}
		$db = &JFactory::getDBO();
		
		$query = 'SELECT gm.group_id AS course_id, p.id, COALESCE( p.preferred_firstname, p.firstname ) AS firstname, COALESCE( p.preferred_surname, p.surname ) AS surname'
			."\n".'FROM #__apoth_ppl_people AS p'
			."\n".'INNER JOIN #__apoth_tt_group_members AS gm'
			."\n".'   ON gm.person_id = p.id'
			."\n".'INNER JOIN #__apoth_cm_courses AS c'
			."\n".'   ON c.id = gm.group_id'
			."\n".'  AND c.deleted = 0'
			."\n".'WHERE gm.group_id IN ("'.implode('", "', array_keys($this->_courses)).'")'
			."\n".'ORDER BY c.shortname, p.surname, p.firstname';
		$db->setQuery( $query );
		$results = $db->loadObjectList();
		
		foreach ($results as $row) {
			$cId = $row->course_id;
			unset($row->course_id);
			$this->_enrolments[$cId][$row->id] = $row;
		}
	}
	
	function getCourses()
	{
		if (!isset($this->_courses)) {
			$this->setCourses( array() );
		}
		return $this->_courses;
	}
	
	function setCourses( $requirements )
	{
		$db = &JFactory::getDBO();
		$whereStr = array();
		$innerStr = array();
		// construct where strings
		foreach ($requirements as $col=>$val) {
			if(($val != NULL) || ($val != '')) {
				if (is_array($val)) {
					foreach ($val as $k=>$v) {
						$val[$k] = $db->getEscaped($v);
					}
					$val = '"'.implode('", "', $val).'"';
				}
				else {
					$val = $db->getEscaped($val);
				}
				switch($col) {
				case('courseType'):
					$whereStrs[] = '`c`.`type` = "'.$val.'"';
					break;
				}
			}
		}
		$whereStrs[] = '`c`.`deleted` = 0';
		
		$whereStr = "\n".' WHERE '.implode(' AND ', $whereStrs);
		
		$query = 'SELECT c.id, c.shortname, c.fullname'
			."\n".' FROM #__apoth_cm_courses AS c'
			.$whereStr;
		$db->setQuery( $query );
		$this->_courses = $db->loadObjectList( 'id' );
	}
}
?>