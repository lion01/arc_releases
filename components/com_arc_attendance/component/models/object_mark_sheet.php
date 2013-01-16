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

/**
 * Mark object
 *
 * A sheet of marks is modeled by this class
 *
 * @author		lightinthedark <code@lightinthedark.org.uk>
 * @package		Apotheosis
 * @subpackage	Attendance
 * @since 0.1
 */
class MarkSheet extends JObject
{
	function __construct()
	{
		parent::__construct();
		
		$this->_aggregate = 0;
		$this->_filter = null;
		
		$this->_rId = 0;
		$this->_cId = 0;
		
		$this->_rowStruct = array();
		$this->_rowTags = array();
		$this->_allRows = array();
		$this->_colStruct = array();
		$this->_colTags = array();
		$this->_allCols = array();
		
		$this->_markRows = array();
		$this->_headRows = array();
		$this->_headRowCount = 0;
		
		foreach( $this->_rowLevels as $k=>$v ) {
			$this->_groupings[$k] = false;
		}
	}
	
	/**
	 * Creates a mark sheet
	 * @param $requirements array  Associative array of requirements (property=>value)
	 * @param $type string  The mark sheet type (used to determine how to display, amongst others)
	 * @return MarkSheet  The newly created mark sheet
	 */
	function &getNew( $requirements, $type )
	{
		// Load up all the data the sheet will need before creating it
		$mf = &MarkFactory::getFactory();
		$db = &JFactory::getDBO();
		
		if( array_key_exists('start_date', $requirements) ) { $requirements['valid_from'] = $requirements['start_date']; }
		if( array_key_exists('end_date',   $requirements) ) { $requirements['valid_to']   = $requirements['end_date']; }
		$requirements['role'] = ApotheosisLibAcl::getRoleId( 'group_participant_student' );
		
		$u = &ApotheosisLib::getUser();
		$table = ApotheosisLibDbTmp::getTable( 'att', 'sheet', $u->id, false, false, false );
		
		require_once(JPATH_SITE.DS.'components'.DS.'com_arc_timetable'.DS.'helpers'.DS.'data_access.php');
		ApotheosisTimetableData::getEnrolments( $requirements, $table, true );
		
		// add attendance code to each entry
		$mf->addAtt( $table );
		
		//add overall attendance percent to each entry if needed
		if( isset($requirements['att_percent']) && $requirements['att_percent'] != false ) {
			$mf->addAttPercent( $table, $requirements );
		}
		
		foreach( $requirements as $col=>$val ) {
			// pre-escape/quote arrays of values
			if( is_array($val) ) {
				foreach($val as $k=>$v) {
					$val[$k] = $db->Quote($v);
				}
				$assignPart = ' IN ('.implode(', ', $val).')';
			}
			else {
				$assignPart = ' = '.$db->Quote($val);
			}
			
			switch( $col ) {
			case( 'att_code' ):
				$wheres[] = $db->nameQuote('t').'.'.$db->nameQuote($col).$assignPart;
				break;
				
			case( 'att_percent' ):
				switch( $requirements['att_percent_com'] ) {
				case( 'less_than' ):
					$operator = ' <= ';
					break;
				case( 'exactly' ):
					$operator = ' = ';
					break;
				case( 'more_than' ):
					$operator = ' >= ';
					break;
				}
				$wheres[] = $db->nameQuote('t').'.'.$db->nameQuote($col).$operator.$db->Quote($val);
				break;
// *** We should add "last_modified_by" to dailyatt table
//			case( 'last_modified_by' ):
//				$wheres[] = $d->nameQuote('t').'.'.$db->nameQuote($col).$assignPart;
//				break;
			}
		}
		
		$query = 'SELECT t.*'
			."\n".', dd.start_time AS `time`, dd.statutory AS statutory, dd.day_section_short'
			."\n".', p.title, p.firstname, p.middlenames, p.surname'
			."\n".'FROM '.$table.' AS t'
			."\n".'INNER JOIN #__apoth_tt_daydetails AS dd'
			."\n".'   ON dd.day_section = t.day_section'
			."\n".'  AND dd.valid_from < t.date'
			."\n".'  AND ((dd.valid_to > t.date) OR (dd.valid_to IS NULL))'
			."\n".'INNER JOIN #__apoth_ppl_people AS p'
			."\n".'   ON p.id = t.person_id'
			.( empty($wheres) ? '' : "\n".'WHERE '.implode( "\n".' AND ', $wheres ) );
		$db->setQuery( $query );
		$data = $db->loadAssocList();
		if( !is_array($data) ) { $data = array(); }
		
		ApotheosisLibDbTmp::clear( $table );
		ApotheosisLibDbTmp::commit( $table );
		
		switch( $type ) {
		case( 'full' ):
			$t = &MarkSheetFull::getNew( $data );
			break;
		
		case( 'summary' ):
			$t = &MarkSheetSummary::getNew( $data );
			break;
		}
		
		return $t;
	}
	
	/**
	 * Clones a section of the given mark sheet.
	 * Section root defined by rootId
	 * This general function navigates the tree to find the node we're to focus on then
	 * passes control down to the appropriate sub-class
	 */
	function getClone( &$orig, $rootId, $newType )
	{
		$root = $orig->_allRows[$rootId];
		$rows = array();
		
		// get the row tree that will have been used and search it for the node identified
		$row = $orig->_rowStruct;
		
		if( ($orig->getType() == 'full') && ($newType == 'summary') ) {
			// when going from full to summary we need to get all rows for the selected pupil
			$rows = $orig->_rowTags['pupil'][$root['pupil']];
		}
		else {
			$groupingsOn = array_keys($orig->_groupings);
			$g = reset( $groupingsOn );
			while( !is_null($root[$g]) && ($g !== false) ) {
				$row = &$row[$root[$g]];
				$g = next( $groupingsOn );
			}
			
			$queue = array( $row );
			$cur = reset( $queue );
			while( $cur !== false ) {
				if( is_array($cur) ) {
					foreach( $cur as $k=>$v ) {
						$queue[] = &$cur[$k];
					}
				}
				else {
					$rows[] = $cur;
				}
				$cur = next( $queue );
			}
		}
		
		// Hand over to the specific sheet type to copy the marks over
		// so it can do any restructuring or aggregate calculation it needs to
		switch( $newType ) {
		case( 'full' ):
			$t = &MarkSheetFull::getClone( $orig, $row, $rows );
			break;
		
		case( 'summary' ):
			$t = &MarkSheetSummary::getClone( $orig, $row, $rows );
			break;
		
		default:
			return false;
		}
		
		return $t;
	}
	
	/**
	 * Gives the type of the mark sheet as defined in the constructor arguments
	 * @return string  either "full" or "summary"
	 */
	function getType()
	{
		return $this->_type;
	}
	
	/**
	 * 
	 * @param $headRowCats
	 */
	function _makeTreeHeadRows()
	{
		$headRowCats = array();
		
		// Calculate column groupings (tabs)
		reset($this->_headLevels);
		$cat = key($this->_headLevels);
		$maxRows = count($this->_maxCols);
		do {
			array_unshift($headRowCats, $cat);
			$cat = $this->_nextHeadLevel( $cat, $this->_headRowCount );
			$this->_headRowCount++;
		} while( ($cat !== false) && ($this->_headRowCount < $maxRows) );
		
		$this->_headRowLabels[0] = 'Matching targets';
		foreach( $headRowCats as $cat ) {
			$this->_headRowLabels[] = $this->_headLevels[$cat]['label'];
		}
		
		$this->_headRows = array( 'all'=>array(
			  '_text'     =>JText::_('Results')
			, '_text_full'=>JText::_('Results')
			, '_colid'    =>null
			, '_active'   =>true
			, '_enabled'  =>false
			, '_children' =>array()
			) );
		$this->_headRowCount++;
		
		// **** This mechanism probably needs to change.
		// The head row levels should provide multiple options
		// eg (period > day > week > term , or period > date , etc.)
		// we probably also want option to roll data up together (eg put date in period text)
		// if we skip a head level.
		// Criteria for going up a level needs to expand to not only "will it fit" but also
		// "do we need to disambiguate"
//		echo 'making head rows with:<br />';
//		var_dump_pre($this->_allCols, 'cols:');
//		var_dump_pre($this->_colStruct, 'colStruct:');
//		var_dump_pre($headRowCats, 'headRowCats:');
		// Having worked out what groupings we need, let's put the heading columns into an n-dimensional tree
		foreach( $this->_allCols as $id=>$tags) {
			unset( $tmp );
			$tmp = &$this->_headRows['all']['_children'];
			$maxCat = count($headRowCats) - 1;
			for( $i = 0; $i < $maxCat; $i++ ) {
				$cat = $headRowCats[$i];
				$tc = $tags[$cat];
				if( !isset($tmp[$tc]['_children']) ) {
					$val = $this->_headLevels[$headRowCats[$i]]['name'];
					$valFull = $this->_headLevels[$headRowCats[$i]]['label'];
					$tmp[$tc]['_text'] = $tags[$val];
					$tmp[$tc]['_text_full'] = $tags[$val];
					$tmp[$tc]['_colid'] = $tc;
					if( $valFull ) {
						$tmp[$tags[$cat]]['_text_full'] = $tags[$val];
					}
					$tmp[$tc]['_active'] = false;
					$tmp[$tc]['_enabled'] = $tags['_enabled'];
					$tmp[$tc]['_children'] = array();
				}
				else {
					$tmp[$tc]['_enabled'] = ($tmp[$tc]['_enabled'] || $tags['_enabled']);
				}
				$tmp = &$tmp[$tags[$cat]]['_children'];
			}
			$val = $this->_headLevels[$headRowCats[$i]]['name'];
			$cat = $headRowCats[$i];
			$tmp[$tags[$cat]]['_text'] = $tags[$val];
			$tmp[$tags[$cat]]['_text_full'] = $tags[$val];
			$tmp[$tags[$cat]]['_colid'] = $id;
			$tmp[$tags[$cat]]['_active'] = false;
			$tmp[$tags[$cat]]['_enabled'] = $tags['_enabled'];
		}
		// ... finally: sort them and set the first entries down the tree as active and count the leaves
		$this->_sortHeads( $this->_headRows );
	}
	
	/**
	 * Find the next level up of heading category needed to allow the category given
	 * at the level given (levels have limits on column count)
	 * @param $cat string  The category key (refers to _headLevels) that we need to group up
	 * @param $lvl int  The level at which the given category must fit
	 * @return string  The category key that needs to be used next above the given category
	 */
	function _nextHeadLevel( $cat, $lvl )
	{
		$baseMax = $this->_maxCols[$lvl];
		reset( $this->_headLevels );
		$baseCat = key( $this->_headLevels );
		while( !is_null($baseCat) && ($baseCat != $cat) ) {
			next( $this->_headLevels ); 
			$baseCat = key( $this->_headLevels );
		}
		
		$c = ( isset($this->_colTags[$baseCat]) ? count($this->_colTags[$baseCat]) : 0 );
//		var_dump_pre($baseCat, 'base cat');
//		var_dump_pre($baseMax, 'base max');
//		var_dump_pre($this->_colTags, 'coltags');
//		var_dump_pre($c, 'c');
		if( is_null($baseCat)
		 || is_null($baseMax)
		 || ($c == 0) ) {
//		 	echo 'early exit for having nothing left<br />';
		 	$retVal = false;
		}
		elseif( $c <= $baseMax) {
			// looks like we've got enough headers, but do we really?
			// if any of the entries for our base category occurs more than once then we need the next level
//		 	echo 'probably ok...<br />';
			$retVal = false;
			$cur = reset($this->_colTags[$baseCat]);
			do {
		 		if( $cur > 1 ) {
//		 			echo 'but have multiple of '.$baseCat.'<br />';
					next( $this->_headLevels );
					$nextCat = key($this->_headLevels);
					$retVal = ( is_null($nextCat) ? false : $nextCat );
					break;
		 		}
		 	} while( (($cur = next($this->_colTags[$baseCat])) !== false) );
		}
		else {
//			echo 'going up normally<br />';
			// There's too many things at this level to show on this row, so we need a new row
			// with as high a level as will allow its descendants at this level to always fit on a row
			$lastWorked = true;
			next( $this->_headLevels );
			$nextCat = key($this->_headLevels);
			$retVal = ( is_null($nextCat) ? false : $nextCat ); // even if it's not perfect we'll always go up at least one heading level if we can
			while( $lastWorked && !is_null($nextCat) ) {
				// find out how many entries per category are needed for categories at that level
				$tmp = array();
				// ... put into divisions
				foreach( $this->_allCols as $col ) {
					$tmp[$col[$nextCat]][$col[$baseCat]] = 1;
				}
				// ... find the biggest category
				$maxCat = 0;
				foreach( $tmp as $tmpCat ) {
					$c = count($tmpCat);
					if( $c > $maxCat ) {
						$maxCat = $c;
					}
				}
				
				// if things don't fit, stop trying our luck
				if( $maxCat > $baseMax ) {
					$lastWorked = false;
				}
				else {
//					echo 'found happiness with '.$nextCat.'<br />';
					$retVal = $nextCat;
					next( $this->_headLevels );
					$nextCat = key($this->_headLevels);
				}
			}
		}
		return $retVal;
	}
	
	/**
	 * Sorts the heading row given and its descendants by key (by recursion).
	 * Sets the first entry in each row as the active one
	 * @param $headRow array  The heading row to be sorted entries are: (id=>_children,_active,_text)
	 */
	function _sortHeads( &$headRow )
	{
		ksort($headRow);
		$count = 0;
		foreach( $headRow as $k=>$v ) {
			if( isset( $headRow[$k]['_leafCount']) ) {
				continue; // in case php weirdly resets the array cursor between recursions
			}
			if( !empty($headRow[$k]['_children']) ) {
				reset($headRow);
				$headRow[key($headRow)]['_active'] = true;
				$r = $this->_sortHeads( $headRow[$k]['_children'] );
				$headRow[$k]['_leafCount'] = $r;
				$count += $r;
			}
			else {
				$headRow[$k]['_leafCount'] = 0;
				$count += 1;
			}
		}
		return $count;
	}
	
	/**
	 * Retrieves the count of how many levels of heading rows we have in our tree
	 * @return int  The number of head rows
	 */
	function getHeadRowCount()
	{
		return $this->_headRowCount;
	}
	
	/**
	 * Gives back a 2d array of the heading rows
	 */
	function getHeadRows()
	{
		return $this->_headRows;
	}
	
	/**
	 * @param $lvl  The level of heading whose row label is required
	 * @return Gives back the head row label
	 */
	function getHeadRowLabel( $lvl )
	{
		return isset($this->_headRowLabels[$lvl]) ? $this->_headRowLabels[$lvl] : null;
	}
	
	/**
	 * The current item at the level above determines
	 * which tab's children this gives.
	 * @param $lvl  The level of heading whose row is required
	 */
	function getHeadRow( $lvl )
	{
		
	}
	
	/**
	 * Retrieves the id of the active heading entry at the given level
	 * @param $lvl  The level of heading whose active element is required
	 */
	function getHeadActive( $lvl )
	{
		
	}
	
	/**
	 * Sets an item on the given level as being active
	 * @param $lvl int  The level of the item ( 0 = col headers, 1 = tabs grouping cols, etc )
	 * @param $id string|int  The id of the header to be made active
	 */
	function setHeadActive( $lvl, $id )
	{
		$cur = &$this->_headRows;
		for( $i = ($lvl + 1); $i < $this->_headRowCount; $i++ ) {
			foreach( $cur as $k=>$v ) {
				if( $v['_active'] ) {
					$cur = &$cur[$k]['_children'];
					break;
				}
			}
		}
		
		if( isset($cur[$id]) ) {
			foreach( $cur as $k=>$v ) {
				$cur[$k]['_active'] = false;
			}
			$cur[$id]['_active'] = true;
			return true;
		}
		return false;
	}
	
	/**
	 * 
	 */
	function getDaySections()
	{
		return array_keys( $this->_colTags['day_section_short'] );
	}
	
	/**
	 * Switch toggle $t to either !currentVal or $val if given
	 * 
	 * @param $t string  The toggle to be switched
	 * @param $val boolean|null|-1  The value to set the toggle to. true = on, false = off, null = disabled, -1 to invert
	 * @return boolean  true on success, false on failure
	 */
	function setGroupings( $t, $val = -1 )
	{
		if( !array_key_exists($t, $this->_groupings) ) {
			return false;
		}
		if( $val === -1 ) {
			$this->_groupings[$t] = ( is_null($this->_groupings[$t]) ? null : !$this->_groupings[$t] );
		}
		elseif( is_null($val) || is_bool($val) ) {
			$this->_groupings[$t] = $val;
		}
		else {
			$this->_groupings[$t] = (bool)$val;
		}
		
		return true;
	}
	
	/**
	 * Gives an array of toggles and states
	 * 
	 * @return array  id=>state 
	 */
	function getGroupings()
	{
		return $this->_groupings;
	}
	
	function getRowIsPupil( $rId )
	{
		return !is_null($this->_allRows[$rId]['pupil']);
	}
	
	function getRowText( $rId )
	{
		$r = &$this->_allRows[$rId];
		if( !is_null($r) ) {
			if( isset($r['_text']) ) {
				return $r['_text'];
			}
			else {
				return 'label unknown, sorry';
			}
		}
		else {
			return 'Unknown row';
		}
	}
	
	/**
	 * Gives back an ordered n-dimensional array of row ids with
	 * types, ordering / grouping defined by $this->_groupings
	 */
	function getRows()
	{
		$groupingsOn = array_keys($this->_groupings, true);
		$rows = array();
		
		foreach( $this->_allRows as $rId=>$tags ) {
			// we want to avoid adding rows which are not to be shown in the current grouping
			$doit = false;
			foreach( $this->_groupings as $g=>$tf ) {
				if( !is_null($tags[$g]) ) {
					$doit = (bool)$tf;
				}
			}
			if( !$doit ) {
				continue;
			}
			
			unset( $tmp );
			$tmp = &$rows;
			$include = false;
			$nTag = reset( $groupingsOn );
			$ntc = $tags[$nTag];
			do {
				$tag = $nTag;
				$tc = $ntc;
				
				$nTag = next( $groupingsOn );
				$ntc = (isset( $tags[$nTag] ) ? $tags[$nTag] : null );
				
				if( is_null($tc) ) {
					end( $groupingsOn );
				}
				else {
					$include = true;
				}
				
				if( is_null($ntc) ) {
					if( $include ) {
						$tmp[$tc]['_rows'][] = $rId;
						$tmp[$tc]['_text'] = $tags[$this->_rowLevels[$tag]];
						$tmp[$tc]['_multi'] = $tags['multi'];
						$this->_allRows[$rId]['_text'] = $tmp[$tc]['_text'];
					}
				}
				else {
					if( !isset($tmp[$tc]['_children']) ) {
						$tmp[$tc]['_children'] = array();
					}
					$tmp = &$tmp[$tc]['_children'];
				}
			} while( !is_null($ntc) );
		}
		
		$this->_sortRows( $rows );
		
		return $rows;
	}
	
	/**
	 * Sorts the heading row given and its descendants by key (by recursion).
	 * Sets the first entry in each row as the active one
	 * @param $headRow array  The heading row to be sorted entries are: (id=>_children,_active,_text)
	 */
	function _sortRows( &$row )
	{
		uasort( $row, array($this, '_sortRowCallback') );
//		ksort($row);
		foreach( $row as $k=>$v ) {
			if( !empty($row[$k]['_children']) ) {
				$this->_sortRows( $row[$k]['_children'] );
			}
		}
	}
	
	/**
	 * Callback function for _sortRows()
	 */
	function _sortRowCallback( $a, $b )
	{
		if( $a['_text'] == $b['_text'] ) {
			return 0;
		}
		elseif( is_numeric($a['_text']) && is_numeric($b['_text']) ) {
			return ( ((int)$a['_text'] < (int)$b['_text']) ? -1 : 1 );
		}
		return ( ($a['_text'] < $b['_text']) ? -1 : 1 );
	}
	
	function getMarkTypes( $rowIds, $colIds )
	{
//		var_dump_pre(func_get_args(), 'getMarkTypes with args:');
		$retVal = array();
		foreach( $rowIds as $rId ) {
			foreach( $colIds as $cId ) {
//				var_dump_pre($this->_markRows[$rId][$cId], 'mark row:');
				if( isset($this->_markRows[$rId][$cId]['type']) ) {
					$retVal[$this->_markRows[$rId][$cId]['type']] = $this->_markRows[$rId][$cId]['type'];
				}
			}
		}
		
		return $retVal;
	}
	
	/**
	 * Retrieves separate mark objects located in the given row / column
	 * @param $rowIds array  The id(s) of the row(s) containing the mark
	 * @param $colIds array  The id(s) of the column(s) containing the mark
	 * @return array  An array of mark objects which exist in the given rows' columns with additional info
	 */
	function getMarks( $rowIds, $colIds )
	{
		$retVal = array();
		foreach( $rowIds as $rId ) {
			foreach( $colIds as $cId ) {
				if( isset($this->_markRows[$rId][$cId]) && ($this->_markRows[$rId][$cId]['type'] == 's') ) {
					$retVal[] = $this->_markRows[$rId][$cId];
				}
			}
		}
		
		return $retVal;
	}
	
	/**
	 * Sets the values of marks identified in the given array to the values given in that array
	 * @param array $marks  2d array of marks. rowId=>colId=>mark_value ('/', 'N', etc)
	 * @return array  2-element array 'good' and 'bad' with array of row ids that had no or some errors respectively when saving
	 */
	function setMarks( $marks )
	{
		$u = ApotheosisLib::getUser();
		$uId = $u->person_id;
		$retVal = array( 'good'=>array(), 'bad'=>array() );
		foreach( $marks as $rId=>$cols ) {
			$rowOk = true;
			foreach( $cols as $cId=>$mark ) {
				if( is_object($this->_markRows[$rId][$cId]['mark']) ) {
					$r = $this->_markRows[$rId][$cId]['mark']->setValue( $mark, $uId );
					if( !$r ) {
						$rowOk = false;
					}
				}
			}
			if( $rowOk ) {
				$retVal['good'][] = $rId;
			}
			else {
				$retVal['bad'][] = $rId;
			}
		}
		return $retVal;
	}
	
	/**
	 * Gets the number of mark rows we have
	 * 
	 * @return int  the number of mark rows
	 */
	function getMarkRowCount()
	{
		return count( $this->_markRows );
	}
	
	/**
	 * Replaces all occurrences of the $needle with the $replace when it occurs in
	 * the given date range at the given time
	 */
	function replaceMarks( $find, $replace, $rowColTuples, $time, $from, $to )
	{
		$colIds = array();
		foreach( $this->_allCols as $id=>$col ) {
			if( (($col['day_section_short'] == $time) || ($time == ''))
			 && ($col['date'] >= $from)
			 && ($col['date'] <= $to) ) {
				$colIds[$id] = true;
			}
		}
		
		$changed = 0;
		foreach( $rowColTuples as $k=>$tuples ) {
			foreach( $tuples as $tuple ) {
				$rId = $tuple['row'];
				$cId = $tuple['col'];
				if( isset($this->_markRows[$rId][$cId])
				 && isset($colIds[$cId])
				 && ($this->_markRows[$rId][$cId]['type'] == 's')
				 && ($this->_markRows[$rId][$cId]['mark']->getValue() == $find) ) {
					$this->_markRows[$rId][$cId]['mark']->setValue( $replace );
					$changed++;
				}
			}
		}
		
		return $changed;
	}
	
	/**
	 * Gets the count of marks in the given row / column, filtered by mark if given
	 * @param $rowIds array  The id(s) of the row(s) whose child marks are to be counted
	 * @param $colIds array  The id(s) of the column(s) contianing the marks to count
	 * @param $mark string  The mark symbols to count in the process
	 * @return array  The count and total of marks in the rows' columns which are the given mark (or any mark if no mark given)
	 */
	function getMarkCount( $rowIds, $colIds, $mark = null )
	{
		$c = 0;
		$t = 0;
		require_once(JPATH_SITE.DS.'components'.DS.'com_arc_attendance'.DS.'helpers'.DS.'data_access.php');
		$noMarkObj = &ApotheosisAttendanceData::getNoCode();
		foreach( $rowIds as $rId ) {
			foreach( $colIds as $cId ) {
				if( isset($this->_markRows[$rId][$cId]) ) {
					if( $this->_markRows[$rId][$cId]['type'] == 's' ) {
						$t++;
						if( is_null($mark) || ($this->_markRows[$rId][$cId]['mark']->getValue() == $mark) ) {
							$c++;
						}
					}
					elseif( ($this->_markRows[$rId][$cId]['type'] == 'm')
					     || ($this->_markRows[$rId][$cId]['type'] == 'sum') ) {
						
						if( !isset($this->_markRows[$rId][$cId]['total'])                   ) { $this->_markRows[$rId][$cId]['total']                   = 0; }
						if( !isset($this->_markRows[$rId][$cId]['marks'][$noMarkObj->code]) ) { $this->_markRows[$rId][$cId]['marks'][$noMarkObj->code] = 0; }
						if( !isset($this->_markRows[$rId][$cId]['marks'][$mark])            ) { $this->_markRows[$rId][$cId]['marks'][$mark]            = 0; }
						$t += $this->_markRows[$rId][$cId]['total'];
						if( is_null($mark) ) {
							$c += $this->_markRows[$rId][$cId]['total'];
							$c -= $this->_markRows[$rId][$cId]['marks'][$noMarkObj->code];
						}
						else {
							$c += $this->_markRows[$rId][$cId]['marks'][$mark];
						}
					}
				}
			}
		}
		$retVal = array( $c, $t );
		return $retVal;
	}
	
	/**
	 * Gets the percentage of marks in the given row / column, which match the mark if given
	 * @param $rowIds array  The id(s) of the row(s) whose child marks are to be counted
	 * @param $colIds array  The id(s) of the column(s) contianing the marks to count
	 * @param $mark string  The mark symbols to count in the process
	 * @return float  The percentage of marks in the given rows' columns which are the given mark (or any mark if no mark given)
	 */
	function getMarkPercent( $rowIds, $colIds, $mark = null )
	{
		list( $count, $total ) = $this->getMarkCount( $rowIds, $colIds, $mark );
		
		if( $total == 0 ) {
			$retVal = 0;
		}
		else {
			$retVal = ($count / $total)*100;
		}
		
		return $retVal;
	}
	
	/**
	 * Gets array of mark counts and a total
	 * @param $rowIds array|null  The row ids to use, or null to use all rows
	 * @return array  2-element array: The array of code-indexed counts, the total counted
	 */
	function getMarkSummary( $rowIds = null )
	{
		if( is_null($rowIds) ) {
			$rowIds = array_keys( $this->_markRows );
		}
		
		$m = array();
		$t = 0;
		foreach( $rowIds as $rId ) {
			if( isset($this->_markRows[$rId]['summary']) ) {
				$t += $this->_markRows[$rId]['summary']['total'];
				foreach( $this->_markRows[$rId]['summary']['marks'] as $mark=>$count ) {
					if( !isset($m[$mark]) ) { $m[$mark] = 0; }
					$m[$mark] += $count;
				}
			}
		}
		return array( $m, $t );
	}
	
	/**
	 * Gets array of mark counts and a total for statutory periods only
	 * @param $rowIds array|null  The row ids to use, or null to use all rows
	 * @return array  2-element array: The array of daysection-indexed code-indexed counts, the total counted
	 */
	function getMarkSummaryStatutory( $rowIds = null )
	{
		if( is_null($rowIds) ) {
			$rowIds = array_keys( $this->_markRows );
		}
		
		$m = array();
		$h = array();
		foreach( $rowIds as $rId ) {
			foreach( $this->_markRows[$rId] as $cId=>$marks ) {
				if( isset($this->_allCols[$cId]['_statutory'])
				 && $this->_allCols[$cId]['_statutory']
				 && ($marks['type'] == 's') ) {
					$c = &$this->_allCols[$cId];
					$ma = &$marks['mark'];
					$dayNum = date( 'N', strtotime($ma->getDate()) );
					$dayText = date( 'l', strtotime($ma->getDate()) );
					
					if( !isset($m[$dayNum]['_children'][$c['day_section_time']][$ma->getValue()]) ) {
						$m[$dayNum]['_children'][$c['day_section_time']][$ma->getValue()] = 0;
					}
					$m[$dayNum]['_text'] = $dayText;
					$m[$dayNum]['_children'][$c['day_section_time']][$ma->getValue()]++;
					$h[$c['day_section_time']] = $c['day_section_short'];
				}
			}
		}
		
		return array( $m, $h );
	}
	
	/**
	 * Gets array of mark counts and flag statutory marks
	 * @return array  Indexed array of mark arrays (group, att code and statutory flag)
	 */
	function getMarkSummaryGChart()
	{
		$rowIds = array_keys( $this->_markRows );
		
		$i = 0;
		foreach( $rowIds as $rId ) {
			foreach( $this->_markRows[$rId] as $cId=>$marks ) {
				if( ($marks['type'] != 'm') && ($marks['type'] != 'sum') ) {
					$retVal[$i]['group'] = $marks['mark']->_group;
					$retVal[$i]['att_code'] = $marks['mark']->_att_code;
					$retVal[$i]['statutory'] = (int)$this->_allCols[$cId]['_statutory'];
					$i++;
				}
			}
		}
		
		return $retVal;
	}
	
	/**
	 * Retrieves a list of all the pupils for which we have attendance marks to show
	 */
	function getPeople()
	{
		if( isset($this->_rowTags['pupil']) && is_array($this->_rowTags['pupil']) ) {
			$tmp = array_keys( $this->_rowTags['pupil'] );
			foreach( $tmp as $k=>$v ) {
				if( empty($tmp[$k]) ) {
					unset( $tmp[$k] );
				}
			}
			$retVal = $tmp;
		}
		else {
			$retVal = array();
		}
		
		return $retVal;
	}
	
	/**
	 * Retrieves the list of aggregate row types
	 * 
	 * @return array  Indexed array of row types (id=>textual representation)
	 */
	function getAggregateTypes()
	{
		return array( 0=>'#/##', 1=>'%', 2=>'#/## - %' );
	}
	
	/**
	 * Sets the aggregate row type
	 * 
	 * @param $a int  Optional param to specify the aggregate row type. If omitted the first option is used.
	 */
	function setAggregateType( $a = null )
	{
		if( !is_numeric($a) ) {
			$agg = $this->getAggregateTypes();
			reset( $agg );
			$a = key( $agg );
		}
		
		$this->_aggregate = $a;
	}
	
	/**
	 * Retrieves the current aggregate row type
	 * 
	 * @return array
	 */
	function getAggregateType()
	{
		return $this->_aggregate;
	}
	
	/**
	 * Sets the filter to use when displaying marks and totals
	 * 
	 * @param $f string  The code by which to filter
	 */
	function setFilter( $f )
	{
		$this->_filter = $f;
	}
	
	/**
	 * Retrieve the filter mark
	 * 
	 * @return string  The mark by which we are filtering
	 */
	function getFilter()
	{
		return $this->_filter;
	}
}
?>