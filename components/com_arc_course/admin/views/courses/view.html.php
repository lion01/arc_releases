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

// Give us access to the joomla view class
jimport('joomla.application.component.view');

/**
 * Course Admin Courses View
 *
 * @author     Punnet - Arc Team
 * @package    Arc
 * @subpackage Course
 * @since      1.6
 */
class CourseAdminViewCourses extends JView
{
	/**
	 * Creates a new course manager courses view
	 */
	function __construct()
	{
		parent::__construct();
		JHTML::_( 'behavior.tooltip' );
		
		// Path to tmpl to aid inclusion of CSS and JS
		$this->addPath = JURI::base().'components'.DS.'com_arc_course'.DS.'views'.DS.'courses'.DS.'tmpl'.DS;
	}
	
	/**
	 * Default method
	 */
	function display()
	{
		// Document
		$document = &JFactory::getDocument();
		$document->setTitle( JText::_('Arc - Course Manager') );
		
		// Add missing style declaration for the refresh image
		$refreshButtonStyle = '.icon-32-refresh { background-image: url(templates'.DS.'khepri'.DS.'images'.DS.'toolbar'.DS.'icon-32-refresh.png); }';
		$document->addStyleDeclaration( $refreshButtonStyle );
		
		// Set toolbar items for the page
		JToolBarHelper::title( JText::_('Course Manager: Courses'), 'config.png' );
		JToolBarHelper::addNew();
		JToolBarHelper::editList();
		JToolBarHelper::custom( 'update_anc', 'refresh', 'refresh', 'Update Ancestry', false );
		JToolBarHelper::deleteList();
		
		// Retrieve data for display in templates
		$this->search = $this->get( 'SearchTerm' );
		$this->type = $this->get( 'TypeTerm' );
		$this->courses = &$this->get( 'PagedCourses' );
		$this->courseCount = count( $this->courses );
		$this->pagination = &$this->get( 'Pagination' );
		
		// Display the template(s)
		parent::display();
	}
	
	/**
	 * Add a course
	 */
	function add()
	{
		// Document
		$document = &JFactory::getDocument();
		$document->setTitle( JText::_('Arc - Course Manager') );
		
		// Set toolbar items for the page
		JToolBarHelper::title( JText::_('Course Manager: Add a course'), 'config.png' );
		
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
		$document->setTitle( JText::_('Arc - Course Manager') );
		
		// Set toolbar items for the page
		JToolBarHelper::title( JText::_('Course Manager: Edit a course'), 'config.png' );
		
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
		$this->course = &$this->get( 'Course' );
		
		// Display the edit template
		parent::display( 'edit' );
	}
	
	/**
	 * Remove a course or courses
	 */
	function remove()
	{
		// Document
		$document = &JFactory::getDocument();
		$document->setTitle( JText::_( 'Arc - Course Manager' ) );
	
		// Set toolbar items for the page
		JToolBarHelper::title( JText::_( 'Course Manager: Delete Course(s)' ), 'config.png' );
		JToolBarHelper::custom( 'delete', 'delete', 'delete', 'Confirm Delete', false );
		JToolBarHelper::cancel();
		
		// Retrieve data for display in templates
		$this->courses = &$this->get( 'DelCourses' );
		$this->pagination = &$this->get( 'DelPagination' );
		
		// Display the delete template
		parent::display( 'delete' );
	}
}
?>
