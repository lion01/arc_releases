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

jimport( 'joomla.application.component.controller' );

/**
 * @package		Joomla
 * @subpackage	Banners
 */
class ReportsControllerPseudo extends JController
{
	/**
	 * Constructor
	 */
	function __construct( $config = array() )
	{
		parent::__construct( $config );
		// Register Extra tasks
		$this->registerTask( 'edit',		'edit' );
		$this->registerTask( 'remove',		'remove' );
		$this->registerTask( 'updateCourse',		'updateCourse' );
		$this->registerTask( 'newCourse',		'newCourse' );
	}

	function show()
	{
		$viewName = JRequest::getVar('view', 'pseudo');

		$model	= &$this->getModel( $viewName );
		$view	= &$this->getView( $viewName, 'html' );
		
		$view->setModel( $model, true );
		$view->display();
	}

	function updateCourse()
	{
		global $mainframe;
		$viewName = JRequest::getVar('view', 'pseudo');
		$model = &$this->getModel( $viewName );
		$view = &$this->getView( $viewName, 'html' );
		
		$params['id'] = JRequest::getVar('id');
		$params['fullname'] = JRequest::getVar('fullname');
		$params['shortname'] = JRequest::getVar('shortname');
		$params['cycle_id'] = JRequest::getVar('cycle_id');
		$params['enrolment_subject'] = JRequest::getVar('enrolment_subject');
		$params['enrolment_course'] = JRequest::getVar('enrolment_course');
		$params['enrolment_class'] = JRequest::getVar('enrolment_class');
		
		//Start setting the response message
		$message = 'Saving the Pseudo Course..........';
		
		$model->updateCourse($params);
		$view->setModel( $model, true );
		
		//Re-direct back to the admin screen
		$mainframe->redirect( 'index.php?option=com_arc_report&view='.$viewName, $message );
	}
	
	function newCourse()
	{
		global $mainframe;
		$viewName = JRequest::getVar('view', 'pseudo');
		
		$model = 	&$this->getModel( $viewName );
		$view = 	&$this->getView( $viewName, 'html' );
		
		$params['fullname'] = 			JRequest::getVar('fullname');
		$params['shortname'] = 			JRequest::getVar('shortname');
		$params['start_date'] = 		JRequest::getVar('start_date');
		$params['end_date'] = 			JRequest::getVar('end_date');
		$params['reportable'] =			JRequest::getVar('reportable');
		$params['year'] = 					JRequest::getVar('year');
		$params['parent_subject'] = JRequest::getVar('parent_subject');
		$params['parent_class'] = 	JRequest::getVar('parent_class');
		$params['parent_pseudo'] = 	JRequest::getVar('parent_pseudo');
		$params['twin_subject'] = 	JRequest::getVar('twin_subject');
		$params['twin_class'] = 		JRequest::getVar('twin_class');
		$params['twin_pseudo'] = 		JRequest::getVar('twin_pseudo');
		
		//Start setting the response message
		$message = 'Saving New Pseudo Course..........';
		
		ob_start();
		$model->newCourse($params);
		$view->setModel( $model, true );
		
		$message .= ob_get_clean();
		//Re-direct back to the admin screen
		$mainframe->redirect( 'index.php?option=com_arc_report&view='.$viewName, $message );
	}
	
	function edit()
	{
		$viewName = JRequest::getVar('view', 'pseudo');

		$model	= &$this->getModel( $viewName );
		$view	= &$this->getView( $viewName, 'html' );
				
		$view->setModel( $model, true );
		$view->edit();
	}
//*	
	function add()
	{
		$viewName = JRequest::getVar('view', 'pseudo');

		$model	= &$this->getModel( $viewName );
		$view	= &$this->getView( $viewName, 'html' );
				
		$view->setModel( $model, true );
		$view->newCourse();
	}
// */
	function cancel()
	{
		$this->setRedirect( 'index.php?option=com_arc_report&view=pseudo' );

		$this->redirect();
	}

	function remove()
	{
		$this->setRedirect( 'index.php?option=com_arc_report&view=pseudo' );

		// Initialize variables
		$db		=	&JFactory::getDBO();
		$eid	= JRequest::getVar( 'eid', array(), 'post', 'array' );

		$n		= count( $eid );
		JArrayHelper::toInteger( $eid );

		if ($n)
		{
			$query = 'DELETE FROM `#__apoth_rpt_pseudo`'
			. ' WHERE `id` = ' . implode( ' OR `id` = ', $eid )
			;
			$db->setQuery( $query );
			if (!$db->query()) {
				JError::raiseWarning( 500, $row->getError() );
			}
		}

		$this->setMessage( JText::sprintf( 'Courses removed', $n ) );
	}

}
