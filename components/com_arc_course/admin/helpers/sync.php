<?php
/**
 * @package     Arc
 * @subpackage  Course
 * @copyright   Copyright (C) 2005 Punnet. All rights reserved. See COPYRIGHT_ARC.txt
 * @license     http://www.gnu.org/licenses/gpl.html GNU/GPL. See LICENSE_ARC.txt
 * Arc is free software: you can redistribute it and/or modify
 * it under the terms of version 3 of the GNU General Public License
 * as published by the Free Software Foundation.
 * The disclaimer of warranty as stated in the GPL applies to this program
 */

defined( '_JEXEC' ) or die( 'Restricted access' );

/**
 * Course Sync Helper
 *
 * @author     Punnet - Arc Team <arc_developers@pun.net>
 * @package    Arc
 * @subpackage Course
 * @since      1.6.5
 */
class ArcSync_Course extends ArcSync
{
	/**
	 * Import all data about courses
	 *
	 * @param array $params  Values from the form used to originally add the job
	 *                       'parentage'=>Do we want to adopt the parentage as given from SIMS (true),
	 *                       or just put everything under the root group (false)?
	 * @param array $jobs  Array of jobs. Each job is an array with all that job's settings
	 */
	function importCourses( $params, $jobs )
	{
		$tablesArray = array( '#__apoth_cm_courses', '#__apoth_cm_courses_ancestry', '#__apoth_cm_courses_pseudo_map', '#__apoth_cm_courses_pastoral_map' );
		ApotheosisLibDb::disableDBChecks( $tablesArray );
		
		$this->_tmpIds = array();
		$this->_complete = (bool)$params['complete'];
		$parentage = (bool)$params['parentage'];
		
		timer( 'Importing groups' );
		
		// Pastoral
		$j = $this->jobSearch( array('call'=>'arc_course_pastoral'), $jobs );
		$xml = $this->_loadReport( $jobs[$j], 'progressive' );
		$this->_srcId = $jobs[$j]['src'];
		$this->_pastoral = array();
		while( $r = $xml->next('record') ) {
			$this->_rawToObjects( 'arc_course_pastoral', $r );
		}
		$xml->free();
		
		$this->_importGroups( 'pastoral', $parentage );
		unset( $this->_existingPastoral );
		unset( $this->_externalPastoral );
		unset( $this->_pastoral );
		timer( 'Imported pastoral groups' );
		
		// Non-pastoral subjects
		$j = $this->jobSearch( array( 'call'=>'arc_course_curriculum' ), $jobs );
		$xml = $this->_loadReport( $jobs[$j], 'progressive' );
		$this->_srcId = $jobs[$j]['src'];
		$this->_non = array();
		while( $r = $xml->next('record') ) {
			$this->_rawToObjects( 'arc_course_curriculum.non', $r );
		}
		$xml->free();
		
		$this->_importGroups( 'non', $parentage );
		unset( $this->_non );
		timer( 'Imported subject groups' );
		
		// Non-pastoral classes
		$j = $this->jobSearch( array( 'call'=>'arc_course_curriculum' ), $jobs );
		$xml = $this->_loadReport( $jobs[$j], 'progressive' );
		$this->_srcId = $jobs[$j]['src'];
		$this->_setExisting( 'non', true );
		$this->_setExternal( 'non', true );
		$this->_normal = array();
		while( $r = $xml->next('record') ) {
			$this->_rawToObjects( 'arc_course_curriculum.normal', $r );
		}
		$xml->free();
		
		$this->_importGroups( 'normal', $parentage );
		timer( 'Imported class groups' );
		
		timer( 'Imported all groups' );
		
		ApotheosisLibDb::updateAncestry( '#__apoth_cm_courses' );
		timer( 'Updated andestry' );
		
		$this->_updateYears();
		timer( 'Updated years' );
		
		$this->_cleanPseudo();
		timer( 'Cleaned pseudo map' );
		
		ApotheosisLibDb::enableDBChecks( $tablesArray );
		
		return true; // *** this would be better as an indicator of actual success rather than the assumption of success
	}
	
	function _rawToObjects( $rpt, $r )
	{
		switch($rpt) {
		case( 'arc_course_curriculum.non' ):
			$rawIds = $r->childData( 'multiple_id' );
			if( !is_null($rawIds) ) {
				$ids = explode( ',', $rawIds );
			}
			if( (!is_null($ids) && (array_search(0, $ids) === false)) || is_null($ids) ) {
				$type = 'non';
				$this->_setExisting( $type );
				$this->_setExternal( $type );
				
				$arcId = $r->childData( 'arc_subject_id' );
				$extId = is_null($ids) ? null : $ids[0];
				
				$groupIds = $this->_getGroupIds( $arcId, $extId, $type );
				$start_date = $this->_cleanDate( $r->childData('start') );
				$end_date   = $this->_cleanDate( $r->childData('end') );
				
				if( !isset($this->_non[$groupIds['arcId']]) ) {
					$obj = new stdClass();
					$obj->id            = $groupIds['arcId'];
					$obj->src           = $this->_srcId;
					$obj->ext_course_id = $groupIds['extId'];
					$obj->shortname     = $r->childData( 'subj_short' );
					$obj->fullname      = $r->childData( 'subj_full' );
					$obj->type          = 'non';
					$obj->ext_type      = 'subject';
					$obj->start_date    = $start_date;
					$obj->end_date      = $end_date;
					
					$this->_non[$groupIds['arcId']] = $obj;
				}
				else {
					$cur = &$this->_non[$groupIds['arcId']];
					$cur->start_date = min( $cur->start_date, $start_date );
					$cur->end_date = ( (is_null($cur->end_date) || is_null($end_date)) ? null : max($cur->end_date, $end_date) );
					unset( $cur );
				}
			}
			break;
			
		case( 'arc_course_curriculum.normal' ):
			$rawIds = $r->childData( 'multiple_id' );
			if( !is_null($rawIds) ) {
				$ids = explode( ',', $rawIds );
			}
			if( (!is_null($ids) && (array_search(0, $ids) === false)) || is_null($ids) && !is_null($end_date) ) {
				$type = 'normal';
				$this->_setExisting( $type );
				$this->_setExternal( $type );
				$this->_setExisting( 'non' );
				$this->_setExternal( 'non' );
				
				$arcId = $r->childData( 'arc_class_id' );
				$extId = is_null($ids) ? null : $ids[1];
				
				$groupIds = $this->_getGroupIds( $arcId, $extId, $type );
				$start_date = $this->_cleanDate( $r->childData('start') );
				$end_date   = $this->_cleanDate( $r->childData('end') );
				
				if( !isset($this->_normal[$groupIds['arcId']]) ) {
					$obj = new stdClass();
					$obj->id            = $groupIds['arcId'];
					$obj->src           = $this->_srcId;
					$obj->ext_course_id = $groupIds['extId'];
					$obj->shortname     = $r->childData( 'class_short' );
					$obj->fullname      = $r->childData( 'class_full' );
					$obj->type          = 'normal';
					$obj->ext_type      = 'class';
					$obj->parent        = $r->childData( 'arc_subject_id' );
					$obj->start_date    = $start_date;
					$obj->end_date      = $end_date;
					
					$this->_normal[$groupIds['arcId']] = $obj;
					
					if( is_null($obj->parent) ) {
						$obj->parent = $this->_externalNon[$ids[0]];
					}
					
				}
				else {
					$cur = &$this->_normal[$groupIds['arcId']];
					$cur->start_date = min( $cur->start_date, $start_date );
					$cur->end_date = max( $cur->end_date, $end_date );
					unset( $cur);
				}
			}
			break;
		
		case( 'arc_course_pastoral' ):
			$rawIds = $r->childData( 'multiple_id' );
			if( !is_null($rawIds) ) {
				$ids = explode( ',', $rawIds );
			}
			if( (!is_null($rawIds) && (array_search(0, $ids) === false)) || is_null($rawIds) ) {
				// pastoral
				$type = 'pastoral';
				$this->_setExisting( $type );
				$this->_setExternal( $type );
				
				$arcId = $r->childData( 'arc_group_id' );
				$extId = is_null($ids) ? null : $ids[0];
				
				$groupIds = $this->_getGroupIds( $arcId, $extId, $type );
				// only consider incoming data if it has valid ids
				if( $groupIds ) {
					$start_date = $this->_cleanDate( $r->childData('start') );
					$end_date   = $this->_cleanDate( $r->childData('end') );
					
					if( !isset($this->_pastoral[$groupIds['arcId']]) ) {
						$obj = new stdClass();
						$obj->id            = $groupIds['arcId'];
						$obj->src           = $this->_srcId;
						$obj->ext_course_id = $groupIds['extId'];
						$obj->shortname     = $r->childData( 'class_short' );
						$obj->fullname      = $r->childData( 'class_full' );
						$obj->type          = 'pastoral';
						$obj->ext_type      = 'pastoral';
						$obj->start_date    = $start_date;
						$obj->end_date      = $end_date;
						
						$this->_pastoral[$groupIds['arcId']] = $obj;
					}
					else {
						$cur = &$this->_pastoral[$groupIds['arcId']];
						$cur->start_date = min( $cur->start_date, $start_date );
						$cur->end_date = ( (is_null($cur->end_date) || is_null($end_date)) ? null : max($cur->end_date, $end_date) );
						unset( $cur );
					}
				}
			}
			break;
		}
	}
	
	/**
	 * Columns required for SIMS XML report creation from an uploaded CSV
	 * 
	 * @param string $report  The report name
	 * @return array $columns  Array of column names as keys with description as value
	 */
	function CSVcolumns( $report )
	{
		$columns = array();
		
		switch( $report ) {
		case( 'arc_course_pastoral' ):
			$columns['Arc Group ID'] = 'Arc Group ID or blank for a new pastoral class';
			$columns['Unique ID'] = 'User generated unique class ID if available';
			$columns['class_short'] = 'Short class name';
			$columns['class_full'] = 'Full class name';
			$columns['Start'] = 'Start date and time as yyyy-mm-ddThh:mm:ss for example 2011-07-28T14:02:00';
			$columns['End'] = 'End date and time as yyyy-mm-ddThh:mm:ss for example 2011-07-28T14:02:00';
			break;
			
		case( 'arc_course_curriculum' ):
			$columns['Arc Subject ID'] = 'Arc Group ID or blank for a new class';
			$columns['Arc Class ID'] = 'Arc Group ID or blank for a new class';
			$columns['Unique Subject ID'] = 'User generated unique subject ID';
			$columns['Unique Class ID'] = 'User generated unique class ID';
			$columns['Subj_short'] = 'Short subject name';
			$columns['Subj_full'] = 'Full subject name';
			$columns['Class_short'] = 'Short class name';
			$columns['Class_full'] = 'Full class name';
			$columns['Start'] = 'Start date and time as yyyy-mm-ddThh:mm:ss for example 2011-07-28T14:02:00';
			$columns['End'] = 'End date and time as yyyy-mm-ddThh:mm:ss for example 2011-07-28T14:02:00';
			break;
		}
		
		return $columns;
	}
	
	/**
	 * Goes through our list of groups and imports them into the courses table
	 *
	 * @param string $type  The subset of groups to import (non, normal, pastoral)
	 * @param boolean $parentage  Do we want to adopt the parentage as given from SIMS, or just put everything under the root group
	 */
	function _importGroups( $type, $parentage )
	{
		$insertVals = array();
		$updateVals = array();
		
		$nowDate = date( 'Y-m-d' );
		$nowTime = date( 'Y-m-d H:i:s' );
		$yesterdayDate = date( 'Y-m-d', strtotime('yesterday') );
		
		$root = ApotheosisLibDb::getRootItem( '#__apoth_cm_courses' );
		$this->_groups = &$this->{'_'.$type};
		$db = &JFactory::getDBO();
		
		switch( $type ) {
		case( 'pastoral' ):
			$pastoral = $this->_getPastoralId(); // 'root' pastoral id
			break;
		}
		
		foreach( $this->_groups as $id=>$group ) {
			
			// sort out parentage
			if( $parentage ) {
				switch( $type ) {
				case( 'non' ):
					$group->parent = $root;
					break;
				
				case( 'pastoral' ):
					$group->parent = $pastoral;
					break;
				}
			}
			else {
				unset( $group->parent );
			}
			
			// prepare objects for inclusion in db setting arrays
			if( $id < 0 ) {
				if( !$parentage ) {
					$group->parent = $root;
				}
				$group->id = null;
				$group->time_created = $nowTime;
				$insertVals[] = $group;
			}
			else {
				$group->time_modified = $nowTime;
				$updateVals[] = $group;
			}
			
			unset( $this->_groups[$id] );
			unset( $this->{'_existing'.ucfirst($type)}[$id] );
		}
		
		// terminate courses that exist but haven't been imported this time
		if( $this->_complete && !empty($this->{'_existing'.ucfirst($type)}) ) {
			foreach( $this->{'_existing'.ucfirst($type)} as $id=>$c ) {
				if( ($c->start_date < $nowDate) && (is_null($c->end_date) || ($c->end_date > $nowDate)) ) {
					$ids[] = $db->Quote($id);
				}
			}
			if( !empty($ids) ) {
				$query = 'UPDATE '.$db->nameQuote('#__apoth_cm_courses')
					."\n".'SET '.$db->nameQuote('end_date').' = '.$db->Quote( $yesterdayDate )
					."\n".'WHERE '.$db->nameQuote('id').' IN ('.implode(', ', $ids).')';
				$db->setQuery( $query );
				$db->Query();
			}
		}
		
		// apply changes to db
		ApotheosisLibDb::insertList( '#__apoth_cm_courses', $insertVals );
		ApotheosisLibDb::updateList( '#__apoth_cm_courses', $updateVals, array('id') );
		timer( 'Imported '.$type.' courses ('.count($insertVals).' inserts, '.count($updateVals).' updates)' );
		
		// process pastoral groups
		if( $type == 'normal' ) {
			
			// Find the ids of the subjects whose classes are tied to pastoral classes
			$paramsObj = &JComponentHelper::getParams( 'com_arc_attendance' );
			$pGroups = $paramsObj->get( 'pastoral_subjects' );
			$pGroups = explode( ';', $pGroups );
			foreach( $pGroups as $k=>$v ) {
				$pGroups[$k] = $db->Quote( $v );
			}
			
			$cm = $db->nameQuote('cm');
			$cm2 = $db->nameQuote('cm2');
			$query = 'SELECT '.$cm.'.'.$db->nameQuote('id').', '.$cm.'.'.$db->nameQuote('fullname')
				."\n".'FROM '.$db->nameQuote('#__apoth_cm_courses').' AS '.$cm
				."\n".'INNER JOIN '.$db->nameQuote('#__apoth_cm_courses').' AS '.$cm2
				."\n".'   ON '.$cm2.'.'.$db->nameQuote('id').' = '.$cm.'.'.$db->nameQuote('parent')
				."\n".'  AND '.$cm2.'.'.$db->nameQuote('shortname').' IN ('.implode(', ', $pGroups).')'
				."\n".'  AND '.$cm.'.'.$db->nameQuote('ext_course_id').' IS NOT NULL'
				."\n".'  AND '.$cm.'.'.$db->nameQuote('type').' = '.$db->Quote('normal')
				."\n".'  AND '.$cm.'.'.$db->nameQuote('src').' = '.$db->Quote($this->_srcId)
				."\n".'  AND '.ApotheosisLibDb::dateCheckSql( $cm.'.'.$db->nameQuote('start_date'), $cm.'.'.$db->nameQuote('end_date'), $nowDate, $nowDate )
				."\n".'  AND '.ApotheosisLibDb::dateCheckSql( $cm2.'.'.$db->nameQuote('start_date'), $cm2.'.'.$db->nameQuote('end_date'), $nowDate, $nowDate );
			$db->setQuery( $query );
			$pastoralClasses = $db->loadObjectList('id');
			
			// Find the data for the pastoral classes so we can try to match up those classes to pastoral groups
			$query = 'SELECT '.$db->nameQuote('id').', '.$db->nameQuote('ext_course_id').', '.$db->nameQuote('ext_type').', '.$db->nameQuote('parent').', '.$db->nameQuote('fullname').', '.$db->nameQuote('start_date').', '.$db->nameQuote('end_date')
				."\n".'FROM '.$db->nameQuote('#__apoth_cm_courses')
				."\n".'WHERE '.$db->nameQuote('ext_course_id').' IS NOT NULL'
				."\n".'  AND'.$db->nameQuote('type').' = '.$db->Quote('pastoral')
				."\n".'  AND '.$db->nameQuote('src').' = '.$db->Quote($this->_srcId);
			$db->setQuery( $query );
			$pastoralGroups = $db->loadObjectList('id');
			
			$query = 'SELECT *'
				."\n".' FROM '.$db->nameQuote('#__apoth_cm_pastoral_map');
			$db->setQuery( $query );
			$pastoralMaps = $db->loadObjectList();
			
			$insertPastoralVals = array();
			foreach( $pastoralClasses as $pClass ) {
				// This class corresponds to some pastoral group, so needs to be added to the pastoral_map
				$minLev = 9999; // just a large number
				$bestFit = null;
				
				$mapObj = new stdClass();
				$mapObj->course = $pClass->id;
				$mapObj->pastoral_course = NULL;
				$haystack = strtolower( $pClass->fullname );
				
				foreach( $pastoralGroups as $p ) {
					$needle = strtolower( $p->fullname );
					if( strpos($haystack, $needle) !== false ) {
						$mapObj->pastoral_course = $p->id;
						break;
					}
					elseif( is_null($mapObj->pastoral_course) ) {
						$lev = levenshtein( $needle, $haystack );
						if( $lev < $minLev ) {
							$bestFit = $p->id;
							$minLev = $lev;
						}
					}
				}
				
				if( is_null($mapObj->pastoral_course) && !empty($bestFit) ) {
					$mapObj->pastoral_course = $bestFit;
				}
				
				if( !is_null($mapObj->pastoral_course) && (ApotheosisLibArray::array_search_partial($mapObj, $pastoralMaps) === false) ) {
					$pastoralMaps[] = $mapObj;
					$insertPastoralVals[] = $mapObj;
				}
			}
			
			ApotheosisLibDb::insertList( '#__apoth_cm_pastoral_map', $insertPastoralVals );
			timer( count($insertPastoralVals).' pastoral classes mapped' );
		}
		
		unset( $this->_groups );
	}
	
	/**
	 * Find the id of the pastoral subject
	 */
	function _getPastoralId()
	{
		$db = &JFactory::getDBO();
		$query = 'SELECT '.$db->nameQuote('id')
			."\n".'FROM '.$db->nameQuote('#__apoth_cm_courses')
			."\n".'WHERE '.$db->nameQuote('parent').' = '.$db->Quote( ApotheosisLibDb::getRootItem( '#__apoth_cm_courses' ) )
			."\n".'  AND '.$db->nameQuote('ext_course_id').' IS NULL'
			."\n".'  AND '.$db->nameQuote('fullname').' = '.$db->Quote('Pastoral');
		$db->setQuery( $query );
		
		return $db->loadResult();
	}
	
	/**
	 * Set the year groups (and reportable-ness) for groups
	 */
	function _updateYears()
	{
		$db = &JFactory::getDBO();
		$query = 'SELECT @now := NOW();'
			."\n"
			."\n".'UPDATE '.$db->nameQuote('#__apoth_cm_courses')
			."\n".'SET '.$db->nameQuote('year').' = CAST( '.$db->nameQuote('fullname').' AS UNSIGNED INTEGER )'
			."\n".'  , '.$db->nameQuote('time_modified').' = @now'
			."\n".'  , '.$db->nameQuote('reportable').' = '.$db->Quote('1')
			."\n".'WHERE '.$db->nameQuote('type').' IN ( '.$db->Quote('normal').', '.$db->Quote('pastoral').' )'
			."\n".'  AND ( (CAST('.$db->nameQuote('fullname').' AS UNSIGNED INTEGER) != '.$db->nameQuote('year').') OR ('.$db->nameQuote('year').' IS NULL) )'
			."\n".'  AND '.$db->nameQuote('deleted').' = '.$db->Quote('0').';';
		$db->setQuery( $query );
		$db->queryBatch();
	}
	
	/**
	 * Clean the pseudo-map table of any references to no-longer-valid course ids
	 */
	function _cleanPseudo()
	{
		$db = &JFactory::getDBO();
		$query = 'CREATE TEMPORARY TABLE tmp_dead_pseudo AS'
			."\n".' SELECT m.*, c1.id AS id1'
			."\n".' FROM `jos_apoth_cm_pseudo_map` AS m'
			."\n".' LEFT JOIN jos_apoth_cm_courses AS c1'
			."\n".'   ON c1.id = m.course'
			."\n".' LEFT JOIN jos_apoth_cm_courses AS c2'
			."\n".'   ON c2.id = m.twin'
			."\n".' HAVING id1 IS NULL'
			."\n".'     OR id2 IS NULL;'
			."\n".' '
			."\n".' DELETE `jos_apoth_cm_pseudo_map`'
			."\n".' FROM `jos_apoth_cm_pseudo_map` AS m'
			."\n".' INNER JOIN tmp_dead_pseudo AS td'
			."\n".'    ON td.course = m.course'
			."\n".'   AND td.twin = m.twin;';
		$db->setQuery($query);
		$db->queryBatch();
	}
	
	/**
	 * Set a class variable to store existing groups indexed on Arc ID
	 * @param string $type  Type of group to set
	 * @param boolean $refresh  Do we want a fresh set of data even if we already cached some?
	 */
	function _setExisting( $type, $refresh = false )
	{
		if( $refresh ) {
			unset( $this->{'_existing'.ucfirst($type)} );
		}
		
		if( !isset($this->{'_existing'.ucfirst($type)}) ) {
			$db = &JFactory::getDBO();
			$query = 'SELECT '.$db->nameQuote('id').', '.$db->nameQuote('src').', '.$db->nameQuote('ext_course_id').', '.$db->nameQuote('ext_type').', '.$db->nameQuote('parent').', '.$db->nameQuote('fullname').', '.$db->nameQuote('start_date').', '.$db->nameQuote('end_date')
				."\n".'FROM '.$db->nameQuote('#__apoth_cm_courses')
				."\n".'WHERE '.$db->nameQuote('type').' = '.$db->Quote($type)
				."\n".'  AND '.$db->nameQuote('src').' = '.$db->Quote($this->_srcId);
			$db->setQuery( $query );
			
			$this->{'_existing'.ucfirst($type)} = $db->loadObjectList('id');
		}
	}
	
	/**
	 * Set an array of existing Arc IDs keyed on Arc Group ID
	 * @param string $type  Type of group to set
	 * @param boolean $refresh  Do we want a fresh set of data even if we already cached some?
	 */
	function _setExternal( $type, $refresh = false )
	{
		if( $refresh ) {
			unset( $this->{'_external'.ucfirst($type)} );
		}
		
		if( !isset($this->{'_external'.ucfirst($type)}) ) {
			foreach( $this->{'_existing'.ucfirst($type)} as $course ) {
				if( !is_null($course->ext_course_id) ) {
					$this->{'_external'.ucfirst($type)}[$course->ext_course_id] = $course->id;
				}
			}
		}
	}
	
	/**
	 * Get all the ids for a group based on raw ids from data.xml
	 * 
	 * @param mixed $arcId  Null if no arc id or string if provided
	 * @param mixed $extId  Null if no ext id or string if provided
	 * @param string $type  The type of group
	 * @return mixed $retVal  Each id or false if arc ID source is invalid
	 */
	function _getGroupIds( $arcId, $extId, $type )
	{
		// first we must check that if an Arc ID was provided that it matches the data source
		if( is_null($arcId) || (!is_null($arcId) && (isset($this->{'_existing'.ucfirst($type)}[$arcId]))) ) {
			
			// get Arc group id from ext id of known existing group
			if( is_null($arcId) && !is_null($extId) && isset($this->{'_external'.ucfirst($type)}[$extId]) ) {
				$arcId = $this->{'_external'.ucfirst($type)}[$extId];
			}
			// or we have no Arc id but a new ext id so track the ext id and what new arc id was assigned
			elseif( is_null($arcId) && !is_null($extId) ) {
				if( array_key_exists($extId, $this->_tmpIds) ) {
					$arcId = $this->_tmpIds[$extId];
				}
				else {
					$arcId = $this->_tmpIds[$extId] = $this->_getNewId();
				}
			}
			// or we have no Arc id or ext id at all
			elseif( is_null($arcId) ) {
				$arcId = $this->_getNewId();
			}
			// or we have an Arc id but not been given an ext id so add the ext id if it exists
			elseif( !is_null($arcId) && is_null($extId) ) {
				$extId = $this->{'_existing'.ucfirst($type)}[$arcId]->ext_course_id;
			}
			
			$retVal['arcId'] = $arcId;
			$retVal['extId'] = $extId;
		}
		else {
			$retVal = false;
		}
		
		return $retVal;
	}
	
	/**
	 * Generate a 'new' negative ext group id
	 * 
	 * @return string  New negative group id
	 */
	function _getNewId()
	{
		// we set new ids to be negative so as not to interfere with valid array keys later
		if( !isset($this->newGroup) ) {
			$this->newGroup = -1;
		}
		
		return $this->newGroup--;
	}
}
?>