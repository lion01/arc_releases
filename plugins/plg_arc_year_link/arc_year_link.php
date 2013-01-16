<?php
/**
 * @package     Arc
 * @subpackage  Plugin_Year_Link
 * @copyright   Copyright (C) 2005 Punnet. All rights reserved. See COPYRIGHT_ARC.txt
 * @license     http://www.gnu.org/licenses/gpl.html GNU/GPL. See LICENSE_ARC.txt
 * Arc is free software: you can redistribute it and/or modify
 * it under the terms of version 3 of the GNU General Public License
 * as published by the Free Software Foundation.
 * The disclaimer of warranty as stated in the GPL applies to this program
 */

defined('_JEXEC') or die('Restricted access');

jimport('joomla.application.menu');
jimport( 'joomla.plugin.plugin' );
require_once( JPATH_BASE.DS.'components'.DS.'com_arc_core'.DS.'libraries'.DS.'apoth_library.php' );

class plgSystemArc_Year_Link extends JPlugin
{

	var $_db = null;

	/**
	 * Constructor
	 *
	 * For php4 compatability we must not use the __constructor as a constructor for plugins
	 * because func_get_args ( void ) returns a copy of all passed arguments NOT references.
	 * This causes problems with cross-referencing necessary for the observer design pattern.
	 *
	 * @access	protected
	 * @param	object	$subject The object to observe
	 * @param 	array   $config  An array that holds the plugin configuration
	 * @since	1.0
	 */
	function plgSystemArc_Year_Link(& $subject, $config)
	{
		$this->_db = JFactory::getDBO();
		parent :: __construct($subject, $config);
	}


	function onAfterInitialise()
	{
		global $mainframe;
		if ($mainframe->isAdmin()) {
			return; // Dont run in admin
		}
		
		$trigger = $this->params->get( 'trigger', false );
		
		// Check we're not redirecting to ourselves
		// don't redirect if we're already where we need to be, or aren't at the trigger location
		if( ($trigger == false)
		 || (!stristr($_SERVER['REQUEST_URI'], $trigger) && !stristr($_SERVER['SCRIPT_NAME'].'/'.$_SERVER['QUERY_STRING'], $trigger)) ) {
			return;
		}
		else {
			$u = JFactory::getUser();
			$year = ApotheosisLib::getUserYear( $u->id );
			$msg = $this->params->get( 'message', false );
			$url = $this->params->get( 'target'.$year, false );
			
			if( !is_numeric($year) ) {
				$year = 'default';
				$msg = false;
			}
			
			if( ($url == false) || ($trigger == $url)
			 || (stristr($_SERVER['REQUEST_URI'], $url) !== false) || (stristr($_SERVER['SCRIPT_NAME'].'/'.$_SERVER['QUERY_STRING'], $url) !== false) ) {
				return;
			}
			else {
				if( $msg !== false ) {
					$mainframe->enqueueMessage($msg);
				}
				$mainframe->redirect( JURI::base().$url );
			}
		}
	}
}