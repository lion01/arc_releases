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

require_once( JPATH_COMPONENT.DS.'models'.DS.'extension.php' );

/**
 * Reports Report Model
 *
 * @author		lightinthedark <code@lightinthedark.org.uk>
 * @package		Apotheosis
 * @subpackage	Reports
 * @since 0.1
 */
class ReportsModelReport extends ReportsModel
{
	/** @var The report instance that we want to deal with in this model */
	var $_report;
	var $_reports;
	var $_blankReports;
	
	function __construct( $config = array() )
	{
		parent::__construct( $config );
		$this->_reports = array();
		$this->_blankReports = array();
	}
	
	/**
	 * Set the current report to be the existing report with the given id.
	 */
	function setReportExisting( $id )
	{
		if( !array_key_exists($id, $this->_reports) ) {
			$this->_reports[$id] = &ApothReport::getInstance( $id );
		}
		$this->_report = &$this->_reports[$id];
		return true;
	}
	
	/**
	 * Sets the current report to be the new report object created from
	 * the given student, group, cycle combination
	 * Does NOT add the new report to the internal list of pre-existing reports
	 */
	function setReportNew( $student, $group, $cycle, $addToList = false, $allowDuplicates = false )
	{
		$rpt = &ApothReport::newInstance( $student, $group, $cycle );
		if( $addToList
		 && ( $allowDuplicates || empty($this->_blankReports[$cycle][$group][$student]) ) ) {
			$this->_blankReports[$cycle][$group][$student][] = &$rpt;
		}
		$this->_report = &$rpt;
		return true;
	}
	
	/**
	 * Sets the internal list of reports to hold all existing reports from the db
	 * which meet the requirements given
	 */
	function setReports( $requirements, $dataOnly = false )
	{
		if( !isset($this->_requirements) || ($this->_requirements != $requirements) ) {
			$this->_requirements = $requirements;
			$this->_reports = array();
			$db = &JFactory::getDBO();
			//  *** can we make the where string and join string more optimal?
			if( $requirements['getBy'] == 'course' ) {
				if( array_key_exists( 'course', $requirements ) && is_array($tmp = $requirements['course']) ) {
					foreach( $tmp as $k=>$v ) {
						$tmp[$k] = $db->Quote($v);
					}
					$joinStrs['course'] = 'INNER JOIN #__apoth_cm_courses AS c'
						."\n".' ON c.id = r.group';
					$whereStrs[] = $db->nameQuote('c').'.'.$db->nameQuote('parent').' IN ('.implode(', ', $tmp).')';
					$whereStrs[] = $db->nameQuote('c').'.'.$db->nameQuote('deleted').' = '.$db->Quote('0');
				}
				
				if( array_key_exists( 'group', $requirements ) && is_array($tmp = $requirements['group']) ) {
					foreach( $tmp as $k=>$v ) {
						$tmp[$k] = $db->Quote($v);
					}
					$whereStrs[] = $db->nameQuote('r').'.'.$db->nameQuote('group').' IN ('.implode(', ', $tmp).')';
				}
				
				if( array_key_exists( 'pupil', $requirements ) && is_array($tmp = $requirements['pupil']) ) {
					foreach( $tmp as $k=>$v ) {
						$tmp[$k] = $db->Quote($v);
					}
					$whereStrs[] = $db->nameQuote('r').'.'.$db->nameQuote('student').' IN ('.implode(', ', $tmp).')';
				}
			}
			else {
				if( array_key_exists( 'tutor', $requirements ) && is_array($tmp = $requirements['tutor']) ) {
					foreach( $tmp as $k=>$v ) {
						$tmp[$k] = $db->Quote($v);
					}
					$joinStrs['tutor'] = 'INNER JOIN #__apoth_tt_group_members AS gm'
						."\n".'    ON gm.person_id = r.student'
						."\n".' INNER JOIN #__apoth_cm_courses AS t'
						."\n".'    ON t.id = gm.group_id'
						."\n".'   AND t.type = "pastoral"'
						."\n".'   AND t.deleted = "0"';
					$whereStrs[] = $db->nameQuote('t').'.'.$db->nameQuote('id').' IN ('.implode(', ', $tmp).')';
				}
				if( array_key_exists( 'member', $requirements ) && is_array($tmp = $requirements['member']) ) {
					foreach( $tmp as $k=>$v ) {
						$tmp[$k] = $db->Quote($v);
					}
					$whereStrs[] = $db->nameQuote('r').'.'.$db->nameQuote('student').' IN ('.implode(', ', $tmp).')';
				}
				if( array_key_exists( 'course2', $requirements ) && is_array($tmp = $requirements['course2']) ) {
					foreach( $tmp as $k=>$v ) {
						$tmp[$k] = $db->Quote($v);
					}
					$joinStrs['course'] = 'INNER JOIN #__apoth_cm_courses AS c'
						."\n".' ON c.id = r.group';
					$whereStrs[] = $db->nameQuote('c').'.'.$db->nameQuote('parent').' IN ('.implode(', ', $tmp).')';
					$whereStrs[] = $db->nameQuote('c').'.'.$db->nameQuote('deleted').' = '.$db->Quote('0');
				}
			}
			
			if( array_key_exists( 'cycle', $requirements ) && is_array($tmp = $requirements['cycle']) ) {
				foreach( $tmp as $k=>$v ) {
					$tmp[$k] = $db->Quote($v);
				}
				$whereStrs[] = $db->nameQuote('cycle').' IN ('.implode(', ', $tmp).')';
			}
    	
			if( array_key_exists( 'reportId', $requirements )) {
				$id = $db->Quote($requirements['reportId']);
				$whereStrs[] = $db->nameQuote('r').'.'.$db->nameQuote('id').' = '.$id;
			}
			
			$query = 'SELECT r.id'
				."\n".' FROM #__apoth_rpt_reports AS r'
				.(isset($joinStrs) ? "\n".implode("\n".' ', $joinStrs) : '')
				.(isset($whereStrs) ? "\n".' WHERE '.implode("\n".' AND ', $whereStrs) : '');
			$db->setQuery($query);
			$results = $db->loadResultArray();
			
			foreach( $results as $k=>$v ) {
				$this->_reports[$v] = &ApothReport::getInstance( $v, $dataOnly );
			}
		}
	}
	
	/**
	 * Retrieves the report with the id given, or if none given / specified report doesn't exist, the current report object
	 */
	function &getReport( $id = false )
	{
		if( ($id !== false) && array_key_exists($id, $this->_reports) ) {
			return $this->_reports[$id];
		}
		elseif( !empty($this->_report) ) {
			return $this->_report;
		}
		else {
			$f = false;
			return $f;
		}
	}
	
	/**
	 * Retrieves the complete list of current report objects
	 */
	function &getReports()
	{
		if( !empty($this->_reports) ) {
			return $this->_reports;
		}
		else {
			$r = array();
			return $r;
		}
	}
	
	/**
	 * Retrieves the blank reports initialised for the given cycle, group, student combination
	 * All params are required, but I hope to build in some more cunning searching to allow
	 * retrieval by any combination of single, multiple, or no values in these params
	 */
	function getBlankReports( $cycle, $group, $student )
	{
		return $this->_blankReports[$cycle][$group][$student];
	}
	
	/**
	 * Removes a report from the internal list of reports
	 * Optionally calls the report's "delete" method to remove it from the database
	 *
	 * @param $id mixed  The id of the report to remove
	 * @param $complete boolean  Do we want to remove the report from the database too
	 * @return boolean  True on success, false on failure
	 */
	function delete( $id, $complete = false )
	{
		$ok = false;
		if( array_key_exists($id, $this->_reports) ) {
			$ok = true;
			if( $complete ) {
				$ok = $this->_reports[$id]->delete();
			}
			unset( $this->_reports[$id] );
		}
		return $ok;
	}
	
	/**
	 * Sorts the current list of report objects by tutor, pupil, and subject
	 * according to a pre-defined set of comparison rules
	 * *** which I want to make user-definable when I have time
	 */
	function sortReports()
	{
		uasort($this->_reports, array($this, '_sortReports'));
	}
	
	/**
	 * Do the comparisons used for the uasort()
	 */
	function _sortReports($a, $b)
	{
		$_a = $a->getTutorGroupName();
		$_b = $b->getTutorGroupName();
		if( $_a != $_b ) {
			return (($_a > $_b) ? 1 : -1 );
		}
		else {
			$_a = $a->getStudentSurname();
			$_b = $b->getStudentSurname();
			if( $_a != $_b ) {
				return (($_a > $_b) ? 1 : -1 );
			}
			else {
				$_a = $a->getStudentFirstname();
				$_b = $b->getStudentFirstname();
				if( $_a != $_b ) {
					return (($_a > $_b) ? 1 : -1 );
				}
				else {
					$_a = $a->getSubjectOrder();
					$_b = $b->getSubjectOrder();
					if( $_a != $_b ) {
						return (($_a > $_b) ? 1 : -1 );
					}
					else {
						$_a = $a->getSubjectName();
						$_b = $b->getSubjectName();
						
						if( $_a != $_b ) {
							return (($_a > $_b) ? 1 : -1 );
						}
						else {
							return 0;
						}
					}
				}
			}
		}
	}
	
	/**
	 * Retrieve the incFiles array
	 */
	function getIncFiles()
	{
		$incFiles = array();
		if( is_array($this->_reports) ) {
			foreach( $this->_reports as $rep ) {
				$incFiles[] = $rep->getFile();
			}
		}
		if( isset($this->_report) && is_object($this->_report) ) {
			$incFiles[] = $this->_report->getFile();
		}
		return $incFiles;
	}
}
?>