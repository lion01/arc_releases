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
class MarkSheetSummary extends MarkSheet
{
	var $_maxCols = array(15, 15, 5, 5); // first num is lowest level headings (eg day sections)
	var $_headLevels = array(
		  'day_section_time'=>array( 'name'=>'day_section_short', 'label'=>'Time' )
		, 'week'=>array( 'name'=>'wb', 'label'=>'Week' )
		, 'term'=>array( 'name'=>'term_name', 'name_full'=>'term_name_full', 'label'=>'Term' )
		, 'year'=>array( 'name'=>'year', 'label'=>'Year' )
		);
	
	var $_rowLevels = array( 'pupil'=>'pupil_name'
		, 'day'=>'day_name' );
	
	/**
	 * Create a new summary mark sheet
	 */
	function __construct()
	{
		parent::__construct();
		$this->_type = 'summary';
	}
	
	/**
	 * Creates a mark sheet
	 * @param $requirements array  Associative array of requirements (property=>value)
	 * @param $type string  The mark sheet type (used to determine how to display, amongst others)
	 * @return MarkSheet  The newly created mark sheet
	 */
	function &getNew( &$data )
	{
		$t = new MarkSheetSummary();
		
		$mf = &MarkFactory::getFactory();
		
		// Hold references to mark objects accessible via factory
		$i = 0;
		while( !is_null( ($d = array_shift($data)) ) ) {
			// Pupil rows (single row use type 's')
			$mark = &$mf->getMark( $d['date'], $d['day_section'], $d['person_id'], $d['group_id'], $d );
			$markVal = $mark->getValue(); // just for easy reference below
			
			$d['multi'] = false;
			$c = $t->_getColId( $d );
			$r = $t->_getRowId( $d );
			$t->_markRows[$r][$c]['row'] = $r;
			$t->_markRows[$r][$c]['col'] = $c;
			$t->_markRows[$r][$c]['type'] = 's';
			$t->_markRows[$r][$c]['mark'] = &$mark;
			
			// accumulate data for aggregate rows (multiple values use type 'm')
			// ... pupil
			$d['multi'] = true;
			unset($d['date']);
			unset($d['time']);
			$r = $t->_getRowId( $d );
			if( !isset($t->_markRows[$r][$c]['marks'][$markVal]       ) ) { $t->_markRows[$r][$c]['marks'][$markVal]        = 0; }
			if( !isset($t->_markRows[$r][$c]['total']                 ) ) { $t->_markRows[$r][$c]['total']                  = 0; }
			if( !isset($t->_markRows[$r]['summary']['marks'][$markVal]) ) { $t->_markRows[$r]['summary']['marks'][$markVal] = 0; }
			if( !isset($t->_markRows[$r]['summary']['total']          ) ) { $t->_markRows[$r]['summary']['total']           = 0; }
			$t->_markRows[$r][$c]['row'] = $r;
			$t->_markRows[$r][$c]['col'] = $c;
			$t->_markRows[$r][$c]['type'] = 'm';
			$t->_markRows[$r][$c]['marks'][$markVal]++;
			$t->_markRows[$r][$c]['total']++;
			$t->_markRows[$r]['summary']['marks'][$markVal]++;
			$t->_markRows[$r]['summary']['total']++;
			$t->_markRows[$r]['summary']['type'] = 'sum';
			
			unset( $mark );
			$i++;
		}
		
		// Let's put the heading columns into an n-dimensional tree
		$t->_makeTreeHeadRows();
		$t->_initGroupings();
		return $t;
	}
	
	/**
	 * Clones a section of the given mark sheet.
	 * Section root defined by rootId
	 */
	function getClone( &$orig, &$root, $rows )
	{
		$t = new MarkSheetSummary();
		
		// copy over the marks, re-creating the structure of columns / rows as we go
		foreach( $rows as $rId ) {
			foreach( $orig->_markRows[$rId] as $cId=>$mark ) {
				if( !is_null($orig->_allCols[$cId]) ) {
					// combine tags of r and c then get new row / col ids for this mark
					$tags = array_merge( $orig->_allRows[$rId], $orig->_allCols[$cId] );
					$c = $t->_getColIdTagged( $tags );
					
					$r = $t->_getRowIdTagged( $tags );
					$t->_markRows[$r][$c] = $mark;
				}
			}
		}
		
		// Rebuild the head row tree with the updated col values
		$t->_makeTreeHeadRows();
		$t->_initGroupings();
		return $t;
	}
	
	/**
	 * Gives an id which identifies which column this datum should go in
	 * Also sets up various info to help organise columns later
	 * @param $data array  The data for the mark whose column id is sought
	 */
	function _getColId( $data )
	{
		static $pInstances = null;
		if( is_null($pInstances) ) {
			$db = &JFactory::getDBO();
			$query = 'SELECT * FROM #__apoth_tt_pattern_instances';
			$db->setQuery( $query );
			$pInstances = $db->loadAssocList( 'id' );
		}
		$time = strtotime( $data['date'].' '.$data['time'] );
		$w = date( 'W', $time );
		if( !isset($this->_colStruct[$w][$data['time']]) ) {
			$m = date( 'm', $time );
			$T = $pInstances[$data['pattern_instance']]['start'];
			$y = date( 'Y', $time );
			$this->_colStruct[$w][$data['time']] = $this->_cId;
			
			if( !isset($this->_colTags['time' ][$time]                                 ) ) { $this->_colTags['time' ][$time]                                  = 0; }
			if( !isset($this->_colTags['day_section_time'][$data['time']]              ) ) { $this->_colTags['day_section_time'][$data['time']]               = 0; }
			if( !isset($this->_colTags['day_section_short'][$data['day_section_short']]) ) { $this->_colTags['day_section_short'][$data['day_section_short']] = 0; }
			if( !isset($this->_colTags['date' ][$data['date']]                         ) ) { $this->_colTags['date' ][$data['date']]                          = 0; }
			if( !isset($this->_colTags['week' ][$w]                                    ) ) { $this->_colTags['week' ][$w]                                     = 0; }
			if( !isset($this->_colTags['month'][$m]                                    ) ) { $this->_colTags['month'][$m]                                     = 0; }
			if( !isset($this->_colTags['term' ][$T]                                    ) ) { $this->_colTags['term' ][$T]                                     = 0; }
			if( !isset($this->_colTags['year' ][$y]                                    ) ) { $this->_colTags['year' ][$y]                                     = 0; }
			$this->_colTags['time' ][$time]++;
			$this->_colTags['day_section_time'][$data['time']]++;
			$this->_colTags['day_section_short'][$data['day_section_short']]++;
			$this->_colTags['date' ][$data['date']]++;
			$this->_colTags['week' ][$w]++;
			$this->_colTags['month'][$m]++;
			$this->_colTags['term' ][$T]++;
			$this->_colTags['year' ][$y]++;
			$this->_allCols[$this->_cId] = array(
				  'time'             =>$time
				, 'day_section'      =>$data['day_section']
				, 'day_section_time' =>$data['time']
				, 'day_section_short'=>$data['day_section_short']
				, 'date'             =>$data['date']
				, 'week'             =>$y.$w
				, 'wb'               =>date( 'M dS', ($time - (86400 * (date( 'N', $time ) - 1))) ) // go back as many days as we're into the week (86400 s/day)
				, 'month'            =>$y.$m
				, 'term'             =>$T
				, 'term_name'        =>$pInstances[$data['pattern_instance']]['description_short']
				, 'term_name_full'   =>$pInstances[$data['pattern_instance']]['description']
				, 'year'             =>$y
				, '_enabled'         =>true
				, '_statutory'       =>$data['statutory']
				);
			
			$this->_cId++;
		}
		return $this->_colStruct[$w][$data['time']];
	}
	
	/**
	 * Gives an id which identifies which column these tags refer to
	 * Also sets up various info to help organise columns later
	 * @param $tags array  The tags whose column id is sought
	 */
	function _getColIdTagged( $tags )
	{
		if( !isset($this->_colStruct[$tags['week']][$tags['day_section_time']]) ) {
			$this->_colStruct[$tags['week']][$tags['day_section_time']] = $this->_cId;
			
			if( !isset($this->_colTags['time' ][$tags['time']]                         ) ) { $this->_colTags['time' ][$tags['time']]                          = 0; }
			if( !isset($this->_colTags['day_section_time'][$tags['day_section_time']]  ) ) { $this->_colTags['day_section_time'][$tags['day_section_time']]   = 0; }
			if( !isset($this->_colTags['day_section_short'][$tags['day_section_short']]) ) { $this->_colTags['day_section_short'][$tags['day_section_short']] = 0; }
			if( !isset($this->_colTags['date' ][$tags['date' ]]                        ) ) { $this->_colTags['date' ][$tags['date' ]]                         = 0; }
			if( !isset($this->_colTags['week' ][$tags['week' ]]                        ) ) { $this->_colTags['week' ][$tags['week' ]]                         = 0; }
			if( !isset($this->_colTags['month'][$tags['month']]                        ) ) { $this->_colTags['month'][$tags['month']]                         = 0; }
			if( !isset($this->_colTags['term' ][$tags['term' ]]                        ) ) { $this->_colTags['term' ][$tags['term' ]]                         = 0; }
			if( !isset($this->_colTags['year' ][$tags['year' ]]                        ) ) { $this->_colTags['year' ][$tags['year' ]]                         = 0; }
			$this->_colTags['time' ][$tags['time']]++;
			$this->_colTags['day_section_time'][$tags['day_section_time']]++;
			$this->_colTags['day_section_short'][$tags['day_section_short']]++;
			$this->_colTags['date' ][$tags['date' ]]++;
			$this->_colTags['week' ][$tags['week' ]]++;
			$this->_colTags['month'][$tags['month']]++;
			$this->_colTags['term' ][$tags['term' ]]++;
			$this->_colTags['year' ][$tags['year' ]]++;
			$this->_allCols[$this->_cId] = array(
				  'time'             =>$tags['time'             ]
				, 'day_section'      =>$tags['day_section'      ]
				, 'day_section_time' =>$tags['day_section_time' ]
				, 'day_section_short'=>$tags['day_section_short']
				, 'date'             =>$tags['date'             ]
				, 'week'             =>$tags['week'             ]
				, 'wb'               =>$tags['wb'               ]
				, 'month'            =>$tags['month'            ]
				, 'term'             =>$tags['term'             ]
				, 'term_name'        =>$tags['term_name'        ]
				, 'term_name_full'   =>$tags['term_name_full'   ]
				, 'year'             =>$tags['year'             ]
				, '_enabled'         =>true
				, '_statutory'       =>$tags['_statutory'       ]
				);
			
			$this->_cId++;
		}
		return $this->_colStruct[$tags['week']][$tags['day_section_time']];
	}
	
	/**
	 * Gives an id which identifies which row this datum should go in
	 * Also sets up various info to help organise rows later
	 * @param $data array  The data for the mark whose row id is sought
	 */
	function _getRowId( $data )
	{
		if( isset($data['date']) && isset($data['time']) ) {
			$time = strtotime( $data['date'].' '.$data['time'] );
			$d = date( 'w', $time );
			$dn = '<!--'.date( 'w', $time ).'-->'.date( 'D', $time );
		}
		else {
			$d = null;
			$dn = null;
		}
		
		if( !isset($this->_rowStruct[$data['person_id']][$d]) ) {
			$this->_rowStruct[$data['person_id']][$d] = $this->_rId;
			
			$this->_rowTags['pupil'][$data['person_id']][] = $this->_rId;
			$this->_rowTags['day'  ][$d                ][] = $this->_rId;
			$this->_allRows[$this->_rId] = array(
				  'year'      =>$data['year']
				, 'group'     =>$data['group_id']
				, 'group_name'=>$data['group_name']
				, 'pupil'     =>$data['person_id']
				, 'pupil_name'=>ApotheosisLib::nameCase( 'pupil', $data['title'], $data['firstname'], $data['middlenames'], $data['surname'] )
				, 'multi'     =>$data['multi']
				, 'day'       =>$d
				, 'day_name'  =>$dn
				);
			
			$this->_rId++;
		}
		return $this->_rowStruct[$data['person_id']][$d];
	}
	
	/**
	 * Gives an id which identifies which row these tags refer to
	 * Also sets up various info to help organise rows later
	 * @param $tags array  The tags whose row id is sought
	 */
	function _getRowIdTagged( $tags )
	{
		if( !isset($this->_rowStruct[$tags['pupil']][$tags['day']]) ) {
			$this->_rowStruct[$tags['pupil']][$tags['day']] = $this->_rId;
			
			$this->_rowTags['pupil'][$tags['pupil']][] = $this->_rId;
			$this->_rowTags['day'  ][$tags['day'  ]][] = $this->_rId;
			$this->_allRows[$this->_rId] = array(
				  'year'      =>$tags['year'      ]
				, 'group'     =>$tags['group'     ]
				, 'group_name'=>$tags['group_name']
				, 'pupil'     =>$tags['pupil'     ]
				, 'pupil_name'=>$tags['pupil_name']
				, 'multi'     =>$tags['multi'     ]
				, 'day'       =>$tags['day'       ]
				, 'day_name'  =>$tags['day_name'  ]
				);
			
			$this->_rId++;
		}
		return $this->_rowStruct[$tags['pupil']][$tags['day']];
	}
	
	
	/**
	 * Initialises the groupings which will be used to group the rows.
	 * These equates to toggles in the model.
	 * We want 2 levels of rows from (and including) the first with multiple values on
	 * and at least one (most specific if no others found)
	 * Groupings which would place a node without a corresponding row as root to be disabled
	 */
	function _initGroupings()
	{
		$row = &$this->_rowStruct;
		reset( $this->_groupings );
		$g = key( $this->_groupings );
		
		$useMax = 2;
		$used = 0;
		$donePupil = false;
		do {
			$use = ( (count($row) > 1) );
			$row = &$row[key($row)]; // Move down to the next level of rows
			$use = $use || !is_array($row);
			
//			if( !$donePupil ) {
//				$use = $donePupil = ( $g == 'pupil' );
//			}
			
			if( !$use ) {
				if( isset($row['']) ) {
					$this->_groupings[$g] = false;
				}
				else {
					$this->_groupings[$g] = null;
				}
			}
			else {
				$used++;
				$this->_groupings[$g] = true;
			}
			
			// Move on to the next grouping down in the grouping list
			next($this->_groupings);
			$g = key($this->_groupings);
		} while( ($used < $useMax) && !is_null($g) && !is_null($row) );
	}
	
	/**
	 * Sweep through the marksheet and recalculate the aggregate rows and all row summaries
	 */
	function resetAggregateRows()
	{
		$tmp = array();
		foreach( $this->_markRows as $row=>$cols ) {
			foreach( $cols as $col=>$v ) {
				if( $v['type'] == 'm' ) {
					$this->_markRows[$row][$col]['marks'] = array();
					$this->_markRows[$row][$col]['total'] = 0;
				}
				elseif( $v['type'] == 'sum' ) {
					$this->_markRows[$row][$col]['marks'] = array();
					$this->_markRows[$row][$col]['total'] = 0;
				}
				elseif( $v['type'] == 's' ) {
					$p = $this->_allRows[$row]['pupil'];
					$tmpRowP = $this->_rowStruct[$p][''];
					
					$mv = $v['mark']->getValue();
					
					$tmp[$tmpRowP][$col]['marks'][$mv]++;
					$tmp[$tmpRowP][$col]['total']++;
					
					$tmp[$tmpRowP]['summary']['marks'][$mv]++;
					$tmp[$tmpRowP]['summary']['total']++;
				}
			}
		}
		foreach( $tmp as $row=>$cols ) {
			foreach( $cols as $col=>$data ) {
				$this->_markRows[$row][$col]['marks'] = $data['marks'];
				$this->_markRows[$row][$col]['total'] = $data['total'];
			}
		}
	}
}
?>