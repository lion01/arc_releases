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
 * Timetable Admin Pattern Instances View
 *
 * @author     Punnet - Arc Team
 * @package    Arc
 * @subpackage Timetable
 * @since      1.6
 */
class TimetableAdminViewInstances extends JView
{
	/**
	 * Provides the instances view class
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
		JToolBarHelper::title( JText::_('Timetable Manager: Pattern Instances'), 'config.png' );
		
		$varMap = array( 'patternInstances'=>'PagedPatternInstances'
			, 'search'=>'Search'
			, 'pagination'=>'Pagination' );
		ApotheosisLib::setViewVars( $this, $varMap );
		
		// Display the template(s)
		parent::display();
	}
	
	function getPattern( $id )
	{
		if( !isset($this->_patterns[$id]) ) {
			$fPat = ApothFactory::_( 'timetable.Pattern' );
			$this->_patterns[$id] = $fPat->getInstance( $id );
		}
		return $this->_patterns[$id];
	}
}
?>