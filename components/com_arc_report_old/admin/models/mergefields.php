<?php
/**
 * @package     Arc
 * @subpackage  Report
 * @copyright   Copyright (C) 2005 Punnet. All rights reserved. See COPYRIGHT_ARC.txt
 * @license     http://www.gnu.org/licenses/gpl.html GNU/GPL. See LICENSE_ARC.txt
 * Arc is free software: you can redistribute it and/or modify
 * it under the terms of version 3 of the GNU General Public License
 * as published by the Free Software Foundation.
 * The disclaimer of warranty as stated in the GPL applies to this program
 */

defined( '_JEXEC' ) or die( 'Restricted access' );

jimport( 'joomla.application.component.model' );
jimport( 'joomla.installer.installer' );
require_once( JPATH_COMPONENT_ADMINISTRATOR.DS.'helpers'.DS.'cycles.php' );

/**
 * Extension Manager Summary Model
 *
 * @author		Louis Landry <louis.landry@joomla.org>
 * @package		Joomla
 * @subpackage	Installer
 * @since		1.5
 */
class ReportsModelMergeFields extends JModel
{
	/** @var array Array of installed components */
	var $_items = array();
	
	/** @var object of the last individually retrieved item */
	var $_item = false;
	
	/** @var array 2-d array of all meanings for marks */
	var $_meanings = array();
	
	/** @var string Name of the property defining the individual item */
	var $_properties = 'code';
	
	/**
	 * Overridden constructor
	 * @access	protected
	 */
	function __construct()
	{
		parent::__construct();
	}

	/**
	 *
	 * @param array $colVals  Associative array of column=>value pairs from which to create a WHERE clause
	 */
	function &setItems($colVals)
	{
		foreach ($colVals as $k=>$v ) {
			$whereArr[] = '`'.$k.'` = "'.$v.'"';
		}
		$whereStr = implode(' AND ', $whereArr);
		
		$this->_loadItems($whereStr);
	}
	
	function &getItems()
	{
		if (empty($this->_items)) {
			// Load the items
			$this->_loadItems();
		}
		return $this->_items;
	}
	
	function _loadItems($whereStr = false)
	{
		/* Get a database connector */
		$db =& JFactory::getDBO();
		
		$query = '	SELECT *' .
				"\n".' 	FROM `#__apoth_rpt_merge_words`' .
				($whereStr === false ? '' : "\n".' WHERE '.$whereStr).
				"\n".' ORDER BY `id`';
		$db->setQuery($query);
		$rows = $db->loadObjectList();
		
		$numRows = count($rows);
		
		$this->setState('pagination.total', $numRows);
		$this->_items = $rows;
	}
	
	function getField()
	{
		$db = &JFactory::getDBO();
		$field = current(JRequest::getVar('eid'));

		$query = '	SELECT *' .
				"\n".' 	FROM `#__apoth_rpt_merge_words`' .
				"\n".'	WHERE `id` = '.$db->quote($field);
		$db->setQuery($query);
		
		return $db->loadObject();
	}
	
	/**
	 * Updates a Report Merge Field's details to the db
	 */
	function updateField( $params = array() )
	{
		if(empty($params)) {
			return false;
		}
		// Get a database connector
		$db =& JFactory::getDBO();
		
		// write the changes, can't use $db->updateObject as key may have changed
		// * may move the id change into an sql up in the first "if", then use the
		// * updateObject function down here, just seems in-efficient.
		$sqlStr = 'UPDATE `#__apoth_rpt_merge_words` SET'
			."\n".' `word`= '.$db->quote($params['word'])
			."\n".', `male`= '.$db->quote($params['male'])
			."\n".', `female`='.$db->quote($params['female'])
			."\n".', `neuter`='.$db->quote($params['neuter'])
			."\n".', `property`='.$db->quote($params['property'])
			."\n".' WHERE `id`= '.$db->quote($params['id']);
		$db->setQuery($sqlStr);
		
		return $db->query();
	}

	/**
	 * Creates a Report Merge Field
	 */
	function newField( $params = array() )
	{
		if(empty($params)) {
			return false;
		}
		// Get a database connector
		$db =& JFactory::getDBO();

		// write the changes, can't use $db->updateObject as key may have changed
		// * may move the id change into an sql up in the first "if", then use the
		// * updateObject function down here, just seems in-efficient.
		$sqlStr = 'INSERT INTO `#__apoth_rpt_merge_words` (`word`, `male`, `female`, `neuter`, `property`)'
				."\n".' VALUES ('.$db->quote($params['word']).', '
				."\n".' '.$db->quote($params['id']).', '
				."\n".' '.$db->quote($params['male']).', '
				."\n".' '.$db->quote($params['female']).', '
				."\n".' '.$db->quote($params['neuter']).', '
				."\n".' '.$db->quote($params['property']).')';
		
		$db->setQuery($sqlStr);
		
		return $db->query();
	}
}
?>