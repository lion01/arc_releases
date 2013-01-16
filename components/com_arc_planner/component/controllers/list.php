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

/**
 * Planner List Controller
 */
class PlannerControllerList extends PlannerController
{
	/**
	 * Displays the lists (one per category which had tasks found)
	 * by calling the relevant function in the view
	 */
	function display()
	{
		$model = &$this->getModel( 'list' );
		$view  = &$this->getView ( 'list', 'html' );
		$view->setModel( $model );
		$view->link = $this->_getLink();
		$view->taskLink = $this->_getLink( array('option'=>'com_arc_planner', 'view'=>'task') );
		
		switch( $scope = JRequest::getWord('scope') ) {
		case( 'xxx' ): // *** dev code
			$view->xxx_view_xxx(); // *** dev code
			break;
		
		default:
			$view->lists();
		}
		
		$this->saveModel( 'list' );
	}
	
	/**
	 * Uses the data submitted via the search form to set the model's collection of tasks
	 * Then calls display() to show the retrieved tasks
	 */
	function search()
	{
		$taskId = JRequest::getVar( 'taskId', false );
		if( $taskId !== false ) {
			$model = &$this->getModel( 'list' );
//			$model->setTasks( array('taskId'=>$taskId) ); // *** dev code
//			$requirements = array('taskId'=>array(2,3,5,6,7,8,9,10,11,12)); // *** dev code
			$requirements = array('taskId'=>array(21));
			$model->setTasks( $requirements, true );
		}
		
		$this->display();
	}
}
?>