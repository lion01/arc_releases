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
 * People Admin Profiles View
 *
 * @author     Punnet - Arc Team
 * @package    Arc
 * @subpackage People
 * @since      1.6
 */
class PeopleAdminViewProfiles extends JView
{
	/**
	 * Provides the profile view class
	 */
	function __construct()
	{
		parent::__construct();
		JHTML::_( 'behavior.tooltip' );
	}
	
	/**
	 * Default method
	 */
	function display()
	{
		// Document
		$document = &JFactory::getDocument();
		$document->setTitle( JText::_( 'Arc - People Manager' ) );
		
		// Set toolbar items for the page
		JToolBarHelper::title( JText::_('People Manager: Profiles'), 'config.png' );
		JToolBarHelper::custom( 'select_task', 'forward', 'forward', 'Continue', false );
		
		// Display the task select list
		parent::display();
	}
	
	/**
	 * Displays a list of templates
	 */
	function selectTemplate()
	{
		// Document
		$document = &JFactory::getDocument();
		$document->setTitle( JText::_( 'Arc - People Manager' ) );
		
		// Add missing style declaration for the revert image
		$revertButtonStyle = '.icon-32-revert { background-image: url(templates'.DS.'khepri'.DS.'images'.DS.'toolbar'.DS.'icon-32-revert.png); }';
		$document->addStyleDeclaration( $revertButtonStyle );
		
		// Retrieve data for display in templates
		$this->curType = $this->get( 'curType' );
		
		// Set toolbar items for the page
		JToolBarHelper::title( JText::_('People Manager: Profiles: Manage Profile Templates'), 'config.png' );
		JToolBarHelper::custom( 'save_template_types', 'save', 'save', 'Save', false );
		JToolBarHelper::custom( 'revert_template_types', 'revert', 'revert', 'Revert', false );
		JToolBarHelper::custom( 'edit_template', 'edit', 'edit', 'Edit Template', false );
		JToolBarHelper::custom( 'profile_tasks', 'cancel', 'cancel', 'Cancel', false );
		
		// Retrieve data for display in templates
		$this->templateIds = $this->get( 'TemplateIds' );
		
		// Display the list of templates
		parent::display( 'templates' );
	}
	
	/**
	 * Displays a the year group advancement page
	 */
	function updateYearGroups()
	{
		// Document
		$document = &JFactory::getDocument();
		$document->setTitle( JText::_( 'Arc - People Manager' ) );
		
		// Set toolbar items for the page
		JToolBarHelper::title( JText::_('People Manager: Profiles: Update Year Groups'), 'config.png' );
		JToolBarHelper::custom( 'year_groups_apply', 'apply', 'apply', 'Apply', false );
		JToolBarHelper::custom( 'profile_tasks', 'cancel', 'cancel', 'Cancel', false );
		
		// Display the list of year group management tasks
		parent::display( 'year_groups' );
	}
	
	/**
	 * Displays a given profile for viewing / editing
	 */
	function profile()
	{
		// Document
		$document = &JFactory::getDocument();
		$document->setTitle( JText::_( 'Arc - People Manager' ) );
		
		// Add missing style declaration for the revert image
		$revertButtonStyle = '.icon-32-revert { background-image: url(templates'.DS.'khepri'.DS.'images'.DS.'toolbar'.DS.'icon-32-revert.png); }';
		$document->addStyleDeclaration( $revertButtonStyle );
		
		// Retrieve data for display in templates
		$this->curType = $this->get( 'curType' );
		$this->curIds = $this->get( 'CurIds' );
		$this->profile = $this->get( 'Profiles' );
		$this->templateIds = $this->get( 'TemplateIds' );
		
		// Prepare tooblar text
		if( count($this->curIds) == 1 ) {
			$curType = ucfirst( $this->curType );
			if( $this->curType == 'template' ) {
				$curId = ucfirst( reset($this->curIds) );
			}
			elseif( $this->curType == 'profile' ) {
				$curId = ApotheosisData::_( 'people.displayName', reset($this->curIds), 'person' ).'\'s';
			}
		}
		else {
			$curType = ucfirst( $this->curType ).'s';
			$curId = 'Multiple';
		}
		
		// Set toolbar items for the page
		JToolBarHelper::title( JText::_('People Manager: Profiles: '.$curId.' '.$curType), 'config.png' );
		JToolBarHelper::custom( 'save_'.$this->curType, 'save', 'save', 'Save', false );
		JToolBarHelper::custom( 'revert_'.$this->curType, 'revert', 'revert', 'Revert', false );
		JToolBarHelper::custom( 'select_task', 'cancel', 'cancel', 'Cancel', false );
		
		// Display the template(s)
		parent::display( 'profile' );
	}
}
?>