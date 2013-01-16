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
 * Timetable Admin Synch View
 *
 * @author     Punnet - Arc Team
 * @package    Arc
 * @subpackage Timetable
 * @since      1.6
 */
class TimetableAdminViewSynch extends JView
{
	/**
	 * Provides the synch view class
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
		$document->setTitle( JText::_( 'Arc - Timetable Manager' ) );
		
		// Add missing style declaration for the export image
		$exportButtonStyle = '.icon-32-export { background-image: url(templates'.DS.'khepri'.DS.'images'.DS.'toolbar'.DS.'icon-32-export.png); }';
		$document->addStyleDeclaration( $exportButtonStyle );
		
		// Set toolbar items for the page
		JToolBarHelper::title( JText::_('Timetable Manager: Import Timetable'), 'config.png' );
		JToolBarHelper::custom( 'import', 'export', 'export', 'Import', false );
		
		// Retrieve data for display in templates
		$this->synchParams = $this->get( 'SynchParams' );
		
		// Display the template(s)
		parent::display();
	}
}
?>