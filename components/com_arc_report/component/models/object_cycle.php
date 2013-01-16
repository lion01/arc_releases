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
 * Report Cycle Factory
 *
 * @author     Punnet - Arc Team <arc_developers@pun.net>
 * @package    Arc
 * @subpackage Report
 * @since      1.8
 */
class ApothFactory_Report_Cycle extends ApothFactory
{
	function &getDummy( $id )
	{
		if( $id >= 0 ) {
			$r = null;
			return $r;
		}
		$r = &$this->_getInstance( $id );
		if( is_null($r) ) {
			$r = new ApothReportCycle( array('id'=>$id,
				'name'=>'New Cycle',
				'active_from'=>date( 'Y-m-d H:i:s' ),
				'active_to'=>date( 'Y-m-d H:i:s' ),
				'layout_id'=>0) );
			$this->_addInstance( $id, $r );
		}
		return $r;
	}
	
	/**
	 * Retrieves the identified cycle, creating the object if it didn't already exist
	 * @param $id
	 */
	function &getInstance( $id )
	{
		$r = &$this->_getInstance( $id );
		if( is_null($r) ) {
			$db = &JFactory::getDBO();
			$query = 'SELECT c.*'
				."\n".'FROM '.$db->nameQuote( '#__apoth_rpt_cycles' ).' AS c'
				."\n".'WHERE c.id = '.$db->Quote( $id );
			$db->setQuery($query);
			$data = $db->loadAssoc();
			
			$r = new ApothReportCycle( $data );
			$this->_addInstance( $id, $r );
		}
		return $r;
	}
	
	function &getInstances( $requirements, $init = true )
	{
		$sId = $this->_getSearchId( $requirements );
		$ids = $this->_getInstances($sId);
		$restrict = $this->getParam( 'restrict' );
		$now = date( 'Y-m-d H:i:s' );
		
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
				case( 'valid_from' ):
				case( 'valid_to' ):
					if( !isset($where['date']) ) {
						$where['date'] = ApotheosisLibDb::dateCheckSql( 'c.active_from', 'c.active_to', $requirements['valid_from'], $requirements['valid_to'] );
					}
					break;
				
				case( 'active' ):
					$where['date'] = ApotheosisLibDb::dateCheckSql( 'c.active_from', 'c.active_to', $now, $now );
					break;
					
				case( 'id' ):
					$where[] = 'c.id'.$assignPart;
					break;
				}
			}
			
			$query = 'SELECT DISTINCT c.*'
				."\n".'FROM '.$db->nameQuote( '#__apoth_rpt_cycles' ).' AS c'
				.( empty($where) ? '' : "\nWHERE ".implode("\n AND ", $where) )
				."\n".'ORDER BY c.active_to, c.active_from';
			
			$db->setQuery( $restrict ? ApotheosisLibAcl::limitQuery($query, 'report.cycles') : $query );
			$data = $db->loadAssocList( 'id' );
			
			$ids = array_keys( $data );
			$this->_addInstances( $sId, $ids );
			
			if( $init ) {
				$existing = $this->_getInstances();
				$newIds = array_diff( $ids, $existing );
				
				if( !empty($newIds) ) {
					// initialise and cache
					foreach( $newIds as $id ) {
						$objData = $data[$id];
						$obj = new ApothReportCycle( $objData );
						$this->_addInstance( $id, $obj );
						unset( $obj );
					}
				}
			}
		}
		
		return $ids;
	}
	
	
	function commitInstance( $id )
	{
	}
	
}


/**
 * Report Cycle Object
 */
class ApothReportCycle extends JObject
{
	function __construct( $data )
	{
		$this->_id     = $data['id'];
		$this->_core   = $data;
	}
	
	// #####  accessors  #####
	function getId()          { return $this->_id; }
	
	function getDatum( $key ) {
		return ( isset($this->_core[$key]) ? $this->_core[$key] : null );
	}
	
	function getEvents( $person = null )
	{
		$fEvent = ApothFactory::_( 'report.event' );
		$requirements = array( 'cycle'=>$this->_id );
		if( !is_null($person) ) {
			$requirements['person'] = $person;
		}
		return $fEvent->getInstances( $requirements );
	}
	
	function getLayout()
	{
		$fLayout = ApothFactory::_( 'report.layout' );
		return $fLayout->getInstance( $this->_core['layout_id'] );
	}
	
	/**
	 * Commit the cycle to the database
	 */
	function commit()
	{
		$fCyc = ApothFactory::_( 'report.cycle' );
		$fCyc->commitInstance( $this->_id );
		return $this->_id;
	}
}
?>