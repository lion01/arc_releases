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

// Give us access to the joomla model class
jimport( 'joomla.application.component.model' );

/**
 * Course Admin Courses Model
 *
 * @author     Punnet - Arc Team
 * @package    Arc
 * @subpackage Course
 * @since      1.6
 */
class CourseAdminModelCourses extends JModel
{
	/**
	 * Set the search term
	 * 
	 * @param string $searchTerm  The search term to set
	 */
	function setSearchTerm( $searchTerm )
	{
		$this->_searchTerm = JString::strtolower( $searchTerm );
	}
	
	/**
	 * Retrieve the search term
	 * 
	 * @return string $this->_searchTerm  The search term
	 */
	function getSearchTerm()
	{
		return $this->_searchTerm;
	}
	
	/**
	 * Set the type term
	 */
	function setTypeTerm( $typeTerm )
	{
		$this->_typeTerm = JString::strtolower( $typeTerm );
	}
	
	/**
	 * Retrieve the type term
	 * 
	 * @return string $this->_typeTerm  The type term
	 */
	function getTypeTerm()
	{
		return $this->_typeTerm;
	}
	
	/**
	 * Set the currently valid pagination object
	 * 
	 * @param int $limitStart Where to start paging from
	 * @param int $limit  The total number of items to page
	 */
	function setPagination( $limitStart, $limit )
	{
		$total = $this->_loadPagedCourses( true );
		$this->_pagination = new JPagination( $total, $limitStart, $limit );
	}
	
	/**
	 * Retrieve the currently valid pagination object
	 * 
	 * @return object $this->_pagination  The pagination object
	 */
	function &getPagination()
	{
		return $this->_pagination;
	}
	
	/**
	 * Set a paginated array of course objects
	 */
	function setPagedCourses()
	{
		$courseInfo = $this->_loadPagedCourses();
		foreach( $courseInfo as $courseData ) {
			$this->_pagedCourses[] = new AdminCourse( $courseData );
		}
	}
	
	/**
	 * Fetch a paginated list of courses
	 * 
	 * @return array $this->_pagedCourses  Array of course objects
	 */
	function &getPagedCourses()
	{
		return $this->_pagedCourses;
	}
	
	/**
	 * Retrieve courses or a count of courses from the db
	 * 
	 * @param bool $numOnly  Whether we only want a count of courses, defaults to false
	 * @return int|array $result  The count of courses or array of courses info
	 */
	function _loadPagedCourses( $numOnly = false )
	{
		$db = &JFactory::getDBO();
		$searchTerm = $this->_searchTerm;
		$typeTerm = $this->_typeTerm;
		
		// create the select
		$select = ( $numOnly ? 'SELECT COUNT(*)' : 'SELECT *' );
		
		// create the search term where clause
		if( $searchTerm != '' ) {
			$searchEscaped = $db->Quote( '%'.$db->getEscaped( $searchTerm, true ).'%', false );
			$where[] = $db->nameQuote('id').' LIKE '.$searchEscaped
				.' OR '.$db->nameQuote('fullname').' LIKE '.$searchEscaped
				.' OR '.$db->nameQuote('shortname').' LIKE '.$searchEscaped;
		}
		
		// create the type term where clause
		if( $typeTerm != '' ) {
			$where[] = $db->nameQuote('type').' = '.$db->Quote($typeTerm);
		}
		
		// only select courses not marked as deleted
		$where[] = $db->nameQuote('deleted').' = '.$db->Quote('0');
		
		// create the combined where clause
		$where = "\n".'WHERE (' . implode( ') AND (', $where ) . ')';
		
		// create the order clause
		$order = "\n".'ORDER BY '.$db->nameQuote('id');
		
		// create the query
		$query = $select
			."\n".'FROM '.$db->nameQuote('#__apoth_cm_courses')
			.$where;
		
		// get the results
		if( $numOnly ) {
			$db->setQuery( $query );
			$result = $db->loadResult();
		}
		else {
			$pagination = $this->_pagination;
			$db->setQuery( $query.$order, $pagination->limitstart, $pagination->limit );
			$result = $db->loadAssocList();
		}
		
		return $result;
	}
	
	/**
	 * Set a course object
	 * 
	 * @param int|array $courseInfo  Optional current pagination index of the course we want or array of data
	 */
	function setCourse( $courseInfo = null )
	{
		if( is_array($courseInfo) ) {
			$this->_course = new AdminCourse( $courseInfo );
		}
		elseif( !is_null($courseInfo) ) {
			$this->_course = $this->_pagedCourses[$courseInfo];
		}
		else {
			$this->_course = new AdminCourse();
		}
	}
	
	/**
	 * Retrieve the current course object
	 * 
	 * @return obj $this->_course  The current course object
	 */
	function &getCourse()
	{
		return $this->_course;
	}
	
	/**
	 * Set some course objects
	 * 
	 * @param array $courseInfo  Current pagination indices of the courses we want
	 */
	function setCourses( $courseIndices )
	{
		if( is_array($courseIndices) ) {
			foreach( $courseIndices as $courseIndex ) {
				$this->_courses[] = $this->_pagedCourses[$courseIndex];
			}
		}
	}
	
	/**
	 * Retrieve the current course objects array
	 * 
	 * @return array $this->_courses  The array of current course objects
	 */
	function &getCourses()
	{
		return $this->_courses;
	}
	
	/**
	 * Set an array of deletable course objects
	 * 
	 * @return int  The total number of deletable courses
	 */
	function setDelCourses()
	{
		$delCourseInfo = $this->_loadDelCourses();
		foreach( $delCourseInfo as $delCourseData ) {
			$this->_delCourses[] = new AdminCourse( $delCourseData );
		}
		
		return count( $this->_delCourses );
	}
	
	/**
	 * Fetch an array list of deletable course objects
	 * 
	 * @return array $this->_delCourses  Array of deletable course objects
	 */
	function &getDelCourses()
	{
		return $this->_delCourses;
	}
	
	/**
	 * Retrieve deletable courses or a count of deletable courses from the db
	 * 
	 * @return int|array $result  The count of deletable courses or array of courses info
	 */
	function _loadDelCourses()
	{
		// Find all the descendants of the current courses
		$descs = array();
		foreach( $this->_courses as $courseObj ) {
			$tmpDescs = array_keys( ApotheosisLibDb::getDescendants($courseObj->getData('id'), '#__apoth_cm_courses') );
			$descs = array_merge( $descs, $tmpDescs );
		}
		$descs = array_unique( $descs );
		
		$db = JFactory::getDBO();
		foreach( $descs as $k=>$courseId ) {
			$quotedDescs[] = $db->Quote($courseId);
		}
		
		$query = 'SELECT *'
			."\n".'FROM '.$db->nameQuote('#__apoth_cm_courses')
			."\n".'WHERE '.$db->nameQuote('id').' IN ('.implode( ', ', $quotedDescs ).')'
			."\n".'  AND'.$db->nameQuote('deleted').' = '.$db->Quote('0');
			
		$db->setQuery( $query );
		$result = $db->loadAssocList();
		
		return $result;
	}
	
	/**
	 * Save the course data
	 * 
	 * @param array $courseData  Course data to save
	 * @return boolean $commit Whether or not the object was successfully committed
	 */
	function save( $courseData )
	{
		// Create course object with passed in data and commit it
		$courseObj = new AdminCourse( $courseData );
		$commit = $courseObj->commit();
		
		// If the save was successful and it was a new course then update ancestry
		if( $commit && ($courseData['id'] == '') ) {
			ApotheosisLibDb::updateAncestry( '#__apoth_cm_courses' );
		}
		
		return $commit;
	}
	
	/**
	 * Mark courses as deleted in the database
	 * 
	 * @param array $courseIds  Array of course IDs for deletion
	 * @return boolean $deleted  The number of courses successfully deleted
	 */
	function delete( $courseIds )
	{
		$db = &JFactory::getDBO();
		
		foreach( $courseIds as $k=>$courseId ) {
			$courseIds[$k] = $db->Quote( $courseId );
		}
		$quotedIds = implode( ', ', $courseIds );
		
		// Create course objects from list of IDs 
		$query = 'SELECT *'
			."\n".'FROM '.$db->nameQuote('#__apoth_cm_courses')
			."\n".'WHERE '.$db->nameQuote('id').' IN ('.$quotedIds.')';
		$db->setQuery( $query );
		$courseInfo = $db->loadAssocList();
		
		// Loop through course objects and delete each one
		$deleted = 0;
		foreach( $courseInfo as $courseData ) {
			$delCourse = new AdminCourse( $courseData );
			if( $delCourse->delete() ) {
				$deleted++;
			}
		}
		
		return $deleted;
	}
	
	/**
	 * Update the courses ancestry table
	 * 
	 * @return mixed  Did the query run successfully: null = yes, false = no
	 */
	function updateAnc()
	{
		return ApotheosisLibDb::updateAncestry( '#__apoth_cm_courses' );
	}
}
?>