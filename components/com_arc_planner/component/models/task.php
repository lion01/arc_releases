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
 * Planner Task Model
 */
class PlannerModelTask extends PlannerModel
{
	/**
	 * Extends parent::setTasks specifically for tasks view
	 * @param array $requirements  The values to search for
	 * @param bool $reset  Should we remove all the currently stored tasks from the model
	 */
	function setTasks( $requirements = array(), $reset = false )
	{
		// process found data in a manner suited to task model
		$foundIds = $remainingIds = parent::setTasks( $requirements, $reset );
		$allRoots = ApotheosisLibDb::getRootItems( '#__apoth_plan_tasks' );
		
		// loop through each task and build ancestry list
		foreach( $foundIds as $foundId ) {
			$myAnc = ApotheosisLibDb::getAncestors( $foundId, '#__apoth_plan_tasks', 'id', 'parent', true );
			unset( $myAnc[$foundId] );
			$anc[$foundId] = $myAnc;
			
			// determine category for this task and instantiate it if it doesn't exist
			$myRoot = reset( array_intersect(array_keys($myAnc), $allRoots) );
			if( !isset($this->_tasks[$myRoot]) && ($myRoot !== false) ) {
				$this->_tasks[$myRoot] = new ApothTask( $myRoot );
				$this->_taskTree[$myRoot]['matched'] = false;
				$this->_taskTree[$myRoot]['shownChildren'] = array();
				$this->_categories[] = $myRoot;
			}
			
			// determine if this is a category, or a toptask, or not (only if reset = true and we have a new search)
			if( $reset ) {
				$tmp = array_intersect( array_keys($myAnc), $foundIds );
				if( empty($myAnc) ) { // is a category
					$this->_categories[] = $foundId;
				}
				elseif( empty($tmp) ) { // is a top task
					$this->_taskTree[$foundId]['matched'] = true;
					$this->_taskTree[$foundId]['shownChildren'] = array();
					$this->_taskTree[$myRoot]['shownChildren'][] = (int)$foundId;
					unset( $remainingIds[array_search($foundId, $remainingIds)] );
				}
			}
		}
		
		// loop through remaining tasks (not toptasks) and build object tree up to its toptask
		foreach( $remainingIds as $deepId ) {
			$this->_taskTree[$deepId]['matched'] = $reset;
			$this->_taskTree[$deepId]['shownChildren'] = array();
			$myParent = $this->_tasks[$deepId]->getParent();
			
			// now process the parent(s), instantiating if necessary
			$lastChild = $deepId;
			while( !isset($this->_tasks[$myParent]) ) {
				$this->_tasks[$myParent] = new ApothTask( $myParent );
				$this->_taskTree[$myParent]['matched'] = false;
				$this->_taskTree[$myParent]['shownChildren'][] = (int)$lastChild;
				$lastChild = $myParent;
				$myParent = $this->_tasks[$myParent]->getParent();
			}
			
			// set shownChildren for first existing ancestor
			$this->_taskTree[$myParent]['shownChildren'][] = (int)$lastChild;
		}
		
		// call sort to order the tasks in the model
		$this->sort();
		
		// expand task if only one is found as result of a new search
		if( $reset && (count($foundIds) == 1) ) {
			$this->toggleDetails( reset($foundIds) );
		}
	}
	
	/**
	 * Retrieves the matched status of the task specified
	 * @param int $id  The ID of the task whose matched status is to be retrieved
	 * @return boolean  True if matched, False if not
	 */
	function getTaskMatched( $id )
	{
		return $this->_taskTree[$id]['matched'];
	}
	
	/**
	 * Changes whether the details section for a task is displayed
	 * Calls display to re-render the page
	 * @param int $id  The ID of the task whose details shown status is to be toggled
	 */
	function toggleDetails( $id )
	{
		$detailsShown = !$this->_tasks[$id]->getDetailsShown();
		
		$this->_tasks[$id]->setDetailsShown( $detailsShown );
	}
	
	/**
	 * Changes whether the details section for all currently loaded tasks are displayed (based on first one)
	 * Calls display to re-render the page
	 */
	function toggleManyDetails()
	{
		// Only want to process tasks under those we're currently showing
		$queue = array();
		foreach( $this->_categories as $catId ) {
			$ids = &$this->getTaskShownChildren( $catId );
			foreach( $ids as $id ) {
				$queue[] = $id;
			}
		}
		
		if( !empty($queue) ) {
			$detailsShown = !$this->_tasks[reset($queue)]->getDetailsShown();
			
			while( !is_null(($id = array_shift($queue))) ) {
				$this->_tasks[$id]->setDetailsShown( $detailsShown );
				
				// Add this task's children to the queue
				$ids = &$this->getTaskShownChildren( $id );
				foreach( $ids as $id ) {
					$queue[] = $id;
				}
			}
		}
	}
	
	/**
	 * Changes whether the subtasks section for a task is displayed
	 * @param int $id  The ID of the task whose subtasks shown status is to be toggled
	 */
	function toggleSubtasks( $id )
	{
		$shownChildren = $this->getTaskShownChildren( $id );
		$subtasks = $this->_tasks[$id]->getLinkedTasks( array( 'type'=>'children' ) );
		
		if( count($shownChildren) < count($subtasks) ) {
			$this->_taskTree[$id]['shownChildren'] = $subtasks;
			$this->setTasks( array('taskId'=>$this->_taskTree[$id]['shownChildren']) );
		}
		else {
			$subtasksShown = $this->_tasks[$id]->getSubtasksShown();
			$this->_tasks[$id]->setSubtasksShown( !$subtasksShown );
		}
	}
	
	/**
	 * Changes whether the sub-tasks section for all currently loaded tasks are displayed (based on first one)
	 * Calls display to re-render the page
	 */
	function toggleManySubtasks()
	{
		$this->_allSubsOn = !( (bool)$this->_allSubsOn );
		
		$queue = array();
		foreach( $this->_categories as $catId ) {
			$ids = &$this->getTaskShownChildren( $catId );
			foreach( $ids as $id ) {
				$queue[] = $id;
			}
		}
		
		if( !empty($queue) ) {
			while( !is_null(($id = array_shift($queue))) ) {
				// Make sure we have all the children loaded first
				$shownChildren = $this->getTaskShownChildren( $id );
				$subtasks = $this->_tasks[$id]->getLinkedTasks( array( 'type'=>'children' ) );
				if( count($shownChildren) < count($subtasks) ) {
					$this->_taskTree[$id]['shownChildren'] = $subtasks;
					$this->setTasks( array('taskId'=>$this->_taskTree[$id]['shownChildren']) );
				}
				
				// Now toggle the display
				$this->_tasks[$id]->setSubtasksShown( $this->_allSubsOn );
				
				// Add this task's children to the queue
				$ids = &$this->getTaskShownChildren( $id );
				foreach( $ids as $id ) {
					$queue[] = $id;
				}
			}
			
		}
	}
	
	/**
	 * Loads all subtasks (right down to leaf nodes) and expands all subtask sections
	 * Calls display to re-render the page
	 */
	function showAllSubtasks()
	{
		// *** code in here plx kaithnxbai
	}
	
	/**
	 * Changes whether the updates for an assignment group are displayed
	 * 
	 * @param int $taskId  The ID of the task containing the group
	 * @param int $groupId  The ID of the group whose up updates shown status is to be toggled
	 */
	function toggleUpdates( $taskId, $groupId )
	{
		$group = &$this->_tasks[$taskId]->getGroup( $groupId );
		
		$updatesShown = $group->getUpdatesShown();
		$group->setUpdatesShown( !$updatesShown );
	}
	
	/**
	 * Retrieves the progress of each subtask along with its duration and complete flag.
	 * Sub-group progress is weighted by the number of members who are members of the given group
	 * (eg our group contains A, B, C. Sub-tasks groups are at (A)=20%, (B, C)=50%, value for that task
	 *  is (20+50+50)/3 = 40%  (not 35% as (20+50)/2 would give)
	 * Useful for calculating group-progress
	 *
	 * @param int $tId  The id of the task whose subtasks are to be considered
	 * @param int $gId  The id of the group whose members are to be considered
	 * @return array  2-d array. Values=>(prog1/100, prog2/100, ...), weights=>(dur1, dur2, ...), complete=>(true, false, ...)
	 */
	function getSubtasksGroupProgress( $tId, $gId )
	{
		$db = &JFactory::getDBO();
		
		// find all the groups for child tasks containing members of the given group
		// This will include each group's progress in that task
		$query = ' SELECT g2.task_id, SUM(g2.progress)/COUNT(*) AS prog, MIN(g2.complete) AS comp, t2.micro'
			."\n".' FROM `jos_apoth_plan_group_members` AS gm1'
			."\n".' INNER JOIN `jos_apoth_plan_groups` AS g1'
			."\n".'    ON g1.id = gm1.group_id'
			."\n".' INNER JOIN `jos_apoth_plan_group_members` AS gm2'
			."\n".'    ON gm2.person_id = gm1.person_id'
			."\n".' INNER JOIN `jos_apoth_plan_groups` AS g2'
			."\n".'    ON g2.id = gm2.group_id'
			."\n".' INNER JOIN `jos_apoth_plan_tasks` AS t2'
			."\n".'    ON t2.id = g2.task_id'
			."\n".'   AND t2.parent = g1.task_id'
			."\n".' WHERE gm1.group_id = '.$db->Quote( $gId )
			."\n".'   AND g1.task_id = '.$db->Quote( $tId )
			."\n".' GROUP BY g2.task_id';
		$db->setQuery( $query );
		$r = $db->loadAssocList();
		$prog = array();
		foreach( $r as $row ) {
			$prog[$row['task_id']] = $row['prog'];
			if( $row['micro'] == 0 ) { // micro tasks don't have a "complete" flag
				$r['complete'][$row['task_id']] = (bool)$row['comp'];
			}
		}
		if( !isset($r['complete']) ) {
			$r['complete'] = array();
		}
		
		// Get micro-tasks-which-have-been-ticked list
		$query = ' SELECT task_id'
			."\n".' FROM `jos_apoth_plan_update_microtasks` AS m'
			."\n".' INNER JOIN `jos_apoth_plan_updates` AS u'
			."\n".'    ON m.update_id = u.id'
			."\n".' WHERE u.group_id = '.$db->Quote( $gId );
		$db->setQuery( $query );
		$micros = $db->loadResultArray();
		$micros = array_flip( $micros );
		
		$task = &$this->getTask( $tId );
		$subTasks = $task->getLinkedTasks( array( 'type'=>'children' ) );
		
		foreach( $subTasks as $id ) {
			$t = &$this->getTask( $id );
			if( $t->getMicro() ) {
				$r['values'][$id]  = ( isset($micros[$id]) ? 1 : 0 );
				$r['weights'][$id] = $t->getDuration();
			}
			elseif( isset($prog[$id]) ) {
				$r['values'][$id]  = $prog[$id] / 100;
				$r['weights'][$id] = $t->getDuration();
			}
			unset( $t );
		}
		return $r;
	}
	
	/**
	 * Checks a task's updates/children to see if the over-all progress has changed.
	 * Checks a task's groups' updates to see if their over-all progress has changed.
	 * If either has, then propogate the change to its parent.
	 *
	 * A task's progress is either:
	 * If task has children: the average of its child-tasks' progress
	 * If task has no children: the average of its assignment groups' progress
	 *
	 * An assignment group's progress:
	 * If group has children: Progress of all child groups containing members of this group is averaged
	 *  (amount is weighted per child (within each task) and task duration).
	 * If group has no children: The value must be entered, and is definitive
	 *
	 * A task's complete status is true if all its groups are complete, false otherwise
	 * A group's complete status remains true if it has 100% progress and complete flag,
	 *   and the same can be said of all its members in subtasks, false otherwise
	 */
	function checkTask( $id, $byGroups = true )
	{
//		echo '<h2>Checking '.$id.'</h2>';
		$db = &JFactory::getDBO();
		$changed = false;
		
		$t = &$this->getTask($id);
		$children = $t->getLinkedTasks( array( 'type'=>'children' ), false );
		
		$complete = !empty( $groupsNewKeys ); // task groups completeness indicator
		$micro = $t->getMicro(); // **** support for microtasks has taken a hit and needs to be looked at again 
		
		// Rules are different depending on if we have children or not (see func. comment)
		if( empty($children) ) {
			// Leaf task's progress is average of its groups' progress
			$query = 'SELECT FLOOR(SUM(progress)/COUNT(*)) AS avg'
				."\n".', MIN(complete) AS complete'
				."\n".'FROM `jos_apoth_plan_groups`'
				."\n".'WHERE task_id = '.$db->Quote( $id )
				."\n".'GROUP BY task_id';
			$db->setQuery( $query );
			$r = $db->loadAssoc();
			$tProg = $r['avg'];
			$complete = (bool)$r['complete'];
			
			// Leaf task's groups don't need any update checks as their values are entered* and definitive
			// ( * "entered" includes the jiggery-pokery we do when saving an update for a microtask
			// to work out the group's average then enter it as part of the update for the parent task)
		}
		else {
			// Non-leaf task's progress is weighted average of its subtasks' progress
			$query = 'SELECT FLOOR( SUM(progress*duration)/SUM(duration) ) AS avg'
				."\n".', MIN(complete) AS complete'
				."\n".'FROM jos_apoth_plan_tasks'
				."\n".'WHERE parent = '.$db->Quote( $id )
				."\n".'GROUP BY parent';
			$db->setQuery( $query );
			$r = $db->loadAssoc();
			$tProg = $r['avg'];
			$complete = (bool)$r['complete'];
			
			// Non-leaf task's groups' progress are weighted average of their members' avg progress in subtasks
			$query = 'CREATE TEMPORARY TABLE tmp AS'
				."\n".'SELECT g1.id, FLOOR(SUM(g2.progress*t2.duration)/SUM(t2.duration)) AS prog, MIN(g2.complete) AS comp'
				."\n".'FROM `jos_apoth_plan_group_members` AS gm1'
				."\n".'INNER JOIN `jos_apoth_plan_groups` AS g1'
				."\n".'   ON g1.id = gm1.group_id'
				."\n".'INNER JOIN `jos_apoth_plan_group_members` AS gm2'
				."\n".'   ON gm2.person_id = gm1.person_id'
				."\n".'INNER JOIN `jos_apoth_plan_groups` AS g2'
				."\n".'   ON g2.id = gm2.group_id'
				."\n".'INNER JOIN `jos_apoth_plan_tasks` AS t2'
				."\n".'   ON t2.id = g2.task_id'
				."\n".'  AND t2.parent = g1.task_id'
				."\n".'WHERE g1.task_id = 71'
				."\n".'  AND gm1.role IN ( "assignee", "leader" )'
				."\n".'  AND gm2.role IN ( "assignee", "leader" )'
				."\n".'GROUP BY g1.id;'
				."\n".''
				."\n".'UPDATE jos_apoth_plan_groups AS g'
				."\n".'INNER JOIN tmp AS t'
				."\n".'   ON t.id = g.id'
				."\n".'SET g.progress = t.prog,'
				."\n".'    g.complete = t.comp;';
			$db->setQuery( $query );
			$db->queryBatch();
			if( $db->getAffectedRows() != 0 ) {
				$changed = true;
			}
			$query = 'DROP TABLE IF EXISTS tmp';
			$db->setQuery($query);
			$db->query();
		}
		
		// If the task progress or completeness has changed, update it
		if( $tProg != $t->getProgress() ) {
			$changed = true;
			$t->setProgress( $tProg );
		}
		if( !$complete == $t->getComplete() ) {
			$changed = true;
			$t->setComplete( $complete );
		}
		
		// If anything has changed we need to commit it then check the parent task (recurse all the way up the tree)
		if( $changed ) {
			$t->commit();
			$this->_tasks[$id] = new ApothTask( $id );
			
			// Make the new task have the same sections shown as the old one
			$new = &$this->_tasks[$id];
			$new->setDetailsShown(  $t->getDetailsShown() );
			$new->setSubtasksShown( $t->getSubtasksShown() );
			
			if( $t->groupsLoaded() ) {
				$tGroups = &$t->getGroups();
				$newGroups = &$new->getGroups();
				$newGroupKeys = array_keys($newGroups);
				foreach( $newGroupKeys as $gId ) {
					$show = ( is_object($tGroups[$gId]) ? $tGroups[$gId]->getUpdatesShown() : false );
					$newGroups[$gId]->setUpdatesShown( $show );
				}
			}
			
			$rippleUp = ( ($t->getParent() == $t->getId()) ? false : $t->getParent() );
			unset($t);
			
			// Check further up the tree if we're not at a root node
			if( $rippleUp ) {
				$this->checkTask( $rippleUp, false );
			}
		}
	}
	
	function reloadAll()
	{
		foreach( $this->_tasks as $k=>$v ) {
			$groups = &$this->_tasks[$k]->getGroups();
			foreach( $groups as $gId=>$group ) {
				$groups[$gId] = $group->getUpdatesShown();
			}
			
			$d = $v->getDetailsShown();
			$s = $v->getSubtasksShown();
			$this->_tasks[$k] = new ApothTask( $k );
			$this->_tasks[$k]->setDetailsShown( $d );
			$this->_tasks[$k]->setSubtasksShown( $s );
			
			$newGroups = &$this->_tasks[$k]->getGroups();
			foreach( $groups as $gId=>$shown ) {
				if( isset($newGroups[$gId]) ) {
					$newGroups[$gId]->setUpdatesShown( $shown );
				}
			}
		}
	}
}
?>