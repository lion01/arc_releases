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

jimport( 'joomla.application.component.model' );

/**
 * Message Hub Model
 */
class MessageModelHub extends JModel
{
	function __construct()
	{
		parent::__construct();
		$this->fTags = ApothFactory::_( 'message.Tag', $this->fTags );
		$this->fMsg = ApothFactory::_( 'message.Message', $this->fMsg );
		$this->fThread = ApothFactory::_( 'message.Thread', $this->fThread );
		$this->threadPager = ApothPagination::_( 'message.thread', $this->threadPager );
		
		$f = reset( $this->fTags->getInstances( array('category'=>'folder', 'label'=>'searched', 'folder'=>null) ) );
		$this->_searchFolder = $f;
		$this->_lastSearch = array( 'id'=>-1);
	}
	
	function __wakeup()
	{
		$this->fTags = ApothFactory::_( 'message.Tag', $this->fTags );
		$this->fMsg = ApothFactory::_( 'message.Message', $this->fMsg );
		$this->fThread = ApothFactory::_( 'message.Thread', $this->fThread );
		$this->threadPager = ApothPagination::_( 'message.thread', $this->threadPager );
		unset( $this->curThread );
	}
	
	function setDate()
	{
		$this->fMsg->setDate();
		$this->fThread->setDate();
	}
	
	function setPage( $page )
	{
 		return $this->threadPager->setPage( $page );
	}
	
	function getPage()
	{
 		return $this->threadPager->getPage();
	}
	
	function getPageCount()
	{
		return $this->threadPager->getPageCount();
	}
	
	// #####  Manipulate threads  #####
	
	function setThreads( $requirements, $page = null )
	{
		// remember / revive the search terms when necessary
		if( isset($requirements['tag']) ) {
			if( $requirements['tag'] == $this->_searchFolder ) {
				$requirements = $this->_lastSearch;
			}
		}
		else {
			$this->_lastSearch = $requirements;
		}
		
		if( $requirements != array('tag'=>$this->_searchFolder) ) {
			$this->threads = array();
			$msgTmp = $this->fMsg->getInstances( $requirements );
			if( empty( $msgTmp ) ) {
				$msgTmp = -999;
			}
			
			$this->threadPager->setData( array( 'message_id'=>$msgTmp ), array( 'message_latest'=>'d' ) );
			$this->threadPager->setPageSize( 20 );
			if( !is_null( $page ) ) {
				$this->setPage( $page ); // puts page into class var
			}
			$tmp = $this->threadPager->getPagedInstances();
			
			foreach( $tmp as $k=>$v ) {
				$tmp2 = $this->fThread->getInstance( $v );
				$this->threads[$tmp2->getId()] = $tmp2;
				unset( $tmp2 );
			}
		}
		unset( $this->curThread );
	}
	
	function setPdfThreads( $threads )
	{
		$this->pdfThreads = $threads;
	}
	
	function clearThreads()
	{
		$this->fMsg->clearCache();
		$this->fThread->clearCache();
		$this->threadPager->clearCache( false );
		unset($this->curThread);
		unset($this->message);
		$this->threads = array();
	}
	
	function getThreadCount()
	{
		return count( $this->threads );
	}
	
	function setCurrentThread( $tId )
	{
		$this->curThread = &$this->threads[$tId];
	}
	
	function &getCurThread()
	{
		return $this->curThread;
	}
	
	/**
	 * Retrieve the first or next thread from our list
	 */
	function &getThread()
	{
		if( !empty($this->threads) ) {
			// get the entry from our array
			if( !isset($this->curThread) || is_null($this->curThread) || $this->curThread === false ) {
				reset($this->threads);
			}
			else {
				next($this->threads);
			}
			unset($this->curThread);
			if( !is_null(key($this->threads)) ) {
				$this->curThread = &$this->threads[key($this->threads)];
			}
		}
		return $this->curThread;
	}
	
	/**
	 * Retrieve the first or next thread from our pdf list for pdf output
	 */
	function &getPdfThread()
	{
		if( !empty($this->pdfThreads) ) {
			// get the entry from our array
			if( is_null($this->curPdfThread) || ($this->curPdfThread === false) ) {
				reset( $this->pdfThreads );
			}
			else {
				next( $this->pdfThreads );
			}
			unset( $this->curPdfThread );
			if( !is_null(current($this->pdfThreads)) ) {
				$this->curPdfThread = &$this->threads[current($this->pdfThreads)];
			}
			$retVal = $this->curPdfThread; 
		}
		else {
			$retVal = $this->getThread();
		}
		
		return $retVal; 
	}
	
	/**
	 * Move all of the messages within a thread
	 */
	function moveThreads( $ids, $dest )
	{
		$folderObj = $this->fTags->getInstance( $this->folder );
		$path = $folderObj->getPath();
		if( empty($ids) || empty($dest) ) {
			return;
		}
		foreach( $ids as $id ) {
			if( isset($this->threads[$id]) ) {
				$msgs = $this->threads[$id]->getMessageIds();
				foreach( $msgs as $msgId ) {
					if( $msgId >= 0 ) { // don't save new messages that haven't been saved already
						$m = &$this->fMsg->getInstance( $msgId );
						
						$folders = $m->getTagLabels('folder');
						if( isset($folders[$this->folder]) ) {
							$destId = reset( $this->fTags->getInstances(array('category'=>'folder', 'folder'=>$path[1], 'label'=>$dest)) );
							$m->move( $this->folder, $destId );
						}
						$m->commit();
					}
				}
				unset( $this->threads[$id] );
			}
		}
	}
	
	
	// #####  Manipulate messages  #####
	function setMessage( $msgId )
	{
		$this->message = &$this->fMsg->getInstance( $msgId );
	}
	
	function sendMessage( $data, $method )
	{
		$this->_setMessageData( $data );
		$this->message->setRecipients( ApotheosisData::_( 'message.recipients', $this->message, $method ) );
		$ok = $this->message->commit();
		if( $ok ) {
			$threadId = $this->message->getThreadId();
			$this->threads[$threadId] = &$this->fThread->getInstance( $threadId );
			$tmp = ApotheosisData::_( 'message.helperData', 'eventAfter'.ucfirst($method), 'message', $this->message->getId(), $method );
			if( !is_null( $tmp ) ) {
				$ok = $ok && $tmp;
			}
		}
		else {
			$threadId = $this->message->getThreadId();
			$mId = $this->message->getId();
			$this->fMsg->freeInstance( $this->message->getId() );
			if( !is_null($threadId) && ($threadId > 0) ) {
				// replies to existing threads must be removed from the thread's message list
				$thread = $this->threads[$threadId];
				$thread->removeMessage( $mId );
			}
			else {
				// new initial messages should be replaced with a safe blank
				$parts = explode( '.', JRequest::getVar('form') );
				$component = array_shift($parts);
				$this->message = &$this->fMsg->getDummy( $mId );
				$this->message->setTags( array(), array($this->folder) );
				$this->message->setHandler( $component );
				$this->message->setDetailsShown( $details );
			}
		}
		return $ok;
	}
	
	function _setMessageData( $data )
	{
		$this->message = &$this->fMsg->getInstance( $data['id'] );
		$this->message->setId( $data['id'] );
		$this->message->setHandler( $data['handler'] );
		$this->message->setAuthor( $data['author'] );
		$this->message->setCreated( $data['created'] );
		$this->message->setDate( $data['date'] );
		$this->message->setTags( $data['tags']['gen'], $data['tags']['per'] );
		
		foreach( $data['data'] as $k=>$v ) {
			$this->message->setDatum( $k, $v );
		}
	}
	
	function addReply( $msgId )
	{
		$this->message = &$this->fMsg->getInstance( $msgId );
		$thread = &$this->threads[ $this->message->getThreadId() ];
		$dummy = ApotheosisData::_( 'message.helperData', 'getReply', 'message', $this->message->getId() );
		if( !is_null($dummy) ) {
			$dummy->setThreadId( $thread->getId() );
			$thread->addMessage( $dummy->getId() );
		}
	}
	
	// #####  Deal with tags and our current area of interest (component/folder)  #####
	
	function setTags()
	{
		$tagList = JRequest::getVar( 'tags', '' );
		if( $tagList === '' ) {
			$tagList = '21'; // behaviour inbox in base install. Should really be a config option (default folder)
		}
		$tags = ( is_array( $tagList ) ? $tagList : explode( ',', $tagList ) );
		
		foreach( $tags as $id=>$tag ) {
			if( !is_numeric($tag) ) {
				unset($tags[$id]);
			}
		}
		$this->fTags = ApothFactory::_( 'message.Tag', $this->fTags );
		if( !empty($tags) ) {
			$this->tags = array();
			$ids = $this->fTags->getInstances( array('id'=>$tags) );
			foreach( $ids as $id ) {
				$this->tags[$id] = $id;
				$tag = $this->fTags->getInstance($id);
				if( $tag->getCategory() == 'folder' ) {
					$this->folder = $id;
				}
			}
		}
		
		// Set up default tags if none were given
		if( !isset($this->folder) ) {
			$this->folder = reset( $this->fTags->getInstances(array('folder'=>null)) );;
		}
	}
	
	function getTagIds()
	{
		if( !isset($this->folder) ) {
			$this->setTags();
		}
		return $this->tags;
	}
	
	function setFolder( $fId )
	{
		$f = $this->fTags->getInstance( $fId );
		if( $f->getId() == $fId ) {
			$fId = $f->getId();
			$this->tags[$fId] = $f;
			$this->folder = $fId;
			$retVal = true;
		}
		else {
			$retVal = true;
		}
		return $retVal;
	}
	
	function getFolder()
	{
		if( !isset($this->folder) ) {
			$this->setTags();
		}
		return $this->folder;
	}
	
	function getSearchFolder()
	{
		return $this->_searchFolder;
	}
	
	
	// #####  Load page parts or info from helpers (for the current, specific message)  #####
	
	function getMessageForm()
	{
		$parts = explode( '.', JRequest::getVar('form') );
		$component = array_shift($parts);
		$this->folder = reset( $this->fTags->getInstances( array('category'=>'folder', 'label'=>$component) ) );
		
		$data = json_decode( urldecode(JRequest::getVar('data')), true );
		if( $data['new'] ) {
			$this->message = &$this->fMsg->getDummy( -1 );
			$this->message->setTags( array(), array($this->folder) );
			$this->message->setHandler( $component );
		}
		
		return ApotheosisData::_( 'message.helperData', 'renderMessageForm', 'message', $this->message->getId(), implode('.', $parts) );
	}
	
}