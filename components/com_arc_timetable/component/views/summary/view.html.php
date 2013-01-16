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
 * Timetable Manager Summary View
 *
 * @author     David Swain <code@lightinthedark.org.uk>
 * @package    Apotheosis
 * @subpackage Timetable
 * @since      1.5
 */
class TimetableViewSummary extends JView 
{
	
//	var $_varMap = array('teachers'=>'Teachers', 'rooms'=>'Rooms', 'days'=>'Days', 'periods'=>'DaySections', 'courses'=>'Courses' );
	
	function display( $tpl = NULL )
	{
		$document = &JFactory::getDocument();
		$document->setTitle( JText::_('Timetable') );
		
		$this->_varMap['state'] = 'State';
		$this->_varMap['timetable'] = 'Timetable';
		$this->_varMap['noSessions'] = 'NoSessions';
		ApotheosisLib::setViewVars($this, $this->_varMap);
		
		$this->timetable = ApotheosisLibArray::sortObjects( $this->timetable, array('pattern', 'day', 'start_time', 'surname') );
		$tmp = array();
		if( is_array($this->timetable) ) {
			foreach ($this->timetable as $k=>$v) {
				$tmp[$v->pattern.'-'.$v->day][$v->start_time] = $v;
			}
		}
		$this->timetable = $tmp;
		
		parent::display();
	}
	
	function loadPeriod( $pattern, $day, $daySection )
	{
		$item = &$this->timetable[$pattern.'-'.$day][$daySection];
		$this->assignRef('period', $item);
	}
}
?>
