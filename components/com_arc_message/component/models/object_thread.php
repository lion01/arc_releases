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
 * Messaging Thread Factory
 */
class ApothFactory_Message_Thread extends ApothFactory
{
	function initialise()
	{
		$this->setDate();
	}
	
	function setDate( $date = null )
	{
		$this->_date = ( is_null($date) ? date( 'Y-m-d H:i:s' ) : $date );
	}
	
	/**
	 * Creates a blank instance with the given id
	 * @param int $id  The id that should be used for the dummy object. Must be negative.
	 */
	function &getDummy( $id )
	{
		if( $id >= 0 ) {
			$r = null;
			return $r;
		}
		$r = &$this->_getInstance( $id );
		if( is_null($r) ) {
			$r = new ApothThread( array('id'=>$id) );
			$this->_addInstance( $id, $r );
		}
		return $r;
	}
	
	/**
	 * Retrieves the identified message, creating the object if it didn't already exist
	 * @param $id
	 */
	function &getInstance( $id )
	{
		$r = &$this->_getInstance( $id );
		if( is_null($r) ) {
			$db = &JFactory::getDBO();
			$u = &ApotheosisLib::getUser();
			$personId = $db->Quote($u->person_id);
			$tId = $db->Quote( $id );
			$query = 'SELECT DISTINCT t.*'
				."\n".'FROM '.$db->nameQuote( '#__apoth_msg_threads' ).' AS t'
				."\n".'INNER JOIN '.$db->nameQuote( '#__apoth_msg_messages' ).' AS m'
				."\n".'   ON m.id = t.msg_id'
				."\n".'INNER JOIN '.$db->nameQuote( '#__apoth_msg_tag_map' ).' AS tm'
				."\n".'   ON tm.msg_id = m.id'
				."\n".'  AND (tm.person_id = '.$personId.' OR tm.person_id IS NULL)'
				."\n".'WHERE t.id = '.$tId
				."\n".'  AND '.ApotheosisLibDb::dateCheckSql( 'tm.valid_from', 'tm.valid_to', $this->_date, $this->_date );
			$db->setQuery($query);
			$data = $db->loadAssocList();
			
			$r = new ApothThread( $data );
			$this->_addInstance( $id, $r );
		}
		return $r;
	}
	
	function &getInstances( $requirements, $init = true, $orders = null )
	{
		$sId = $this->_getSearchId( $requirements );
		$ids = $this->_getInstances($sId);
		if( is_null($ids) ) {
			$db = &JFactory::getDBO();
			$where = array();
			$orderBy = array();
			foreach( $requirements as $col=>$val ) {
				if( is_array($val) ) {
					if( empty($val) ) {
						continue;
					}
					foreach( $val as $k=>$v ) {
						$val[$k] = $db->Quote( $v );
					}
					$assignPart = ' IN ('.implode( ', ',$val ).')';
				}
				else {
					$assignPart = ' = '.$db->Quote( $val );
				}
				switch( $col ) {
				case( 'id' ):
					$where[] = 't.id '.$assignPart;
					break;
				
				case( 'message_id' ):
					$where[] = 't.msg_id '.$assignPart;
					break;
				}
			}
			
			if( !is_null($orders) ) {
				foreach( $orders as $orderOn=>$orderDir ) {
					if( $orderDir == 'a' ) {
						$orderDir = 'ASC';
					}
					elseif( $orderDir == 'd' ) {
						$orderDir = 'DESC';
					}
					switch( $orderOn ) {
					case( 'message_latest'):
						$viewCount = true;
						$joins[] = 'INNER JOIN '.$db->nameQuote( '#__apoth_msg_threads' ).' AS t_order'
							."\n".'   ON t_order.id = t.id'
							."\n".'INNER JOIN '.$db->nameQuote( '#__apoth_msg_messages' ).' AS m_order'
							."\n".'   ON m_order.id = t_order.msg_id';
						$orderBy[] = 'COALESCE( m_order.applies_on, m_order.created ) '.$orderDir;
						break;
					}
				}
			}
			$query = 'SELECT t.id'
				."\n".'FROM '.$db->nameQuote( '#__apoth_msg_threads' ).' AS t'
				.( empty($joins) ? '' : "\n".implode("\n", $joins) )
				.( empty($where) ? '' : "\n WHERE ".implode("\n AND ", $where) )
				."\n".'GROUP BY t.id'
				.( empty($orderBy) ? '' : "\n ORDER BY ".implode(', ', $orderBy) );
			$db->setQuery($query);
			$ids = $db->loadResultArray();
		}
		
		$this->_addInstances( $sId, $ids );
		return $ids;
	}
	
	/**
	 * Commits the instance to the db,
	 * updates the cached instance,
	 * clears the search cache if we've added a new instance
	 *  (the newly created instance may match any of the searches we preveiously executed)
	 * 
	 * @param $id
	 */
	function commitInstance( $id )
	{
		$r = &$this->_getInstance( $id );
		if( is_null($r) ) {
			return false;
		}
		$db = &JFactory::getDBO();
		$id = $r->getId();
		
		$d = $r->getNewMessageIds();
		if( !empty($d) ) {
			$query = 'START TRANSACTION;';
			if( $id < 0 ) {
				$query .= "\n".'SELECT @tid:=(MAX(id)+1) FROM '.$db->nameQuote( '#__apoth_msg_threads' ).';';
			}
			else {
				$query .= "\n".'SET @tid = '.$db->Quote($id).';';
			}
			
			$v1 = '( @tid, ';
			$v2 = ', @order := (@order+1) )';
			foreach( $d as $v ) {
				$values[] = $v1.$db->Quote($v).$v2;
			}
			
			$query .= "\n".'SELECT @order := IFNULL( MAX('.$db->nameQuote('order').'), 0 )'
				."\n".'FROM '.$db->nameQuote('#__apoth_msg_threads')
				."\n".'WHERE '.$db->nameQuote('id').' = @tid;'
				."\n" 
				."\n".'INSERT INTO '.$db->nameQuote('#__apoth_msg_threads')
				."\n".'('.$db->nameQuote('id')
				    .', '.$db->nameQuote('msg_id')
				    .', '.$db->nameQuote('order')
				    .')'
				."\n".'VALUES'
				."\n".implode( "\n, ", $values ).';'
				."\n"
				."\n".'COMMIT;';
			$db->setQuery( $query );
			$db->QueryBatch();
			
			$this->_clearCachedInstances( $id );
			$this->_clearCachedSearches();
		}
	}
	
	function freeInstance( $id )
	{
		if( isset( $this->_instances[$id] ) ) {
			$fMsg = ApothFactory::_( 'message.message' );
			$msgIds = $this->_instances[$id]->getMessageIds();
			foreach( $msgIds as $mId ) {
				$fMsg->freeInstance( $mId );
			}
		}
		parent::freeInstance( $id );
	}
	
}


/**
 * Messaging Thread Object
 */
class ApothThread extends JObject
{
	/**
	 * The unique id of this thread
	 * @access protected
	 * @var int
	 */
	var $_id;
	
	/**
	 * All the ids of messages in this thread
	 * @access protected
	 * @var array
	 */
	var $_messages = array();
	
	/**
	 * All the messages that we added to this thread since last commit
	 * Though their order can be assured, other messages may be added to the thread
	 * before we commit which will offset their position once we commit them
	 * @var array
	 */
	var $_newMessages = array();
	
	function __construct( $messageRows )
	{
		if( empty($messageRows) ) {
			$this->_id = -1;
			$this->_messages = array();
		}
		else {
			$tmp = reset($messageRows);
			$this->_id = $tmp['id'];
			foreach( $messageRows as $row ) {
				$this->_messages[$row['order']] = $row['msg_id'];
			}
			$final = end( $this->_messages );
			$fMsg = ApothFactory::_( 'message.Message' );
			$final = &$fMsg->getInstance( $final );
			$final->setDetailsShown( true );
		}
		$this->_showDetails = false;
	}
	
	function getDetailsShown()
	{
		return $this->_showDetails;
	}
	
	function setDetailsShown( $val = null )
	{
		$this->_showDetails = ( is_null($val) ? !$this->_showDetails : (bool)$val );
	}
	
	function getId()
	{
		return $this->_id;
	}
	
	function getHandler()
	{
		$fMsg = ApothFactory::_( 'message.Message' );
		$m = $fMsg->getInstance( reset($this->_messages) );
		return $m->getHandler();
	}
	
	function getMessageIds()
	{
		return $this->_messages;
	}
	
	function getNewMessageIds()
	{
		return $this->_newMessages;
	}
	
	function getFirstMessageId()
	{
		return reset($this->_messages);
	}
	
	function getMessageIdBefore( $id )
	{
		$cur = null;
		$next = reset( $this->_messages );
		while( ($next != $id) && ($next !== false) ) {
			$cur = $next;
			$next = next( $this->_messages );
		}
		return $cur;
	}
	
	function getMessageCount()
	{
		return count($this->_messages);
	}
	
	function addMessage( $id )
	{
		$this->_newMessages[] = $id;
		$this->_messages[] = $id;
		end($this->_messages);
		return key($this->_messages);
	}
	
	function removeMessage( $id )
	{
		if( ($k = array_search($id, $this->_messages)) !== false ) {
			unset($this->_messages[$k]);
		}
		if( ($k = array_search($id, $this->_newMessages)) !== false ) {
			unset($this->_newMessages[$k]);
		}
	}
	
	function commit()
	{
		$fThread = ApothFactory::_( 'message.Thread' );
		$fThread->commitInstance( $this->_id );
	}
}
?>