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
class ReportsControllerMergeFields extends JController
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
		$this->registerTask( 'updateField',		'updateField' );
		$this->registerTask( 'newField',		'newField' );

	}

	function show()
	{
		$viewName = JRequest::getVar('view', 'mergefields');

		$model	= &$this->getModel( $viewName );
		$view	= &$this->getView( $viewName, 'html' );
		
		$view->setModel( $model, true );
		$view->display();
	}

	function updateField()
	{
		global $mainframe;
		$viewName = JRequest::getVar('view', 'mergefields');
		$model = &$this->getModel( $viewName );
		$view = &$this->getView( $viewName, 'html' );
		
		$params['id'] = JRequest::getVar('id');
		$params['word'] = JRequest::getVar('word');
		$params['male'] = JRequest::getVar('male');
		$params['female'] = JRequest::getVar('female');
		$params['neuter'] = JRequest::getVar('neuter');
		$params['property'] = JRequest::getVar('property');
		
		//Start setting the response message
		$message = 'Saving the merge field..........';
		
		$chk = $model->updateField($params);
		$view->setModel( $model, true );
		
		if($chk) {
			$message = 'Update Successful';
			$msgType = 'message';
		}
		else {
			$message = 'Update Failed....<br />';
			$msgType = 'error';
		}
		
		//Re-direct back to the admin screen
		$mainframe->redirect( 'index.php?option=com_arc_report&view='.$viewName, $message, $msgType );
	}
	
	function newField()
	{
		global $mainframe;
		$viewName = JRequest::getVar('view', 'mergefields');
		$model = &$this->getModel( $viewName );
		$view = &$this->getView( $viewName, 'html' );
		
		$params['word'] = JRequest::getVar('word');
		$params['male'] = JRequest::getVar('male');
		$params['female'] = JRequest::getVar('female');
		$params['neuter'] = JRequest::getVar('neuter');
		$params['property'] = JRequest::getVar('property');
		
		//Start setting the response message
		$message = 'Saving New Merge Field..........';
		
		$chk = $model->newField($params);
		if($chk) {
			$message = 'New Merge Field Successful';
			$msgType = 'message';
		}
		else {
			$message = 'New Merge Field Failed....<br />';
			$msgType = 'error';
		}
		$view->setModel( $model, true );
		
		//Re-direct back to the admin screen
		$mainframe->redirect( 'index.php?option=com_arc_report&view='.$viewName, $message, $msgType );
	}
	
	function edit()
	{
		$viewName = JRequest::getVar('view', 'mergefields');

		$model	= &$this->getModel( $viewName );
		$view	= &$this->getView( $viewName, 'html' );
				
		$view->setModel( $model, true );
		$view->edit();
	}
//*	
	function add()
	{
		$viewName = JRequest::getVar('view', 'mergefields');

		$model	= &$this->getModel( $viewName );
		$view	= &$this->getView( $viewName, 'html' );
				
		$view->setModel( $model, true );
		$view->newField();
	}
// */
	function cancel()
	{
		$this->setRedirect( 'index.php?option=com_arc_report&view=mergefields' );

		$this->redirect();
	}

	function remove()
	{
		$this->setRedirect( 'index.php?option=com_arc_report&view=mergefields' );

		// Initialize variables
		$db		=& JFactory::getDBO();
		$eid	= JRequest::getVar( 'eid', array(), 'post', 'array' );

		$n		= count( $eid );
		JArrayHelper::toInteger( $eid );

		if ($n)
		{
			$query = 'DELETE FROM `#__apoth_rpt_merge_words`'
			. ' WHERE `id` = ' . implode( ' OR `id` = ', $eid )
			;
			$db->setQuery( $query );
			if (!$db->query()) {
				JError::raiseWarning( 500, $row->getError() );
			}
		}

		$this->setMessage( JText::sprintf( 'Marge Fields removed', $n ) );
	}

}
