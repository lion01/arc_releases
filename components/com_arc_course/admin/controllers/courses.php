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

/**
 * Course Admin Courses Controller
 *
 * @author     Punnet - Arc Team
 * @package    Arc
 * @subpackage Course
 * @since      1.6
 */
class CourseAdminControllerCourses extends CourseAdminController
{
	function __construct()
	{
		parent::__construct();
		$this->registerTask( 'add', 'display' );
		$this->registerTask( 'edit', 'display' );
		$this->registerTask( 'remove', 'display' );
		$this->registerTask( 'cancel', 'display' );
	}
	
	/**
	 * Default method
	 */
	function display()
	{
		global $mainframe, $option;
		jimport('joomla.html.pagination');
		$model = &$this->getModel( 'courses' );
		$view = &$this->getView( 'courses', 'html' );
		$view->setModel( $model, true );
		
		$searchTerm = $mainframe->getUserStateFromRequest( $option.'.search', 'search', '', 'string' );
		$model->setSearchTerm( $searchTerm );
		
		$typeTerm = $mainframe->getUserStateFromRequest( $option.'.filter_type', 'filter_type', '', 'string' );
		$model->setTypeTerm( $typeTerm );
		
		$limitStart = $mainframe->getUserStateFromRequest( $option.'.limitstart', 'limitstart', 0, 'int' );
		$limit = $mainframe->getUserStateFromRequest( 'global.list.limit', 'limit', $mainframe->getCfg('list_limit'), 'int' );
		$model->setPagination( $limitStart, $limit );
		
		$model->setPagedCourses();
		
		switch( JRequest::getVar('task') ) {
		// Add a new course
		case( 'add' ):
			$model->setCourse();
			$view->add();
			break;
		
		// Edit a course
		case( 'edit' ):
			$courseIndex = reset( array_keys(JRequest::getVar('eid')) );
			$model->setCourse( $courseIndex );
			$view->edit();
			break;
		
		// Update courses ancestry
		case( 'update_anc' ):
			$updateAncMsg = $model->updateAnc();
			
			if( $updateAncMsg !== false ) {
				$message = 'Courses ancestry successfully updated.';
				$msgType = 'message';
			}
			else {
				$message = 'There was an error updating the courses ancestry.';
				$msgType = 'error';
			}
			$mainframe->enqueueMessage( $message, $msgType );
			
			$view->display();
			break;
		
		// Remove a course or courses
		case( 'remove' ):
			$courseIndices = array_keys( JRequest::getVar('eid') );
			$model->setCourses( $courseIndices );
			$courseCount = $model->setDelCourses();
			
			if( $courseCount > 1 ) {
				$message = 'Are you sure you want to delete these courses?';
			}
			else {
				$message = 'Are you sure you want to delete this course?';
			}
			$mainframe->enqueueMessage( $message, 'notice' );
			
			$view->remove();
			break;
		
		// Show a paginated list of all the courses
		default:
			$view->display();
			break;
		}
	}
	
	/**
	 * Save changes to a course
	 */
	function save()
	{
		global $mainframe;
		$model = &$this->getModel( 'courses' );
		$view = &$this->getView( 'courses', 'html' );
		$view->setModel( $model, true );
		
		// Retrieve data for new course 
		$data = array();
		$data['id']              = JRequest::getVar( 'id' ); 
		$data['type']            = JRequest::getVar( 'type' );
		$data['ext_course_id']   = JRequest::getVar( 'ext_course_id' );
		$data['ext_type']        = JRequest::getVar( 'ext_type' );
		$data['ext_course_id_2'] = JRequest::getVar( 'ext_course_id_2' );
		$data['shortname']       = JRequest::getVar( 'shortname' );
		$data['fullname']        = JRequest::getVar( 'fullname' );
		$data['description']     = JRequest::getVar( 'description' );
		$data['parent']          = reset(unserialize(JRequest::getVar('parent')) );
		$data['sortorder']       = JRequest::getVar( 'sortorder' );
		$data['start_date']      = JRequest::getVar( 'start_date' );
		$data['end_date']        = JRequest::getVar( 'end_date' );
		$data['reportable']      = ( JRequest::getVar('reportable') ? '1' : '' );
		$data['year']            = JRequest::getVar( 'year' );
		
		// Save the course data
		$save = $model->save( $data );
		
		if( $save ) {
			$mainframe->enqueueMessage( 'Course was successfully saved.' );
			$this->display();
		}
		else {
			$mainframe->enqueueMessage( 'There was a problem saving the course, please try again.', 'error' );
			$model->setCourse( $data );
			$view->edit();
		}
	}
	
	/**
	 * Mark the courses as deleted
	 */
	function delete()
	{
		global $mainframe;
		$model = &$this->getModel( 'courses' );
		
		// Retrieve the courses for deletion
		$courseIds = unserialize( JRequest::getVar('course_ids') );
		
		// Mark courses as deleted
		$delete = $model->delete( $courseIds );
		
		if( $delete ) {
			$message = ( count($courseIds) > 1 ) ? 'The courses were successfully deleted.' : 'The course was successfully deleted.';
			$mainframe->enqueueMessage( $message );
		}
		else {
			$message = ( count($courseIds) > 1 ) ? 'There was a problem deleting the courses, please try again.' : 'There was a problem deleting the course, please try again.';
			$mainframe->enqueueMessage( $message, 'error' );
		}
		
		$this->display();
	}
}
?>