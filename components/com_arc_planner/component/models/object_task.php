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
 * Planner Task Object
 */
class ApothTask extends JObject
{
	/**
	 * All the data for this task (equates to a row in the db)
	 * @access protected
	 * @var array
	 */
	var $_data = array();
	
	/**
	 * Min/max due dates for this task and the user viewing it
	 * @access protected
	 * @var array
	 */
	var $_dueDates = array();
	
	/**
	 * Whether or not this tasks details should be shown
	 * @access protected
	 * @var boolean  Should the details of this task be displayed
	 */
	var $_showDetails;
	
	/**
	 * Whether or not this tasks subtasks should be shown
	 * @access protected
	 * @var boolean  Should the sub-tasks of this task be displayed
	 */
	var $_showSubtasks;
	
	/**
	 * IDs of tasks linked with this task (either by dependancy, loose relationship, or parentage)
	 * 2d array indexed by relationship=>index=>data(just the ID)
	 * @access protected
	 * @var array
	 */
	var $_linkedTasks = array();
	
	/**
	 * All the groups associated with this task
	 * 1d array of group objects indexed on group ID
	 * @access protected
	 * @var array
	 */
	var $_groups = array();
	
	/**
	 * All the constraints to apply when retrieving groups associated with this task
	 * @access protected
	 * @var array
	 */
	var $_groupRequirements = array();
	
	/**
	 * All the categories for which we want to allow updates in this task
	 * and their accompanying labels (category name=>label)
	 * @var array
	 */
	var $_categories = array();
	
	/**
	 * All the form elements for which we want to specify text specific to this task
	 * and their accompanying labels (category name=>label)
	 * @var array
	 */
	var $_labels = array(
		  'task_new'=>'New task'
		, 'task_existing'=>'Existing task'
		, 'task_title'=>'Task'
		, 'task_text_1'=>'Description'
		, 'task_text_2'=>'Reason'
		, 'task_add'=>'Add task'
		, 'task_demote'=>'Save and demote self'
		, 'update_intro'          =>'You can save your answers at any time, even if they are not complete yet.'
		, 'update_evidence_toggle'=>'Click to toggle evidence'
		, 'update_evidence_intro' =>'Add any evidence urls or files:'
		, 'update_evidence_url'   =>'Evidence urls:'
		, 'update_evidence_file'  =>'Evidence files:'
		, 'update_done_intro'     =>'Check this box when you have completed this task.'
		, 'update_done'           =>'Complete?'
		, 'update_save'           =>'Save All'
		);
	
	/**
	 * Constructs a task object.
	 * The result is either empty or if an ID is given it is
	 * populated by the $data array or by retrieving data from the db
	 * @param int $id  optional If not provided an empty task object is created.
	 * @param array $data  optional If given along with an id this is used as the data for the object (omits linked tasks)
	 * @return object  The newly created task object
	 */
	function __construct( $id = false, $data = array(), $groupRequirements = array() )
	{
		parent::__construct();
		
		$db = &JFactory::getDBO();
		// if we have an id but no supplied data
		if( $id !== false && empty($data) ) {
			$mainQuery = 'SELECT *'
				."\n".' FROM '.$db->nameQuote( '#__apoth_plan_tasks' ).' AS '.$db->nameQuote( 't' )
				."\n".'~LIMITINGJOIN~'
				."\n".' WHERE '.$db->nameQuote( 't' ).'.'.$db->nameQuote( 'id' ).' = '.$db->Quote( $id );
			$db->setQuery( ApotheosisLibAcl::limitQuery($mainQuery, 'planner.tasks') );
			$data = $db->loadAssoc();
		}
			
		// if we have supplied the data
		if( !empty($data) ) {
			// store the data in the object
			$this->_data['id'] =   (int)$data['id'];
			$this->setParent(      (int)$data['parent'] );
			$this->setTitle(            $data['title'] );
			$this->setColor(            $data['color'] );
			$this->setMicro(      (bool)$data['micro'] );
			$this->setText1(            $data['text_1'] );
			$this->setText2(            $data['text_2'] );
			$this->setDuration(    (int)$data['duration'] );
			$this->setEvidenceNum(      $data['evidence_num'] );
			$this->setComplete(   (bool)$data['complete'] );
			$this->setProgress(    (int)$data['progress'] );
			$this->setOrder(       (int)$data['order'] );
			$this->setTemplate(         $data['template'] );
			
			// run query to find task due dates with current users privelages
			// *** This is slow. Could probably be re-written if we put due dates on tasks
			// ... that way we'd be getting the task due from there, and the person due from a much smaller list (with WHERE person_id = $user->id)
			$user = ApotheosisLib::getUser();
			$db = &JFactory::getDBO();
			$dueQuery = 'SELECT'
				."\n".'  MIN(g.'.      $db->nameQuote( 'due' ).') AS task_min'
				."\n".', MAX(g.'.      $db->nameQuote( 'due' ).') AS task_max'
				."\n".', MIN( IF( (m.'.$db->nameQuote( 'person_id' ).' = '.$db->Quote( $user->person_id ).') , g.'.$db->nameQuote( 'due' ).' , NULL ) ) AS person_min'
				."\n".', MAX( IF( (m.'.$db->nameQuote( 'person_id' ).' = '.$db->Quote( $user->person_id ).') , g.'.$db->nameQuote( 'due' ).' , NULL ) ) AS person_max'
				."\n".' FROM '.        $db->nameQuote( 'jos_apoth_plan_tasks' ).' AS t'
				."\n".' INNER JOIN '.  $db->nameQuote( 'jos_apoth_plan_tasks_ancestry' ).' AS ta'
				."\n".'    ON ta.'.    $db->nameQuote( 'ancestor' ).' = t.'.$db->nameQuote( 'id' )
				."\n".' INNER JOIN '.  $db->nameQuote( 'jos_apoth_plan_groups' ).' AS g'
				."\n".'    ON g.'.     $db->nameQuote( 'task_id' ).' = ta.'.$db->nameQuote( 'id' )
				."\n".' LEFT JOIN '.   $db->nameQuote( 'jos_apoth_plan_group_members' ).' AS m'
				."\n".'   ON m.'.      $db->nameQuote( 'group_id' ).' = g.'.$db->nameQuote( 'id' )
				."\n".' WHERE t.'.     $db->nameQuote( 'id' ).' = '.$db->Quote( $id )
				."\n".' GROUP BY t.'.  $db->nameQuote( 'id' );
			$db->setQuery( $dueQuery );
			$this->_dueDates = $db->loadAssoc();

			// add in the update categories
			$data2 = array();
			
			$t = str_replace(array('.', ' '), '', microtime());
			$tName1 = $db->nameQuote( 'tmp_rank_'.$t );
			$tName2 = $db->nameQuote( 'tmp_task_cat_'.$t );
			$tName3 = $db->nameQuote( 'tmp_task_lab_'.$t );
			// **** The creation of the first temporary table should be moved to the db library
			// **** there it could be used with getAncestors (and a variation with getDescendants)
			// **** also could be made semi-temporary 
			$ucQuery = 'CREATE TEMPORARY TABLE '.$tName1.' AS'
				."\n".'SELECT a2.id, COUNT( a2.ancestor) AS `rank`'
				."\n".' FROM jos_apoth_plan_tasks_ancestry AS a'
				."\n".'INNER JOIN jos_apoth_plan_tasks_ancestry AS a2'
				."\n".'   ON a2.id = a.ancestor'
				."\n".'WHERE a.id = '.$db->Quote( $this->_data['id'] )
				."\n".'GROUP BY a2.id'
				."\n".'ORDER BY rank DESC;'
				."\n".''
				."\n".'CREATE TABLE '.$tName2.' AS'
				."\n".'SELECT c.task_id'
				."\n".'FROM jos_apoth_plan_update_categories AS c'
				."\n".'INNER JOIN '.$tName1.' AS r'
				."\n".'   ON r.id = c.task_id'
				."\n".'ORDER BY rank DESC'
				."\n".'LIMIT 1;'
				."\n".''
				."\n".'CREATE TABLE '.$tName3.' AS'
				."\n".'SELECT l.task_id'
				."\n".'FROM jos_apoth_plan_tasks_labels AS l'
				."\n".'INNER JOIN '.$tName1.' AS r'
				."\n".'   ON r.id = l.task_id'
				."\n".'ORDER BY rank DESC'
				."\n".'LIMIT 1;';
			$db->setQuery( ApotheosisLibAcl::limitQuery($ucQuery, 'planner.tasks', 'c', 'task_id') );
			$db->QueryBatch();

			$ucQuery = 'SELECT c.*'
				."\n".'FROM jos_apoth_plan_update_categories AS c'
				."\n".'INNER JOIN '.$tName2.' AS t'
				."\n".'   ON t.task_id = c.task_id'
				."\n".'ORDER BY c.'.$db->nameQuote( 'order');
			$db->setQuery( $ucQuery );
			$data2 = $db->loadAssocList();
			
			$ucQuery = 'SELECT l.*'
				."\n".'FROM jos_apoth_plan_tasks_labels AS l'
				."\n".'INNER JOIN '.$tName3.' AS t'
				."\n".'   ON t.task_id = l.task_id;';
			$db->setQuery( $ucQuery );
			$data3 = $db->loadAssocList();
			
			$ucQuery = 'DROP TABLE '.$tName1.';'
				."\n".'DROP TABLE '.$tName2.';'
				."\n".'DROP TABLE '.$tName3.';';
			$db->setQuery( $ucQuery );
			$db->QueryBatch();
			
			foreach( $data2 as $cat ) {
				$this->_categories[$cat['category']] = $cat;
			}
			
			foreach( $data3 as $label ) {
				$this->_labels[$label['item']] = $label['label'];
			}
		}
		// if the limit query has blocked data assimilation or none supplied then return now
		else {
			return;
		}
		
		$this->_groupRequirements = $groupRequirements;
		$this->_groupRequirements['taskId'] = $this->getId();
		$this->setDetailsShown( false );
		$this->setSubtasksShown( true );
	}
	
	/**
	 * Retrieves the ID of the task
	 * @return int  The task ID
	 */
	function getId()
	{
		return (int)$this->_data['id'];
	}
	
	/**
	 * Retrieves the _data array from the task
	 * @return array  The _data array from the task
	 */
	function getData()
	{
		return $this->_data;
	}
	
	/**
	 * Retrieves the parent ID of the task
	 * @return int  The ID of tasks parent
	 */
	function getParent()
	{
		return (int)$this->_data['parent'];
	}
	
	/**
	 * Sets the parent of the task
	 * @param int $parent  The ID of the task to set as parent
	 */
	function setParent( $parent )
	{
		$this->_data['parent'] = (int)$parent;
	}
	
	/**
	 * Retrieves the title of the task
	 * @return string  The title of the task
	 */
	function getTitle()
	{
		return $this->_data['title'];
	}
	
	/**
	 * Sets the title of the task
	 * @param string $title  The title to set for the task
	 */
	function setTitle( $title )
	{
		$this->_data['title'] = $title;
	}
	
	/**
	 * Retrieves the display colour of the task
	 * @return string  The display colour of the task
	 */
	function getColor()
	{
		return $this->_data['color'];
	}
	
	/**
	 * Sets the color of the task
	 * @param string $color  The color to set for the task
	 */
	function setColor( $color )
	{
		$this->_data['color'] = $color;
	}
	
	/**
	 * Retrieves the micro task or not status of the task
	 * @return boolean  True if a micro task, false otherwise
	 */
	function getMicro()
	{
		return (bool)$this->_data['micro'];
	}
	
	/**
	 * Sets the micro task or not status of the task
	 * @param boolean $micro  True if a micro task, false otherwise
	 */
	function setMicro( $micro )
	{
		$this->_data['micro'] = (bool)$micro;
	}
	
	/**
	 * Retrieves the first block of text for the task
	 * @return string  The text_1 attribute of the task
	 */
	function getText1()
	{
		return $this->_data['text_1'];
	}
	
	/**
	 * Sets the first block of text for the task
	 * @param string $text  The text_1 attribute of the task
	 */
	function setText1( $text )
	{
		$this->_data['text_1'] = $text;
	}
	
	/**
	 * Retrieves the second block of text for the task
	 * @return string  The text_2 attribute of the task
	 */
	function getText2()
	{
		return $this->_data['text_2'];
	}
	
	/**
	 * Sets the second block of text for the task
	 * @param string $text  The text_2 attribute of the task
	 */
	function setText2( $text )
	{
		$this->_data['text_2'] = $text;
	}
		
	/**
	 * Retrieves the tasks duration
	 * @return int  The duration of the task in hours
	 */
	function getDuration()
	{
		return (int)$this->_data['duration'];
	}
	
	/**
	 * Sets the tasks duration
	 * @param int $duration  The duration of the task in hours
	 */
	function setDuration( $duration ) // **** based on due dates in assignment groups?
	{
		$this->_data['duration'] = (int)$duration;
	}
	
	/**
	 * Retrieves the category list (name=>label)
	 * @return array  The defined categories for this task
	 */
	function getCategories()
	{
		return $this->_categories;
	}
	
	/**
	 * Retrieves the label list (name=>label)
	 * @return array  The defined labels for this task
	 */
	function getLabels()
	{
		return $this->_labels;
	}
	
	/**
	 * Retrieves the number of pieces of evidence required
	 * @return int|null  The number of evidence pieces required, or null if not set
	 */
	function getEvidenceNum()
	{
		return ( is_null($this->_data['evidence_num']) ? null : (int)$this->_data['evidence_num'] );
	}
	
	/**
	 * Sets the number of pieces of evidence required
	 * @param int $numEvidence  The number of evidence pieces required
	 */
	function setEvidenceNum( $num )
	{
		$this->_data['evidence_num'] = $num;
	}
	
	/**
	 * Retrieves the completed or not status of the task
	 * @return boolean  True if complete, false otherwise
	 */
	function getComplete()
	{
		return (bool)$this->_data['complete'];
	}
	
	/**
	 * Sets the completed or not status of the task
	 * @param boolean $complete  True if complete, false otherwise
	 */
	function setComplete( $complete )
	{
		$this->_data['complete'] = (bool)$complete;
	}
	
	/**
	 * Retrieves the progress of the task
	 * @return int  The percentage of the task completed
	 */
	function getProgress()
	{
		return (int)$this->_data['progress'];
	}
	
	/**
	 * Sets the progress of the task
	 * @param int $progress  The percentage of the task completed
	 */
	function setProgress( $progress ) // **** work this out over all subtasks/assignments
	{
		$this->_data['progress'] = (int)$progress;
	}
	
	/**
	 * Retrieves the display order of the task
	 * @return int  The display order of the task
	 */
	function getOrder()
	{
		return (int)$this->_data['order'];
	}
	
	/**
	 * Sets the display order of the task
	 * @param int $order  The display order of the task
	 */
	function setOrder( $order )
	{
		$this->_data['order'] = (int)$order;
	}
	
	function getTemplate( $fullPath = true )
	{
		if( empty($this->_data['template']) ) {
			return null;
		}
		if( $fullPath ) {
			return 'components'.DS.'com_arc_planner'.DS.'clips'.DS.$this->_data['template'];
		}
		else {
			return $this->_data['template'];
		}
	}
	
	function setTemplate( $template )
	{
		$this->_data['template'] = $template;
	}
	
	/**
	 * Retrieves the status of whether or not to show details for this task
	 * @return boolean  True if details shown, false otherwise
	 */
	function getDetailsShown()
	{
		return (bool)$this->_showDetails;
	}
	
	/**
	 * Sets the status of whether or not to show details for this task
	 * @param boolean $detailsShown  True if details shown, false otherwise
	 */
	function setDetailsShown( $detailsShown )
	{
		$this->_showDetails = (bool)$detailsShown;
	}
	
	/**
	 * Retrieves the status of whether or not to show subtasks for this task
	 * @return boolean  True if subtasks shown, false otherwise
	 */
	function getSubtasksShown()
	{
		return (bool)$this->_showSubtasks;
	}
	
	/**
	 * Sets the status of whether or not to show subtasks for this task
	 * @param boolean $subtasksShown  True if subtasks shown, false otherwise
	 */
	function setSubtasksShown( $subtasksShown )
	{
		$this->_showSubtasks = (bool)$subtasksShown;
	}
	
	/**
	 * Retrieves the number of children for this task
	 * @return int  The number of child tasks
	 */
	function getSubtasksCount()
	{
		if( empty($this->_linkedTasks['children']) ) {
			$this->_loadLinkedTasks( array('type'=>'children') );
		}
		
		return (int)count( $this->_linkedTasks['children'] );
	}
	
	/**
	 * Retrieves an array of task due dates.
	 * This includes next and ultimate due dates for the task as a whole
	 * and similar for the user if they are a member of any of the tasks
	 * assignment groups
	 * @return array  An associative array of due dates
	 */
	function getDueDates()
	{
		return $this->_dueDates;
	}
	
	function setGroupRequirements( $req )
	{
		if( !is_array($req) ) { $req = array(); }
		$this->_groupRequirements = $req;
		unset( $this->_groups );
	}
	
	/**
	 * Simply find out if this task has loaded its groups
	 * @return bool  True if some groups have been loaded, false otherwise
	 */
	function groupsLoaded()
	{
		return !empty($this->_groups);
	}
	
	/**
	 * Retrieves (by reference) the assignment group specified
	 * @param int $id  The id of the group to retrieve
	 * @return mixed  The task object with the given id (null if invalid or missing id given)
	 */
	function &getGroup( $id )
	{
//echo 'in task '.$this->_data['id'].' getting group '.$id.'<br />';
//echo 'number of groups already in getGroup:'.count($this->_groups).'<br />';
		if( empty($this->_groups) || !isset($this->_groups[$id]) ) {
			$this->_loadGroups();
		}
//echo 'num groups after load: '.count($this->_groups).'<br />';
		
		if( array_key_exists($id, $this->_groups) ) {
			$retVal = &$this->_groups[$id];
		}
		else {
			$retval = null;
		}
		
		return $retVal;
	}
	
	/**
	 * Retrieves an array of this tasks assignment group objects indexed on group ID
	 * loading them if necessary
	 * @return array  An associative array of assignment group objects
	 */
	function &getGroups()
	{
		if( empty($this->_groups) ) {
			$this->_loadGroups();
		}
		
		return $this->_groups;
	}
	
	/**
	 * Sets the array of this tasks assignment group objects indexed on group ID
	 * @param array $groups  An associative array of assignment group objects
	 */
	function setGroups( $groups )
	{
		if( is_string($groups) ) {
			$groups = array( $groups );
		}
		if( is_array($groups) ) {
			if( !is_object(reset($groups)) )
			{
				$groups = array();
			}
		}
		$this->_groups = $groups;
	}
	
	/**
	 * Loads assignment groups for this task from db
	 */
	function _loadGroups()
	{
		// get a database object
		$db = &JFactory::getDBO();
		
		// build query and retrieve data from database
		$query = ApothGroup::getInitQueryStatic( $this->_groupRequirements );
		$db->setQuery( $query );
		$this->_groups = $db->loadAssoclist( 'id' );
		
		// instantiate the groups we have found
		foreach( $this->_groups as $id=>$groupArray ) {
			$this->_groups[$id] = new ApothGroup( $id, $groupArray );
		}
	}
	
	/**
	 * Adds the given group to this task's list of groups
	 *
	 * @param object $g  The group object to be added to this task
	 */
	function addGroup( $g )
	{
		if( is_object($g) && (strtolower(get_class($g)) == 'apothgroup') ) {
			$this->_groups[$g->getId()] = $g;
		}
	}
	
	/**
	 * Removes the given group from this task's list of groups
	 *
	 * @param int $groupId  The id of the group object to be removed from this task
	 * @return boolean  True on success, false on failure
	 */
	function removeGroup( $groupId )
	{
		if( isset($this->_groups[$groupId]) ) {
			unset( $this->_groups[$groupId] );
			return true;
		}
		else {
			return false;
		}
	}
	
	/**
	 * Retrieves an array of the specified linked tasks
	 * @param string $type  Type of linked task to retrieve, corresponds to a _linkedTasks array key
	 * @return array  An indexed array of tasks IDs
	 */
	function getLinkedTasks( $requirements, $restrict = true )
	{
		$type = $requirements['type'];
		if( empty($this->_linkedTasks[$type]) ) {
			$this->_loadLinkedTasks( $requirements, $restrict );
		}

		return $this->_linkedTasks[$type];
	}
	
	/**
	 * Sets the array of this tasks linked tasks
	 * @param string $type  Type of linked task to set, corresponds to a _linkedTasks array key
	 * @param array $tasks  Indexed array of tasks IDs
	 */
	function setLinkedTasks( $type, $tasks )
	{
		if( is_string($tasks) ) {
			$tasks = array( $tasks );
		}
		if( is_array($tasks) ) {
			$t1 = reset($tasks);
			if( !is_string($t1) && !is_int($t1) )
			{
				$tasks = array();
			}
		}
		$this->_linkedTasks[$type] = $tasks;
	}
	
	/**
	 * Loads linked tasks of the specified type for this task from db
	 * *** requirements only get applied to the "children" case
	 * @param string $type  type of linked task to load, corresponds to array key
	 */
	function _loadLinkedTasks( $requirements, $restrict = true )
	{
		// get a database object
		$db = &JFactory::getDBO();
		$whereStrs = array();
		$innerStrs = array();

		foreach( $requirements as $col=>$val ) {
			//die('here');
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
			switch($col) {
			case('member'):
				$innerStrs[] = 'INNER JOIN `#__apoth_plan_groups` AS pg ON pg.task_id = t.id';					
				$innerStrs[] = 'INNER JOIN `#__apoth_plan_group_members` AS `gm` ON `gm`.`group_id` = `pg`.`id`';
				$whereStrs[] = '`gm`.`person_id` '.$assignPart;			
				break;
			}
		}
		
		$type = $requirements['type'];
		switch( $type ) {
		case( 'children' ):
			// query for child tasks of this task
			$whereStr = ((count($whereStrs) > 0) ? 'AND '.implode(' AND ', $whereStrs) : '');
			$innerStr = ((count($innerStrs) > 0) ? implode(' ', $innerStrs) : '');
			$query = 'SELECT DISTINCT'.$db->nameQuote( 't' ).'.'.$db->nameQuote( 'id' )
			."\n".' FROM '.$db->nameQuote( '#__apoth_plan_tasks' ).' AS '.$db->nameQuote( 't' )
			.( $restrict ? "\n".'~LIMITINGJOIN~' : '' )
			."\n".' '.$innerStr
			."\n".' WHERE ('.$db->nameQuote( 't' ).'.'.$db->nameQuote( 'parent' ).' = '.$this->_data['id'].')'
			."\n".' AND ('.$db->nameQuote( 't' ).'.'.$db->nameQuote( 'id' ).' != '.$db->nameQuote( 'parent' ).')'
			."\n".' AND ('.$db->nameQuote( 't' ).'.'.$db->nameQuote( 'deleted' ).' != 1)'
			."\n".' '.$whereStr
			."\n".' ORDER BY '.$db->nameQuote( 't' ).'.'.$db->nameQuote( 'order' ).' ASC';
			$db->setQuery( ApotheosisLibAcl::limitQuery($query, 'planner.tasks') );

			$this->_linkedTasks[$type] = $db->loadResultArray();
			break;
		
		case( 'requires' ):
			// query for the tasks required by this task
			$query = 'SELECT '.$db->nameQuote( 'req' ).'.'.$db->nameQuote( 'requires' )
			."\n".' FROM '.$db->nameQuote( '#__apoth_plan_tasks_requirements' ).' AS '.$db->nameQuote( 'req' )
			.( $restrict ? "\n".'~LIMITINGJOIN~' : '' )
			."\n".' WHERE ('.$db->nameQuote( 'req' ).'.'.$db->nameQuote( 'task_id' ).' = '.$this->_data['id'].')';
			$db->setQuery( ApotheosisLibAcl::limitQuery($query, 'planner.tasks', 'req', 'task_id') );
			
			$this->_linkedTasks[$type] = $db->loadResultArray();
			break;
		
		case( 'requiredBy' ):
			// query for the tasks that require this task
			$query = 'SELECT '.$db->nameQuote( 'req' ).'.'.$db->nameQuote( 'task_id' )
			."\n".' FROM '.$db->nameQuote( '#__apoth_plan_tasks_requirements' ).' AS '.$db->nameQuote( 'req' )
			.( $restrict ? "\n".'~LIMITINGJOIN~' : '' )
			."\n".' WHERE ('.$db->nameQuote( 'req' ).'.'.$db->nameQuote( 'requires' ).' = '.$this->_data['id'].')';
			$db->setQuery( ApotheosisLibAcl::limitQuery($query, 'planner.tasks', 'req', 'task_id') );
			
			$this->_linkedTasks[$type] = $db->loadResultArray();
			break;
		
		case( 'related' ):
			// query for the tasks related to this task, doing it twice to make limitQuery easier
			$query = 'SELECT '.$db->nameQuote( 'rel' ).'.'.$db->nameQuote( 'task_1' )
			."\n".' FROM '.$db->nameQuote( '#__apoth_plan_tasks_relations' ).' AS '.$db->nameQuote( 'rel' )
			.( $restrict ? "\n".'~LIMITINGJOIN~' : '' )
			."\n".' WHERE ('.$db->nameQuote( 'rel' ).'.'.$db->nameQuote( 'task_2' ).' = '.$this->_data['id'].')';
			$db->setQuery( ApotheosisLibAcl::limitQuery($query, 'planner.tasks', 'rel', 'task_1') );
			$task1 = $db->loadResultArray();
			
			$query = 'SELECT '.$db->nameQuote( 'rel' ).'.'.$db->nameQuote( 'task_2' )
			."\n".' FROM '.$db->nameQuote( '#__apoth_plan_tasks_relations' ).' AS '.$db->nameQuote( 'rel' )
			.( $restrict ? "\n".'~LIMITINGJOIN~' : '' )
			."\n".' WHERE ('.$db->nameQuote( 'rel' ).'.'.$db->nameQuote( 'task_1' ).' = '.$this->_data['id'].')';
			$db->setQuery( ApotheosisLibAcl::limitQuery($query, 'planner.tasks', 'rel', 'task_2') );
			$task2 = $db->loadResultArray();
			
			$this->_linkedTasks[$type] = array_merge( $task1, $task2 );
			break;
		}
		
		if( !is_array($this->_linkedTasks[$type]) ) {
			$this->_linkedTasks[$type] = array();
		}
		foreach($this->_linkedTasks[$type] as $k=>$v) {
			$this->_linkedTasks[$type][$k] = (int)$v;
		}
	}
	
	/**
	 * Retrieves the link which should be used to access the edit-update page for
	 * this task relevant to the given action name
	 * @param $actionId string  The id of the action that wants to display a link
	 * @param $JUserid string  Optional JUserId to limit query
	 * @return string  The url to use
	 */
	function getUrl( $actionId, $JUserId, $assigneeId, $forceRefresh = false )
	{
		if( empty($this->_urls[$actionId][$assigneeId]) || $forceRefresh ) {
			$this->_loadUrlData( $actionId, $JUserId, $assigneeId );
		}
		
		return $this->_urls[$actionId][$assigneeId];
	}
	
	/**
	 * Loads the link which should be used to access the edit-update page for
	 * this task relevant to the given action name
	 * @param $actionId string  The id of the action that wants to display a link
	 * @param $JUserid string  Optional JUserId to limit query
	 */
	function _loadUrlData( $actionId, $JUserId, $assigneeId )
	{
		$db = &JFactory::getDBO();
		
		$t = explode( ' ', microtime() );
		$table = $db->nameQuote( 'tmp_'.$t[1].str_replace('.', '', $t[0]) );
		$query = 'CREATE TABLE '.$table.' ENGINE=MyISAM AS'
			."\n".'SELECT COUNT(*) AS level, a2.id'
			."\n".'FROM `jos_apoth_plan_tasks_ancestry` AS a1'
			."\n".'INNER JOIN jos_apoth_plan_tasks_ancestry AS a2'
			."\n".'   ON a2.id = a1.ancestor'
			."\n".'WHERE a1.id = '.$db->Quote( $this->_data['id'] )
			."\n".'GROUP BY a2.id';
		$db->setQuery( $query );
		$db->Query();
		
		$myTasks = ApotheosisLibAcl::getUserTable( 'planner.tasks', $JUserId );
		$query = 'SELECT l.*, l.role IS NULL AS nullrole'
			."\n".'FROM '.$table.' AS a'
			."\n".'LEFT JOIN jos_apoth_plan_links AS l'
			."\n".'  ON l.task_id = a.id'
			."\n".' AND l.action_id = '.$db->Quote($actionId)
			."\n".'INNER JOIN '.$db->nameQuote($myTasks).' AS lim_t'
			."\n".'   ON lim_t.id = `l`.`task_id`'
			."\n".'  AND (lim_t.role = `l`.`role` OR l.role IS NULL)'
			."\n".'ORDER BY level DESC, nullrole ASC;';
		$db->setQuery( $query );
		$r = $db->loadAssocList();

//		debugQuery($db, $r);
//		die();
		if( !is_array($r) ) { $r = array(); }
		
		$query = 'DROP TABLE '.$table;
		$db->setQuery( $query );
		$db->Query();

		// default dependancies if none given
		$dependancies = array( 'planner.pretty'=>'0', 'planner.form'=>'update_single', 'planner.scope'=>'editUpdate' );
		foreach( $r as $row ) {

			if( !is_null($row['pretty']) && !is_null($row['form']) ) {
				$dependancies = array( 'planner.pretty'=>$row['pretty'], 'planner.form'=>$row['form'], 'planner.scope'=>$row['scope'] );
				break;
			}
		}
		$dependancies['planner.tasks'] = $this->_data['id'];
		$dependancies['planner.arc_people'] = $assigneeId;
		$dependancies['core.actions'] = $actionId;
		
		$this->_urls[$actionId][$assigneeId] = ApotheosisLib::getActionLinkByName( 'apoth_plan_update_edit_inter2', $dependancies );
	}
	
	/**
	 * Commits the current state of a task by writing it to the database
	 * 
	 * @return boolean|int  The task id on success, false on failure
	 */
	function commit()
	{
		$db = &JFactory::getDBO();
		
		$assignments = array();
		foreach( $this->_data as $col=>$val ) {
			$assignments[] = $db->nameQuote( $col ).' = '.( is_null($val) ? 'NULL' : $db->Quote($val) );
		}
		
		$queryMid = 'SET '.implode( ', ', $assignments );
		if( empty($this->_data['id']) ) {
			$query = 'INSERT INTO '.$db->nameQuote( '#__apoth_plan_tasks' )
				."\n".$queryMid;
		}
		else {
			$query = 'UPDATE '.$db->nameQuote( '#__apoth_plan_tasks' )
				."\n".$queryMid
				."\n".' WHERE '.$db->nameQuote( 'id' ).' = '.$db->Quote( $this->_data['id'] );
		}
		$db->setQuery( $query );
		$db->Query();
		
		// work out a nice return value
		if( $db->getErrorMsg() != '' ) {
			return false;
		}
		
		// set id for this task from db auto-increment if needed
		if( empty($this->_data['id']) ) {
			$this->_data['id'] = $db->insertId();
			$retVal = $this->_data['id'];
		}
		
		ApotheosisLibDb::updateAncestry( '#__apoth_plan_tasks' );
		
		// set the order column based on siblings
		if( $this->_data['order'] == null ) {
			$maxOrderQuery = 'SELECT MAX(`order`)'
				."\n".'FROM '.$db->nameQuote( '#__apoth_plan_tasks' )
				."\n".'WHERE '.$db->nameQuote( 'parent' ).' = '.$db->Quote( $this->getParent() )
				."\n".'GROUP BY '.$db->nameQuote( 'parent' );
			$db->setQuery( $maxOrderQuery );
			$maxOrder = $db->loadResult();
			
			$setOrderQuery = 'UPDATE '.$db->nameQuote( '#__apoth_plan_tasks' )
				."\n".'SET '.$db->nameQuote( 'order' ).' = '.$db->Quote( ++$maxOrder )
				."\n".'WHERE '.$db->nameQuote( 'id' ).' = '.$db->Quote( $this->_data['id'] );
			$db->setQuery( $setOrderQuery );
			$db->Query();
		}
		
		return $retVal;
	}
}
?>
