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

/**
 * TV Admin Server Controller
 *
 * @author     Punnet - Arc Team
 * @package    Arc
 * @subpackage TV
 * @since      1.6
 */
class TvAdminControllerServer extends TVAdminController
{
	/**
	 * Default method
	 */
	function display()
	{
		$model = &$this->getModel( 'server' );
		$view = &$this->getView( 'server', 'html' );
		$view->setModel( $model, true );
		
		$view->display();
	}
	
	/**
	 * Save changes to video server parameters
	 */
	function save()
	{
		global $mainframe;
		$model = &$this->getModel( 'server' );
		$view = &$this->getView( 'server', 'html' );
		$view->setModel( $model, true );
	
		// Retrieve data for video server configuration
		$data = JRequest::getVar( 'params' );
		
		// Get the current params and update 
		$params = &$model->getParams();
		foreach( $data as $name=>$value ) {
			$params->set( $name, $value );
		}
		
		// Save the configuration data
		$save = $model->saveParams();
	
		if( $save ) {
			$mainframe->enqueueMessage( 'Settings were successfully saved' );
		}
		else {
			$mainframe->enqueueMessage( 'There was a problem saving the settings, please try again', 'error' );
		}
		
		$this->display();
	}
}
?>