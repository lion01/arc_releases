<?php
/**
 * @package     Arc
 * @subpackage  Attendance
 * @copyright   Copyright (C) 2005 Punnet. All rights reserved. See COPYRIGHT_ARC.txt
 * @license     http://www.gnu.org/licenses/gpl.html GNU/GPL. See LICENSE_ARC.txt
 * Arc is free software: you can redistribute it and/or modify
 * it under the terms of version 3 of the GNU General Public License
 * as published by the Free Software Foundation.
 * The disclaimer of warranty as stated in the GPL applies to this program
 */

defined( '_JEXEC' ) or die( 'Restricted access' );

require_once( JPATH_COMPONENT_ADMINISTRATOR.DS.'models'.DS.'extension.php' );

/**
 * Extension Manager Summary Model
 *
 * @author		Louis Landry <louis.landry@joomla.org>
 * @package		Joomla
 * @subpackage	Installer
 * @since		1.5
 */
class AttendancemanagerModelSettings extends AttendancemanagerModel
{
	/** @var array Array of installed components */
	var $_items = array();
	
	/** @var object JTable object */
	var $_table = null;

	/** @var object JTable object */
	var $_url = null;

	/**
	 * Overridden constructor
	 * @access	protected
	 */
	function __construct()
	{
		parent::__construct();
	}
	
	function &getItems()
	{
		$items = new JParameter('', JPATH_COMPONENT.DS.'config.xml');
		return $items;
	}

	function getParams()
	{
		$db = &JFactory::getDBO();
		$db->setQuery('SELECT params FROM #__components WHERE `link` != "" AND `option` = "com_arc_attendance" ');
		$paramsList = $db->loadObject();
		//var_dump($paramsList);
		
		return $paramsList->params;
	}

}