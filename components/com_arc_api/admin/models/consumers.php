<?php
/**
 * @package     Arc
 * @subpackage  API
 * @copyright   Copyright (C) 2005 Punnet. All rights reserved. See COPYRIGHT_ARC.txt
 * @license     http://www.gnu.org/licenses/gpl.html GNU/GPL. See LICENSE_ARC.txt
 * Arc is free software: you can redistribute it and/or modify
 * it under the terms of version 3 of the GNU General Public License
 * as published by the Free Software Foundation.
 * The disclaimer of warranty as stated in the GPL applies to this program
 */

defined( '_JEXEC' ) or die( 'Restricted access' );

// Give us access to the joomla model class
jimport( 'joomla.application.component.model' );

/**
 * API Admin Consumers Model
 *
 * @author     Punnet - Arc Team
 * @package    Arc
 * @subpackage API
 * @since      1.6
 */
class ApiAdminModelConsumers extends JModel
{
	// #####  Main registered consumer listing  #####
	
	/**
	 * Set the search term
	 * 
	 * @param string $term  The search term to set
	 */
	function setSearchTerm( $searchTerm )
	{
		$this->_searchTerm = JString::strtolower( $searchTerm );
	}
	
	/**
	 * Retrieve the search term
	 * 
	 * @return string $this->_searchTerm  The search term
	 */
	function getSearchTerm()
	{
		return $this->_searchTerm;
	}
	
	/**
	 * Set the currently valid pagination object
	 * 
	 * @param int $limitStart Where to start paging from
	 * @param int $limit  The total number of items to page
	 */
	function setPagination( $limitStart, $limit )
	{
		$total = $this->_loadPagedConsumers( true );
		$this->_pagination = new JPagination( $total, $limitStart, $limit );
	}
	
	/**
	 * Retrieve the currently valid pagination object
	 * 
	 * @return object $this->_pagination  The pagination object
	 */
	function &getPagination()
	{
		return $this->_pagination;
	}
	
	/**
	 * Fetch a paginated list of consumers loading them if necessary
	 *
	 * @return array $this->_pagedConsumers  Array of consumer objects
	 */
	function getPagedConsumers()
	{
		if( !isset($this->_pagedConsumers) ) {
			$consInfo = $this->_loadPagedConsumers();
			
			$fCon = ApothFactory::_( 'api.consumer' );
			
			$this->_pagedConsumers = array();
			foreach( $consInfo as $id ) {
				$this->_pagedConsumers[] = $fCon->getInstance( $id );
			}
		}
		
		return $this->_pagedConsumers;
	}
	
	/**
	 * Retrieve consumers or a count of consumers from the factory
	 * 
	 * @param bool $numOnly  Whether we only want a count of consumers, defaults to false
	 * @return int|array $result  The count of consumers or array of consumer ids
	 */
	function _loadPagedConsumers( $numOnly = false )
	{
		$fCon = ApothFactory::_( 'api.consumer' );
		$r = $fCon->getInstances( array('enabled'=>1, 'text'=>$this->_searchTerm) ); 
		
		if( $numOnly) {
			$r = count($r);
		}
		else {
			$r = array_slice( $r, $this->_pagination->limitstart, $this->_pagination->limit );
		}
		
		return $r;
	}
	
	
	/**
	 * Set the consumer objects to affect
	 * 
	 * @param int|array $consumerInfo  Optional current pagination index of the consumer we want or array of data
	 */
	function setConsumers( $consumerInfo = null )
	{
		$fCon = ApothFactory::_( 'api.consumer' );
		$this->_consumers = array();
		if( is_null($consumerInfo) ) {
			$this->_consumers[] = $fCon->getDummy();
		}
		else {
			if( !is_array($consumerInfo) ) {
				$consumerInfo = array( $consumerInfo );
			}
			
			$this->getPagedConsumers();
			foreach( $consumerInfo as $eId ) {
				$this->_consumers[] = $this->_pagedConsumers[$eId];
			}
		}
	}
	
	function getConsumer()
	{
		reset($this->_consumers);
		return $this->_consumers[key($this->_consumers)];
	}
	
	function getConsumers()
	{
		return $this->_consumers;
	}
	
	
	/**
	 * Save the consumer data
	 * 
	 * @param array $consumerData  Consumer data to save
	 * @return boolean $commit Whether or not the object was successfully committed
	 */
	function save( $consumerData )
	{
		// Create consumer object with passed in data and commit it
		$fCon = ApothFactory::_( 'api.consumer' );
		if( $consumerData['id'] < 0 ) {
			$consumerObj = &$fCon->getDummy( $consumerData['id'] );
		}
		else {
			$consumerObj = &$fCon->getInstance( $consumerData['id'] );
		}
		$consumerObj->setDatum( 'name', $consumerData['name'] );
		$consumerObj->setDatum( 'description', $consumerData['description'] );
		$consumerObj->setDatum( 'cons_key', $consumerData['key'] );
		
		$commit = $consumerObj->commit();
		$this->_consumers = array( $consumerObj );
		
		return $commit;
	}
	
	function delete()
	{
		$retVal = true;
		foreach( $this->_consumers as $k=>$v ) {
			$retVal = $this->_consumers[$k]->disable() && $retVal;
		}
		unset( $this->_pagedConsumers );
		$this->getPagedConsumers();
		return $retVal;
	}
}
?>