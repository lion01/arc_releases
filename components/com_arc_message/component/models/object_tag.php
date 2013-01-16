<?php
/**
 * @package     Arc
 * @subpackage  Message
 * @copyright   Copyright (C) 2005 Punnet. All rights reserved. See COPYRIGHT_ARC.txt
 * @license     http://www.gnu.org/licenses/gpl.html GNU/GPL. See LICENSE_ARC.txt
 * Arc is free software: you can redistribute it and/or modify
 * it under the terms of version 3 of the GNU General Public License
 * as published by the Free Software Foundation.
 * The disclaimer of warranty as stated in the GPL applies to this program
 */

defined( '_JEXEC' ) or die( 'Restricted access' );

/**
 * Messaging Tag Factory
 */
class ApothFactory_Message_Tag extends ApothFactory
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
			$query = 'SELECT mt.*'
				."\n".'FROM '.$db->nameQuote( '#__apoth_msg_tags' ).' AS mt'
				."\n".'WHERE mt.id = '.$db->Quote( $id );
			$db->setQuery($query);
			$data = $db->loadAssoc();
			
			$r = new ApothTag( $data );
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
				case( 'category' ):
					$where[] = 'mt.category'.$assignPart;
					$where[] = 'mt.id != mt.parent'; // exclude categories from list of tags within them
					break;
				
				case( 'folder' ):
					if( is_null($val) ) {
						// no val means all 
						$where[] = 'mt2.id = mt2.parent';
						$where[] = 'mt.category = '.$db->Quote('folder');
					}
					else {
						$where[] = 'mt.parent'.$assignPart;
					}
					$where[] = 'mt.id != mt.parent'; // exclude the "folder" category from list of folders
					break;
				
				case( 'active' ):
					$where[] = 'mt.enabled'.$assignPart;
					break;
				
				case( 'label' ):
					$where[] = 'mt.label'.$assignPart;
					break;
				
				case( 'id' ):
					$where[] = 'mt.id'.$assignPart;
					break;
				}
			}
			
			$query = 'SELECT mt.*'
				."\n".'FROM '.$db->nameQuote( '#__apoth_msg_tags' ).' AS mt'
				."\n".'INNER JOIN '.$db->nameQuote( '#__apoth_msg_tags' ).' AS mt2'
				."\n".'   ON mt2.id = mt.parent'
				.( empty($where) ? '' : "\n WHERE ".implode("\n AND ", $where) )
				."\n".'ORDER BY `order` ASC';
			$db->setQuery($query);
			$data = $db->loadAssocList( 'id' );
			$ids = array_keys( $data );
			$this->_addInstances( $sId, $ids );
			
			if( $init ) {
				$existing = $this->_getInstances();
				$newIds = array_diff( $ids, $existing );
				
				// initialise and cache
				foreach( $newIds as $id ) {
					$r = new ApothTag( $data[$id] );
					$this->_addInstance( $id, $r );
					unset( $r );
				}
			}
			
			if( $buildTree ) {
				$this->_addStructure( $sId, $data, 'id', 'parent' );
			}
		}

		return $ids;
	}
}


/**
 * Messaging Message Object
 */
class ApothTag extends JObject
{
	function __construct( $data )
	{
		$this->_id     = $data['id'];
		$this->_parent = $data['parent'];
		$this->_label  = $data['label'];
		$this->_cat    = $data['category'];
		$this->_order  = $data['order'];
	}
	
	function getId()
	{
		return $this->_id;
	}
	
	function getLabel()
	{
		return $this->_label;
	}
	
	function getCategory()
	{
		return $this->_cat;
	}
	
	/**
	 * Generates an array of the tags in this tag's ancestry
	 */
	function getPath()
	{
		if( !isset($this->_path) ) {
			if( $this->_parent != $this->_id ) {
				$fTags = ApothFactory::_( 'message.Tag' );
				$p = $fTags->getInstance( $this->_parent );
				$t = $p->getPath();
			}
			else {
				$t = array();
			}
			array_push($t, $this->_id);
			$this->_path = $t;
		}
		return $this->_path;
	}
}
?>