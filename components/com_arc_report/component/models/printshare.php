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
class ReportModelPrintshare extends JModel
{
	function __construct()
	{
		parent::__construct();
		$this->fSub = ApothFactory::_( 'report.subreport' );
		$this->fCycle = ApothFactory::_( 'report.cycle' );
		$this->fSub->setParam( 'restrict', true );
		$this->fSub->setParam( 'date', date( 'Y-m-d H:i:s' ) );
		$this->_requirements = array();
	}
	
	function __wakeup()
	{
		$this->fSub = ApothFactory::_( 'report.subreport' );
	}
	
	function __sleep()
	{
		$this->fSub->_doNotPersist = false;
		unset( $this->fSub );
		return array_keys( get_object_vars( $this ) );
	}
	
	function setSearch( $filters = array() )
	{
		$u = ApotheosisLib::getUser();
		$personId = $u->person_id;
		
		$role = ApotheosisLibAcl::getRoleId( 'any_report_reader');
		
		$requirements = $filters;
		if( $requirements !== $this->_requirements ) {
			$this->_requirements = $requirements;
		}
	}
	
	function getCycle()
	{
		unset( $this->_cycle );
		$this->_cycle = false;
		if( isset( $this->_requirements['cycle'] ) ) {
			$cId = ( is_array( $this->_requirements['cycle'] ) ? reset( $this->_requirements['cycle'] ) : $this->_requirements['cycle'] );
			$this->_cycle = &$this->fCycle->getInstance( $cId );
		}
		return $this->_cycle;
	}
	
	function getReportees()
	{
		return ApotheosisData::_( 'report.reportees', $this->_requirements, array( 'reportee_tutorgroup'=>'a', 'reportee_name'=>'a' ) );
	}
	
	/**
	 * Gets all the subreports for the given person, sorted into printable order
	 * 
	 * @param string $personId  The Arc person id whose set of subreports is required
	 */
	function getReportSet( $personId )
	{
		$r = $this->_requirements;
		$r['reportee'] = $personId;
//		$ids = $this->fSub->getInstances( $r, true, array( 'reportee_tutorgroup'=>'a', 'reportee_name'=>'a' ) );
		$ids = $this->fSub->getInstances( $r, true );
		
		$subreports = array();
		foreach( $ids as $id ) {
			$subreports[$id] = $this->fSub->getInstance( $id );
		}
		
		return $subreports;
	}
}
?>