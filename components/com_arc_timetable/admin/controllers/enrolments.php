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
 * Timetable Admin Year Controller
 *
 * @author     Punnet - Arc Team
 * @package    Arc
 * @subpackage Timetable
 * @since      1.6
 */
class TimetableAdminControllerEnrolments extends TimetableAdminController
{
	function __construct()
	{
		parent::__construct();
		$this->registerTask( 'add', 'display' );
		$this->registerTask( 'edit', 'display' );
		$this->registerTask( 'cancel', 'display' );
	}
	
	/**
	 * Default method
	 */
	function display()
	{
		global $mainframe, $option;
		jimport( 'joomla.html.pagination' );
		
		$model = &$this->getModel( 'enrolments' );
		$view = &$this->getView( 'enrolments', 'html' );
		$view->setModel( $model, true );
		
		$model->setSearchGroup(  $mainframe->getUserStateFromRequest( $option.'.search_group',   'search_group',   '', 'string' ) );
		$model->setSearchPerson( $mainframe->getUserStateFromRequest( $option.'.search_person',  'search_person',  '', 'string' ) );
		$model->setSearchValid(  $mainframe->getUserStateFromRequest( $option.'.search_valid',   'search_valid',   '', 'string' ) );
		
		$limitStart = $mainframe->getUserStateFromRequest( $option.'.limitstart', 'limitstart', 0, 'int' );
		$limit = $mainframe->getUserStateFromRequest( 'global.list.limit', 'limit', $mainframe->getCfg('list_limit'), 'int' );
		$model->setPagination( $limitStart, $limit );
		
		switch( JRequest::getVar('task') ) {
		// Add a new course
		case( 'add' ):
			$model->setEnrolment();
			$view->add();
			break;
		
		// Edit a course
		case( 'edit' ):
			$id = $model->indexToEnrolmentId( reset( array_keys(JRequest::getVar('eid')) ) );
			$model->setEnrolment( $id );
			$view->edit();
			break;
		
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
		global $mainframe, $option;
		
		$model = &$this->getModel( 'enrolments' );
		$model->setEnrolment( JRequest::getVar( 'id' ) );
		
		// Retrieve data for new course 
		$data = array();
		$data['id']              = JRequest::getVar( 'id' ); 
		$data['group_id']        = reset( unserialize( JRequest::getVar( 'group_id', 'a:0{}' ) ) );
		$data['person_id']       = JRequest::getVar( 'person_id' );
		$data['role']            = JRequest::getVar( 'role' );
		$data['valid_from']      = JRequest::getVar( 'valid_from' );
		$data['valid_to']        = JRequest::getVar( 'valid_to' );
		if( empty( $data['valid_to'] ) ) {
			$data['valid_to'] = null;
		}
		
		// Save the course data
		$save = $model->save( $data );
		
		if( $save ) {
			$mainframe->enqueueMessage( 'Enrolment was successfully saved.' );
			$this->display();
		}
		else {
			$mainframe->enqueueMessage( 'There was a problem saving the enrolment, please try again.', 'error' );
			$view = &$this->getView( 'enrolments', 'html' );
			$view->setModel( $model, true );
			$view->edit();
		}
	}
	
	
	/**
	 * Mark the enrolment as finished
	 */
	function terminate()
	{
		global $mainframe, $option;
		jimport( 'joomla.html.pagination' );
		
		$model = &$this->getModel( 'enrolments' );
		
		$model->setSearchGroup(  $mainframe->getUserStateFromRequest( $option.'.search_group',   'search_group',   '', 'string' ) );
		$model->setSearchPerson( $mainframe->getUserStateFromRequest( $option.'.search_person',  'search_person',  '', 'string' ) );
		$model->setSearchValid(  $mainframe->getUserStateFromRequest( $option.'.search_valid',   'search_valid',   '', 'string' ) );
		
		$limitStart = $mainframe->getUserStateFromRequest( $option.'.limitstart', 'limitstart', 0, 'int' );
		$limit = $mainframe->getUserStateFromRequest( 'global.list.limit', 'limit', $mainframe->getCfg('list_limit'), 'int' );
		$model->setPagination( $limitStart, $limit );
		
		// Retrieve the enrolments for termination
		$enrolIndices = array_keys( JRequest::getVar('eid') );
		
		// Mark enrolments as terminated
		$r = $model->terminate( $enrolIndices );
		
		if( $r ) {
			$message = ( count($enrolIndices) > 1 ) ? 'The enrolments were successfully terminated.' : 'The enrolment was successfully terminated.';
			$mainframe->enqueueMessage( $message );
		}
		else {
			$message = ( count($enrolIndices) > 1 ) ? 'There was a problem terminating the enrolments, please try again.' : 'There was a problem terminating the enrolment, please try again.';
			$mainframe->enqueueMessage( $message, 'error' );
		}
		
		$this->display();
	}}
?>