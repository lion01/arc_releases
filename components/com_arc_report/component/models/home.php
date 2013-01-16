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
 * Report Model Home
 *
 * @author     Punnet - Arc Team <arc_developers@pun.net>
 * @package    Arc
 * @subpackage Report
 * @since      1.8
 */
class ReportModelHome extends JModel
{
	/**
	 * Find and store the active cycles
	 * Sets $this->_cycles
	 */
	function setCycles()
	{
		$fCyc = ApothFactory::_( 'report.cycle' );
		$this->_cycles = $fCyc->getInstances( array( 'active'=>true ) );
		return true;
	}
	
	function getNextCycle()
	{
		if( !isset( $this->_cycle ) || is_null( $this->_cycle ) ) {
			$id = reset( $this->_cycles );
		}
		else {
			$id = next( $this->_cycles );
		}
		
		if( $id === false ) {
			$this->_cycle = null;
		}
		else {
			$fCyc = ApothFactory::_( 'report.cycle' );
			$this->_cycle = $fCyc->getInstance( $id );
		}
		$this->_event = null;
		return $this->_cycle;
	}
	
	/**
	 * Find the events for the cycles and sort them by due date
	 * Sets $this->_events
	 */
	function setEvents()
	{
		$fCyc   = ApothFactory::_( 'report.cycle' );
		$fEvent = ApothFactory::_( 'report.event' );
		$u = ApotheosisLib::getUser();
		$person = $u->person_id;
		foreach( $this->_cycles as $cId ) {
			$cycle = $fCyc->getInstance( $cId );
			$events = $cycle->getEvents();
			$eList = array();
			foreach( $events as $eId ) {
				$eList[$eId] = $fEvent->getInstance( $eId );
			}
			
			uasort( $eList, array($this, '_cmp_events') );
			reset( $eList );
			$this->_events[$cId] = $eList;
		}
	}
	
	function _cmp_events( $a, $b )
	{
		$ad = $a->getDatum( 'end_time' );
		$bd = $b->getDatum( 'end_time' );
		
		if( $ad == $bd ) {
			return 0;
		}
		else {
			return ( ($ad < $bd) ? -1 : 1 );
		}
	}
	
	function getNextEvent()
	{
		if( is_null( $this->_event ) ) {
			$this->_event = reset( $this->_events[$this->_cycle->getId()] );
		}
		else {
			$this->_event = next( $this->_events[$this->_cycle->getId()] );
		}
		
		return $this->_event;
	}
}
?>