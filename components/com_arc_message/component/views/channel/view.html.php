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
class MessageViewChannel extends JView
{
	function __construct()
	{
		$document = &JFactory::getDocument();
		$document->setTitle( JText::_( 'Arc messaging channels' ) );
		
		parent::__construct();
	}
	
	function display()
	{
		$subscribed = $this->get('Subscribed');
		$this->mySubs   = $subscribed[0];
		$this->derived  = empty( $subscribed[1] ) ? '' : htmlspecialchars($subscribed[1]);
		$this->global   = $this->get('Global');
		$this->public   = $this->get('Shared');
		$this->private  = $this->get('Private');
		$this->channel  = $this->get('Channel');
		parent::display();
	}
}
?>