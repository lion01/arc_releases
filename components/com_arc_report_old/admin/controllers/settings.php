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
require_once (JPATH_COMPONENT_ADMINISTRATOR.DS.'helpers'.DS.'settings.php' );

/**
 * @package		Joomla
 * @subpackage	Reports
 */
class ReportsControllerSettings extends JController
{
	/**
	 * Constructor
	 */
	function __construct( $config = array() )
	{
		parent::__construct( $config );

	}

	function show()
	{
		$viewName = JRequest::getVar('view', 'settings');
		
		$model	= &$this->getModel( $viewName );
		$view	= &$this->getView( $viewName, 'html' );
		
		$view->setModel( $model, true );
		$view->display();
	}

	function save()
	{
		$redirect = 'save'.ucfirst( JRequest::getVar( 'view' ) );		
		$this->$redirect();
	}
	
	function saveSettings()
	{
		global $mainframe;
		$viewName = JRequest::getVar('view', 'settings');
		$model = &$this->getModel( $viewName );
		$view = &$this->getView( $viewName, 'html' );
		
		$params = JRequest::getVar('params');

		//$model->saveParams($text);
		$view->setModel( $model, true );
		
				//Start setting the response message
		$message = 'Saving the Settings';
		
		$model->saveParams($params);
		$view->setModel( $model, true );
		
		//Re-direct back to the admin screen
		$mainframe->redirect( 'index.php?option=com_arc_report&view='.$viewName, $message );
	}
}