<?php
/**
 * @package     Arc
 * @subpackage  People
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
 * People Manager List View
 *
 * @author     Lightinthedark <code@lightinthedark.org.uk>
 * @package    Arc
 * @subpackage People Manager
 * @since      1.5
 */
class PeopleViewList extends JView
{
	/**
	 * Default method to display the search form and list of found people
	 */
	function display()
	{
		$document = &JFactory::getDocument();
		$document->setTitle( JText::_('Arc People Manager') );
		
		$this->model = &$this->getModel();
		$this->people = &$this->get( 'People' );
		$this->rels = $this->get( 'RelSearch' );
		
		parent::display();
	}
}
?>