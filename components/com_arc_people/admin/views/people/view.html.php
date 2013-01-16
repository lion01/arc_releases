<?php
/**
 * @package     Arc
 * @subpackage  People
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
 * People Admin People View
 *
 * @author     Punnet - Arc Team
 * @package    Arc
 * @subpackage People
 * @since      1.6
 */
class PeopleAdminViewPeople extends JView
{
	/**
	 * Provides the people view class
	 */
	function __construct()
	{
		parent::__construct();
		JHTML::_( 'behavior.tooltip' );
		
		// Path to tmpl to aid inclusion of CSS and JS
		$this->addPath = JURI::base().'components'.DS.'com_arc_people'.DS.'views'.DS.'people'.DS.'tmpl'.DS;
	}
	
	/**
	 * Default method
	 */
	function display()
	{
		// Document
		$document = &JFactory::getDocument();
		$document->setTitle( JText::_('Arc - People Manager') );
		
		// Add missing style declaration for the search image
		$searchButtonStyle = '.icon-32-search { background-image: url(templates'.DS.'khepri'.DS.'images'.DS.'toolbar'.DS.'icon-32-search.png); }';
		$document->addStyleDeclaration( $searchButtonStyle );
		
		// Set toolbar items for the page
		JToolBarHelper::title( JText::_('People Manager: People'), 'config.png' );
		JToolBarHelper::custom( 'details', 'search', 'search', 'Edit Details', true );
		JToolBarHelper::custom( 'profiles', 'copy', 'copy', 'Edit Profile(s)', true );
		
		// Retrieve data for display in templates
		$this->search = implode( ' ', $this->get('SearchTerms') );
		$this->people = &$this->get( 'PagedPeople' );
		$this->peopleCount = count( $this->people );
		$this->pagination = &$this->get( 'Pagination' );
		
		// Display the template(s)
		parent::display();
	}
	
	/**
	 * View a persons details
	 */
	function details()
	{
		// Document
		$document = &JFactory::getDocument();
		$document->setTitle( JText::_('Arc - People Manager') );
		
		// Add missing style declaration for the revert image
		$revertButtonStyle = '.icon-32-revert { background-image: url(templates'.DS.'khepri'.DS.'images'.DS.'toolbar'.DS.'icon-32-revert.png); }';
		$document->addStyleDeclaration( $revertButtonStyle );
		
		// Set toolbar items for the page
		JToolBarHelper::title( JText::_('People Manager: Detailed Person Information'), 'config.png' );
		JToolBarHelper::custom( 'save_details', 'save', 'save', 'Save', false );
		JToolBarHelper::custom( 'revert_details', 'revert', 'revert', 'Revert', false );
		JToolBarHelper::custom( 'display', 'cancel', 'cancel', 'Cancel', false );
		
		// Retrieve data for display in templates
		$this->personIndex = &$this->get( 'PersonIndex' );
		$this->person = &$this->get( 'Person' );
		$this->edit = false;
		
		// Display the edit template
		parent::display( 'details' );
	}
}
?>