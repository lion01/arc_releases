<?php
/**
 * @package     Arc
 * @subpackage  Course
 * @copyright   Copyright (C) 2005 Punnet. All rights reserved. See COPYRIGHT_ARC.txt
 * @license     http://www.gnu.org/licenses/gpl.html GNU/GPL. See LICENSE_ARC.txt
 * Arc is free software: you can redistribute it and/or modify
 * it under the terms of version 3 of the GNU General Public License
 * as published by the Free Software Foundation.
 * The disclaimer of warranty as stated in the GPL applies to this program
 */

defined( '_JEXEC' ) or die( 'Restricted access' );

/**
 * Utility class for generating arc specific HTML entities
 *
 * @static
 * @package    Arc
 * @subpackage HTML
 * @since      1.5
 */
class JHTMLArc_Course
{
	/**
	 * Retrieve filter options and setup HTML for select input
	 * 
	 * @param string $id  The html name for the input
	 * @param string $attribs  Any other properties to pass directly to input
	 * @param string $default  Default value to select
	 * @return string $filterType  The complete HTML for a filter type select input
	 */
	function filterType( $id = '', $attribs = '', $default = '' )
	{
		// Get the necessaries
		$db = &JFactory::getDBO();
		
		// Build the query
		$query = 'SELECT '.$db->nameQuote('type')
			."\n".' FROM '.$db->nameQuote('#__apoth_cm_types');
		
		// Execute query and build HTML
		$db->setQuery( $query );
		$types = $db->loadObjectList('type');
		$any = new stdClass();
		$any->type = 'any';
		$types[''] = $any;
		ksort( $types );
		$filterType = JHTML::_( 'select.genericlist', $types, $id, $attribs, 'type', 'type', $default );
		
		return $filterType;
	}
	
	/**
	 * Retrieve year options and setup HTML for select input
	 * 
	 * @param string $id  The html name for the input
	 * @param string $attribs  Any other properties to pass directly to input
	 * @param string $default  Default value to select
	 * @return string $years  The complete HTML for a year select input
	 */
	function year( $id = '', $default = '' )
	{
		// Get the necessaries
		$db = &JFactory::getDBO();
		
		// Build the query
		$query = 'SELECT DISTINCT '.$db->nameQuote('year')
			."\n".'FROM '.$db->nameQuote('#__apoth_cm_courses').' AS '.$db->nameQuote('cm')
			."\n".'INNER JOIN '.$db->nameQuote('#__apoth_tt_timetable').' AS '.$db->nameQuote('tt')
			."\n".'   ON '.$db->nameQuote('tt').'.'.$db->nameQuote('course').' = '.$db->nameQuote('cm').'.'.$db->nameQuote('id')
			."\n".'  AND '.$db->nameQuote('cm').'.'.$db->nameQuote('deleted').' = '.$db->Quote('0')
			."\n".'ORDER BY '.$db->nameQuote('year');
		
		// Execute query and build HTML
		$db->setQuery( $query );
		$years = $db->loadObjectList('year');
		$years[''] = '';
		ksort( $years );
		$years = JHTML::_('select.genericList', $years, $id, $attribs , 'year', 'year', $default );
		
		return $years;
	}
}
?>