<?php
/**
 * @package     Arc
 * @subpackage  Core
 * @copyright   Copyright (C) 2005 Punnet. All rights reserved. See COPYRIGHT_ARC.txt
 * @license     http://www.gnu.org/licenses/gpl.html GNU/GPL. See LICENSE_ARC.txt
 * Arc is free software: you can redistribute it and/or modify
 * it under the terms of version 3 of the GNU General Public License
 * as published by the Free Software Foundation.
 * The disclaimer of warranty as stated in the GPL applies to this program
 */

defined( '_JEXEC' ) or die( 'Restricted access' );

/**
 * Core Admin Settings Controller
 *
 * @author     Punnet - Arc Team
 * @package    Arc
 * @subpackage Core
 * @since      1.6
 */
class CoreAdminControllerSettings extends CoreAdminController
{
	function save()
	{
		global $mainframe;
		$model = &$this->getModel( 'settings' );
		$view = &$this->getView( 'settings', 'html' );
		$view->setModel( $model, true );
		
		// Retrieve data for twitter configuration 
		$data = JRequest::getVar( 'params' );
		
		// Save the configuration data
		$save = $model->saveParams( $data );
		
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