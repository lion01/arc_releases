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
	
	function groups( $gIds )
	{
		if( empty($gIds ) ) { return array(); }
		$fGroup = ApothFactory::_( 'course.group' );
		$groups = $fGroup->getInstances( array('id'=>$gIds), true );
		return $groups;
	}
	
	function ancestors( $gIds, $limittTo = null )
	{
		if( empty($gIds ) ) { return array(); }
		$requirements = array('ancestor_of'=>$gIds);
		if( !is_null( $limitTo ) ) {
			$requirements['id'] = $limitTo;
		}
		
		$fGroup = ApothFactory::_( 'course.group' );
		$groups = $fGroup->getInstances( $requirements, true );
		return $groups;
	}
	
	function descendants( $gIds, $limitTo = null )
	{
		if( empty($gIds ) ) { return array(); }
		$requirements = array('descendant_of'=>$gIds);
		if( !is_null( $limitTo ) ) {
			$requirements['id'] = $limitTo;
		}
		
		$fGroup = ApothFactory::_( 'course.group' );
		$groups = $fGroup->getInstances( $requirements, true );
		return $groups;
	}
	
	function parents( $gIds )
	{
		if( empty($gIds ) ) { return array(); }
		$fGroup = ApothFactory::_( 'course.group' );
		$groups = $fGroup->getInstances( array('parent_of'=>$gIds), true );
		return $groups;
	}
	
	function children( $gIds, $limitTo = null )
	{
		if( empty($gIds ) ) { return array(); }
		$requirements = array('child_of'=>$gIds);
		if( !is_null( $limitTo ) ) {
			$requirements['id'] = $limitTo;
		}
		
		$fGroup = ApothFactory::_( 'course.group' );
		$groups = $fGroup->getInstances( $requirements, true );
		return $groups;
	}
	
	function subject( $gId )
	{
		return reset( $this->subjects( array( $gId ) ) );
	}
	
	/**
	 * "Subjects" are defined as being the highest level groups below a root node
	 * 
	 * @param array $gIds  The groups whos subjects are sought
	 */
	function subjects( $gIds )
	{
		if( empty($gIds ) ) { return array(); }
		$fGroup = ApothFactory::_( 'course.group' );
		$ancestors = $fGroup->getInstances( array('ancestor_of'=>$gIds), false );
		$roots = $fGroup->getInstances( array('root'=>true, 'ancestor_of'=>$gIds), false );
		$roots = array_intersect( $ancestors, $roots );
		
		return $this->children( $roots, $ancestors );;
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
		if( empty($gId ) ) { return null; }
		$fGroup = ApothFactory::_( 'course.group' );
		$g = $fGroup->getInstance( $gId );
		return $g->getDatum( 'fullname' );
	}
	
	function names( $gIds )
	{
		if( empty($gIds ) ) { return array(); }
		$fGroup = ApothFactory::_( 'course.group' );
		$groups = $fGroup->getInstances( array('id'=>$gIds), true );
		
		$names = array();
		foreach( $groups as $group ) {
			$g = $fGroup->getInstance( $group );
			$names[$group] = $g->getDatum( 'fullname' );
		}
		
		return $names;
	}
	
	function short( $gId )
	{
		$fGroup = ApothFactory::_( 'course.group' );
		$g = $fGroup->getInstance( $gId );
		return $g->getDatum( 'shortname' );
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
}
?>