
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
 * Report Model Subreport
 *
 * @author     Punnet - Arc Team <arc_developers@pun.net>
 * @package    Arc
 * @subpackage Report
 * @since      1.8
 */
class ReportModelSubreport extends JModel
{
	/**
	 * Set up the activity
	 * Sets $this->_activity
	 * @param string $activity  The activity in progress ('write'|'check'|'view')
	 */
	function setActivity( $activity )
	{
		$this->_activity = $activity;
	}
	
	function getActivity()
	{
		return $this->_activity;
	}
	
	/**
	 * Set up the active cycle
	 * Sets $this->_cycle
	 * @param int $id  The cycle id to set as current
	 */
	function setSubreport( $id )
	{
		$fSub = ApothFactory::_( 'report.subreport' );
		$this->_subreport = $fSub->getInstance( $id );
		return( is_object( $this->_subreport ) && ( $this->_subreport->getId() == $id ) );
	}
	
	function &getSubreport()
	{
		return $this->_subreport;
	}
	
	function updateSubreport( $data )
	{
		foreach( $data as $fieldId=>$value ) {
			$this->_subreport->setFieldDatum( $fieldId, $value );
		}
	}
	
	function saveSubreport( $status, $comment )
	{
		$this->_subreport->setStatus( $status, $comment );
		return $this->_subreport->commit();
	}
	
	function setField( $id )
	{
		$fCyc = ApothFactory::_( 'report.cycle' );
		$fField = ApothFactory::_( 'report.field' );
		$this->_field = $fField->getInstance( $id );
		
		$cycleId = $this->_subreport->getDatum( 'cycle_id' );
		$cycle = $fCyc->getInstance( $cycleId );
		$layoutId = $cycle->getDatum( 'layout_id' );
		$sectionId = $this->_subreport->getSectionId();;
		$groupId = $this->_subreport->getDatum( 'rpt_group_id' );
		
		$this->_field->setContext( $cycleId, $layoutId, $sectionId, $groupId );
		$this->_field->setReportData( $this->_subreport );
		$this->_field->setConfig();
		return( is_object( $this->_field ) && ( $this->_field->getId() == $id ) );
	}
	
	function getField()
	{
		return $this->_field;
	}
}
?>