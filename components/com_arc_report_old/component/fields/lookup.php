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
 * A class for handling lookup fields
 */
class ApothFieldLookup extends ApothField
{
	function __construct( $rpt, $name, $column, $l, $t, $r, $b, $lp, $rp, $tp, $bp, $hw, $hh, $title, $student, $group )
	{
		parent::__construct( $rpt, $name, $column, $l, $t, $r, $b, $lp, $rp, $tp, $bp, $hw, $hh, $title, false, false );
		
		$this->_style->lookup_id = explode( ';', $this->_style->lookup_id );
		
		switch($this->_style->lookup_type) {
		case('aspect'):
			ApotheosisData::_( 'assessment.prepare', $this->_style->lookup_id, $student, $group, $this->_style->start_date, $this->_style->end_date, 'report.people.'.$this->_style->cycle, 'report.groups.'.$this->_style->cycle, false );
			$m = JHTML::_( 'arc_assessment.markCoalesce', $this->_style->lookup_id, $student, $group );
			$this->_value   = $m['mark'];
			$this->_display = ( $m['hasMark'] ? $m['raw'] : 'N/A' );
			break;
		
		case('aspect_average'):
			if( is_null($student) ) {
				$id = $group;
				$type = 'group';
			}
			else {
				$id = $student;
				$type = 'pupil';
			}
			ApotheosisData::_( 'assessment.prepare', $this->_style->lookup_id, $student, $group, $this->_style->start_date, $this->_style->end_date, 'report.people.'.$this->_style->cycle, 'report.groups.'.$this->_style->cycle, false );
			$m = JHTML::_( 'arc_assessment.markAverage', $this->_style->lookup_id, $id, $type );
			$this->_value   = $m['mark'];
			$this->_display = ( $m['hasMark'] ? $m['raw'] : 'N/A' );
			break;
		
		case('att_percent_from_to'):
			require_once( JPATH_SITE.DS.'components'.DS.'com_arc_attendance'.DS.'helpers'.DS.'data_access.php' );
			$this->_display = $this->_value = ApotheosisAttendanceData::getAttendancePercent( 'fixed', $student, $group, $this->_style->start_date, $this->_style->end_date, true );
			break;
			
		case('att_percent_from'):
			require_once( JPATH_SITE.DS.'components'.DS.'com_arc_attendance'.DS.'helpers'.DS.'data_access.php' );
			$this->_display = $this->_value = ApotheosisAttendanceData::getAttendancePercent( 'fixed', $student, $group, $this->_style->start_date, date('Y-m-d H:i:s'), true );
			break;
			
		case('att_percent_from_to_cycle'):
			require_once( JPATH_SITE.DS.'components'.DS.'com_arc_attendance'.DS.'helpers'.DS.'data_access.php' );
			$cycle = ApothReportLib::getCycle( $this->_style->cycle );
			$this->_display = $this->_value = ApotheosisAttendanceData::getAttendancePercent( 'fixed', $student, $group, $this->_style->start_date, $cycle->valid_from, true );
			break;
		
		case('att_count'):
			require_once( JPATH_SITE.DS.'components'.DS.'com_arc_attendance'.DS.'helpers'.DS.'data_access.php' );
			$cycle = ApothReportLib::getCycle( $this->_style->cycle );
			$tmp = ApotheosisAttendanceData::getAttendanceCount( $this->_style->lookup_id, $student, $this->_style->start_date, $this->_style->end_date );
			
			if( count($tmp) > 1 ) {
				foreach( $tmp as $code=>$count ) {
					$tmp2[] = $code.': '.$count;
				}
				$str = implode( ', ', $tmp2 );
			}
			else {
				$str = ( (reset($tmp) !== false ) ? reset($tmp) : '0' );
			}
			
			$this->_display = $this->_value = $str;
			break;
		
		default:
			$this->_display = $this->_value = '--';
			break;
		}
		
		$this->_default = NULL;
		$this->htmlEnabled = false;
		$this->htmlSmallEnabled = false;
	}
	
	/**
	 * Displays the value of the input (in a text box if enabled)
	 * or as text in a div with a hidden input to carry the value through on form submit if not enabled
	 *
	 * @param $enabled boolean  Optional parameter to determine if the input should be enabled
	 *                          If omitted, the field's pre-set htmlEnabled attribute is used
	 */
	function dataHtml( $enabled = NULL )
	{
		$e = (is_null($enabled) ? $this->htmlEnabled : $enabled);
		return( $e
			? htmlspecialchars($this->prefix).'<textarea name="' .$this->_name.'" id="' .$this->_name.'" style="width: %1$s; height: %2$s;">'.htmlspecialchars($this->_value).'</textarea>'.htmlspecialchars($this->suffix)
			: '<div style="width: %1$s; height: %2$s;">'.htmlspecialchars($this->prefix.$this->_display.$this->suffix).'</div>'
			."\n".'<input type="hidden" name="'.$this->_name.'" id="'.$this->_name.'" value="'.htmlspecialchars($this->_value).'">');
	}
	
	function dataPdf()
	{
		return $this->prefix.$this->_display.$this->suffix;
	}
	
	function validate()
	{
		$failStr = 'No data available for '.$this->_name;
		
		if(($this->_value === 0) || ($this->_value === '0')) { $retVal = true; }
		elseif(($this->_value == '--') || empty($this->_value)) { $retVal = $failStr; }
		else {$retVal = true; }
		
		return $retVal;
	}
	
	/**
	 * Retrieve the lookup type
	 */
	function getLookupType()
	{
		return $this->_style->lookup_type;
	}
	
	/**
	 * Retrieve the available lookup types
	 */
	function getLookupTypes()
	{
		$blank = new stdClass();
		$blank->type   = '';
		$blank->title  = '';
		
		$asp = new stdClass();
		$asp->type     = 'aspect';
		$asp->title    = 'Aspect (single)';
		
		$aspAvg = new stdClass();
		$aspAvg->type  = 'aspect_average';
		$aspAvg->title = 'Aspects (average)';
		
		$attFT = new stdClass();
		$attFT->type   = 'att_percent_from_to';
		$attFT->title  = 'Attendance Avg (start to end)';
		
		$attF = new stdClass();
		$attF->type    = 'att_percent_from';
		$attF->title   = 'Attendance Avg (start to now)';
		
		$attFTC = new stdClass();
		$attFTC->type  = 'att_percent_from_to_cycle';
		$attFTC->title = 'Attendance Avg (start to cycle start)';
		
		$attCount = new stdClass();
		$attCount->type  = 'att_count';
		$attCount->title = 'Attendance Count (start to cycle start)';
		
		$types = array( $blank, $asp, $aspAvg, $attFT, $attF, $attFTC, $attCount );
		return $types;
	}
	
	/**
	 * Retrieve the lookup id(s) (aspect ids if lookup_type is aspect_...)
	 */
	function getLookupIds()
	{
		return $this->_style->lookup_id;
	}
	
	/**
	 * Retrieve the lookup dates
	 * @return array  Associative array of start_date=>somedate, end_date=>somedate
	 */
	function getLookupDates()
	{
		return array('start_date'=>$this->_style->start_date, 'end_date'=>$this->_style->end_date);
	}
}
?>