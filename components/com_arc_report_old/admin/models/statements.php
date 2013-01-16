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

require_once( JPATH_COMPONENT_ADMINISTRATOR.DS.'models'.DS.'extension.php' );

/**
 * Extension Manager Summary Model
 *
 * @author		Mike Heaver <m.heaver@wildern.hants.sch.uk>
 * @package		Joomla
 * @subpackage	Installer
 * @since		1.5
 */
class ReportsModelStatements extends ReportsModel
{
	/** @var array Array of selected courses */
	var $_courses = array();
	
	/** @var string Earliest report cycle date */
	var $_mincycledates = '';
	
	/** @var string Latest report cycle date */
	var $_maxcycledates = '';
	
	/**
	 * Overridden constructor
	 * @access	protected
	 */
	function __construct()
	{
		parent::__construct();
	}
	
	/**
	 * Fetches Subjects that are Direct Children of the Root Item within all Report Cycle Boundaries
	 */
	function &getCourses()
	{
		if (empty($this->_courses)) {
			// Load the courses
			$this->_loadCourses();
		}
		return $this->_courses;
	}
	
	function _loadCourses()
	{
		$db = &JFactory::getDBO();
		$query = 'SELECT *'
			."\n".'FROM '.$db->nameQuote('#__apoth_cm_courses')
			."\n".'WHERE '.$db->nameQuote('parent').' = '.ApotheosisLibDb::getRootItem()
			."\n".'  AND '.$db->nameQuote('ext_type').' = '.$db->Quote('subject')
			."\n".'  AND '.$db->nameQuote('deleted').' = '.$db->Quote('0')
			."\n".'  AND '. ApotheosisLibDb::dateCheckSql('start_date', 'end_date', $this->getMinCycleDates(), $this->getMaxCycleDates())
			."\n".'ORDER BY '.$db->nameQuote('fullname');
		
		$db->setQuery($query);
		$results = $db->loadObjectList( );
		
		$this->_courses = $results;
	}
	
	/**
	 * Returns earliest report cycle start date
	 */
	function &getMinCycleDates()
	{
		if (empty($this->_mincycledates)) {
			// Load the items
			$this->_loadCycleDates();
		}
		return $this->_mincycledates;
	}
	
	/**
	 * Returns latest report cycle end date
	 */
	function &getMaxCycleDates()
	{
		if (empty($this->_maxcycledates)) {
			// Load the items
			$this->_loadCycleDates();
		}
		return $this->_maxcycledates;
	}
	
	function _loadCycleDates()
	{
		// Get a database connector
		$db =& JFactory::getDBO();
		
		$query = 'SELECT MIN('.$db->nameQuote('valid_from').') AS '.$db->nameQuote('mindate').', MAX('.$db->nameQuote('valid_to').') AS '.$db->nameQuote('maxdate')
			."\n".' FROM '.$db->nameQuote('#__apoth_rpt_cycles');
		$db->setQuery($query);
		$result = $db->loadObject();
		
		$this->_mincycledates = $result->mindate;
		$this->_maxcycledates = $result->maxdate;
	}
	
	/**
	 * Copies Statement banks to new Cycle
	 * @param $subj array  Array of subjects to copy statements for
	 * @return int  The number of statements copied
	 * @param $source int  The ID of the source cycle
	 * @param $target int  The ID of the target cycle
	 */
	function copyStatements( $subj, $source, $target )
	{
		// Get a database connector
		$db =& JFactory::getDBO();
		
		foreach ($subj as $k=>$v) {
			$subj[$k] = $db->Quote($v);
		}
		
		$query = 'INSERT INTO '.$db->nameQuote('jos_apoth_rpt_statements_map').' ('.$db->nameQuote('group_id').', '.$db->nameQuote('statement_id').', '.$db->nameQuote('cycle_id').', '.$db->nameQuote('order').')'
			."\n".' SELECT '.$db->nameQuote('group_id').', '.$db->nameQuote('statement_id').', '.$db->Quote($target).' AS '.$db->nameQuote('cycle_id').', '.$db->nameQuote('order')
			."\n".' FROM '.$db->nameQuote('jos_apoth_rpt_statements_map')
			."\n".' WHERE '.$db->nameQuote('group_id').' IN ('.implode( ', ', $subj ).')'
			."\n".' AND '.$db->nameQuote('cycle_id').' = '.$db->Quote($source);
		$db->setQuery($query);
		$db->query();
		return $db->getAffectedRows();
	}
}
?>