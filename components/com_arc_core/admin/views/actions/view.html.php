<?php
/**
 * @package     Arc
 * @subpackage  Core
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
 * Core Admin Actions View
 *
 * @author     Punnet - Arc Team
 * @package    Arc
 * @subpackage Core
 * @since      1.6
 */
class CoreAdminViewActions extends JView
{
	/**
	 * Provides the actions view class
	 */
	function __construct()
	{
		parent::__construct();
		JHTML::_( 'behavior.tooltip' );
	}
	
	function display()
	{
		// Document
		$document = &JFactory::getDocument();
		$document->setTitle( JText::_('Arc - Core Manager') );
		
		// Set toolbar items for the page
		JToolBarHelper::title( JText::_('Core Manager: Actions'), 'config.png' );
		JToolBarHelper::addNew();
		JToolBarHelper::editList();
		JToolBarHelper::deleteList();
		
		// Get data from the model
		$this->items = &$this->get( 'Items' );
		
		// and display
		parent::display();
	}
	
	function loadItem( $index = 0 )
	{
		$this->item = &$this->items[$index];
		$this->item->index = $index;
	}
	
	function add()
	{
		// Document
		$document = &JFactory::getDocument();
		$document->setTitle( JText::_('Arc - Core Manager') );
		
		// Set toolbar items for the page
		JToolBarHelper::title( JText::_('Core Manager: New Action'), 'config.png' );
		JToolBarHelper::save();
		JToolBarHelper::cancel();
		
		// Get data from the model
		$this->action = $this->get( 'Action' );
		$this->menus = $this->get( 'MenuOptions' );
		
		// and display
		parent::display( 'form' );
	}
	
	function edit()
	{
			// Document
		$document = &JFactory::getDocument();
		$document->setTitle( JText::_('Arc - Core Manager') );
		
		// Set toolbar items for the page
		JToolBarHelper::title( JText::_('Core Manager: Edit action'), 'config.png' );
		JToolBarHelper::save();
		JToolBarHelper::apply();
		JToolBarHelper::cancel();
		
		// Get data from the model
		$this->action = $this->get( 'Action' );
		$this->menus = $this->get( 'MenuOptions' );
		
		// and display
		parent::display( 'form' );
	}
}
?>