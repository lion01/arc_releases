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
 * Data Access Helper
 *
 * @author     Punnet - Arc Team <arc_developers@pun.net>
 * @package    Arc
 * @subpackage Report
 * @since      1.8
 */
class ApotheosisData_Report extends ApotheosisData
{
	function info()
	{
		return 'Report component installed';
	}
	
	/**
	 * Find the group ids according to requirements
	 * 
	 * @param array $requirements  The requirements as an associative array of col=>val pairs
	 */
	function groups( $requirements )
	{
		if( !array($requirements) || empty($requirements) ) {
			return array();
		}
		
		$db = &JFactory::getDBO();
		$dbC = $db->nameQuote( 'c' );
		$where = array();
		$join = array();
		$requirements['type'] = 'report';
		foreach( $requirements as $col=>$val ) {
			if( is_array($val) ) {
				if( empty($val) ) {
					continue;
				}
				foreach( $val as $k=>$v ) {
					$val[$k] = $db->Quote( $v );
				}
				$assignPart = ' IN ('.implode( ', ',$val ).')';
			}
			else {
				$assignPart = ' = '.$db->Quote( $val );
			}
			switch( $col ) {
			case( 'type' ):
				$where[] = $dbC.'.'.$db->nameQuote( 'type' ).$assignPart;
				break;
			
			case( 'cycle' ):
				$where[] = $dbC.'.'.$db->nameQuote( 'reportable' ).$assignPart;
				break;
			}
		}
		
		$query = 'SELECT DISTINCT '.$dbC.'.'.$db->nameQuote('id')
			."\n".'FROM '.$db->nameQuote('#__apoth_cm_courses').' AS '.$dbC
			.( empty($where) ? '' : "\nWHERE ".implode("\n  AND ", $where) );
		
		$db->setQuery( $query );
		$r = $db->loadResultArray();
		
		return $r;
	}
	
}
?>