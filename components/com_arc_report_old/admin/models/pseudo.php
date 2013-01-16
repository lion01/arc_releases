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

jimport( 'joomla.application.component.model' );
jimport( 'joomla.installer.installer' );
require_once( JPATH_COMPONENT_ADMINISTRATOR.DS.'helpers'.DS.'cycles.php' );

/**
 * Extension Manager Summary Model
 *
 * @author		Louis Landry <louis.landry@joomla.org>
 * @package		Joomla
 * @subpackage	Installer
 * @since		1.5
 */
class ReportsModelPseudo extends JModel
{
	/** @var array Array of installed components */
	var $_items = array();
	
	/** @var object of the last individually retrieved item */
	var $_item = false;
	
	/** @var array 2-d array of all meanings for marks */
	var $_meanings = array();
	
	/** @var string Name of the property defining the individual item */
	var $_properties = 'code';
	
	/**
	 * Overridden constructor
	 * @access	protected
	 */
	function __construct()
	{
		parent::__construct();
	}

	/**
	 *
	 * @param array $colVals  Associative array of column=>value pairs from which to create a WHERE clause
	 */
	function &setItems($colVals)
	{
		foreach ($colVals as $k=>$v ) {
			$whereArr[] = '`'.$k.'` = "'.$v.'"';
		}
		$whereStr = implode(' AND ', $whereArr);
		
		$this->_loadItems($whereStr);
	}
	
	function &getCourses()
	{
		if (empty($this->_courses)) {
			// Load the items
			$this->_loadCourses();
		}
		return $this->_courses;
	}
	
	function _loadCourses($whereStr = false)
	{
		/* Get a database connector */
		$db =& JFactory::getDBO();
		
		$query = 'SELECT `c`.*, `c2`.`fullname` AS `twin_name`, `c3`.`fullname` AS `parent_name`'
			."\n".'FROM `#__apoth_cm_courses` AS c'
			."\n".'INNER JOIN `#__apoth_cm_pseudo_map` AS p'
			."\n".'   ON p.course = c.id'
			."\n".'  AND c.deleted = 0'
			."\n".'INNER JOIN `#__apoth_cm_courses` AS c2'
			."\n".'   ON p.twin = c2.id'
			."\n".'  AND c2.deleted = 0'
			."\n".'INNER JOIN `#__apoth_cm_courses` AS c3'
			."\n".'   ON c.parent = c3.id'
			."\n".'  AND c3.deleted = 0'
			.($whereStr === false ? '' : "\n".' WHERE '.$whereStr)
			."\n".'ORDER BY `c`.`start_date` DESC, `c`.`parent`, `c`.`fullname`, `c`.`year`';
		$db->setQuery($query);
		$rows = $db->loadObjectList();

		$numRows = count($rows);
		
		$this->setState('pagination.total', 20);
		$this->_courses = $rows;
	}
	
	function getCurrentCycles()
	{
		$db = JFactory::getDBO();
		$query = 'SELECT *, CONCAT("Year ", `year_group`, " - ", `valid_from`) AS `displayname`'
			."\n".' FROM `#__apoth_rpt_cycles`'
			."\n".' WHERE '.ApotheosisLibDb::dateCheckSql('`valid_from`', '`valid_to`', date('Y-m-d'), date('Y-m-d') );
		$db->setQuery($query);
		$cycles = $db->loadObjectList('id');

		return $cycles;
	}
	
	function getCourse()
	{
		$db = &JFactory::getDBO();
		$cycle = current(JRequest::getVar('eid'));
		
		$query = 'SELECT *'
			."\n".'FROM `#__apoth_cm_courses`'
			."\n".'WHERE `type` = '.$db->Quote('pseudo')
			."\n".'  AND `deleted` = '.$db->Quote('0')
			."\n".'ORDER BY `start_date`, `fullname`';
		$db->setQuery($query);
		
		return $db->loadObject();
	}
	
	function getEnrolSubjects()
	{
		$db = &JFactory::getDBO();
		
		$query = 'SELECT *'
			."\n".'FROM `#__apoth_cm_courses`'
			."\n".'WHERE `type` = "non"'
			."\n".'  AND `ext_type` = "subject"'
			."\n".'  AND `deleted` = "0"'
			."\n".'  AND '.ApotheosisLibDb::dateCheckSql('`start_date`', '`end_date`', date('Y-m-d'), date('Y-m-d') )
			."\n".'ORDER BY `fullname`';
		$db->setQuery($query);
		$results = $db->loadObjectList();
		return $results;
	}
	
	function getEnrolCourses()
	{
		$db = &JFactory::getDBO();
		
		$query = 'SELECT *'
			."\n".'FROM `#__apoth_cm_courses`'
			."\n".'WHERE `type` = "non"'
			."\n".'  AND `ext_type` = "course"'
			."\n".'  AND `deleted` = "0"'
			."\n".'  AND '.ApotheosisLibDb::dateCheckSql('`start_date`', '`end_date`', date('Y-m-d'), date('Y-m-d') )
			."\n".'ORDER BY `fullname`';
		$db->setQuery($query);
		$results = $db->loadObjectList();
		return $results;
	}
	
	function getEnrolClasses()
	{
		$db = &JFactory::getDBO();
		
		$query = 'SELECT *'
			."\n".'FROM `#__apoth_cm_courses`'
			."\n".'WHERE `ext_type` = "class"'
			."\n".'  AND `deleted` = "0"'
			."\n".'  AND '.ApotheosisLibDb::dateCheckSql('`start_date`', '`end_date`', date('Y-m-d'), date('Y-m-d') )
			."\n".'ORDER BY `fullname`';
		$db->setQuery($query);
		$results = $db->loadObjectList();
		return $results;
	}
	
	function getEnrolPseudo()
	{
		$db = &JFactory::getDBO();
		
		$query = 'SELECT *'
			."\n".'FROM `#__apoth_cm_courses`'
			."\n".'WHERE `type` = "pseudo"'
			."\n".'  AND `deleted` = "0"'
			."\n".'  AND '.ApotheosisLibDb::dateCheckSql('`start_date`', '`end_date`', date('Y-m-d'), date('Y-m-d') )
			."\n".'ORDER BY `fullname`';
		$db->setQuery($query);
		$results = $db->loadObjectList();
		return $results;
	}

	function getAllGroups()
	{
		$db = &JFactory::getDBO();
		$root = new stdClass();
		$root->id = 0;
		$kids = array($root);
		
		do {
			$parents = $kids;
			foreach( $parents as $k=>$v ) {
				$parents[$k] = $db->Quote($v->id);
			}
			$parentStr = implode(', ', $parents);
			
			$query = 'SELECT id, fullname'
				."\n".'FROM #__apoth_cm_courses'
				."\n".'WHERE '.$db->nameQuote('parent').' IN ('.$parentStr.')'
				."\n".'  AND '.$db->nameQuote('id').' != 0'
				."\n".'  AND '.$db->nameQuote('deleted').' = 0';
			$db->setQuery( $query );
			$kids = $db->loadObjectList('id');
			
			$retVal[] = $kids;
			
		} while( !empty($kids) );
		
		return $retVal;
	}
	
	/**
	 * Updates a Report Pseudo Course's details to the db
	 */
	function updateCourse( $params = array() )
	{
		if(empty($params)) {
			return false;
		}
		if($params['enrolment_class'] != '') {
			$parent_id = $params['enrolment_class'];			
		}
		elseif($params['enrolment_course'] != '') {
			$parent_id = $params['enrolment_course'];
		}
		elseif($params['enrolment_subject'] != '') {
			$parent_id = $params['enrolment_subject'];
		}
		else {
			return false;
		}
		// Get a database connector
		$db =& JFactory::getDBO();
		
		// write the changes, can't use $db->updateObject as key may have changed
		// * may move the id change into an sql up in the first "if", then use the
		// * updateObject function down here, just seems in-efficient.
		$sqlStr = 'UPDATE `#__apoth_rpt_pseudo` SET'
			."\n".' 	`fullname`				= '.$db->quote($params['fullname'])
			."\n".', 	`shortname`				= '.$db->quote($params['shortname'])
			."\n".', 	`cycle_id`				=	'.$db->quote($params['cycle_id'])
			."\n".', 	`enrolment_range`	=	'.$db->quote($enrolment_range)
			."\n".' WHERE `id`= '.$db->quote($params['id']);
		$db->setQuery($sqlStr);

		$db->query();
	}

	/**
	 * Creates a Report Pseudo Course
	 */
	function newCourse( $params = array() )
	{
		if(empty($params)) {
			return false;
		}
		//working out the parent of the course
		if($params['parent_pseudo'] != '') {
			$parent_id = $params['parent_pseudo'];			
		}
		elseif($params['parent_class'] != '') {
			$parent_id = $params['parent_class'];
		}
		elseif($params['parent_subject'] != '') {
			$parent_id = $params['parent_subject'];
		}
		else {
			$parent_id = ApotheosisLibDb::getRootItem( '#__apoth_cm_courses' );
		}
		//working out the twin course
		if($params['twin_pseudo'] != '') {
			$twin_id = $params['twin_pseudo'];			
		}
		elseif($params['twin_class'] != '') {
			$twin_id = $params['twin_class'];
		}
		elseif($params['twin_subject'] != '') {
			$twin_id = $params['twin_subject'];
		}
		else {
			return false;
		}
		// Get a database connector
		$db =& JFactory::getDBO();
		
		// Work out all the courses and their details
		$courses = ApotheosisLibDb::getDescendants( $twin_id, '#__apoth_cm_courses' );
		$parents = ApotheosisLibDb::getAncestors( $twin_id, '#__apoth_cm_courses' );
		$courses[$twin_id]->_parents = $parents[$twin_id]->_parents;
		
		foreach($courses as $k=>$v) {
			
			$pId = reset($courses[$k]->_parents);
			
			// for the first level we use given parent, all others use pseudo course parent
			if($pId == $parent_id) {
				$parent 		= $parent_id;
				$shortname 	= $db->Quote($params['shortname']);
				$fullname 	= $db->Quote($params['fullname']);
			}
			else {
				$parent = $courses[$pId]->pseudo;
				$shortname 	= $db->nameQuote('shortname');
				$fullname 	= $db->nameQuote('fullname');
			}
			$start_date = $db->Quote($params['start_date']);
			$end_date 	= $db->Quote($params['end_date']);
	
			// add pseudo course to the courses table
			$query = 'INSERT INTO `#__apoth_cm_courses`'
					."\n".' ('.$db->nameQuote('type').', '
					."\n".'  '.$db->nameQuote('shortname').', '
					."\n".'  '.$db->nameQuote('fullname').', '
					."\n".'  '.$db->nameQuote('parent').', '
					."\n".'  '.$db->nameQuote('start_date').', '
					."\n".'  '.$db->nameQuote('end_date').', '
					."\n".'  '.$db->nameQuote('reportable').', '
					."\n".'  '.$db->nameQuote('year').')'
					."\n".' SELECT'
					."\n".' '.$db->Quote('pseudo').','
					."\n".' '.$shortname.','
					."\n".' '.$fullname.','
					."\n".' '.$db->Quote($parent).','
					."\n".' '.$start_date.','
					."\n".' '.$end_date.','
					."\n".' '.$db->nameQuote('reportable').','
					."\n".' '.$db->nameQuote('year')
					."\n".' FROM `#__apoth_cm_courses`'
					."\n".' WHERE `id` = '.$db->Quote($k);
			$db->setQuery($query);
			$db->query();
			
			// pull out last installed ID
			$lastCourse = $db->insertid();
			
			// map the course to twin relationship in pseudo map
			$db->setQuery('INSERT INTO `#__apoth_cm_pseudo_map` (`course`, `twin`)'
							."\n".' VALUES ('.$db->Quote($lastCourse).', '.$db->Quote($k).')');
			$db->query();
			
			// make that the Pseudo id for that element in the courses array
			$courses[$k]->pseudo = $lastCourse;
		}
		
		ApotheosisLibDb::updateAncestry( '#__apoth_cm_courses' );
	}
	
	function getYearGroups()
	{
		// Build Year Group select list
		$db = &JFactory::getDBO();
		$query = 'SELECT SUBSTRING( shortname, 1, 2 ) AS year'
			."\n".'FROM `#__apoth_cm_courses`'
			."\n".'WHERE `type` = "pastoral"'
			."\n".'  AND `deleted` = "0"'
			."\n".'GROUP BY `year`'
			."\n".'ORDER BY `year`';
		$db->setQuery( $query );
		if (!$db->query())
		{
			$this->setRedirect( 'index.php?option=com_arc_report&view=cycles' );
			return JError::raiseWarning( 500, $db->getErrorMsg() );
		}
	
		$yearGroups = $db->loadObjectList();
		return $yearGroups;
	}
}
?>