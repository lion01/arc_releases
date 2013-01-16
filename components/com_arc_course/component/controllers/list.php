<?php
/**
 * @package     Arc
 * @subpackage  Course
 * @copyright   Copyright (C) 2005 Punnet. All rights reserved. See COPYRIGHT_ARC.txt
 * @license     http://www.gnu.org/licenses/gpl.html GNU/GPL. See LICENSE_ARC.txt
 * Arc is free software: you can redistribute it and/or modify
 * it under the terms of version 3 of the GNU General Public License
 * as published by the Free Software Foundation.
 * The disclaimer of warranty as stated in the GPL applies to this program
 */

defined( '_JEXEC' ) or die( 'Restricted access' );

jimport( 'joomla.application.helper' );

/**
 * Course Manager List Controller
 *
 * @author     lightinthedark <code@lightinthedark.org.uk>
 * @package    Arc
 * @subpackage Course Manager
 * @since      0.1
 */
class CourseControllerList extends CourseController
{
	/**
	 * Default action.
	 * Calls appropriate display function
	 */
	function display()
	{
		$model = &$this->getModel( 'list' );
		$view =  &$this->getView( 'list', 'html' );
		
		$view->setModel( $model );
		$view->display();
	}
}
?>