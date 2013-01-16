<?php
/**
 * @package     Arc
 * @subpackage  Plugin_Log
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

class plgSystemArc_log extends JPlugin
{
	/**
	 * Constructor
	 *
	 * For php4 compatability we must not use the __constructor as a constructor for
	 * plugins because func_get_args ( void ) returns a copy of all passed arguments
	 * NOT references.  This causes problems with cross-referencing necessary for the
	 * observer design pattern.
	 */
	function plgSystemArc_log( &$subject )
	{
		parent::__construct( $subject, false );
		
		// load plugin parameters
		$this->_plugin = JPluginHelper::getPlugin( 'system', 'Arc_log' );
	}
	
	/**
	 * Plugin methods with the same name as the event will be called automatically.
	 */
	
	/**
	 * Includes the code to operate Arc System Log
	 */
	function onAfterInitialise()
	{
		$db = &JFactory::getDBO();
		$user = &JFactory::getUser();
		ob_start();
		var_dump($_GET);
		$gdata = ob_get_clean();
		ob_start();
		var_dump($_POST);
		$pdata = ob_get_clean();
		$ip = $_SERVER['REMOTE_ADDR'];
		$query = 'INSERT INTO `#__apoth_sys_log` ( `j_userid`, `ip_add`, `action_time`, `url`, `get_data`, `post_data`)'
			."\n".'VALUES'
			."\n".'('.$db->Quote($user->id).', '.$db->Quote($ip).', '.$db->Quote(date('Y-m-d H:i:s')).', '.$db->Quote($_SERVER['REQUEST_URI']).', '.$db->Quote($gdata).', '.$db->Quote($pdata).' )';
		$db->setQuery( $query );
		$db->query();
	}
}
?>