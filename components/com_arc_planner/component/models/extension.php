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

jimport( 'joomla.application.component.model' );
require_once( JPATH_SITE.DS.'components'.DS.'com_arc_core'.DS.'libraries'.DS.'apoth_library.php' ); 

 /*
 * Planner Model Extension
 */
class PlannerModel extends JModel
{
	/**
	 * All the categories (root level tasks) that have been created are stored in here
	 * @access protected
	 * @var array  Array of category objects and their relationships
	 */
	var $_categories = array();
	
	/**
	 * All the tasks that have been created are stored in here
	 * @access protected
	 * @var array  Array of id-indexed task objects
	 */
	var $_tasks = array();
	
	/**
	 * A linked list of task id's and their relationships
	 * @access protected
	 * @var array  Id-indexed array of task relationships
	 */
	var $_taskTree = array();
	
	/**
	 * An array describing the previous search type and direction
	 * @access protected
	 * @var array  Associative array of search type and direction
	 */
	var $_sortPattern = array();
	
	/**
	 * Takes an associative array of criteria and loads all tasks that match them
	 * @param array $requirements  The values to search for
	 * @param bool $reset  Should we remove all the currently stored tasks from the model
	 * @return array  array of matched tasks ID's
	 */
	function setTasks( $requirements = array(), $reset = false )
	{
		// check if controller has issued a reset, if so empty the stored tasks
		if( $reset ) {
			$this->_categories = array();
			$this->_tasks = array();
			$this->_taskTree = array();
		}
		
		// get a database object
		$db = &JFactory::getDBO();
		$groupRequirements = array();
		
		if( isset($requirements['asPerson']) ) {
			$uId = ApotheosisLib::getJUserId( $requirements['asPerson'] );
			$requirements['personId'] = $requirements['asPerson'];
			unset( $requirements['asPerson'] );
		}
		else {
			$uId = null;
		}
		
		$where = array( 'del'=>'t.deleted != 1' );
		$select = 'SELECT t.*';
		// loop through search result(s) to build query
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
			case( 'taskId' ):
				$where[] = $db->nameQuote('t').'.'.$db->nameQuote('id').$assignPart;
				break;
			
			case( 'parentId'):
				$where[] = $db->nameQuote('t').'.'.$db->nameQuote('parent').$assignPart;
				break;
			
			case( 'descendants' ):
				if( $val ) {
					$select = 'SELECT d.*';
					$join['descendants'] = 'INNER JOIN '.$db->nameQuote( '#__apoth_plan_tasks_ancestry' ).' AS '.$db->nameQuote( 'a' )
						."\n".' ON a.ancestor = t.id'
						."\n".' INNER JOIN'.$db->nameQuote( '#__apoth_plan_tasks' ).' AS '.$db->nameQuote('d')
						."\n".' ON d.id = a.id';
				}
				break;
			
			case( 'special' ):
				if( $val == '1' ) {
					$join['special'] = 'INNER JOIN '.$db->nameQuote( '#__apoth_plan_tasks_special' ).' AS '.$db->nameQuote( 's' )
						."\n".' ON s.task_id = t.id';
				}
				else {
					$join['special'] = 'LEFT JOIN '.$db->nameQuote( '#__apoth_plan_tasks_special' ).' AS '.$db->nameQuote( 's' )
						."\n".' ON s.task_id = t.id';
					$where[] = 's.task_id IS NULL';
				}
				break;
			
			case( 'personId' ):
				$groupRequirements['members'] = $val;
			case( 'assignees' ):
				$join['assignees'] = 'INNER JOIN '.$db->nameQuote( '#__apoth_plan_groups' ).' AS '.$db->nameQuote( 'g' )
					."\n".' ON g.task_id = t.id'
					."\n".' INNER JOIN '.$db->nameQuote( '#__apoth_plan_group_members' ).' AS '.$db->nameQuote( 'gm' )
					."\n".' ON gm.group_id = g.id';
				$where[] = $db->nameQuote( 'gm' ).'.'.$db->nameQuote( 'person_id' ).$assignPart;
				break;
			}
		}
		
		// formulate query to return matched task id's
		$query = $select
			."\n".' FROM '.$db->nameQuote( '#__apoth_plan_tasks' ).' AS '.$db->nameQuote('t')
			.( empty($join)  ? '' : "\n ".implode("\n ", $join) )
			."\n".'~LIMITINGJOIN~'
			.( empty($where) ? '' : "\n WHERE ".implode("\n AND ", $where) );
		$db->setQuery( ApotheosisLibAcl::limitQuery($query, 'planner.tasks', false, false, $uId) );
		$tasks = $db->loadAssocList( 'id' );
		
		// loop through target id's and instantiate tasks as objects if we don't already have them
		foreach( $tasks as $id=>$taskArray ) {
			if( !array_key_exists($id, $this->_tasks) ) {
				$this->_tasks[$id] = new ApothTask( $id, $taskArray, $groupRequirements );
			}
		}
		
		return array_keys( $tasks );
	}
	
	/**
	 * Re-order the tasks in the tree, ordering each set of sub-tasks independantly
	 * If supplying a node, preceeding params should be false
	 * @param string $on  optional Sort type, defaults to task ordering
	 * @param string $direction  optional Directionality for the sort
	 * @param int $node  optional Task ID to limit the sorting to
	 */
	function sort( $on = false, $direction = false, $node = false )
	{
		// determine and store sort type
		$on = $this->_sortPattern['on'] = ( ($on !== false)
			? $on
			: ( !empty($this->_sortPattern['on']) ? $this->_sortPattern['on'] : 'order') );
		
		// determine and store sort direction
		$direction = $this->_sortPattern['direction'] = ( ($direction !== false)
			? $direction
			: ( !empty($this->_sortPattern['direction']) ? $this->_sortPattern['direction'] : 'ascending') );
		
		// determine start point of recursion (either given node or entire tasks tree)
		if( $node !== false ) {
			$ids = &$this->getTaskShownChildren( $node );
		}
		else {
			$ids = &$this->_categories;
		}
		
		$objects = array();
		foreach( $ids as $k=>$id ) {
			if( !isset($objects[$id]) && ($id != $node) ) {
				//recurse
				$this->sort( false, false, $id );
				
				// create a temp associative array of IDs => sort type
				$obj = $this->getTask( $id );
				$objects[$id] = $obj->_data[$on];
			}
		}
		
		// perform sort on temp array then apply results by re-ordering the original model array
		if( is_array($objects) ) {
			if( $direction == 'ascending' ) {
				asort( $objects );
			}
			else {
				arsort( $objects );
			}
			$ids = array_keys( $objects );
		}
	}
	
	/**
	 * Get a list of categories with matched tasks in them
	 * @return array  String names of categories indexed by id
	 */
	function getCategories()
	{
		$categoriesArray = array();
		foreach( $this->_categories as $k=>$catId ) {
			$cat = $this->getTask( $catId );
			$categoriesArray[$catId] = $cat->getTitle();
		}
		
		return $categoriesArray;
	}
	
	/**
	 * Retrieve ids of all the top level tasks we've found
	 * 
	 * @param int $categoryId  The id of the category whose top tasks are to be returned
	 * @return array  An indexed array of the task ID's to be displayed immediately below the given category
	 */
	function getTopTasks( $categoryId )
	{
		return $this->getTaskShownChildren( $categoryId );
	}
	
	/**
	 * Retrieves (by reference) the task specified
	 * @param int $id  The id of the task to retrieve
	 * @return mixed  The task object with the given id (null if invalid or missing id given)
	 */
	function &getTask( $id )
	{
		if( !array_key_exists($id, $this->_tasks) ) {
			$this->_tasks[$id] = new ApothTask( $id );
		}
		
		return $this->_tasks[$id];
	}
	
	/**
	 * Retrieves an array of task objects indexed on task ID
	 * @param array $list  Indexed array of task IDs
	 * @return array  The array of task objects indexed on task ID
	 */
	function getTaskObjList( $list )
	{
		$objList = array();
		foreach( $list as $k=>$id ) {
			if( array_key_exists($id, $this->_tasks) ) {
				$objList[$id] = $this->_tasks[$id];
			}
			else {
				$objList[$id] = $this->_tasks[$id] = new ApothTask( $id );
			}
		}
		
		return $objList;
	}
	
	/**
	 * Retrieves an array of all task objects which matched our current search
	 *
	 * @return array  An indexed array of the ids of the tasks which matched our search
	 */
	function getMatchedTasks()
	{
		$list = array();
		$tk = array_keys( $this->_taskTree );
		foreach( $tk as $id ) {
			if( $this->_taskTree[$id]['matched'] ) {
				$list[] = $id;
			}
		}
		return $list;
	}
	
	/**
	 * Retrieves only the shown children of the task specified
	 * @param int $id  The id of the task whose shown children are to be retrieved
	 * @return array  An indexed array of the id's (strings) of the tasks to be displayed below the one with the given id
	 */
	function &getTaskShownChildren( $id )
	{
		return $this->_taskTree[$id]['shownChildren'];
	}
}
?>