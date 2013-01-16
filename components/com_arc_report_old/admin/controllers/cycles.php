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
class ReportsControllerCycles extends JController
{
	/**
	 * Constructor
	 */
	function __construct( $config = array() )
	{
		parent::__construct( $config );
		// Register Extra tasks
		$this->registerTask( 'edit',       'edit' );
		$this->registerTask( 'remove',     'remove' );
		$this->registerTask( 'updateCycle','updateCycle' );
		$this->registerTask( 'newCycle',   'newCycle' );
	}
	
	function show()
	{
		$viewName = JRequest::getVar('view', 'cycles');
		
		$model	= &$this->getModel( $viewName );
		$view	= &$this->getView( $viewName, 'html' );
		
		$view->setModel( $model, true );
		$view->display();
	}
	
	function updateCycle()
	{
		global $mainframe;
		$viewName = JRequest::getVar('view', 'cycles');
		$model = &$this->getModel( $viewName );
		$view = &$this->getView( $viewName, 'html' );
		
		$params['id'] = JRequest::getVar('id');
		$params['valid_from'] = JRequest::getVar('valid_from').' 00:00:01';;
		$params['valid_to'] = JRequest::getVar('valid_to').' 23:59:59';;
		$params['year'] = JRequest::getVar('year');
		$params['allow_multiple'] = JRequest::getVar('allow_multiple');
		$params['recheck'] = JRequest::getVar('recheck');
		
		//Start setting the response message
		$message = 'Saving the Report Cycle..........';
		
		$model->updateCycle($params);
		$view->setModel( $model, true );
		
		//Re-direct back to the admin screen
		$mainframe->redirect( 'index.php?option=com_arc_report&view='.$viewName, $message );
	}
	
	function newCycle()
	{
		global $mainframe;
		$viewName = JRequest::getVar('view', 'cycles');
		$model = &$this->getModel( $viewName );
		$view = &$this->getView( $viewName, 'html' );
		
		$params['valid_from'] = JRequest::getVar('valid_from').' 00:00:01';;
		$params['valid_to'] = JRequest::getVar('valid_to').' 23:59:59';;
		$params['year'] = JRequest::getVar('year');
		$params['allow_multiple'] = JRequest::getVar('allow_multiple');
		$params['recheck'] = JRequest::getVar('recheck');
		
		//Start setting the response message
		$message = 'Saving New Report Cycle..........';
		
		$model->newCycle($params);
		$view->setModel( $model, true );
		
		//Re-direct back to the admin screen
		$mainframe->redirect( 'index.php?option=com_arc_report&view='.$viewName, $message );
	}
	
	function edit()
	{
		$viewName = JRequest::getVar('view', 'cycles');
		
		$model	= &$this->getModel( $viewName );
		$view	= &$this->getView( $viewName, 'html' );
		
		$view->setModel( $model, true );
		$view->edit();
	}
	
	function add()
	{
		$viewName = JRequest::getVar('view', 'cycles');
		
		$model	= &$this->getModel( $viewName );
		$view	= &$this->getView( $viewName, 'html' );
		
		$view->setModel( $model, true );
		$view->newCycle();
	}
	
	function cancel()
	{
		$this->setRedirect( 'index.php?option=com_arc_report&view=cycles' );
		$this->redirect();
	}
	
	function remove()
	{
		// Initialize variables
		$eid = JRequest::getVar( 'eid', array(), 'post', 'array' );
		
		$model = &$this->getModel('cycles');
		
		if( !$model->removeCycle( $eid ) ) {
			JError::raiseWarning( 500, $row->getError() );
		}
		
		$this->setRedirect( 'index.php?option=com_arc_report&view=cycles', JText::sprintf( '%1$s cycles removed', count( $eid ) ) );
	}
}
?>