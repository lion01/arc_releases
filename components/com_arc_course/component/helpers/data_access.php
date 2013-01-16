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
 * Data Access Helper
 *
 * @author     lightinthedark <code@lightinthedark.org.uk>
 * @package    Arc
 * @subpackage Course Manager
 * @since      0.1
 */
class ApotheosisCoursesData extends JObject
{
	
	/**
	 * Find the id of the twin group for a pseudo-group (or just give back the original id if not a pseudo)
	 */
	function getTwin( $gId )
	{
		static $checked = array();
		
		if( !isset( $checked[$gId] ) ) {
			$db = &JFactory::getDBO();
			$query = 'SELECT COALESCE( c.id, p.id )'
				."\n".'FROM jos_apoth_cm_courses AS p'
				."\n".'LEFT JOIN jos_apoth_cm_pseudo_map AS m'
				."\n".'  ON p.id = m.`course`'
				."\n".'LEFT JOIN jos_apoth_cm_courses AS c'
				."\n".'  ON c.id = m.twin'
				."\n".' AND c.deleted = 0'
				."\n".'WHERE p.id = '.$db->Quote($gId)
				."\n".' AND p.deleted = 0';
			$db->setQuery($query);
			$checked[$gId] = $db->loadResult();
		}
		return $checked[$gId];
	}
	
}

class ApotheosisData_Course extends ApotheosisData
{
	function info()
	{
		return 'Course component installed';
	}
	
	/**
	 * Find the id of the twin groups for a pseudo-group (or the original ids if not pseudo)
	 * *** Think the whole notion of twinned groups borrowing their enrolments on a real group was a bad one
	 * *** perhaps using twins as an aide for the importer would be better? 
	 * 
	 * @param array $gIds  The (potentially) pseudo courses whose twinned ids are sought
	 * @return array  The twin ids keyed on the given ids
	 */
	function toReal( $gIds )
	{
		static $checked = array();
		$db = &JFactory::getDBO();
		
		foreach( $gIds as $gId ) {
			if( !isset($checked[$gId]) ) {
				$toCheck[] = $db->Quote( $gId );
			}
		}
		
		if( !empty( $toCheck ) ) {
			$query = 'SELECT c.id, COALESCE( m.twin, c.id ) AS '.$db->nameQuote( 'real' )
				."\n".'FROM jos_apoth_cm_courses AS c'
				."\n".'LEFT JOIN jos_apoth_cm_pseudo_map AS m'
				."\n".'  ON m.course = c.id'
				."\n".'WHERE c.id IN ('.implode( ', ', $toCheck ).')';
			$db->setQuery($query);
			$r = $db->loadAssocList();
			foreach( $r as $row ) {
				$checked[$row['id']] = $row['real'];
			}
		}
		
		foreach( $gIds as $gId ) {
			$retVal[$gId] = $checked[$gId];
		}
		
		return $retVal;
	}
	
	/**
	 * Find any / all pseudo courses which are twinned onto the given real courses
	 * See note on "twin" function regarding sense of this system
	 * 
	 * @param array $gIds  The (potentially) basis courses whose pseudo group ids are sought
	 * @return array  The indexed pseudo id arrays keyed on the given ids
	 */
	function toPseudo( $gIds )
	{
		static $checked = array();
		$db = &JFactory::getDBO();
		
		foreach( $gIds as $gId ) {
			if( !isset($checked[$gId]) ) {
				$toCheck[] = $db->Quote( $gId );
				$checked[$gId] = array();
			}
		}
		
		if( !empty( $toCheck ) ) {
			$query = 'SELECT m.course, m.twin'
				."\n".'FROM jos_apoth_cm_pseudo_map AS m'
				."\n".'WHERE m.twin IN ('.implode( ', ', $toCheck ).')';
			$db->setQuery($query);
			$r = $db->loadAssocList();
			foreach( $r as $row ) {
				$checked[$row['twin']][] = $row['course'];
			}
		}
		
		foreach( $gIds as $gId ) {
			$retVal[$gId] = $checked[$gId];
		}
		
		return $retVal;
	}
	
	function name( $gId )
	{
		if( !isset($this->_name[$gId]) ) {
			$db = &JFactory::getDBO();
			$query = 'SELECT fullname'
				."\n".'FROM #__apoth_cm_courses'
				."\n".'WHERE id = '.$db->Quote($gId);
			$db->setQuery($query);
			
			$this->_name[$gId] = $db->loadResult();
		}
		
		return $this->_name[$gId];
	}
	
	function names( $gIds )
	{
		if( is_array($gIds) && !empty($gIds) ) {
			$db = &JFactory::getDBO();
			foreach( $gIds as $k=>$gId ) {
				$gIds[$k] = $db->Quote( $gId );
			}
			$gIds = implode( ',', $gIds );
			
			$query = 'SELECT id, fullname'
				."\n".'FROM #__apoth_cm_courses'
				."\n".'WHERE id IN ('.$gIds.')';
			$db->setQuery( $query );
			$this->_name = $db->loadAssocList( 'id' );
			
			if( !is_array($this->_name) ) {
				$this->_name = array();
			}
			
			foreach( $this->_name as $gId=>$array ) {
				$this->_name[$gId] = $array['fullname'];
			}
		}
		
		return $this->_name;
	}
	
	function short( $gId )
	{
		static $checked = array();
		
		if( !isset( $checked[$gId] ) ) {
			$db = &JFactory::getDBO();
			$query = 'SELECT shortname'
				."\n".' FROM jos_apoth_cm_courses'
				."\n".' WHERE id = '.$db->Quote($gId);
			$db->setQuery($query);
			$checked[$gId] = $db->loadResult();
		}
		return $checked[$gId];
	}
	
	function type( $gId )
	{
		static $checked = array();
		
		if( !isset( $checked[$gId] ) ) {
			$db = &JFactory::getDBO();
			$query = 'SELECT IF( m.course IS NULL , c.type, '.$db->Quote('pastoral').' )'
				."\n".'FROM jos_apoth_cm_courses AS c'
				."\n".'LEFT JOIN `jos_apoth_cm_pastoral_map` AS m'
				."\n".'  ON m.course = c.id'
				."\n".' WHERE c.id = '.$db->Quote($gId);
			$db->setQuery($query);
			$checked[$gId] = $db->loadResult();
		}
		return $checked[$gId];
	}
	
	function subject( $gId )
	{
		static $checked = array();
		
		if( !isset( $checked[$gId] ) ) {
			$db = &JFactory::getDBO();
			$query = 'SELECT parent'
				."\n".' FROM jos_apoth_cm_courses'
				."\n".' WHERE id = '.$db->Quote($gId);
			$db->setQuery($query);
			$checked[$gId] = $db->loadResult();
		}
		return $checked[$gId];
	}
	
	function descendants( $gId, $from = null, $to = null )
	{
		if( is_null($from) ) { $from = date('Y-m-d H:i:s'); }
		if( is_null($to)   ) { $to   = date('Y-m-d H:i:s'); }
		static $checked = array();
		$db = &JFactory::getDBO();
		
		if( is_array($gId) ) {
			foreach( $gId as $k=>$v ) {
				$gId[$k] = $db->Quote( $v );
			}
			$assignPart = ' IN ('.implode( ', ', $gId ).')';
			$ident = md5(implode('', $gId));
		}
		else {
			$assignPart = ' = '.$db->Quote( $gId );
			$ident = $gId;
		}
		if( !isset( $checked[$ident] ) ) {
			$query = 'SELECT DISTINCT ca.id'
				."\n".'FROM jos_apoth_cm_courses_ancestry AS ca'
				."\n".'INNER JOIN jos_apoth_cm_courses AS c'
				."\n".'   ON c.id = ca.id'
				."\n".'~LIMITINGJOIN~'
				."\n".'WHERE ca.ancestor '.$assignPart
				."\n".'  AND '.ApotheosisLibDb::dateCheckSql( 'c.start_date', 'c.end_date', $from, $to );
			$db->setQuery( ApotheosisLibAcl::limitQuery($query, 'timetable.groups', 'ca') );
			$checked[$ident] = $db->loadResultArray();
		}
		return $checked[$ident];
	}
}
?>