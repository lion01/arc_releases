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

jimport('joomla.application.component.controller');

/**
 * Timetable Admin Controller
 *
 * @author     David Swain
 * @package    Arc
 * @subpackage Attendance
 * @since      1.5
 */
class AttendancemanagerController extends JController
{
	function show()
	{
		$viewName = JRequest::getVar('view', 'settings');
		$model	= &$this->getModel( $viewName );
		$view	= &$this->getView( $viewName );
		
		$view->setModel( $model, true );
		$view->display();
	}
	
//* // 
	function save()
	{
		$redirect = 'save'.ucfirst( JRequest::getVar( 'view' ) );		
		$this->$redirect();
	}
	
	function export()
	{
		$redirect = 'import'.ucfirst( JRequest::getVar( 'view' ) );		
		$this->$redirect();
	}	
// */
	
	function add()
	{
		$redirect = 'add'.ucfirst( JRequest::getVar( 'view' ) );
		$this->$redirect();
	}
	
	function addMarks()
	{
		$model = &$this->getModel( 'marks' );
		$view = &$this->getView( 'marks' );
		
		$view->setModel( $model, true );
		$view->addMark();
	}
	
	function editMark()
	{
		$model = &$this->getModel( 'marks' );
		$view = &$this->getView( 'marks' );
		
		$type = JRequest::getVar('type', false);
		
		$model->setItems(array ('code'=>JRequest::getVar('code'), 'type'=>$type) );
		
		$view->setModel( $model, true );
		$view->editMark();
	}
/*	
	function saveSynch_Writes()
	{
		global $mainframe;
		
		$task = JRequest::getVar('task', 'save');
		$viewName = JRequest::getVar('view', 'Synch_Writes');
		$option	= JRequest::getVar( 'option', 'com_arc_attendance' );
		
		$model	= &$this->getModel( $viewName );
		$view	= &$this->getView( $viewName );
		
		$params = JRequest::getVar('params', array() );
		if(($viewName == 'Synch_Writes') && (!empty($params))) {
			
			$text = '';
			foreach ($params as $key=>$value) {
				$text .= $key.'='.$value."\n";
			}
			$model->saveParams($text);
			
		}
		$view->setModel( $model, true );
		
		$msg = JText::sprintf('Successfully Saved Synchronous Write Settings', $row->title);
		$mainframe->redirect('index.php?option=com_arc_attendance&view=synch_writes', $msg);
	}
*/
	/**
	 * Save an edited attendance mark, redirects back to attendance codes screen
	 */
	function saveMark()
	{
		global $mainframe;
		$model = &$this->getModel( 'marks' );
		$view = &$this->getView( 'marks' );
		
		if ($model->save()) {
			$message = 'Attendance code successfully updated';
		}
		else {
			$db = JFactory::getDBO();
			$message = 'There was a problem saving the requested changes:<br />'.$db->errorMsg();
		}

		$mainframe->redirect( 'index.php?option=com_arc_attendance&view='.JRequest::getVar('view'), $message );
	}
	
	function saveSettings()
	{
		global $mainframe;
		$viewName = JRequest::getVar('view', 'settings');
		$model = &$this->getModel( $viewName );
		$view = &$this->getView( $viewName );
		
		$params = JRequest::getVar('params');
		
		//$model->saveParams($text);
		$view->setModel( $model, true );
		
				//Start setting the response message
		$message = 'Saving the Settings';
		
		$model->saveParams($params);
		$view->setModel( $model, true );
		
		//Re-direct back to the admin screen
		$mainframe->redirect( 'index.php?option=com_arc_attendance&view='.$viewName, $message );
	}
	
	/**
	 * Saves the parameters from the Settings section of the database synchronisation section
	 * Redirects to the same screen, with the new settings stored in the database
	 */
	function saveSynch()
	{
		global $mainframe;
		$viewName = JRequest::getVar('view', 'synch');
		$model = &$this->getModel( $viewName );
		$view = &$this->getView( $viewName );
		
		$params = JRequest::getVar('params');
		
		$paramsObj = &JComponentHelper::getParams('com_arc_attendance');
		$mergeampm = $paramsObj->get('att_mergeampm', false);
		
		if($mergeampm != $params['att_mergeampm'])
		{
			$helper = &AttSynch::getInstance();
			if ($params['att_mergeampm'] == 1) {
				$helper->replaceMarks($params['internal_mark'], $params['external_am_mark'] , $params['external_pm_mark'], 1);
			}
			else {
				$helper->replaceMarks($params['internal_mark'], $params['external_am_mark'] , $params['external_pm_mark'], 0);
			}
		}
		
		//Start setting the response message
		$message = 'Saving the following Parameters:<br /><br />';

		$model->saveParams($params);
		$view->setModel( $model, true );
		
		//Complete the message with the current settings
		$message .= '<li>- Synchronous writes: '.(($params['synch_writes'] == 1) ? 'On' : 'Off').'</li>';
		$message .= '<li>- Merge AM / PM: '.(($params['att_mergeampm'] == 1) ? 'On' : 'Off').'</li>';
		$message .= '<li>- Present Mark: '.(($params['internal_mark'] !== '') ? $params['internal_mark'] : 'Not set').'</li>';
		
		//Re-direct back to the admin screen
		$mainframe->redirect( 'index.php?option=com_arc_attendance&view='.$viewName, $message );
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
		
		$fromDate = ( ($params['date_from'] == '') ? date('Y-m-d H:i:s') : $params['date_from'].' 00:00:00' );
		$toDate =   ( ($params['date_to']   == '') ? date('Y-m-d H:i:s') : $params['date_to']  .' 23:59:59' );
		
		switch( $srcName ) {
		case( 'MIStA - SIMS' ):
			// Separates the values by an extra day in each direction as the reporting tool
			// views dates as exclusive, not inclusive as the user may expect.
			$startDate = date( 'Y-m-d', (strtotime($fromDate) - 86400) );
			$endDate = date( 'Y-m-d', (strtotime($toDate) + 86400) );
			
			if( $params['att_codes'] == 1 ) {
				$id = ApotheosisData::_( 'core.addImportBatch', 'attendance', 'importCodes', $params );
				if( $id === false ) {
					$r = false;
					break;
				}
				$r1 = ApotheosisData::_( 'core.addToImportQueue', $id, $src, 'arc_student_attendance', array('Start'=>$startDate, 'End'=>$endDate, 'complete'=>$complete) );
				$r = $r1;
			}
			
			// pause to datestamp separate the import jobs
			sleep( 1 );
			
			if( $params['att_history'] == 1 ) {
				$id = ApotheosisData::_( 'core.addImportBatch', 'attendance', 'importAttendance', $params );
				if( $id === false ) {
					$r = false;
					break;
				}
				$r1 = ApotheosisData::_( 'core.addToImportQueue', $id, $src, 'arc_student_attendance', array('Start'=>$startDate, 'End'=>$endDate, 'complete'=>$complete) );
			
				$r = $r1;
			}
			break;
		}
		
		global $mainframe;
		$mainframe->redirect( 'index.php?option=com_arc_core&view=synch', 'Import jobs added' );
	}
	
	function import_OLD()
	{
		timer( 'import started' );
		global $mainframe;
		ob_start();
		$arcParams = &JComponentHelper::getParams('com_arc_core');
		$model = &$this->getModel( 'synch_'.$arcParams->get('ext_source') );
		$msg = '';
		
		if( $model !== false ) {
			$params = JRequest::getVar('params');
			$fromDate = ( ($params['date_from'] == '') ? date('Y-m-d H:i:s') : $params['date_from'].' 00:00:00' );
			$toDate =   ( ($params['date_to']   == '') ? date('Y-m-d H:i:s') : $params['date_to']  .' 23:59:59' );
			$model->setDates( $fromDate, $toDate );
			
			// Import attendance codes
			if( $params['att_codes'] == 1 ) {
				$tablesArray = array( '#__apoth_att_physical_meaning', '#__apoth_att_school_meaning', '#__apoth_att_statistical_meaning', '#__apoth_att_codes' );
				ApotheosisLibDb::disableDBChecks( $tablesArray );
				
				if( $params['truncate'] == 1 ) {
					$model->truncateCodes();
				}
				
				$model->importCodes();
				
				$mainframe->enqueueMessage( JText::_('Synchronisation Complete - Imported attendance codes') );
				
				ApotheosisLibDb::enableDBChecks( $tablesArray );
				timer('End of attendance codes');
			}
			
			// Import the recorded attendance for pupils
			if( $params['att_history'] == 1 ) {
				$tablesArray = array( '#__apoth_att_dailyatt', '#__apoth_att_dailyincidents' );
				ApotheosisLibDb::disableDBChecks( $tablesArray );
				
				if ($params['truncate'] == 1) {
					$model->truncateAttendance();
				}
				
				$model->importAttendance();
				
				$mainframe->enqueueMessage( JText::_('Synchronisation Complete - Imported attendance history '.((!is_null($from_date)) && (!is_null($to_date)) ? 'from '.$from_date.' to '.$to_date : '')) );
				
				ApotheosisLibDb::enableDBChecks( $tablesArray );
				timer('End of attendance history');
			}
		}
		else {
			echo 'Could not create synchronisation model for external source '.$arcParams->get('ext_source').'<br />';
			echo 'Plese check your settings in the Arc core settings page';
		}
			
		timer('did it all');
		timer(false, false, 'print');
		
		$msg = ob_get_clean().$msg;
		$mainframe->redirect('index.php?option=com_arc_attendance&view=synch', $msg);
	}
	
	// **** DANGER! Unfinished function
	function exportSynch()
	{
		global $mainframe;
		$viewName = JRequest::getVar('view', 'synch');
		$model = &$this->getModel( $viewName );
		$view = &$this->getView( $viewName );
		
		$params = JRequest::getVar('params');
		
		if( $params['att_codes'] == 1 ) {
			echo 'Importing attendance codes<br />';
		}
		if( $params['date_from'] !== '' ) {
			echo 'date from: '.$params['date_from'].'<br />';
		}
		if( $params['date_to'] !== '' ) {
			echo 'date to: '.$params['date_to'].'<br />';
		}
		if( $params['att_history'] == 1 ) {
			echo 'Importing attendance history<br />';
		}
	}
	
	/**
	 * Toggles whether an attendance code is a commonly used code or not
	 */
	function toggleCommon()
	{
		global $mainframe;
		$db = &JFactory::getDBO();

		$cid	= JRequest::getVar( 'eid', array(), 'post', 'array' );
		$option	= JRequest::getCmd( 'option' );
		$view = JRequest::getVar( 'view' );
		$code = array_keys($cid);
		$type = array_keys($cid[$code[0]]);
		
		$query = 'SELECT * FROM #__apoth_att_codes WHERE `code` = "'.$code[0].'" AND `type` = "'.$type[0].'"';
		$db->setQuery($query);
		$codeDetails = $db->loadObject();
		
		$is_common = (($codeDetails->is_common) ? 0 : 1);

		$query = 'UPDATE #__apoth_att_codes SET `is_common` = '.$is_common.' WHERE `code` = "'.$code[0].'" AND `type` = "'.$type[0].'"';
		$db->setQuery($query);
		$db->query();
		
		$msg = 'Changed is_common to '.(($is_common) ? "Yes" : "No");

		$mainframe->redirect('index.php?option='.$option.'&view='.$view, $msg);
	}
	
	/**
	 * Toggles whether an attendance code is applicable all day (for future marking)
	 */
	function toggleAllday()
	{
		global $mainframe;
		$db = &JFactory::getDBO();

		$cid	= JRequest::getVar( 'eid', array(), 'post', 'array' );
		$option	= JRequest::getCmd( 'option' );
		$view = JRequest::getVar( 'view' );
		$code = array_keys($cid);
		$type = array_keys($cid[$code[0]]);
		
		$query = 'SELECT * FROM #__apoth_att_codes WHERE `code` = "'.$code[0].'" AND `type` = "'.$type[0].'"';
		$db->setQuery($query);
		$codeDetails = $db->loadObject();
		
		$allDay = (($codeDetails->apply_all_day) ? 0 : 1);

		$query = 'UPDATE #__apoth_att_codes SET `apply_all_day` = '.$allDay.' WHERE `code` = "'.$code[0].'" AND `type` = "'.$type[0].'"';
		$db->setQuery($query);
		$db->query();
		
		$msg = 'Changed All day to '.(($allDay) ? "Yes" : "No");

		$mainframe->redirect('index.php?option='.$option.'&view='.$view, $msg);
	}
	
}
?>