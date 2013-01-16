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

/**
 * Timetable Admin Synch Controller
 *
 * @author     Punnet - Arc Team
 * @package    Arc
 * @subpackage Timetable
 * @since      1.6
 */
class TimetableAdminControllerSynch extends TimetableAdminController
{
	/**
	 * Default method
	 */
	function display()
	{
		$model = &$this->getModel( 'synch' );
		$view = &$this->getView( 'synch', 'html' );
		
		$view->setModel( $model, true );
		$view->display();
	}
	
	/**
	 * Queues up the calls needed to get the data to import
	 */
	function import()
	{
		$arcParams = &JComponentHelper::getParams( 'com_arc_core' );
		$src = $arcParams->get('ext_source');
		$db = &JFactory::getDBO();
		$db->setQuery( 'SELECT '.$db->nameQuote('name').' FROM #__apoth_sys_data_sources WHERE '.$db->nameQuote( 'id' ).' = '.$db->Quote( $src ) );
		$srcName = $db->loadResult();
		
		$params = JRequest::getVar( 'params' );
		$params['_subclass'] = $src;
		$complete = $params['complete'];
		
		$fromDate = ( ($params['date_from'] == '') ? date('Y-m-d', strtotime('-2 days')) : $params['date_from'] ).' 00:00:00';
		$toDate =   ( ($params['date_to']   == '') ? date('Y-m-d', strtotime('+1 day'))  : $params['date_to']   ).' 23:59:59';
		$effDate =  ( ($params['eff_date']  == '') ? date('Y-m-d')                       : $params['eff_date']   ).' 00:00:00';
		
		switch( $srcName ) {
		case( 'MIStA - SIMS' ):
			$start = str_replace( ' ', 'T', $fromDate );
			$end = str_replace( ' ', 'T', $toDate );
			$eff = str_replace( ' ', 'T', $effDate );
			
			if( $params['tt_data'] == 1 ) {
				$id = ApotheosisData::_( 'core.addImportBatch', 'timetable', 'importTimetable', $params );
				if( $id === false ) {
					$r = false;
					break;
				}
				$r1 = ApotheosisData::_( 'core.addToImportQueue', $id, $src, 'arc_timetable_patterns', array('Start'=>$start, 'End'=>$end, 'complete'=>$complete) );
				$r2 = ApotheosisData::_( 'core.addToImportQueue', $id, $src, 'arc_timetable_instances', array('Start'=>$start, 'End'=>$end, 'complete'=>$complete) );
				$r3 = ApotheosisData::_( 'core.addToImportQueue', $id, $src, 'arc_timetable_classes', array('effective'=>$eff, 'complete'=>$complete) );
				$r = ($r1 && $r2 && $r3);
			}
			
			// pause to datestamp separate the import jobs
			sleep( 1 );
			
			if( $params['tt_enrolment'] == 1 ) {
				$id = ApotheosisData::_( 'core.addImportBatch', 'timetable', 'importEnrolments', $params );
				if( $id === false ) {
					$r = false;
					break;
				}
				$r1 = ApotheosisData::_( 'core.addToImportQueue', $id, $src, 'arc_timetable_members', array('effective'=>$eff, 'active_start'=>$start, 'active_end'=>$end, 'complete'=>$complete) );
		
				$r = $r1;
			}
			break;
		}
		
		global $mainframe;
		$mainframe->redirect( 'index.php?option=com_arc_core&view=synch', 'Import jobs added' );
	}
}
?>