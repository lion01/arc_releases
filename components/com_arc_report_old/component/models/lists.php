<?php
/**
 * @package     Arc
 * @subpackage  Report
 * @copyright   Copyright (C) 2005 Punnet. All rights reserved. See COPYRIGHT_ARC.txt
 * @license     http://www.gnu.org/licenses/gpl.html GNU/GPL. See LICENSE_ARC.txt
 * Arc is free software: you can redistribute it and/or modify
 * it under the terms of version 3 of the GNU General Public License
 * as published by the Free Software Foundation.
 * The disclaimer of warranty as stated in the GPL applies to this program
 */

defined( '_JEXEC' ) or die( 'Restricted access' );

require_once( JPATH_COMPONENT.DS.'models'.DS.'extension.php' );

/**
 * Reports Lists Model
 *
 * @author		lightinthedark <code@lightinthedark.org.uk>
 * @package		Apotheosis
 * @subpackage	Reports
 * @since 0.1
 */
class ReportsModelLists extends ReportsModel
{
	/** @var normal course list */
	var $_normalCourses;
	
	/** @var pastoral course list */
	var $_pastoralCourses;
	
	/** @var child course list */
	var $_children;
	
	/** @var group course id */
	var $_group;
	
	/** @var link to admin options */
	var $_links;
	
	
	function __construct( $config = array() )
	{
		parent::__construct( $config );
		$this->_group = false;
		$this->_links = array();
	}
	
	
	function setCycle( $id )
	{
		parent::setCycle( $id );
		$this->_group = false;
		$this->_links = array();
	}
	
	function getAdminLink()
	{
		if (!array_key_exists('admin', $this->_links)) {
			$this->_links['admin'] = $this->_loadLink('admin');
		}
		return $this->_links['admin'];
	}
	
	function getListLink()
	{
		$this->_links['lists'] = $this->_loadLink('lists');
		return $this->_links['lists'];
	}
	
	function getReportLink()
	{
		if (!array_key_exists('report', $this->_links)) {
			$this->_links['report'] = $this->_loadLink('report');
		}
		return $this->_links['report'];
	}
	
	function getOutputLink()
	{
		if (!array_key_exists('output', $this->_links)) {
			$this->_links['output'] = $this->_loadLink('output');
		}
		return $this->_links['output'];
	}
	
	/**
	 * Loads the admins for the current course from the database into this->_admins
	 * Only pulls out currently valid admins, and orders by valid_from
	 */
	function getGroupAdmins()
	{
		$this->_admins = $this->_loadGroupAdmins();
		return $this->_admins;
	}

	function _loadGroupAdmins()
	{
		$db = &JFactory::getDBO();
		$qStr = 'SELECT a.*, p.title, COALESCE( p.preferred_firstname, p.firstname ) AS firstname, p.middlenames, COALESCE( p.preferred_surname, p.surname ) AS surname'
			."\n".' FROM #__apoth_rpt_admins AS a'
			."\n".' INNER JOIN #__apoth_ppl_people AS p'
			."\n".'    ON a.person = p.id'
			."\n".'	WHERE '.ApotheosisLibDb::dateCheckSql( 'a.valid_from', 'a.valid_to', date('Y-m-d H:i:s'), date('Y-m-d H:i:s') )
			."\n".'   AND '.$db->nameQuote('cycle').' = '.$db->Quote($this->getCycleId())
//			.( (is_null($this->_group) || ($this->_group == ApotheosisLibDb::getRootItem())) ? '' : "\n".' AND a.'.$db->nameQuote('group').' = '.$this->_group )
			."\n".' ORDER BY a.'.$db->nameQuote('valid_from').' ASC';
		$db->setQuery( $qStr );
		$adminsArr = $db->loadObjectList();
		foreach( $adminsArr as $key=>$row ) {
			$adminsArr[$key]->displayname = ApotheosisLib::nameCase('teacher', $row->title, $row->firstname, $row->middlenames, $row->surname);
		}
		$duser = &JFactory::getUser();

		$admins = array();
		foreach($adminsArr as $k=>$v) {
			if(empty($admins[$v->group]->admin1)) {
				$admins[$v->group]->admin1 = $v->displayname;
			}
			else {
				$admins[$v->group]->admin2 = $v->displayname;				
			}
		}
		
		return $admins;
	}	

	function _loadLink( $view )
	{
		$db = &JFactory::getDBO();
		$criteria = array( 'option=com_arc_report', 'view='.$db->getEscaped($view) );
		$qStr = 'SELECT id, link'
		 ."\n".' FROM #__menu'
		 ."\n".' WHERE (link LIKE "%'.implode('%" AND link LIKE "%', $criteria).'%")'
		 ."\n".' ORDER BY LENGTH(link) ASC'
		 ."\n".' LIMIT 1';
		$db->setQuery($qStr);
		$r = $db->loadObject();

		return $r->link.'&Itemid='.$r->id;
	}
	
	/**
	 * Retrieves the "normal" courses (non-teaching groups)
	 *
	 * @return array  The course_id-indexed array of course objects
	 */
	function &getSubjects()
	{
		if (!isset($this->_subjects)) {
//			$this->_subjects = $this->_loadSubjects();
			$db = &JFactory::getDBO();
			$where = 'c.'.$db->nameQuote('id').' != '.$db->Quote( ApotheosisLibDb::getRootItem('#__apoth_cm_courses') );
			$having = $db->nameQuote('ct').' = '.$db->Quote('non');
			$this->_subjects = $this->_loadCourses( $where, $having );
		}
		return $this->_subjects;
	}
	
	/**
	 * Retrieves the "pastoral" courses
	 *
	 * @return array  The course_id-indexed array of course objects
	 */
	function &getPastoralCourses()
	{
		if (!isset($this->_pastoralCourses)) {
			$db = &JFactory::getDBO();
			$this->_pastoralCourses = $this->_loadCourses( 'c.'.$db->nameQuote('type').' = '.$db->Quote('pastoral') );
		}
		return $this->_pastoralCourses;
	}
	
	/**
	 * Loads the courses specified by the given WHERE string
	 *
	 * @return array  The course_id-indexed array of course objects
	 */
	function &getCourses( $whereStr )
	{
		if( ($this->_lastWhereStr !== $whereStr) || !isset($this->_courses) ) {
			$this->_courses = $this->_loadCourses( $whereStr );
		}
		return $this->_courses;
	}
	
	/**
	 * Loads the courses specified by the given WHERE string
	 *
	 * @param string $whereStr  The WHERE string to limit which courses are loaded
	 * @return array  The course_id-indexed array of course objects
	 */
	function _loadCourses( $whereStr, $havingStr = false )
	{
		$db = &JFactory::getDBO();
		$query = 'SELECT c.*, COUNT(gc.'.$db->nameQuote('id').') AS '.$db->nameQuote('childcount').', COALESCE( pc.'.$db->nameQuote('type').' , c.'.$db->nameQuote('type').' ) AS '.$db->nameQuote('ct')
			."\n".' FROM '.$db->nameQuote('#__apoth_cm_courses').' AS '.$db->nameQuote('c')
			."\n".' INNER JOIN '.$db->nameQuote('#__apoth_cm_courses').' AS '.$db->nameQuote('p')
			."\n".'   ON p.'.$db->nameQuote('id').' = c.'.$db->nameQuote('parent')
			."\n".' LEFT JOIN '.$db->nameQuote('#__apoth_cm_courses').' AS '.$db->nameQuote('gc')
			."\n".'   ON gc.'.$db->nameQuote('parent').' = c.'.$db->nameQuote('id')
			."\n".'  AND gc.'.$db->nameQuote('deleted').' = '.$db->Quote('0')
			."\n".' INNER JOIN '.$db->nameQuote('#__apoth_rpt_cycles_groups').' AS '.$db->nameQuote('cg')
			."\n".'    ON cg.'.$db->nameQuote('group').' = c.'.$db->nameQuote('id')
			."\n".'   AND cg.'.$db->nameQuote('cycle').' = '.$db->Quote($this->getCycleId())
			."\n".' LEFT JOIN '.$db->nameQuote('#__apoth_cm_pseudo_map').' AS '.$db->nameQuote('pm')
			."\n".'   ON pm.'.$db->nameQuote('course').' = c.'.$db->nameQuote('id')
			."\n".' LEFT JOIN '.$db->nameQuote('#__apoth_cm_courses').' AS '.$db->nameQuote('pc')
			."\n".'   ON pc.'.$db->nameQuote('id').' = pm.'.$db->nameQuote('twin')
			."\n".'  AND pc.'.$db->nameQuote('deleted').' = '.$db->Quote('0')
			."\n".' WHERE ('.$whereStr.')'
//			."\n".'  AND c.year = '.$this->_cycle->year_group
			."\n".'  AND c.'.$db->nameQuote('deleted').' = '.$db->Quote('0')
			."\n".'  AND '.ApotheosisLibDb::dateCheckSql( 'c.'.$db->nameQuote('start_date'), 'c.'.$db->nameQuote('end_date'), $this->getCycleStart(), $this->getCycleEnd() )
			."\n".' GROUP BY c.'.$db->nameQuote('id')
			.( ($havingStr === false) ? '' : "\n".' HAVING '.$havingStr )
			."\n".' ORDER BY p.'.$db->nameQuote('fullname').', c.'.$db->nameQuote('fullname');
		$db->setQuery($query);
		$results = $db->loadObjectList('id');
		if( !is_array($results) ) { $results = array(); }
		foreach( $results as $k=>$v ) {
			$results[$k]->teachers = array();
			$results[$k]->admins = array();
		}
		
		// get the teachers
		$query = 'SELECT gm.group_id, p.id, p.title, COALESCE( p.preferred_firstname, p.firstname ) AS firstname, p.middlenames, COALESCE( p.preferred_surname, p.surname ) AS surname'
			."\n".' FROM #__apoth_tt_group_members AS gm'
			."\n".' INNER JOIN #__apoth_ppl_people AS p'
			."\n".'    ON p.id = gm.person_id'
			."\n".' WHERE gm.group_id IN ('.implode(', ', array_keys($results) ).')'
			."\n".'   AND '.ApotheosisLibDb::dateCheckSql( 'gm.valid_from', 'gm.valid_to', $this->getCycleStart(), $this->getCycleEnd() )
			."\n".'   AND gm.is_teacher = 1'; // *** titikaka
		$db->setQuery($query);
		$teachers = $db->loadObjectList();
		if( !is_array($teachers) ) { $teachers = array(); }
		
		foreach( $teachers as $row ) {
			$course = $row->group_id;
			if( isset($results[$course]) ) {
				$results[$course]->teachers[$row->id] = ApotheosisLib::nameCase( 'teacher', $row->title, $row->firstname, $row->middlenames, $row->surname );
			}
		}
		
		// get the admins
		$query = 'SELECT a.`group`, p.id, p.title, COALESCE( p.preferred_firstname, p.firstname ) AS firstname, p.middlenames, COALESCE( p.preferred_surname, p.surname ) AS surname'
			."\n".' FROM #__apoth_rpt_admins AS a'
			."\n".' INNER JOIN #__apoth_ppl_people AS p'
			."\n".'    ON a.person = p.id'
			."\n".'	WHERE '.ApotheosisLibDb::dateCheckSql( 'a.valid_from', 'a.valid_to', date('Y-m-d H:i:s'), date('Y-m-d H:i:s') )
			."\n".'   AND '.$db->nameQuote('cycle').' = '.$db->Quote($this->getCycleId());
		$db->setQuery( $query );
		$admins = $db->loadObjectList();
		if( !is_array($admins) ) { $admins = array(); }
		
		foreach( $admins as $row ) {
			$course = $row->group;
			if( isset($results[$course]) ) {
				$results[$course]->admins[$row->id] = ApotheosisLib::nameCase( 'teacher', $row->title, $row->firstname, $row->middlenames, $row->surname );
			}
		}
		
		return $results;
	}
	
	function getStudentCourses()
	{
		if (!isset($this->_courses)) {
			$this->_courses = $this->_loadStudentCourses();
		}
		return $this->_courses;
	}
	
	function _loadStudentCourses()
	{
		$db = &JFactory::getDBO();
		if(is_array($this->_group)) {
			foreach($this->_group as $k=>$v) {
				$this->_group[$k] = $db->Quote($v);
			}
			$whereStr = '`person_id` IN ('.implode(', ', $this->_group).')';
		}
		else {
			$whereStr = '`person_id` = '.$db->Quote($this->_group);
		}
		$query = 'SELECT group_id, ps.course'
			."\n".' FROM #__apoth_tt_group_members AS gm'
			."\n".' LEFT JOIN #__apoth_cm_pseudo_map AS ps'
			."\n".'   ON ps.twin = gm.group_id'
			."\n".' INNER JOIN #__apoth_rpt_cycles_groups AS cg'
			."\n".'    ON cg.'.$db->nameQuote('group').' = gm.'.$db->nameQuote( 'group_id' )
			."\n".' WHERE '.$whereStr
			."\n".'   AND `is_student` = 1' // *** titikaka
			."\n".'   AND '.ApotheosisLibDb::dateCheckSql( 'gm.valid_from', 'gm.valid_to', $this->getCycleStart(), $this->getCycleStart() )
			."\n".'   AND '.$db->nameQuote('cycle').' = '.$db->Quote( $this->getCycleId() );
		$db->setQuery($query);
		$groups = $db->loadObjectList();
		foreach($groups as $group) {
			$ancestors[] = ApotheosisLibDb::getAncestors( $group->group_id, '#__apoth_cm_courses', 'id', 'parent', true );
			if( !is_null($group->course) ) {
				$ancestors[] = ApotheosisLibDb::getAncestors( $group->course, '#__apoth_cm_courses', 'id', 'parent', true );
			}
		}
		foreach($ancestors as $aList) {
			foreach($aList as $k=>$v) {
				if( is_object($subjects[$k]) ) {
					$subjects[$k]->_children = array_merge( $subjects[$k]->_children, $v->_children );
				}
				else {
					$subjects[$k] = $v;
				}
			}
		}
		return $subjects;
	}
	
	/**
	 * Sets the current course_id to use when getting child courses
	 *
	 * @param int $id  The course_id of the current course
	 */
	function setGroup( $id )
	{
		// when there's a change, clear out the data we have loaded
		if( $this->_group !== $id ) {
			unset($this->_courses);
			unset($this->_parent);
			unset($this->_children);
			unset($this->_students);
		}
		$this->_group = $id;
	}
	
	/**
	 * Sets the source course_id to use when getting child courses
	 *
	 * @param int $id  The course_id of the current course
	 */
	function setSourceGroup( $id )
	{
		// when there's a change, clear out the data we have loaded
		if( $this->_sourceGroup !== $id ) {
			unset($this->_courses);
			unset($this->_parent);
			unset($this->_children);
			unset($this->_students);
		}
		$this->_sourceGroup = $id;
	}
	
	/**
	 * Accessor to retrieve the current group which is current to all items listed
	 *
	 * @return int  The id number of the current group
	 */
	function getGroup()
	{
		return $this->_group;
	}

	/**
	 * Accessor to retrieve the source group which is current to all items listed
	 *
	 * @return int  The id number of the current group
	 */
	function getSourceGroup()
	{
		return $this->_sourceGroup;
	}
	
	function getGrandparent()
	{
		if( !isset($this->_grandparent) ) {
			$this->_loadParent();
		}
		return $this->_grandparent;
	}
	function getParent()
	{
		if( !isset($this->_parent) ) {
			$this->_loadParent();
		}
		return $this->_parent;
	}
	/**
	 * Loads the parent and grandparent of the current group
	 */
	function _loadParent()
	{
		$db = &JFactory::getDBO();
		$g = $db->Quote( $this->_group );
		$query = 'SELECT p.id AS parent, p.parent AS grandparent'
			."\n".'FROM #__apoth_cm_courses AS c'
			."\n".'INNER JOIN #__apoth_cm_courses AS p'
			."\n".'   ON p.id = c.parent'
			."\n".'WHERE c.id = '.$g
			."\n".'  AND c.deleted = 0';
		$db->setQuery($query);
		$r = $db->loadAssoc();
		$this->_parent = $r['parent'];
		$this->_grandparent = $r['grandparent'];
	}
	
	/**
	 * Retrieves the courses which are children of the previously set current course
	 *
	 * @return array  The course_id-indexed array of course objects
	 */
	function &getChildren()
	{
		if (!isset($this->_children)) {
			$db = &JFactory::getDBO();
			if(is_array($this->_group)) {
				foreach($this->_group as $k=>$v) {
					$g[$k] = $db->Quote( $v );
				}
				$whereStr = 'c.`parent` IN ('.implode(', ', $g).')';
			}
			else {
				$g = $db->Quote( $this->_group );
				$whereStr = 'c.`parent` = '.$g;
			}
			$this->_children = $this->_loadCourses( $whereStr );
		}
		return $this->_children;
	}
	
	/**
	 * Retrieves the students that are part of the current course/group
	 *
	 * @return array  The array of student information objects
	 */
	function &getStudents()
	{
		if (!isset($this->_students)) {
			$db = &JFactory::getDBO();
			if(is_array($this->_group)) {
				foreach($this->_group as $k=>$v) {
					$g[$k] = $db->Quote( $v );
				}
				$whereStr = '`c`.`id` IN ('.implode(', ', $g).')';
			}
			else {
				$g = $db->Quote( $this->_group );
				$whereStr = '`c`.`id` = '.$g;
			}
			$this->_students = $this->_loadStudents( $whereStr );
		}
		return $this->_students;
	}
	
	/**
	 * Loads the students specified by the WHERE string
	 *
	 * @param string $whereStr  The WHERE string to limit which students are loaded
	 * @return array  The array of student information objects
	 */
	function _loadStudents( $whereStr )
	{
		$db = &JFactory::getDBO();
		
		$tmpName = $db->nameQuote( 'tmp_'.str_replace(array(' ', '.'), '_',microtime()) );
		$query = 'CREATE TEMPORARY TABLE '.$tmpName.' AS'
			."\n".'SELECT c.id, COALESCE(p.twin, c.id) AS enrol_id'
			."\n".'FROM jos_apoth_cm_courses AS c'
			."\n".'LEFT JOIN jos_apoth_cm_pseudo_map AS p'
			."\n".'   ON p.course = c.id'
			."\n".'WHERE '.$whereStr
			."\n".'  AND c.deleted = 0';
		$db->setQuery($query);
		$db->Query();
		
		$query = ' SELECT DISTINCT p.id AS pupilid, t.id AS courseid, COALESCE( p.preferred_firstname, p.firstname ) AS firstname, p.middlenames, COALESCE( p.preferred_surname, p.surname ) AS surname'
			."\n".' FROM jos_apoth_ppl_people AS p'
			."\n".' INNER JOIN jos_apoth_tt_group_members AS gm'
			."\n".'    ON gm.person_id = p.id'
			."\n".' INNER JOIN '.$tmpName.' AS t'
			."\n".'    ON t.enrol_id = gm.group_id'
			."\n".' WHERE is_student = 1' // *** titikaka
			."\n".'  AND '.ApotheosisLibDb::dateCheckSql('gm.valid_from', 'gm.valid_to', $this->getCycleStart(), $this->getCycleStart())
			."\n".' ORDER BY surname';
		$db->setQuery($query);
		$results = $db->loadObjectList();

		foreach( $results as $key=>$row ) {
			$results[$key]->displayname = ApotheosisLib::nameCase('pupil', '', $row->firstname, $row->middlenames, $row->surname);
		}
		return $results;
	}
}

?>
