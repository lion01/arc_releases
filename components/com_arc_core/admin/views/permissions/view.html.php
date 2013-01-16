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
 * Core Admin Permissions View
 *
 * @author     Punnet - Arc Team
 * @package    Arc
 * @subpackage Core
 * @since      1.6
 */
class CoreAdminViewPermissions extends JView
{
	/**
	 * Creates a new core manager permissions view
	 */
	function __construct()
	{
		parent::__construct();
		JHTML::_( 'behavior.tooltip' );
		
		// Path to tmpl to aid inclusion of CSS and JS
		$this->addPath = JURI::base().'components'.DS.'com_arc_core'.DS.'views'.DS.'permissions'.DS.'tmpl'.DS;
	}
	
	function display()
	{
		// Document
		$document = &JFactory::getDocument();
		$document->setTitle( JText::_('Arc - Core Manager') );
		
		// Set toolbar items for the page
		JToolBarHelper::title( JText::_('Arc Manager: Permissions'), 'config.png' );
		JToolBarHelper::custom( 'flush', 'forward', 'forward', 'Flush Privileges', false );
		
		// Get data from the model
		$this->actions = &$this->get('actions');
		$this->roles = &$this->get('roles');
		
		// and display
		parent::display();
	}
}
?>