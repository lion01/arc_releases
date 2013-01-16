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
require_once( JPATH_COMPONENT_ADMINISTRATOR.DS.'helpers'.DS.'cycles.php' );
require_once( JPATH_COMPONENT_ADMINISTRATOR.DS.'models'.DS.'extension.php' );

/**
 * Extension Manager Summary Model
 *
 * @author		Louis Landry <louis.landry@joomla.org>
 * @package		Joomla
 * @subpackage	Installer
 * @since		1.5
 */
class ReportsModelCycles extends ReportsModel
{
	function getYearGroups()
	{
		// Build Year Group select list
		$db = &JFactory::getDBO();
		$query = 'SELECT '.$db->nameQuote('year')
			."\n".'FROM '.$db->nameQuote('#__apoth_cm_courses')
			."\n".'WHERE '.$db->nameQuote('type').' = '.$db->Quote('pastoral')
			."\n".'  AND '.$db->nameQuote('deleted').' = '.$db->Quote('0')
			."\n".'GROUP BY '.$db->nameQuote('year')
			."\n".'ORDER BY '.$db->nameQuote('year');
		$db->setQuery( $query );
		if (!$db->query())
		{
			$this->setRedirect( 'index.php?option=com_arc_report&view=cycles' );
			return JError::raiseWarning( 500, $db->getErrorMsg() );
		}
		
		$yearGroups = $db->loadObjectList();
		return $yearGroups;
	}
	
	function getReCheckOptions()
	{
		$f = new stdClass();
		$f->recheck = 'first';
		$l = new stdClass();
		$l->recheck = 'last';
		
		return array( $f, $l );
	}
	
	function getCycle()
	{
		$db = &JFactory::getDBO();
		$cycle = current(JRequest::getVar('eid'));
		
		$query = 'SELECT *'
			."\n".' FROM '.$db->nameQuote('#__apoth_rpt_cycles')
			."\n".' WHERE '.$db->nameQuote('id').' = '.$db->quote($cycle);
		$db->setQuery($query);
		
		return $db->loadObject();
	}
	
	/**
	 * Creates a Report Cycle
	 */
	function newCycle( $params = array() )
	{
		if(empty($params)) {
			return false;
		}
		// Get a database connector
		$db =& JFactory::getDBO();
		
		// write the changes, can't use $db->updateObject as key may have changed
		// * may move the id change into an sql up in the first "if", then use the
		// * updateObject function down here, just seems in-efficient.
		$sqlStr = 'INSERT INTO '.$db->nameQuote('#__apoth_rpt_cycles').' ('
			."\n".' '.$db->nameQuote('valid_from').', '
			."\n".' '.$db->nameQuote('valid_to').', '
			."\n".' '.$db->nameQuote('year_group').', '
			."\n".' '.$db->nameQuote('allow_multiple').','
			."\n".' '.$db->nameQuote('rechecker').')'
			."\n".' VALUES ('
			."\n".' '.$db->quote($params['valid_from']).','
			."\n".' '.$db->quote($params['valid_to']).','
			."\n".' '.$db->quote($params['year']).','
			."\n".' '.$db->quote($params['allow_multiple']).','
			."\n".' '.$db->quote($params['recheck']).')';
		
		$db->setQuery($sqlStr);
		$db->query();
		debugQuery($db);
	}
	
	/**
	 * Updates a Report Cycle's details to the db
	 */
	function updateCycle( $params = array() )
	{
		if(empty($params)) {
			return false;
		}
		// Get a database connector
		$db =& JFactory::getDBO();
		
		// write the changes, can't use $db->updateObject as key may have changed
		// * may move the id change into an sql up in the first "if", then use the
		// * updateObject function down here, just seems in-efficient.
		$sqlStr = 'UPDATE '.$db->nameQuote('#__apoth_rpt_cycles').' SET'
			."\n".' '.$db->nameQuote('valid_from').    ' = '.$db->quote($params['valid_from']).','
			."\n".' '.$db->nameQuote('valid_to').      ' = '.$db->quote($params['valid_to']).','
			."\n".' '.$db->nameQuote('year_group').    ' = '.$db->quote($params['year']).','
			."\n".' '.$db->nameQuote('allow_multiple').' = '.$db->quote($params['allow_multiple']).','
			."\n".' '.$db->nameQuote('rechecker').     ' = '.$db->quote($params['recheck'])
			."\n".' WHERE '
			."\n".$db->nameQuote('id').' = '.$db->quote($params['id']);
			
		$db->setQuery($sqlStr);
		$db->query();
		debugQuery($db);
	}
	
	function removeCycle( $eid )
	{
		// Initialize variables
		$db =& JFactory::getDBO();
		
		JArrayHelper::toInteger( $eid );
		
		if( !empty($eid) )
		{
			$query = 'DELETE FROM '.$db->nameQuote('#__apoth_rpt_cycles')
			."\n".' WHERE '.$db->nameQuote('id').' = ' . implode( ' OR '.$db->nameQuote('id').' = ', $eid );
			
			$db->setQuery( $query );
			$retVal = $db->query();
			debugQuery($db, $retVal); // *** needed?
		}
		return $retVal;
	}
}
?>