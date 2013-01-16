<?php
/**
 * @package     Arc
 * @subpackage  TV
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
 * TV Admin Database View
 *
 * @author     Punnet - Arc Team
 * @package    Arc
 * @subpackage TV
 * @since      1.6
 */
class TvAdminViewDatabase extends JView
{
	/**
	 * Creates a new TV Database view
	 */
	function __construct()
	{
		parent::__construct();
		JHTML::_( 'behavior.tooltip' );
		
		// Path to tmpl to aid inclusion of CSS and JS
// 		$this->addPath = JURI::base().'components'.DS.'com_arc_tv'.DS.'views'.DS.'database'.DS.'tmpl'.DS;
	}
	
	/**
	 * Default method
	 */
	function display()
	{
		// Document
		$document = &JFactory::getDocument();
		$document->setTitle( JText::_('Arc - TV Manager') );
		
		// Set toolbar items for the page
		JToolBarHelper::title( JText::_('TV Manager: Video Database'), 'config.png' );
		JToolBarHelper::save();
		
		// Retrieve data for display in templates
		$this->databaseParams = &$this->get( 'Params' );
		
		// Display the template(s)
		parent::display();
	}
}
?>