<?php
/**
 * @package     Arc
 * @subpackage  Timetable
 * @copyright   Copyright (C) 2005 Punnet. All rights reserved. See COPYRIGHT_ARC.txt
 * @license     http://www.gnu.org/licenses/gpl.html GNU/GPL. See LICENSE_ARC.txt
 * Arc is free software: you can redistribute it and/or modify
 * it under the terms of version 3 of the GNU General Public License
 * as published by the Free Software Foundation.
 * The disclaimer of warranty as stated in the GPL applies to this program
 */

defined( '_JEXEC' ) or die( 'Restricted access' );

jimport( 'joomla.application.component.view' );

/**
 * Timetable Manager Enrolments View
 *
 * @author     David Swain <code@lightinthedark.org.uk>
 * @package    Apotheosis
 * @subpackage Timetable
 * @since      1.5
 */
class TimetableViewEnrolments extends JView 
{
	
//	var $_varMap = array('teachers'=>'Teachers', 'rooms'=>'Rooms', 'days'=>'Days', 'periods'=>'DaySections', 'courses'=>'Courses' );
	
	function display( $tpl = NULL )
	{
		$document = &JFactory::getDocument();
		$document->setTitle( JText::_( 'Timetable' ) );
		
		$this->_varMap['state'] = 'State';
		$this->_varMap['enrolments'] = 'Enrolments';
		$this->_varMap['courses'] = 'Courses';
		ApotheosisLib::setViewVars($this, $this->_varMap);		
		$this->state->search = false;
		
		
		foreach ($this->enrolments as $courseId=>$pupils) {
			echo '<h3>'.$this->courses[$courseId]->shortname.'</h3>';
			foreach ($pupils as $pId=>$pupil) {
				echo strtoupper($pupil->surname).', '.$pupil->firstname.'<br />';
			}
		}
		
/*		
		$this->timetable = ApotheosisLibArray::sortObjects( $this->timetable, array('pattern', 'day', 'start_time', 'surname') );
		foreach ($this->timetable as $k=>$v) {
			$tmp[$v->pattern.'-'.$v->day][$v->start_time] = $v;
		}
		$this->timetable = $tmp;
//		var_dump_pre($this->timetable);
		
		// for search form
		foreach ($this->teachers as $k=>$v) {
			$this->teachers[$k]->displayName = $v->firstname.' '.$v->surname;
		}
		
		parent::display();
*/
	}
	
	function loadPeriod( $pattern, $day, $daySection )
	{
		$item =& $this->timetable[$pattern.'-'.$day][$daySection];
		$this->assignRef('period', $item);
	}
	
}
?>