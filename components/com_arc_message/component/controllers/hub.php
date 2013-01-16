<?php
/**
 * @package     Arc
 * @subpackage  Message
 * @copyright   Copyright (C) 2005 Punnet. All rights reserved. See COPYRIGHT_ARC.txt
 * @license     http://www.gnu.org/licenses/gpl.html GNU/GPL. See LICENSE_ARC.txt
 * Arc is free software: you can redistribute it and/or modify
 * it under the terms of version 3 of the GNU General Public License
 * as published by the Free Software Foundation.
 * The disclaimer of warranty as stated in the GPL applies to this program
 */

defined( '_JEXEC' ) or die( 'Restricted access' );

/**
 * Message Hub Controller
 */
class MessageControllerHub extends MessageController
{
	function __construct( $config = array() )
	{
		parent::__construct( $config );
		$this->_redisplay = false;
	}
	
	function display()
	{
		$model = &$this->getModel( 'hub' );
		$model->setDate();
		if( JRequest::getVar( 'tags' ) !== '' ) {
			$model->setTags();
		}
		$viewType = JRequest::getVar( 'format', 'html' );
		$page = JRequest::getVar( 'page', null );
		
		switch( strtolower(JRequest::getVar('scope', 'summary')) ) {
		case( 'list' ):
		case( 'summary' ):
		default:
			$viewFunc = 'showMessages';
			
//			if( !$this->_redisplay ) {
				// **** is there a way to save server load when showing the same threads again?
				// **** there used to be, but now we're paginating I'm not so sure
//			}
			$model->setThreads( array('tag'=>$model->getFolder(), 'restrict'=>true), $page );
			break;
		
		case( 'search' ):
			$viewFunc = 'showSearch';
			
			if( !$this->_redisplay ) {
				$model->setFolder( $model->getSearchFolder() );
				
				$search = strtolower( JRequest::getVar('msg_search', '') );
				$requirements = array();
				$requirements['restrict'] = (bool)JRequest::getVar( 'restrict', true );
				$requirements['text'] = explode( ' ', $search );
				$requirements['id'] = JRequest::getVar( 'msgIds', false );
				if( $requirements['id'] !== false ) {
					$requirements['id'] = explode( ',', $requirements['id'] );
				}
				foreach( $requirements as $k=>$v ) {
					if( is_array($v) ) {
						foreach( $v as $k2=>$v2 ) {
							if( empty($v2) ) {
								unset($requirements[$k][$k2]);
							}
						}
					}
					if( empty($requirements[$k]) ) {
						unset( $requirements[$k] );
					}
				}
				$model->setThreads( $requirements, $page );
			}
			break;
		
		case( 'thread' ):
			$viewFunc = 'showThread';
			break;
		
		case( 'message' ):
			$viewFunc = 'showMessages';
			break;
		
		case( 'form' ):
			$viewFunc = 'showForm';
			break;
		
		case( 'settings' ):
			$viewFunc = 'showMessages';
			break;
		
		}
		
		$view = $this->getView( 'Hub', $viewType );
		$view->setModel( $model, true );
		$view->$viewFunc();
		
		$this->saveModel();
	}
	
	function search()
	{
		JRequest::setVar( 'scope', 'search' );
		$this->display();
	}
	
	function toggleThread()
	{
		$this->_redisplay = true;
		$model = &$this->getModel( 'hub' ); // Retrieve the model so its factories are re-initialised so we'll share the same data
		$fThread = ApothFactory::_( 'message.Thread' );
		$tId = JRequest::getVar('threadId');
		$t = &$fThread->getInstance( $tId );
		$t->setDetailsShown();
		$this->saveModel();
		if( JRequest::getVar('format') == 'raw' ) {
			JRequest::setVar( 'scope', 'thread' );
			$model->setCurrentThread( $tId );
			$this->display();
		}
		else {
			$l = ApotheosisLib::getActionLinkByName( 'apoth_msg_hub', array('message.tags'=>$model->getFolder(), 'message.scopes'=>'list') );
			global $mainframe;
			$mainframe->redirect( $l.'#thread_'.$tId );
		}
	}
	
	function toggleMessage()
	{
		$this->_redisplay = true;
		$model = &$this->getModel( 'hub' ); // Retrieve the model so its factories are re-initialised so we'll share the same data
		$fMsg = ApothFactory::_( 'message.Message' );
		$m = &$fMsg->getInstance( JRequest::getVar('msgId') );
		$m->setDetailsShown();
		$this->saveModel();
		
		$l = ApotheosisLib::getActionLinkByName( 'apoth_msg_hub', array('message.tags'=>$model->getFolder(), 'message.scopes'=>'list') );
		global $mainframe;
		$mainframe->redirect( $l.'#msg_'.JRequest::getVar('msgId') ); // last 2 params optional
	}
	
	function replyToMessage()
	{
		$this->_redisplay = true;
		$msgId = JRequest::getVar( 'msgId' );
		if( $msgId > 0 ) {
			$model = &$this->getModel( 'hub' );
			$model->addReply( $msgId );
		}
		$this->display();
	}
	
	
	function refresh()
	{
		$model = &$this->getModel( 'hub' );
		$model->clearThreads();
		$model->setThreads( array('tag'=>$model->getFolder()) );
		$this->display();
	}
	
	function archive()
	{
		$model = &$this->getModel( 'hub' );
		$model->moveThreads( json_decode(JRequest::getVar('thread_ids')), 'archive' );
		$this->display();
	}
	function delete()
	{
		$model = &$this->getModel( 'hub' );
		$model->moveThreads( json_decode(JRequest::getVar('thread_ids')), 'bin' );
		$this->display();
	}
	function revive()
	{
		$model = &$this->getModel( 'hub' );
		$model->moveThreads( json_decode(JRequest::getVar('thread_ids')), 'inbox' );
		$this->display();
	}
	
	
	function saveDraft()
	{
		$statuses = $this->_send( 'draft' );
		
		global $mainframe;
		if( $statuses['bad'] == 0 && $statuses['good'] == 0 ) {
			$mainframe->enqueueMessage( 'no messages defined' );
		}
		else {
			if( $statuses['good'] > 0 ) {
				$mainframe->enqueueMessage( $statuses['good'].' message'.($statuses['good'] > 1 ? 's' : '' ).' saved' );
			}
			if( $statuses['bad'] > 0 ) {
				$mainframe->enqueueMessage( $statuses['bad'].' message'.($statuses['bad'] > 1 ? 's' : '' ).' failed to save', 'error' );
			}
		}
		$model = &$this->getModel( 'hub' );
		$model->clearRecipients();
		$this->display();
	}
	
	function send()
	{
		JHTML::script( 'extranames.js', JURI::base().'components'.DS.'com_arc_message'.DS.'views'.DS.'hub'.DS.'tmpl'.DS );
		$statuses = $this->_send( 'send' );
		
		global $mainframe;
		if( $statuses['bad'] == 0 && $statuses['good'] == 0 ) {
			$mainframe->enqueueMessage( 'no messages defined' );
		}
		else {
			if( $statuses['good'] > 0 ) {
				$mainframe->enqueueMessage( $statuses['good'].' message'.($statuses['good'] > 1 ? 's' : '' ).' sent' );
			}
			if( $statuses['bad'] > 0 ) {
				$mainframe->enqueueMessage( $statuses['bad'].' message'.($statuses['bad'] > 1 ? 's' : '' ).' failed to send', 'error' );
			}
		}
		$model = &$this->getModel( 'hub' );
		$r = $model->getRecipients();
		$listLength = 3;
		if( !empty( $r['all'] ) ) {
			$r1 = $r2 = array();
			$rNum = count( $r['all'] );
			$rStop = min( $rNum, $listLength );
			
			$names = array();
			for( $i = 0; $i < $rStop; $i++ ) {
				$names[] = ApotheosisData::_( 'people.displayName', $r['all'][$i], 'teacher' );
			}
			$str = implode( ', ', $names );
			if( $rNum > $rStop ) {
				JHTML::script( 'extranames.js', JURI::base().'components'.DS.'com_arc_message'.DS.'views'.DS.'hub'.DS.'tmpl'.DS );
				$str .= '<a href="#" id="moreAll"> more...</a><div id="extraAll" style="display:none;">, ';
				$names = array();
				for( ; $i < $rNum; $i++ ) {
					$names[] = ApotheosisData::_( 'people.displayName', $r['all'][$i], 'teacher' );
				}
				$str .= implode( ', ', $names );
				$str .= '</div>';
			}
			
			$mainframe->enqueueMessage( 'All sent messages were sent to:<br />'.$str );
		}
		if( !empty( $r['some'] ) ) {
			$r1 = $r2 = array();
			$rNum = count( $r['some'] );
			$rStop = min( $rNum, $listLength );
			
			$names = array();
			for( $i = 0; $i < $rStop; $i++ ) {
				$names[] = ApotheosisData::_( 'people.displayName', $r['some'][$i], 'teacher' );
			}
			$str = implode( ', ', $names );
			if( $rNum > $rStop ) {
				JHTML::script( 'extranames.js', JURI::base().'components'.DS.'com_arc_message'.DS.'views'.DS.'hub'.DS.'tmpl'.DS );
				$str .= '<a href="#" id="moreSome"> more...</a><div id="extraSome" style="display:none;">, ';
				$names = array();
				for( ; $i < $rNum; $i++ ) {
					$names[] = ApotheosisData::_( 'people.displayName', $r['some'][$i], 'teacher' );
				}
				$str .= implode( ', ', $names );
				$str .= '</div>';
			}
			
			$mainframe->enqueueMessage( 'Messages were sent to:<br />'.$str );
		}
		$model->clearRecipients();
		$this->display();
	}
	
	function _send( $method )
	{
		$model = &$this->getModel( 'hub' );
		
		// common message data
		$msgData = JRequest::getVar( 'msg_data' );
		$data = array();
		$data['id'] = JRequest::getVar( 'msg_id' );
		$data['handler'] = JRequest::getVar( 'msg_handler' );
		$data['author'] = JRequest::getVar( 'msg_author' );
		$data['created'] = JRequest::getVar( 'msg_created' );
		$data['date'] = JRequest::getVar( 'msg_date' );
		$data['tags'] = JRequest::getVar( 'msg_tags', array('gen'=>array(), 'per'=>array()) );
		$data['data'] = $msgData;
		// sort out the potentially multi-part date
		if( is_array($data['date']) ) {
			$d = '';
			if( isset($data['date']['date']) ) {
				$d .= $data['date']['date'].' ';
			}
			if( isset($data['date']['time']) ) {
				if( strlen($data['date']['time']) == 5 ) {
					$data['date']['time'] .= ':00';
				}
				$d .= $data['date']['time'];
			}
			$data['date'] = $d;
		}
		
		if( empty($data['date']) ) {
			$data['date'] = null;
		}
		// *** I know this isn't a super-accurate date validation, but it should catch formatting errors
		elseif( !preg_match( '~[0-9][0-9][0-9][0-9]-[0-1][0-9]-[0-3][0-9] [0-2][0-9]:[0-5][0-9]:[0-5][0-9]~', $data['date'] ) ) {
			return 'Problem with date';
		}
		
		$count = array( 'good'=>0, 'bad'=>0 );
		$split = JRequest::getVar('split_on');
		// send the one and only message we need to
		if( empty($split) ) {
			$success = $model->sendMessage( $data, $method );
			$count[( $success ? 'good' : 'bad' )]++;
		}
		// go through the values in the split field and send a message per value
		else {
			$opts = $msgData[$split];
			if( !is_array($opts) ) {
				$opts = array($opts);
			}
			foreach( $opts as $opt ) {
				$data['data'][$split] = $opt;
				if( !empty($opt) ) {
					$success = $model->sendMessage( $data, $method );
					$count[( $success ? 'good' : 'bad' )]++;
				}
			}
		}
		
		$form = JRequest::getVar( 'form' );
		if( !empty($form) ) {
			JRequest::setVar( 'form', str_replace('.edit', '.view', $form) );
		}
		return $count;
	}
	
	/*
	 * Generates pdf output of the messages hub
	 */
	function generate()
	{
		$model = $this->getModel( 'hub' );
		$view = &$this->getView ( 'hub', 'apothpdf' );
		
		$threads = JRequest::getVar( 'threads' );
		if( !is_null($threads) ) {
			$threads = explode( ',', $threads );
			$model->setPdfThreads( $threads );
		}
		
		$view->setModel( $model, true );
		$view->display();
		
		$this->saveModel();
	}
	
	
	function ajax()
	{
		$model = $this->getModel( 'hub' );
		
		switch( JRequest::getVar('scope') ) {
		case( 'formParts' ):
			$output = ApotheosisData::_( 'message.helperData', 'renderMessageForm', 'message', $model->message->getId(), 'part.'.JRequest::getVar( 'part' ));
			break;
		}
		
		echo $output;
	}
}
?>