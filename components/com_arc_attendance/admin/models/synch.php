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
 * Attendance Data Import Model - using SIMS' CommandReporter.exe
 *
 * @author     David Swain
 * @package    Arc
 * @subpackage Timetable
 * @since      1.5
 */
class AttendancemanagerModelSynch extends AttendancemanagerModel
{
	/**
	 * Function to retrieve the parameters file
	 *
	 * @return array  Returns an array of parameters from the XML file
	 */
	function &getItems()
	{
		$form = new JParameter('synch_db', JPATH_COMPONENT.DS.'config.xml');
		return $form;
	}
}