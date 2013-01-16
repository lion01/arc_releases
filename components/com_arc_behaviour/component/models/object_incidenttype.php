<?php
/**
 * @package     Arc
 * @subpackage  Behaviour
 * @copyright   Copyright (C) 2005 Punnet. All rights reserved. See COPYRIGHT_ARC.txt
 * @license     http://www.gnu.org/licenses/gpl.html GNU/GPL. See LICENSE_ARC.txt
 * Arc is free software: you can redistribute it and/or modify
 * it under the terms of version 3 of the GNU General Public License
 * as published by the Free Software Foundation.
 * The disclaimer of warranty as stated in the GPL applies to this program
 */

defined( '_JEXEC' ) or die( 'Restricted access' );

/**
 * Behaviour IncidentType Factory
 */
class ApothFactory_Behaviour_IncidentType extends ApothFactory
{
	function &getDummy()
	{
		$id = -1;
		$r = &$this->_getInstance( $id );
		if( is_null($r) ) {
			$r = new ApothIncidentType( array('id'=>$id, 'parent'=>null, 'label'=>null, 'has_text'=>false, 'score'=>null, 'msg_tag'=>null) );
			$this->_addInstance( $id, $r );
		}
		return $r;
	}
	
	/**
	 * Retrieves the identified incident type, creating the object if it didn't already exist
	 * @param $id
	 */
	function &getInstance( $id )
	{
		$r = &$this->_getInstance( $id );
		if( is_null($r) ) {
			$db = &JFactory::getDBO();
			$tId = $db->Quote( $id );
			$query = 'SELECT *'
				."\n".'FROM '.$db->nameQuote( '#__apoth_bhv_inc_types' )
				."\n".'WHERE id = '.$tId;
			$db->setQuery($query);
			$data = $db->loadAssoc();
			
			$r = new ApothIncidentType( $data );
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
				elseif( is_null($val) ) {
					$assignPart = ' IS NULL';
				}
				else {
					$assignPart = ' = '.$db->Quote( $val );
				}
				switch( $col ) {
				case( 'id' ):
					$where[] = 'id'.$assignPart;
					break;
				
				case( 'parent' ):
					$where[] = 'parent'.$assignPart;
					break;
				
				case( 'root' ):
					$where[] = 'parent '.($val ? '=' : '!=' ).' id';
					break;
				
				case( 'deleted' ):
					$where[] = 'parent IS '.($val ? '' : 'NOT').' NULL';
					break;
				}
			}
			
			$query = 'SELECT *'
				."\n".'FROM '.$db->nameQuote( '#__apoth_bhv_inc_types' )
				.( empty($where) ? '' : "\n WHERE ".implode("\n AND ", $where) );
			$db->setQuery($query);
			$data = $db->loadAssocList( 'id' );
			$ids = array_keys( $data );
			$this->_addInstances( $sId, $ids );
			
			if( $init ) {
				$existing = $this->_getInstances();
				$newIds = array_diff( $ids, $existing );
				
				// initialise and cache
				foreach( $newIds as $id ) {
					$r = new ApothIncidentType( $data[$id] );
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
	
	/**
	 * Commits the instance to the db,
	 * updates the cached instance,
	 * clears the search cache if we've added a new instance
	 *  (the newly created instance may match any of the searches we preveiously executed)
	 * 
	 * @param $id
	 */
	function commitInstance( $id )
	{
		$r = &$this->_getInstance( $id );
		if( is_null($r) ) {
			return false;
		}
		$db = &JFactory::getDBO();
		$id = $r->getId();
		$isNew = ( $id < 0 );
		
		if( $isNew ) {
			$query = 'INSERT INTO '.$db->nameQuote( '#__apoth_bhv_inc_types' );
			$query2 = '';
		}
		else {
			$query = 'UPDATE '.$db->nameQuote( '#__apoth_bhv_inc_types' );
			$query2 = 'WHERE '.$db->nameQuote( 'id' ).' = '.$db->Quote( $id );
		}
		
		$p = $r->getParentId( false );
		$dbP = is_null($p) ? 'NULL' : $db->Quote( $p );
		$s = $r->getScore( false );
		$dbS = is_null($s) ? 'NULL' : $db->Quote( $s );
		$t = $r->getTag( false );
		$dbT = is_null($t) ? 'NULL' : $db->Quote( $t );
		
		$query = $query
			."\n".'SET'
			."\n  ".$db->nameQuote( 'parent'   ).' = '.$dbP
			."\n, ".$db->nameQuote( 'label'    ).' = '.$db->Quote( $r->getLabel() )
			."\n, ".$db->nameQuote( 'score'    ).' = '.$dbS
			."\n, ".$db->nameQuote( 'msg_tag'  ).' = '.$dbT
			."\n, ".$db->nameQuote( 'has_text' ).' = '.$db->Quote( (int)$r->getHasText() )
			."\n".$query2;
		$db->setQuery( $query );
		$db->Query();
//		debugQuery( $db );
		
		// no errors means successful commit
		return ( $db->getErrorMsg() == '' );
	}
}


/**
 * Messaging Message Object
 */
class ApothIncidentType extends JObject
{
	/**
	 * All the data for this message (equates to a row in the db)
	 * @access protected
	 * @var array
	 */
	var $_core = array();
	
	/**
	 * Reference to the parent incident type
	 * @var object(ApothIncidentType) reference
	 */
	var $_parent;
	
	function __construct( $data )
	{
		$this->_core = $data;
	}
	
	/**
	 * Accessor functions to retrieve core data
	 */
	function getId()       { return $this->_core['id']; }
	function getParentId() { return $this->_core['parent']; }
	function getLabel()    { return $this->_core['label']; }
	function getHasText()  { return (bool)$this->_core['has_text']; }
	
	function setParentId( $id )
	{
		$this->_core['parent'] = (int)$id;
		unset( $this->_parent );
	}
	
	function setLabel( $val )
	{
		$this->_core['label'] = (string)$val;
	}
	
	function setHasText( $val )
	{
		$this->_core['has_text'] = (bool)$val;
	}
	
	function getTag( $inherit = true )
	{
		if( $inherit
		 &&  is_null($this->_core['msg_tag'])
		 && !is_null($this->_core['parent'])
		 && ($this->_core['parent'] != $this->_core['id']) ) {
			if( !isset($this->_parent) ) {
				$fInc = ApothFactory::_( 'behaviour.IncidentType' );
				$this->_parent = &$fInc->getInstance( $this->_core['parent'] );
			}
			$r = $this->_parent->getTag();
		}
		else {
			$r = $this->_core['msg_tag'];
		}
		return $r;
	}
	
	function setTag( $tagId )
	{
		$this->_core['msg_tag'] = null;
		if( $tagId != $this->getTag() ) {
			$this->_core['msg_tag'] = (int)$tagId;
		}
	}
	
	function getScore( $inherit = true)
	{
		if( $inherit
		 &&  is_null($this->_core['score'])
		 && !is_null($this->_core['parent'])
		 && ($this->_core['parent'] != $this->_core['id']) ) {
			if( !isset($this->_parent) ) {
				$fInc = ApothFactory::_( 'behaviour.IncidentType' );
				$this->_parent = $fInc->getInstance( $this->_core['parent'] );
			}
			$r = $this->_parent->getScore();
		}
		else {
			$r = $this->_core['score'];
		}
		return $r;
	}
	
	function setScore( $score )
	{
		$this->_core['score'] = null;
		if( $score != $this->getScore() ) {
			$this->_core['score'] = (int)$score;
		}
	}
		
	function hasOwnScore()
	{
		return !( is_null( $this->_core['score'] )
		 && !is_null($this->_core['parent'])
		 && ($this->_core['parent'] != $this->_core['id']) );
	}
	
	/**
	 * Mark the object as deleted and commit
	 */
	function delete()
	{
		$this->_core['parent'] = null;
		unset( $this->_parent );
		return $this->commit();
	}
	
	/**
	 * Trigger saving of the object to the database
	 */
	function commit()
	{
		$fInc = ApothFactory::_( 'behaviour.IncidentType' );
		return $fInc->commitInstance( $this->_core['id'] );
	}
}
?>