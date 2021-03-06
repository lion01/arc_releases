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
 * Planner Category Controller
 */
class PlannerControllerCategory extends PlannerController
{
	/**
	 * Displays the standard category list (of current and retired)
	 * by calling the relevant function in the view
	 */
	function display()
	{
		$model = &$this->getModel( 'category' );
		$view  = &$this->getView ( 'category', 'html' );
		$view->setModel( $model );
		$view->link = $this->_getLink();
		
		switch( $scope = JRequest::getWord('scope') ) {
		case( 'xxx' ): // *** dev code
			$view->xxx_view_xxx(); // *** dev code
			break;
		
		default:
			$model->setCategories();
			$view->catList();
		}
		
		$this->saveModel( 'category' );
	}
}
?>