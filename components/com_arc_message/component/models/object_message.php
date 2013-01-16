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
 * Messaging Message Factory
 */
class ApothFactory_Message_Message extends ApothFactory
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
			$r = new ApothMessage( array('id'=>$id, 'tags'=>array('gen'=>array(), 'per'=>array()), 'data'=>array()) );
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
			$mId = $db->Quote( $id );
			$query = 'SELECT m.*, tm.tag_id, tm.person_id AS tag_person, IFNULL(t.id, -1) AS thread'
				."\n".'FROM '.$db->nameQuote( '#__apoth_msg_messages' ).' AS m'
				."\n".'INNER JOIN '.$db->nameQuote( '#__apoth_msg_tag_map' ).' AS tm'
				."\n".'   ON tm.msg_id = m.id'
				."\n".'  AND (tm.person_id = '.$personId.' OR tm.person_id IS NULL)'
				."\n".'INNER JOIN '.$db->nameQuote( '#__apoth_msg_threads' ).' AS t'
				."\n".'  ON t.msg_id = m.id'
				."\n".'WHERE m.id = '.$mId
				."\n".'  AND '.ApotheosisLibDb::dateCheckSql( 'tm.valid_from', 'tm.valid_to', $this->_date, $this->_date );
			$db->setQuery($query);
			$r = $db->loadAssocList();
			$data = reset($r);
			
			$data['tags']['gen'] = array();
			$data['tags']['per'] = array();
			foreach( $r as $tag ) {
				if( is_null($tag['tag_person']) ) {
					$data['tags']['gen'][] = $tag['tag_id'];
				}
				else {
					$data['tags']['per'][] = $tag['tag_id'];
				}
			}
			unset($data['tag_id']);
			unset($data['tag_person']);
			
			$data['data'] = array();
			$query = 'SELECT *'
				."\n".'FROM '.$db->nameQuote( '#__apoth_msg_data' ).' AS d'
				."\n".'WHERE d.msg_id = '.$mId
				."\n".'  AND '.ApotheosisLibDb::dateCheckSql( 'd.valid_from', 'd.valid_to', $this->_date, $this->_date );
			$db->setQuery($query);
			$d2 = $db->loadAssocList();
			if( !is_array($d2) ) { $d2 = array(); }
			foreach( $d2 as $row ) {
				$data['data'][$row['col_id']] = $row['data'];
			}
			
			$r = new ApothMessage( $data );
			$this->_addInstance( $id, $r );
		}
		return $r;
	}
	
	function &getInstances( $requirements )
	{
		$sId = $this->_getSearchId( $requirements );
		$ids = $this->_getInstances($sId);
		if( is_null($ids) ) {
			$db = &JFactory::getDBO();
			$u = &ApotheosisLib::getUser();
			$personId = $db->Quote($u->person_id);
			
			$where = array();
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
					$where[] = 'm.id '.$assignPart;
					break;
				
				case( 'tag' ):
				case( 'tags' ):
					$count = ( is_array($val) ? count($val) : 1 );
					$q = 'CREATE TEMPORARY TABLE tmp_msg_tags AS'
						."\n".'SELECT DISTINCT m.id, tm.tag_id'
						."\n".'FROM '.$db->nameQuote( '#__apoth_msg_messages' ).' AS m'
						."\n".'INNER JOIN '.$db->nameQuote( '#__apoth_msg_tag_map' ).' AS tm'
						."\n".'   ON tm.msg_id = m.id'
						."\n".'  AND (tm.person_id = '.$personId.' OR tm.person_id IS NULL)'
						."\n".'  AND '.ApotheosisLibDb::dateCheckSql( 'tm.valid_from', 'tm.valid_to', $this->_date, $this->_date )
						."\n".'WHERE tm.tag_id '.$assignPart.';'
						."\n"
						."\n".'CREATE TABLE ~TABLE~ AS'
						."\n".'SELECT id'
						."\n".'FROM tmp_msg_tags'
						."\n".'GROUP BY id'
						."\n".'HAVING COUNT(*) = '.$count.';'
						."\n"
						."\n".'DROP TABLE tmp_msg_tags;';
					$tTbl = ApotheosisLibDbTmp::initTable( $q, true, 'msg', 'thread_tags' );
					ApotheosisLibDbTmp::setTtl( $tTbl, 30 );
					$joins[] = 'INNER JOIN '.$tTbl.' AS tmp_tags'
						."\n".'   ON tmp_tags.id = m.id';
					break;
				
				case( 'text' ):
					if( is_array($val) ) {
						$count = count($val);
					}
					else {
						$count = 1;
						$val = array($val);
					}
					$q = 'CREATE TABLE ~TABLE~ ( id INT, INDEX (`id`) )';
					$wTbl = ApotheosisLibDbTmp::initTable( $q, true, 'msg', 'thread_words' );
					ApotheosisLibDbTmp::setTtl( $wTbl, 30 );
					
					$insQuery = 'CREATE TEMPORARY TABLE words ('
						."\n".'  `word` VARCHAR( 50 ),'
						."\n".'  INDEX (`word`)'
						."\n".');'
						."\n".''
						."\n".'INSERT INTO words VALUES'
						."\n".'('.implode('), (', $val).');'
						."\n".''
						."\n".'CREATE TEMPORARY TABLE msg_words AS'
						."\n".'SELECT DISTINCT m.id, w.word'
						."\n".'FROM jos_apoth_msg_messages AS m'
						."\n".'INNER JOIN jos_apoth_msg_data AS d'
						."\n".'   ON d.msg_id = m.id'
						."\n".'INNER JOIN jos_apoth_ppl_people AS p'
						."\n".'   ON p.id = m.author'
						."\n".'LEFT JOIN jos_apoth_ppl_people AS p2'
						."\n".'  ON p2.id = d.data' // not every row is a pupil id row
						."\n".'INNER JOIN words AS w'
						."\n".'   ON d.data LIKE CONCAT("%", w.word, "%")'
						."\n".'   OR CONCAT_WS( ",",  p.title,  p.firstname,  p.middlenames,  p.surname,  p.preferred_firstname,  p.preferred_surname) LIKE CONCAT("%", w.word, "%")'
						."\n".'   OR CONCAT_WS( ",", p2.title, p2.firstname, p2.middlenames, p2.surname, p2.preferred_firstname, p2.preferred_surname) LIKE CONCAT("%", w.word, "%")'
						."\n".'WHERE valid_from < NOW()'
						."\n".'  AND (valid_to > NOW() OR valid_to IS NULL);'
						."\n".''
						."\n".'INSERT INTO '.$wTbl
						."\n".'SELECT id'
						."\n".'FROM msg_words'
						."\n".'GROUP BY id'
						."\n".'HAVING COUNT(*) = '.$count;
					$db->setQuery($insQuery);
					$db->queryBatch();
					
					$joins[] = 'INNER JOIN '.$wTbl.' AS tmp_words'
						."\n".'   ON tmp_words.id = m.id';
					break;
				
				case( 'restrict' ):
					$joins[] = 'INNER JOIN '.$db->nameQuote( '#__apoth_msg_tag_map' ).' AS tm'
						."\n".'   ON tm.msg_id = m.id'
						."\n".'  AND tm.person_id = '.$personId
						."\n".'  AND '.ApotheosisLibDb::dateCheckSql( 'tm.valid_from', 'tm.valid_to', $this->_date, $this->_date )
						."\n".'INNER JOIN '.$db->nameQuote( '#__apoth_msg_tags' ).' AS tag'
						."\n".'   ON tag.id = tm.tag_id'
						."\n".'  AND tag.enabled = 1';
						break;
				
				case( 'student_id'):
					$joins[] = 'INNER JOIN '.$db->nameQuote( '#__apoth_msg_data').' AS mdata1'
						."\n".'   ON mdata1.msg_id = m.id';
					$where[] = 'mdata1.col_id = '.$db->Quote( 'student_id' );
					$where[] = 'mdata1.data '.$assignPart;
					break;
					
				case( 'group_id'):
					$joins[] = 'INNER JOIN '.$db->nameQuote( '#__apoth_msg_data').' AS mdata2'
						."\n".'   ON mdata2.msg_id = m.id';
					$where[] = 'mdata2.col_id = '.$db->Quote( 'group_id' );
					$where[] = 'mdata2.data '.$assignPart;
					break;
				
				case( 'date' ):
					$where[] = 'DATE( COALESCE( m.applies_on, m.created ) ) '.$assignPart;
					break;
				
				case( 'first' ):
					$joins[] = 'INNER JOIN '.$db->nameQuote( '#__apoth_msg_threads' ).' AS t'
						."\n".'   ON t.msg_id = m.id'
						."\n".'  AND t.'.$db->nameQuote('order').' '.((bool)$val ? '=' : '!=').' 1';
					break;
				
				}
			}
			
			$query = 'SELECT m.id'
				."\n".'FROM '.$db->nameQuote( '#__apoth_msg_messages' ).' AS m'
				.( empty($joins) ? '' : "\n".implode("\n", $joins) )
				.( empty($where)  ? '' : "\nWHERE " .implode("\n AND ", $where) )
				.( empty($having) ? '' : "\nHAVING ".implode("\n AND ", $having) );
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
		$isNew = ( $id < 0 );
		$u = &ApotheosisLib::getUser();
		
		// Set up core data
		$applies = $r->getDate();
		$applies = ( is_null($applies) ? 'NULL' : $db->Quote($applies) );
		if( $isNew ) {
			$now = $r->getCreated();
			$dbNow = $db->Quote( $now );
			$query = 'INSERT INTO '.$db->nameQuote( '#__apoth_msg_messages' )
				."\n".'SET '
				."\n  ".$db->nameQuote('handler').' = '.$db->Quote($r->getHandler())
				."\n, ".$db->nameQuote('author').' = '.$db->Quote($r->getAuthor())
				."\n, ".$db->nameQuote('created').' = '.$dbNow
				."\n, ".$db->nameQuote('applies_on').' = '.$applies;
		}
		else {
			$now = date('Y-m-d H:i:s', (strtotime($this->_date)));
			$dbNow = $db->Quote( $now );
			$query = 'UPDATE '.$db->nameQuote( '#__apoth_msg_messages' )
				."\n".'SET '
				."\n  ".$db->nameQuote('id').' = '.$db->Quote($id)
				."\n, ".$db->nameQuote('handler').' = '.$db->Quote($r->getHandler())
				."\n, ".$db->nameQuote('author').' = '.$db->Quote($r->getAuthor())
				."\n, ".$db->nameQuote('applies_on').' = '.$applies
				."\n, ".$db->nameQuote('last_modified').' = '.$dbNow
				."\n, ".$db->nameQuote('last_modified_by').' = '.$db->Quote($u->person_id)
				."\n".' WHERE '.$db->nameQuote('id').' = '.$db->Quote($id);
		}
		$then = $db->Quote( date('Y-m-d H:i:s', (strtotime($now) - 1)) );
		$db->setQuery( $query );
		$db->Query();
		
		// early abort if we couldn't even put in the core data
		if( $db->getErrorMsg() != '' ) {
			return false;
		}
		
		// Get our proper id if we're doing a new entry
		$fThread = ApothFactory::_( 'message.Thread' );
		$fThread->setDate();
		$this->setDate();
		$this->_clearCachedInstances( $id );
		$this->_clearCachedSearches();
		if( $isNew ) {
			$oldId = $id;
			$r->setId( $db->insertid() );
			$id = $r->getId();
			
			// Remove the dummy message and add the new message to its thread
			$thread = &$fThread->getInstance( $r->getThreadId() );
			$thread->removeMessage( $oldId );
			$thread->addMessage( $id );
			$thread->commit();
		}
		else {
			// the thread's commit function does this, so new messages are dealt with
			// but changes to existing messages must also trigger clearing the searches
			$fThread->_clearCachedInstances();
			$fThread->_clearCachedSearches();
		}
		
		// Set up message data
		$d = $r->getData();
		$values = array();
		$v1 = '( '.$db->Quote($id).', ';
		$v2 = ' )';
		foreach( $d as $k=>$v ) {
			$values[] = $v1.$db->Quote($k).', '.$db->Quote($v).$v2;
		}
		
		$tmpTbl = $db->nameQuote( 'tmp_message_'.$id.'_'.time() );
		$query = 'START TRANSACTION;'
			."\n"
			."\n".'CREATE TEMPORARY TABLE '.$tmpTbl.' AS '
			."\n".'SELECT '.$db->nameQuote('msg_id')
			          .', '.$db->nameQuote('col_id')
			          .', '.$db->nameQuote('data')
			."\n".'FROM '.$db->nameQuote('#__apoth_msg_data')
			."\n".'LIMIT 0;'
			."\n";
		if( !empty($values) ) {
			$query .= "\n".'INSERT INTO '.$tmpTbl
				."\n".'VALUES'
				."\n".implode("\n, ", $values).';'
				."\n";
		}
		$query .= "\n".'UPDATE jos_apoth_msg_data AS d'
			."\n".'LEFT JOIN '.$tmpTbl.' AS t'
			."\n".'  ON t.msg_id = d.msg_id'
			."\n".' AND t.col_id = d.col_id'
			."\n".' AND t.data = d.data'
			."\n".'SET valid_to = '.$then
			."\n".'WHERE d.msg_id = '.$db->Quote($id)
			."\n".'  AND d.valid_to IS NULL'
			."\n".'  AND t.msg_id IS NULL;'
			."\n"
			."\n".'INSERT INTO '.$db->nameQuote('#__apoth_msg_data')
			."\n".'SELECT t.msg_id, t.col_id, t.data, '.$dbNow.' AS valid_from, NULL'
			."\n".'FROM jos_apoth_msg_data AS d'
			."\n".'RIGHT JOIN '.$tmpTbl.' AS t'
			."\n".'   ON t.msg_id = d.msg_id'
			."\n".'  AND t.col_id = d.col_id'
			."\n".'  AND t.data = d.data'
			."\n".'  AND d.valid_to IS NULL'
			."\n".'WHERE t.msg_id = '.$db->Quote($id)
			."\n".'  AND d.msg_id IS NULL;'
			."\n"
			."\n".'DROP TABLE '.$tmpTbl.';'
			."\n"
			."\n".'COMMIT;';
		$db->setQuery($query);
		$db->queryBatch();
		
		
		// Set up tags in tag map.
		$values = array();
		// ... first the personal ones (from the recipient list)
		$recipients = $r->getRecipients();
		$v1 = '( '.$db->Quote($id).', ';
		$v2 = ' )';
		foreach( $recipients as $pId=>$froms ) {
			foreach( $froms as $fId=>$tId ) {
				$values[] = $v1.$db->Quote($pId).', '.$db->Quote($tId).', '.( empty($fId) ? 'NULL' : $db->Quote($fId) ).$v2;
			}
		}
		// ... then the general ones (from the tag list)
		$tagsGen = $r->getTagIds( false ); // get all the non-person-specific tags
		$v1 = '( '.$db->Quote($id).', NULL, ';
		$v2 = ', NULL )';
		foreach( $tagsGen as $tag ) {
			$values[] = $v1.$db->Quote($tag).$v2;
		}
		
		$tmpTbl = $db->nameQuote( 'tmp_tags_'.$id.'_'.time() );
		$query = 'START TRANSACTION;'
			."\n"
			."\n".'CREATE TEMPORARY TABLE '.$tmpTbl.' AS '
			."\n".'SELECT '.$db->nameQuote('msg_id')
			          .', '.$db->nameQuote('person_id')
			          .', '.$db->nameQuote('tag_id')
			          .', '.$db->nameQuote('from_channel')
			."\n".'FROM '.$db->nameQuote('#__apoth_msg_tag_map')
			."\n".'LIMIT 0;'
			."\n";
		if( !empty($values) ) {
			$query .= "\n".'INSERT INTO '.$tmpTbl
				."\n".'VALUES'
				."\n".implode("\n, ", $values).';'
				."\n"
				."\n".'ALTER TABLE '.$tmpTbl
				."\n".'  ADD INDEX (`msg_id`)'
				."\n".', ADD INDEX (`person_id`)'
				."\n".', ADD INDEX (`tag_id`);';
		}
		$query .= "\n".'UPDATE jos_apoth_msg_tag_map AS m'
			."\n".'LEFT JOIN '.$tmpTbl.' AS t'
			."\n".'  ON t.msg_id = m.msg_id'
			."\n".' AND t.person_id <=> m.person_id'
			."\n".' AND t.tag_id = m.tag_id'
			."\n".'SET valid_to = '.$then
			."\n".'WHERE m.msg_id = '.$db->Quote($id)
			."\n".'  AND m.valid_to IS NULL'
			."\n".'  AND t.msg_id IS NULL;'
			."\n"
			."\n".'INSERT INTO '.$db->nameQuote('#__apoth_msg_tag_map')
			."\n".'SELECT t.msg_id, t.person_id, t.tag_id, t.from_channel, '.$dbNow.' AS valid_from, NULL'
			."\n".'FROM jos_apoth_msg_tag_map AS m'
			."\n".'RIGHT JOIN '.$tmpTbl.' AS t'
			."\n".'   ON t.msg_id = m.msg_id'
			."\n".'  AND t.person_id <=> m.person_id'
			."\n".'  AND t.tag_id = m.tag_id'
			."\n".'  AND m.valid_to IS NULL'
			."\n".'WHERE t.msg_id = '.$db->Quote($id)
			."\n".'  AND m.msg_id IS NULL;'
			."\n"
			."\n".'DROP TABLE '.$tmpTbl.';'
			."\n"
			."\n".'COMMIT;';
		$db->setQuery($query);
		$db->queryBatch();
		
		// add the new message to the permissions tables
		// ... first the recipients
		$ids = ApotheosisLib::getJUserIds( array_keys($recipients) );
		foreach( $ids as $pId ) {
			$tbl = ApotheosisLibDbTmp::getTable( 'message', 'messages', $pId, false, false, false );
			if( ApotheosisLibDbTmp::getExists( $tbl ) && ApotheosisLibDbTmp::getPopulated( $tbl ) ) {
				$db->setQuery( 'INSERT INTO '.$db->nameQuote($tbl).' VALUES ('.$db->Quote($id).') ');
				$db->Query();
			}
		}
		// ... then the sender
		$tbl = ApotheosisLibDbTmp::getTable( 'message', 'messages', $u->id, false, false, false );
		if( ApotheosisLibDbTmp::getExists( $tbl ) && ApotheosisLibDbTmp::getPopulated( $tbl ) ) {
			$db->setQuery( 'INSERT INTO '.$db->nameQuote($tbl).' VALUES ('.$db->Quote($id).') ');
			$db->Query();
		}
		
		// **** Execute any post-commit hooks based on tags (eg: behaviour, new)
		return $db->getErrorMsg() == '';
	}
	
	function loadRecipients( $id )
	{
		$db = &JFactory::getDBO();
		$query = 'SELECT DISTINCT tm.person_id, tm.tag_id, tm.from_channel'
			."\n".'FROM `jos_apoth_msg_tag_map` AS tm'
			."\n".'INNER JOIN jos_apoth_msg_tags AS t'
			."\n".'   ON t.id = tm.tag_id'
			."\n".'WHERE t.category = '.$db->Quote('folder')
			."\n".'  AND '.ApotheosisLibDb::dateCheckSql( 'tm.valid_from', 'tm.valid_to', $this->_date, $this->_date )
			."\n".'  AND tm.person_id IS NOT NULL' // folders must be person-specific
			."\n".'  AND tm.msg_id = '.$db->Quote($id);
		$db->setQuery( $query );
		$r = $db->loadAssocList();
		
		$retVal = array();
		foreach( $r as $row ) {
			$retVal[$row['person_id']][$row['from_channel']] = $row['tag_id'];
		}
		return $retVal;
	}
}


/**
 * Messaging Message Object
 */
class ApothMessage extends JObject
{
	/**
	 * All the data for this message (equates to a row in the db)
	 * @access protected
	 * @var array
	 */
	var $_core = array();
	
	/**
	 * All the data for this message (equates to a row in the db)
	 * @access protected
	 * @var array
	 */
	var $_data = array();
	
	/**
	 * All the data for this message (equates to a row in the db)
	 * @access protected
	 * @var array
	 */
	var $_tags = array();
	
	function __construct( $data )
	{
		$this->_data = $data['data'];
		$this->setTags( $data['tags']['gen'], $data['tags']['per'] );
		unset( $data['data'] );
		unset( $data['tags'] );
		$this->_core = $data;
		$this->_showDetails = false;
		$this->_date = null;
	}
	
	function getDetailsShown()
	{
		return $this->_showDetails;
	}
	
	function setDetailsShown( $val = null )
	{
		$this->_showDetails = ( is_null($val) ? !$this->_showDetails : (bool)$val );
	}
	
	/**
	 * Accessor functions to retrieve core data
	 */
	function getId()
	{
		return $this->_core['id'];
	}
	
	function getHandler()
	{
		return $this->_core['handler'];
	}
	
	function getAuthor()
	{
		if( !isset($this->_core['author']) ) {
			$u = ApotheosisLib::getUser();
			$retVal = $u->person_id;
		}
		else {
			$retVal = $this->_core['author'];
		}
		return $retVal;
	}
	
	function getCreated()
	{
		return (isset($this->_core['created']) ? $this->_core['created'] : date('Y-m-d H:i:s'));
	}
	
	function getDate()
	{
		if( !isset($this->_date) ) {
			if( isset($this->_core['applies_on'])  ) {
				$this->_date = $this->_core['applies_on'];
			}
			elseif( isset($this->_core['created']) ) {
				$this->_date = $this->_core['created'];
			}
			else {
				$this->_date = date('Y-m-d H:i:s');
			}
		}
		return $this->_date;
	}
	
	function setId($val)      { $this->_core['id']         = $val; }
	function setHandler($val) { $this->_core['handler']    = preg_replace( '~[^a-zA-Z0-9_]~', '', $val ); }
	function setAuthor($val)  { $this->_core['author']     = $val; }
	function setCreated($val) { $this->_core['created']    = $val; }
	function setDate($val)    { $this->_core['applies_on'] = $val; }
	
	function setTags( $general = array(), $personal = array() )
	{
		if( !is_array( $general  ) ) { $general  = array(); }
		if( !is_array( $personal ) ) { $personal = array(); }
		$general  = array_unique( $general );
		$personal = array_unique( $personal );
		$this->_tagLabels = array();
		$this->_tags = array();
		$this->_personalTags = array();
		$this->_generalTags = array();
		$fTag = ApothFactory::_( 'message.Tag' );
		if( !is_array($general)  ) { $general  = array(); }
		if( !is_array($personal) ) { $personal = array(); }
		foreach( $general as $tagId ) {
			$tag = &$fTag->getInstance( $tagId );
			if( $tag->getId() == -1 ) {
				continue; // don't process non-existent tags
			}
			$this->_tagLabels[$tag->getCategory()][$tagId] = $tag->getLabel();
			$this->_tags[$tagId] = $tag;
			$this->_generalTags[] = $tagId;
		}
		foreach( $personal as $tagId ) {
			$tag = &$fTag->getInstance( $tagId );
			if( $tag->getId() == -1 ) {
				continue; // don't process non-existent tags
			}
			$this->_tagLabels[$tag->getCategory()][$tagId] = $tag->getLabel();
			$this->_tags[$tagId] = $tag;
			$this->_personalTags[] = $tagId;
		}
	}
	
	function getTagIds( $personalOnly = null )
	{
		if( is_null($personalOnly) ) {
			return array_keys($this->_tags);
		}
		if( $personalOnly ) {
			return $this->_personalTags;
		}
		else {
			return $this->_generalTags;
		}
	}
	
	/**
	 * Retrieve the labels of tags for this message in the given category if given
	 * 
	 * @param string $cat  The label of the category to look in
	 * @return array  The array of labels for this message in the given category
	 */
	function getTagLabels( $cat = null )
	{
		if( is_null($cat) ) {
			$r = array();
			foreach( $this->_tagLabels as $cat=>$labels ) {
				$r += $labels;
			}
			return $r;
		}
		elseif( !isset($this->_tagLabels[$cat]) ) {
			return array();
		}
		else {
			return $this->_tagLabels[$cat];
		}
	}
	
	function setRecipients( $data )
	{
		$this->_recipients = $data;
	}
	
	function getRecipients( $folder = null )
	{
		if( !isset($this->_recipients) ) {
			$this->_loadRecipients();
		}
		
		if( is_null($folder) ) {
			return $this->_recipients;
		}
		elseif( !isset($this->_recipients[$folder]) ) {
			return array();
		}
		else {
			return $this->_recipients[$folder];
		}
	}
	
	function _loadRecipients()
	{
		$fMessage = ApothFactory::_( 'message.Message' );
		$this->_recipients = $fMessage->loadRecipients( $this->_core['id'] );
	}
	
	/**
	 * Move a message from one folder (tag) to another for the current user
	 * 
	 * @param int $from  The tag to move from
	 * @param int $to  The tag to move to
	 */
	function move( $from, $to )
	{
		// update the tags
		// ... get all the tags currently applied to this message
		$tIds = $this->getTagIds( true );
		// ... remove the old tag
		if( is_array($tIds) ) {
			$k = array_search($from, $tIds);
			if( $k !== false ) {
				unset( $tIds[$k] );
			}
		}
		// ... add the new tag
		$tIds[] = $to;
		$this->setTags( $this->getTagIds(false), $tIds );
		
		// update the recipients list
		$u = ApotheosisLib::getUser();
		$pId = $u->person_id;
		$r = $this->getRecipients();
		if( isset($r[$pId]) ) {
			foreach( $r[$pId] as $channel=>$tag ) {
				if( $tag == $from ) {
					unset( $r[$pId][$channel] );
				}
			}
			$r[$pId][''] = $to;
		}
		$this->setRecipients( $r );
	}
	
	function getData()
	{
		return ( is_array($this->_data) ? $this->_data : array() );
	}
	
	function getDatum( $field )
	{
		return ( isset($this->_data[$field]) ? $this->_data[$field] : null );
	}
	
	function setDatum( $field, $val )
	{
		$this->_data[$field] = $val;
	}
	
	function setThreadId( $threadId )
	{
		$this->_core['thread'] = (int)$threadId;
	}
	
	function getThreadId()
	{
		if( !isset($this->_core['thread']) ) {
			$this->_core['thread'] = -1;
		}
		return $this->_core['thread'];
	}
	
	function &getPreviousMessage()
	{
		$fThread = ApothFactory::_( 'message.Thread' );
		$fMessage = ApothFactory::_( 'message.Message' );
		$t = $fThread->getInstance( $this->getThreadId() );
		$id = $t->getMessageidBefore($this->_core['id']);
		if( is_null($id) ) {
			$this->_prev = null;
		}
		else {
			$this->_prev = &$fMessage->getInstance( $id );
		}
		return $this->_prev;
	}
	
	function commit()
	{
		$fMsg = ApothFactory::_( 'message.Message' );
		return $fMsg->commitInstance( $this->_core['id'] );
	}
}
?>