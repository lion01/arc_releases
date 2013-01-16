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

// Give us access to the joomla model class
jimport( 'joomla.application.component.model' );

/**
 * Behaviour Manager Incidents Model
 *
 * @author		lightinthedark <code@lightinthedark.org.uk>
 * @package		Apotheosis
 * @subpackage	Behaviour
 * @since 0.1
 */
class BehaviourAdminModelIncidents extends JModel
{
	// #####  Main incident listing  #####
	
	/**
	 * Set the currently valid pagination object
	 * 
	 * @param int $limitStart Where to start paging from
	 * @param int $limit  The total number of items to page
	 */
	function setPagination( $limitStart, $limit )
	{
		$total = $this->_loadPagedIncidents( true );
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
	 * Set a paginated array of incident objects
	 */
	function setPagedIncidents()
	{
		$incStructure = $this->_loadPagedIncidents();
		
		$fInc = ApothFactory::_( 'behaviour.IncidentType' );
		
		$this->_pagedIncidents = array();
		foreach( $incStructure as $info ) {
			$info['obj'] = &$fInc->getInstance( $info['id'] );
			$this->_pagedIncidents[] = $info;
		}
	}
	
	/**
	 * Fetch a paginated list of incident objects
	 * 
	 * @return array $this->_pagedIncidents  Array of incident type objects
	 */
	function &getPagedIncidents()
	{
		return $this->_pagedIncidents;
	}
	
	/**
	 * Retrieve threads or a count of threads from the db
	 * 
	 * @param bool $numOnly  Whether we only want a count of threads, defaults to false
	 * @return int|array $result  The count of threads or array of thread info
	 */
	function _loadPagedIncidents( $numOnly = false )
	{
		$fInc = ApothFactory::_( 'behaviour.IncidentType' );
		$r = $fInc->getInstances( array('deleted'=>false), false, true ); 
		
		if( $numOnly) {
			$r = count($r);
		}
		else {
			$r = $fInc->getStructure( array('deleted'=>false) );
			$r = array_slice( $r, $this->_pagination->limitstart, $this->_pagination->limit );
		}
		
		return $r;
	}
	
	// #####  Edits from the list  #####
	function toggleHasText()
	{
		$this->_incident->setHasText( !$this->_incident->getHasText() );
		return $this->_incident->commit();
	}
	
	
	// #####  Incident editing  #####
	
	function setIncident( $incId )
	{
		$fInc = ApothFactory::_( 'behaviour.IncidentType' );
		
		if( $incId < 0 ) {
			$this->_incident = &$fInc->getDummy( $incId );
		}
		else {
			$this->_incident = &$fInc->getInstance( $incId );
		}
		$pId = $this->_incident->getParentId();
		if( is_null($pId) ) { $pId = $incId; } // pretend it's its own parent so we have an object
		$this->_parentIncident = &$fInc->getInstance( $pId );
	}
	
	function getIncident()
	{
		return $this->_incident;
	}
	
	function getParentIncident()
	{
		return $this->_parentIncident;
	}
	
	/**
	 * Save the message data
	 * 
	 * @param array $messageData  Message data to save
	 * @return boolean $commit Whether or not the object was successfully committed
	 */
	function save( $data )
	{
		$this->_incident->setParentId( $data['parent'] );
		$this->_incident->setLabel( $data['label'] );
		$this->_incident->setHasText( $data['has_text'] );
		$this->_incident->setScore( $data['score'] );
		$this->_incident->setTag( $data['tag'] );
		
		return $this->_incident->commit();;
	}
	
	function delete( $ids )
	{
		$fInc = ApothFactory::_( 'behaviour.IncidentType' );
		
		$r = true;
		foreach( $ids as $id ) {
			$inc = $fInc->getInstance( $id );
			$r = $inc->delete() && $r;
		}
		return $r;
	}
}
?>