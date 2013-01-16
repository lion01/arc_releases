<?php
/**
 * @package     Arc
 * @subpackage  API
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
 * API Admin Consumers View
 *
 * @author     Punnet - Arc Team
 * @package    Arc
 * @subpackage Course
 * @since      1.6
 */
class ApiAdminViewConsumers extends JView
{
	/**
	 * Creates a new course manager courses view
	 */
	function __construct()
	{
		parent::__construct();
		
		// Path to tmpl to aid inclusion of CSS and JS
		$this->addPath = JURI::base().'components'.DS.'com_arc_consumers'.DS.'views'.DS.'consumers'.DS.'tmpl'.DS;
	}
	
	/**
	 * Default method
	 */
	function display()
	{
		// Document
		$document = &JFactory::getDocument();
		$document->setTitle( JText::_('Arc - API') );
		
		// Set toolbar items for the page
		JToolBarHelper::title( JText::_('API: Consumers'), 'config.png' );
		JToolBarHelper::addNew();
		JToolBarHelper::editList();
		JToolBarHelper::deleteList();
		
		// Retrieve data for display in templates
		$this->search = $this->get( 'SearchTerm' );
		$this->consumers = &$this->get( 'PagedConsumers' );
		$this->pagination = &$this->get( 'Pagination' );
		
		// Display the template(s)
		parent::display();
	}
	
	/**
	 * Add a consumer
	 */
	function add()
	{
		// Document
		$document = &JFactory::getDocument();
		$document->setTitle( JText::_('Arc - Consumer Manager') );
		
		// Set toolbar items for the page
		JToolBarHelper::title( JText::_('Consumer Manager: Add a consumer'), 'config.png' );
		
		// Show the edit form
		$this->_showEditForm();
	}
	
	/**
	 *  Edit a consumer
	 */
	function edit()
	{
		// Document
		$document = &JFactory::getDocument();
		$document->setTitle( JText::_('Arc - Consumer Manager') );
		
		// Set toolbar items for the page
		JToolBarHelper::title( JText::_('Consumer Manager: Edit a consumer'), 'config.png' );
		
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
		
		// Retrieve the consumer to edit
		$this->consumer = &$this->get( 'Consumer' );
		
		// Display the edit template
		parent::display( 'edit' );
	}
}
?>
