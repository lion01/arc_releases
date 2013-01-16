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
 * Timetable Admin Days View
 *
 * @author     Punnet - Arc Team
 * @package    Arc
 * @subpackage Timetable
 * @since      1.6
 */
class TimetableAdminViewDays extends JView
{
	/**
	 * Provides the days view class
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
		$document->setTitle( JText::_('Arc - Timetable Manager') );
		
		// Set toolbar items for the page
		JToolBarHelper::title( JText::_('Timetable Manager: Day Types'), 'config.png' );
		
		$varMap = array( 'days'=>'PagedDays'
			, 'search'=>'Search'
			, 'pagination'=>'Pagination' );
		ApotheosisLib::setViewVars( $this, $varMap );
		
		// Display the template(s)
		parent::display();
	}
	
	/**
	 * Display the sections of a given day
	 * @see JView::display()
	 */
	function displaySections()
	{
		// Document
		$document = &JFactory::getDocument();
		$document->setTitle( JText::_('Arc - Timetable Manager') );
		
		// Set toolbar items for the page
		JToolBarHelper::title( JText::_('Timetable Manager: Day Types'), 'config.png' );
		
		$varMap = array( 'sections'=>'PagedSections'
			, 'days'=>'Days'
			, 'search'=>'Search'
			, 'pagination'=>'Pagination' );
		ApotheosisLib::setViewVars( $this, $varMap );
		
		// Display the template(s)
		parent::display( 'day_sections' );
	}
}
?>