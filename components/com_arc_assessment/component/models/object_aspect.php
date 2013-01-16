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
 * Assessment Factory Aspect
 */
class ApothFactory_Assessment_Aspect extends ApothFactory
{
	/**
	 * Creates a blank instance with the given id
	 * @param int $id  The id that should be used for the dummy object. Must be negative if supplied.
	 */
	function &getDummy( $id = null )
	{
		if( is_null( $id ) ) {
			$id = $this->_getDummyId();
		}
		else if( $id >= 0 ) {
			$r = null;
			return $r;
		}
		$r = &$this->_getInstance( $id );
		if( is_null($r) ) {
			$r = new ApothAspect( array( 'id'=>$id
				, 'title'=>'Title'
				, 'short'=>'Ttl'
				, 'boundaries'=>'{'
					.'"mark_style":"grades",'
					.'"display_style":"",'
					.'"mark_values":{"A*":96,"A":"85.00","B":"75.00","C":"65.00","D":"55.00","E":"45.00","F":"35.00","G":"25.00","U":"10.00","N":"0.00"},'
					.'"display_bounds":{"A*":"100.00","A":"90.00","B":"80.00","C":"70.00","D":"60.00","E":"50.00","F":"40.00","G":"30.00","U":"20.00","N":"0.01"}'
					.'}'
				, 'shown'=>1
				, 'deleted'=>0) );
			$this->_addInstance( $id, $r );
		}
		return $r;
	}
	
	/**
	 * Returns the requested aspect object, loading if required
	 * 
	 * @param int $id  ID of the aspect object to return
	 * @param array $data  Array of data to be used to construct the apsect object
	 * @param bool $load  Whether or not to load data from DB before overwriting with $data
	 * @return object $aspObj  The requested aspect object
	 */
	function &getInstance( $id, $data = array(), $load = true )
	{
		$aspObj = &$this->_getInstance( $id );
		$restrict = $this->getParam( 'restrict' );
		
		if( is_null($aspObj) ) {
			$db = &JFactory::getDBO();
			$aspectQuery = 'SELECT *'
				."\n".'FROM '.$db->nameQuote( '#__apoth_ass_aspect_instances' ).' AS '.$db->nameQuote('ai')
				.($restrict ? "\n".'~LIMITINGJOIN~' : '' )
				."\n".'WHERE '.$db->nameQuote('ai').'.'.$db->nameQuote( 'id' ).' = '.$db->Quote( $id )
				."\n".'  AND '.$db->nameQuote('ai').'.'.$db->nameQuote( 'deleted' ).' != '.$db->Quote( '1' );
			$db->setQuery( $restrict ? ApotheosisLibAcl::limitQuery($aspectQuery, 'assessment.assessments', 'ai', 'assessment_id') : $aspectQuery );
			$data = $db->loadAssoc();
			
			$aspObj = new ApothAspect( $data );
			$this->_addInstance( $id, $aspObj );
		}
		
		return $aspObj;
	}
	
	/**
	 * Returns an ID-indexed array of aspect objects matching the requirements,
	 * loading and storing if required
	 * 
	 * @param array $requirements  Array of requirements used to pull out aspect
	 * @param array $assObj  Reference to an assessment obj
	 * @return array $aspObjs  array of aspect IDs matching the requirements
	 */
	function &getInstances( $requirements, $init = true )
	{
		$sId = $this->_getSearchId( $requirements );
		$aspIds = $this->_getInstances( $sId );
		$restrict = $this->getParam( 'restrict' );
		
		if( is_null($aspIds) ) {
			$db = &JFactory::getDBO();
			$join = ( $restrict ? array('~LIMITINGJOIN~') : array() );
			
			foreach( $requirements as $col=>$val ) {
				if( is_array($val) ) {
					if( empty($val) ) {
						continue;
					}
					foreach( $val as $k=>$v ) {
						$val[$k] = $db->Quote( $v );
					}
					$assignPart = ' IN ('.implode( ', ',$val ).')';
				}
				else {
					$assignPart = ' = '.$db->Quote( $val );
				}
				switch( $col ) {
				case( 'assId' ):
					$where[] = $db->nameQuote('assessment_id').$assignPart;
					break;
				}
			}
			
			// Pull out aspects based on requirements
			$aspectQuery = 'SELECT DISTINCT ai.*'
				."\n".'FROM '.$db->nameQuote('#__apoth_ass_aspect_instances').' AS '.$db->nameQuote( 'ai' )
				.( empty($join)  ? '' : "\n".implode("\n", $join) )
				."\n".'WHERE '.$db->nameQuote('deleted').' = '.$db->Quote('0')
				.( empty($where) ? '' : "\n AND ".implode("\n".'  AND ', $where) )
				."\n".'ORDER BY ai.id';
			$db->setQuery( $restrict ? ApotheosisLibAcl::limitQuery($aspectQuery, 'assessment.assessments', 'ai', 'assessment_id') : $aspectQuery );
			$aspObjsData = $db->loadAssocList( 'id' );
			$aspIds = array_keys( $aspObjsData );
			$this->_addInstances( $sId, $aspIds );
			
			
			if( $init ) {
				$existing = $this->_getInstances();
				$newIds = array_diff( $aspIds, $existing );
				
				// initialise and cache
				foreach( $newIds as $id ) {
					$data = $aspObjsData[$id];
					$aspObj = new ApothAspect( $data );
					$this->_addInstance( $id, $aspObj );
					unset( $aspObj );
				}
			}
		}
		
		return $aspIds;
	}
	
	function commitInstance( $id )
	{
		$a = &$this->getInstance( $id );
		
		if( is_null($a) ) {
			return null;
		}
		$db = &JFactory::getDBO();
		$data = $a->getData();
		$id = $data->id;
		$isNew = ( $id < 0 );
		
		$u = &ApotheosisLib::getUser();
		
		$now = date('Y-m-d H:i:s');
		if( $isNew ) {
			unset( $data->id );
			$data->created_by = $u->person_id;
			$data->created_on = $now;
			$queryStart = 'INSERT INTO '.$db->nameQuote('#__apoth_ass_aspect_instances');
			$queryEnd = '';
		}
		else {
			$data->modified_by = $u->person_id;
			$data->modified_on = $now;
			$queryStart = 'UPDATE '.$db->nameQuote('#__apoth_ass_aspect_instances');
			$queryEnd = 'WHERE '.$db->nameQuote('id').' = '.$db->Quote($id);
		}
		
		foreach( $data as $col=>$val ) {
			if( is_null($val) || $val === '' ) {
				$values[] = $db->nameQuote($col).' = NULL';
			}
			else {
				$values[] = $db->nameQuote($col).' = '.$db->Quote($val);
			}
		}
		$query = $queryStart
			."\n".'SET '
			."\n".implode( "\n, ", $values )
			."\n".$queryEnd;
		
		$db->setQuery( $query );
		$db->Query();
		
		$this->_clearCachedInstances( $id );
		$this->_clearCachedSearches();
		
		if( $isNew ) {
			$query = 'SELECT LAST_INSERT_ID()';
			$db->setQuery( $query );
			$id = $db->loadResult();
			$a->setId( $id );
		}
	}
	
	function loadAspectData( $aspEnrolments )
	{
//		var_dump_pre( $aspEnrolments[2393], 'aspEnrolments for 2393', true );
		$db = &JFactory::getDBO();
		$ng = true;
		if( !is_array($aspEnrolments) ) {
			return;
		}
		foreach( $aspEnrolments as $aspId=>$pupils ) {
			$aspList[] = $db->Quote( $aspId );
			foreach( $pupils as $pId=>$cur ) {
				if( !isset($pList[$pId]) ) {
					$pList[$pId] = $db->Quote( $pId );
				}
				foreach( $cur as $cId=>$hGroups ) {
					if( is_null($hGroups) ) {
						$ng = true;
					}
					else {
						foreach( $hGroups as $hId=>$trash ) {
							if( !isset($gList[$hId]) ) {
								$gList[$hId] = $db->Quote( $hId );
							}
						}
					}
				}
			}
		}
		$where = array();
		if( !empty($aspList) ) { $where[] = $db->nameQuote('aspect_id').' IN ('.implode(',', $aspList).')'; }
		if( !empty($pList)   ) { $where[] = $db->nameQuote('pupil_id') .' IN ('.implode(',', $pList)  .')'; }
		if( !empty($gList)   ) { $where[] = '('.$db->nameQuote('group_id') .' IN ('.implode(',', $gList).') OR '.$db->nameQuote('group_id') .' IS NULL)'; }
		if( empty($where) || empty($pList) ) {
			$u = ApotheosisLib::getUser();
			$where[] = "\n".$db->nameQuote('pupil_id') .' = '.$db->quote( $u->person_id );
		}
		$where = implode( "\n AND ", $where );
		$query = 'SELECT * FROM `jos_apoth_ass_aspect_mark`'
			."\n".'WHERE '
			."\n".$where
			."\n".'ORDER BY '.$db->nameQuote('aspect_id').','.$db->nameQuote('pupil_id').','.$db->nameQuote('group_id').','.$db->nameQuote('last_modified');
		$db->setQuery( $query );
		$data1 = $db->loadAssocList();
//		debugQuery( $db, $data1 );
		
		$query = 'SELECT * FROM `jos_apoth_ass_aspect_text`'
			."\n".'WHERE '
			."\n".$where
			."\n".'ORDER BY '.$db->nameQuote('aspect_id').','.$db->nameQuote('pupil_id').','.$db->nameQuote('group_id').','.$db->nameQuote('last_modified');
		$db->setQuery( $query );
		$data2 = $db->loadAssocList();
		
		// Allocate the marks to the enrolments
		$data = array_merge( $data1, $data2 );
		foreach( $data as $row ) {
			if( isset(   $aspEnrolments[$row['aspect_id']][$row['pupil_id']])
			 && is_array($aspEnrolments[$row['aspect_id']][$row['pupil_id']]) ) {
				$enrolments = &$aspEnrolments[$row['aspect_id']][$row['pupil_id']];
				foreach( $enrolments as $cId=>$hGroups ) {
					// isset is faster but not by much and would need us to go through after replacing -1 with null
					if( (is_null( $hGroups) && is_null($row['group_id']))
					 || (is_array($hGroups) && array_key_exists($row['group_id'], $hGroups)) ) {
						$enrolments[$cId][$row['group_id']] = $row['value'];
					}
				}
				unset( $enrolments );
			}
		}
//		var_dump_pre( $aspEnrolments, 'sorted marks', true );
		
		// Create mark entries for all the enrolments, using available data from above
		$mark = new stdClass();
		$mark->mark = null;
		$mark->group = null;
		foreach( $aspEnrolments as $aId=>$pupils ) {
			$asp = &$this->getInstance( $aId );
			$a = &$asp->getAssessment();
			$gs = $a->getProperty('group_specific');
			$marks = array();
			foreach( $pupils as $pId=>$cur ) {
				foreach( $cur as $cId=>$hGroups ) {
					if( !$gs ) {
						if( is_null($hGroups) ) {
							$marks[$pId][$cId] = array( 'mark'=>false, 'group'=>null );
						}
						elseif( is_array($hGroups) ) {
							$marks[$pId][$cId] = array( 'mark'=>reset($hGroups), 'group'=>null );
						}
					}
					if( $gs && is_array($hGroups) ) {
						$hasGroup = false;
						$v = reset( $hGroups );
						do {
							$hasGroup = $hasGroup || $a->hasGroup( key($hGroups) );
						} while( is_null($v) && (($v = next($hGroups)) !== false) );
						if( $hasGroup ) {
							$marks[$pId][$cId] = array( 'mark'=>$v, 'group'=>key($hGroups) );
						}
					}
				}
			}
			$asp->setMarks( $marks );
		}
	}
	
	function commitMarks( $aspId )
	{
		$asp = &$this->getInstance( $aspId );
		
		if( is_null($asp) ) {
			return null;
		}
		$a = $asp->getAssessment();
		$gs = $a->getProperty('group_specific');
		$db = &JFactory::getDBO();
		$marks = $asp->getMarks();
		
		$now = date('Y-m-d H:i:s');
		$dbNow = $db->Quote( $now );
		$dbAsp = $db->Quote( $aspId );
		foreach( $marks as $pId=>$groups ) {
			foreach( $groups as $gId=>$mark ) {
				if( $mark['mark'] === false ) {
					$mark['mark'] = -1;
					$dbMark = 'NULL';
				}
				else {
					$dbMark = $db->Quote( $mark['mark']);
				}
				
				if( $gs ) {
					$gIdVal = $db->Quote( $mark['group'] );
				}
				else {
					$gIdVal = 'NULL';
				}
				
				$newVals[] = '('.$dbAsp.', '.$db->Quote( $pId ).', '.$gIdVal.', '.$dbMark.', '.$dbNow.', 0, "0000-00-00")';
			}
		}
		
		$u = &ApotheosisLib::getUser();
		list($usec, $sec) = explode( ' ', str_replace( '.', '', microtime()) );
		$tmpTbl = $db->nameQuote( 'tmp_marks_'.$u->id.'_'.$sec.'_'.$usec );
		$tmpTbl2 = $db->nameQuote( 'tmp_mod_'.$u->id.'_'.$sec.'_'.$usec );
		$style = $asp->getMarkStyle();
		switch( $style['type'] ) {
			case( 'mark' ):
			case( 'numeric' ):
			default:
				$targetTbl = $db->nameQuote( '#__apoth_ass_aspect_mark' );
				break;
			
			case( 'text' ):
				$targetTbl = $db->nameQuote( '#__apoth_ass_aspect_text' );
				break;
		}
		$dbAspId = $db->nameQuote('aspect_id');
		$dbPId = $db->nameQuote('pupil_id');
		$dbGId = $db->nameQuote('group_id');
		$dbVal = $db->nameQuote('value');
		$dbMod = $db->nameQuote('last_modified');
		$dbUsed = $db->nameQuote('used');
		$dbPrev = $db->nameQuote('prev_modified');
		
		$query = 'CREATE TABLE '.$tmpTbl.' AS'
			."\n".'SELECT *, 0 AS '.$dbUsed.', NOW() AS '.$dbPrev
			."\n".'FROM '.$targetTbl.' LIMIT 0;'
			."\n"
			."\n".'ALTER TABLE '.$tmpTbl
			."\n".' ADD INDEX('.$dbPId.'),'
			."\n".' ADD INDEX('.$dbGId.');'
			."\n"
			."\n".'INSERT INTO '.$tmpTbl
			."\n".'VALUES'
			."\n".implode( "\n, ", $newVals ).';'
			."\n"
			."\n".'CREATE TEMPORARY TABLE '.$tmpTbl2.' AS'
			."\n".'SELECT '.$targetTbl.'.'.$dbAspId
			."\n".', '.$targetTbl.'.'.$dbPId
			."\n".', '.$targetTbl.'.'.$dbGId
			."\n".', MAX('.$targetTbl.'.'.$dbMod.') AS '.$dbPrev
			."\n".'FROM '.$tmpTbl
			."\n".'INNER JOIN '.$targetTbl
			."\n".'   ON '.$targetTbl.'.aspect_id = '.$tmpTbl.'.aspect_id'
			."\n".'  AND '.$targetTbl.'.pupil_id  = '.$tmpTbl.'.pupil_id'
			."\n".'  AND '.$targetTbl.'.group_id  = '.$tmpTbl.'.group_id'
			."\n".'GROUP BY '.$targetTbl.'.`aspect_id`, '.$targetTbl.'.`pupil_id`, '.$targetTbl.'.`group_id`;'
			."\n"
			."\n".'UPDATE '.$tmpTbl
			."\n".'INNER JOIN '.$tmpTbl2
			."\n".'   ON '.$tmpTbl.'.aspect_id = '.$tmpTbl2.'.aspect_id'
			."\n".'  AND '.$tmpTbl.'.pupil_id  = '.$tmpTbl2.'.pupil_id'
			."\n".'  AND '.$tmpTbl.'.group_id  = '.$tmpTbl2.'.group_id'
			."\n".'SET '.$tmpTbl.'.'.$dbPrev.' = '.$tmpTbl2.'.'.$dbPrev.';'
			."\n"
			."\n".'DROP TABLE '.$tmpTbl2.';'
			."\n"
			."\n".'DELETE '.$tmpTbl.'.*'
			."\n".'FROM '.$tmpTbl
			."\n".'LEFT JOIN '.$targetTbl
			."\n".'  ON '.$targetTbl.'.'.$dbAspId.' = '.$tmpTbl.'.'.$dbAspId
			."\n".' AND '.$targetTbl.'.'.$dbPId.' = '.$tmpTbl.'.'.$dbPId
			."\n".' AND '.$targetTbl.'.'.$dbGId.' = '.$tmpTbl.'.'.$dbGId
			."\n".' AND '.$targetTbl.'.'.$dbMod.' = '.$tmpTbl.'.'.$dbPrev
			."\n".'WHERE '.$targetTbl.'.'.$dbVal.' = '.$tmpTbl.'.'.$dbVal
			."\n".'   OR ('.$targetTbl.'.'.$dbAspId.' IS NULL AND '.$tmpTbl.'.'.$dbVal.' = '.$db->Quote( -1 ).')';
		$db->setQuery( $query );
		$db->queryBatch();
		
		$query = 'SELECT COUNT(*) FROM '.$tmpTbl;
		$db->setQuery( $query );
		$numToDo = $db->loadResult();
		
		// insert any new marks
		$query = 'INSERT INTO '.$targetTbl
			."\n".'SELECT '.$dbAspId.', '.$dbPId.', '.$dbGId.', '.$dbVal.', '.$dbNow
			."\n".'FROM '.$tmpTbl
			."\n".'WHERE '.$tmpTbl.'.'.$dbUsed.' = 0';
		$db->setQuery( $query );
		$db->Query();
		$numInserted = $db->getAffectedRows();
		
		$query = 'DROP TABLE '.$tmpTbl;
		$db->setQuery( $query );
		$db->query();
		
		$allGood = ( $numToDo == $numInserted );
		// *** should we clear the current mark data and reload ?
		// probably yes if not all the marks were done
		return $allGood;
	}
}

/**
 * Assessment Object Aspect
 */
class ApothAspect extends JObject
{
	var $_data;
	var $_boundaries;
	var $_state;
	
	/**
	 * Constructs a new aspect object from the given data
	 * 
	 * @param array $data  The optional data to use when creating this aspect object
	 * @return object  The newly created aspect object
	 */
	function __construct( $data )
	{
		$this->_data = new stdClass();
		$this->_assignDataArr( $data, true );
		$this->_state->shown = $this->_data->shown;
		$this->_markCache = array();
		$this->_marks = array();
	}
	
	function __clone()
	{
		$this->_data = clone( $this->_data );
	}
	
	/**
	 * Updates the aspect object with the given data
	 * 
	 * @param array $data  An associative array of properties to set for this assessment
	 * @param boolean $missingToNull  A switch to determine whether or not missing data is set to null
	 */
	function _assignDataArr( $data, $missingToNull = false )
	{
		if( !is_array($data) ) {
			$data = array();
		}
		$this->_data->id               = array_key_exists( 'id'               , $data) ? $data['id']               : ( $missingToNull ? NULL : $this->_data->id );
		$this->_data->assessment_id    = array_key_exists( 'assessment_id'    , $data) ? $data['assessment_id']    : ( $missingToNull ? NULL : $this->_data->assessment_id );
		$this->_data->parent_aspect_id = array_key_exists( 'parent_aspect_id' , $data) ? $data['parent_aspect_id'] : ( $missingToNull ? NULL : $this->_data->parent_aspect_id );
		$this->_data->title            = array_key_exists( 'title'            , $data) ? $data['title']            : ( $missingToNull ? NULL : $this->_data->title );
		$this->_data->short            = array_key_exists( 'short'            , $data) ? $data['short']            : ( $missingToNull ? NULL : $this->_data->short );
		$this->_data->boundaries       = array_key_exists( 'boundaries'       , $data) ? $data['boundaries']       : ( $missingToNull ? NULL : $this->_data->boundaries );
		$this->_data->shown            = array_key_exists( 'shown'            , $data) ? (boolean)$data['shown']   : ( $missingToNull ? NULL : $this->_data->shown );
		$this->_data->created_by       = array_key_exists( 'created_by'       , $data) ? $data['created_by']       : ( $missingToNull ? NULL : $this->_data->created_by );
		$this->_data->created_on       = array_key_exists( 'created_on'       , $data) ? $data['created_on']       : ( $missingToNull ? NULL : $this->_data->created_on );
		$this->_data->modified_by      = array_key_exists( 'modified_by'      , $data) ? $data['modified_by']      : ( $missingToNull ? NULL : $this->_data->modified_by );
		$this->_data->modified_on      = array_key_exists( 'modified_on'      , $data) ? $data['modified_on']      : ( $missingToNull ? NULL : $this->_data->modified_on );
		$this->_data->valid_from       = array_key_exists( 'valid_from'       , $data) ? $data['valid_from']       : ( $missingToNull ? NULL : $this->_data->valid_from );
		$this->_data->valid_to         = array_key_exists( 'valid_to'         , $data) ? $data['valid_to']         : ( $missingToNull ? NULL : $this->_data->valid_to );
		$this->_data->deleted          = array_key_exists( 'deleted'          , $data) ? $data['deleted']          : ( $missingToNull ? NULL : $this->_data->deleted );
	}
	
	/**
	 * Retrieves the requested aspect object property
	 * 
	 * @param $prop string  The name of the property requested
	 * @return string  The property requested
	 */
	function getProperty( $prop )
	{
		return ( isset($this->_data->$prop) ? $this->_data->$prop : null );
	}
	
	/**
	 * Give back the raw data object
	 */
	function getData()
	{
		return $this->_data;
	}
	
	function getId()
	{
		return $this->_data->id;
	}
	
	function setId( $id )
	{
		$this->_data->id = (int)$id;
	}
	
	function setIsShown( $val = null )
	{
		$this->_state->shown = ( is_null($val) ? !$this->_state->shown : (bool)$val );
		$tmp = &$this->getAssessment();
		$tmp->unsetIsShown();
	}
	
	function getIsShown()
	{
		return $this->_state->shown;
	}
	
	/**
	 * Sets the mark for a pupil in a group
	 * The two group ids are needed to allow editing of historical data
	 *  The gId is the currently listed group, and the place to display the mark
	 *  The effectiveGId is the group where the mark should be recorded as taking place
	 *  The effectiveGId must be a group that the parent assessment is assigned to
	 * 
	 * @param string $pId  The Arc ID of the pupil
	 * @param string $gId  The group for which to display this mark
	 * @param string $mark  The mark to assign
	 * @param string $effectiveGId  The group for which this mark was received
	 */
	function setMark( $pId, $gId, $mark, $effectiveGId = null )
	{
		if( is_null($effectiveGId) ) {
			$effectiveGId = $gId;
		}
		$this->_marks[$pId][$gId] = array( 'mark'=>$this->encodeMark($mark), 'group'=>$effectiveGId );
	}
	
	/**
	 * Adds the given marks to the currently held list
	 *
	 * @param array $data  The marks to add in. array( pupilId => groupid => [mark, group]
	 */
	function setMarks( $data )
	{
		$this->_marks = array_merge( $this->_marks, $data );
	}
	
	/**
	 * complement to convertMark
	 * Converts a given mark value and gives back the internal representation to use
	 * ! currently only useful for numeric marks as all others come through as internal values from select lists
	 * @param unknown_type $mark
	 */
	function encodeMark( $given )
	{
		if( $given === '' ) { return false; }
		
		$usage = '_encode';
		if( !is_null($given) && !isset($this->_markCache[$usage][$given]) ) {
			$boundaries = &$this->getBoundaries();
			if( !isset($this->markType) ) {
				$style = $this->getMarkStyle();
				$this->markType = $style['type'];
			}
			$type = $this->markType;
			$b = $boundaries['mark_values'];
			switch( $type ) {
			case( 'numeric' ):
				// maths to find the stored % equivalent of the numeric value
				reset($b);
				$low = (float)key($b);
				end($b);
				$high = (float)key($b);
				$mark = 100 * ($given - $low) / ($high - $low);
				$this->_markCache[$usage][$given] = number_format($mark, 2);
				break;
			
			default:
				$this->_markCache[$usage][$given] = $given;
				break;
			}
		}
		return $this->_markCache[$usage][$given];
	}
	
	function clearMarks()
	{
		$this->_marks = array();
	}
	
	/**
	 * Gets the raw mark for a pupil in a group for this aspect
	 * 
	 * @return array  mark=>the mark. this can be a value (the mark), false (mark expectei, none found), or null (no mark expected)
	 *                group=>the group where the mark was received
	 */
	function getMark( $pId, $gId )
	{
		if( isset($this->_marks[$pId][$gId]) ) {
			$retVal = $this->_marks[$pId][$gId];
		}
		elseif( is_null($gId) && isset($this->_marks[$pId]) ) {
			$retVal = reset( $this->_marks[$pId] );
		}
		else {
			$retVal = null;
		}
		
		// *** need to work out the color to send back
		if( !is_null($retVal) ) {
			$retVal['color'] = null;
		}
		return $retVal;
	}
	
	function getMarks()
	{
		return $this->_marks;
	}
	
	/**
	 * Converts a raw mark (percentage) into a displayable mark (potentially anything)
	 *
	 */
	function convertMark( $mark, $usage = 'display' )
	{
		// convert stored percentages into grades / levels etc. when appropriate
		if( !is_null($mark) && !isset($this->_markCache[$usage][$mark]) ) {
			$boundaries = &$this->getBoundaries();
			if( $usage == 'display' ) {
				if( !isset($this->displayType) ) {
					$style = $this->getDisplayStyle();
					$this->displayType = $style['type'];
				}
				$type = $this->displayType;
				$b = $boundaries['display_bounds'];
			}
			else {
				if( !isset($this->markType) ) {
					$style = $this->getMarkStyle();
					$this->markType = $style['type'];
				}
				$type = $this->markType;
				$b = $boundaries['mark_values'];
			}
			
			if( is_null($b) ) {
				$b = array();
			}

//			var_dump_pre($b, 'boundaries to use:');
			switch( $type ) {
			case( 'text' ):
				$this->_markCache[$usage][$mark] = $mark;
				break;
			case( 'mark' ):
				// loopy loop to find the nearest boundary
				reset( $b );
				$match = false;
				do {
					$match = (current($b) >= $mark);
				} while( !$match && (next($b) !== false) );
				
				$this->_markCache[$usage][$mark] = ($match ? key($b) : $mark);
				break;
			case( 'numeric' ):
				// maths to find the numeric equivalent of the stored %
				reset($b);
				$low = (float)key($b);
				end($b);
				$high = (float)key($b);
				$tmp = (($high - $low) * $mark / 100.0) + $low;
				$this->_markCache[$usage][$mark] = number_format($tmp, 2);
				break;
			}
		}
//		var_dump_pre($this->_markCache[$usage][$mark], 'converting '.$mark.' for '.$usage.' gave ' );
		return $this->_markCache[$usage][$mark];
	}
	
	function getMarkStyle()
	{
		if( !isset($this->_boundaries['mark_style']) ) {
			$this->_initBoundaries();
		}
		
		if( !isset($this->_markStyle) ) {
			$tmp = ( ($this->_boundaries['display_style'] == '') ? $this->_boundaries['mark_style'] : $this->_boundaries['display_style'] );
			$this->_markStyle = ApotheosisData::_( 'assessment.markStyleInfo', $tmp );
		}
		
		return $this->_markStyle;
	}
	
	function getDisplayStyle()
	{
		if( !isset($this->_boundaries['mark_style']) ) {
			$this->_initBoundaries();
		}
		
		if( !isset($this->_displayStyle) ) {
			$tmp = ( ($this->_boundaries['display_style'] == '') ? $this->_boundaries['mark_style'] : $this->_boundaries['display_style'] );
			$this->_displayStyle = ApotheosisData::_( 'assessment.markStyleInfo', $tmp );
		}
		
		return $this->_displayStyle;
	}
	
	/**
	 * Retrieves this aspects unserialised boundary data object
	 * 
	 * @return object  This aspects unserialised boundary data object
	 */
	function getBoundaries()
	{
		if( !isset($this->_boundaries['mark_style']) ) {
			$this->_initBoundaries();
		}
		
		return $this->_boundaries;
	}
	
	/**
	 * Unserialises the boundary data object 
	 * and stores it as a new private class variable
	 */
	function _initBoundaries()
	{
		$this->_boundaries = json_decode( $this->_data->boundaries, true );
		$this->_boundaries['mark_values_orig'] = $this->_boundaries['mark_values'];
		if( is_array($this->_boundaries['mark_values'])    ) { asort( $this->_boundaries['mark_values'] );    }
		if( is_array($this->_boundaries['display_bounds']) ) { asort( $this->_boundaries['display_bounds'] ); }
	}
	
	function setAssessment( $id )
	{
		$this->_data->assessment_id = $id;
	}
	
	/**
	 * Returns the id of the assessment to which this aspect is assigned
	 */
	function &getAssessmentId()
	{
		return $this->_data->assessment_id;
	}
	
	/**
	 * Returns the assessment to which this aspect is assigned
	 * *** maybe should just return the id and let the caller to the factory stuff?
	 */
	function &getAssessment()
	{
		$fAss = &ApothFactory::_( 'assessment.assessment' );
		return $fAss->getInstance( $this->_data->assessment_id );
	}
	
	/**
	 * Updates the apect object with the given data
	 * @param array $data  An associative array of properties to set for this aspect
	 * @return boolean  True if the data was stored successfully, false otherwise
	 */
	function update( $data )
	{
		if( !is_array($data) ) {
			return false;
		}
		$this->_assignDataArr( $data, false );
	}
	
	function delete()
	{
		$this->_data->deleted = 1;
	}
	
	function commit()
	{
		$fAsp = &ApothFactory::_( 'assessment.aspect' );
		$fAsp->commitInstance( $this->_data->id );
	}
	
	function commitMarks()
	{
		$fAsp = &ApothFactory::_( 'assessment.aspect' );
		$fAsp->commitMarks( $this->_data->id );
//		$fAsp->commitMarksPurge( $this->_data->id );
	}
}
?>