<?php
/**
 * @package     Arc
 * @subpackage  Assessment
 * @copyright   Copyright (C) 2005 Punnet. All rights reserved. See COPYRIGHT_ARC.txt
 * @license     http://www.gnu.org/licenses/gpl.html GNU/GPL. See LICENSE_ARC.txt
 * Arc is free software: you can redistribute it and/or modify
 * it under the terms of version 3 of the GNU General Public License
 * as published by the Free Software Foundation.
 * The disclaimer of warranty as stated in the GPL applies to this program
 */

defined( '_JEXEC' ) or die( 'Restricted access' );

/**
 * Report Mark Field
 *
 * @author     Punnet - Arc Team <arc_developers@pun.net>
 * @package    Arc
 * @subpackage Report
 * @since      1.8
 */
class ApothReportField_Assessment_Mark extends ApothReportField
{
	function renderHTML( $value )
	{
		$aspId = $this->_config['aspId'];
		$pId = $this->_rptData[$this->_core['lookup_source']];
		$gId = null;
		
		ApotheosisData::_( 'assessment.prepare', $aspIds, $pId, null, $this->_config['from'], $this->_config['to'], null, null, false );
		if( is_null( $value ) ) {
			$m = JHTML::_( 'arc_assessment.mark', $aspId, $pId, $gId );
		}
		else {
			$m = htmlspecialchars( $value );
		}
		$m .= '(needs proper asp, pupil, group vals)';
		return parent::renderHTML( $m );
	}
}

/**
 * Report Average Mark Field
 *
 * @author     Punnet - Arc Team <arc_developers@pun.net>
 * @package    Arc
 * @subpackage Report
 * @since      1.8
 */
class ApothReportField_Assessment_MarkAverage extends ApothReportField
{
	function renderHTML( $value )
	{
		if( is_null( $value ) ) {
			$aspIds = $this->_config['aspIds'];
			$pId = $this->_rptData[$this->_core['lookup_source']];
			ApotheosisData::_( 'assessment.prepare', $aspIds, $pId, null, $this->_config['from'], $this->_config['to'], null, null, false );
			
			$m = JHTML::_( 'arc_assessment.markAverage', $aspIds, $pId, 'pupil' );
			$m = $m['html'];
		}
		else {
			$m = htmlspecialchars( $value );
		}
		return parent::renderHTML( $m );
	}
}

/**
 * Report Average MarkSummary Field
 *
 * @author     Punnet - Arc Team <arc_developers@pun.net>
 * @package    Arc
 * @subpackage Report
 * @since      1.8
 */
class ApothReportField_Assessment_MarkSummary extends ApothReportField
{
	function renderHTML( $value )
	{
		// get relevant config
		$aspIds = $this->_config['aspIds'];
		$pId = $this->_rptData[$this->_core['lookup_source']];
		
		// find enrolments to consider when looking for assessment data
		$requirements = array( 
			'person_id'=>$pId,
			'valid_from'=>$this->_config['from'],
			'valid_to'=>$this->_config['to']
		);
		$e = ApotheosisData::_( 'timetable.studentEnrolments', $requirements, false, false );
		$enrolments = ApotheosisData::_( 'timetable.enrolmentHistory', $e, $requirements['valid_from'], $requirements['valid_to'] );
		if( empty( $enrolments ) ) { $enrolments = array(); }
		
		// tidy up the enrolments into subjects
		$groups = array();
		$subjects = array();
		foreach( $e as $enrol ) {
			$g = $enrol['group_id'];
			$groups[$g] = $g;
		}
		
		foreach( $enrolments[$pId] as $gId=>$history ) {
			$s = ApotheosisData::_( 'course.subject', $gId );
			$name = ApotheosisData::_( 'course.name', $s );
			
			if( !isset( $subjects[$name] ) ) {
				$subjects[$name] = $history;
			}
			else {
				$tmp = array_merge( $subjects[$name], $history );
				$subjects[$name] = array_unique( $tmp );
			}
		}
		ksort( $subjects );
		
		// prepare all the assessment data
		$asps = array();
		$data = $this->_config['data'];
		foreach( $data as $aTitle=>$columns ) {
			foreach( $columns as $cTitle=>$aspIds ) {
				foreach( $aspIds as $aspId ) {
					$asps[] = $aspId;
				}
			}
		}
		ApotheosisData::_( 'assessment.prepare', $asps, $pId, $groups, $this->_config['from'], $this->_config['to'], null, null, false );
		
		// With the groups and aspects worked out, it's time to generate the data table
		$html = '<table class="data">';
		// ... headings
		$html .= '<tr><th>&nbsp;</th>';
		foreach( $data as $aTitle=>$columns ) {
			$html .= '<th colspan="'.count($columns).'">'.$aTitle.'</th>';
		}
		$html .= '</tr>';
		$html .= '<tr><th>Subject</th>';
		$alt = false;
		foreach( $data as $aTitle=>$columns ) {
			foreach( $columns as $cTitle=>$aspIds ) {
				$html .= '<th'.( ($alt = !$alt) ? ' class="odd_col"' : '').'>'.$cTitle.'</th>';
			}
		}
		$html .= '</tr>';
		// ... data
		foreach( $subjects as $name=>$history ) {
			$alt = false;
			$rowGood = false;
			$row = '<tr><td>'.$name.'</td>';
			foreach( $data as $aTitle=>$columns ) {
				foreach( $columns as $cTitle=>$aspIds ) {
					$row .= '<td'.( ($alt = !$alt) ? ' class="odd_col"' : '').'>';
					foreach( $history as $gId ) {
						$m = JHTML::_( 'arc_assessment.markCoalesce', $aspIds, $pId, $gId );
						if( $m['hasMark'] ) {
							$rowGood = true;
							break;
						}
					}
					$row .= $m['html'].'</td>';
				}
			}
			$row .= '</tr>';
			if( $rowGood ) {
				$html .= $row;
			}
		}
		$html .= '</table>';
		
		return parent::renderHTML( $html );
	}
}
?>