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

// Give us access to the joomla view class
jimport('joomla.application.component.view');

/**
 * Message Admin Twitter View
 *
 * @author     Punnet - Arc Team
 * @package    Arc
 * @subpackage Message
 * @since      1.6
 */
class MessageAdminViewTwitter extends JView
{
	/**
	 * Creates a new course manager courses view
	 */
	function __construct()
	{
		parent::__construct();
		JHTML::_( 'behavior.tooltip' );
		
		// Path to tmpl to aid inclusion of CSS and JS
		$this->addPath = JURI::base().'components'.DS.'com_arc_message'.DS.'views'.DS.'twitter'.DS.'tmpl'.DS;
	}
	
	/**
	 * Default method
	 */
	function display()
	{
		// Document
		$document = &JFactory::getDocument();
		$document->setTitle( JText::_('Arc - Message Manager') );
		
		// Set toolbar items for the page
		JToolBarHelper::title( JText::_( 'Message Manager: Twitter Integration' ), 'config.png' );
		JToolBarHelper::custom( 'displayAuth', 'forward', 'forward', 'Authorise', false );
		JToolBarHelper::save();
		JToolBarHelper::cancel();
		
		// Get data from the model
		$this->comParams = &$this->get('Params');
		
		// Display the template(s)
		parent::display();
	}
	
	/**
	 * Redirects the user to the twitter authorization page
	 */
	function displayAuth()
	{
		$_POST = array();
		$_GET = array();
		header( 'Location: '.$this->get( 'AuthUrl' ).'?oauth_token='.$this->get( 'OauthToken' ) );
	}
	
	function displayAuthError( $E )
	{
		global $mainframe;
		$mainframe->enqueueMessage( $E->getMessage(), 'error' );
		
/*  debug help
		ob_start();
		var_dump_pre( $_POST, 'POST' );
		var_dump_pre( $_GET,  'GET' );
		var_dump_pre( $E );
		$mainframe->enqueueMessage( ob_get_clean(), 'error' );
// */
		
		$this->display();
	}
}
?>