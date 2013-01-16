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
 * Core Admin Components View
 *
 * @author     Punnet - Arc Team
 * @package    Arc
 * @subpackage Core
 * @since      1.6
 */
class CoreAdminViewComponents extends JView
{
	/**
	 * Provides the components view class
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
		JToolBarHelper::title( JText::_('Core Manager: Components'), 'config.png' );
		JToolBarHelper::custom( 'Uninstall', 'delete', 'delete', 'Uninstall', true );
		
		// Get data from the model
		$this->items = &$this->get( 'Items' );
		
		// Display the template(s)
		parent::display();
	}
	
	function loadItem( $index = 0 )
	{
		$this->item = &$this->items[$index];
	}
}
?>