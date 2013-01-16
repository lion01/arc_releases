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
class TimetableViewReports extends JView 
{
	function today( $datasets )
	{
		$this->_datasets = &$datasets;
		$this->model = &$this->getModel( 'reports' );
		$this->extLink = 'http://vle.wildern.hants.sch.uk/course/view.php?id=';
		
		$requirements['date'] = date( 'Y-m-d' );
		foreach( $this->_datasets as $this->dataKey=>$dataset ) {
			foreach( $dataset as $col=>$val ) {
				$requirements[$col] = $val;
			}
			$this->attMarks[$this->dataKey] = ApotheosisAttendanceData::getAttendance( $requirements, 'day_section' );
		}
		
		$this->setLayout( 'panel' );
		parent::display();
	}
	
}
?>
