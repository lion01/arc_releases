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
 * Planner Category Model
 */
class PlannerModelCategory extends JModel
{
	/**
	 * All the current categories (root level tasks) that have been created are stored in here
	 * @access protected
	 * @var array  Id-indexed array of current category objects
	 */
	var $_current = array();
	
	/**
	 * All the retired categories (root level tasks) that have been created are stored in here
	 * @access protected
	 * @var array  Id-indexed array of retired category objects
	 */
	var $_retired = array();
	
	/**
	 * Loads all categories and stores their instances as either current or retired
	 * @return int  How many categories there are in total
	 */
	function setCategories()
	{
		// get a list of categories
		$catsList = ApotheosisLibDb::getRootItems( '#__apoth_plan_tasks' );
	
		// get a database object
		$db = &JFactory::getDBO();
		
		// loop through categories list to build query
		if( !is_array($catsList) ) {
			$catsList = array();
		}
		foreach( $catsList as $k=>$id ) {
			$catsList[$k] = $db->Quote( $id );
		}
		
		// formulate query to return matched task id's
		$query = 'SELECT *'
			."\n".' FROM '.$db->nameQuote( '#__apoth_plan_tasks' ).' AS '.$db->nameQuote( 't' )
			."\n".'~LIMITINGJOIN~'
			."\n".' WHERE '.$db->nameQuote( 't' ).'.'.$db->nameQuote( 'id' ).' IN ('.implode( ', ',$catsList).')';
		$db->setQuery( ApotheosisLibAcl::limitQuery($query, 'planner.tasks') );
		$cats = $db->loadAssocList( 'id' );
		
		// split categories into current and retired, instantiate them and store in model
		foreach( $cats as $id=>$cat ) {
			if( (bool)$cat['deleted'] ) {
				$this->_retired[$id] = new ApothCategory( $id, $cat );
			}
			else {
				$this->_current[$id] = new ApothCategory( $id );
			}
		}
		
		// load the stats for the current categories
		$this->_loadStats( array_keys( $this->_current ) );
	}
	
	/**
	 * Used to populate the statistics of the given categories
	 * Uses the getStatQueryStatic function in ApothCategory to formulate the query to run
	 * Pulls out all the data that way, then sends each category's data off to
	 * setStats so the category's stat data is populated
	 * We do this here to avoid re-querying for each category
	 * @see ApothCategory::getStatQueryStatic()
	 * @see ApothCategory::setStats()
	 * @param array $ids  The indexed array of ids of categories whose stats are to be populated
	 */
	function _loadStats( $ids )
	{
		// get a database object
		$db = &JFactory::getDBO();
		
		// get the query
		$query = ApothCategory::getStatQueryStatic( $ids );
		
		// set and run the query and store as array indexed on category ID
		$db->setQuery( $query );
		$stats = $db->loadAssocList( 'ancestor' );
		if( !is_array($stats) ) { $stats = array(); }
		
		// send each array of stats to the relevant category object for inclusion
		foreach( $stats AS $catId=>$catStats ) {
			$catObj = &$this->getCategory( $catId );
			$catObj->setStats( $catStats );
		}
	}
	
	/**
	 * Retrieves a reference to the category specified by $id
	 * @param $id int  The id of the category to fetch
	 * @return object  A reference to the category object specified
	 */
	function &getCategory( $id )
	{
		if( array_key_exists($id, $this->_current) ) {
			$retVal = &$this->_current[$id];
		}
		elseif( array_key_exists($id, $this->_retired) ) {
			$retVal = &$this->_retired[$id];
		}
		else {
			$retVal = null;
		}
		
		return $retVal;
	}
	
	/**
	 * Retrieves the ids of all current categories
	 * @return array  An indexed array of current category ids
	 */
	function getCurrent()
	{
		return array_keys( $this->_current );
	}
	
	/**
	 * Retrieves the ids of all retired categories
	 * @return array  An indexed array of retired category ids
	 */
	function getRetired()
	{
		return array_keys( $this->_retired );
	}
}
?>