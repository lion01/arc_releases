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

jimport('joomla.application.component.view');

/**
 * Attendance Manager Ereg View
 *
 * @author		lightinthedark <code@lightinthedark.org.uk>
 * @package		Apotheosis
 * @subpackage	Attendance
 * @since 0.1
 */
class AttendanceViewEreg extends JView 
{
	/**
	 * Constructor
	 */
	function __construct( $config = array() )
	{
		parent::__construct( $config );
		$this->_varMap = array('state'=>'State');
		$this->_ancestors = $this->_siblings = array();
	}
	
	/**
	 * Displays a generic page
	 * (for when there are no actions or selected registers)
	 *
	 * @param string $template  Optional name of the template to use
	 */
	function display( $tpl = NULL )
	{
		ApotheosisLib::setViewVars($this, $this->_varMap);

		$this->layout = false;
		parent::display( $tpl );
	}
	
	/**
	 * Displays a single class register
	 * (for when there is a single register selected)
	 *
	 * @param string $template  Optional name of the template to use
	 */
	function register( $tpl = NULL )
	{
		$document = &JFactory::getDocument();
		$document->setTitle($document->getTitle().' - '.JText::_( 'Register' ));
		
		$this->_varMap['noMark'] = 'noMark';
		$this->_varMap['common_attendanceMarks'] = 'commonMarks';
		$this->_varMap['uncommon_attendanceMarks'] = 'uncommonMarks';
		ApotheosisLib::setViewVars($this, $this->_varMap);
		
		if( !empty($this->uncommon_attendanceMarks) ) {
			$blank = new stdClass();
			$blank->code = '';
			array_unshift( $this->uncommon_attendanceMarks, $blank );
		}
		
		$noteModel = &$this->getModel( 'notes' );
		$notes = $noteModel->getNotes();
		
		$this->notes = array();
		foreach( $notes as $id=>$note ) {
			if( is_null($note->delivered_on) ) {
				$this->notes[$note->pupil_id][$id] = $note;
			}
		}
		
		$model = &$this->getModel();
		$tmp = &$model->getRegisters();
		$this->register = &$tmp[reset(array_keys($tmp))];
		$this->assignRef( 'course', $this->register->course );
		
		$paramsObj = &JComponentHelper::getParams('com_arc_attendance');
		$showRecent = $paramsObj->get( 'recent_marks', false );
		$showIncidents = $paramsObj->get( 'incidents', false );
		
		$this->assignRef( 'showRecent', $showRecent);
		$this->assignRef( 'showIncidents', $showIncidents);
		
		$this->layout = 'register';
		parent::display( $tpl );
	}
	
	/**
	 * Displays a list of registers to pick from
	 * (for when we have multiple possible registers)
	 *
	 * @param string $template  Optional name of the template to use
	 */
	function classList( $tpl = NULL )
	{
		$document = &JFactory::getDocument();
		$document->setTitle($document->getTitle().' - '.JText::_( 'Class list' ));
		
		$scope = JRequest::getCmd( 'scope' );
		
		$this->_varMap['registers'] = 'Registers';
		ApotheosisLib::setViewVars( $this, $this->_varMap );
		
		foreach ($this->registers as $k=>$v) {
			$v->composite_id = $v->getCompId();
			if($scope == 'history') {
				$v->display_name = $v->date.' - '.$v->daySection.' - '.$v->course->fullname;
			}
			else {
				$v->display_name = ApotheosisLibCycles::cycleDayToDow($v->day, $v->pattern).' - '.$v->daySection.' - '.$v->course->fullname;
			}
			$this->registers[$k] = $v;
		}
		
		$this->layout = 'class';
		parent::display( $tpl );
	}
	
	/**
	 * Displays a list of children, in order to then add them to the register
	 *
	 * @param string $template  Optional name of the template to use
	 */
	function adhoc( $tpl = NULL )
	{
		$document = &JFactory::getDocument();
		$document->setTitle( $document->getTitle().' - '.JText::_( 'Add adhoc pupils' ) );
		
		$this->_varMap['courses'] = 'Courses';
		$this->_varMap['registers'] = 'Registers';
		ApotheosisLib::setViewVars( $this, $this->_varMap );
		
		foreach ( $this->registers as $k=>$v ) {
			$this->regId = $k;
			$this->pupils = $v->getPupils();
			$this->adhocPupils = $v->getAdhocPupils();
			$this->allPupils = $v->getOtherPupils();
			parent::display( 'addhoc' );
		}
	}
	
	
	function loadHeading( &$reg, $scope, $ancestorArr = array() )
	{
		if( !isset($this->_recentHeadings[$scope])) {
			$this->_recentHeadings = $reg->getMarksRecentHeadings();
		}
		$reSearch = ($ancestorArr !== $this->_ancestors);
		if (is_array($ancestorArr) && (count($ancestorArr) > 0)) {
			$this->_ancestors = $ancestorArr;
			$indexStr = '[\''.implode('\'][\'', $ancestorArr).'\']';
		}
		else {
			$this->_ancestors = array();
			$ancestorArr = array();
			$indexStr = '';
		}
		
		$visited = false;
		if($reSearch) {
			$str = '$this->_siblings = &$this->_recentHeadings[\''.$scope.'\']'.$indexStr.';';
			eval($str);

			if (!is_array($this->_ancestorsVisited) || (array_search($indexStr, $this->_ancestorsVisited) === false) ) {
				$this->_ancestorsVisited[$indexStr] = $indexStr;
			}
			else {
				$visited = true;
			}
		}
		else {
			$visited = true;
		}
		
		if (is_array($this->_siblings)) {
			if ($visited) {
				$tmp = next($this->_siblings);
			}
			else {
				ksort($this->_siblings);
				$tmp = reset($this->_siblings);
			}
			array_push($ancestorArr, key($this->_siblings)); // to be used as id later
		}
		else {
			if ($visited) {
				$tmp = false;
			}
			else {
				$tmp = $this->_siblings;
			}
		}
		if($tmp === false) {
			$this->_ancestors = NULL;
			unset($this->_ancestorsVisited[$indexStr]);
			
			return false;
		}
		else {
			// construction of object
			// starting with the id as an array
			if(is_array($tmp)) {
				$obj->id = $ancestorArr;
				$obj->text = key($this->_siblings);
				$obj->colspan = ApotheosisLibArray::countMulti($tmp, true);
			}
			else {
				$obj->id = $ancestorArr;
				$obj->text = $tmp;
				$obj->colspan = 1;
			}
			
			return $obj;
		}
	}
	
	function loadHistoryHeading( &$reg, $ancestorArr = array() )
	{
		if( !isset($this->_historyHeadings)) {
			$this->_historyHeadings = $reg->getMarksHistoryHeadings();
		}
		$reSearch = ($ancestorArr !== $this->_ancestors);
		if (is_array($ancestorArr) && (count($ancestorArr) > 0)) {
			$this->_ancestors = $ancestorArr;
			$indexStr = '[\''.implode('\'][\'', $ancestorArr).'\']';
		}
		else {
			$this->_ancestors = array();
			$ancestorArr = array();
			$indexStr = '';
		}
		
		$visited = false;
		if($reSearch) {
			$str = '$this->_siblings = &$this->_recentHeadings'.$indexStr.';';
			eval($str);

			if (!is_array($this->_ancestorsVisited) || (array_search($indexStr, $this->_ancestorsVisited) === false) ) {
				$this->_ancestorsVisited[$indexStr] = $indexStr;
			}
			else {
				$visited = true;
			}
		}
		else {
			$visited = true;
		}
		
		if (is_array($this->_siblings)) {
			if ($visited) {
				$tmp = next($this->_siblings);
			}
			else {
				ksort($this->_siblings);
				$tmp = reset($this->_siblings);
			}
			array_push($ancestorArr, key($this->_siblings)); // to be used as id later
		}
		else {
			if ($visited) {
				$tmp = false;
			}
			else {
				$tmp = $this->_siblings;
			}
		}
		
		if($tmp === false) {
			$this->_ancestors = NULL;
			unset($this->_ancestorsVisited[$indexStr]);
			
			return false;
		}
		else {
			// construction of object
			// starting with the id as an array
			if(is_array($tmp)) {
				$obj->id = $ancestorArr;
				$obj->text = key($this->_siblings);
				$obj->colspan = ApotheosisLibArray::countMulti($tmp, true);
			}
			else {
				$obj->id = $ancestorArr;
				$obj->text = $tmp;
				$obj->colspan = 1;
			}
			
			return $obj;
		}
	
	}
	
	/**
	 * Loads the first or next pupil in the given register
	 *
	 * @param object $register  The register from which we want to load pupils
	 * @return mixed  The pupil data object, or false on failure
	 */
	function loadPupil( &$reg )
	{
		$id = $reg->getCompId();
		if( !isset($this->_pupilMarkers[$id]) ) {
			$this->_marks[$id] = $reg->getMarks();
			$this->_marksRecent[$id] = $reg->getMarksRecent();
			$this->_incidents[$id] = $reg->getIncidents();
			$this->_pupils[$id] = $reg->getPupils();
			$this->_pupilKeys[$id] = array_keys($this->_pupils[$id]);
			$this->_numPupils[$id] = count( $this->_pupils[$id] );
			$this->_pupilMarkers[$id] = 0;
//			echo 'regular recent marks: ';var_dump_pre($this->_marksRecent);
		}
		
		return $this->_loadAnyPupil( $this->_marks[$id], $this->_marksRecent[$id], $this->_incidents[$id], $this->_pupils[$id], $this->_pupilKeys[$id], $this->_numPupils[$id], $this->_pupilMarkers[$id], false );
	}
	
	/**
	 * Loads the first or next adhoc pupil in the given register
	 *
	 * @param object $register  The register from which we want to load adhoc pupils
	 * @return mixed  The adhoc pupil data object, or false on failure
	 */
	function loadAdhocPupil( &$reg )
	{
		$id = $reg->getCompId();
		if( !isset($this->_ApupilMarkers[$id]) ) {
			$this->_Amarks[$id] = $reg->getMarks();
			$this->_AmarksRecent[$id] = $reg->getMarksRecent();
			$this->_Aincidents[$id] = $reg->getIncidents();
			$this->_Apupils[$id] = $reg->getAdhocPupils();
			$this->_ApupilKeys[$id] = array_keys($this->_Apupils[$id]);
			$this->_AnumPupils[$id] = count( $this->_Apupils[$id] );
			$this->_ApupilMarkers[$id] = 0;
//			echo 'adhoc recent marks: ';var_dump_pre($this->_AmarksRecent);
		}
		return $this->_loadAnyPupil( $this->_Amarks[$id], $this->_AmarksRecent[$id], $this->_Aincidents[$id], $this->_Apupils[$id], $this->_ApupilKeys[$id], $this->_AnumPupils[$id], $this->_ApupilMarkers[$id], true );
	}
	
	/**
	 * Loads the indicated pupil from the list, and includes its mark in the data object
	 *
	 * @param array $marks  All the marks in the register, indexed by pupil id
	 * @param array $recent  All the recent marks in the register, indexed by type then pupil id
	 * @param array $pupils  All the pupil objects in the register, indexed by pupil id
	 * @param array $pupilKeys  All the pupil ids in the register, sequentially indexed
	 * @param int $numPupils  Count of number of pupils
	 * @param int $marker  The index in the pupil keys of the next pupil key to load from the pupils list
	 * @param boolean $adhoc  Are we dealing with adhoc pupils?
	 * @return mixed  The pupil data object, or false on failure
	 */
	function _loadAnyPupil( &$marks, &$recent, &$incidents, &$pupils, &$pupilKeys, &$numPupils, &$marker, $adhoc )
	{
		if ($marker < $numPupils) {
			$pId = $pupilKeys[$marker++];
			$pupil = $pupils[$pId];
			$pupil->person_id = $pId;
			$pupil->mark = ( isset($marks[$pId]) ? $marks[$pId] : null );
			$pupil->adhoc = $adhoc;
			$pupil->history = ( isset($recent['course'][$pId]) ? $recent['course'][$pId] : null );
			$pupil->recent = ( isset($recent['day'][$pId]) ? $recent['day'][$pId] : null );
			$pupil->incHistory = (isset($incidents['course'][$pId]) ? $incidents['course'][$pId] : array() );
			$pupil->incidents  = (isset($incidents['day'][$pId]   ) ? $incidents['day'][$pId]    : array() );
			if (!is_array($pupil->recent)) { $pupil->recent = array(); }
			if (!is_array($pupil->history)) { $pupil->history = array(); }
			
			foreach ($pupil->recent as $k=>$v) {
				foreach($v as $v2=>$info){
					$mark = $info['code'];
					if (array_key_exists($mark, $this->common_attendanceMarks)) {
						$pupil->recent[$k][$v2] = $this->common_attendanceMarks[$mark];
					}
					elseif (array_key_exists($mark, $this->uncommon_attendanceMarks)) {
						$pupil->recent[$k][$v2] = $this->uncommon_attendanceMarks[$mark];
					}
					
					if( isset($pupil->recent[$k][$v2]) && is_object($pupil->recent[$k][$v2]) ) {
						$pupil->recent[$k][$v2]->group = $info['group'];
					}
					else {
						unset( $pupil->recent[$k][$v2] );
					}
				}
			}
			foreach ($pupil->history as $k=>$v) {
				foreach($v as $v2=>$info) {
					$mark = $info['code'];
					if (array_key_exists($mark, $this->common_attendanceMarks)) {
						$pupil->history[$k][$v2] = $this->common_attendanceMarks[$mark];
					}
					elseif (array_key_exists($mark, $this->uncommon_attendanceMarks)) {
						$pupil->history[$k][$v2] = $this->uncommon_attendanceMarks[$mark];
					}
					
					if( isset($pupil->history[$k][$v2]) && is_object( $pupil->history[$k][$v2] ) ) {
						$pupil->history[$k][$v2]->group = $info['group'];
					}
					else {
						unset( $pupil->history[$k][$v2] );
					}
				}
			}
			$this->assignRef('pupil', $pupil);
			return true;
		}
		else {
			$pupil = NULL;
			$this->assignRef('pupil', $pupil);
			return false;
		}
	}
	
	function codeKey()
	{
		$this->_varMap['attendanceMarks'] = 'AllAttendanceMarks';
		ApotheosisLib::setViewVars($this, $this->_varMap);
		
		parent::display('code_key');
	}
}
?>
