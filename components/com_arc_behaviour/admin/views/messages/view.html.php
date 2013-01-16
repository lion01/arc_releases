<?php
/**
 * @package     Arc
 * @subpackage  Behaviour
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
 * Behaviour Admin Messages View
 *
 * @author		Punnet - Arc Team <arc_developers@pun.net>
 * @package		Arc
 * @subpackage	Behaviour
 * @since		1.5
 */
class BehaviourAdminViewMessages extends JView
{
	/**
	 * Creates a new behaviour manager messages view
	 */
	function __construct()
	{
		parent::__construct();
		JHTML::_( 'behavior.tooltip' );
		
		// Path to tmpl to aid inclusion of CSS and JS
		$this->addPath = JURI::base().'components'.DS.'com_arc_behaviour'.DS.'views'.DS.'messages'.DS.'tmpl'.DS;
	}
	
	/**
	 * Default method
	 */
	function display()
	{
		// Document
		$document = &JFactory::getDocument();
		$document->setTitle( JText::_('Arc - Behaviour Manager') );
		
		// Set toolbar items for the page
		JToolBarHelper::title( JText::_('Behaviour Manager: Messages'), 'config.png' );
		JToolBarHelper::editList();
		JToolBarHelper::custom( 'remove', 'delete', 'delete', 'Rescind', true );
		
		// Retrieve data for display in templates
		$this->model = &$this->getModel();
		$this->search = $this->get( 'SearchTerm' );
		$this->sender = $this->get( 'SenderTerm' );
		$this->pupil = $this->get( 'PupilTerm' );
		$this->threads = &$this->get( 'PagedThreads' );
		$this->threadCount = count( $this->threads );
		$this->messageCount = 0;
		foreach( $this->threads as $thread ) {
			$this->messageCount += $thread->getMessageCount();
		}
		$this->pagination = &$this->get( 'Pagination' );
		$this->curIndex = 0;
		
		// Display the template(s)
		parent::display();
	}
	
	/**
	 *  Edit a message
	 */
	function edit()
	{
		// Document
		$document = &JFactory::getDocument();
		$document->setTitle( JText::_('Arc - Behaviour Manager') );
		
		// Set toolbar items for the page
		JToolBarHelper::title( JText::_('Behaviour Manager: Edit a message'), 'config.png' );
		
		// Show the edit form
		$this->_showEditForm();
	}
	
	/**
	 * Show the edit form
	 */
	function _showEditForm()
	{
		// Set toolbar items for the page
		JToolBarHelper::save();
		JToolBarHelper::apply();
		JToolBarHelper::custom( 'resend', 'send', 'send', 'Re-Send', false);
		JToolBarHelper::cancel();
		
		// Retrieve the message to edit
		$this->message = &$this->get( 'Message' );
		
		// work out the incident id
		$fInc = ApothFactory::_( 'behaviour.IncidentType' );
		$inc = $this->message->getDatum( 'incident' );
		if( is_null($inc) ) {
			$this->inc = $fInc->getInstance( -2 );
			$this->incType = $fInc->getInstance( ( isset($data['msg_inc_type']) ? $data['msg_inc_type'] : null ) );
		}
		else {
			$this->inc = $fInc->getInstance( $inc );
			$this->incType = $fInc->getInstance( $this->inc->getParentId() );
		}
		
		$prev = &$this->message->getPreviousMessage();
		$this->first = is_null($prev);
		
		// Display the edit template
		parent::display( 'edit' );
	}
	
	/**
	 * Rescind a message or messages
	 */
	function remove()
	{
		// Document
		$document = &JFactory::getDocument();
		$document->setTitle( JText::_('Arc - Behaviour Manager') );
	
		// Set toolbar items for the page
		JToolBarHelper::title( JText::_('Behaviour Manager: Rescind Message(s)'), 'config.png' );
		JToolBarHelper::custom( 'rescind', 'delete', 'delete', 'Confirm Rescind', false );
		JToolBarHelper::cancel();
		
		// Retrieve data for display in templates
		$this->model = &$this->getModel();
		$this->thread = &$this->get( 'Thread' );
		$this->rescindMsgIds = $this->get( 'RescindMsgIds' );
		
		// Display the rescind template
		parent::display( 'rescind' );
	}
}
?>