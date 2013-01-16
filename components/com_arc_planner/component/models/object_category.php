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
 * Planner Catergory Object
 */
class ApothCategory extends ApothTask
{
	/**
	 * The summary statistics for this category
	 * 
	 * @access protected
	 * @var array
	 */
	var $_stats;
	
	/**
	 * Constructs a category object.
	 * The result is either empty or if an ID is given it is
	 * populated by the $data array or by retrieving data from the db
	 * Since categories extend tasks' features we pass the data up to the task
	 * constructor to get the basic task, then fill in any exta info. If data is given
	 * and it doesn't contain the extra info, then nulls are used.
	 * 
	 * @param int $id optional  If not provided an empty category object is created.
	 * @param array $data optional  If given along with an id this is used as the data for the object (omits linked tasks)
	 * @param boolean $loadListCols optional Defaults to false. If true then the column definitions for list view of this category will be loaded.
	 * 
	 * @return  The newly created task object
	 */
	function __construct( $id = false, $data = array(), $loadListCols = false )
	{
		parent::__construct( $id, $data );
		
		if( $id !== false ) {
			if( array_key_exists('deleted_on', $data) ) {
				$this->_data['deleted_on'] = $data['deleted_on'];
			}
		}
	}
	
	/**
	 * Sets the statistical data for this category
	 * If no data provided, runs the queries from getStatQuery, then sets the data 
	 * @see getStatQuery()
	 * @param array $data  optional If given, this is used as the data to set, otherwise the db is queried
	 */
	function setStats( $data = false )
	{
		if( empty($data) ) {
			// get a database object
			$db = &JFactory::getDBO();
			
			// get the query array
			$query = $this->getStatQuery();
			
			// query for complete/incomplete stats
			$db->setQuery( $query );
			$data = $db->loadAssoc();
		}
		$this->_stats['numComplete'] =   (int)$data['num_complete'];
		$this->_stats['numInComplete'] = (int)$data['num_incomplete'];
		$this->_stats['overdue'] =       (int)$data['overdue'];
		$this->_stats['due'] =           (int)$data['due_this_week'];
	}
	
	/**
	 * Gives the query which is to be run to find the statistics for this category
	 * @see getStatQueryStatic()
	 * @return string  The query to execute to find statistical data
	 */
	function getStatQuery()
	{
		return ApothCategory::getStatQueryStatic( $this->_data['id'] );
	}
	
	/**
	 * Gives the query to be run to find the statistics for the given category(ies)
	 * @param array|int $ids  The id(s) to use in the query string
	 * @return string  The query to execute to find statistical data
	 */
	function getStatQueryStatic( $ids )
	{
		if( !is_array($ids) ) {
			$ids = array( $ids );
		}
		
		// get a database object
		$db = &JFactory::getDBO();
		
		// prepare the mysql IN clause
		foreach ($ids as $k=>$id ) {
			$ids[$k] = $db->Quote( $id );
		}
		$ids = implode( ' ,', $ids );
		
		// set up temporary table
		$plannerParams = &JComponentHelper::getParams( 'com_arc_planner' );
		$dueDaysAhead = $plannerParams->get( 'due_days_ahead' );
		$tmpQuery = 'CREATE TABLE '.$db->nameQuote( '~TABLE~' ).' AS'
		."\n".' SELECT t.'.$db->nameQuote( 'id' ).' AS '.$db->nameQuote( 'task_id' ).','
		."\n".' t.'.$db->nameQuote( 'complete' ).' AS '.$db->nameQuote( 'task_complete' ).','
		."\n".' g.'.$db->nameQuote( 'complete' ).' AS '.$db->nameQuote( 'group_complete' ).','
		."\n".' SUM(IF(((((UNIX_TIMESTAMP(g.'.$db->nameQuote( 'due' ).') - UNIX_TIMESTAMP(NOW())) / 86400) < 0) AND (g.'.$db->nameQuote( 'complete' ).' = 0)), 1, 0)) AS '.$db->nameQuote( 'overdue' ).','
		."\n".' SUM(IF(((((UNIX_TIMESTAMP(g.'.$db->nameQuote( 'due' ).') - UNIX_TIMESTAMP(NOW())) / 86400) BETWEEN 0 AND '.$dueDaysAhead.') AND (g.'.$db->nameQuote( 'complete' ).' = 0)), 1, 0)) AS '.$db->nameQuote( 'due_this_week' )
		."\n".' FROM '.$db->nameQuote( '#__apoth_plan_tasks' ).' AS t'
		."\n".' INNER JOIN '.$db->nameQuote( '#__apoth_plan_tasks_ancestry' ).' AS anc'
		."\n".' ON t.'.$db->nameQuote( 'id' ).' = anc.'.$db->nameQuote( 'id' )
		."\n".' LEFT JOIN '.$db->nameQuote( '#__apoth_plan_groups' ).' AS g'
		."\n".' ON t.'.$db->nameQuote( 'id' ).' = g.'.$db->nameQuote( 'task_id' )
		."\n".'~LIMITINGJOIN~'
		."\n".' WHERE (anc.'.$db->nameQuote( 'ancestor' ).' IN ('.$ids.')) AND (anc.'.$db->nameQuote( 'id' ).' != anc.'.$db->nameQuote( 'ancestor' ).')'
		."\n".' GROUP BY '.$db->nameQuote( 'task_id' ).';';
		$tmpQuery = ApotheosisLibAcl::limitQuery( $tmpQuery, 'planner.tasks', 't', 'task_id' );
		$tmpTableName = ApotheosisLibDbTmp::initTable( $tmpQuery, false, 'planner', 'tasks' );
		ApotheosisLibDbTmp::setTtl( $tmpTableName, 10 );
		
		// formulate return query
		$query = 'SELECT '.$db->nameQuote( 'ancestor' ).','
		."\n".' SUM( IF( (t.'.$db->nameQuote( 'task_complete' ).' = 1), 1, 0 ) ) AS num_complete,'
		."\n".' SUM( IF( (t.'.$db->nameQuote( 'task_complete' ).' = 1), 0, 1 ) ) AS num_incomplete,'
		."\n".' SUM( IF( (t.'.$db->nameQuote( 'overdue' ).' > 0), 1, 0 ) ) AS overdue,'
		."\n".' SUM( IF( (t.'.$db->nameQuote( 'due_this_week' ).' > 0), 1, 0 ) ) AS due_this_week'
		."\n".' FROM '.$db->nameQuote( $tmpTableName ).' AS t'
		."\n".' INNER JOIN '.$db->nameQuote( '#__apoth_plan_tasks_ancestry' ).' AS anc'
		."\n".' ON t.'.$db->nameQuote( 'task_id' ).' = anc.'.$db->nameQuote( 'id' )
		."\n".' WHERE (anc.'.$db->nameQuote( 'ancestor' ).' IN ('.$ids.')) AND (anc.'.$db->nameQuote( 'id' ).' != anc.'.$db->nameQuote( 'ancestor' ).')'
		."\n".' GROUP BY anc.'.$db->nameQuote( 'ancestor' );
		
		return $query;
	}
	
	/**
	 * Retrieves the ID of the category
	 * @return int  The category ID
	 */
	function getId()
	{
		return (int)$this->_data['id'];
	}
	
	/**
	 * Retrieves the title of the category
	 * @return string  The category title
	 */
	function getTitle()
	{
		return $this->_data['title'];
	}
	
	/**
	 * Retrieves the progress of the category
	 * @return int  The category progress
	 */
	function getProgress()
	{
		return (int)$this->_data['progress'];
	}
	
	/**
	 * Retrieves the number of complete categories
	 * @return int  the number of complete categories
	 */
	function getComplete()
	{
		return (int)$this->_stats['numComplete'];
	}
	
	/**
	 * Retrieves the number of incomplete categories
	 * @return int  the number of incomplete categories
	 */
	function getIncomplete()
	{
		return (int)$this->_stats['numInComplete'];
	}
	
	/**
	 * Retrieves the number of overdue categories
	 * @return int  the number of overdue categories
	 */
	function getOverdue()
	{
		return (int)$this->_stats['overdue'];
	}
	
	/**
	 * Retrieves the number of due categories
	 * @return int  the number of due categories
	 */
	function getDue()
	{
		return (int)$this->_stats['due'];
	}
	
	/**
	 * Retrieves the deleted date of the category
	 * @return string  The category deleted date
	 */
	function getDeletedOn()
	{
		return $this->_data['deleted_on'];
	}
	
	
	
	
	
	
	
	
	
	
	
	/**
	 * Retrieves the column definitions for this category's list view
	 * loading them if necessary
	 * List columns are each an assoc array of:
	 *  title,
	 *  sub-task level,
	 *  deep property def (array from string in db),
	 *  how to deal with multiples (one of: first, duplicate, split, bullets)
	 *
	 * @return array  The list view column definitions in the order they are to be used
	 */
	function getListCols()
	{
	}
	
	/**
	 * Interrogates the database to find the column definitions for this category's list view
	 */
	function _loadListCols()
	{
	}
	
	/**
	 * Commits the changes to the category by writing them to the database
	 * First commits the basic task data, then the category-only data
	 * 
	 * @return boolean  true on success, false on failure
	 */
	function commit()
	{
		parent::commit();
	}
}
?>
