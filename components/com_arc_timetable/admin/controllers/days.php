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
 * Timetable Admin Days Controller
 *
 * @author     Punnet - Arc Team
 * @package    Arc
 * @subpackage Timetable
 * @since      1.6
 */
class TimetableAdminControllerDays extends TimetableAdminController
{
	function __construct( $config = array() )
	{
		parent::__construct( $config );
		$this->registerTask( 'toggleStatutory', 'displaySections' );
	}
	
	/**
	 * Default method
	 */
	function display()
	{
		global $mainframe, $option;
		jimport( 'joomla.html.pagination' );
		
		$model = &$this->getModel( 'days' );
		$view = &$this->getView( 'days', 'html' );
		$view->setModel( $model, true );
		
		$limitStart = $mainframe->getUserStateFromRequest( $option.'.limitstart', 'limitstart', 0, 'int' );
		$limit = $mainframe->getUserStateFromRequest( 'global.list.limit', 'limit', $mainframe->getCfg('list_limit'), 'int' );
		$model->setPaginationForDays( $limitStart, $limit );
		
		$view->display();
	}
	
	/**
	 * Display day sections
	 */
	function displaySections()
	{
		global $mainframe, $option;
		jimport( 'joomla.html.pagination' );
		
		$model = &$this->getModel( 'days' );
		$view = &$this->getView( 'days', 'html' );
		$view->setModel( $model, true );
		
		$limitStart = $mainframe->getUserStateFromRequest( $option.'.limitstart', 'limitstart', 0, 'int' );
		$limit = $mainframe->getUserStateFromRequest( 'global.list.limit', 'limit', $mainframe->getCfg('list_limit'), 'int' );
		$model->setPaginationForDays( $limitStart, $limit );
		
		$model->setDays( JRequest::getVar( 'day' ) );
		
		$model->setPaginationForSections( $limitStart, $limit );
		
		switch( JRequest::getVar( 'task' ) ) {
		case( 'toggleStatutory' ):
			$index = array_keys( JRequest::getVar( 'eid' ) );
			$model->setSections( $index );
			$model->toggleSection();
			
			$view->displaySections();
			break;
		
		default:
			$view->displaySections();
			break;
		}
		
	}
}
?>