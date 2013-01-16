<?php
/**
 * @package     Arc
 * @subpackage  Planner
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
 * Planner Category View
 */
class PlannerViewCategory extends JView
{
	function __construct()
	{
		$document = &JFactory::getDocument();
		$document->setTitle( JText::_( 'Arc Planner Categories' ) );
		
		parent::__construct();
	}
	
	/**
	 * Shows the main page containing all the categories and their statistics
	 * Gets the category lists from the model then displays them in 2 separate tables
	 */
	function catList()
	{
		$this->model = &$this->getModel( 'category' );
		$plannerParams = &JComponentHelper::getParams( 'com_arc_planner' );
		$this->dueDaysAhead = $plannerParams->get( 'due_days_ahead' );
		
		parent::display();
	}
	
	
	
	
	
	
	// #####  Interstitials for adding/editing various things  #####
	
}
?>
