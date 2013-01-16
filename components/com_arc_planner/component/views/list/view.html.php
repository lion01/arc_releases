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
 * Planner List View
 */
class PlannerViewList extends JView
{
	function __construct()
	{
		$document = &JFactory::getDocument();
		$document->setTitle( JText::_( 'Arc Planner Lists' ) );
		
		parent::__construct();
	}
	
	/**
	 * Shows the main page containing all the matched tasks 
	 * Gets the task lists from the model then displays them for each category
	 */
	function lists()
	{
		$this->model = &$this->getModel( 'list' );
		
		parent::display();
	}
	
	function _getTaskLink( $taskId, $propFunc = 'getTitle' )
	{
		$task = &$this->model->getTask($taskId);
		$imgPath = '.'.DS.'components'.DS.'com_arc_planner'.DS.'images'.DS.'link.png';
		
		return '<a href="'.$this->taskLink.'&task=search&taskId='.$taskId.'&passthrough=auto"> 
			<img src="'.$imgPath.'" title="Open this task in a new window" />
			</a>
			<a
			class="modal"
			rel="{handler: \'iframe\', size: {x: 640, y: 480}}"
			href="'.$this->taskLink.'&task=search&taskId='.$taskId.'&tmpl=component&modal=true&passthrough=auto" title="View this task">
			'.$task->$propFunc().'
			</a>';
	}
	
	
	
	
	
	
	
	
	
	
	// #####  Interstitials for adding/editing various things  #####
	
}
?>
