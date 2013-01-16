<?php
/**
 * @package     Arc
 * @subpackage  Course
 * @copyright   Copyright (C) 2005 Punnet. All rights reserved. See COPYRIGHT_ARC.txt
 * @license     http://www.gnu.org/licenses/gpl.html GNU/GPL. See LICENSE_ARC.txt
 * Arc is free software: you can redistribute it and/or modify
 * it under the terms of version 3 of the GNU General Public License
 * as published by the Free Software Foundation.
 * The disclaimer of warranty as stated in the GPL applies to this program
 */

defined( '_JEXEC' ) or die( 'Restricted access' );

jimport( 'joomla.application.component.model' );
require_once( JPATH_SITE.DS.'components'.DS.'com_arc_core'.DS.'libraries'.DS.'apoth_library.php' ); 

 /*
 * Course Manager List Model
 *
 * @author     lightinthedark <code@lightinthedark.org.uk>
 * @package    Arc
 * @subpackage Course Manager
 * @since      0.1
 */
class CourseModelList extends JModel
{
	/** @var array Array of people */
	var $_courses = array();
	
	function &getCourses()
	{
		if( empty($this->_courses) ) {
			$this->_loadCourses();
		}
		return $this->_courses;
	}
	
	function _loadCourses()
	{
		$db = &JFactory::getDBO();
		
		$query = 'SELECT COALESCE(cp.fullname, "--") AS subject, c.fullname AS class, c.start_date, p.title, COALESCE( p.preferred_firstname, p.firstname ) AS firstname, p.middlenames, COALESCE( p.preferred_surname, p.surname ) AS surname'
			."\n".' FROM #__apoth_cm_courses AS c'
			."\n".' LEFT JOIN #__apoth_cm_courses AS cp'
			."\n".'   ON cp.id = c.parent'
			."\n".' INNER JOIN jos_apoth_tt_group_members AS gm'
			."\n".'    ON gm.group_id = c.id'
			."\n".'   AND gm.is_teacher = 1' // *** titikaka
			."\n".'   AND gm.valid_from < NOW()'
			."\n".'   AND (gm.valid_to > NOW() OR gm.valid_to IS NULL)'
			."\n".' INNER JOIN jos_apoth_ppl_people AS p'
			."\n".'    ON p.id = gm.person_id'
			."\n".' ~LIMITINGJOIN~'
			."\n".' WHERE c.deleted = 0';
		$db->setQuery( ApotheosisLibAcl::limitQuery($query, 'timetable.groups', 'c') );
		$this->_courses = $db->loadObjectList();
		if( !is_array($this->_courses) ) { $this->_courses = array(); }
		foreach( $this->_courses as $key=>$row ) {
			$this->_courses[$key]->teacher = ApotheosisLib::nameCase( 'teacher', $row->title, $row->firstname, $row->middlenames, $row->surname );
			unset( $this->_courses[$key]->title );
			unset( $this->_courses[$key]->firstname );
			unset( $this->_courses[$key]->middlenames );
			unset( $this->_courses[$key]->surname );
		}
	}
}
?>