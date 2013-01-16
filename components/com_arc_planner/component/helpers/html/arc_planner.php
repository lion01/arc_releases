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
 * Utility class for creating different select lists
 *
 * @static
 * @package 	Joomla.Framework
 * @subpackage	HTML
 * @since		1.5
 */
class JHTMLArc_Planner
{

	function addTasks( $name, $ids, $default = null, $multiple = false )
	{
		$db = &JFactory::getDBO();
		
		foreach( $ids['taskIds'] as $k=>$v ) {
			if( empty($v) ) {
				unset( $ids['taskIds'][$k] );
			}
			else {
				$idsQ['taskIds'][$k] = $db->Quote( $v );
			}
		}
		$taskIdStr = '('.implode(', ', $ids['taskIds']).')';

		foreach( $ids['assIds'] as $k=>$v ) {
			if( empty($v) ) {
				unset( $ids['assIds'][$k] );
			}
			else {
				$idsQ['assIds'][$k] = $db->Quote( $v );
			}
		}
		$assStr = '('.implode(', ', $idsQ['assIds']).')';		
		
		foreach( $ids['adIds'] as $k=>$v ) {
			if( empty($v) ) {
				unset( $ids['adIds'][$k] );
			}
			else {
				$idsQ['adIds'][$k] = $db->Quote( $v );
			}
		}
		$adStr = '('.implode(', ', $idsQ['adIds']).')';
		
		// get existing data
		$query =  'SELECT gm.person_id AS mentee, gm2.person_id AS mentor, gm2.valid_to, pg.task_id, gm.group_id'
			."\n".' FROM #__apoth_plan_group_members AS gm '
			."\n".' INNER JOIN  #__apoth_plan_group_members AS gm2' 
			."\n".'	   ON gm2.group_id = gm.group_id'
			."\n".' INNER JOIN  #__apoth_plan_groups AS pg'
			."\n".'	   ON pg.id = gm.group_id'
			."\n".' INNER JOIN  #__apoth_ppl_people AS p '
			."\n".'	   ON p.id = gm2.person_id'
			."\n".' INNER JOIN  #__apoth_ppl_people AS p2'
			."\n".'    ON p2.id = gm.person_id'
			."\n".' WHERE gm2.`role` =  "admin" '
			."\n".'   AND gm.person_id IN '.$assStr.''
			."\n".'   AND pg.task_id IN '.$taskIdStr.'';

		$db->setQuery($query);
		$db->Query();
		$tasks = $db->loadObjectList();		

		// organise existing data into a searchable structure
		foreach( $tasks as $t ){
			$ex[$t->mentee][$t->task_id][$t->mentor] = array( 'group_id'=>$t->group_id, 'valid'=>is_null( $t->valid_to ) );
		}
		
		$modList = array();
		$newList = array();
		foreach( $ids['adIds'] as $k1=>$mentor ) {
			foreach( $ids['assIds'] as $k2=>$mentee ) {
				foreach( $ids['taskIds'] as $k3=>$task ) {
					// are we wasting our time?
					if( !isset($ex[$mentee][$task][$mentor]) ) {
						// do we need to add to existing?
						if( isset($ex[$mentee][$task]) ) {
							$info = reset( $ex[$mentee][$task] );
							$group = $info['group_id'];
							$modList[] = '('.$group.', '.$idsQ['adIds'][$k1].', "admin", "2010-07-30 00:00:00", NULL)';
						}
						// guess we need to add new then
						else {
							$uId = $idsQ['assIds'][$k2].$idsQ['taskIds'][$k3];
							$newGroups[$uId] = '( '.$idsQ['assIds'][$k2].', '.$idsQ['taskIds'][$k3].', (@id:=@id+1) )';
							$newMentors[] = '( '.$idsQ['adIds'][$k1].', '.$idsQ['assIds'][$k2].', '.$idsQ['taskIds'][$k3].', NULL )';
						}
					}
				}
			}
		}

		$query =  'UPDATE  #__apoth_plan_group_members AS gm '
			."\n".' INNER JOIN  #__apoth_plan_group_members AS gm2' 
			."\n".'	   ON gm2.group_id = gm.group_id'
			."\n".' INNER JOIN  #__apoth_plan_groups AS pg'
			."\n".'	   ON pg.id = gm.group_id'
			."\n".' INNER JOIN  #__apoth_ppl_people AS p '
			."\n".'	   ON p.id = gm2.person_id'
			."\n".' INNER JOIN  #__apoth_ppl_people AS p2'
			."\n".'    ON p2.id = gm.person_id'
			."\n".' SET gm2.valid_to = NULL '
			."\n".' WHERE gm2.`role` =  "admin" '
			."\n".'   AND gm.person_id IN '.$assStr
			."\n".'   AND gm2.person_id IN '.$adStr
			."\n".'   AND pg.task_id IN '.$taskIdStr;

		$db->setQuery($query);
		$db->Query();


		// add any new mentors to groups according to modlist data
		if( !empty($modList) ) {
			$query = 'INSERT INTO #__apoth_plan_group_members'
				."\n".'VALUES'
				."\n".implode( ",\n", $modList );
			$db->setQuery( $query );
			$db->Query();
		}
		
		// create any new groups according to newlist data
		if( !empty($newGroups) ) {
			
			$query = 'CREATE TEMPORARY TABLE tmp_groups (`mentee` VARCHAR(20),`task_id` INT,`group_id` INT);'
			
				."\n".'SET @id = (SELECT MAX(id) FROM `#__apoth_plan_groups`);'
				
				."\n".'INSERT INTO tmp_groups'
				."\n".'VALUES'
				."\n".implode( ",\n", $newGroups ).';'
				
				."\n".'CREATE TEMPORARY TABLE tmp_mentors (`mentor` VARCHAR(20),`mentee` VARCHAR(20),`task_id` INT,`group_id` INT);'
				
				."\n".'INSERT INTO tmp_mentors'
				."\n".'VALUES'
				."\n".implode( ",\n", $newMentors ).';'
				
				."\n".'UPDATE tmp_mentors AS m'
				."\n".'INNER JOIN tmp_groups AS g ON g.task_id = m.task_id AND g.mentee = m.mentee'
				."\n".'SET m.group_id = g.group_id;'
				
				."\n".'INSERT INTO #__apoth_plan_groups'
				."\n".'SELECT group_id AS id, task_id, 0, 0, "2011-07-29 00:00:00"'
				."\n".'FROM tmp_groups;'
				
				."\n".'INSERT INTO #__apoth_plan_group_members'			
				."\n".'SELECT group_id, mentee, "assignee", "2010-07-30 00:00:00", NULL'
				."\n".'FROM tmp_groups;'

				."\n".'INSERT INTO jos_apoth_plan_group_members'			
				."\n".'SELECT group_id, mentor, "admin", "2010-07-30 00:00:00", NULL'
				."\n".'FROM tmp_mentors;';
			$db->setQuery( $query );
			$db->QueryBatch();
		}
		
/*
		$endDate = "'2011-07-29 00:00:00'";
		
		//  ###  clean up given values
		if( !is_array($ids['taskIds'] ) ) { $ids['taskIds'] = array($ids['taskIds']); }
		if( !is_array($ids['assIds']  ) ) { $ids['assIds']  = array($ids['assIds']);  }
		if( !is_array($ids['adIds']  ) ) { $ids['adIds']  = array($ids['adIds']);  }
		
		$idsQ = array( 'taskId'=>array(), 'assIds'=>array(), 'adIds'=>array() );

		$db = &JFactory::getDBO();
		
		foreach( $ids['taskIds'] as $k=>$v ) {
			if( empty($v) ) {
				unset( $ids['taskIds'][$k] );
			}
			else {
				$idsQ['taskIds'][$k] = $db->Quote( $v );
			}			
		}
		$taskIdStr = '('.implode(', ', $idsQ['taskIds']).')';

		foreach( $ids['assIds'] as $k=>$v ) {
			if( empty($v) ) {
				unset( $ids['assIds'][$k] );
			}
			else {
				$idsQ['assIds'][$k] = $db->Quote( $v );
				$pids[] = $v; 
			}
		}
		$assStr = '('.implode(', ', $idsQ['assIds']).')';

		foreach( $ids['adIds'] as $k=>$v ) {
			if( empty($v) ) {
				unset( $ids['adIds'][$k] );
			}
			else {
				$idsQ['adIds'][$k] = $db->Quote( $v );
				$pids[] = $v;
			}
		}
		$adStr = '('.implode(', ', $idsQ['adIds']).')';

		if( empty($ids['taskIds']) || empty($ids['assIds']) || empty($ids['adIds']) ) {
			echo 'FAIL';
			return false;
		}
		$newTriples = array();
		
		foreach( $ids['assIds'] as $v1 ) {
			foreach( $ids['adIds'] as $v2 ) {
				foreach( $ids['taskIds'] as $v3 ) {
					$obj = new stdClass();
					$obj->mentee = $v1;
					$obj->mentor = $v2;
					$obj->task_id = $v3;
					$givenTriples[] = $obj;
				}
			}
		}
		
		//  ###  Generate 2 task id lists (new / existing)
		
		$newTasks = array();
		$exTasks = array();
		$valTasks = array();
		$exTriples = array();
		
		$query =  'SELECT gm.person_id AS mentee, gm2.person_id AS mentor, gm2.valid_to, pg.task_id FROM #__apoth_plan_group_members AS gm '
			."\n".' INNER JOIN  #__apoth_plan_group_members AS gm2' 
			."\n".'	   ON gm2.group_id = gm.group_id'
			."\n".' INNER JOIN  #__apoth_plan_groups AS pg'
			."\n".'	   ON pg.id = gm.group_id'
			."\n".' INNER JOIN  #__apoth_ppl_people AS p '
			."\n".'	   ON p.id = gm2.person_id'
			."\n".' INNER JOIN  #__apoth_ppl_people AS p2'
			."\n".'    ON p2.id = gm.person_id'
			."\n".' WHERE gm2.`role` =  "admin" '
			."\n".'   AND gm.person_id IN '.$assStr.''
			."\n".'   AND pg.task_id IN '.$taskIdStr.'';

		$db->setQuery($query);
		$db->Query();
		// guarantee that $exttasks and $newtasks are populated here
		$tasks = $db->loadObjectList();
		foreach( $tasks as $k=>$v ) {
			if( $v->valid_to !== NULL ) {
				$valTasks[] = $v->task_id;
			}
			unset($v->valid_to);
			$exTriples[] = $v;
		}

		if( !empty($exTriples) ) {
			
			foreach( $givenTriples as $v ) {
				$gt[] = serialize( $v );
			}

			foreach( $exTriples as $v ) {
				$et[] = serialize( $v );
			}
		
			$nt = array_diff( $gt, $et );
		
			foreach( $nt as $v ) {
				$newTriples[] = unserialize( $v );
			}
		}
		else {
			$newTriples = $givenTriples;
		}


		//  ###  Add new tasks
		if( !empty($newTriples) ) {
			foreach( $newTriples as $k=>$v ) {
				$tripStrs[] = '('.$db->Quote($v->mentee).', '.$db->Quote($v->mentor).', '.$v->task_id.')';
			}
			// later you can :
			$tripStrs = implode( ', ', $tripStrs );

			$query =  'CREATE TEMPORARY TABLE tmp_assignments ( `mentee` VARCHAR(20), `mentor` VARCHAR(20), `task` INT NOT NULL );'
				."\n".'INSERT INTO tmp_assignments VALUES '.$tripStrs.';'

				."\n".'CREATE TEMPORARY TABLE tmp_groups (`mentee` VARCHAR(20),`mentor` VARCHAR(20),`task_id` INT,`group_id` INT PRIMARY KEY AUTO_INCREMENT);'
				."\n".'SET @id = (SELECT MAX(group_id) FROM `#__apoth_plan_group_members`);'
				
				."\n".'INSERT INTO tmp_groups SELECT t.*, (@id:=@id+1) FROM tmp_assignments AS t;'
							
				."\n".'INSERT INTO #__apoth_plan_groups'			
				."\n".'SELECT group_id AS id, task_id, 0, 0,'. $endDate
				."\n".'FROM tmp_groups;'

				."\n".'INSERT INTO #__apoth_plan_group_members'			
				."\n".'SELECT group_id, mentee, "assignee", NOW(), NULL'
				."\n".'FROM tmp_groups;'

				."\n".'INSERT INTO jos_apoth_plan_group_members'			
				."\n".'SELECT group_id, mentor, "admin", NOW(), NULL'
				."\n".'FROM tmp_groups;';

				$db->setQuery($query);
				$db->queryBatch();
		}


		//  ###  Re-add existing tasks
		if( !empty($valTasks) ) {

			$valIdStr = '('.implode(', ', $valTasks).')';

			$query =  'UPDATE  #__apoth_plan_group_members AS gm '
				."\n".' INNER JOIN  #__apoth_plan_group_members AS gm2' 
				."\n".'	   ON gm2.group_id = gm.group_id'
				."\n".' INNER JOIN  #__apoth_plan_groups AS pg'
				."\n".'	   ON pg.id = gm.group_id'
				."\n".' INNER JOIN  #__apoth_ppl_people AS p '
				."\n".'	   ON p.id = gm2.person_id'
				."\n".' INNER JOIN  #__apoth_ppl_people AS p2'
				."\n".'    ON p2.id = gm.person_id'
				."\n".' SET gm2.valid_to = NULL '
				."\n".' WHERE gm2.`role` =  "admin" '
				."\n".'   AND gm.person_id IN '.$assStr.''
				."\n".'   AND gm2.person_id IN '.$adStr.''
				."\n".'   AND pg.task_id IN '.$valIdStr.'';

			$db->setQuery($query);
			$db->Query();
		}*/

		$retVal = JHTML::_( 'arc_planner.assignedTasks', $name, $ids, null, true );

		return $retVal;
	}

	function removeTasks( $name, $ids, $default = null, $multiple = false )
	{
		$db = &JFactory::getDBO();
		
		foreach( $ids['taskIds'] as $k=>$v ) {
			if( empty($v) ) {
				unset( $ids['taskIds'][$k] );
			}
			else {
				$idsQ['taskIds'][$k] = $db->Quote( $v );
			}
		}
		$taskIdStr = '('.implode(', ', $ids['taskIds']).')';

		foreach( $ids['assIds'] as $k=>$v ) {
			if( empty($v) ) {
				unset( $ids['assIds'][$k] );
			}
			else {
				$idsQ['assIds'][$k] = $db->Quote( $v );
				$pids[] = $v; 
			}
		}
		$assStr = '('.implode(', ', $idsQ['assIds']).')';		
		
		foreach( $ids['adIds'] as $k=>$v ) {
			if( empty($v) ) {
				unset( $ids['adIds'][$k] );
			}
			else {
				$idsQ['adIds'][$k] = $db->Quote( $v );
				$pids[] = $v;
			}
		}
		$adStr = '('.implode(', ', $idsQ['adIds']).')';

		if( empty($ids) ) {
			$tasks = array();
		}
		
		else {
			
			$query =  'UPDATE  #__apoth_plan_group_members AS gm '
			."\n".' INNER JOIN  #__apoth_plan_group_members AS gm2' 
			."\n".'	   ON gm2.group_id = gm.group_id'
			."\n".' INNER JOIN  #__apoth_plan_groups AS pg'
			."\n".'	   ON pg.id = gm.group_id'
			."\n".' INNER JOIN  #__apoth_ppl_people AS p '
			."\n".'	   ON p.id = gm2.person_id'
			."\n".' INNER JOIN  #__apoth_ppl_people AS p2'
			."\n".'    ON p2.id = gm.person_id'
			."\n".' SET gm2.valid_to = NOW() '
			."\n".' WHERE gm2.`role` =  "admin" '
			."\n".'   AND gm.person_id IN '.$assStr.''
			."\n".'   AND gm2.person_id IN '.$adStr.''
			."\n".'   AND pg.task_id IN '.$taskIdStr.'';

			$db->setQuery($query);
			$db->Query();
		}

		$retVal = JHTML::_( 'arc_planner.assignedTasks', $name, $ids, null, true );

		return $retVal;
	}

	function assignPeople( $name, $ids, $default = null, $multiple = false, $list = null )
	{	
		$db = &JFactory::getDBO();
		
		foreach( $ids['taskIds'] as $k=>$v ) {
			if( empty($v) ) {
				unset( $ids['taskIds'][$k] );
			}
			else {
				$idsQ['taskIds'][$k] = $db->Quote( $v );
			}
		}
		$taskIdStr = '('.implode(', ', $ids['taskIds']).')';

		foreach( $ids['assIds'] as $k=>$v ) {
			if( empty($v) ) {
				unset( $ids['assIds'][$k] );
			}
			else {
				$idsQ['assIds'][$k] = $db->Quote( $v );
			}
		}
		$assStr = '('.implode(', ', $idsQ['assIds']).')';		
		
		foreach( $ids['adIds'] as $k=>$v ) {
			if( empty($v) ) {
				unset( $ids['adIds'][$k] );
			}
			else {
				$idsQ['adIds'][$k] = $db->Quote( $v );
			}
		}
		$adStr = '('.implode(', ', $idsQ['adIds']).')';
		
		// get existing data
		$query =  'SELECT gm.person_id AS mentee, gm2.person_id AS mentor, gm2.valid_to, pg.task_id, gm.group_id'
			."\n".' FROM #__apoth_plan_group_members AS gm '
			."\n".' INNER JOIN  #__apoth_plan_group_members AS gm2' 
			."\n".'	   ON gm2.group_id = gm.group_id'
			."\n".' INNER JOIN  #__apoth_plan_groups AS pg'
			."\n".'	   ON pg.id = gm.group_id'
			."\n".' INNER JOIN  #__apoth_ppl_people AS p '
			."\n".'	   ON p.id = gm2.person_id'
			."\n".' INNER JOIN  #__apoth_ppl_people AS p2'
			."\n".'    ON p2.id = gm.person_id'
			."\n".' WHERE gm2.`role` =  "admin" '
			."\n".'   AND gm.person_id IN '.$assStr.''
			."\n".'   AND pg.task_id IN '.$taskIdStr.'';

		$db->setQuery($query);
		$db->Query();
		$tasks = $db->loadObjectList();		

		// organise existing data into a searchable structure
		foreach( $tasks as $t ){
			$ex[$t->mentee][$t->task_id][$t->mentor] = array( 'group_id'=>$t->group_id, 'valid'=>is_null( $t->valid_to ) );
		}
		
		$modList = array();
		$newList = array();
		foreach( $ids['adIds'] as $k1=>$mentor ) {
			foreach( $ids['assIds'] as $k2=>$mentee ) {
				foreach( $ids['taskIds'] as $k3=>$task ) {
					// are we wasting our time?
					if( !isset($ex[$mentee][$task][$mentor]) ) {
						// do we need to add to existing?
						if( isset($ex[$mentee][$task]) ) {
							$info = reset( $ex[$mentee][$task] );
							$group = $info['group_id'];
							$modList[] = '('.$group.', '.$idsQ['adIds'][$k1].', "admin", "2010-07-30 00:00:00", NULL)';
						}
						// guess we need to add new then
						else {
							$uId = $idsQ['assIds'][$k2].$idsQ['taskIds'][$k3];
							$newGroups[$uId] = '( '.$idsQ['assIds'][$k2].', '.$idsQ['taskIds'][$k3].', (@id:=@id+1) )';
							$newMentors[] = '( '.$idsQ['adIds'][$k1].', '.$idsQ['assIds'][$k2].', '.$idsQ['taskIds'][$k3].', NULL )';
						}
					}
				}
			}
		}

		$query =  'UPDATE  #__apoth_plan_group_members AS gm '
			."\n".' INNER JOIN  #__apoth_plan_group_members AS gm2' 
			."\n".'	   ON gm2.group_id = gm.group_id'
			."\n".' INNER JOIN  #__apoth_plan_groups AS pg'
			."\n".'	   ON pg.id = gm.group_id'
			."\n".' INNER JOIN  #__apoth_ppl_people AS p '
			."\n".'	   ON p.id = gm2.person_id'
			."\n".' INNER JOIN  #__apoth_ppl_people AS p2'
			."\n".'    ON p2.id = gm.person_id'
			."\n".' SET gm2.valid_to = NULL '
			."\n".' WHERE gm2.`role` =  "admin" '
			."\n".'   AND gm.person_id IN '.$assStr
			."\n".'   AND gm2.person_id IN '.$adStr
			."\n".'   AND pg.task_id IN '.$taskIdStr;

		$db->setQuery($query);
		$db->Query();


		// add any new mentors to groups according to modlist data
		if( !empty($modList) ) {
			$query = 'INSERT INTO #__apoth_plan_group_members'
				."\n".'VALUES'
				."\n".implode( ",\n", $modList );
			$db->setQuery( $query );
			$db->Query();
		}
		
		// create any new groups according to newlist data
		if( !empty($newGroups) ) {
			
			$query = 'CREATE TEMPORARY TABLE tmp_groups (`mentee` VARCHAR(20),`task_id` INT,`group_id` INT);'
			
				."\n".'SET @id = (SELECT MAX(id) FROM `#__apoth_plan_groups`);'
				
				."\n".'INSERT INTO tmp_groups'
				."\n".'VALUES'
				."\n".implode( ",\n", $newGroups ).';'
				
				."\n".'CREATE TEMPORARY TABLE tmp_mentors (`mentor` VARCHAR(20),`mentee` VARCHAR(20),`task_id` INT,`group_id` INT);'
				
				."\n".'INSERT INTO tmp_mentors'
				."\n".'VALUES'
				."\n".implode( ",\n", $newMentors ).';'
				
				."\n".'UPDATE tmp_mentors AS m'
				."\n".'INNER JOIN tmp_groups AS g ON g.task_id = m.task_id AND g.mentee = m.mentee'
				."\n".'SET m.group_id = g.group_id;'
				
				."\n".'INSERT INTO #__apoth_plan_groups'
				."\n".'SELECT group_id AS id, task_id, 0, 0, "2011-07-29 00:00:00"'
				."\n".'FROM tmp_groups;'
				
				."\n".'INSERT INTO #__apoth_plan_group_members'			
				."\n".'SELECT group_id, mentee, "assignee", "2010-07-30 00:00:00", NULL'
				."\n".'FROM tmp_groups;'

				."\n".'INSERT INTO jos_apoth_plan_group_members'			
				."\n".'SELECT group_id, mentor, "admin", "2010-07-30 00:00:00", NULL'
				."\n".'FROM tmp_mentors;';
			$db->setQuery( $query );
			$db->QueryBatch();
		}
		
		// work out return value
		if ($list == 'pm') {
			$retVal = JHTML::_( 'arc_people.plannerPeople', 'assignedMentees', 'assignedMenteesStaff', $ids['adIds'][0], null, true );
		}
		else {
			$retVal = JHTML::_( 'arc_people.plannerPeople', 'assignedMentees', 'assignedMentees', $ids['adIds'][0], null, true );
		}
		return $retVal;
	}

	function removeMentees( $name, $ids, $default = null, $multiple = false )
	{
		$db = &JFactory::getDBO();
		
		foreach( $ids['assIds'] as $k=>$v ) {
			if( empty($v) ) {
				unset( $ids['assIds'][$k] );
			}
			else {
				$idsQ['assIds'][$k] = $db->Quote( $v );
			}
		}
		$assStr = implode(', ', $idsQ['assIds']);		
		
		foreach( $ids['adIds'] as $k=>$v ) {
			if( empty($v) ) {
				unset( $ids['adIds'][$k] );
			}
			else {
				$idsQ['adIds'][$k] = $db->Quote( $v );
			}
		}
		$adStr = implode(', ', $idsQ['adIds']);
		

		if( empty($ids) ) {
			$tasks = array();
		}
		
		else {
			
			$query =   'UPDATE  #__apoth_plan_group_members AS gm '
			."\n".' INNER JOIN  #__apoth_plan_group_members AS gm2' 
			."\n".'	   ON gm2.group_id = gm.group_id'
			."\n".' INNER JOIN  #__apoth_plan_groups AS pg'
			."\n".'	   ON pg.id = gm.group_id'
			."\n".' INNER JOIN  #__apoth_ppl_people AS p '
			."\n".'	   ON p.id = gm2.person_id'
			."\n".' INNER JOIN  #__apoth_ppl_people AS p2'
			."\n".'    ON p2.id = gm.person_id'
			."\n".' SET gm2.valid_to = "2011-02-02 11:02:02" '
			."\n".' WHERE gm2.`role` =  "admin" '
			."\n".'   AND gm.person_id IN ('.$assStr.')'
			."\n".'   AND gm2.person_id IN ('.$adStr.')';

			$db->setQuery($query);
			$db->Query();
		}

		$retVal = JHTML::_( 'arc_people.plannerPeople', 'assignedMentees', 'assignedMentees', $ids['adIds'][0], null, true );

		return $retVal;
	}
	
	function removeMentors( $name, $ids, $default = null, $multiple = false )
	{
		$db = &JFactory::getDBO();
		
		foreach( $ids['assIds'] as $k=>$v ) {
			if( empty($v) ) {
				unset( $ids['assIds'][$k] );
			}
			else {
				$idsQ['assIds'][$k] = $db->Quote( $v );
			}
		}
		$assStr = implode(', ', $idsQ['assIds']);		
		
		foreach( $ids['adIds'] as $k=>$v ) {
			if( empty($v) ) {
				unset( $ids['adIds'][$k] );
			}
			else {
				$idsQ['adIds'][$k] = $db->Quote( $v );
			}
		}
		$adStr = implode(', ', $idsQ['adIds']);
		
		if( empty($ids) ) {
			$tasks = array();
		}
		
/*
		foreach( $pids as $k=>$v ) {
			if( empty($v) ) {
				unset( $pids[$k] );
			}
			else {
				$pidsQ[$k] = $db->Quote( $v );
			}
		}

		if( empty($pids) ) {
			$tasks = array();
		}*/

		else {
			
			$query =   'UPDATE  #__apoth_plan_group_members AS gm '
			."\n".' INNER JOIN  #__apoth_plan_group_members AS gm2' 
			."\n".'	   ON gm2.group_id = gm.group_id'
			."\n".' INNER JOIN  #__apoth_plan_groups AS pg'
			."\n".'	   ON pg.id = gm.group_id'
			."\n".' INNER JOIN  #__apoth_ppl_people AS p '
			."\n".'	   ON p.id = gm2.person_id'
			."\n".' INNER JOIN  #__apoth_ppl_people AS p2'
			."\n".'    ON p2.id = gm.person_id'
			."\n".' SET gm2.valid_to = NOW()'
			."\n".' WHERE gm2.`role` =  "admin" '
			."\n".'   AND gm.person_id IN ('.$assStr.')'
			."\n".'   AND gm2.person_id IN ('.$adStr.')';

			$db->setQuery($query);
			$db->Query();
		}
		
		$retVal = JHTML::_( 'arc_people.plannerPeople', 'assignedMentors', 'assignedMentors', $ids['assIds'][0], null, true );

		return $retVal;
	}

	function assignedTasks( $name, $ids, $default = null, $multiple = false )
	{
		if( !isset($ids['taskIds']) ) { $ids['taskIds'] = array(); }
		if( !isset($ids['assIds'] ) ) { $ids['assIds']  = array(); }
		if( !isset($ids['adIds']  ) ) { $ids['adIds']   = array(); }
		
		if( !is_array($ids['taskIds'] ) ) { $ids['taskIds'] = array($ids['taskIds']); }
		if( !is_array($ids['assIds']  ) ) { $ids['assIds']  = array($ids['assIds']);  }
		if( !is_array($ids['adIds']   ) ) { $ids['adIds']   = array($ids['adIds']);   }
		
		$idsQ = array( 'taskId'=>array(), 'assIds'=>array(), 'adIds'=>array() );

		$db = &JFactory::getDBO();

		foreach( $ids['assIds'] as $k=>$v ) {
			if( empty($v) ) {
				unset( $ids['assIds'][$k] );
			}
			else {
				$idsQ['assIds'][$k] = $db->Quote( $v );
				$pids[] = $v; 
			}
		}
		$assStr = '('.implode(', ', $idsQ['assIds']).')';

		foreach( $ids['adIds'] as $k=>$v ) {
			if( empty($v) ) {
				unset( $ids['adIds'][$k] );
			}
			else {
				$idsQ['adIds'][$k] = $db->Quote( $v );
				$pids[] = $v;
			}
		}
		$adStr = '('.implode(', ', $idsQ['adIds']).')';


		if( empty($pids) ) {
			$tasks = array();
		}
		else {

			$query = 'SELECT DISTINCT t.id, t.title, t.private, 0 AS depth'
				."\n".' FROM #__apoth_plan_tasks AS t'
				."\n".' INNER JOIN #__apoth_plan_groups AS pg'
				."\n".'    ON pg.task_id = t.id'
				."\n".' INNER JOIN #__apoth_plan_group_members AS gm1'
				."\n".'    ON gm1.group_id = pg.id'
				."\n".'   AND gm1.valid_to IS NULL'
				."\n".' INNER JOIN #__apoth_plan_group_members AS gm2'
				."\n".'    ON gm2.group_id = pg.id'
				."\n".'   AND gm2.valid_to IS NULL'
				."\n".' WHERE t.id = t.parent'
				."\n".'   AND t.deleted = 0'
				."\n".'   AND gm1.person_id IN '.$assStr.''
				."\n".'   AND gm2.person_id IN '.$adStr.''
				;
			$db->setQuery($query);
			$tasks = $db->loadObjectList();
		}
		
		if( !is_array( $tasks ) ) { $tasks = array(); }

		$loadlevel = 4;
		$level = 1;
		while( $level < $loadlevel ) {
			$queue = $tasks;
			$tasks = array();
			
			foreach( $queue as $k=>$v ) {
				$tasks[] = $v;
				if( $v->depth == ($level - 1) ) {
					// load its children
					$query = 'SELECT DISTINCT gm2.person_id, t.id, t.title, t.private, '.$level.' AS depth'
						."\n".' FROM #__apoth_plan_tasks AS t'
						."\n".' INNER JOIN #__apoth_plan_groups AS pg'
						."\n".'    ON pg.task_id = t.id'
						."\n".' INNER JOIN #__apoth_plan_group_members AS gm1'
						."\n".'    ON gm1.group_id = pg.id'
						."\n".'   AND gm1.valid_to IS NULL'
						."\n".' INNER JOIN #__apoth_plan_group_members AS gm2'
						."\n".'    ON gm2.group_id = pg.id'
						."\n".'   AND gm2.valid_to IS NULL'
						."\n".' WHERE t.id != t.parent'
						."\n".'   AND t.parent = '.$v->id
						."\n".'   AND gm1.person_id IN '.$assStr.''
						."\n".'   AND gm2.person_id IN '.$adStr.''
						;
					$db->setQuery($query);
					$children = $db->loadObjectList();
					foreach( $children as $kc=>$vc ) {
						$vc->title = str_repeat('- ', $level).$vc->title;
						$tasks[] = $vc;
					}

				}
			}
			$level++;
		}
		
		if ($multiple) {
			$retVal =  JHTML::_( 'select.genericList', $tasks, $name.'[]', 'multiple="multiple" class="multi_medium"', 'id', 'title' );
		}
		else {
			$retVal =  JHTML::_( 'select.genericList', $tasks, $name, '', 'id', 'title' );
		}
		return $retVal;		
		
	}
	
	function tasks( $name, $ids, $default = null, $multiple = false )
	{
		$db = &JFactory::getDBO();
		
		if( !isset($ids['assIds']) ) { $ids['assIds'] = array(); }
		if( !is_array($ids['assIds']) ) { $ids['assIds']  = array($ids['assIds']); }
		foreach( $ids['assIds'] as $k=>$v ) {
			if( empty($v) ) {
				unset( $ids['assIds'][$k] );
			}
			else {
				$idsQ['assIds'][$k] = $db->Quote( $v );
				$pids[] = $v; 
			}
		}

		$roots = ApotheosisLibDb::getRootItems( '#__apoth_plan_tasks' );

		$query = 'SELECT id, title, private, 0 AS depth'
			."\n".' FROM #__apoth_plan_tasks AS t'
			."\n".' WHERE t.id IN ('.implode(", ", $roots).')'
			."\n".'   AND t.private = 0'
			."\n".'   AND t.deleted = 0';
		$db->setQuery($query);
		$tasks = $db->loadObjectList();
				
		if( !is_array( $tasks ) ) { $tasks = array(); }

		$loadlevel = 4;
		$level = 1;
		while( $level < $loadlevel ) {
			$queue = $tasks;
			$tasks = array();

			foreach( $queue as $k=>$v ) {
				$tasks[] = $v;
				if( $v->depth == ($level - 1) ) {
					// load its children
					if( !empty( $idsQ['assIds']) ) {
						$join =
						 "\n".' INNER JOIN `jos_apoth_tt_group_members` AS tgm'
						."\n".'    ON tgm.person_id = gm1.person_id'
						."\n".'   AND tgm.valid_to > NOW()'
						."\n".' INNER JOIN `jos_apoth_cm_courses` AS c'
						."\n".'    ON c.id = tgm.group_id';
						
						$where = 'OR (t.private = 1 AND gm1.person_id IN ('.implode(', ', $idsQ['assIds']).') )';
					}
					else {
						$join = '';
						$where = '';
					}
					
					$query = 'SELECT DISTINCT t.id, t.title, t.private, '.$level.' AS depth'
						."\n".' FROM #__apoth_plan_tasks AS t'
						."\n".' INNER JOIN jos_apoth_plan_groups AS pg'
						."\n".'    ON pg.task_id = t.id'
						."\n".' INNER JOIN jos_apoth_plan_group_members AS gm1'
						."\n".'    ON gm1.group_id = pg.id'
						."\n".'   AND gm1.valid_to IS NULL'
						.$join
			
						."\n".' WHERE t.parent = '.$v->id
						."\n".'   AND t.parent != t.id'
						.(empty($idsQ['assIds']) ? '' : "\n".'AND gm1.person_id IN ('.implode(', ', $idsQ['assIds']).')' )
						."\n".'   AND (t.private = 0 '.$where.')';
						
					$db->setQuery($query);
					$children = $db->loadObjectList();

					foreach( $children as $kc=>$vc ) {
						$vc->title = str_repeat('- ', $level).$vc->title;
						$tasks[] = $vc;
					}
				}
			}
			$level++;
		}
		if ($multiple) {
			$retVal =  JHTML::_( 'select.genericList', $tasks, $name.'[]', 'multiple="multiple" class="multi_medium"', 'id', 'title' );
		}
		else {
			$retVal =  JHTML::_( 'select.genericList', $tasks, $name, '', 'id', 'title' );
		}
		return $retVal;
	}
	
	/**
	 * Generate HTML to display an admin page profile template category management section
	 * *** placeholder for now
	 * 
	 * @param array $catInfo  Info about the category
	 * @param array $catData  The data for display
	 * @return string $html  The HTML
	 */
	function profilePanel( $catInfo, $catData )
	{
		return '';
	}
}