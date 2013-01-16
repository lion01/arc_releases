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
require_once( JPATH_COMPONENT_ADMINISTRATOR.DS.'helpers'.DS.'statements.php' );

/**
 * @package		Joomla
 * @subpackage	Reports
 */
class ReportsControllerStatements extends JController
{
	/**
	 * Constructor
	 */
	function __construct( $config = array() )
	{
		parent::__construct( $config );
	}
	
	/**
	 * Display an admin form
	 */
	function show()
	{
		$viewName = JRequest::getVar( 'view', 'statements' );
		
		$model = &$this->getModel( $viewName );
		$view  = &$this->getView( $viewName, 'html' );
		
		$view->setModel( $model, true );
		$view->display();
	}
	
	/**
	 * Copy statements between selected report cycles
	 */
	function duplicate()
	{
		$model = &$this->getModel( 'statements' );
		
		$subjects = JRequest::getVar( 'subjects', '' );
		$sourceCycle = JRequest::getVar( 'sourceCycle', '' );
		$targetCycle = JRequest::getVar( 'targetCycle', '' );
		
		$n = $model->copyStatements( $subjects, $sourceCycle, $targetCycle );
		
		$this->setRedirect( 'index.php?option=com_arc_report&view=statements',
			JText::sprintf( '%1$s Statements Copied', $n ),
			( ($n == 0) ? 'notice' : 'message' ) );
	}
}
?>