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
 * Utility class for generating arc timetable specific HTML entities
 *
 * @static
 * @package    Arc
 * @subpackage Timetable
 * @since      1.5
 */
class JHTMLArc_Timetable
{
	/**
	 * Generate HTML to display a classes select box with the given name
	 *
	 * @param string $name  The name to use for the input
	 * @param string $default  Default input value
	 * @param boolean $multiple  Allow multiple selects?
	 * @return string $retVal  The HTML to display the required input
	 */
	function classes( $name, $default = null, $multiple = false, $disabled = false )
	{
		$default = ( !is_null($default) ? $default : '' );
		$oldVal = JRequest::getVar( $name, $default );
		$class = new stdClass();
		$class->id = '';
		$class->shortname = '';
		$classes[''] = $class;
		
		$db = &JFactory::getDBO();
		$query = 'SELECT DISTINCT c.id, c.type, c.shortname'
			."\n".'FROM #__apoth_cm_courses AS c'
			."\n".'INNER JOIN #__apoth_tt_timetable AS t'
			."\n".'   ON t.course = c.id'
			."\n".'WHERE '.JHTML::_( 'arc._dateCheck', 'c.start_date', 'c.end_date' )
			."\n".'  AND '.JHTML::_( 'arc._dateCheck', 't.valid_from', 't.valid_to' )
			."\n".'  AND c.deleted = 0'
			."\n".'ORDER BY type, sortorder, shortname';
		$db->setQuery( $query );
		$classes = array_merge( $classes, $db->loadObjectList('id') );
		
		if( $disabled ) {
			$retVal2 = JHTML::_( 'arc.hidden', $name, $default );
			$name = '_dis_'.$name;
		}
		else {
			$retVal2 = '';
		}
		
		$name = ( $multiple ? $name.'[]' : $name );
		$attribs = ( $multiple ? 'multiple="multiple" class="multi_medium" ' : '' );
		$attribs .= ( $disabled ? 'disabled="disabled" ' : '' );
		
		$retVal =  JHTML::_( 'select.genericList', $classes, $name, $attribs, 'id', 'shortname', $oldVal );
		$retVal .= JHTML::_( 'arc.hidden', 'search_default_'.$name, $default, 'class="search_default"' );
		$retVal .= $retVal2;
		
		return $retVal;
	}
	
	/**
	 * Generate HTML to display a tutor class select box with the given name
	 *
	 * @param string $name  The name to use for the input
	 * @param string $default  Default input value
	 * @param boolean $multiple  Allow multiple selects?
	 * @return string $retVal  The HTML to display the required input
	 */
	function tutorgroups( $name, $default = null, $multiple = false )
	{
		$default = ( !is_null($default) ? $default : '' );
		$oldVal = JRequest::getVar( $name, $default );
		$class = new stdClass();
		$class->id = '';
		$class->shortname = '';
		$classes[''] = $class;
		
		$db = &JFactory::getDBO();
		$query = 'SELECT DISTINCT c.id, c.type, c.shortname'
			."\n".'FROM #__apoth_cm_courses AS c'
			."\n".'WHERE '.JHTML::_( 'arc._dateCheck', 'c.start_date', 'c.end_date' )
			."\n".'  AND c.type = \'pastoral\''
			."\n".'  AND c.deleted = 0'
			."\n".'ORDER BY type, sortorder, shortname';
		$db->setQuery( $query );
		$classes = array_merge( $classes, $db->loadObjectList('id') );
		
		$attribs = ( $multiple ? 'multiple="multiple" class="multi_medium"' : '' );
		$name = ( $multiple ? $name.'[]' : $name );
		
		$retVal =  JHTML::_( 'select.genericList', $classes, $name, $attribs , 'id', 'shortname', $oldVal );
		$retVal .= JHTML::_( 'arc.hidden', 'search_default_'.$name, $default, 'class="search_default"' );
		
		return $retVal;
	}
	
	/**
	 * Generate HTML to display a room select box with the given name
	 *
	 * @param string $name  The name to use for the input
	 * @param string $default  Default input value
	 * @param boolean $multiple  Allow multiple selects?
	 * @return string $retVal  The HTML to display the required input
	 */
	function room( $name, $default = null, $multiple = false )
	{
		$default = ( !is_null($default) ? $default : '' );
		$oldVal = JRequest::getVar( $name, $default );
		$room = new stdClass();
		$room->room = '';
		$rooms[''] = $room;
		
		$db = &JFactory::getDBO();
		$query = 'SELECT DISTINCT room_id'
			."\n".' FROM #__apoth_tt_timetable'
			."\n".' ORDER BY room_id';
		$db->setQuery( $query );
		$rooms = array_merge( $rooms, $db->loadObjectList('room_id') );
		
		if ($multiple) {
			$retVal = JHTML::_('select.genericList', $rooms, $name.'[]', 'multiple="multiple" class="multi_medium"', 'room_id', 'room_id', $oldVal);
		}
		else {
			$retVal = JHTML::_('select.genericList', $rooms, $name, '', 'room_id', 'room_id', $oldVal);
		}
		$retVal .= JHTML::_( 'arc.hidden', 'search_default_'.$name, $default, 'class="search_default"' );
		
		return $retVal;
	}
	
	/**
	 * Generate HTML to display a pupil select box with the given name
	 *
	 * @param string $name  The name to use for the input
	 * @param string $default  Default input value
	 * @param boolean $multiple  Allow multiple selects?
	 * @return string $retVal  The HTML to display the required input
	 */
	function pupil( $name, $default = null, $multiple = false )
	{
		$retVal =  JHTML::_( 'arc._renderOtherPupils', $name, null, $multiple, array() );
		$retVal .= JHTML::_( 'arc.hidden', 'search_default_'.$name, $default, 'class="search_default"' );
		
		return $retVal;
	}
	
	/**
	 * Generate HTML to display a teacher select box with the given name
	 *
	 * @param string $name  The name to use for the input
	 * @param string $default  Default input value
	 * @param boolean $multiple  Allow multiple selects?
	 * @return string $retVal  The HTML to display the required input
	 */	
	
	function teacher( $name, $default = null, $multiple = false )
	{
		$u = ApotheosisLib::getUser();
		$default = ( !is_null($default) ? $default : $u->person_id );
		$oldVal = JRequest::getVar( $name, $default );
		$teacher = new stdClass();
		$teacher->id = '';
		$teacher->displayname = '';
		$dummyTeachers[''] = $teacher;
		
		$db = &JFactory::getDBO();
		$query = 'SELECT DISTINCT p.id, p.title, COALESCE( p.preferred_firstname, p.firstname ) AS firstname, p.middlenames, COALESCE( p.preferred_surname, p.surname ) AS surname'
			."\n".' FROM #__apoth_ppl_people AS p'
			."\n".' INNER JOIN #__apoth_tt_group_members AS c ON c.person_id = p.id'
			."\n".' WHERE c.is_teacher = 1' // *** titikaka
			."\n".' AND '.JHTML::_( 'arc._dateCheck', 'c.valid_from', 'c.valid_to' )
			."\n".' ORDER BY COALESCE( p.preferred_surname, p.surname ), COALESCE( p.preferred_firstname, p.firstname )';
		$db->setQuery($query);
		$teachers = $db->loadObjectList('id');
		
		foreach( $teachers as $key=>$row ) {
			$teachers[$key]->displayname = ApotheosisLib::nameCase('teacher', $row->title, $row->firstname, $row->middlenames, $row->surname);
		}
		$teachers = array_merge($dummyTeachers, $teachers);
		
		if ($multiple) {
			$retVal =  JHTML::_( 'select.genericList', $teachers, $name.'[]', 'multiple="multiple" class="multi_medium"', 'id', 'displayname', $oldVal );
		}
		else {
			$retVal =  JHTML::_( 'select.genericList', $teachers, $name, '', 'id', 'displayname', $oldVal );
		}
		$retVal .= JHTML::_( 'arc.hidden', 'search_default_'.$name, $default, 'class="search_default"' );
		
		return $retVal;
	}
	
	/**
	 * Generate HTML to display a day section select box with the given name
	 *
	 * @param string $name  The name to use for the input
	 * @param string $default  Default input value
	 * @param boolean $multiple  Allow multiple selects?
	 * @return string $retVal The HTML to display the required input
	 */
	function day_details( $name, $default = null, $multiple = false )
	{
		$default = ( !is_null($default) ? $default : '' );
		$oldVal = JRequest::getVar( $name, $default );
		$day_section = new stdClass();
		$day_section->day_type = '';
		$day_section->day_section = '';
		$day_section->start_time = '';
		$day_sections[''] = $day_section;
		
		$db = &JFactory::getDBO();
		$query = 'SELECT day_type, day_section, start_time, end_time'
			."\n".' FROM #__apoth_tt_daydetails'
			."\n".' WHERE '.JHTML::_( 'arc._dateCheck', 'valid_from', 'valid_to' )
			."\n".' GROUP BY day_section'
			."\n".' ORDER BY day_type, start_time';
		$db->setQuery( $query );
		$periods = array_merge($day_sections, $db->loadObjectList('day_section'));
		
		if ($multiple) {
			$retVal =  JHTML::_('select.genericList', $periods, $name.'[]', 'multiple="multiple" class="multi_small"', 'day_section', 'day_section', $oldVal);
		}
		else {
			$retVal =  JHTML::_('select.genericList', $periods, $name, '', 'day_section', 'day_section', $oldVal);
		}
		$retVal .= JHTML::_( 'arc.hidden', 'search_default_'.$name, $default, 'class="search_default"' );
		
		return $retVal;
	}	

}
?>