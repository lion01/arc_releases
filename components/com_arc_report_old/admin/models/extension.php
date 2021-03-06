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

jimport( 'joomla.application.component.model' );
jimport( 'joomla.installer.installer' );

/**
 * Extension Manager Summary Model
 *
 * @author		Mike Heaver <m.heaver@wildern.hants.sch.uk>
 * @package		Joomla
 * @subpackage	Installer
 * @since		1.5
 */
class ReportsModel extends JModel
{
	/**
	 * Saves the parameters to the database (com_arc_reportmanager only)
	 *
	 * @param array  Array containing the different settings
	 */
	function saveParams($newParams)
	{
		$db = &JFactory::getDBO();
		$params = JComponentHelper::getParams('com_arc_report');
		$paramsArr = $params->getParams('_default', '');
		// **** Needs a better way to pull out these params
		$paramsArr = get_object_vars($params->_registry['_default']['data']);
		//*/
		//var_dump_pre($paramsArr);
		
		//Merge the two associative arrays
		$updatedParams = array_merge($paramsArr, $newParams);
		
		//Create the params string
		$paramStr = '';
		foreach ($updatedParams as $key=>$value) {
			$paramStr .= $db->getEscaped($key).'='.$db->getEscaped($value)."\n";
		}
		//SQL to update the field in the database
		$db->setQuery(' UPDATE `#__components` SET `params` = "'.$paramStr.'" WHERE `link` != "" AND `option` = "com_arc_report" ');
		$db->query();
		//echo $db->getQuery();
	}
	
	function &getCycles()
	{
		if (empty($this->_cycles)) {
			// Load the items
			$this->_loadCycles();
		}
		return $this->_cycles;
	}
	
	function _loadCycles()
	{
		/* Get a database connector */
		$db =& JFactory::getDBO();
		
		$query = 'SELECT *'
			."\n".' FROM '.$db->nameQuote('#__apoth_rpt_cycles')
			."\n".' ORDER BY '.$db->nameQuote('valid_from').' DESC, '.$db->nameQuote('year_group');
		$db->setQuery($query);
		$rows = $db->loadObjectList();
		
		$numRows = count($rows);
		
		$this->setState('pagination.total', $numRows);
		$this->_cycles = $rows;
	}
}
?>