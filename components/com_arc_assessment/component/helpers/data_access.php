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
 * Data Access Helper
 *
 * @author		lightinthedark <code@lightinthedark.org.uk>
 * @package		Apotheosis
 * @subpackage Assessments
 * @since 0.1
 */
class ApotheosisData_Assessment extends ApotheosisData
{
	function info()
	{
		return 'Assessment component installed';
	}
	
	
	function markStyleInfo( $style )
	{
		if( !isset($this->styleInfoCache) ) {
			$this->styleInfoCache = array();
		}
		
		if( !isset($this->styleInfoCache[$style]) ) {
			$db = &JFactory::getDBO();
			$db->setQuery( 'SELECT '.$db->nameQuote( 'style' ).', '.$db->nameQuote( 'type' ).', '.$db->nameQuote( 'format' )
				."\n".'FROM #__apoth_sys_markstyles_info'
				."\n".'WHERE style = '.$db->Quote($style) );
			$this->styleInfoCache[$style] = $db->loadAssoc();
		}
		return $this->styleInfoCache[$style];
	}
	
	function prepare( $aspId = null, $pId = null, $gId = null, $validFrom = null, $validTo = null, $limPeople = null, $limGroups = null, $restrict = true )
	{
//		var_dump_pre( func_get_args(), 'args for prepare' );
		$this->fAss = &ApothFactory::_( 'assessment.assessment' );
		$this->fAsp = &ApothFactory::_( 'assessment.aspect' );
		
		$this->fAss->setParam( 'restrict', $restrict );
		$this->fAsp->setParam( 'restrict', $restrict );
		
		$requirements['valid_from'] = ( is_null($validFrom) ? date( 'Y-m-d H:i:s' ) : $validFrom );
		$requirements['valid_to']   = ( is_null($validTo)   ? date( 'Y-m-d H:i:s' ) : $validTo );
		
		if( !is_null($aspId) ) {
			$requirements['aspects'] = ( is_array($aspId) ? $aspId : array( $aspId ) );
		}
		
		if( !is_null($pId) ) {
			$requirements['pupil'] = ( is_array($pId) ? $pId : array( $pId ) );
		}
		
		if( !is_null($gId) ) {
			$requirements['groups'] = ( is_array($gId) ? $gId : array( $gId ) );
		}
		
		// *** from here down is (except for a couple of variable names) a direct copy
		// from the model. Kinda feel we could be a bit smarter about this
		$e = ApotheosisData::_( 'timetable.studentEnrolments', $requirements, $limPeople, $limGroups );
		$this->_enrolments = ApotheosisData::_( 'timetable.enrolmentHistory', $e, $requirements['valid_from'], $requirements['valid_to'] );
		
//		var_dump_pre( $e, 'e' );
//		var_dump_pre( $this->_enrolments, 'enrolments' );
		$groups = array();
		foreach( $this->_enrolments as $pId=>$cur ) {
			foreach( $cur as $curId=>$hGroups ) {
				foreach( $hGroups as $hId ) {
					$groups[$hId] = $hId;
				}
			}
		}
		
		// then that lets us know what group assignments we want in our assessments
		$requirements['groups'] = $groups;
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
}
?>