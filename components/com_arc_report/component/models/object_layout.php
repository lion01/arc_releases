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
 * Report Layout Factory
 *
 * @author     Punnet - Arc Team <arc_developers@pun.net>
 * @package    Arc
 * @subpackage Report
 * @since      1.8
 */
class ApothFactory_Report_Layout extends ApothFactory
{
	function &getDummy( $id )
	{
		if( $id >= 0 ) {
			$r = null;
			return $r;
		}
		$r = &$this->_getInstance( $id );
		if( is_null($r) ) {
			$r = new ApothReportLayout( array('id'=>$id,
				'name'=>'New Layout',
				'description'=>'A new dummy layout',
				'print_page_size'=>'A4',
				'print_page_limit'=>2,
				'print_default_font'=>'Arial',
				'print_default_font_size'=>10) );
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
			$query = 'SELECT rl.*'
				."\n".'FROM '.$db->nameQuote( '#__apoth_rpt_report_layouts' ).' AS rl'
				."\n".'WHERE rl.id = '.$db->Quote( $id );
			$db->setQuery($query);
			$data = $db->loadAssoc();
			
			$r = new ApothReportLayout( $data );
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
			$where = $join = $orderBy = array();
			$this->requirementsToClauses( $requirements, $where, $join );
			
			$db = &JFactory::getDBO();
			$query = 'SELECT DISTINCT rl.*'
				."\n".'FROM '.$db->nameQuote( '#__apoth_rpt_report_layouts' ).' AS rl'
				.( empty($join)  ? '' : "\n".implode("\n", $join) )
				.( empty($where) ? '' : "\nWHERE ".implode("\n AND ", $where) );
			
 			$db->setQuery( $restrict ? ApotheosisLibAcl::limitQuery($query, 'report.layouts') : $query );
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
						$obj = new ApothReportLayout( $objData );
						$this->_addInstance( $id, $obj );
						unset( $obj );
					}
				}
			}
		}
		
		return $ids;
	}
	
	/**
	 * Generate sql clauses suitable for use in getInstances.
	 * Generated clauses are added to $where and $join parameters (passed by reference).
	 * 
	 * @param array $requirements  Associative array of column=>value(s) by which to restrict the results
	 * @param array $where  Array to populate with clauses. Passed by reference,
	 * @param array $join   Array to populate with clauses. Passed by reference,
	 */
	function requirementsToClauses( $requirements, &$where, &$join )
	{
		if( !is_array( $where ) ) { $where = array(); }
		if( !is_array( $join )  ) { $join  = array(); }
		if( !is_array( $requirements ) || empty( $requirements ) ) { return; }
		
		$db = &JFactory::getDBO();
		$dbRl = $db->nameQuote( 'rl' );
		$dbId = $db->nameQuote( 'id' );
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
				$dbC  = $db->nameQuote( 'c' );
				$join[] = 'INNER JOIN '.$db->nameQuote( '#__apoth_rpt_cycles' ).' AS '.$dbC
					."\n".'   ON '.$dbC.'.'.$db->nameQuote( 'layout_id' ).' = '.$rdRl.'.'.$dbId;
				$where[] = $dbC.'.'.$dbId.$assignPart;
				break;
				
			case( 'id' ):
				$where[] = $dbRl.'.'.$dbId.$assignPart;
				break;
			}
		}
	}
	
	function commitInstance( $id )
	{
	}
	
	function loadOrders( $id )
	{
		$db = &JFactory::getDBO();
		$query = 'SELECT '.$db->nameQuote( 'section_id' ).', '.$db->nameQuote( 'order' )
			."\n".'FROM '.$db->nameQuote( '#__apoth_rpt_report_layout_sections' ).' AS ls'
			."\n".'WHERE '.$db->nameQuote( 'layout_id' ).' = '.$db->Quote( $id );
		$db->setQuery( $query );
		$raw = $db->loadAssocList();
		
		$retVal = array();
		foreach( $raw as $row ) {
			$retVal[$row['section_id']] = $row['order'];
		}
		return $retVal;
	}
	
}


/**
 * Report Layout Object
 */
class ApothReportLayout extends JObject
{
	function __construct( $data )
	{
		$this->_id     = $data['id'];
		$this->_core   = $data;
	}
	
	// #####  accessors  #####
	function getId()          { return $this->_id; }
	
	function getDatum( $key )
	{
		return ( isset($this->_core[$key]) ? $this->_core[$key] : null );
	}
	
	function getOrderedSections()
	{
		$fLay = ApothFactory::_( 'report.layout' );
		$fSec = ApothFactory::_( 'report.section' );
		$orders = $fLay->loadOrders( $this->_id );
		$sections = $fSec->getInstances( array( 'layout_id'=>$this->_id ), true, array( 'order'=>'a' ) );
		$orderedSections = array();
		
		foreach( $sections as $sectionId ) {
			$section = $fSec->getInstance( $sectionId );
			$orderPos = $orders[$sectionId];
			
			if( !isset($orderedSections[$orderPos]) ) {
				$orderedSections[$orderPos] = array( 'non'=>array(), 'sub'=>array() );
			}
			
			if( $section->getDatum( 'subreport' ) ) {
				$orderedSections[$orderPos]['sub'][$sectionId] = $section;
			}
			else {
				$orderedSections[$orderPos]['non'][$sectionId] = $section;
			}
		}
		
		return $orderedSections;
	}
	
	/**
	 * Commit the layout to the database
	 */
	function commit()
	{
		$fCyc = ApothFactory::_( 'report.layout' );
		$fCyc->commitInstance( $this->_id );
		return $this->_id;
	}
}
?>