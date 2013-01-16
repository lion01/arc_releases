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
 * Course Group Factory
 */
class ApothFactory_Course_Group extends ApothFactory
{
	/**
	 * Retrieves the identified tag, creating the object if it didn't already exist
	 * @param $id
	 */
	function &getInstance( $id )
	{
		$r = &$this->_getInstance( $id );
		if( is_null($r) ) {
			$db = &JFactory::getDBO();
			$query = 'SELECT c.*'
				."\n".'FROM '.$db->nameQuote( '#__apoth_cm_courses' ).' AS c'
				."\n".'WHERE c.id = '.$db->Quote( $id );
			$db->setQuery($query);
			$data = $db->loadAssoc();
			
			$r = new ApothGroup( $data );
			$this->_addInstance( $id, $r );
		}
		return $r;
	}
	
	function &getInstances( $requirements, $init = true, $buildTree = false )
	{
		$sId = $this->_getSearchId( $requirements );
		$ids = $this->_getInstances($sId);
		if( is_null($ids) ) {
			$db = &JFactory::getDBO();
			$dbId = $db->nameQuote( 'id' );
			
			$join = $this->getParam( 'restrict' ) ? array( '~LIMITINGJOIN~' ) : array();
			$where = array();
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
				case( 'id' ):
					$where[] = 'c.'.$dbId.$assignPart;
					break;
				
				case( 'root' ):
					$where[] = 'c.'.$dbId.' = c.'.$db->nameQuote( 'parent' );
					break;
				
				case( 'ancestor_of' ):
				case( 'has_descendant' ):
					$dbCA = $db->nameQuote( 'ca' );
					$dbAncestor = $db->nameQuote( 'ancestor' );
					$join[] = 'INNER JOIN '.$db->nameQuote( '#__apoth_cm_courses_ancestry' ).' AS '.$dbCA
						."\n".'   ON '.$dbCA.'.'.$dbAncestor.' = c.'.$dbId;
					$where[] = $dbCA.'.'.$dbId.$assignPart;
					break;
				
				case( 'descendant_of' ):
				case( 'has_ancestor' ):
					$dbCD = $db->nameQuote( 'cd' );
					$dbAncestor = $db->nameQuote( 'ancestor' );
					$join[] = 'INNER JOIN '.$db->nameQuote( '#__apoth_cm_courses_ancestry' ).' AS '.$dbCD
						."\n".'   ON '.$dbCD.'.'.$dbId.' = c.'.$dbId;
					$where[] = $dbCD.'.'.$dbAncestor.$assignPart;
					break;
				
				case( 'parent_of' ):
				case( 'has_child' ):
					$dbCC = $db->nameQuote( 'cc' );
					$dbParent = $db->nameQuote( 'parent' );
					$join[] = 'INNER JOIN '.$db->nameQuote( '#__apoth_cm_courses' ).' AS '.$dbCC
						."\n".'   ON '.$dbCC.'.'.$dbParent.' = c.'.$dbId
						."\n".'  AND '.$dbCC.'.'.$dbId.' != c.'.$dbId;
					$where[] = $dbCC.'.'.$dbId.$assignPart;
					break;
				
				case( 'child_of' ):
				case( 'has_parent' ):
					$dbCP = $db->nameQuote( 'cp' );
					$dbParent = $db->nameQuote( 'parent' );
					$join[] = 'INNER JOIN '.$db->nameQuote( '#__apoth_cm_courses' ).' AS '.$dbCP
						."\n".'   ON '.$dbCP.'.'.$dbId.' = c.'.$dbParent
						."\n".'  AND '.$dbCP.'.'.$dbId.' != c.'.$dbId;
					$where[] = $dbCP.'.'.$dbId.$assignPart;
					break;
				}
			}
			
			$query = 'SELECT DISTINCT c.'.( $init ? '*' : $dbId )
				."\n".'FROM '.$db->nameQuote( '#__apoth_cm_courses' ).' AS c'
				.( empty($join)  ? '' : "\n".implode("\n", $join) )
				.( empty($where) ? '' : "\n WHERE ".implode("\n AND ", $where) )
				."\n".'ORDER BY `fullname` ASC';
			$db->setQuery( $this->getParam( 'restrict' ) ? ApotheosisLibAcl::limitQuery($query, 'assessment.assessments') : $query);
			$data = $db->loadAssocList( 'id' );
//			debugQuery( $db, $data );
			$ids = array_keys( $data );
			$this->_addInstances( $sId, $ids );
			
			if( $init ) {
				$existing = $this->_getInstances();
				$newIds = array_diff( $ids, $existing );
				
				// initialise and cache
				foreach( $newIds as $id ) {
					$r = new ApothGroup( $data[$id] );
					$this->_addInstance( $id, $r );
					unset( $r );
				}
			}
			
//			if( $buildTree ) {
//				$this->_addStructure( $sId, $data, 'id', 'parent' );
//			}
		}
		
		return $ids;
	}
}


/**
 * Course Group Object
 */
class ApothGroup extends JObject
{
	/**
	 * The unique id of the group
	 * @access protected
	 * @var int
	 */
	var $_id = array();
	
	/**
	 * All the data for this group (equates to a row in the db)
	 * @access protected
	 * @var array
	 */
	var $_core = array();
	
	function __construct( $data )
	{
		$this->_id     = $data['id'];
		$this->_core   = $data;
	}
	
	function getId()
	{
		return $this->_id;
	}
	
	function getData()
	{
		return ( is_array($this->_core) ? $this->_core : array() );
	}
	
	function getDatum( $field )
	{
		return ( isset($this->_core[$field]) ? $this->_core[$field] : null );
	}
}
?>