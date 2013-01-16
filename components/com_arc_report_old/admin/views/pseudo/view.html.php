<?php
/**
 * @package     Arc
 * @subpackage  Report
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
class ReportsViewPseudo extends JView
{
	function display($tpl = null)
	{
		/*
		 * Set toolbar items for the page
		 */
		JToolBarHelper::title( JText::_( 'Reports - Pseudo Courses' ), 'massemail.png' );

		JToolBarHelper::addNewX();
		JToolBarHelper::editListX();
		JToolBarHelper::deleteList();
		
		// Document
		$document = &JFactory::getDocument();
		$document->setTitle(JText::_( 'Apotheosis - Reports' ));
		
		// Get data from the model
		$varMap = array('state'=>'State', 'courses'=>'Courses');
		ApotheosisLib::setViewVars( $this, $varMap );
		
		$params = JComponentHelper::getParams('com_arc_report');
		$this->assignRef('params', $params);
		
		// and display
		parent::display($tpl);
	}

	function loadCourse($index=0)
	{
		$item =& $this->courses[$index]; // change in model so we get numbers for meanings, and on a different property (name)
				
		$item->index = $index;
		$this->assignRef('course', $item);
	}
	
	function edit()
	{
		JToolBarHelper::title( JText::_( 'Reports - Edit Pseudo Course' ), 'massemail.png' );

		JToolBarHelper::save('updateCourse');
		JToolBarHelper::cancel();

		// Get data from the model
		$varMap = array('course'=>'Course', 'currentCycles'=>'CurrentCycles', 'enrolSubjects'=>'EnrolSubjects', 'enrolClasses'=>'EnrolClasses', 'yearGroups'=>'YearGroups');
		ApotheosisLib::setViewVars( $this, $varMap );

		$tpl = 'edit';
		parent::display( $tpl );
	}
	
	function newCourse()
	{
		JToolBarHelper::title( JText::_( 'Reports - New Pseudo Course' ), 'massemail.png' );

		JToolBarHelper::save('newCourse');
		JToolBarHelper::cancel();

		// Get data from the model
		$varMap = array('currentCycles'=>'CurrentCycles', 'parentSubjects'=>'EnrolSubjects', 'parentPseudo'=>'EnrolPseudo', 'parentClasses'=>'EnrolClasses', 'yearGroups'=>'YearGroups');
		ApotheosisLib::setViewVars( $this, $varMap );
				
		$tpl = 'new';
		parent::display( $tpl );
	}
}
?>
