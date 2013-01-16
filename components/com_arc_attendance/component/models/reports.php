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

require_once( JPATH_COMPONENT.DS.'models'.DS.'ereg.php' );

/**
 * Attendance Manager Reporting Model
 *
 * @author		lightinthedark <code@lightinthedark.org.uk>
 * @package		Apotheosis
 * @subpackage	Attendance
 * @since 0.1
 */
class AttendanceModelReports extends AttendanceModel
{
	// #####  Mark sheet creation  #####
	
	/**
	 * Creates an instance of the attendance reports model
	 */
	function __construct()
	{
		parent::__construct();
		$this->_requirements = array();
		$this->_markSheets = array();
		$this->_edits = array();
		$this->_editsOn = false;
		$this->_breadcrumbs = array();
	}
	
	/**
	 * Check to see if we've already got a mark sheet for these requirements
	 * If we do, use it, otherwise create a new one and cache it
	 * 
	 * @param $requirements array  Associative array of requirements (property=>value)
	 * @param $method string  "new" or "clone" depending on the method to use to create the new sheet
	 * @param $sheetId int  Optional sheet id (probably to clone from)
	 * @return mixed  the current marksheet ID
	 */
	function _addMarkSheet( $requirements, $method, $sheetId = null )
	{
		$start_date = ( array_key_exists('start_date', $requirements) ? $requirements['start_date'] : date('Y-m-d H:i:s') );
		$end_date   = ( array_key_exists('end_date',   $requirements) ? $requirements['end_date']   : date('Y-m-d H:i:s') );
		
		// grab then clear the toggle requirement as it is not really a search term as such
		if( isset($requirements['toggle']) ) {
			$toggles = $requirements['toggle'];
			unset( $requirements['toggle'] );
		}
		
		$curSheetId = array_search( $requirements, $this->_requirements );
		if( $curSheetId === false ) {
			$curSheetId = count($this->_markSheets);
			$this->_requirements[$curSheetId] = $requirements;
			
			switch( $method ) {
			case( 'new' ):
				$type = ( (isset($requirements['person_id']) && (count($requirements['person_id']) == 1)) ? 'summary' : 'full' );
				$this->_markSheets[$curSheetId] = &MarkSheet::getNew( $requirements, $type );
				break;
			
			case( 'clone' ):
				$markSheetToClone = &$this->_markSheets[$sheetId];
				$type = ( $markSheetToClone->getRowIsPupil($requirements['scope']) ? 'summary' : 'full' );
				$this->_markSheets[$curSheetId] = &MarkSheet::getClone( $markSheetToClone, $requirements['scope'], $type );
				break;
			}
			$this->_markSheets[$curSheetId]->setAggregateType();
		}
		if( isset($toggles) ) {
			foreach( $this->_markSheets[$curSheetId]->getGroupings() as $toggle=>$v ) {
				$this->_markSheets[$curSheetId]->setGroupings( $toggle, (array_search($toggle, $toggles) !== false) );
			}
		}
		
		return $curSheetId;
	}
	
	/**
	 * Clears all mark sheets from this model and resets related properties
	 */
	function _clearMarkSheets()
	{
		$this->_requirements = array();
		$this->_markSheets = array();
	}
	
	/**
	 * Create a mark sheet which matches the given requirements
	 * This becomes the only sheet in the model
	 * @param $requirements array  Associative array of requirements (property=>value)
	 */
	function setMarkSheet( $requirements )
	{
		// Wipe out the old mark sheets, breadcrumb trail and expanded searches
		$this->_clearMarkSheets();
		$this->_clearBreadcrumbs();
		$this->_clearExpandSearches();
		unset( $this->marksExpanded );
		
		// then add new mark sheet
		$this->_addMarkSheet( $requirements, 'new' );
	}
	
	/**
	 * Create a new marksheet which is an expanded version of the current one
	 * @param $expand str  The type of expansion we are performing
	 */
	function expandMarkSheet( $expand )
	{
		$reqSets = array();
		
		switch( $expand ) {
		case( 'expand_dates' ):
			$requirements = $this->getExpandedDates();
			$r = $this->_getNonScopeRequirements();
			$markSheetIds = $this->getMarksheetIds();
			
			foreach( $markSheetIds as $sheetId ) {
				$requirements['person_id'] = $this->getPupilList( $sheetId );
				$requirements = array_merge( $r, $requirements );
				$reqSets[] = $requirements;
			}
			$label = 'All dates';
			break;
			
		case( 'expand_criteria' ):
			$r = $this->_getNonScopeRequirements();
			$markSheetIds = $this->getMarksheetIds();
			
			foreach( $markSheetIds as $sheetId ) {
				$requirements['person_id'] = $this->getPupilList( $sheetId );
				$requirements = array_merge($r, $requirements );
				unset( $requirements['subject'] );
				unset( $requirements['course_id'] );
				unset( $requirements['tutor_grp'] );
				unset( $requirements['period_type'] );
				unset( $requirements['att_code'] );
				unset( $requirements['teacher'] );
				unset( $requirements['truant_id'] );
				unset( $requirements['day_section'] );
				unset( $requirements['academic_year'] );
				$reqSets[] = $requirements;
			}
			$label = 'Broad search';
			break;
			
		case( 'expand_all' ):
			$requirements = $this->getExpandedDates();
			$r = $this->_getNonScopeRequirements();
			$markSheetIds = $this->getMarksheetIds();
			
			foreach( $markSheetIds as $sheetId ) {
				$requirements['person_id'] = $this->getPupilList( $sheetId );
				$requirements['period_type'] = 1; // statutory periods only
				$requirements = array_merge($r, $requirements );
				unset( $requirements['subject'] );
				unset( $requirements['course_id'] );
				unset( $requirements['tutor_grp'] );
				unset( $requirements['att_code'] );
				unset( $requirements['teacher'] );
				unset( $requirements['truant_id'] );
				unset( $requirements['day_section'] );
				unset( $requirements['academic_year'] );
				$reqSets[] = $requirements;
			}
			$label = 'Complete search';
			break;
		
		case( 'expand_marks' ):
			$r = $this->_getNonScopeRequirements();
			$markSheetIds = $this->getMarksheetIds();
			
			foreach( $markSheetIds as $sheetId ) {
				$requirements['person_id'] = $this->getPupilList();
				$requirements['toggle'] = array( 0=>'pupil' );
				$requirements = array_merge( $r, $requirements );
				unset( $requirements['subject'] );
				unset( $requirements['course_id'] );
				unset( $requirements['tutor_grp'] );
				unset( $requirements['period_type'] );
				unset( $requirements['att_code'] );
				unset( $requirements['teacher'] );
				unset( $requirements['truant_id'] );
				unset( $requirements['day_section'] );
				unset( $requirements['academic_year'] );
				$reqSets[] = $requirements;
			}
			$label = 'All marks';
			$this->marksExpanded = $this->getCurBreadcrumb();
			break;
			
		case( 'show_summaries' ):
			$r = $this->_getNonScopeRequirements();
			$pupilList = $r['person_id'];
			unset( $r['person_id'] );
			
			// loop through and set requirements for each individual
			// with otherwise the same search criteria
			foreach( $pupilList as $pupilId ) {
				$requirements = $r;
				$requirements['person_id'][] = $pupilId;
				$reqSets[] = $requirements;
			}
			$label = 'As summaries';
			break;
		}
		
		$sheets = array();
		foreach( $reqSets as $requirements ) {
			// add new marksheet
			$newSheetId = $this->_addMarkSheet( $requirements, 'new' );
			$sheets[] = $newSheetId;
		}
		
		// add a new breadcrumb if appropriate and reset expand searches
		if( $this->addBreadcrumb($label, $sheets) ) {
			$this->_clearExpandSearches();
		}
	}
	
	/**
	 * Creates a mark sheet which is a clone of part of the current mark sheet
	 * Adds to the breadcrumb trail so we can trace back our steps
	 * @param $sheetIdToClone int  The id of the mark sheet to clone
	 * @param $scope int  The id of the row to use as our new root item
	 */
	function drillDown( $sheetIdToClone, $scope )
	{
		// add cloned marksheet
		$clonedSheetId = $this->_addMarkSheet( array('scope'=>$scope), 'clone', $sheetIdToClone );
		
		$labelName = '';
		if( count($this->getMarkSheetIds()) > 1 ) {
			$labelName = ' ('.ApotheosisData::_( 'people.displayname', reset($this->getPupilList($clonedSheetId)) ).')';
		}
		
		// fetch breadcrumb label from existing mark sheet
		$label = $this->_markSheets[$sheetIdToClone]->getRowText( $scope ).$labelName;
		
		// add a new breadcrumb and reset expand searches
		$this->addBreadcrumb( $label, array($clonedSheetId) );
		$this->_clearExpandSearches();
	}
	
	/**
	 * Sets a pre-existing breadcrumb as the current one
	 * Removes breadcrumbs pointing to breadcrumbs below the newly current one
	 * @param $breadcrumbId int  The id of the breadcrumb to set as current
	 */
	function drillUp( $breadcrumbId )
	{
		if( isset($this->_breadcrumbs[$breadcrumbId]) ) {
			// remove breadcrumbs below this and clear expand searches
			$this->_deleteBreadcrumbs( $breadcrumbId );
			$this->_clearExpandSearches();
			if( isset($this->marksExpanded) && ($this->marksExpanded >= $breadcrumbId) ) {
				unset( $this->marksExpanded );
			}
		}
	}
	
	/**
	 * Get the most recently stored requirements that aren't a scope
	 * @return array  most recently stored requirements that aren't a scope
	 */
	function _getNonScopeRequirements()
	{
		$found = false;
		end( $this->_breadcrumbs );
		while( !$found && !is_null(($r = &$this->_requirements[$this->_breadcrumbs[key($this->_breadcrumbs)]['sheets'][0]])) ) {
			$found = ( (count($r) > 1) || !isset($r['scope']) );
			prev( $this->_breadcrumbs );
		}
		
		return $r;
	}
	
	/**
	 * Determine if current date range could be expanded at all by 'Expand dates'
	 * @return bool  true is yes, false if not
	 */
	function getExpandDates()
	{
		if( !isset($this->expandDates) ) {
			$lastSearch = $this->_getNonScopeRequirements();
			$startDate = $endDate = false;
			
			if( isset($lastSearch['start_date']) && ($lastSearch['start_date'] > ApotheosisLib::getEarlyDate()) ) {
				$startDate = true;
			}
			
			if( isset($lastSearch['end_date']) && ($lastSearch['end_date'] < date('Y-m-d')) ) {
				$endDate = true;
			}
			
			$this->expandDates = ( $startDate || $endDate );
		}
		
		return $this->expandDates;
	}
	
	/**
	 * Get expanded date search range
	 * @return array  earliest start_date and latest end_date
	 */
	function getExpandedDates()
	{
		$lastSearch = $this->_getNonScopeRequirements();
		$retVal = array();
		
		if( isset($lastSearch['start_date']) ) {
			$retVal['start_date'] = min( $lastSearch['start_date'], ApotheosisLib::getEarlyDate() );
		}
		
		if( isset($lastSearch['end_date']) ) {
			$retVal['end_date'] = max( $lastSearch['end_date'], date('Y-m-d') );
		}
		
		return $retVal;
	}
	
	/**
	 * Determine if current search criteria could be less restricted by 'Expand search'
	 * @return bool  true is yes, false if not
	 */
	function getExpandSearch()
	{
		if( !isset($this->expandSearch) ) {
			$lastSearch = $this->_getNonScopeRequirements();
			$startDate = $endDate = $personId = false;
			
			foreach( $lastSearch as $req=>$v ) {
				if( $req == 'start_date' ) {
					$startDate = true;
				}
				elseif( $req == 'end_date' ) {
					$endDate = true;
				}
				elseif( $req == 'person_id' ) {
					$personId = true;
				}
			}
			
			$this->expandSearch = !( (count($lastSearch) == 3) && $startDate && $endDate && $personId );
		}
		
		return $this->expandSearch;
	}
	
	/**
	 * Determine if current search criteria could be less restricted by 'Expand all'
	 * to fulfil the requirements for providing a compact pdf
	 * 
	 * @return bool  true is yes, false if not
	 */
	function getExpandCompact()
	{
		if( !isset($this->expandCompact) ) {
			$lastSearch = $this->_getNonScopeRequirements();
			$startDate = $endDate = $personId = false;
			
			foreach( $lastSearch as $req=>$v ) {
				if( ($req == 'start_date') && ($lastSearch['start_date'] <= ApotheosisLib::getEarlyDate()) ) {
					$startDate = true;
				}
				elseif( ($req == 'end_date') && ($lastSearch['end_date'] >= date('Y-m-d')) ) {
					$endDate = true;
				}
				elseif( $req == 'person_id' ) {
					$personId = true;
				}
				elseif( ($req == 'period_type') && ($lastSearch['period_type'] == 1) ) {
					$statutory = true;
				}
			}
			
			$this->expandCompact = !( (count($lastSearch) == 4) && $startDate && $endDate && $personId && $statutory );
		}
		
		return $this->expandCompact;
	}
	
	/**
	 * Determine if showing all marks for the date range regardless of previous searches
	 * would return too big a data set
	 * @return bool  true is yes, false if not
	 */
	function getExpandMarks()
	{
		if( !isset($this->expandMarks) ) {
			$this->expandMarks = !isset($this->marksExpanded) && ( count($this->getPupilList()) <= 300 );
		}
		
		return $this->expandMarks;
	}
	
	/**
	 * Clears all records of expanded search status
	 */
	function _clearExpandSearches()
	{
		unset( $this->expandDates );
		unset( $this->expandSearch );
		unset( $this->expandCompact );
		unset( $this->expandMarks );
	}
	
	// #####  Bread crumb handling  #####
	
	/**
	 * Retrieves the breadcrumb array
	 * @return array  Array of sheetId=>text pairs
	 */
	function getBreadcrumbs()
	{
		return $this->_breadcrumbs;
	}
	
	/**
	 * Resets the breadcrumbs array to only have the current sheet's id labeled as original search 
	 */
	function _clearBreadcrumbs()
	{
		$this->_breadcrumbs = array();
		$this->_breadcrumbs[] = array( 'label'=>'Original Search','sheets'=>array(0) );
	}
	
	/**
	 * Adds an entry to the end of the breadcrumb trail
	 * @param string $str  The text to display for this breadcrumb
	 * @param array $sheets  The array of id's of the sheets to associate with this breadcrumb
	 * @return boolean $new  Is this a unique breadcrumb?
	 */
	function addBreadcrumb( $str, $sheets )
	{
		$new = true;
		foreach( $this->_breadcrumbs as $breadcrumb ) {
			if( $breadcrumb['sheets'] == $sheets ) {
				$new = false;
				break;
			}
		}
		
		if( $new ) {
			$this->_breadcrumbs[] = array( 'label'=>$str, 'sheets'=>$sheets );
		}
		
		return $new;
	}
	
	/**
	 * Removes all breadcrumbs up to (but not including) the given sheet id
	 * @param $newFinalSheetId  The id of the sheet which should be the final crumb in the trail
	 */
	function _deleteBreadcrumbs( $newFinalSheetId )
	{
		end( $this->_breadcrumbs );
		while( !empty($this->_breadcrumbs) && (key($this->_breadcrumbs) != $newFinalSheetId) ) {
			array_pop($this->_breadcrumbs);
			end($this->_breadcrumbs);
		}
	}
	
	/**
	 * Get the current (last) breadcrumb id
	 * @return int  Current breadcrumb id
	 */
	function getCurBreadcrumb()
	{
		end($this->_breadcrumbs);
		
		return key( $this->_breadcrumbs );
	}
	
	/**
	 * Return either the numbered marksheet or the first for the current breadcrumb
	 * 
	 * @param int $sheetId  Optional id of the specific marksheet we want
	 * @return obj|null  The current marksheet or null if none exist yet
	 */
	function &getMarkSheet( $sheetId = null )
	{
		$sheetId = ( is_null( $sheetId ) ? reset($this->getMarkSheetIds()) : $sheetId );
		$markSheet = &$this->_markSheets[$sheetId];
		$retVal = ( is_object($markSheet) ? $markSheet : null );
		
		return $retVal;
	}
	
	/**
	 * Fetch the list of marksheets associated with a breadcrumb
	 * @param int $breadcrumbId  optional id of the breadcrumb whose associated mark sheet id's we want, otherwise current breadcrumb
	 * @return array $markSheetIds array of mark sheet ids for ths given breadcrumb or empty array
	 */
	function getMarkSheetIds( $breadcrumbId = null )
	{
		$breadcrumbId = ( is_null($breadcrumbId) ? $this->getCurBreadcrumb() : $breadcrumbId );
		$markSheetIds = array();
		
		if( isset($this->_breadcrumbs[$breadcrumbId]['sheets']) && is_array($this->_breadcrumbs[$breadcrumbId]['sheets']) ) {
			$markSheetIds = $this->_breadcrumbs[$breadcrumbId]['sheets'];
		}
		
		return $markSheetIds;
	}
	
	// #####  Sheet info and controls  #####
	
	/**
	 * Gives the type of the current marksheet
	 * 
	 * @return string  Either "full" or "summary"
	 */
	function getMarkSheetType()
	{
		$markSheet = &$this->getMarkSheet();
		
		if( is_object($markSheet) ) {
			return $markSheet->getType();
		}
	}
	
	/**
	 * Retrieves the list of aggregate row types
	 * 
	 * @return array  Indexed array of row types (id=>textual representation)
	 */
	function getAggregateTypes()
	{
		$tmpMarkSheet = &$this->getMarkSheet( 0 );
		
		return $tmpMarkSheet->getAggregateTypes();
	}
	
	/**
	 * Sets the aggregate row type
	 * 
	 * @param $sheetId int  ID of the marksheet whose aggregate type we wish to change
	 * @param $a int  Optional param to specify the aggregate row type. If omitted the first option is used.
	 */
	function setAggregateType( $sheetId, $a = null )
	{
		$markSheet = &$this->getMarkSheet( $sheetId );
		
		if( is_object($markSheet) ) {
			$markSheet->setAggregateType( $a );
		}
	}
	
	/**
	 * Retrieves the current aggregate row type
	 * 
	 * @param $sheetId int  ID of the marksheet whose aggregate type why wish to retrieve
	 * @return array
	 */
	function getAggregateType( $sheetId )
	{
		$markSheet = &$this->getMarkSheet( $sheetId );
		
		if( is_object($markSheet) ) {
			return $markSheet->getAggregateType();
		}
	}
	
	/**
	 * Sets the filter to use when displaying marks and totals
	 * 
	 * @param $sheetId int  ID of the marksheet whose mark filter type we wish to change
	 * @param $f string  The code by which to filter
	 */
	function setFilter( $sheetId, $f )
	{
		$markSheet = &$this->getMarkSheet( $sheetId );
		
		if( is_object($markSheet) ) {
			$markSheet->setFilter( $f );
		}
	}
	
	/**
	 * Retrieve the filter mark
	 * 
	 * @param $sheetId int  ID of the marksheet whose mark filter type why wish to retrieve
	 * @return string  The mark by which we are filtering
	 */
	function getFilter( $sheetId )
	{
		$markSheet = &$this->getMarkSheet( $sheetId );
		
		if( is_object($markSheet) ) {
			return $markSheet->getFilter();
		}
	}
	
	/**
	 * Switch toggle $t to either !currentVal or $val if given
	 * 
	 * @param $sheetId int  The marksheet id on which to make changes
	 * @param $t string  The toggle to be switched
	 * @param $val boolean|null|-1  The value to set the toggle to. true = on, false = off, null = disabled, -1 to invert
	 * @return boolean  true on success, false on failure
	 */
	function setToggle( $sheetId, $t, $val = -1 )
	{
		$markSheet = &$this->getMarkSheet( $sheetId );
		if( is_object($markSheet) ) {
			return $markSheet->setGroupings( $t, $val );
		}
	}
	
	/**
	 * Gives an array of toggles and states
	 * 
	 * @param $sheetId int  The marksheet id whose toggles we want
	 * @return array  id=>state
	 */
	function getToggles( $sheetId )
	{
		$markSheet = &$this->getMarkSheet( $sheetId );
		
		if( is_object($markSheet) ) {
			return $markSheet->getGroupings();
		}
	}
	
	/**
	 * Get an array of objects representing the day sections used in this mark sheet
	 * 
	 * @param $sheetId int  The marksheet id whose day sections we want
	 */
	function getDaySections( $sheetId )
	{
		$obj = new stdClass();
		$obj->id = '';
		$obj->displayname = '';
		$retVal = array( $obj );
		
		$markSheet = &$this->getMarkSheet( $sheetId );
		if( is_object($markSheet) ) {
			$raw = $markSheet->getDaySections();
			sort( $raw, SORT_NUMERIC );
			foreach( $raw as $ds ) {
				$obj = new stdClass();
				$obj->id = $ds;
				$obj->displayname = $ds;
				$retVal[] = $obj;
			}
		}
		
		return $retVal;
	}
	
	// #####  Heading col/rows handling  #####
	
	/**
	 * How many rows of headings will we be showing?
	 * 
	 * @param $sheetId int  The marksheet id whose head row count we want
	 * @return int  The count of heading rows
	 */
	function getHeadRowCount( $sheetId )
	{
		$markSheet = &$this->getMarkSheet( $sheetId );
		if( is_object($markSheet) ) {
			return $markSheet->getHeadRowCount();
		}
	}
	
	/**
	 * Gets the next (or first) heading row following the active elements down
	 * 
	 * @param $sheetId int  The marksheet id whose head row we want
	 * @return array  The heading row's id=>[current(bool), title]
	 */
	function getHeadRow( $sheetId )
	{
		$markSheet = &$this->getMarkSheet( $sheetId );
		
		static $rowLevel = 0;
		static $rows = null;
		static $sheetIdCache = -1;
		if( is_null($rows) || ($sheetIdCache != $sheetId) ) {
			$sheetIdCache = $sheetId;
			if( is_object($markSheet) ) {
				$rows = $markSheet->getHeadRows();
			}
		}
		
		if( ($rows !== false) && is_object($markSheet) ) {
			$this->_curHeadRow = $rows;
			$retVal = array();
			foreach( $rows as $id=>$info ) {
				$r['id'] = $id;
				$r['colid'] = $info['_colid'];
				$r['text'] = $info['_text'];
				$r['text_full'] = $info['_text_full'];
				$r['active'] = $info['_active'];
				$r['enabled'] = $info['_enabled'];
				$r['row_label'] = $markSheet->getHeadRowLabel( $rowLevel );
				$retVal[] = $r;
			}
			$rowLevel++;
			
			$found = false;
			foreach( $rows as $id=>$info ) {
				if( $info['_active'] ) {
					$rows = $info['_children'];
					$found = true;
					break;
				}
			}
			if( !$found ) {
				$rows = false;
			}
		}
		else {
			$retVal = false;
		}
		
		return $retVal;
	}
	
	/**
	 * Generates and returns a 2d array of head row information arrays
	 * containing all the head rows (active or not) with column counts of their total children
	 */
	function getHeadRowGrid( $sheetId, $curRow = null, $level = 0 )
	{
		$markSheet = &$this->getMarkSheet( $sheetId );
		if( is_null($curRow) && !is_null($markSheet) ) {
			$curRow = $markSheet->getHeadRows();
		}
		$nextLevel = $level + 1;
		
		foreach( $curRow as $id=>$col ) {
			$entry = array();
			$entry['id'] = $id;
			$entry['colid'] = $col['_colid'];
			$entry['text'] = $col['_text'];
			$entry['text_full'] = $col['_text_full'];
			$entry['active'] = $col['_active'];
			$entry['enabled'] = $col['_enabled'];
			$entry['row_label'] = $markSheet->getHeadRowLabel( $level );
			$entry['decendants'] = $col['_leafCount'];
			$retVal[$level][] = $entry;
			if( !empty($col['_children']) ) {
				$tmp = $this->getHeadRowGrid( $sheetId, $col['_children'], $nextLevel );
				foreach( $tmp as $l=>$v ) {
					if( !is_array($retVal[$l]) ) {
						$retVal[$l] = array();
					}
					$retVal[$l] = array_merge( $retVal[$l], $v );
				}
			}
		}
		
		if( $level == 0 ) {
			$nice = end( $retVal );
			foreach( $nice as $id=>$col ) {
				$this->_curHeadRow[$id] = array( '_text'=>$col['text'], '_colid'=>$col['colid'], '_enabled'=>true );
			}
		}
		return( $retVal );
	}
	
	/**
	 * 
	 */
	function setHeadActive( $sheetId, $lvlId )
	{
		$markSheet = &$this->getMarkSheet( $sheetId );
		if( is_object($markSheet) ) {
			list($lvl, $id) = explode( '_', $lvlId );
			$markSheet->setHeadActive( $lvl, $id );
		}
	}
	
	
	// #####  Data handling  #####
	
	/**
	 * Gets the number of mark rows in the current marksheet
	 * 
	 * @return null|int  false if no current marksheet or the number of mark rows
	 */
	function getMarkRowCount()
	{
		$markSheet = &$this->getMarkSheet();
		
		return ( is_object($markSheet) ? $markSheet->getMarkRowCount() : null );
	}
	
	/**
	 * Gets the next row of mark data
	 * including its title, indent level and data for the current headings.
	 * Must pay attention to our toggles and the row types
	 * 
	 * @param $sheetId int  The marksheet id on whose data we want
	 * @return $curRow array  Data for the current mark row
	 */
	function getMarkRow( $sheetId )
	{
		$markSheet = &$this->getMarkSheet( $sheetId );
		
		static $rows = null
			, $branches = array()
			, $sheetIdCache = -1
			, $newBranch = false
			, $die = false;
		
		if( is_object($markSheet) && (is_null($rows) || ($sheetIdCache != $sheetId)) ) {
			$sheetIdCache = $sheetId;
			$rows = $markSheet->getRows();
			
			// Set up the first level of this 
			$branches = array( &$rows );
			$newBranch = true;
			$this->onToggles = array_keys( $this->getToggles( $sheetId ), true );
		}
		
		// going down a level (new branch, first node)
		if( $newBranch ) {
			$newBranch = false;
			end($branches);
			unset($this->_curBranch);
			$this->_curBranch = $branches[key($branches)];
			$curNode = reset($this->_curBranch);
		}
		// going through same level
		else {
			$curNode = next($this->_curBranch);
			
			// ... but if we've run out we need to go up levels until we find the next node if we can
			if( $die ) { ob_start(); }
			while( ($curNode === false) && !empty($branches) ) {
				unset($this->_curBranch);
				array_pop( $branches );
				if( end($branches) !== false ) {
					$this->_curBranch = &$branches[key($branches)];
					$curNode = next( $this->_curBranch );
				}
			}
		}
		$depth = count($branches);
		
		// if there are child branches to explore add them to the branch queue
		if( !empty($curNode['_children']) ) {
			$t = reset( $curNode['_children'] );
			$branches[] = &$curNode['_children'];
			$newBranch = true;
		}
		
		if( is_null($curNode['_rows']) ) {
			return false;
		}
		
		// with all the tree navigation taken care of, let's get the info and marks for this row
		$curRow = array( 'id'=>reset($curNode['_rows'])//key($this->_curBranch)
			, 'name' =>$curNode['_text']
			, 'multi'=>$curNode['_multi']
			, 'depth'=>$depth );
		$curRow['edits'] = ( isset($this->_edits[$sheetId][$curRow['id']]) ? $this->_edits[$sheetId][$curRow['id']] : false );
		switch( $this->onToggles[$depth-1] ) {
		case( 'group' ):
			$tmp = ApotheosisData::_( 'timetable.teachers', key($this->_curBranch) );
			foreach( $tmp as $k=>$v ) {
				$tmp[$k] = ApotheosisData::_( 'people.displayname', $v, 'teacher' );
			}
			$curRow['info'] = implode( ', ', $tmp );
			break;
		
		case( 'pupil' ):
			$curRow['info'] = ApotheosisData::_( 'course.name', ApotheosisData::_( 'timetable.tutorgroup', key($this->_curBranch) ) );
			break;
		}
		
		if( !isset($this->_curHeadRow) ) {
			$this->getHeadRow( $sheetId );
		}
		$heads = ( is_array($this->_curHeadRow) ? $this->_curHeadRow : array() );
		$heads[] = array('_colid'=>'summary');
		$isMulti = false;
		if( is_object($markSheet) ) {
			foreach( $heads as $info ) {
				$colId = $info['_colid'];
				$types = $markSheet->getMarkTypes( $curNode['_rows'], array($colId) );
				if( isset($types['m']) ) {
					$aggType = $markSheet->getAggregateType();
					$filter = $markSheet->getFilter();
					if( ($aggType == 0) || ($aggType == 2) ) {
						list( $c, $t ) = $markSheet->getMarkCount( $curNode['_rows'], array($colId), $filter );
						$curRow[$colId]['count'] = $c;
						$curRow[$colId]['total'] = $t;
					}
					if( ($aggType == 1) || ($aggType == 2) ) {
						$curRow[$colId]['percent'] = $markSheet->getMarkPercent( $curNode['_rows'], array($colId), $filter );
					}
				}
				if( isset($types['s']) ) {
					$curRow[$colId]['marks'] = $markSheet->getMarks( $curNode['_rows'], array($colId) );
				}
				if( isset($types['sum']) ) {
					list( $m, $t ) = $markSheet->getMarkSummary( $curNode['_rows'] );
					$curRow['summary']['markList'] = $m;
					$curRow['summary']['total'] = $t;
				}
			}
		}
		
		return $curRow;
	}
	
	/**
	 * Simply passes the instruction through to the current mark sheet and resets all aggregates / summaries
	 * 
	 * @param $sheetId int  Marksheet id to set marks in
	 * @return array  2-element array 'good' and 'bad' with array of row ids that had no or some errors respectively when saving
	 */
	function setMarks( $sheetId, $marks )
	{
		// save marks...
		$markSheet = &$this->getMarkSheet( $sheetId );
		if( is_object($markSheet) ) {
			$retVal = $markSheet->setMarks( $marks );
		}
		
		// ...then loop though ALL marksheets and reset aggregates
		foreach( $this->_markSheets as $markSheet ) {
			$markSheet->resetAggregateRows();
		}
		
		return $retVal;
	}
	
	/**
	 * Simply passes the instruction through to the current mark sheet and resets all aggregates / summaries
	 * 
	 * @param $sheetId int  Marksheet id to replace marks in
	 * @return int  The number of replace marks
	 */
	function replaceMarks( $sheetId, $find, $replace, $rowColTuples, $time, $from, $to )
	{
		// save marks...
		$markSheet = &$this->getMarkSheet( $sheetId );
		if( is_object($markSheet) ) {
			$retVal = $markSheet->replaceMarks( $find, $replace, $rowColTuples, $time, $from, $to );
		}
		
		// ...then loop though ALL marksheets and reset aggregates
		foreach( $this->_markSheets as $markSheet ) {
			$markSheet->resetAggregateRows();
		}
		
		return $retVal;
	}
	
	/**
	 * Retrieves a list of all the pupils
	 * for which we have attendance marks to show in the current mark sheet
	 * 
	 * @param $sheetId int  Optional marksheet id
	 * @return $pupilList array  Array of pupils in the current marksheet
	 */
	function getPupilList( $sheetId = null )
	{
		$markSheet = &$this->getMarkSheet( $sheetId );
		$pupilList = array();
		if( is_object($markSheet) ) {
			return $markSheet->getPeople();
		}
	}
	
	/**
	 * Get an array of statistical meanings for the first (only) pupil
	 * 
	 * @param $sheetId int  Marksheet id to get stats from
	 * @return array  code=>[session count, %]
	 */
	function getPupilStats( $sheetId )
	{
		$markSheet = &$this->getMarkSheet( $sheetId );
		if( is_object($markSheet) ) {
			$rawMarks = $markSheet->getMarkSummaryGChart();
		}
		$groupCodes = array();
		foreach( $rawMarks as $mark ) {
			$groupCodes[$mark['group']] = $mark['group'];
		}
		
		$db = &JFactory::getDBO();
		$groupQuery = 'SELECT '.$db->nameQuote('cm').'.'.$db->nameQuote('id').', '.$db->nameQuote('cp').'.'.$db->nameQuote('fullname')
			."\n".'FROM '.$db->nameQuote('#__apoth_cm_courses').' as '.$db->nameQuote('cm')
			."\n".'INNER JOIN '.$db->nameQuote('#__apoth_cm_courses').' as '.$db->nameQuote('cp')
			."\n".'   ON '.$db->nameQuote('cp').'.'.$db->nameQuote('id').' = '.$db->nameQuote('cm').'.'.$db->nameQuote('parent')
			."\n".'  AND '.$db->nameQuote('cp').'.'.$db->nameQuote('deleted').' = '.$db->Quote('0')
			."\n".'~LIMITINGJOIN~'
			."\n".'WHERE '.$db->nameQuote('cm').'.'.$db->nameQuote('id').' IN ('.implode( ', ', $groupCodes ).')'
			."\n".'  AND '.$db->nameQuote('cm').'.'.$db->nameQuote('deleted').' = '.$db->Quote('0');
		$db->setQuery( ApotheosisLibAcl::limitQuery($groupQuery, 'timetable.groups', 'cm') );
		$groupCodes = $db->loadAssocList('id');
		
		$attCodes = ApotheosisAttendanceData::getCodeObjects( array(), false );
		
		// process raw marks to include name and mark data
		foreach( $rawMarks as $k=>$v ) {
			$rawMarks[$k]['fullname'] = $groupCodes[$v['group']]['fullname'];
			unset($rawMarks[$k]['group']);
			$rawMarks[$k]['meaning'] = $attCodes[$v['att_code']]->st_meaning;
			$rawMarks[$k]['sc_id'] = $attCodes[$v['att_code']]->sc_id;
			$rawMarks[$k]['st_summary'] = $attCodes[$v['att_code']]->st_summary;
			$rawMarks[$k]['code'] = $attCodes[$v['att_code']]->code;
			unset($rawMarks[$k]['att_code']);
		}
		
		// get an array of statistical meanings
		$query = 'SELECT *'
			."\n".'FROM '.$db->nameQuote( '#__apoth_att_statistical_meaning' );
		$db->setQuery( $query );
		$statMeaning = $db->loadAssocList();
		
		// make an array of empty stat meaning counters
		foreach( $statMeaning as $v ) {
			$meaningsArray[$v['meaning']] = 0;
			if( !is_null($v['summary']) ) {
				$meaningsArrayLimited[$v['meaning']] = 0;
			}
			$scMeaningsArray[$v['meaning']] = array();
		}
		
		// get a map of school meaning id to stat meaning
		$query = 'SELECT '.$db->nameQuote( 'schm' ).'.'.$db->nameQuote( 'id' ).' as '.$db->nameQuote( 'sc_id' ).',
						 '.$db->nameQuote( 'schm' ).'.'.$db->nameQuote( 'meaning' ).' as '.$db->nameQuote( 'sc_meaning' ).',
						 '.$db->nameQuote( 'statm' ).'.'.$db->nameQuote( 'meaning' ).' as '.$db->nameQuote( 'stat_meaning' )
			."\n".'FROM '.$db->nameQuote( '#__apoth_att_codes' ).' as '.$db->nameQuote( 'codes' )
			."\n".'INNER JOIN '.$db->nameQuote( '#__apoth_att_statistical_meaning' ).' as '.$db->nameQuote( 'statm' )
			."\n".'   ON '.$db->nameQuote( 'statm' ).'.'.$db->nameQuote( 'id' ).' = '.$db->nameQuote( 'codes' ).'.'.( 'statistical_meaning' )
			."\n".'INNER JOIN '.$db->nameQuote( '#__apoth_att_school_meaning' ).' as '.$db->nameQuote( 'schm' )
			."\n".'   ON '.$db->nameQuote( 'schm' ).'.'.( 'id' ).' = '.$db->nameQuote( 'codes' ).'.'.$db->nameQuote( 'school_meaning' );
		$db->setQuery( $query );
		$scStatMap = $db->loadAssocList( 'sc_id' );
		
		// sort data by 'statutory' and 'all'
		foreach( $rawMarks as $k=>$v ) {
			// setup statutory array if we have stat data
			if( $v['statutory'] == 1 ) {
				// add empty meanings arrays first time around
				if( !isset($retVal['statutory']) ) {
					$retVal['statutory'] = $meaningsArray;
					$retVal['statutory_limited'] = $meaningsArrayLimited;
					$retVal['statutory_sc'] = $scMeaningsArray;
					$retVal['statutory_possible'] = 0;
				}
				$retVal['statutory'][$v['meaning']]++;
				if( !isset($retVal['statutory_sc'][$scStatMap[$v['sc_id']]['stat_meaning']][$scStatMap[$v['sc_id']]['sc_meaning']]) ) {
					$retVal['statutory_sc'][$scStatMap[$v['sc_id']]['stat_meaning']][$scStatMap[$v['sc_id']]['sc_meaning']] = 0;
				}
				$retVal['statutory_sc'][$scStatMap[$v['sc_id']]['stat_meaning']][$scStatMap[$v['sc_id']]['sc_meaning']]++;
				if( !is_null($v['st_summary']) ) {
					$retVal['statutory_limited'][$v['meaning']]++;
					$retVal['statutory_possible']++;
				}
			}
			
			// setup array for each class first time around
			if( !isset($retVal['all'][$v['fullname']]) ) {
				// add empty stat meanings array first time around
				$retVal['all'][$v['fullname']] = $meaningsArray;
			}
			
			// setup array for school meanings for each class first time around
			if( !isset($retVal['all_sc']) ) {
				// add empty school meanings array first time around
				$retVal['all_sc'] = $scMeaningsArray;
			}
			if( !isset($retVal['all_sc'][$scStatMap[$v['sc_id']]['stat_meaning']][$scStatMap[$v['sc_id']]['sc_meaning']]) ) {
				$retVal['all_sc'][$scStatMap[$v['sc_id']]['stat_meaning']][$scStatMap[$v['sc_id']]['sc_meaning']] = 0;
			}
			
			// setup array for total marks for each class first time around
			if( !isset($retVal['all_totals']['group'][$v['fullname']]) ) {
				$retVal['all_totals']['group'][$v['fullname']] = 0;
			}
			
			// setup array for total marks for each stat meaning first time around
			if( !isset($retVal['all_totals']['meaning'][$v['meaning']]) ) {
				// add empty stat meanings array first time around
				$retVal['all_totals']['meaning'] = $meaningsArray;
				$retVal['all_totals']['meaning_limited'] = $meaningsArrayLimited;
			}
			
			// setup count of total possible attendances
			if( !isset($retVal['all_possible']) ) {
				$retVal['all_possible'] = 0;
			}
			
			$retVal['all'][$v['fullname']][$v['meaning']]++;
			$retVal['all_totals']['group'][$v['fullname']]++;
			$retVal['all_totals']['meaning'][$v['meaning']]++;
			$retVal['all_sc'][$scStatMap[$v['sc_id']]['stat_meaning']][$scStatMap[$v['sc_id']]['sc_meaning']]++;
			if( !is_null($v['st_summary']) ) {
				$retVal['all_totals']['meaning_limited'][$v['meaning']]++;
				$retVal['all_possible']++;
			}
		}
		
		return $retVal;
	}
	
	/**
	 * Get an array of code totals
	 * 
	 * @param $sheetId int  Marksheet id to get code totals from
	 * @return array  code=>[session count, %]
	 */
	function getCodeTotals( $sheetId )
	{
		$markSheet = &$this->getMarkSheet( $sheetId );
		if( is_object($markSheet) ) {
			list($marks, $total) = $markSheet->getMarkSummary();
		}
		$codes = ApotheosisAttendanceData::getCodeObjects( array(), false );
		
		$retVal = array( 'total'=>array('count'=>$total, 'text'=>'Total') );
		foreach( $marks as $m=>$data ) {
			$id = $codes[$m]->id;
			$retVal[$id] = array(
				'count'=>$data,
				'object' =>$codes[$m]
			);
		}
		
		return $retVal;
	}
	
	/**
	 * Get an array of daily session attendance
	 * 
	 * @param $sheetId int  Marksheet id to get session totals from
	 * @return array  day=>sessions=>att%
	 */
	function getSessionTotals( $sheetId )
	{
		$markSheet = &$this->getMarkSheet( $sheetId );
		if( is_object($markSheet) ) {
			list($marks, $heads) = $markSheet->getMarkSummaryStatutory();
		}
		$codes = ApotheosisAttendanceData::getCodeObjects( array(), false );
		ksort( $marks );
		ksort( $heads );
		
		$retVal = array( 'heads'=>$heads );
		$retVal['all']['text'] = 'All';
		foreach( $marks as $day=>$dayInfo ) {
			ksort( $marks[$day]['_children'] );
			$retVal[$day]['text'] = $dayInfo['_text'];
			foreach( $marks[$day]['_children'] as $time=>$data ) {
				$retVal[$day][$time]['count'] = 0;
				$retVal[$day][$time]['total'] = 0;
				if( !isset($retVal['all'][$time]['count']) ) { $retVal['all'][$time]['count'] = 0; }
				if( !isset($retVal['all'][$time]['total']) ) { $retVal['all'][$time]['total'] = 0; }
				foreach( $data as $code=>$count ) {
					if( ($codes[$code]->st_meaning == 'Present') || $codes[$code]->st_meaning == 'Approved educational activity' ) {
						$retVal[$day][$time]['count'] += $count;
						$retVal['all'][$time]['count'] += $count;
					}
					if( ($codes[$code]->st_meaning != 'Attendance not required') && ($codes[$code]->st_meaning != 'No mark') ) { //  don't count these mark types
						$retVal[$day][$time]['total'] += $count;
						$retVal['all'][$time]['total'] += $count;
					}
				}
			}
		}
		
		return $retVal;
	}
	
	/**
	 * Set the rows which can be editted
	 * Also sets the value of editsOn so we can easily determine if any rows are editable
	 * 
	 * @param $rows array  The rows to set
	 * @param $allow boolean  The value to set them to
	 */
	function setRowEdits( $sheetId, $rows, $allow )
	{
		$this->_editsOn = !empty( $rows );
		$this->_edits = array();
		foreach( $rows as $rId ) {
			$this->_edits[$sheetId][$rId] = $allow;
		}
	}
	
	/**
	 * Sees if there are any rows currently set to be editable
	 * 
	 * @return boolean  True if any rows are editable, false otherwise
	 */
	function getRowEditsOn()
	{
		return $this->_editsOn;
	}
	
	/**
	 * Gets the number of people in the users tmp people table
	 * 
	 * @return int  Number of people in users tmp people table
	 */
	function getUsersTmpPplNum()
	{
		$db = &JFactory::getDBO();
		$usersTmpPeople = $db->nameQuote( ApotheosisLibAcl::getUserTable('people.people') );
		$query = 'SELECT COUNT(*) FROM '.$usersTmpPeople;
		$db->setQuery( $query );
		
		return $db->loadResult();
	}
	
	/**
	 * Gets the requiremnts used to generate the given marksheet
	 * 
	 * @param int $sheetId  ID of the mark sheet whose requirements we require
	 * @return array  The array of requirements used to create the given marksheet
	 */
	function getRequirements( $sheetId )
	{
		return ( is_array($this->_requirements[$sheetId]) ? $this->_requirements[$sheetId] : array() );
	}
	
	/**
	 * Use the given URL to create a tmp image file and return the path to the file
	 * 
	 * @param string $url  URL to google API
	 * @return string $tmpName  Path the image file
	 */
	function getImageFile( $url )
	{
		// Use cURL to get the image from google api
		$cHandle = curl_init();
		curl_setopt( $cHandle, CURLOPT_URL, $url );
		curl_setopt( $cHandle, CURLOPT_HEADER, false );
		curl_setopt( $cHandle, CURLOPT_RETURNTRANSFER, true );
		$imageData = curl_exec( $cHandle );
		curl_close( $cHandle );
		
		// Write image data to a tmp file
		$config = &JFactory::getConfig();
		$tmpName = tempnam( realpath($config->getValue('config.tmp_path')), 'atten_' );
		rename( $tmpName, $tmpName.'.png' );
		$tmpName = $tmpName.'.png';
		chmod( $tmpName, 0644 );
		$handle = fopen( $tmpName, "w" );
		fwrite( $handle, $imageData );
		fclose( $handle );
		
		return $tmpName;
	}
}
?>