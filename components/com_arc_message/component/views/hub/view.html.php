<?php
/**
 * @package     Arc
 * @subpackage  Message
 * @copyright   Copyright (C) 2005 Punnet. All rights reserved. See COPYRIGHT_ARC.txt
 * @license     http://www.gnu.org/licenses/gpl.html GNU/GPL. See LICENSE_ARC.txt
 * Arc is free software: you can redistribute it and/or modify
 * it under the terms of version 3 of the GNU General Public License
 * as published by the Free Software Foundation.
 * The disclaimer of warranty as stated in the GPL applies to this program
 */

defined( '_JEXEC' ) or die( 'Restricted access' );

jimport( 'joomla.application.component.view' );

/**
 * Planner List View
 */
class MessageViewHub extends JView
{
	function __construct()
	{
		$document = &JFactory::getDocument();
		$document->setTitle( JText::_( 'Arc messaging hub' ) );
		
		parent::__construct();
	}
	
	/**
	 * Shows the main page containing all the matched tasks 
	 * Gets the task lists from the model then displays them for each category
	 */
	function showMessages()
	{
		$this->emptyMessage = 'This folder is empty';
		$this->empty = $this->get('ThreadCount') == 0;
		$this->page = $this->get('Page');
		$this->pageCount = $this->get('PageCount');
		$document = &JFactory::getDocument();
		parent::display();
	}
	
	function showForm()
	{
		parent::display( 'form' );
	}
	
	function showSearch()
	{
		$this->emptyMessage = '<p>Your search for <b>'.htmlspecialchars(JRequest::getVar('msg_search', '')).'</b> returned no results.<br />'
			.'Try broadening your search criteria.</p>';
		$this->empty = $this->get('ThreadCount') == 0;
		$this->page = $this->get('Page');
		$this->pageCount = $this->get('PageCount');
		$document = &JFactory::getDocument();
		parent::display();
	}
	
}
?>
