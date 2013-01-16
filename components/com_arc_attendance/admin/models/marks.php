<?php
/**
 * @package     Arc
 * @subpackage  Attendance
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

/**
 * Extension Manager Summary Model
 *
 * @author		Louis Landry <louis.landry@joomla.org>
 * @package		Joomla
 * @subpackage	Installer
 * @since		1.5
 */
class AttendancemanagerModelMarks extends JModel
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
		
		$query = "SELECT c.code, c.type, c.school_meaning AS school_meaning_id, c.physical_meaning AS physical_meaning_id, c.statistical_meaning AS statistical_meaning_id, c.is_common, c.apply_all_day, c.order_id, c.image_link" .
				"\n FROM #__apoth_att_codes AS c" .
				($whereStr === false ? '' : "\n WHERE ".$whereStr).
				"\n ORDER BY `type`, `order_id`";
		$db->setQuery($query);
		$rows = $db->loadObjectList();
		
		$numRows = count($rows);
		
		$this->setState('pagination.total', $numRows);
		$this->_items = $rows;
	}
	
	function &getMeanings()
	{
		if (empty($this->_meanings)) {
			// Load the items
			$this->_loadMeanings();
		}
		return $this->_meanings;
	}
	
	/**
	 * Fetches a list of the attendance code meanings stored in the database
	 *
	 * @return object  An associative array of objects, indexed on type of meaning
	 */
	function _loadMeanings()
	{
		/* Get a database connector */
		$db =& JFactory::getDBO();

		$query = "SELECT DISTINCT sm.id, sm.meaning AS school_meaning" .
				"\n FROM #__apoth_att_codes AS c" .
				"\n INNER JOIN #__apoth_att_school_meaning AS sm ON sm.id = c.school_meaning" .
				"\n ORDER BY `order_id`";
		$db->setQuery($query);
		$this->_meanings['school'] = $db->loadObjectList('id');
		
		$query = "SELECT DISTINCT stm.id, stm.meaning AS statistical_meaning" .
				"\n FROM #__apoth_att_codes AS c" .
				"\n INNER JOIN #__apoth_att_statistical_meaning AS stm ON stm.id = c.statistical_meaning" .
				"\n ORDER BY `order_id`";
		$db->setQuery($query);
		$this->_meanings['statistical'] = $db->loadObjectList('id');
		
		$query = "SELECT DISTINCT pm.id, pm.meaning AS physical_meaning" .
				"\n FROM #__apoth_att_codes AS c" .
				"\n INNER JOIN #__apoth_att_physical_meaning AS pm ON pm.id = c.statistical_meaning" .
				"\n ORDER BY `order_id`";
		$db->setQuery($query);
		$this->_meanings['physical'] = $db->loadObjectList('id');
	}
	
	/**
	 * Saves a mark's definitions to the db
	 * A change of code is recorded in the "changes" column and applied to all attendance marks
	 * recorded in the dailyatt table.
	 */
	function save()
	{
		// Get a database connector
		$db =& JFactory::getDBO();
		
		$oldCode = JRequest::getVar('code');
		$newCode = JRequest::getVar('newCode');
		$schMeaning = JRequest::getVar('schMeaning');
		$statMeaning = JRequest::getVar('statMeaning');
		$physMeaning = JRequest::getVar('physMeaning');
		$is_common = JRequest::getVar('is_common');
		$imageLink = ((JRequest::getVar('image_link') == '') ? NULL : JRequest::getVar('image_link') );
		$type = JRequest::getVar('type');
		$order_id = JRequest::getVar('order_id');
		if ( ($imageLink != '') && (strpos($imageLink, DS) === false) ) {
			$imageLink = '.'.DS.'components'.DS.'com_arc_attendance'.DS.'images'.DS.$imageLink;
			JRequest::setVar('image_link', $imageLink);
		}
		
		$res = array();
		if ($oldCode != $newCode) {
			// check that the change of code isn't going to cause a clash
			$sqlStr = 'SELECT `code` FROM #__apoth_att_codes'
				."\n".' WHERE `code` = "'.$newCode.'" AND `type` = "'.$type.'"';
			$db->setQuery($sqlStr);
			$res = $db->loadObjectList();
		}
		
		$sqlStr = 'SELECT * FROM #__apoth_att_codes WHERE `code`="'.$oldCode.'" AND `type` = "'.$type.'"';
		$db->setQuery($sqlStr);
		$mark = $db->loadObject();
		
		if ((is_null($oldCode)) && ($newCode != '') && ($type != '')) {
			$obj->code = $newCode;
			$obj->school_meaning = $schMeaning;
			$obj->statistical_meaning = $statMeaning;
			$obj->physical_meaning = $physMeaning;
			$obj->is_common = $is_common;
			$obj->valid_from = date('Y-m-d h:i:s');
			$obj->image_link = $imageLink;
			$obj->type = $type;
			$obj->order_id = $order_id;
			
			$db->insertObject( '#__apoth_att_codes', $obj);
			
			$retVal = true;
		}
		elseif ( (count($res) > 0) || is_null($mark) ) {
			$retVal = false;
		}
		else {
			// write the changes, can't use $db->updateObject as key may have changed
			// * may move the id change into an sql up in the first "if", then use the
			// * updateObject function down here, just seems in-efficient.
			$sqlStr = 'UPDATE #__apoth_att_codes SET'
				."\n".' `code`="'.$newCode.'"'
				."\n".', `school_meaning`="'.$schMeaning.'"'
				."\n".', `statistical_meaning`="'.$statMeaning.'"'
				."\n".', `physical_meaning`="'.$physMeaning.'"'
				."\n".', `is_common`="'.$is_common.'"'
				."\n".', `image_link`="'.$imageLink.'"'
				."\n".', `type` ="'.$type.'"'
				."\n".', `order_id` ="'.$order_id.'"'
				."\n".' WHERE `code`="'.$oldCode.'" AND `type` = "'.$type.'"';
			$db->setQuery($sqlStr);
			$retVal = $db->query();
		}

		return $retVal;
	}

	
}
?>