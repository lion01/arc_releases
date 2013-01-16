<?php
/**
 * @package     Arc
 * @subpackage  Behaviour
 * @copyright   Copyright (C) 2005 Punnet. All rights reserved. See COPYRIGHT_ARC.txt
 * @license     http://www.gnu.org/licenses/gpl.html GNU/GPL. See LICENSE_ARC.txt
 * Arc is free software: you can redistribute it and/or modify
 * it under the terms of version 3 of the GNU General Public License
 * as published by the Free Software Foundation.
 * The disclaimer of warranty as stated in the GPL applies to this program
 */

defined( '_JEXEC' ) or die( 'Restricted access' );

/**
 * Behaviour Admin Messages Controller
 *
 * @author     Punnet - Arc Team
 * @package    Arc
 * @subpackage Behaviour
 * @since      1.6
 */
class BehaviourAdminControllerMessages extends BehaviourAdminController
{
	/**
	 * Default method
	 */
	function display()
	{
		global $mainframe, $option;
		jimport('joomla.html.pagination');
		$model = &$this->getModel( 'messages' );
		$view = &$this->getView( 'messages', 'html' );
		$view->setModel( $model, true );
		
		$searchTerm = $mainframe->getUserStateFromRequest( $option.'.search', 'search', '', 'string' );
		$model->setSearchTerm( $searchTerm );
		
		$senderTerm = $mainframe->getUserStateFromRequest( $option.'.sender', 'sender', '', 'string' );
		$model->setSenderTerm( $senderTerm );
		
		$pupilTerm = $mainframe->getUserStateFromRequest( $option.'.pupil', 'pupil', '', 'string' );
		$model->setPupilTerm( $pupilTerm );
		
		$limitStart = $mainframe->getUserStateFromRequest( $option.'.limitstart', 'limitstart', 0, 'int' );
		$limit = $mainframe->getUserStateFromRequest( 'global.list.limit', 'limit', $mainframe->getCfg('list_limit'), 'int' );
		$model->setPagination( $limitStart, $limit );
		
		$model->setPagedThreads();
		
		switch( JRequest::getVar('task') ) {
		// Edit a message
		case( 'edit' ):
			$msgIds = JRequest::getVar( 'msgId' );
			$messageId = $msgIds[reset( array_keys(JRequest::getVar('eid')) )];
			$model->setMessage( $messageId );
			$view->edit();
			break;
			
		// Rescind a message or messages
		case( 'remove' ):
			$checked = JRequest::getVar( 'eid' );
			$thrIds = JRequest::getVar( 'thrId' );
			$msgIds = JRequest::getVar( 'msgId' );
			
			$thrId = $thrIds[reset( array_keys($checked) )];
			// loop through $checked to find all marked messages in the thread
			foreach( $checked as $index=>$on ) {
				if( $thrIds[$index] == $thrId ) {
					$rescindMsgIds[] = $msgIds[$index];
				}
			}
			
			$model->setThread( $thrId );
			$model->setRescindMsgIds( $rescindMsgIds );
			
			if( count($rescindMsgIds) > 1 ) {
				$message = 'Are you sure you want to rescind the highlighted messages?';
			}
			else {
				$message = 'Are you sure you want to rescind the highlighted message?';
			}
			$mainframe->enqueueMessage( $message, 'notice' );
			
			$view->remove();
			break;
		
		// Show a paginated list of all the messages
		default:
			$view->display();
			break;
		}
	}
	
	/**
	 * Save changes to a message and return to the list
	 */
	function save()
	{
		global $mainframe;
		$msgId = JRequest::getVar( 'msg_id' );
		$model = &$this->getModel( 'messages' );
		
		$success = $this->_save( $msgId );
		
		if( $success ) {
			$mainframe->enqueueMessage( 'Message was successfully saved' );
			$this->display();
		}
		else {
			$mainframe->enqueueMessage( 'There was a problem saving the message, please try again', 'error' );
			$model->setMessage( $msgId );
			$view = &$this->getView( 'messages', 'html' );
			$view->setModel( $model, true );
			$view->edit();
		}
	}
	
	/**
	 * Save changes to a message and stay on the edit page
	 */
	function apply()
	{
		global $mainframe;
		$msgId = JRequest::getVar( 'msg_id' );
		$model = &$this->getModel( 'messages' );
		
		$success = $this->_save( $msgId );
		
		if( $success ) {
			$mainframe->enqueueMessage( 'Message was successfully saved' );
		}
		else {
			$mainframe->enqueueMessage( 'There was a problem saving the message, please try again', 'error' );
		}
		
		$model->setMessage( $msgId );
		$view = &$this->getView( 'messages', 'html' );
		$view->setModel( $model, true );
		$view->edit();
	}
	
	function _save( $msgId )
	{
		$model = &$this->getModel( 'messages' );
		$model->setMessage( $msgId );
		
		// common message data
		$msgData = JRequest::getVar( 'msg_data' );
		$msgData['more_action'] = isset($msgData['more_action']);
		$msgData['callout'] = isset($msgData['callout']);
		$data = array();
		$data['inc_type'] = JRequest::getVar( 'msg_inc_type' );
		$data['author'] = JRequest::getVar( 'msg_author' );
		$data['applies'] = JRequest::getVar( 'msg_applies' );
		$data['data'] = $msgData;
		
		// Save the message data
		return $model->save( $data );
	}
	
	/**
	 * Reset the recipients of the message based on its current data
	 */
	function resend()
	{
		global $mainframe;
		$msgId = JRequest::getVar( 'msg_id' );
		$model = &$this->getModel( 'messages' );
		
		$model->setMessage( $msgId );
		$recipients = $model->resend();
		
		if( is_array($recipients) ) {
			$r = array_keys($recipients);
			foreach( $r as $k=>$v ) {
				$r[$k] = ApotheosisData::_( 'people.displayname', $v, 'staff' );
			}
			
			$mainframe->enqueueMessage( 'Message was successfully re-sent to:' );
			$mainframe->enqueueMessage( implode( ', ', $r ) );
			$this->display();
		}
		else {
			$mainframe->enqueueMessage( 'There was a problem re-sending the message, please try again', 'error' );
			$model->setMessage( $msgId );
			$view = &$this->getView( 'messages', 'html' );
			$view->setModel( $model, true );
			$view->edit();
		}
	}

	/**
	 * Rescind the messages
	 */
	function rescind()
	{
		global $mainframe;
		$model = &$this->getModel( 'messages' );
		
		// Retrieve the messages for rescinding
		$threadId = JRequest::getVar( 'rescind_thr_id' );
		
		// Retrieve the messages for rescinding
		$rescindMsgIds = unserialize( JRequest::getVar('rescind_msg_ids') );
		
		// Retrieve rescind message
		$rescMsg = JRequest::getVar( 'rescind_message' );
		
		// Rescind the messages
		$rescind = $model->rescind( $threadId, $rescindMsgIds, $rescMsg );
		
		// check return from model->rescind()
		if( $rescind ) {
			$message = ( count($rescindMsgIds) > 1 ) ? 'The messages were successfully rescinded' : 'The message was successfully rescinded';
			$mainframe->enqueueMessage( $message );
		}
		else {
			$message = ( count($rescindMsgIds) > 1 ) ? 'There was a problem rescinding the messages, please try again' : 'There was a problem rescinding the message, please try again';
			$mainframe->enqueueMessage( $message, 'error' );
		}
		
		$this->display();
	}
}
?>