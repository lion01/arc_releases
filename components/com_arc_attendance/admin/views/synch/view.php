<?php
/**
 * @package     Arc
 * @subpackage  Attendance
 * @copyright   Copyright (C) 2005 Punnet. All rights reserved. See COPYRIGHT_ARC.txt
 * @license     http://www.gnu.org/licenses/gpl.html GNU/GPL. See LICENSE_ARC.txt
 * Arc is free software: you can redistribute it and/or modify
 * it under the terms of version 3 of the GNU General Public License
 * as published by the Free Software Foundation.
 * The disclaimer of warranty as stated in the GPL applies to this program
 */

defined( '_JEXEC' ) or die( 'Restricted access' );

jimport('joomla.application.component.view');

/**
 * Extension Manager Install View
 *
 * @author		Louis Landry <louis.landry@joomla.org>
 * @package		Joomla
 * @subpackage	Installer
 * @since		1.5
 */
class AttendancemanagerViewSynch extends JView
{
	function display($tpl = null)
	{
		/*
		 * Set toolbar items for the page
		 */
		JToolBarHelper::title( JText::_( 'Attendance Manager' ), 'install.png' );
		JToolBarHelper::save();
		
		$bar = & JToolBar::getInstance('toolbar');
		$bar->addButtonPath( JPATH_SITE.DS.'administrator'.DS.'components'.DS.'com_arc_core'.DS.'images' );
		
		$document = &JFactory::getDocument();
		
		$style = '.icon-32-import 		{ background-image: url(components'.DS.'com_arc_core'.DS.'images'.DS.'icon-32-import.png); }';
		$document->addStyleDeclaration( $style );
		/* for when we can write to external applications
		$style = '.icon-32-export 		{ background-image: url(components'.DS.'com_arc_core'.DS.'images'.DS.'icon-32-export.png); }';
		$document->addStyleDeclaration( $style );
		*/
		
		JToolBarHelper::customX('import', 'import', '', 'Import', false);
		/* for when we can write to external applications
		JToolBarHelper::customX('export', 'export', '', 'Export', false);
		*/
		
		// Document
		$document = & JFactory::getDocument();
		$document->setTitle(JText::_( 'Apotheosis - Attendance Manager' ));
		
		// Get data from the model
		$varMap = array('state'=>'State', 'items'=>'Items');
		ApotheosisLib::setViewVars( $this, $varMap );
		
		$params = JComponentHelper::getParams('com_arc_attendance');
		$this->assignRef('params', $params);
		
		// and display
		parent::display($tpl);
	}
		
}
?>
