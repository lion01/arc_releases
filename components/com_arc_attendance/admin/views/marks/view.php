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
class AttendancemanagerViewMarks extends JView
{
	function display($tpl = null)
	{
		/*
		 * Set toolbar items for the page
		 */
		JToolBarHelper::title( JText::_( 'Attendance Manager' ), 'install.png' );
		JToolBarHelper::addNew();
		JToolBarHelper::deleteList();
		
		// Document
		$document = & JFactory::getDocument();
		$document->setTitle(JText::_( 'Apotheosis - Attendance Manager' ));
		
		// Get data from the model
		$varMap = array('state'=>'State', 'items'=>'Items');
		ApotheosisLib::setViewVars( $this, $varMap );

		// and display
		parent::display($tpl);
	}

	/**
	 * Add an attendance mark screen
	 */
	function addMark()
	{
		/*
		 * Set toolbar items for the page
		 */
		JToolBarHelper::title( JText::_( 'Attendance Manager' ), 'install.png' );
		JToolBarHelper::save('saveMark');
		JToolBarHelper::cancel();

		// Document
		$document = & JFactory::getDocument();
		$document->setTitle(JText::_( 'Apotheosis - Attendance Manager' ));
		
		// Get data from the model
		$varMap = array('state'=>'State',
			'items'=>'Items',
			'meanings'=>'Meanings');
		ApotheosisLib::setViewVars($this, $varMap);
		
		parent::display('add');
	}
		
	/**
	 * Set the edit an attendance mark screen up
	 */
	function editMark()
	{
		/*
		 * Set toolbar items for the page
		 */
		JToolBarHelper::title( JText::_( 'Attendance Manager' ), 'install.png' );
		JToolBarHelper::save('saveMark');
		JToolBarHelper::apply('applyMark');
		JToolBarHelper::cancel();

		// Document
		$document = & JFactory::getDocument();
		$document->setTitle(JText::_( 'Apotheosis - Attendance Manager' ));
		
		// Get data from the model
		$varMap = array('state'=>'State',
			'items'=>'Items',
			'meanings'=>'Meanings');
		ApotheosisLib::setViewVars($this, $varMap);
		
		parent::display('edit');
	}
	
	function loadItem($index=0)
	{
		if (!isset($this->meanings)) {
			$meanings = &$this->get('Meanings');
			$this->assignRef('meanings', $meanings);
		}
		$item =& $this->items[$index]; // change in model so we get numbers for meanings, and on a different property (name)
		
		$item->school_meaning = $this->meanings['school'][$item->school_meaning_id]->school_meaning;
		$item->statistical_meaning = $this->meanings['statistical'][$item->statistical_meaning_id]->statistical_meaning; // other meanings
		$item->physical_meaning = $this->meanings['physical'][$item->physical_meaning_id]->physical_meaning;
		
		$item->index = $index;
		$this->assignRef('item', $item);
	}
	
}
?>
