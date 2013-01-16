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

require_once( JPATH_COMPONENT.DS.'models'.DS.'extension.php' );

/**
 * Assessments Markbook Model
 *
 * @author		lightinthedark <code@lightinthedark.org.uk>
 * @package		Apotheosis
 * @subpackage Assessments
 * @since 0.1
 */
class AssessmentsModelMarkbook extends AssessmentsModel
{
	function __construct()
	{
		parent::__construct();
		$this->_assessments = array();
	}
	
	function setAssessments( $requirements )
	{
		// Clear the decks, it's a new search
		$this->fAsp->clearCache();
		$this->fAss->clearCache();
		$this->fAss->setParam( 'restrict', true );
		
		// First we work out the enrolments (and therefore groups) indicated by the requirements
		ApotheosisLib::setTmpAction( ApotheosisLib::getActionIdByName('apoth_ass_main_search') );
		if( isset($requirements['groups']) ) {
			$requirements['groups'] = ApotheosisData::_( 'course.descendants', $requirements['groups'] );
		}
		$r2 = $requirements;
		$e = ApotheosisData::_( 'timetable.studentEnrolments', $r2 );
		$this->_enrolments = ApotheosisData::_( 'timetable.enrolmentHistory', $e, $requirements['valid_from'], $requirements['valid_to'] );
		
//		var_dump_pre( $requirements, 'requirements' );
//		var_dump_pre( $e, 'e' );
//		var_dump_pre( $this->_enrolments, 'enrolments' );
		
		foreach( $this->_enrolments as $pId=>$cur ) {
			foreach( $cur as $curId=>$hGroups ) {
				foreach( $hGroups as $hId ) {
					$groups[$hId] = $hId;
				}
			}
		}
		
		// then that lets us know what group assignments we want in our assessments
		$requirements['groups'] = $groups;
		ApotheosisLib::resetTmpAction();
		
		$this->_assessments = &$this->fAss->getInstances( $requirements );
		
		$aspEnrolments = array();
		foreach( $this->_assessments as $aId ) {
			$ass = $this->fAss->getInstance( $aId );
			$aspects = $ass->getAspects();
			$gs = $ass->getProperty('group_specific');
			
			$assGroups = array();
			$assEnrolments = array();
			
			foreach( $groups as $gId ) {
				if( $ass->hasGroup($gId) ) {
					$assGroups[$gId] = $gId;
				}
			}
			
			foreach( $this->_enrolments as $pId=>$cur ) {
				foreach( $cur as $curId=>$hGroups ) {
					// if it's a group-specific ass then include the enrolments of
					// all those who were ever in a group the ass is assigned to
					if( $gs ) {
						$use = false;
						foreach( $hGroups as $hId ) {
							if( !$use && isset($assGroups[$hId]) ) {
								$use = true;
							}
							$assEnrolments[$pId][$curId][$hId] = null;
						}
						if( !$use ) {
							unset( $assEnrolments[$pId][$curId] );
						}
					}
					// anyone can have a mark for non-group-specific regardles of enrolments
					else {
						$assEnrolments[$pId][$curId] = null;
					}
				}
				if( empty($assEnrolments[$pId]) ) {
					unset( $assEnrolments[$pId] ) ;
				}
			}
			
			foreach( $aspects as $aspId=>$asp ) {
				$aspEnrolments[$aspId] = $assEnrolments;
			}
		}
		
		$this->fAsp->loadAspectData( $aspEnrolments );
	}
	
	function getAssessments()
	{
		return $this->_assessments;
	}
	
	function sort( $by = 'group', $direction = 1 )
	{
		$this->_sortDir = ( ($direction == -1) ? -1 : 1 );
		if( is_numeric($by) ) {
			$this->_sortOn = &$this->fAsp->getInstance( (int)$by );
		}
		else {
			$this->_sortOn = 'group';
		}
		
		$this->_sorted = array();
		if( isset($this->_enrolments) && is_array($this->_enrolments) ) {
			foreach( $this->_enrolments as $pId=>$cur ) {
				foreach( $cur as $cId=>$hGroups ) {
					$this->_sorted[] = array( 'person'=>$pId, 'group'=>$cId, 'historicalGroups'=>$hGroups );
				}
			}
			usort( $this->_sorted, array($this, 'asortCB') );
		}
	}
	
	function asortCB( $a, $b )
	{
		if( $this->_sortOn == 'group' ) {
			$aVal = ApotheosisData::_( 'course.name', $a['group'] );
			$bVal = ApotheosisData::_( 'course.name', $b['group'] );
		}
		else {
			$t = $this->_sortOn->getMark( $a['person'], $a['group'] );
			$aVal = $t['mark'];
			$t = $this->_sortOn->getMark( $b['person'], $b['group'] );
			$bVal = $t['mark'];
		}
		
		if( $aVal == $bVal ) {
			// use name as a decider
			$aVal = ApotheosisData::_( 'people.displayName', $a['person'] );
			$bVal = ApotheosisData::_( 'people.displayName', $b['person'] );
			if( $aVal == $bVal ) {
				// use group id as a final decider
				if( $a['group'] == $b['group'] ) {
					$r = 0;
				}
				else {
					$r = ( ($a['group'] > $b['group']) ? 1 : -1 );
				}
			}
			else {
				$r = ( ($aVal > $bVal) ? 1 : -1 );
			}
		}
		else {
			$r = ( ($aVal > $bVal) ? 1 : -1 );
		}
		
		return $r * $this->_sortDir;
	}
	
	function getSortedEnrolments()
	{
		if( !isset($this->_sorted) ) {
			$this->sort();
		}
		return $this->_sorted;
	}
	
	function setShown( $aspects, $state = null, $invertSelection = false )
	{
		if( !is_array($aspects) ) {
			$aspects = array( $aspects );
		}
		if( $invertSelection ) {
			foreach( $this->_assessments as $aId ) {
				$a = &$this->fAss->getInstance( $aId );
				$asps = $a->getAspects();
				foreach( $asps as $aspId=>$asp ) {
					if( !isset($aspects[$aspId]) ) {
						$asp = &$this->fAsp->getInstance( $aspId );
						$asp->setIsShown( $state );
					}
				}
			}
		}
		else {
			foreach( $aspects as $aspId ) {
				$asp = &$this->fAsp->getInstance( $aspId );
				$asp->setIsShown( $state );
			}
		}
	}
	
	function setEdits( $aId, $state = null )
	{
		$a = &$this->fAss->getInstance( $aId );
		$a->setEditsOn( $state );
	}
	
	function getEdits()
	{
		$edits = false;
		foreach( $this->_assessments as $aId ) {
			$a = &$this->fAss->getInstance( $aId );
			if( $a->getEditsOn() ) {
				$edits = true;
				break;
			}
		}
		
		return $edits;
	}
	
	/**
	 * Save the mark in the most recent valid group (where the assessment is assigned to a group that the pupil was in)
	 * or with no group if appropriate to the assessment 
	 * Enter description here ...
	 * @param unknown_type $marks
	 */
	function saveMarks( $marks )
	{
		foreach( $marks as $aspId=>$aspData ) {
			$asp = &$this->fAsp->getInstance( $aspId );
			$a = &$asp->getAssessment();
			$gs = $a->getProperty('group_specific');
			foreach( $aspData as $pId=>$groups ) {
				foreach( $groups as $gId=>$mark ) {
					// check if the mark can be assigned and work out which group id to use
					$gId2 = null;
					if( $gs ) {
						$assigned = false;
						if( is_array($this->_enrolments[$pId][$gId]) ) {
							$v = reset( $this->_enrolments[$pId][$gId] );
							do {
								$assigned = $assigned || $a->hasGroup( $v );
							} while( !$assigned && (($v = next($this->_enrolments[$pId][$gId])) !== false) );
							$gId2 = $v;
						}
						else {
							$assigned = $a->hasGroup( $gId );
						}
						
					}
					else {
						$assigned = true; // non-group-specific assessments don't care
					}
					// ... and assign the mark if all was ok
					if( $assigned ) {
						$asp->setMark( $pId, $gId, $mark, $gId2 );
					}
					
				}
			}
			$asp->commitMarks();
		}
	}
}
?>