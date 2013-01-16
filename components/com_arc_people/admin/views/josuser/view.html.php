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
 * People Admin Josuser View
 *
 * @author     Punnet - Arc Team
 * @package    Arc
 * @subpackage People
 * @since      1.6
 */
class PeopleAdminViewJosuser extends JView
{
	/**
	 * Provides the people view class
	 */
	function __construct()
	{
		parent::__construct();
		JHTML::_( 'behavior.tooltip' );
		
//		// Path to tmpl to aid inclusion of CSS and JS
//		$this->addPath = JURI::base().'components'.DS.'com_arc_people'.DS.'views'.DS.'people'.DS.'tmpl'.DS;
	}
	
	/**
	 * Default method
	 */
	function display()
	{
		// Document
		$document = &JFactory::getDocument();
		$document->setTitle( JText::_('Arc - People Manager') );
		
		// Set toolbar items for the page
		JToolBarHelper::title( JText::_('People Manager: Joomla! User Management'), 'user.png' );
		JToolBarHelper::custom( 'select_task', 'forward', 'forward', 'Continue', false );
		
		// Display the task select list
		parent::display();
	}
	
	/**
	 * Display a screen with options for creating Joomla user accounts
	 */
	function josCreate()
	{
		// Document
		$document = &JFactory::getDocument();
		$document->setTitle( JText::_('Arc - People Manager') );
		
		// Add missing style declaration for the adduser image
		$adduserButtonStyle = '.icon-32-adduser { background-image: url(templates'.DS.'khepri'.DS.'images'.DS.'toolbar'.DS.'icon-32-adduser.png); }';
		$document->addStyleDeclaration( $adduserButtonStyle );
		
		// Set toolbar items for the page
		JToolBarHelper::title( JText::_('People Manager: Joomla! User Creation'), 'user.png' );
		JToolBarHelper::custom( 'apply_task', 'adduser', 'adduser', 'Create Joomla! Users', false );
		JToolBarHelper::cancel();
		
		// Display the Joomla user creation page
		parent::display( 'josuser_create' );
	}
	
	/**
	 * Display a screen to upload a CSV containing Arc IDs / Passwords
	 */
	function pword()
	{
		// Document
		$document = &JFactory::getDocument();
		$document->setTitle( JText::_('Arc - People Manager') );
		
		// Set toolbar items for the page
		JToolBarHelper::title( JText::_('People Manager: Joomla! Password Creation'), 'user.png' );
		JToolBarHelper::custom( 'apply_task', 'upload', 'upload', 'Process Passwords', false );
		JToolBarHelper::cancel();
		
		// Display the Joomla user creation page
		parent::display( 'josuser_pword' );
	}
	
	/**
	 * Display a screen with options for creating Joomla user accounts
	 */
	function josFormat()
	{
		// Document
		$document = &JFactory::getDocument();
		$document->setTitle( JText::_('Arc - People Manager') );
		
		// Add missing style declaration for the revert image
		$revertButtonStyle = '.icon-32-revert { background-image: url(templates'.DS.'khepri'.DS.'images'.DS.'toolbar'.DS.'icon-32-revert.png); }';
		$document->addStyleDeclaration( $revertButtonStyle );
		
		// Set toolbar items for the page
		JToolBarHelper::title( JText::_('People Manager: Joomla! User Variable Format'), 'user.png' );
		JToolBarHelper::custom( 'save_format', 'save', 'save', 'Save Format', false );
		JToolBarHelper::custom( 'revert_format', 'revert', 'revert', 'Revert', false );
		JToolBarHelper::cancel();
		
		// Retrieve data for display in templates
		$this->params = $this->get( 'Params' );
		$this->curDomain = $this->get( 'Domain' );
		
		// Display the Joomla user creation warning
		parent::display( 'josuser_format' );
	}
}
?>