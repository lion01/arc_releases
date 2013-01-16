<?php
/**
 * @package     Arc
 * @subpackage  Plugin_Subdomains
 * @copyright   Copyright (C) 2005 Punnet. All rights reserved. See COPYRIGHT_ARC.txt
 * @license     http://www.gnu.org/licenses/gpl.html GNU/GPL. See LICENSE_ARC.txt
 * Arc is free software: you can redistribute it and/or modify
 * it under the terms of version 3 of the GNU General Public License
 * as published by the Free Software Foundation.
 * The disclaimer of warranty as stated in the GPL applies to this program
 */

defined( '_JEXEC' ) or die( 'Restricted access' );

// Import library dependencies
jimport('joomla.event.plugin');

class plgSystemArc_subdomains extends JPlugin
{
	/**
	 * Constructor
	 *
	 * For php4 compatability we must not use the __constructor as a constructor for
	 * plugins because func_get_args ( void ) returns a copy of all passed arguments
	 * NOT references.  This causes problems with cross-referencing necessary for the
	 * observer design pattern.
	 */
	function plgSystemArc_subdomains( &$subject )
	{
		parent::__construct( $subject, false );
		
		// load plugin parameters
		$this->_plugin = JPluginHelper::getPlugin( 'system', 'Arc_subdomains' );
	}
	
	/**
	 * Plugin methods with the same name as the event will be called automatically.
	 */
	
	/**
	 * Includes the code to ensure logins work across subdomains
	 * Currently specific to Wildern but could be expanded to allow configuration
	 * **** Wildern-specific ****
	 */
	function onAfterInitialise()
	{
		if( strpos(JURI::base(), 'wildern') !== false ) {
			ini_set('session.cookie_domain', '.wildern.hants.sch.uk');
		}
	}
}
?>