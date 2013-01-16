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

require_once( JPATH_COMPONENT.DS.'models'.DS.'extension.php' );

 /*
 * Planner List Model
 */
class PlannerModelList extends PlannerModel
{
	/**
	 * All the table column and associated information for each required category
	 * @access protected
	 * @var array  Array of table column info
	 */
	var $_tableInfo = array();
	
	/**
	 * Used by model to keep track of working table row when building table grid
	 * @access protected
	 * @var int  Table row number
	 */
	var $_rowCount;
	
	/**
	 * 2-D Array representing the final structure of dispaly tables per category
	 * @access protected
	 * @var array  Category ID-indexed array of table structure
	 */
	var $_grid = array();
	
	/**
	 * Used by view to keep track of which table row it is outputting per category
	 * @access protected
	 * @var array  Category ID-indexed array of table row numbers
	 */
	var $_max = array();
	
	/**
	 * Extends parent::setTasks specifically for lists view
	 * @param array $requirements  The values to search for
	 * @param bool $reset  Should we remove all the currently stored tasks from the model
	 */
	function setTasks( $requirements = array(), $reset = false )
	{
		// check if controller has issued a reset, if so empty _tableInfo
		if( $reset ) {
			$this->_tableInfo = array();
			$this->_rowCount = 0;
			$this->_grid = array();
			$this->_max = array();
		}
		
		// get a reference to the DB object
		$db = &JFactory::getDBO();
		
		$query = 'SELECT *'
			."\n".' FROM '.$db->nameQuote( '#__apoth_plan_category_cols' )
			."\n".' ORDER BY '.$db->nameQuote( 'column' );
		$db->setQuery( $query );
		$rawColInfo = $db->loadAssocList();
		// sort returned array
		foreach( $rawColInfo as $id=>$info ) {
			$catCols = &$this->_tableInfo[$info['cat_id']];
			
			$catCols['colInfo'][$info['column']] = $info;
			if( !isset($catCols['max_depth'])
			 || ($info['task_depth'] > $catCols['max_depth']) ) {
				$catCols['max_depth'] = (int)$info['task_depth'];
			}
		}
//		var_dump_pre( $this->_tableInfo, '$this->_tableInfo before loop:' );
		$allRoots = array_keys($this->_tableInfo);
		
		// Repeatedly use setTasks to retrieve the next layer of tasks
		// After the initial run using the given requirements, uses 
		$depth = 0;
		do {
			$foundIds = parent::setTasks( $requirements, $reset );
			// determine categories for these tasks, and while doing that ensure
			// on the next level we only look for tasks in categories that need more
			$newTasks = array();
			foreach( $foundIds as $foundId ) {
				// first time through we need to look at ancestors
				if( $depth == 0 ) {
					$myAnc = ApotheosisLibDb::getAncestors( $foundId, '#__apoth_plan_tasks', 'id', 'parent', true );
					unset( $myAnc[$foundId] );
					
					// determine category for this task ...
					$pNode = reset( array_intersect(array_keys($myAnc), $allRoots) );
					// ...and instantiate it if it doesn't exist
					if( !isset($this->_tasks[$pNode]) ) {
						$this->_tasks[$pNode] = new ApothTask( $pNode );
						$this->_taskTree[$pNode]['shownChildren'] = array();
						$this->_categories[] = $pNode;
					}
					$curCat = $pNode;
				}
				// subsequent runs use the category of the parent
				else {
					$pNode = $this->_tasks[$foundId]->getParent();
					$curCat = $curTasks[$pNode];
				}
				
				// set this in its place in the tree
				$this->_taskTree[$foundId]['shownChildren'] = array();
				$this->_taskTree[$pNode]['shownChildren'][] = (int)$foundId;
				
				if( $this->_tableInfo[$curCat]['max_depth'] > $depth ) {
					$newTasks[$foundId] = $curCat;
				}
			}
			$curTasks = $newTasks;
			
			// Prepare variables for next itteration
			if( $reset ) { $reset = false; }
			$requirements = array( 'parentId'=>array_keys($curTasks) );
			$depth++;
		} while( !empty($curTasks) );
		
		// call sort to order the tasks in the model
		$this->sort();
		
		// Clear out tableInfo for categories which didn't have any matched tasks
		$surplus = array_diff( array_keys($this->_tableInfo), $this->_categories );
		foreach( $surplus as $id ) {
			unset( $this->_tableInfo[$id] );
		}
		
		// Work out the row-spans for each node
		// (iterate through top-tasks, then use recursive func to traverse their trees)
		// then create grid data for each category
		foreach( $this->_categories as $catId ) {
			$this->_rowCount = 0;
			foreach( $this->_taskTree[$catId]['shownChildren'] as $taskId ) {
				$this->_setRows( $catId, $taskId, 0 );
			}
		}
	}
	
	
	/**
	 * Recursively traverses the task tree, setting the row-span count for each task
	 * relevant to whichever tier the task appears on via this route.
	 * Simultaneously sets up the 2d array which we use to allow row-by-row retrieval of data
	 * putting data in where there is any, and leaving un-set where there is none.
	 * @param int $catId  The id of the category whose data grid is to be calculated
	 * @param int $taskId  The id of the task whose rowspan is to be calculated
	 * @param int $depth  The current depth into the tree
	 * @return int  How many rows would the task span if all descendants were on separate rows.
	 */
	function _setRows( $catId, $taskId, $depth )
	{
		$t = &$this->_taskTree[$taskId];
		
		// Set up data-grid with data where available
		foreach( $this->_tableInfo[$catId]['colInfo'] as $colId=>$info ) {
			if( $info['task_depth'] == $depth ) {
				$this->_grid[$catId][$this->_rowCount][$colId] = $taskId;
			}
		}
		
		// recursively calculate the row span for this task at this tier
		if( empty($t['shownChildren']) || ($depth == $this->_tableInfo[$catId]['max_depth']) ) {
			$span = 1;
			$this->_rowCount++;
		}
		else {
			$span = 0;
			$nextDepth = $depth+1;
			foreach( $t['shownChildren'] as $c ) {
				$span += $this->_setRows( $catId, $c, $nextDepth );
			}
		}
		
		return ( $t[$depth] = $span );
	}
	
	/**
	 * Retrieves the column definitions array for a given category
	 * @param int $catId  the category ID whose column definitions are required
	 * @return array  column ID indexed array of column definitions
	 */
	function getTableInfo( $catId )
	{
		return $this->_tableInfo[$catId]['colInfo'];
	}
	
	/**
	 * Retrieves the maximum child task depth (in relation to top tasks = 0) for the category
	 * @param int $catId  the category ID whose maximum child depth is required
	 * @return int  the maximum child depth for this category
	 */
	function getMaxDepth( $catId )
	{
		return (int)$this->_tableInfo[$catId]['max_depth'];
	}
	
	/**
	 * Retrieves a row of data from _grid
	 * @param int $catId  the category ID whose row of data is required
	 * @return array  column ID indexed array of data
	 */
	function getRow( $catId )
	{
		if( !array_key_exists($catId, $this->_max) ) {
			$this->_max[$catId] = 0;
		}
		$retVal = $this->_grid[$catId][$this->_max[$catId]];
		if( is_null($retVal) ) {
			$this->_max[$catId] = 0;
		}
		else {
			$this->_max[$catId]++;
		}
		
		return $retVal;
	}
}
?>