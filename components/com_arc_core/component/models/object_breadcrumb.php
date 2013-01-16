<?php
/**
 * @package     Arc
 * @subpackage  Core
 * @copyright   Copyright (C) 2005 Punnet. All rights reserved. See COPYRIGHT_ARC.txt
 * @license     http://www.gnu.org/licenses/gpl.html GNU/GPL. See LICENSE_ARC.txt
 * Arc is free software: you can redistribute it and/or modify
 * it under the terms of version 3 of the GNU General Public License
 * as published by the Free Software Foundation.
 * The disclaimer of warranty as stated in the GPL applies to this program
 */

defined( '_JEXEC' ) or die( 'Restricted access' );

/**
 * Core Breadcrumb Factory
 *
 * @author     Punnet - Arc Team <arc_developers@pun.net>
 * @package    Arc
 * @subpackage Core
 * @since      1.8
 */
class ApothFactory_Core_Breadcrumb extends ApothFactory
{
	var $_existingHashes;
	
	/**
	 * To comply with automated saving of factories
	 * we must explicitly sleep any class vars in child factories
	 */
	function __sleep()
	{
		$parentVars = parent::__sleep();
		$myVars = array( '_existingHashes' );
		$allVars = array_merge( $parentVars, $myVars );
	
		return $allVars;
	}
	
	function &getDummy( $id )
	{
		if( $id >= 0 ) {
			$r = null;
			return $r;
		}
		$r = &$this->_getInstance( $id );
		if( is_null($r) ) {
			$r = new ApothCoreBreadcrumb( $id, 'Crumb', array() );
			$this->_addInstance( $id, $r );
		}
		return $r;
	}
	
	/**
	 * Retrieves the identified breadcrumb if it already exists
	 * 
	 * @param $id
	 */
	function &getInstance( $id )
	{
		$r = &$this->_getInstance( $id );
		if( is_null($r) ) {
			$r = null;
		}
		return $r;
	}
	
	/**
	 * Get all the breadcrumb objects matching the given search
	 * 
	 * @param array $requirements
	 */
	function &getInstances( $requirements )
	{
		if( empty( $requirements ) ) {
			$requirements = array( 'trail'=>'_default' );
		}
		$sId = $this->_getSearchId( $requirements );
		$ids = $this->_getInstances( $sId );
		$restrict = $this->getParam( 'restrict' );
		$now = date( 'Y-m-d H:i:s' );
		
		if( is_null($ids) ) {
			$ids = array();
			$this->_addInstances( $sId, $ids );
		}
		
		return $ids;
	}
	
	/**
	 * Return the breadcrumb at the start of the given trail
	 * 
	 * @param string $trail  The trail upon which to act
	 */
	function &getHead( $trail )
	{
		$ids = $this->getInstances( array( 'trail'=>$trail ) );
		$r = empty( $ids ) ? null : $this->getInstance( reset( $ids ) );
		return $r;
	}
	
	/**
	 * Return the breadcrumb at the end of the given trail
	 * 
	 * @param string $trail  The trail upon which to act
	 */
	function &getTail( $trail )
	{
		$ids = $this->getInstances( array( 'trail'=>$trail ) );
		$r = empty( $ids ) ? null : $this->getInstance( end( $ids ) );
		return $r;
	}
	
	/**
	 * Completely clear the trail identified, removing instances on it
	 * 
	 * @param string $trail  The trail upon which to act
	 */
	function sweepTrail( $trail )
	{
		$req = array( 'trail'=>$trail );
		$sId = $this->_getSearchId( $req );
		$ids = $this->getInstances( $req );
		foreach( $ids as $id ) {
			$this->_clearCachedInstances( $id );
		}
		
		$this->_clearCachedSearches( $sId );
		$newIds = $this->getInstances( $req );
		return empty( $newIds );
	}
	
	/**
	 * Remove all breadcrumbs after but not including the crumb identified
	 * 
	 * @param string $trail  The trail upon which to act
	 * @param int $crumbId  The crumb which should be left as last in the trail
	 */
	function curtailTrail( $trail, $crumbId )
	{
		$req = array( 'trail'=>$trail );
		$sId = $this->_getSearchId( $req );
		$ids = $this->getInstances( $req );
		
		$id = end( $ids );
		while( !empty( $id ) && ($id != $crumbId) ) {
			$crumb = $this->getInstance( $id );
			unset( $this->_existingHashes[$sId][$crumb->getDataHash()] );
			$this->_clearCachedInstances( $id );
			$id = prev( $ids );
			array_pop( $this->_searches[$sId] );
		}
		
		$t = $this->getTail( $trail );
		return ( !is_null( $t ) && ($t->getId() == $crumbId) );
	}
	
	/**
	 * Add a new breadcrumb to the end of the trail
	 * 
	 * @param string $trail  The trail upon which to act
	 * @param string $label  The crumb label to display
	 * @param array $data  The data which should be associated with the new crumb
	 */
	function addBreadcrumb( $trail, $label, $url, $data = array() )
	{
		$req = array( 'trail'=>$trail );
		$sId = $this->_getSearchId( $req );
		$ids = $this->getInstances( $req );
		
		// create a dummy breadcrumb to provide the hash the given data produces
		$r = new ApothCoreBreadcrumb( null, $label, $url, $data );
		$hash = $r->getDataHash(); 
		
		// check through all existing crumbs in the trail to determine if this crumb actually needs to be added
		$isNew = true;
		foreach( $ids as $id ) {
			$o = $this->getInstance( $id );
			if( $o->getDataHash() == $hash ) {
				$isNew = false;
				break;
			}
			unset( $o );
		}
		
		if( $isNew ) {
			$id = end( array_keys( $this->_instances ) ) + 1;
			$r->setId( $id );
			$this->_addInstance( $id, $r );
			$this->_searches[$sId][] = &$id;
			$this->_existingHashes[$sId][$hash] = $id;
		}
		else {
			unset( $r );
			$r = $this->getInstance( $this->_existingHashes[$sId][$hash] );
		}
		
		return $r;
	}
}


/**
 * Report Cycle Object
 */
class ApothCoreBreadcrumb extends JObject
{
	function __construct( $id, $label, $url, $data = array() )
	{
		$this->_id     = $id;
		$this->_label  = $label;
		$this->_url    = $url;
		$this->_data   = $data;
		
		$tmp = serialize( $url );
		$this->_dataHash = md5( $tmp ).'-'.strlen( $tmp );
		
	}
	
	// #####  accessors  #####
	function setId( $id )     { $this->_id = $id; }
	function getId()          { return $this->_id; }
	function getLabel()       { return $this->_label; }
	function getURL()         { return $this->_url; }
	
	function setDatum( $key, $val )
	{
		$this->_data[$key] = $val;
	}
	
	function getDatum( $key = null ) {
		if( is_null( $key ) ) {
			return $this->_data;
		}
		else {
			return ( isset($this->_data[$key]) ? $this->_data[$key] : null );
		}
	}
	
	function getDataHash()
	{
		return $this->_dataHash;
	}
}
?>