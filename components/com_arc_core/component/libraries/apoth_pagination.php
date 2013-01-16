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
 * Apotheosis Pagination
 */
class ApothPagination extends JObject
{
	static $_instances = array();
	
	/**
	 * Static method to retrieve the singleton instance of the pagination class named
	 * 
	 * @param string $ident  The component.factory identifier 
	 * @param object|null $target  Optionally the instance of the named factory, or null if not a defined factory
	 */
	function &_( $ident, &$paginator = null, $requirements = null, $order = null, $pageSize = null )
	{
		$i = &self::$_instances;
		
		$ident = strtolower( $ident );
		
		if( !isset( $i[$ident] ) ) {
			// create / store a pagination instance
			if( is_a( $paginator, 'ApothPagination' ) ) {
				$i[$ident] = $paginator;
			}
			else {
				$i[$ident] = new ApothPagination( $ident );
			}
			
			
			// get the factory for the pagination to use
			$identParts = explode( '.', $ident, 3 );
			$factoryIdent = $identParts[0].'.'.$identParts[1];
			$factory = ApothFactory::_( $factoryIdent );
			
			if( $factory == false ) {
				// mark as invalid
				$i[$ident] = false;
			}
			else {
				$i[$ident]->setFactory( $factory );
				
				// set any starting params
				if( !is_null( $requirements ) ) {
					$i[$ident]->setData( $requirements, $order );
				}
				if( !is_null( $pageSize ) ) {
					$i[$ident]->setPageSize( $pageSize );
				}
			}
		}
		
		return $i[$ident];
	}
	
	var $_factory;
	var $_pageSize;
	var $_pagedIds;
	var $_pageCount;
	var $_page;
	var $_loadedPages;
	var $_dataSignature;
	
	function __construct( $ident )
	{
		$this->_ident = $ident;
		$this->_factory = null;
		$this->_pageSize = 10;
		$this->_rollSize = 2;
		$this->_pagedIds = array();
		$this->_page = 0;
		$this->_loadedPages = array();
		$this->_dataSignature = null;
		$this->_setPageCount();
	}
	
	function __wakeup()
	{
		$this->_factory = ApothFactory::_( $this->_ident, $this->_factory );
	}
	
	function setFactory( &$factory )
	{
		$this->_factory = &$factory;
	}
	
	
	function &getFactory()
	{
		return $this->_factory;
	}
	
	function setData( $requirements, $order )
	{
		$tmp = serialize( $requirements ).serialize( $order );
		$sig = md5( $tmp ).'-'.strlen($tmp);
		if( $this->_dataSignature != $sig ) {
			$this->_dataSignature = $sig;
			$this->_trimLoadedData( true );
			$this->_loadedPages = array();
			$this->_pagedIds = $this->_factory->getInstances( $requirements, false, $order );
			$this->_setPageCount();
			$this->_page = 0;
		}
	}
	
	function setPageSize( $size, $rollSize = null ) 
	{
		if( !is_null( $rollSize ) ) {
			$this->_rollSize = $rollSize;
		}
		$this->_trimLoadedData();
		
		$this->_pageSize = (int)$size;
		$this->_setPageCount();
	}
	
	function getPageSize()
	{
		return $this->_pageSize;
	}
	
	function _setPageCount()
	{
		$this->_pageCount = ceil( count($this->_pagedIds) / $this->_pageSize );
	}
	
	function getPageCount()
	{
		return $this->_pageCount;
	}
	
	function setPage( $pageNum )
	{
		if( $pageNum >= $this->_pageCount ) {
			$pageNum = $this->_pageCount - 1;
		}
		elseif( $pageNum < 0 ) {
			$pageNum = 0;
		}
		
		$this->_page = (int)$pageNum;
		return $this->_page;
	}
	
	function getPage()
	{
		return $this->_page;
	}
	
	function getInstanceCount()
	{
		return count( $this->_pagedIds );
	}
	
	/**
	 * Retrieves the given page worth of instances
	 * 
	 * @param int $pagenum  The page number whose contents we want
	 * @param boolean $init  Do we want to also initialise and cache the objects
	 * @return array $instanceIds  The IDs of the instances on the given page
	 */
	function getPagedInstances( $pageNum = null, $init = true )
	{
		if( $this->_pageCount == 0 ) {
			$ids = array();
		}
		else {
			if( !is_null($pageNum) ) {
				$this->setPage( $pageNum );
			}
			$start = $this->_page * $this->_pageSize;
			$ids = array_slice( $this->_pagedIds, $start, $this->_pageSize );
			if( $init ) {
				$this->_factory->getInstances( array('id'=>$ids), $init );
			}
			
			if( array_search( $this->_page, $this->_loadedPages ) === false ) {
				$this->_loadedPages[] = $this->_page;
				$this->_trimLoadedData();
			}
		} 
		
		return $ids;
	}
	
	function _trimLoadedData( $all = false )
	{
		asort( $this->_loadedPages );
		
		foreach( $this->_loadedPages as $k=>$page ) {
			if( $all
			 || (($page + $this->_rollSize) < $this->_page)
			 || (($page - $this->_rollSize) > $this->_page) ) {
				// free all the instances for that page
				$start = $page * $this->_pageSize;
				$ids = array_slice( $this->_pagedIds, $start, $this->_pageSize );
			 	foreach( $ids as $id ) {
			 		$this->_factory->freeInstance( $id );
			 	}
			 	unset( $this->_loadedPages[$k] );
			}
		}
		
	}
}
?>