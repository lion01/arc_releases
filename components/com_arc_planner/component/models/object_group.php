<?php
/**
 * @package     Arc
 * @subpackage  Planner
 * @copyright   Copyright (C) 2005 Punnet. All rights reserved. See COPYRIGHT_ARC.txt
 * @license     http://www.gnu.org/licenses/gpl.html GNU/GPL. See LICENSE_ARC.txt
 * Arc is free software: you can redistribute it and/or modify
 * it under the terms of version 3 of the GNU General Public License
 * as published by the Free Software Foundation.
 * The disclaimer of warranty as stated in the GPL applies to this program
 */

defined( '_JEXEC' ) or die( 'Restricted access' );

/**
 * Planner Group Object
 */
class ApothGroup extends JObject
{
	/**
	 * All the data for this assignment group (equates to a row in the db)
	 * @access  protected
	 * @var  array
	 */
	var $_data = array();
	
	/**
	 * All people in roles in this assignment group
	 * 2d array: role=>personId=>personObject
	 * @access  protected
	 * @var array
	 */
	var $_roles = array();
	
	/**
	 * All the updates associated with this group
	 * 1d array of updates objects indexed on update ID
	 * @access protected
	 * @var array
	 */
	var $_updates = array();
	
	/**
	 * References to all the updates associated with this group, organised by category
	 * 1d array of updates objects indexed on category and update ID
	 * @access protected
	 * @var array
	 */
	var $_updatesCat = array();
	
	/**
	 * Whether or not this groups updates should be shown
	 * @access  protected
	 * @var boolean
	 */
	var $_showUpdates;
	
	/**
	 * What actions to perform when this group's "complete" flag is changed
	 * @access protected
	 * @var array
	 */
	var $_triggers;
	
	/**
	 * Constructs an assignment group object.
	 * The result is either empty or if an id is given it is
	 * populated by the $data array or by retrieving data from the db 
	 * @param int $id  optional If not provided an empty group object is created.
	 * @param array $data  optional If given along with an id this is used as the data for the object (omits role assignments)
	 * @return object  The newly created assignment group object
	 */
	function __construct( $id = false, $data = array() )
	{
		parent::__construct();
		$this->_triggers = array(0=>array(), 1=>array());
		$db = &JFactory::getDBO();
		
		// if we have an id but no supplied data
		if( $id !== false && empty($data) ) {
			$this->_data['id'] = (int)$id;
			$mainQuery = $this->getInitQuery();
			$db->setQuery( $mainQuery );
			$data = $db->loadAssoc();
		}
			
		if( !empty($data) ) {
			// store the data in the object
			$this->_data = $data;
		}
		// if the limit query has blocked data assimilation or none supplied then return now
		else {
			return;
		}
		
		// find and instanciate assignment group members
		$membersQuery = $this->_getMembersQuery();
		$db->setQuery( $membersQuery );
		$memberData = $db->loadAssocList();
		$roleData = array();
		
		foreach( $memberData as $k=>$memberArray ) {
			$data = array( 'person_id'=>$memberArray['person_id'],
				'valid_from'=>$memberArray['valid_from'],
				'valid_to'=>$memberArray['valid_to'] );
			$roleData[$memberArray['role']][] = $data;
		}
		
		foreach( $roleData as $role=>$data ) {
			$this->setPeopleInRole( $role, $data );
		}
		
		
		// find any completion triggers
		$triggerQuery = 'SELECT '.$db->nameQuote( 'class' ).', '.$db->nameQuote( 'func' ).', '.$db->nameQuote( 'undo_func' )
			."\n".' FROM '.$db->nameQuote( '#__apoth_plan_complete_triggers' )
			."\n".' WHERE '.$db->nameQuote( 'task_id' ).' = '.$this->_data['task_id'];
		$db->setQuery( $triggerQuery);
		$tmp = $db->loadAssocList();
		foreach( $tmp as $trigger ) {
			$this->_triggers[1][] = $trigger['class'].'::'.$trigger['func'].';';
			$this->_triggers[0][] = $trigger['class'].'::'.$trigger['undo_func'].';';
		}
		
		$this->setUpdatesShown( false );
	}
	
	/**
	 * Gives the query which is to be run to find the assignment groups for this tasks
	 * @see getStatQueryStatic()
	 * @return string  The query to execute to find statistical data
	 */
	function getInitQuery()
	{
		return ApothGroup::getInitQueryStatic( array('groupId'=>$this->_data['id']) );
	}
	
	/**
	 * Gives the query to be run to find the assignment groups for the given tasks
	 * @param int $groupId  The group id to use in the query string
	 * @param int $taskId  The task id to use in the query string
	 * @return string  The query to execute to find the assignment groups
	 */
	function getInitQueryStatic( $requirements = array() )
	{
		$db = &JFactory::getDBO();
		
		foreach( $requirements as $col=>$val ) {
			if( is_array($val) ) {
				foreach( $val as $k=>$v ) {
					$val[$k] = $db->Quote( $v );
				}
				$assignPart = ' IN ('.implode( ', ',$val ).')';
			}
			else {
				$assignPart = ' = '.$db->Quote( $val );
			}
			switch( $col ) {
			case( 'groupId' ):
				$where[] = $db->nameQuote('g').'.'.$db->nameQuote('id').$assignPart;
				break;
			
			case( 'taskId' ):
				$where[] = $db->nameQuote('t').'.'.$db->nameQuote('id').$assignPart;
				break;
			
			case( 'assignees'):
				$join['members'] = 'INNER JOIN '.$db->nameQuote( '#__apoth_plan_group_members' ).' AS '.$db->nameQuote( 'gm' )
					."\n".' ON '.$db->nameQuote('gm').'.'.$db->nameQuote('group_id').' = '.$db->nameQuote('g').'.'.$db->nameQuote('id')
					."\n".'AND '.$db->nameQuote('gm').'.'.$db->nameQuote('role').' IN ( '.$db->Quote( 'assignee' ).', '.$db->Quote( 'leader' ).')'
					."\n".'AND '.ApotheosisLibDb::dateCheckSql( 'gm.valid_from', 'gm.valid_to', date('Y-m-d H:i:s'), date('Y-m-d H:i:s') );
				$where['members'] = $db->nameQuote( 'gm' ).'.'.$db->nameQuote( 'person_id' ).$assignPart;
				break;
			
			case( 'members'):
				$join['members'] = 'INNER JOIN '.$db->nameQuote( '#__apoth_plan_group_members' ).' AS '.$db->nameQuote( 'gm' )
					."\n".' ON '.$db->nameQuote('gm').'.'.$db->nameQuote('group_id').' = '.$db->nameQuote('g').'.'.$db->nameQuote('id')
					."\n".'AND '.ApotheosisLibDb::dateCheckSql( 'gm.valid_from', 'gm.valid_to', date('Y-m-d H:i:s'), date('Y-m-d H:i:s') );
				$where['members'] = $db->nameQuote( 'gm' ).'.'.$db->nameQuote( 'person_id' ).$assignPart;
				break;
			}
		}
		
		$query = 'SELECT'
			."\n".' g.'.$db->nameQuote( 'id' ).' AS id'
			."\n".', t.'.$db->nameQuote( 'id' ).' AS '.$db->nameQuote( 'task_id' )
			."\n".', g.'.$db->nameQuote( 'complete' ).' AS '.$db->nameQuote( 'complete' )
			."\n".', MAX( IF((t.'.$db->nameQuote('micro').' = 0), g.'.$db->nameQuote('progress').', IF((m.'.$db->nameQuote('task_id').' IS NULL), 0, 100) ) ) AS '.$db->nameQuote('progress')
			."\n".', g.'.$db->nameQuote( 'due' ).' AS '.$db->nameQuote( 'due' )
			."\n".', SUM(IF('.ApothGroup::_getMicroSql().', 1, 0)) AS '.$db->nameQuote( 'num_updates' )
			.ApothGroup::_getFromJoinSql()
			.( empty($join)  ? '' : "\n ".implode("\n ", $join) )
			."\n".'~LIMITINGJOIN~'
			.( empty($where) ? '' : "\n WHERE ".implode("\n AND ", $where) )
			."\n".' GROUP BY t.id, g.id';
		
		return ApotheosisLibAcl::limitQuery( $query, 'planner.groups' );
	}
	
	/**
	 * Internal utility to get sql which can be used to filter for updates (micro- where appropriate)
	 */
	function _getMicroSql()
	{
		$str = '((t.micro = 0 AND u.id IS NOT NULL) OR (t.micro = 1 AND m.task_id IS NOT NULL))';
		return $str;
	}
	
	function _getFromJoinSql()
	{
		$db = &JFactory::getDBO();
		$str = "\n".' FROM '.$db->nameQuote( '#__apoth_plan_tasks' ).' AS t'
			."\n".' INNER JOIN '.$db->nameQuote( '#__apoth_plan_groups' ).' AS g'
			."\n".'    ON g.task_id = t.id'
			."\n".'    OR (g.task_id = t.parent AND t.micro = 1)'
			."\n".' LEFT JOIN '.$db->nameQuote( '#__apoth_plan_updates' ).' as u'
			."\n".'   ON u.`group_id` = g.`id`'
			."\n".' LEFT JOIN '.$db->nameQuote( '#__apoth_plan_update_microtasks' ).' AS m'
			."\n".'   ON m.task_id = t.id'
			."\n".'  AND m.update_id = u.id';
		return $str;
	}
	
	function _getMembersQuery()
	{
		$db = &JFactory::getDBO();
		$str = 'SELECT *'
			."\n".' FROM '.$db->nameQuote( '#__apoth_plan_group_members' )
			."\n".' WHERE '.$db->nameQuote( 'group_id' ).' = '.$db->Quote( $this->_data['id'] )
			."\n".' AND '.ApotheosisLibdb::dateCheckSql( 'valid_from', 'valid_to', date('Y-m-d H:i:s'), date('Y-m-d H:i:s') );
		return $str;
	}
	
	/**
	 * Retrieves the ID of the group
	 * @return int  The group ID
	 */
	function getId()
	{
		return (int)$this->_data['id'];
	}
	
	/**
	 * Retrieves the _data array from the group
	 * @return array  The _data array from the group
	 */
	function getData()
	{
		return $this->_data;
	}
	
	/**
	 * Retrieves the task ID this group is assigned to
	 * @return int  The ID of the task
	 */
	function getTaskId()
	{
		return (int)$this->_data['task_id'];
	}
	
	/**
	 * Sets the task ID that this group should be assigned to
	 * @param int $taskId  The task ID this group should be assigned to
	 */
	function setTaskId( $taskId )
	{
		$this->_data['task_id'] = (int)$taskId;
	}
	
	/**
	 * Retrieves the completed or not status of the assignment group
	 * @return boolean  True if complete, false otherwise
	 */
	function getComplete()
	{
		return (bool)$this->_data['complete'];
	}
	
	/**
	 * Sets the complete or not status of the assignment group
	 * @param boolean $complete  True if complete, false otherwise
	 */
	function setComplete( $complete )
	{
		$this->_data['complete'] = (bool)$complete;
		
		foreach( $this->_triggers[(int)$complete] as $cmd ) {
			foreach( $this->_roles['assignee']['people'] as $pId=>$person ) {
				$c = str_replace( '~PERSONID~', $pId, $cmd );
				eval( $c );
			}
		}
	}
	
	/**
	 * Retrieves the progress of the assignment group
	 * @return int  The percentage of the assignment group that is complete
	 */
	function getProgress()
	{
		return (int)$this->_data['progress'];
	}
	
	/**
	 * Sets the progress of the assignment group
	 * @param int $progress  Assignment group completeness as a percentage
	 */
	function setProgress( $progress )
	{
		$this->_data['progress'] = (int)$progress;
	}
	
	/**
	 * Checks the progress of the assignment group against the highest progress value
	 * entered for its updates and refreshes group progress if necessary
	 */
	function refreshProgress()
	{
		$maxP = 0;
		foreach( $this->_updatesCat as $cat ) {
			if( !empty($cat) ) {
				$update = end($cat);
				$p = $update->getProgress();
				if( $p > $maxP ) {
					$maxP = $p;
				}
			}
		}
		$this->_data['progress'] = $maxP;
	}
	
	/**
	 * Retrieves the due date of the assignment group
	 * @return string  The date string of the assignment due date
	 */
	function getDue()
	{
		return $this->_data['due'];
	}
	
	/**
	 * Sets the due date of the assignment group
	 * @param string $due  Due date for the assignment group
	 */
	function setDue( $due )
	{
		$this->_data['due'] = $due;
	}
	
	
	/**
	 * Retrieves the raw data for the named role, or all roles if none given. Null if given role not found
	 * @param $role string  The role name whose data is to be retrieved
	 * @return array|null  The array of raw data for the given role, or a role-indexed array of such, or null if given role not found
	 */
	function getRoleData( $role = false )
	{
		if( $role === false ) {
			foreach( $this->_roles as $role=>$details ) {
				$retVal[$role] = $details['data'];
			}
		}
		elseif( isset($this->_roles[$role]) ) {
			$retVal = $this->_roles[$role]['data'];
		}
		else {
			$retVal = null;
		}
		
		return $retVal;
	}
	
	
	/**
	 * Retrieves an array of people objects for a given role
	 * @param string $role  The role to be retrieved
	 * @return array  An Arc ID indexed array of people objects in the given role
	 */
	function getPeopleInRole( $role )
	{
		if( isset($this->_roles[$role]['people']) ) {
			$retVal = $this->_roles[$role]['people'];
		}
		else {
			$retVal = array();
		}
		
		return $retVal;
	}
	
	/**
	 * Sets people into the specified role in the assignment group
	 * @param string $role  The role to place people into
	 * @param array $data  An array of data sets (associative array of person_id, valid_from, valid_to)
	 */
	function setPeopleInRole( $role, $data )
	{
		$db = JFactory::getDBO();
		foreach( $data as $datum ) {
			$people[] = $db->Quote($datum['person_id']);
			$this->_roles[$role]['data'][$datum['person_id']] = $datum;
		}
		$this->_roles[$role]['people'] = ApotheosisLib::getUserList( ' WHERE p.id IN ('.(implode(', ', $people)).')', false );
	}
	
	
	/**
	 * Moves a person from one role to another
	 * @param $id string  The person id of the person to move
	 * @param $from string  The role to move the person from
	 * @param $to string   The role to move the person to
	 * @return boolean  True on success, false on failure
	 */
	function updatePersonRole( $id, $from, $to )
	{
		if( isset($this->_roles[$from]['people'][$id]) ) {
			$this->_roles[$to]['people'][$id] = $this->_roles[$from]['people'][$id];
			$this->_roles[$to]['data'][$id] = $this->_roles[$from]['data'][$id];
			
			$now = time();
			$t1 = date( 'Y-m-d H:i:s', ($now-1) );
			$t2 = date( 'Y-m-d H:i:s', $now );
			$this->_roles[$from]['data'][$id]['valid_to'  ] = $t1;
			$this->_roles[$to  ]['data'][$id]['valid_from'] = $t2;
			
			unset( $this->_roles[$from]['people'][$id] );
		}
	}
	
	/**
	 * Retrieves the status of whether or not updates are shown for this assignment group
	 * @return boolean  True if updates shown, false otherwise
	 */
	function getUpdatesShown()
	{
		return (bool)$this->_showUpdates;
	}
	
	/**
	 * Retrieves the status of whether or not updates are sohwn for this assignment group
	 * @param string $due  Due date for the assignment group
	 */
	function setUpdatesShown( $updatesShown )
	{
		$this->_showUpdates = (bool)$updatesShown;
	}
	
	/**
	 * Retrieves (by reference) the update specified
	 * @param int $id  The id of the update to retrieve
	 * @return mixed  The update object with the given id (null if invalid or missing id given)
	 */
	function &getUpdate( $id )
	{
		if( ($id == 'new' )
		 || (substr($id, 0, 4) == 'new_') ) {
			$retVal = new ApothUpdate();
		}
		else {
			if( empty($this->_updates) ) {
				$this->_loadUpdates();
			}
			
			if( array_key_exists($id, $this->_updates) ) {
				$retVal = &$this->_updates[$id];
			}
			else {
				$retval = null;
			}
		}
		return $retVal;
	}
	
	/**
	 * Retrieves an array of this assignment groups update objects indexed on update ID
	 * loading them if necessary
	 * @param $categorised boolean  Should the returned array have the updates grouped by category? (defaults to false)
	 * @param $category mixed  if we're loading a categorised list, then this indicates that we should 
	 * @return array  An associative array of update objects (optionally grouped into category arrays)
	 */
	function &getUpdates( $categorised = false, $category = null )
	{
		if( empty($this->_updates) ) {
			$this->_loadUpdates();
		}
		
		if( $categorised ) {
			if( is_null($category) ) {
				$retVal = &$this->_updatesCat;
			}
			else {
				$retVal = array($category=>&$this->_updatesCat[$category]);
			}
			if( !is_array($retVal) ) {
				$retVal = array($category=>array());
			}
		}
		else {
			if( is_null($category) ) {
				$retVal = &$this->_updates;
			}
			else {
				$retVal = &$this->_updatesCat[$category];
			}
			if( !is_array($retVal) ) {
				$retVal = array();
			}
		}
		return $retVal;
	}
	
	/**
	 * Sets the array of this groups update objects indexed on update ID
	 * @param array $updates  An associative array of update objects
	 */
	function setUpdates( $updates )
	{
		if( is_string($updates) ) {
			$updates = array( $updates );
		}
		if( is_array($updates) ) {
			if( !is_object(reset($updates)) ) {
				$updates = array();
			}
		}
		
		$this->_updatesCat = array();
		$this->_updates = $updates;
		foreach( $this->_updates as $id=>$update ) {
			$this->_updatesCat[$update->getCategory()][$id] = &$this->_updates[$id];
		}
	}
	
	/**
	 * Loads updates for this group from db
	 */
	function _loadUpdates()
	{
		// get a database object
		$db = &JFactory::getDBO();
		
		// build query and retrieve data from database
		$query = 'SELECT u.*'
			.ApothGroup::_getFromJoinSql()
			."\n".'~LIMITINGJOIN~'
			."\n".'WHERE '.ApothGroup::_getMicroSql()
			."\n".'  AND u.'.$db->nameQuote( 'group_id' ).' = '.$db->Quote( $this->getId() )
			."\n".'  AND t.'.$db->nameQuote( 'id' ).' = '.$db->Quote( $this->getTaskId() )
			."\n".'ORDER BY id'; // updates are sequentially numbered, so higher id = later update
		$db->setQuery( ApotheosisLibAcl::limitQuery($query, 'planner.updates') );
		$this->_updates = $db->loadAssocList( 'id' );
		
		// instantiate the updates we have found
		$this->_updatesCat = array();
		foreach( $this->_updates as $id=>$updateArray ) {
			$this->_updates[$id] = new ApothUpdate( $id, $updateArray );
			$this->_updatesCat[$updateArray['category']][$id] = &$this->_updates[$id];
		}
		$this->_data['num_updates'] = count($this->_updates);
	}
	
	/**
	 * Adds the given update to this group's list of updates
	 *
	 * @param object $u  The update object to be added to this group
	 */
	function addUpdate( $u )
	{
		if( is_object($u) && (strtolower(get_class($u)) == 'apothupdate') ) {
			$id = $u->getId();
			$this->_updates[$id] = $u;
			$this->_updatesCat[$u->getCategory()][$id] = &$this->_updates[$id];
			$this->_data['num_updates']++;
		}
	}
	
	/**
	 * Removes the given update from this group's list of updates
	 *
	 * @param int $updateId  The id of the update object to be removed from this group
	 * @return boolean  True on success, false on failure
	 */
	function removeUpdate( $updateId )
	{
		if( isset($this->_updates[$updateId]) ) {
			$cat = $this->_updates[$updateId]->getCategory();
			unset( $this->_updatesCat[$cat][$updateId] );
			unset( $this->_updates[$updateId] );
			$this->_data['num_updates']--;
			return true;
		}
		else {
			return false;
		}
	}
	
	/**
	 * Retrieves the number of updates for this assignment group
	 * @return int  The number of updates
	 */
	function getUpdatesCount()
	{
		return (int)$this->_data['num_updates'];
	}
	
	/**
	 * Finds the roles (if any) of a person in this group
	 * @param string $personId  The Arc person id to look for
	 * @return array  Role indexed array of boolean results
	 */
	function roles( $personId )
	{
		$retVal = false;
		
		foreach( $this->_roles as $role=>$roleArray ) {
			$retVal[$role] = array_key_exists( $personId, $roleArray['people'] );
		}
		
		return $retVal;
	}
	
	/**
	 * Commits the current state of a group by writing it to the database
	 * 
	 * @return boolean|int  The group id on success, false on failure
	 */
	function commit()
	{
		$db = &JFactory::getDBO();
		
		$queryMid = 'SET '
			     .$db->nameQuote( 'complete' ).' = '.$db->Quote( $this->_data['complete'] )
			.', '.$db->nameQuote( 'progress' ).' = '.$db->Quote( $this->_data['progress'] )
			.', '.$db->nameQuote( 'due' )     .' = '.$db->Quote( $this->_data['due'] )
			.', '.$db->nameQuote( 'task_id' ) .' = '.$db->Quote( $this->_data['task_id'] );
		if( empty($this->_data['id']) ) {
			$query = 'INSERT INTO '.$db->nameQuote( '#__apoth_plan_groups' )
				."\n".$queryMid;
		}
		else {
			$query = 'UPDATE '.$db->nameQuote( '#__apoth_plan_groups' )
				."\n".$queryMid
				."\n".' WHERE '.$db->nameQuote( 'id' ).' = '.$db->Quote( $this->_data['id'] );
		}
		$db->setQuery( $query );
		$db->Query();
		
		// work out a nice return value
		if( $db->getErrorMsg() != '' ) {
			return false;
		}

		if( empty($this->_data['id']) ) {
			$this->_data['id'] = $db->insertId();
			$retVal = $this->_data['id'];
		}
		
		// Deal with group memberships
		$membersQuery = $this->_getMembersQuery();
		$db->setQuery( $membersQuery );
		$tmp = $db->loadAssocList();
		
		$newRoles = $this->getRoleData();
		
		$gId = $db->Quote($this->_data['id']);
		foreach( $tmp as $row ) {
			$cur = $newRoles[$row['role']][$row['person_id']];
			$where = $db->nameQuote('group_id').' = '.$gId
				."\n".' AND '.$db->nameQuote('person_id').' = '.$db->Quote($row['person_id'])
				."\n".' AND '.$db->nameQuote('role').' = '.$db->Quote($row['role']);
			
			if( is_null($cur) ) {
				$deleteClauses[] = '('.$where.')';
			}
			else {
				if( ($row['valid_from'] != $cur['valid_from'])
				 || ($row['valid_to']   != $cur['valid_to']) ) {
				 	$cur['where'] = $where;
				 	$updates[] = $cur;
				 }
				unset( $newRoles[$row['role']][$row['person_id']] );
			}
		}
		
		$tbl = $db->nameQuote('#__apoth_plan_group_members');
		
		// Do deletes, if any
		if( is_array($deleteClauses) ) {
			$query = 'DELETE FROM '.$tbl
				."\n".' WHERE' 
				."\n".implode( "\n".' OR ', $deleteClauses );
			$db->setQuery( $query );
			$db->Query();
		}
		
		// Do updates, if any
		if( is_array($updates) ) {
			foreach( $updates as $row ) {
				$query = 'UPDATE '.$tbl
					."\n".'SET '.$db->nameQuote( 'valid_from' ).' = '.$db->Quote( $row['valid_from'] )
					."\n".'  , '.$db->nameQuote( 'valid_to'   ).' = '.( empty($row['valid_to']) ? 'NULL' : $db->Quote($row['valid_to']) )
					."\n".'WHERE '.$where;
				$db->setQuery( $query );
				$db->Query();
			}
		}
		
		// Do inserts, if any
		if( is_array($newRoles) ) {
			foreach( $newRoles as $role=>$pData ) {
				$r = $db->Quote($role);
				foreach( $pData as $pId=>$row ) {
					$insertVals[] = '('
						.$gId
						.', '.$db->Quote($row['person_id'])
						.', '.$r
						.', '.$db->Quote($row['valid_from'])
						.', '.( empty($row['valid_to']) ? 'NULL' : $db->Quote($row['valid_to']) )
						.')';
				}
			}
			if( !empty($insertVals) ) {
				$query = 'INSERT INTO '.$tbl
					."\n".'VALUES'
					."\n".implode( "\n".', ', $insertVals );
				$db->setQuery( $query );
				$db->Query();
			}
		}
		
		// refresh temp tables
		ApotheosisLibAcl::getUserTable( 'planner.groups',  $user->id, true ); // refresh the list of allowed groups
		ApotheosisLibAcl::getUserTable( 'planner.tasks',   $user->id, true ); // refresh the list of allowed tasks
		ApotheosisLibAcl::getUserTable( 'planner.updates', $user->id, true ); // refresh the list of allowed updates
		
		return $retVal;
	}
}
?>