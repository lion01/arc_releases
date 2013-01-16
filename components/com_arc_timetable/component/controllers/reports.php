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

require_once( JPATH_SITE.DS.'components'.DS.'com_arc_attendance'.DS.'helpers'.DS.'data_access.php' );

class TimetableControllerReports extends TimetableController
{
	function display()
	{
		timer('displaying');
//		$vars = $this->_getPassthrough();
		
		$scope  = JRequest::getWord( 'scope', false );
		$report = JRequest::getWord( 'report', false );
		
		$model = &$this->getModel( 'reports' );
		$view  = &$this->getView ( 'reports', JRequest::getVar('format', 'html') );
		
		$view->setModel( $model, true );
		$view->_link = $this->_getLink();
		$view->setModel( $model, true );
		
		switch( strtolower($report) ) {
		case( 'today' ):
			$datasets = array();
			
			if( $scope == 'person' ) {
				$pId = JRequest::getVar( 'pId', false );
				if( $pId !== false ) {
					$datasets[0] = array('person'=>$pId);
				}
			}
			
			$view->today( $datasets );
			break;
		}
	}
}
?>