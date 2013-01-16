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

/**
 * Utility class for Synchronising Data
 *
 * @static
 * @author		lightinthedark <code@lightinthedark.org.uk>
 * @author    Mike Heaver <m.heaver@wildern.hants.sch.uk>
 * @package		Apotheosis
 * @subpackage	Attendance
 * @since 0.1
 */
class AttSynch
{
	/**
	 * A function to call the appropriate class for interacting with an external application
	 * *** It currently just creates an instance of itself, but should create instances
	 *     of child classes defined by a param. This class should then be abstract
	 *
	 * @return object  Returns an instantiated object
	 */
	function &getInstance()
	{
		/* figure out what our external application is */
//		$extType = 'somehowParse( $param )';
//		eval('$obj = new AttSynch_'.$extType.';');
		$obj = new AttSynch();
		return $obj;
	}
	
	function getConfigFile()
	{
		$items = new JParameter('', JPATH_COMPONENT.DS.'synch.xml');
		return $items;
	}
	
	function getParams()
	{
		return false;
	}
	
	function import_codes( )
	{
		return false;
	}
	
	function import_att_dates()
	{
		return false;
	}
	
	function replaceMarks()
	{
		return false;
	}
	
	function getExternalCodes()
	{
		return array();
	}
	
	function getInternalCodes()
	{
		return array();
	}
	
}
?>
