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
 * Planner Task View
 */
class PlannerViewTask extends JView
{
	/**
	 * Shows the main page containing all the tasks and their subtasks
	 * Gets the task trees from the model and puts each under its own category heading
	 */
	function tasks()
	{
		$this->model = &$this->getModel( 'task' );
		
		$this->_curTaskId = null;
		
		$cats =& $this->model->getCategories();
		if( !empty($cats) ) {
			reset($cats);
			$cat = key($cats);
			$tt = $this->model->getTopTasks( $cat );
			if( !empty($tt) ) {
				$this->_curTaskId = reset($tt);
			}
		}
		
		$this->setLayout( 'ajax' );
		parent::display();
	}
	
	
	
	
	
	
}
?>
