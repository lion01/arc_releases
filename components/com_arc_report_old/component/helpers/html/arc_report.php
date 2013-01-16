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
 * Utility class for generating arc report specific HTML entities
 *
 * @static
 * @package    Arc
 * @subpackage Report
 * @since      1.5
 */
class JHTMLArc_Report
{

	/**
	 * Generate HTML to display a cycle select box with the given name
	 *
	 * @param string $name  The name to use for the input
	 * @param string $default  Default input value
	 * @param boolean $multiple  Allow multiple selects?
	 * @return string $retVal  The HTML to display the required input
	 */
	function cycles( $name, $default = null, $multiple = false )
	{
		// get current cycle from component params
		$paramsObj = &JComponentHelper::getParams('com_arc_report');
		$default = ( !is_null($default) ? $default : $paramsObj->get('current_cycle', false) );
		$oldVal = JRequest::getVar( $name, $default );
		
		$db = &JFactory::getDBO();
		$query = 'SELECT id, valid_from, valid_to, year_group'
			."\n".' FROM #__apoth_rpt_cycles AS c'
			."\n".' ORDER BY valid_from DESC, year_group ASC';
		$db->setQuery( $query );
		$cycles = $db->loadObjectList('id');
		if( !is_array($cycles) ) { $cycles = array(); }
		
		$format = 'Year %4$s (%2$s - %3$s)';
		foreach( $cycles as $key=>$row ) {
			$cycles[$key]->text = sprintf( $format, $row->id, $row->valid_from, $row->valid_to, $row->year_group);
		}
		
		$attribs = ( $multiple ? 'multiple="multiple" class="multi_medium"' : '' );
		$name = ( $multiple ? $name.'[]' : $name );
		
		$retVal =  JHTML::_( 'select.genericList', $cycles, $name, $attribs , 'id', 'text', $oldVal );
		$retVal .= JHTML::_( 'arc.hidden', 'search_default_'.$name, $default, 'class="search_default"' );
		
		return $retVal;
	}

}
?>