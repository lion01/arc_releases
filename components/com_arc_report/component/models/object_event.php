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
 * Report Event Factory
 *
 * @author     Punnet - Arc Team <arc_developers@pun.net>
 * @package    Arc
 * @subpackage Report
 * @since      1.8
 */
class ApothFactory_Report_Event extends ApothFactory
{
	function &getDummy( $id )
	{
		if( $id >= 0 ) {
			$r = null;
			return $r;
		}
		$r = &$this->_getInstance( $id );
		if( is_null($r) ) {
			$r = new ApothReportEvent( array('id'=>$id,
				'name'=>'New Event',
				'check_source'=>'report.complete',
				'icon'=>'',
				'target_action'=>'apoth_report') );
			$this->_addInstance( $id, $r );
		}
		return $r;
	}
	
	/**
	 * Retrieves the identified event, creating the object if it didn't already exist
	 * @param $id
	 */
	function &getInstance( $id )
	{
		$r = &$this->_getInstance( $id );
		if( is_null($r) ) {
			$db = &JFactory::getDBO();
			$query = 'SELECT ec.*, e.check_source, e.icon, e.target_action'
				."\n".'FROM '.$db->nameQuote( '#__apoth_rpt_event_config' ).' AS ec'
				."\n".'INNER JOIN '.$db->nameQuote( '#__apoth_rpt_events' ).' AS e'
				."\n".'   ON e.id = ec.event_id'
				."\n".'WHERE ec.id = '.$db->Quote( $id );
			$db->setQuery($query);
			$data = $db->loadAssoc();
			
			$r = new ApothReportEvent( $data );
			$this->_addInstance( $id, $r );
		}
		return $r;
	}
	
	function &getInstances( $requirements, $init = true )
	{
		$sId = $this->_getSearchId( $requirements );
		$ids = $this->_getInstances($sId);
		$restrict = $this->getParam( 'restrict' );
		
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
				case( 'cycle' ):
					$where[] = 'ec.cycle_id'.$assignPart;
					break;
					
				case( 'id' ):
					$where[] = 'ec.id'.$assignPart;
					break;
				}
			}
			
			$query = 'SELECT DISTINCT ec.*, e.check_source, e.icon, e.target_action'
				."\n".'FROM '.$db->nameQuote( '#__apoth_rpt_event_config' ).' AS ec'
				."\n".'INNER JOIN '.$db->nameQuote( '#__apoth_rpt_events' ).' AS e'
				."\n".'   ON e.id = ec.event_id'
				.( empty($where) ? '' : "\nWHERE ".implode("\n AND ", $where) )
				."\n".'ORDER BY ec.end_time';
			
 			$db->setQuery( $restrict ? ApotheosisLibAcl::limitQuery($query, 'report.events') : $query );
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
						$obj = new ApothReportEvent( $objData );
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
 * Report Event Object
 */
class ApothReportEvent extends JObject
{
	function __construct( $data )
	{
		$this->_id     = $data['id'];
		$this->_core   = $data;
	}
	
	// #####  accessors  #####
	function getId()          { return $this->_id; }
	function getDueDate()     { return $this->_core['end_time']; }
	
	function getDatum( $key ) {
		return ( isset($this->_core[$key]) ? $this->_core[$key] : null );
	}
	
	function getDueDays()
	{
		return floor( ( strtotime( $this->_core['end_time'] ) - time() ) / 86400 ); // 86400 seconds in a day;
	}
	
	
	/**
	 * Commit the event to the database
	 */
	function commit()
	{
		$fCyc = ApothFactory::_( 'report.event' );
		$fCyc->commitInstance( $this->_id );
		return $this->_id;
	}
}
?>