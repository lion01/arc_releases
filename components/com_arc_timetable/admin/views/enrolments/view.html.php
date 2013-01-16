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

// Give us access to the joomla view class
jimport('joomla.application.component.view');

/**
 * Timetable Admin Enrolments View
 *
 * @author     Punnet - Arc Team
 * @package    Arc
 * @subpackage Timetable
 * @since      1.6
 */
class TimetableAdminViewEnrolments extends JView
{
	function __construct()
	{
		parent::__construct();
		JHTML::_( 'behavior.tooltip' );
		
		$this->addPath = JURI::base().'/components/com_arc_timetable/views/enrolments/tmpl/';
	}
	
	/**
	 * Default method
	 */
	function display()
	{
		// Document
		$document = &JFactory::getDocument();
		$document->setTitle( JText::_('Arc - Timetable Manager') );
		
		// Set toolbar items for the page
		JToolBarHelper::title( JText::_('Timetable Manager: Group Enrolments'), 'config.png' );
		JToolBarHelper::addNew();
		JToolBarHelper::editList();
		JToolBarHelper::deleteList( '', 'terminate', 'Terminate');
		
		$varMap = array( 'enrolments'=>'PagedEnrolments'
			, 'searchPerson'=>'SearchPerson'
			, 'searchGroup'=>'SearchGroup'
			, 'searchValid'=>'SearchValid'
			, 'pagination'=>'Pagination' );
		ApotheosisLib::setViewVars( $this, $varMap );
		
		// Display the template(s)
		parent::display();
	}
	
	/**
	 * Add an enrolment
	 */
	function add()
	{
		// Document
		$document = &JFactory::getDocument();
		$document->setTitle( JText::_('Arc - Timetable Manager') );
		
		// Set toolbar items for the page
		JToolBarHelper::title( JText::_('Timetable Manager: Add an enrolment'), 'config.png' );
		
		// Show the edit form
		$this->_showEditForm();
	}
	
	/**
	 *  Edit a course
	 */
	function edit()
	{
		// Document
		$document = &JFactory::getDocument();
		$document->setTitle( JText::_('Arc - Timetable Manager') );
		
		// Set toolbar items for the page
		JToolBarHelper::title( JText::_('Timetable Manager: Edit an enrolment'), 'config.png' );
		
		// Show the edit form
		$this->_showEditForm();
	}
	
	/**
	 * Show the edit form
	 */
	function _showEditForm()
	{
		// Set toolbar items for the page
		JToolBarHelper::save();
		JToolBarHelper::cancel();
		
		// Retrieve the course to edit
		$this->enrolment = &$this->get( 'Enrolment' );
		
		// Display the edit template
		parent::display( 'edit' );
	}
}
?>