<?php
/**
 * @package     Arc
 * @subpackage  Behaviour
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
 * Behaviour Manager Incidents View
 *
 * @author		Punnet - Arc Team <arc_developers@pun.net>
 * @package		Arc
 * @subpackage	Behaviour
 * @since		1.5
 */
class BehaviourAdminViewIncidents extends JView
{
	/**
	 * Creates a new behaviour manager messages view
	 */
	function __construct()
	{
		parent::__construct();
		JHTML::_( 'behavior.tooltip' );
		
		// Path to tmpl to aid inclusion of CSS and JS
		$this->addPath = JURI::base().'components'.DS.'com_arc_behaviour'.DS.'views'.DS.'incidents'.DS.'tmpl'.DS;
	}
	
	/**
	 * Default method
	 */
	function display()
	{
		// Document
		$document = &JFactory::getDocument();
		$document->setTitle( JText::_('Arc - Behaviour Manager') );
		
		// Set toolbar items for the page
		JToolBarHelper::title( JText::_('Behaviour Manager'), 'config.png' );
		JToolBarHelper::addNew();
		JToolBarHelper::editList();
		JToolBarHelper::deleteList();
		
		// Retrieve data for display in templates
		$this->model = &$this->getModel();
		$this->incidents = &$this->get( 'PagedIncidents' );
		$this->pagination = &$this->get( 'Pagination' );
		
		// Display the template(s)
		parent::display();
	}
	
	/**
	 *  Edit a message
	 */
	function edit()
	{
		// Document
		$document = &JFactory::getDocument();
		$document->setTitle( JText::_('Arc - Behaviour Manager: Edit incident type') );
		
		// Set toolbar items for the page
		JToolBarHelper::title( JText::_('Behaviour Manager: Edit incident type'), 'config.png' );
		
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
		JToolBarHelper::apply();
		JToolBarHelper::cancel();
		
		// Retrieve the message to edit
		$this->incident = &$this->get( 'Incident' );
		$this->parent = &$this->get( 'parentIncident' );
		
		// Display the edit template
		parent::display( 'edit' );
	}
}
?>
