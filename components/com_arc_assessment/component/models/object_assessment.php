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
 * Assessment Factory Assessment
 */
class ApothFactory_Assessment_Assessment extends ApothFactory
{
	var $_access;
	var $_date;
	
	/**
	 * Constructs a new assessment factory object
	 */
	function __construct( $config = array() )
	{
		parent::__construct( $config );
		
		$this->_access->teachers = array(
			ApotheosisLibAcl::getRoleId('sys_teacher'),
			ApotheosisLibAcl::getRoleId('group_supervisor_teacher'),
			ApotheosisLibAcl::getRoleId('group_supervisor_admin'),
			ApotheosisLibAcl::getRoleId('group_ancestor_teacher'),
			ApotheosisLibAcl::getRoleId('group_ancestor_admin'),
			ApotheosisLibAcl::getRoleId('group_successor_teacher'),
			ApotheosisLibAcl::getRoleId('group_successor_admin')
		);
		
		$this->_access->students = array(
			ApotheosisLibAcl::getRoleId('group_participant_student'),
			ApotheosisLibAcl::getRoleId('group_ancestor_student'),
			ApotheosisLibAcl::getRoleId('group_successor_student')
		);
		$this->_access->parents = array(
			ApotheosisLibAcl::getRoleId('rel_parental_foster parent'),
			ApotheosisLibAcl::getRoleId('rel_parental_guardian'),
			ApotheosisLibAcl::getRoleId('rel_parental_parent'),
			ApotheosisLibAcl::getRoleId('rel_parental_step parent')
		);
		$this->_access->admins = array(
			ApotheosisLibAcl::getRoleId('group_ancestor_admin'),
			ApotheosisLibAcl::getRoleId('group_ancestor_teacher')
		);
	}
	
	/**
	 * To comply with automated saving of factories
	 * we must explicitly sleep any class vars in child factories
	 */
	function __sleep()
	{
		$parentVars = parent::__sleep();
		$myVars = array( '_access', '_date' );
		$allVars = array_merge( $parentVars, $myVars );
		
		return $allVars;
	}
	
	function initialise()
	{
		$this->setDate();
	}
	
	function setDate( $date = null )
	{
		$this->_date = ( is_null($date) ? date( 'Y-m-d H:i:s' ) : $date );
	}
	
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
			$r = new ApothAssessment( array('id'=>$id, 'always_show'=>0, 'group_specific'=>1, 'deleted'=>0, 'locked'=>0, 'groups'=>array()), array('teachers'=>true) );
			$this->_addInstance( $id, $r );
		}
		return $r;
	}
	
	/**
	 * Returns the requested assessment object, loading if required
	 * 
	 * @param int $id  ID of the assessment object to return
	 * @return object $assObj  The requested assessment object
	 */
	function &getInstance( $id )
	{
		$assObj = &$this->_getInstance( $id );
		$restrict = $this->getParam( 'restrict' );
		
		if( is_null($assObj) ) {
			$db = &JFactory::getDBO();
			$assQuery = 'SELECT *'
				."\n".'FROM '.$db->nameQuote( '#__apoth_ass_assessments' ).' AS '.$db->nameQuote('a')
				.($restrict ? "\n".'~LIMITINGJOIN~' : '' )
				."\n".'WHERE '.$db->nameQuote('a').'.'.$db->nameQuote( 'id' ).' = '.$db->Quote( $id )
				."\n".'  AND '.$db->nameQuote('a').'.'.$db->nameQuote( 'deleted' ).' != '.$db->Quote( '1' );
			$db->setQuery( $restrict ? ApotheosisLibAcl::limitQuery($assQuery, 'assessment.assessments') : $assQuery );
			$data = $db->loadAssoc();
			
			$data['groups'] = $this->loadGroups( $id );
			$access = $this->loadAccess( $id );
			
			$assObj = new ApothAssessment( $data, $access );
			$assObj->loadAspects();
			$this->_addInstance( $id, $assObj );
		}
		
		return $assObj;
	}
	
	/**
	 * Returns an ID-indexed array of assessment objects matching the requirements,
	 * loading and storing if required
	 * 
	 * @param array $requirements  Array of requirements used to pull out assessment
	 * @return array $assObjs  Array of assessment ids matching the requirements
	 */
	function &getInstances( $requirements, $init = true )
	{
//		var_dump_pre( $requirements, 'requirements' );
		$sId = $this->_getSearchId( $requirements );
		$assIds = $this->_getInstances( $sId );
		$restrict = $this->getParam( 'restrict' );
		
		if( is_null($assIds) ) {
			$db = &JFactory::getDBO();
			$join = ( $restrict ? array('~LIMITINGJOIN~') : array() );
			$where = array();
			
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
				case( 'valid_from' ):
				case( 'valid_to' ):
					if( !isset($where['date']) ) {
						$where['date'] = '('.ApotheosisLibDb::dateCheckSql('a.valid_from', 'a.valid_to', $requirements['valid_from'], $requirements['valid_to'])
							."\n".' OR '.$db->nameQuote( 'a' ).'.'.$db->nameQuote( 'always_show' ).' = 1 )';;
					}
					
					break;
				
				case( 'groups' ):
					// load in assessments assigned to any group above these (eg asmnt for Art shows for all Art classes
					$join[] = 'LEFT JOIN '.$db->nameQuote('#__apoth_ass_course_map').' AS '.$db->nameQuote('cm')
						."\n".'  ON '.$db->nameQuote('cm').'.'.$db->nameQuote('assessment').' = '.$db->nameQuote('a').'.'.$db->nameQuote('id')
						."\n".'LEFT JOIN '.$db->nameQuote('#__apoth_cm_courses_ancestry').' AS '.$db->nameQuote('ca')
						."\n".'  ON '.$db->nameQuote('ca').'.'.$db->nameQuote('ancestor').' = '.$db->nameQuote('cm').'.'.$db->nameQuote('group');
					$where[] = $db->nameQuote('ca').'.'.$db->nameQuote('id').$assignPart;
					break;
				
				case( 'assessments' ):
					if( isset($requirements['no_others']) ) {
						$where[] = $db->nameQuote( 'a' ).'.'.$db->nameQuote( 'id' ).$assignPart;
					}
					else {
						$where[] = '('.$db->nameQuote( 'a' ).'.'.$db->nameQuote( 'id' ).$assignPart
							."\n".' OR '.$db->nameQuote( 'a' ).'.'.$db->nameQuote( 'always_show' ).' = 1 )';
					}
					break;
				
				case( 'aspects' ):
					$dbAsp = $db->nameQuote( 'asp' );
					$join[] = 'INNER JOIN '.$db->nameQuote('#__apoth_ass_aspect_instances').' AS '.$dbAsp
						."\n".'   ON '.$dbAsp.'.'.$db->nameQuote('assessment_id').' = '.$db->nameQuote('a').'.'.$db->nameQuote( 'id' );
					$where[] = $dbAsp.'.'.$db->nameQuote( 'id' ).$assignPart;
					break;
				}
			}
			
			// Pull out assessments based on requirements
			$assQuery = 'SELECT DISTINCT '.$db->nameQuote('a').'.*'
				."\n".'FROM '.$db->nameQuote('#__apoth_ass_assessments').' AS '.$db->nameQuote('a')
				.( empty($join)  ? '' : "\n".implode("\n", $join) )
				.( empty($where) ? '' : "\n".'WHERE '.implode("\n".'  AND ', $where) )
				."\n".'  AND '.$db->nameQuote('a').'.'.$db->nameQuote('deleted').' = '.$db->Quote('0')
				."\n".'ORDER BY a.'.$db->nameQuote('always_show').' DESC, a.'.$db->nameQuote('valid_from').' ASC, a.'.$db->nameQuote('valid_to').' ASC';
				
			$db->setQuery( $restrict ? ApotheosisLibAcl::limitQuery($assQuery, 'assessment.assessments') : $assQuery );
			$assObjsData = $db->loadAssocList( 'id' );
//			debugQuery( $db, $assObjsData );
			$assIds = array_keys( $assObjsData );
			$this->_addInstances( $sId, $assIds );
			
			
			if( $init ) {
				$existing = $this->_getInstances();
				$newIds = array_diff( $assIds, $existing );
				
				if( !empty($newIds) ) {
					// prepare all the aspects we're going to need
					$fAsp = &ApothFactory::_( 'assessment.aspect' );
					$requirements = array( 'assId'=>$newIds );
					$aspIds = $fAsp->getInstances( $requirements );
					foreach( $aspIds as $aspId ) {
						$a = &$fAsp->getInstance( $aspId );
						$assAsps[$a->getAssessmentId()][] = $aspId;
					}
					
					// initialise and cache
					foreach( $newIds as $id ) {
						$data = $assObjsData[$id];
						$data['groups'] = $this->loadGroups( $id );
						$access = $this->loadAccess( $id );
						$assObj = new ApothAssessment( $data, $access );
						if( isset($assAsps[$id]) ) {
							$assObj->setAspects( $assAsps[$id] );
						}
						else {
							$assObj->setAspects( array() );
						}
						$this->_addInstance( $id, $assObj );
						unset( $assObj );
					}
				}
			}
		}
		
		return $assIds;
	}
	
	function loadAccess( $id )
	{
		$db = &JFactory::getDBO();
		// get teacher / student / parent access the horrible way for now
		foreach( $this->_access->teachers as $role ) {
			$teacherRoles[] = $db->Quote( $role );
		}
		$teacherAccessQuery = 'SELECT *'
			."\n".'FROM '.$db->nameQuote( '#__apoth_ass_accessors' )
			."\n".'WHERE '.$db->nameQuote( 'assessment' ).' = '.$db->Quote( $id )
			."\n".'  AND '.$db->nameQuote( 'role' ).' IN ('.implode( ', ', $teacherRoles ).');';
		$db->setQuery( $teacherAccessQuery );
		$result = $db->loadRowlist();
		$access['teachers'] = !empty( $result );
		
		foreach( $this->_access->students as $role ) {
			$studentRoles[] = $db->Quote( $role );
		}
		$studentAccessQuery = 'SELECT *'
			."\n".'FROM '.$db->nameQuote( '#__apoth_ass_accessors' )
			."\n".'WHERE '.$db->nameQuote( 'assessment' ).' = '.$db->Quote( $id )
			."\n".'  AND '.$db->nameQuote( 'role' ).' IN ('.implode( ', ', $studentRoles ).');';
		$db->setQuery( $studentAccessQuery );
		$result = $db->loadRowlist();
		$access['students'] = !empty( $result );
		
		foreach( $this->_access->parents as $role ) {
			$parentRoles[] = $db->Quote( $role );
		}
		$parentAccessQuery = 'SELECT *'
			."\n".'FROM '.$db->nameQuote( '#__apoth_ass_accessors' )
			."\n".'WHERE '.$db->nameQuote( 'assessment' ).' = '.$db->Quote( $id )
			."\n".'  AND '.$db->nameQuote( 'role' ).' IN ('.implode( ', ', $parentRoles ).');';
		$db->setQuery( $parentAccessQuery );
		$result = $db->loadRowlist();
		$access['parents'] = !empty( $result );
		
		return $access;
	}
	
	function loadGroups( $id )
	{
		$db = &JFactory::getDBO();
		$query = 'SELECT '.$db->nameQuote('group')
			."\n".'FROM '.$db->nameQuote('#__apoth_ass_course_map')
			."\n".'WHERE '.$db->nameQuote('assessment').' = '.$db->Quote($id);
		$db->setQuery( $query );
		$groups = $db->loadResultArray();
		if( !is_array($groups) ) {
			$groups = array();
		}
		return $groups;
		
		/* *** old way of doing "set groups" kept for reference as new way currently pays no attention to permissions 
		// Pick out the groups from the list we were given that we are actually assigned to
		// or assigned to a parent of
		$db = &JFactory::getDBO();
		$query = 'SELECT '.$db->nameQuote('c').'.'.$db->nameQuote('id').', '
			              .$db->nameQuote('c').'.'.$db->nameQuote('shortname')
			."\n".' FROM '.$db->nameQuote('#__apoth_ass_course_map').' AS '.$db->nameQuote('cm')
			."\n".' INNER JOIN '.$db->nameQuote('#__apoth_cm_courses_ancestry').' AS '.$db->nameQuote('ca')
			."\n".'    ON '.$db->nameQuote('ca').'.'.$db->nameQuote('ancestor').' = '.$db->nameQuote('cm').'.'.$db->nameQuote('group')
			."\n".' INNER JOIN '.$db->nameQuote('#__apoth_cm_courses').' AS '.$db->nameQuote('c')
			."\n".'    ON '.$db->nameQuote('c').'.'.$db->nameQuote('id').' = '.$db->nameQuote('ca').'.'.$db->nameQuote('id')
			."\n".' ~LIMITINGJOIN~'
			."\n".' WHERE '.$db->nameQuote('cm').'.'.$db->nameQuote('assessment').' = '.$db->Quote($this->_data->id)
			."\n". (!empty($groups) ? '  AND '.$db->nameQuote('c').'.'.$db->nameQuote('id').' IN ('.implode(', ', $groups).')' : '' );
		$db->setQuery( ApotheosisLibAcl::limitQuery($query, 'timetable.groups', 'c') );
		$groupsList = $db->loadObjectList();
		
		if( !is_array($groupsList) ) { $groupsList = array(); }
		$this->_groups = array();
		
		foreach( $groupsList as $k=>$v ) {
			$this->_groups[$v->id]->id        = $v->id;
			$this->_groups[$v->id]->shortname = $v->shortname;
		}
		
		if( $withPupils ) {
			$this->setPupils();
		}
		*/
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
			$queryStart = 'INSERT INTO '.$db->nameQuote('#__apoth_ass_assessments');
			$queryEnd = '';
		}
		else {
			$data->modified_by = $u->person_id;
			$data->modified_on = $now;
			$queryStart = 'UPDATE '.$db->nameQuote('#__apoth_ass_assessments');
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
		
		// sort out course mapping
		$g = $a->getGroupIds();
		$values = array();
		$aId = $db->Quote($id);
		foreach( $g as $gId ) {
			$values[] = '('.$aId.', '.$db->Quote($gId).')';
		}
		$query = 'START TRANSACTION;'
			."\n".'DELETE FROM '.$db->nameQuote('#__apoth_ass_course_map')
			."\n".'WHERE '.$db->nameQuote('assessment').' = '.$db->Quote($id).';'
			."\n".'INSERT INTO '.$db->nameQuote('#__apoth_ass_course_map')
			."\n".'('.$db->nameQuote('assessment').', '.$db->nameQuote('group').') VALUES '
			."\n".implode( "\n, ", $values ).';'
			."\n".'COMMIT;';
		$db->setQuery($query);
		$db->queryBatch();
			
		// sort out accessors and editors (admins)
		// *** This should really be less rigid / more configurable / generally not slightly rubbish
		$access = $a->getAccess();
		$values = array();
		if( isset($access['teachers']) && $access['teachers'] ) {
			foreach( $this->_access->teachers as $role ) {
				$values[] = '('.$id.', '.$role.')';
			}
		}
		if( isset($access['students']) && $access['students'] ) {
			foreach( $this->_access->students as $role ) {
				$values[] = '('.$id.', '.$role.')';
			}
		}
		if( isset($access['parents']) && $access['parents'] ) {
			foreach( $this->_access->parents as $role ) {
				$values[] = '('.$id.', '.$role.')';
			}
		}
		
		$query = 'START TRANSACTION;'
			."\n".'DELETE FROM #__apoth_ass_accessors'
			."\n".'WHERE assessment = '.$id.';';
		if( !empty($values) ) {
			$query .= "\n".'INSERT INTO #__apoth_ass_accessors'
				."\n".'VALUES '.implode("\n, ", $values).';';
		}
		
		$values = array();
		foreach( $this->_access->admins as $role ) {
				$values[] = '('.$id.', '.$role.')';
		}
		
		$query .= "\n".'DELETE FROM #__apoth_ass_editors'
			."\n".'WHERE assessment = '.$id.';';
		if( !empty($values) ) {
			$query .= "\n".'INSERT INTO #__apoth_ass_editors'
				."\n".'VALUES '.implode("\n, ", $values).';';
		}
		$query .= "\n".'COMMIT;';
		
		$db->setQuery($query);
		$db->QueryBatch();
		
		// refresh the permissions tables
		ApotheosisLibDbTmp::flush( $u->id );
	}
}


/**
 * Assessment Object Assessment
 */
class ApothAssessment extends JObject
{
	var $_data;
	var $_aspects;
	var $_oldAspects; // store of aspects to be removed from this assessment
	var $_state;  // current state, eg if edits are currently turned on
	
	/**
	 * Constructs a new assessment object from the given data
	 * Does NOT initialise the aspects of this assessment,
	 * you must call loadAspects() to do this immediately after constructing
	 * 
	 * @param array $data  The optional data to use when creating this assessment object
	 * @param array $access  The optional access controls to use when creating this assessment object
	 * @return object  The newly created assessment object
	 */
	function __construct( $data = array(), $access = array() )
	{
		$this->_data = new stdClass();
		$this->_assignDataArr( $data, true );
		$this->setGroups( $data['groups'], false );
		$this->_aspects = array();
		$this->_oldAspects = array();
		$this->_access = $access;
		$this->_state = new stdClass();
		$this->_state->edits = false;
	}
	
	function __clone()
	{
		$this->_data = clone( $this->_data );
		
		$tmp = $this->_aspects;
		$this->_aspects = array();
		foreach( $tmp as $aId=>$aspect ) {
			$this->copyAspect( $aId );
		}
	}
	
	/**
	 * Switch aspect references out for aspect ids (for serialisation)
	 * Don't do this as sleep as that would imply doing the opposite as wakeup. see refAspects for why that's bad
	 */
	function deRefAspects()
	{
		foreach( $this->_aspects as $id=>$asp ) {
			unset( $this->_aspects[$id] );
			$this->_aspects[$id] = $id;
		}
		foreach( $this->_oldAspects as $id=>$asp ) {
			unset( $this->_oldAspects[$id] );
			$this->_oldAspects[$id] = $id;
		}
		return( array_keys(get_object_vars($this)) );
	}
	
	/**
	 * Re-create the references to aspect objects having previously switched these for ids
	 * Must not be done as our own __wakeup as the factories are (currently) cached by the model
	 * so we've got to wait for the model to reinitialise them then tell us it's safe to go
	 */
	function refAspects()
	{
		$fAsp = &ApothFactory::_( 'assessment.aspect' );
		foreach( $this->_aspects as $id=>$asp ) {
			$this->_aspects[$id] = &$fAsp->getInstance( $id );
		}
		foreach( $this->_oldAspects as $id=>$asp ) {
			$this->_oldAspects[$id] = &$fAsp->getInstance( $id );
		}
	}
	
	/**
	 * Updates the assessment object with the given data
	 * 
	 * @param array $data  An associative array of properties to set for this assessment
	 * @param boolean $missingToNull  A switch to determine whether or not missing data is set to null
	 */
	function _assignDataArr( $data, $missingToNull = false )
	{
		$this->_data->id              = array_key_exists( 'id',             $data) ? $data['id'            ] : ( $missingToNull ? NULL : $this->_data->id             );
		$this->_data->parent          = array_key_exists( 'parent',         $data) ? $data['parent'        ] : ( $missingToNull ? NULL : $this->_data->parent         );
		$this->_data->title           = array_key_exists( 'title',          $data) ? $data['title'         ] : ( $missingToNull ? NULL : $this->_data->title          );
		$this->_data->short           = array_key_exists( 'short',          $data) ? $data['short'         ] : ( $missingToNull ? NULL : $this->_data->short          );
		$this->_data->description     = array_key_exists( 'description',    $data) ? $data['description'   ] : ( $missingToNull ? NULL : $this->_data->description    );
		$this->_data->color           = array_key_exists( 'color',          $data) ? $data['color'         ] : ( $missingToNull ? NULL : $this->_data->color          );
		$this->_data->always_show     = array_key_exists( 'always_show',    $data) ? $data['always_show'   ] : ( $missingToNull ? 0    : $this->_data->always_show    );
		$this->_data->group_specific  = array_key_exists( 'group_specific', $data) ? $data['group_specific'] : ( $missingToNull ? 1    : $this->_data->group_specific );
		$this->_data->ext_source      = array_key_exists( 'ext_source',     $data) ? $data['ext_source'    ] : ( $missingToNull ? NULL : $this->_data->ext_source     );
		$this->_data->ext_id          = array_key_exists( 'ext_id',         $data) ? $data['ext_id'        ] : ( $missingToNull ? NULL : $this->_data->ext_id         );
		$this->_data->locked          = array_key_exists( 'locked',         $data) ? $data['locked'        ] : ( $missingToNull ? NULL : $this->_data->locked         );
		$this->_data->locked_by       = array_key_exists( 'locked_by',      $data) ? $data['locked_by'     ] : ( $missingToNull ? NULL : $this->_data->locked_by      );
		$this->_data->locked_on       = array_key_exists( 'locked_on',      $data) ? $data['locked_on'     ] : ( $missingToNull ? NULL : $this->_data->locked_on      );
		$this->_data->created_by      = array_key_exists( 'created_by',     $data) ? $data['created_by'    ] : ( $missingToNull ? NULL : $this->_data->created_by     );
		$this->_data->created_on      = array_key_exists( 'created_on',     $data) ? $data['created_on'    ] : ( $missingToNull ? NULL : $this->_data->created_on     );
		$this->_data->modified_by     = array_key_exists( 'modified_by',    $data) ? $data['modified_by'   ] : ( $missingToNull ? NULL : $this->_data->modified_by    );
		$this->_data->modified_on     = array_key_exists( 'modified_on',    $data) ? $data['modified_on'   ] : ( $missingToNull ? NULL : $this->_data->modified_on    );
		$this->_data->valid_from      = array_key_exists( 'valid_from',     $data) ? $data['valid_from'    ] : ( $missingToNull ? NULL : $this->_data->valid_from     );
		$this->_data->valid_to        = array_key_exists( 'valid_to',       $data) ? $data['valid_to'      ] : ( $missingToNull ? NULL : $this->_data->valid_to       );
		$this->_data->deleted         = array_key_exists( 'deleted',        $data) ? $data['deleted'       ] : ( $missingToNull ? NULL : $this->_data->deleted        );
	}
	
	/**
	 * Retrieves the requested assessment object property
	 * 
	 * @param $prop string  The name of the property requested
	 * @return string  The property requested
	 */
	function getProperty( $prop )
	{
		return $this->_data->$prop;
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
	
	function setGroups( $groupIds )
	{
		$this->_groups = array();
		foreach( $groupIds as $gId ) {
			$this->_groups[$gId] = $gId;
		}
		return count( $this->_groups );
	}
	
	function getGroupIds()
	{
		return array_keys($this->_groups);
	}
	
	function getIsShown()
	{
		if( !isset( $this->_state->shown ) ) {
			$a = &$this->getAspects();
			$retVal = false;
			
			foreach( $a as $asp ) {
				$retVal = $retVal || $asp->getIsShown();
			}
			$this->_state->shown = $retVal;
		}
		return $this->_state->shown;
	}
	
	/**
	 * When an aspect is shown / hidden the "shown" state of the containing assessment needs to be re-calculated
	 */
	function unsetIsShown()
	{
		unset( $this->_state->shown );
	}
	
	function setEditsOn( $val = null )
	{
		$this->_state->edits = ( is_null($val) ? !$this->_state->edits : (bool)$val );
	}
	
	function getEditsOn()
	{
		return $this->_state->edits;
	}
	
	function hasGroup( $gId )
	{
		return isset( $this->_groups[$gId] );
	}
	
	
	
	/**
	 * Initialises all of the aspects for this assessment
	 * This needs to be done outside of the constructor as the aspects need
	 * a reference to the owning assessment, and you can't pass $this
	 * by reference inside the constructor
	 */
	function loadAspects()
	{
		// Reset the aspects for this assessment
		$this->_aspects = array();
		$this->_oldAspects = array();
		
		// Get aspects from the aspect factory
		$fAsp = &ApothFactory::_( 'assessment.aspect' );
		$requirements = array( 'assId'=>$this->_data->id );
		
		$this->setAspects( $fAsp->getInstances( $requirements ) );
	}
	
	function setAspects( $ids )
	{
		if( !is_array($ids) || empty($ids) ) {
			$this->_aspects = array();
		}
		else {
			$fAsp = &ApothFactory::_( 'assessment.aspect' );
			foreach( $ids as $id ) {
				$this->_aspects[$id] = &$fAsp->getInstance( $id );
			}
		}
	}
	
	/**
	 * Retrieves all of the aspects of this assessment
	 * 
	 * @return array  All the shown aspect objects
	 */
	function getAspects()
	{
		if( empty($this->_aspects) ) {
			$this->loadAspects();
		}
		
		return $this->_aspects;
	}
	
	/**
	 * Add a new aspect
	 */
	function addNewAspect()
	{
		$this->_newId = ( isset($this->_newId) ? ($this->_newId-1) : -1 );
		$fAsp = &ApothFactory::_( 'assessment.aspect' );
		$dummyAsp = &$fAsp->getDummy( $this->_newId );
		$dummyAsp->setAssessment( $this->_data->id );
		$this->_aspects[$this->_newId] = &$dummyAsp;
		return $this->_newId;
	}
	
	/**
	 * Add a new aspect by copying an existing one
	 */
	function copyAspect( $id )
	{
		$this->_newId = ( isset($this->_newId) ? ($this->_newId-1) : -1 );
		$fAsp = &ApothFactory::_( 'assessment.aspect' );
		$orig = $this->_aspects[$id];
		$fAsp->copy( $id, $this->_newId );
		$dummyAsp = &$fAsp->getInstance( $this->_newId );
		$this->_aspects[$this->_newId] = &$dummyAsp;
		return is_object($this->_aspects[$this->_newId]);
	}
	
	function removeAspect( $id )
	{
		if( isset($this->_aspects[$id]) ) {
			$this->_aspects[$id]->delete();
			$this->_oldAspects[$id] = &$this->_aspects[$id];
			unset( $this->_aspects[$id] );
			$retVal = true;
		}
		else {
			$retVal = false;
		}
		return $retVal;
	}
	
	function updateAspect( $id, $data )
	{
		if( !isset($this->_aspects[$id]) ) {
			return false;
		}
		$this->_aspects[$id]->update( $data );
	}
	
	/**
	 * Updates the assessment object with the given data
	 * @param array $data  An associative array of properties to set for this assessment
	 * @return boolean  True if the data was stored successfully, false otherwise
	 */
	function update( $data )
	{
		if( !is_array($data) ) {
			return false;
		}
		$aspsData = ( ( isset($data['asp']) && is_array($data['asp']) ) ? $data['asp'] : array() );
		unset( $data['asp'] );
		foreach( $aspsData as $aspId=>$aspData ) {
			$this->updateAspect( $aspId, $aspData );
		}
		
		$accessData = ( ( isset($data['access']) && is_array($data['access']) ) ? $data['access'] : array() );
		foreach( $accessData as $userGroup=>$state ) {
			$this->_access[$userGroup] = true;
		}
		unset( $data['access'] );
		
		$groupData = unserialize($data['admin_groups']);
		if( !is_array($groupData) ) {
			$groupData = array();
		}
		$this->setGroups( $groupData );
		unset( $data['admin_groups'] );
		
		$data['group_specific'] = isset($data['group_specific']);
		$data['always_show']    = isset($data['always_show']);
		$this->_assignDataArr( $data, false );
	}
	
	/**
	 * Retrieve the array holding the access permissions
	 */
	function getAccess()
	{
		return $this->_access;
	}
	
	/**
	 * Commit this assessment
	 * then commit all its aspects
	 */
	function commit()
	{
		$new = ( $this->_data->id < 0 );
		$fAss = &ApothFactory::_( 'assessment.assessment' );
		$fAss->commitInstance( $this->_data->id );
		
		foreach( $this->_aspects as $aId=>$asp ) {
			if( $new ) {
				$this->_aspects[$aId]->setAssessment( $this->_data->id );
			}
			$this->_aspects[$aId]->commit();
		}
		foreach( $this->_oldAspects as $aId=>$asp ) {
			$this->_oldAspects[$aId]->commit();
		}
		
		$this->_aspects = array(); // forget what we knew so we will load fresh data
		$this->_oldAspects = array(); // forget what we knew so we don't carry stale data around
		return $this->_data->id;
	}
}
?>