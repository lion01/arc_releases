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

class ApothReportLib
{
	function getBulletText()
	{
		static $bullet;
		
		if( !isset($bullet) ) {
			$paramsObj = &JComponentHelper::getParams('com_arc_attendance');
			$bulletId = $paramsObj->get( 'bullet_id', 1 );
			$db = &JFactory::getDBO();
			$query = 'SELECT neuter'
				."\n".' FROM #__apoth_rpt_merge_words'
				."\n".' WHERE id = '.$db->Quote($bulletId);
			$db->setQuery($query);
			$bullet = $db->loadResult();
		}
		
		return $bullet;
	}
	
	/**
	 * Get a TCPDF object
	 *
	 * @return object  Returns a reference to the global TCPDF object,
	 *                 only creating it if it doesn't already exist
	 */
	function &getPDF()
	{
		jimport('tcpdf.tcpdf');
		
		static $instance;
		
		if( !is_object($instance) ) {
			$instance = new TCPDF();
		}
		
		return $instance;
	}
	
	/**
	 * Get a cycle object
	 *
	 * @return object  Returns a reference to the cycle object with the given id,
	 *                 only creating it if it doesn't already exist
	 */
	function &getCycle( $id )
	{
		if( !is_integer($id) && !is_string($id) ) { $id = -1; }
		static $cycles = array();
		
		if( !isset($cycles[$id]) ) {
			$db = &JFactory::getDBO();
			if( $id == -1 ) {
				$db->setQuery( 'SELECT *'
					."\n".' FROM #__apoth_rpt_cycles'
					."\n".' WHERE '.ApotheosisLibDb::dateCheckSQL( 'valid_from', 'valid_to', date('Y-m-d H:i:s'), date('Y-m-d H:i:s'))
					."\n".' ORDER BY '.$db->nameQuote('valid_from')
					."\n".' LIMIT 1' );
			}
			else {
				$db->setQuery( 'SELECT *'
					."\n".' FROM #__apoth_rpt_cycles'
					."\n".' WHERE id = '.$db->Quote($id) );
			}
			$cycle = $db->loadObject();
			
			$db->setQuery( 'SELECT '
				."\n".' '.$db->nameQuote('group')
				."\n".' FROM '.$db->nameQuote('#__apoth_rpt_cycles_groups')
				."\n".' WHERE '.$db->nameQuote('cycle').' = '.$db->Quote($cycle->id) );
			$cycle->groups = $db->loadResultArray();
			
			if( !is_array($cycle->groups) ) {
				$cycle->groups = array();
			}
			
			$tmp = array();
			foreach($cycle->groups as $v) {
				$tmp[] = $db->Quote($v);
			}
			$cycle->groupsList = implode(', ', $tmp);
			
			$cycles[$id] = $cycle;
		}
		
		return $cycles[$id];
	}
	
}
?>
