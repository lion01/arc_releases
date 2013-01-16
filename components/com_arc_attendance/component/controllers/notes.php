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
jimport('joomla.application.helper');

/**
 * Attendance Controller Reg
 *
 * @author		lightinthedark <code@lightinthedark.org.uk>
 * @package		Apotheosis
 * @subpackage	Attendance
 * @since 0.1
 */
class AttendanceControllerNotes extends AttendanceController
{
	function __construct( $config = array() )
	{
		parent::__construct( $config );
		$this->registerTask( '', 'edit' );
	}
	
	function _display( $task, $search )
	{
		$model = &$this->getModel( 'notes' );
		$view  = &$this->getView ( 'notes', 'html' );
		
		$pupil = JRequest::getVar( 'pupil', false );
		
		$model->setState('search', $search );
		$model->setPupilNotes( $pupil );
		
		$view->pupil = $pupil;
		$view->link = $this->_getLink().'&task2='.$task;
		$view->setModel( $model, true );
		$view->$task();
		
		$this->saveModel();
	}
	
	/**
	 * Display a page on which notes can be added, removed and editted
	 */
	function edit()
	{
		$this->_display( 'edit', true );
	}
	
	
	/**
	 * Display a page showing the current (and optionally any old) note(s) for a pupil
	 */
	function display()
	{
		global $mainframe;
		$tmpl = $mainframe->getTemplate();
		$doc = &JFactory::getDocument();
		$doc->addStyleSheet( 'templates'.DS.$tmpl.DS.'css'.DS.'nomenu.css' );
		
		$this->_display( 'display', false );
	}
	
	function search()
	{
		$t = JRequest::getVar( 'task2', 'display' );
		$this->$t();
	}
	
	
	/**
	 * Saves the note(s) (including delivery status) and redirects to the previously displayed page
	 * calls $this->_save
	 */
	function save()
	{
		$this->_save( 'save' );
	}
	
	/**
	 * Does the actual saving
	 */
	function _save( $task )
	{
		ob_start();
		global $mainframe;
		
		$model = &$this->getModel( 'notes' );
		
		$notes = $model->getNotes();
		
		$link = $this->_getLink();
		$t = JRequest::getVar( 'task2', 'display' );
		$a = ( (($tmp = JRequest::getVar( 'all', false )) === false) ? '' : '&all='.$tmp );
		$link .= '&task='.$t.$a;
		if( !$this->_checkAuth() ) {
			$mainframe->enqueueMessage( JText::_('You do not have permission to perform that action on this assessment.'), 'error' );
		}
		// if we're good to go, then go save
		else {
			$formNotes = JRequest::getVar('notes', array());
			$r = true;
			
			$new = new stdClass();
			$new->pupil_id = JRequest::getVar( 'pupil', false );
			foreach( $formNotes as $id=>$message ) {
				$new->id = ( ($id == '__new__') ? NULL : $id );
				$new->message  = $message['note'];
				if( array_key_exists( 'delivered_on', $message ) ) {
					$new->delivered_on = $message['delivered_on'];
				}
				elseif( array_key_exists( 'delivered', $message ) ) {
					$new->delivered_on = date( 'Y-m-d H:i:s' );
				}
				else {
					$new->delivered_on = NULL;
				}
				
				if( ($new->message      != $notes[$id]->message)
				 || ($new->delivered_on != $notes[$id]->delivered_on) ) {
					$new->last_modified = date('Y-m-d H:i:s');
					$r = $model->saveNote( $new ) && $r;
				}
			}
			
			if( $r === true ) {
				$mainframe->enqueueMessage( 'Changes saved', 'message' );
			}
			else {
				$mainframe->enqueueMessage( 'Changes could not be saved', 'error' );
			}
		}
		
		$this->saveModel();
		if( ($msg = ob_get_clean()) !== '' ) {
			$mainframe->enqueueMessage( $msg, 'message' );
		}
		$mainframe->redirect( $link );
		
	}
	
	/**
	 * Checks the user is allowed to view / edit / deliver the note
	 */
	function _checkAuth()
	{
		// **** Just returns true at the moment. needs to actually do checks
		return true;
	}
}
?>