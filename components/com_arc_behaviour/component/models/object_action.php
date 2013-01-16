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
 * Behaviour Action Factory
 */
class ApothFactory_Behaviour_Action extends ApothFactory
{
	function &getDummy()
	{
		$id = -1;
		$r = &$this->_getInstance( $id );
		if( is_null($r) ) {
			$r = new ApothAction( array('id'=>$id, 'label'=>null, 'score'=>null, 'has_text'=>false) );
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
				."\n".'FROM '.$db->nameQuote( '#__apoth_bhv_actions' )
				."\n".'WHERE id = '.$tId;
			$db->setQuery($query);
			$data = $db->loadAssoc();
			
			$r = new ApothAction( $data );
			$this->_addInstance( $id, $r );
		}
		return $r;
	}
	
	function &getInstances( $requirements )
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
				
				case( 'incident' ):
					$join[] = 'INNER JOIN '.$db->nameQuote( '#__apoth_bhv_inc_actions' ).' AS ia'
						."\n".'   ON ia.act_id = a.id'
						."\n".'  AND ia.inc_id'.$assignPart;
					break;
				}
			}
			
			$query = 'SELECT a.*'
				."\n".'FROM '.$db->nameQuote( '#__apoth_bhv_actions' ).' AS a'
				.( empty($join)  ? '' : "\n".implode("\n", $join) )
				.( empty($where) ? '' : "\n WHERE ".implode("\n AND ", $where) );
			$db->setQuery($query);
			$rows = $db->loadAssocList( 'id' );
			$ids = array_keys( $rows );
			
			foreach( $rows as $id=>$data ) {
				$r = new ApothAction( $data );
				$this->_addInstance( $id, $r );
				unset( $r );
			}
		}
		
		$this->_addInstances( $sId, $ids );
		return $ids;
	}
	
	function commitInstance( $id )
	{
		$r = &$this->_getInstance( $id );
		if( is_null($r) ) {
			return false;
		}
		$db = &JFactory::getDBO();
		$id = $r->getId();
		$isNew = ( $id < 0 );
		$dbId = $db->Quote( $id );
		
		if( $isNew ) {
			$query = 'INSERT INTO '.$db->nameQuote( '#__apoth_bhv_actions' );
			$query2 = '';
		}
		else {
			$query = 'UPDATE '.$db->nameQuote( '#__apoth_bhv_actions' );
			$query2 = 'WHERE '.$db->nameQuote( 'id' ).' = '.$dbId;
		}
		
		$s = $r->getScore( false );
		$dbS = is_null($s) ? 'NULL' : $db->Quote( $s );
		
		$query = $query
			."\n".'SET'
			."\n  ".$db->nameQuote( 'label'    ).' = '.$db->Quote( $r->getLabel() )
			."\n, ".$db->nameQuote( 'score'    ).' = '.$dbS
			."\n, ".$db->nameQuote( 'has_text' ).' = '.$db->Quote( (int)$r->getHasText() )
			."\n".$query2;
		$db->setQuery( $query );
		$db->Query();
//		debugQuery( $db );
		if( $isNew ) {
			$id = $db->insertid();
			$dbId = $db->Quote( $id );
		}
		
		// if the core data could not be inserted then go no further and indicate failure
		if( $db->getErrorMsg() != '' ) {
			return false;
		}
		
		// update any incident/action pairings
		$new = $r->getIncidents();
		$cur = $this->loadActionIncidents( $id );
		$del = array_diff( $cur, $new );
		$add = array_diff( $new, $cur );
		if( !empty($del) ) {
			foreach( $del as $k=>$v ) {
				$del[$k] = $db->Quote( $v );
			}
			$query = 'DELETE FROM '.$db->nameQuote( '#__apoth_bhv_inc_actions' )
				."\n".'WHERE '.$db->nameQuote( 'act_id' ).' = '.$dbId
				."\n".'  AND '.$db->nameQuote( 'inc_id' ).' IN ( '.implode( ', ', $del ).' )';
			$db->setQuery( $query );
			$db->Query();
//			debugQuery( $db );
		}
		if( !empty($add) ) {
			foreach( $add as $k=>$v ) {
				$add[$k] = '('.$db->Quote( $v ).', '.$dbId.', NULL )';
			}
			$query = 'INSERT INTO '.$db->nameQuote( '#__apoth_bhv_inc_actions' )
				."\n".'VALUES'
				.implode( "\n, ", $add );
			$db->setQuery( $query );
			$db->Query();
//			debugQuery( $db );
		}
		
		// make the ordering all nice
		$query = 'UPDATE `#__apoth_bhv_inc_actions`'
			."\n".'SET `order` = 99999'
			."\n".'WHERE `order` IS NULL;'
			."\n".''
			."\n".'SET @inc = 0;'
			."\n".'SET @order = 0;'
			."\n".''
			."\n".'CREATE TEMPORARY TABLE tmp_o AS'
			."\n".'SELECT *, @order:=IF(`inc_id` = @inc, @order+1, 1) AS o, @inc:=`inc_id` AS i'
			."\n".'FROM `#__apoth_bhv_inc_actions`'
			."\n".'ORDER BY `inc_id`, `order`;'
			."\n".''
			."\n".'UPDATE tmp_o'
			."\n".'INNER JOIN `#__apoth_bhv_inc_actions` AS act'
			."\n".'   ON act.inc_id = tmp_o.inc_id'
			."\n".'  AND act.act_id = tmp_o.act_id'
			."\n".'SET act.order = tmp_o.o;';
		$db->setQuery( $query );
		$db->queryBatch();
		
		// no errors means successful commit
		return ( $db->getErrorMsg() == '' );
		
	}
	
	function loadActionIncidents( $id )
	{
		$db = &JFactory::getDBO();
		
		$query = 'SELECT inc_id'
			."\n".'FROM '.$db->nameQuote( '#__apoth_bhv_inc_actions')
			."\n".'WHERE '.$db->nameQuote( 'act_id' ).' = '.$db->Quote( $id )
			."\n".'ORDER BY '.$db->nameQuote( 'inc_id' );
		$db->setQuery( $query );
		$actions = $db->loadResultArray();
//		debugQuery( $db, $actions );
		
		return $actions;
	}
}


/**
 * Behaviour Action Object
 */
class ApothAction extends JObject
{
	/**
	 * All the data for this message (equates to a row in the db)
	 * @access protected
	 * @var array
	 */
	var $_core = array();
	
	function __construct( $data )
	{
		$this->_core = $data;
	}
	
	/**
	 * Accessor functions to retrieve core data
	 */
	function getId()       { return $this->_core['id']; }
	function getLabel()    { return $this->_core['label']; }
	function getScore()    { return $this->_core['score']; }
	function getHasText()  { return (bool)$this->_core['has_text']; }
	
	function setLabel( $val )
	{
		$this->_core['label'] = (string)$val;
	}
	
	function setScore( $val )
	{
		$this->_core['score'] = ( empty($val) ? null : (int)$val );
	}
	
	function setHasText( $val )
	{
		$this->_core['has_text'] = (bool)$val;
	}
	
	function _loadIncidents()
	{
		$fAct = ApothFactory::_( 'behaviour.Action' );
		$this->_incidents = $fAct->loadActionIncidents( $this->_core['id'] );
	}
	
	function getIncidents()
	{
		if( !isset($this->_incidents) ) {
			$this->_loadIncidents();
		}
		return $this->_incidents;
	}
	
	function getIncidentLabels()
	{
		if( !isset($this->_incidents) ) {
			$this->_loadIncidents();
		}
		
		$r = array();
		$fInc = ApothFactory::_( 'behaviour.IncidentType' );
		$fInc->getInstances( array('id'=>$this->_incidents) );
		foreach( $this->_incidents as $i ) {
			$tmp = $fInc->getInstance( $i );
			$r[$i] = $tmp->getLabel();
		}
		return $r;
	}
	
	function setIncidents( $ids )
	{
		$this->_incidents = $ids;
	}
	
	function commit()
	{
		$fAct = ApothFactory::_( 'behaviour.Action' );
		return $fAct->commitInstance( $this->_core['id'] );
	}
}
?>