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

// include front-end message factories
require_once( JPATH_SITE.DS.'components'.DS.'com_arc_core'.DS.'libraries'.DS.'apoth_factory.php' );

/**
 * Behaviour Manager Actions Model
 *
 * @author		lightinthedark <code@lightinthedark.org.uk>
 * @package		Apotheosis
 * @subpackage	Behaviour
 * @since 0.1
 */
class BehaviourAdminModelActions extends JModel
{
	// #####  Main action listing  #####
	
	/**
	 * Set the currently valid pagination object
	 * 
	 * @param int $limitStart Where to start paging from
	 * @param int $limit  The total number of items to page
	 */
	function setPagination( $limitStart, $limit )
	{
		$total = $this->_loadPagedActions( true );
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
	 * Set a paginated array of action objects
	 */
	function setPagedActions()
	{
		$incStructure = $this->_loadPagedActions();
		
		$fAct = ApothFactory::_( 'behaviour.Action' );
		
		$this->_pagedActions = array();
		foreach( $incStructure as $id ) {
			$this->_pagedActions[] = &$fAct->getInstance( $id );
		}
	}
	
	/**
	 * Fetch a paginated list of action objects
	 * 
	 * @return array $this->_pagedActions  Array of action type objects
	 */
	function &getPagedActions()
	{
		return $this->_pagedActions;
	}
	
	/**
	 * Retrieve threads or a count of threads from the db
	 * 
	 * @param bool $numOnly  Whether we only want a count of threads, defaults to false
	 * @return int|array $result  The count of threads or array of thread info
	 */
	function _loadPagedActions( $numOnly = false )
	{
		$fAct = ApothFactory::_( 'behaviour.Action' );
		$r = $fAct->getInstances( array(), false, true ); 
		
		if( $numOnly) {
			$r = count($r);
		}
		else {
			$r = array_slice( $r, $this->_pagination->limitstart, $this->_pagination->limit );
		}
		
		return $r;
	}
	
	
	// #####  Edits from the list  #####
	function toggleHasText()
	{
		$this->_action->setHasText( !$this->_action->getHasText() );
		return $this->_action->commit();
	}
	
	
	// #####  Action editing  #####
	
	function setAction( $incId )
	{
		$fAct = ApothFactory::_( 'behaviour.Action' );
		
		if( $incId < 0 ) {
			$this->_action = &$fAct->getDummy( $incId );
		}
		else {
			$this->_action = &$fAct->getInstance( $incId );
		}
	}
	
	function getAction()
	{
		return $this->_action;
	}
	
	/**
	 * Save the message data
	 * 
	 * @param array $messageData  Message data to save
	 * @return boolean $commit Whether or not the object was successfully committed
	 */
	function save( $data )
	{
		$this->_action->setLabel( $data['label'] );
		$this->_action->setHasText( $data['has_text'] );
		$this->_action->setScore( $data['score'] );
		$this->_action->setIncidents( $data['incidents'] );
		
		return $this->_action->commit();;
	}
	
	function delete( $ids )
	{
		$fAct = ApothFactory::_( 'behaviour.Action' );
		
		$r = true;
		foreach( $ids as $id ) {
			$inc = $fAct->getInstance( $id );
			$r = $inc->delete() && $r;
		}
		return $r;
	}
}
?>