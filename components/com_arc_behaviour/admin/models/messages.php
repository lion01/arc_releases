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

// Give us access to the joomla model class
jimport( 'joomla.application.component.model' );

// include front-end message factories
require_once( JPATH_SITE.DS.'components'.DS.'com_arc_core'.DS.'libraries'.DS.'apoth_factory.php' );
require_once( JPATH_SITE.DS.'components'.DS.'com_arc_behaviour'.DS.'helpers'.DS.'message.php' );

/**
 * Behaviour Admin Messages Model
 *
 * @author     Punnet - Arc Team
 * @package    Arc
 * @subpackage Behaviour
 * @since      1.6
 */
class BehaviourAdminModelMessages extends JModel
{
	// #####  Main message/thread listing  #####
	
	/**
	 * Set the search term
	 * 
	 * @param string $searchTerm  The search term to set
	 */
	function setSearchTerm( $searchTerm )
	{
		$this->_searchTerm = JString::strtolower( $searchTerm );
	}
	
	/**
	 * Retrieve the search term
	 * 
	 * @return string $this->_searchTerm  The search term
	 */
	function getSearchTerm()
	{
		return $this->_searchTerm;
	}
	
	/**
	 * Set the sender filter term
	 */
	function setSenderTerm( $senderTerm )
	{
		$this->_senderTerm = JString::strtolower( $senderTerm );
	}
	
	/**
	 * Retrieve the sender filter term
	 * 
	 * @return string $this->_senderTerm  The sender filter term
	 */
	function getSenderTerm()
	{
		return $this->_senderTerm;
	}
	
	/**
	 * Set the pupil filter term
	 */
	function setPupilTerm( $pupilTerm )
	{
		$this->_pupilTerm = JString::strtolower( $pupilTerm );
	}
	
	/**
	 * Retrieve the pupil filter term
	 * 
	 * @return string $this->_pupilTerm  The pupil filter term
	 */
	function getPupilTerm()
	{
		return $this->_pupilTerm;
	}
	
	/**
	 * Set the currently valid pagination object
	 * 
	 * @param int $limitStart Where to start paging from
	 * @param int $limit  The total number of items to page
	 */
	function setPagination( $limitStart, $limit )
	{
		$total = $this->_loadPagedThreads( true );
		$this->_pagination = new JPagination( $total, $limitStart, $limit );
	}
	
	/**
	 * Retrieve the currently valid pagination object
	 * 
	 * @return object $this->_pagination  The pagination object
	 */
	function &getPagination()
	{
		return $this->_pagination;
	}
	
	/**
	 * Set a paginated array of thread objects
	 */
	function setPagedThreads()
	{
		$threadIds = $this->_loadPagedThreads();
		
		$fThread = ApothFactory::_( 'behaviour.Thread' );
		
		$this->_pagedThreads = array();
		foreach( $threadIds as $threadId ) {
			$this->_pagedThreads[] = &$fThread->getInstance( $threadId );
		}
	}
	
	/**
	 * Fetch a paginated list of thread objects
	 * 
	 * @return array $this->_pagedThreads  Array of thread objects
	 */
	function &getPagedThreads()
	{
		return $this->_pagedThreads;
	}
	
	/**
	 * Retrieve threads or a count of threads from the db
	 * 
	 * @param bool $numOnly  Whether we only want a count of threads, defaults to false
	 * @return int|array $result  The count of threads or array of thread info
	 */
	function _loadPagedThreads( $numOnly = false )
	{
		$db = &JFactory::getDBO();
		$searchTerm = $this->_searchTerm;
		$senderTerm = $this->_senderTerm;
		$pupilTerm = $this->_pupilTerm;
		
		// create the search term where clause
		if( $searchTerm != '' ) {
			$where[] = '('
				.'  ( '.$db->nameQuote('data').'.'.$db->nameQuote('col_id').' = '.$db->Quote('outline')
				.' OR '.$db->nameQuote('data').'.'.$db->nameQuote('col_id').' = '.$db->Quote('comment')
				.' OR '.$db->nameQuote('data').'.'.$db->nameQuote('col_id').' = '.$db->Quote('incident')
				.' OR '.$db->nameQuote('data').'.'.$db->nameQuote('col_id').' = '.$db->Quote('incident_text')
				.' OR '.$db->nameQuote('data').'.'.$db->nameQuote('col_id').' = '.$db->Quote('action')
				.' OR '.$db->nameQuote('data').'.'.$db->nameQuote('col_id').' = '.$db->Quote('action_text')
				.' )'
				.' AND '.$db->nameQuote('data').'.'.$db->nameQuote('data').' LIKE '.$db->Quote( '%'.$db->getEscaped( $searchTerm, true ).'%', false )
				.' )';
		}
		
		// create the sender term where clause
		if( $senderTerm != '' ) {
			$where[] = $db->nameQuote('msgs').'.'.$db->nameQuote('author').' = '.$db->Quote( $senderTerm );
		}
		
		// create the pupil term where clause
		if( $pupilTerm != '' ) {
			$where[] = '('.$db->nameQuote('data2').'.'.$db->nameQuote('col_id').' = '.$db->Quote('student_id').' AND '.$db->nameQuote('data2').'.'.$db->nameQuote('data').' = '.$db->Quote($pupilTerm).')';
		}
		
		// get only behaviour messages
		$where[] = $db->nameQuote('msgs').'.'.$db->nameQuote('handler').' = '.$db->Quote( 'behaviour' );
		
		// check date validity of message
		$where[] = ApotheosisLibDb::dateCheckSql( 'data.valid_from', 'data.valid_to', date('Y-m-d H:i:s'), date('Y-m-d H:i:s') );
		
		// create the combined where clause if appropriate
		$where = "\n".'WHERE (' . implode( ')'."\n".'  AND (', $where ) . ')';
		
		// create the order clause
		$order = "\n".'ORDER BY '.$db->nameQuote('thr').'.'.$db->nameQuote('id').' DESC';
		
		// create the query
		$query = 'SELECT DISTINCT '.$db->nameQuote('thr').'.'.$db->nameQuote('id')
			."\n".'FROM '.$db->nameQuote('#__apoth_msg_threads').' AS '.$db->nameQuote('thr')
			."\n".'INNER JOIN '.$db->nameQuote('#__apoth_msg_data').' AS '.$db->nameQuote('data')
			."\n".'   ON '.$db->nameQuote('data').'.'.$db->nameQuote('msg_id').' = '.$db->nameQuote('thr').'.'.$db->nameQuote('msg_id')
			."\n".'INNER JOIN '.$db->nameQuote('#__apoth_msg_messages').' AS '.$db->nameQuote('msgs')
			."\n".'   ON '.$db->nameQuote('msgs').'.'.$db->nameQuote('id').' = '.$db->nameQuote('thr').'.'.$db->nameQuote('msg_id')
			."\n".'INNER JOIN '.$db->nameQuote('#__apoth_msg_data').' AS '.$db->nameQuote('data2')
			."\n".'   ON '.$db->nameQuote('data2').'.'.$db->nameQuote('msg_id').' = '.$db->nameQuote('thr').'.'.$db->nameQuote('msg_id')
			.$where;
		
		// get the results
		if( $numOnly ) {
			$db->setQuery( $query );
			$threadIds = $db->loadResultArray();
			$result = count( $threadIds );
		}
		else {
			$pagination = $this->_pagination;
			$db->setQuery( $query.$order, $pagination->limitstart, $pagination->limit );
			$result = $db->loadResultArray();
		}
		
		return $result;
	}
	
	
	// #####  Message editing  #####
	
	function setMessage( $messageId )
	{
		$fMsg = ApothFactory::_( 'behaviour.Message' );
		
		$this->_message = &$fMsg->getInstance( $messageId );
	}
	
	function getMessage()
	{
		return $this->_message;
	}
	
	/**
	 * Save the message data
	 * 
	 * @param array $messageData  Message data to save
	 * @return boolean $commit Whether or not the object was successfully committed
	 */
	function save( $data )
	{
		$fInc = ApothFactory::_( 'behaviour.IncidentType' );
		$curIncident = $fInc->getInstance( $this->_message->getDatum( 'incident' ) );
		
		$newScore = $data['inc_type'] != $curIncident->getParentId();
		if( $newScore ) {
			$oldP = $this->_message->getDatum( 'student_id');
			$oldG = $this->_message->getDatum( 'group_id');
		}
		// Update the current message object with passed in data and commit it
		
		// ... core data
		$this->_message->setAuthor( $data['author'] );
		$this->_message->setDate( $data['applies'] );
		
		// ... message data
		foreach( $data['data'] as $k=>$v ) {
			$this->_message->setDatum( $k, $v );
		}
		
		// ... incident type tag
		$incType = $fInc->getInstance( $data['inc_type'] );
		$tag1 = $incType->getTag();
		
		// ... group type tag
		$tType = ApotheosisData::_( 'course.type', $this->_message->getDatum('group_id') );
		switch( $tType ) {
		case( 'pastoral' ):
			$tType = 'Tutor';
			break;
			
		case( 'normal' ):
			$tType = 'Lesson';
			break;
			
		default:
			$tType = 'Untaught';
			break;
		}
		$tag2 = ApotheosisData::_( 'message.tagId', 'attribute', $tType );
		
		$this->_message->setTags( array( $tag1, $tag2 ), array() );
		
		// all data set; commit
		$r = $this->_message->commit();
		if( $newScore ) {
			ApotheosisData::_( 'behaviour.removeScore', $oldP, $oldG, $this->_message->getId() );
			$h = new ApothMessage_Behaviour();
			$h->setScore( $this->_message );
		}
		return $r;
	}
	
	/**
	 * Resends the current message
	 * After this has run old recipients' copies will not appear,
	 * new / existing recipients' copies will be in their folders as determined by channel subscriptions
	 */
	function resend()
	{
		$r = ApotheosisData::_( 'message.recipients', $this->_message, 'send' );
		$this->_message->setRecipients( $r );
		$success = $this->_message->commit();
		
		return ( $success ? $r : false );
	}
	
	// #####  Message rescinding  #####
	
	/**
	 * Set the thread object
	 * 
	 * @param int $thrId  ID of thread to be set
	 */
	function setThread( $thrId )
	{
		$fThread = ApothFactory::_( 'behaviour.Thread' );
		
		$this->_thread = &$fThread->getInstance( $thrId );
	}
	
	/**
	 * Retrieve the thread object
	 * 
	 * @return obj $this->thread  The thread object
	 */
	function &getThread()
	{
		return $this->_thread;
	}
	
	/**
	 * Set the IDs of messages to be rescinded
	 * 
	 * @param array $msgIds  IDs of messages to be rescinded
	 */
	function setRescindMsgIds( $msgIds )
	{
		$this->_rescindMsgIds = $msgIds;
	}
	
	/**
	 * Retrieve the IDs of messages to be rescinded
	 * 
	 * @return array $this->_rescindMsgIds  IDs of messages to be rescinded
	 */
	function &getRescindMsgIds()
	{
		return $this->_rescindMsgIds;
	}
	
	/**
	 * Get all the messages for a given thread
	 * 
	 * @param array $msgIds  Array of message IDs
	 * @return array $threadMessages  Array of message objects
	 */
	function &getThreadMessages( $msgIds )
	{
		$fMsg = ApothFactory::_( 'behaviour.Message' );
			
		foreach( $msgIds as $msgId ) {
			$threadMessages[] = &$fMsg->getInstance( $msgId );
		}
		
		return $threadMessages;
	}
	
	/**
	 * Get the incident related details as an object
	 * 
	 * @param int $incId  Incident ID
	 * @return object  Object containing incident related details 
	 */
	function &getIncObject( $incId )
	{
		static $incObjs = array();
		
		if( !isset($incObjs[$incId]) ) {
			$db = JFactory::getDBO();
			
			$query = 'SELECT '.$db->nameQuote('bhv').'.'.$db->nameQuote('id').', '.$db->nameQuote('bhv').'.'.$db->nameQuote('label').', '.$db->nameQuote('par').'.'.$db->nameQuote('label').' AS '.$db->nameQuote('colour')
				."\n".'FROM '.$db->nameQuote('#__apoth_bhv_inc_types').' AS '.$db->nameQuote('bhv')
				."\n".'INNER JOIN '.$db->nameQuote('#__apoth_bhv_inc_types').' AS '.$db->nameQuote('par')
				."\n".'   ON '.$db->nameQuote('par').'.'.$db->nameQuote('id').' = '.$db->nameQuote('bhv').'.'.$db->nameQuote('parent')
				."\n".'WHERE '.$db->nameQuote('bhv').'.'.$db->nameQuote('id').' = '.$db->Quote($incId);
			
			$db->setQuery( $query );
			$incObjs[$incId] = $db->loadObject();
		}
		
		return $incObjs[$incId];
	}
	
	/**
	 * Get the action related details as an object
	 * 
	 * @param int $actId  Action ID
	 * @return object  Object containing action related details 
	 */
	function &getActObject( $actId )
	{
		static $actObjs = array();
		
		if( !isset($actObjs[$actId]) ) {
			$db = JFactory::getDBO();
			
			$query = 'SELECT '.$db->nameQuote('id').', '.$db->nameQuote('label')
				."\n".'FROM '.$db->nameQuote('#__apoth_bhv_actions')
				."\n".'WHERE '.$db->nameQuote('id').' = '.$db->Quote($actId);
			
			$db->setQuery( $query );
			$actObjs[$actId] = $db->loadObject();
		}
		
		return $actObjs[$actId];
	}
	
	/**
	 * Rescind messages
	 * 
	 * @param int $threadId  ID of the thread
	 * @param array $rescindMsgIds  Array of message IDs for rescinding
	 * @param string $rescMsg  Rescind message
	 * @return boolean  An indicator of if execution was error free
	 */
	function rescind( $threadId, $rescindMsgIds, $rescMsg )
	{
		$db = &JFactory::getDBO();
		
		$msgList = implode( ', ', $rescindMsgIds );
		foreach( $rescindMsgIds as $k=>$messageId ) {
			$rescindMsgIds[$k] = $db->Quote( $messageId );
		}
		$rescindMsgIds = implode( ', ', $rescindMsgIds );
		
		$now = date( 'Y-m-d H:i:s' );
		$dbNow = $db->Quote( $now );
		$dbArchive = $db->Quote('24');
		$dbText = $db->Quote( $rescMsg."<br />\n".$msgList.' rescinded at '.$now );
		
		$query = 'START TRANSACTION;'
			."\n"
			."\n".'SELECT @order := ( MAX('.$db->nameQuote('order').') + 1 )'
			."\n".'FROM '.$db->nameQuote('#__apoth_msg_threads')
			."\n".'WHERE '.$db->nameQuote('id').' = '.$db->Quote($threadId).';'
			."\n"
// *** if first message rescinded we should add stuff to retain other info
			."\n".'SELECT @first := ( MIN('.$db->nameQuote('order').') = 1 )'
			."\n".'FROM '.$db->nameQuote('#__apoth_msg_threads')
			."\n".'WHERE '.$db->nameQuote('msg_id').' IN ('.$rescindMsgIds.');'
			."\n"
			// create new message indicating there are recinded messages
			."\n".'INSERT INTO '.$db->nameQuote('#__apoth_msg_messages')
			."\n".'SET '.$db->nameQuote('handler').' = '.$db->Quote('behaviour').','
			."\n".'    '.$db->nameQuote('author').' = '.$db->Quote('GB-0000-00000000-0').','
			."\n".'    '.$db->nameQuote('created').' = '.$dbNow.';'
			."\n"
			."\n".'SET @msgId = LAST_INSERT_ID();'
			."\n"
			."\n".'INSERT INTO '.$db->nameQuote('#__apoth_msg_data')
			."\n".'SET '.$db->nameQuote('msg_id').' = @msgId,'
			."\n".'    '.$db->nameQuote('col_id').' = IF( @first, '.$db->Quote('outline').', '.$db->Quote('comment').' ),'
			."\n".'    '.$db->nameQuote('data').' = '.$dbText.' ,'
			."\n".'    '.$db->nameQuote('valid_from').' = '.$dbNow.';'
			."\n"
			."\n".'INSERT INTO '.$db->nameQuote('#__apoth_msg_threads')
			."\n".'SET '.$db->nameQuote('id').' = '.$db->Quote($threadId).','
			."\n".'    '.$db->nameQuote('msg_id').' = @msgId,'
			."\n".'    '.$db->nameQuote('order').' = @order;'
			."\n"
			."\n".'DELETE '
			."\n".'FROM '.$db->nameQuote('#__apoth_msg_threads')
			."\n".'WHERE '.$db->nameQuote('msg_id').' IN ('.$rescindMsgIds.')'
			."\n".'  AND '.$db->nameQuote('id').' = '.$db->Quote($threadId).';'
			."\n"
			// put the new message into peoples folders
			."\n".'INSERT INTO '.$db->nameQuote('#__apoth_msg_tag_map')
			."\n".'SELECT @msgId, '.$db->nameQuote('person_id').', '.$dbArchive.', NULL, '.$dbNow.', NULL'
			."\n".'FROM '.$db->nameQuote('#__apoth_msg_tag_map')
			."\n".'WHERE '.$db->nameQuote('msg_id').' IN ('.$rescindMsgIds.')'
			."\n".'  AND '.$db->nameQuote('valid_to').' IS NULL'
			."\n".'  AND '.$db->nameQuote('person_id').' IS NOT NULL'
			."\n".'GROUP BY '.$db->nameQuote('person_id').';'
			."\n"
			."\n".'INSERT INTO '.$db->nameQuote('#__apoth_msg_tag_map')
			."\n".'SELECT @msgId, NULL, '.$db->nameQuote('tag_id').', NULL, '.$dbNow.', NULL'
			."\n".'FROM '.$db->nameQuote('#__apoth_msg_tag_map')
			."\n".'WHERE '.$db->nameQuote('msg_id').' IN ('.$rescindMsgIds.')'
			."\n".'  AND '.$db->nameQuote('valid_to').' IS NULL'
			."\n".'  AND '.$db->nameQuote('person_id').' IS NULL'
			."\n".'GROUP BY '.$db->nameQuote('tag_id').';'
			."\n"
			// remove the old messages
			."\n".'UPDATE '.$db->nameQuote('jos_apoth_msg_tag_map')
			."\n".'SET '.$db->nameQuote('valid_to').' = '.$dbNow.''
			."\n".'WHERE '.$db->nameQuote('msg_id').' IN ('.$rescindMsgIds.')'
			."\n".'  AND '.$db->nameQuote('valid_to').' IS NULL'
			."\n".'  AND '.$db->nameQuote('person_id').' IS NOT NULL;'
			."\n"
			// remove scores too
			."\n".'DELETE FROM '.$db->nameQuote('#__apoth_bhv_scores')
			."\n".'WHERE '.$db->nameQuote('msg_id').' IN ('.$rescindMsgIds.');'
			."\n"
			."\n".'COMMIT;';
		$db->setQuery( $query );
		$db->QueryBatch();
		
		return ($db->getErrorMsg() == '');
	}
}