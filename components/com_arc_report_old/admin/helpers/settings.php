<?php
/**
 * @package     Arc
 * @subpackage  Report
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
 * @subpackage	Reports
 * @since 0.1
 */
  
class ReportsSettings
{
	/**
	 * A function to call the appropriate class for interacting with an external database
	 *
	 * @return object  Returns an instantiated object
	 */
	function &getInstance()
	{
		/* figure out what our external application is */
		$extType = 'SIMS';
		eval('$obj = new ReportsSettings_'.$extType.';');
		return $obj;
	}
	
	function getParams()
	{
		return false;
	}
		
}

class ReportsSettings_SIMS extends ReportsSettings
{
	
	/**
	 * Gets the parameters for this component
	 *
	 * @return object Returns a JParameter object
	 */
	function getParams()
	{
		$paramsList = JComponentHelper::getParams('com_arc_report');
		
		return $paramsList;
	}
	
	function getCycles()
	{
		$db = &JFactory::getDBO();
		$date = date('Y-m-d');
		$query = 'SELECT id, CONCAT(\'Year: \', `year_group`, \' ( \', valid_from, \' ) \') AS display'
		 ."\n".' FROM `#__apoth_rpt_cycles`'
		 ."\n".' ORDER BY `id`';
		 $db->setQuery($query);
		 $cycles = $db->loadObjectList();

		 return $cycles;
	}
	
	function getMergeWords()
	{
		$db = &JFactory::getDBO();
		$query = 'SELECT `id`, `word` as display'
		 ."\n".' FROM `#__apoth_rpt_merge_words`'
		 ."\n".' ORDER BY `id`';
		 $db->setQuery($query);
		 $words = $db->loadObjectList();
		 
		 return $words;
	}
}
?>
