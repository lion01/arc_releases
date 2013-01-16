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
 * Planner Controller Admin
 *
 * @author		lightinthedark <code@lightinthedark.org.uk>
 * @package		Apotheosis
 * @subpackage	Reports
 * @since 0.1
 */
class PlannerControllerAdmin extends PlannerController
{
	/**
	 * Constructor
	 */
	function __construct( $config = array() )
	{
		parent::__construct( $config );
		// Register Extra tasks
	}

	/**
	 * Display main form
	 */	
	function display()
	{
		$model = &$this->getModel( 'admin' );
		$view =  &$this->getView( 'admin', 'html' );
		
		$view->setModel( $model );
		$view->display();
	}

	/**
	 * Save assignments
	 */	
	function save()
	{
		
	}

	/**
	 * Upload group assignments csv
	 */	
	function upload()
	{
		
	}

	/**
	 * Clarify people where id not found
	 */	
	function clarifyUpload()
	{
		
	}
}