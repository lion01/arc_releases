<?php
/**
 * @package     Arc
 * @subpackage  Planner
 * @copyright   Copyright (C) 2005 Punnet. All rights reserved. See COPYRIGHT_ARC.txt
 * @license     http://www.gnu.org/licenses/gpl.html GNU/GPL. See LICENSE_ARC.txt
 * Arc is free software: you can redistribute it and/or modify
 * it under the terms of version 3 of the GNU General Public License
 * as published by the Free Software Foundation.
 * The disclaimer of warranty as stated in the GPL applies to this program
 */

defined( '_JEXEC' ) or die( 'Restricted access' );

/**
 * Planner Update Object
 */
class ApothUpdate extends JObject
{
	/**
	 * All the data for this update (equates to a row in the db)
	 * @access protected
	 * @var array
	 */
	var $_data = array();
	
	/**
	 * All evidence associated with this update
	 * @access protected
	 * @var array
	 */
	var $_evidence = array();
	
	/**
	 * All micro-tasks associated with this update
	 * @access protected
	 * @var array
	 */
	var $_micros = array();
	
	
	/**
	 * Constructs an update object.
	 * The result is either empty or if an id is given it is
	 * populated by the $data array or by retrieving data from the db 
	 * @param int $id  optional If not provided an empty update object is created.
	 * @param array $data  optional If given along with an id this is used as the data for the object (omits evidence and microtasks)
	 * @return object  The newly created update object
	 */
	function __construct( $id = false, $data = array() )
	{
		if( $id !== false ) {
			// get a database object
			$db = &JFactory::getDBO();
			
			// get/store the core data
			if( empty($data) ) {
				$data = $this->_loadCoreData( $id );
				
				// if the limit query has blocked data assimilation then return now
				if( empty($data) ) {
					return;
				}
			}
			$this->setCoreData( $data );
			
			// get evidence for the update
			$this->_loadEvidenceData( $id );
			
			// get microtasks for the update
			$this->_loadMicrotaskData( $id );
			
		}
	}
	
	/**
	 * Store the given core data in our class variables
	 * @param array $data  The data to store
	 */
	function setCoreData( $data )
	{
		$this->_data['id'] = (int)$data['id'];
		$this->setGroupId(        $data['group_id'] );
		$this->setCategory(       $data['category'] );
		$this->setText(           $data['text'] );
		$this->setProgress(       $data['progress'] );
		$this->setAuthor(         $data['author'] );
		$this->setDateAdded(      $data['date_added'] );
	}
	
	/**
	 * Loads the core data from the database and stores it in our class variables
	 * @param int $id  The id of the update whose data is to be retrieved
	 */
	function _loadCoreData( $id )
	{
		$db = &JFactory::getDBO();
		$mainQuery = 'SELECT '.$db->nameQuote( 't' ).'.*'
			."\n".' FROM '.$db->nameQuote( '#__apoth_plan_updates' ).' AS '.$db->nameQuote('u')
			."\n".'~LIMITINGJOIN~'
			."\n".' WHERE '.$db->nameQuote( 'u' ).'.'.$db->nameQuote( 'id' ).' = '.$db->Quote($id);
		$db->setQuery( ApotheosisLibAcl::limitQuery($mainQuery, 'planner.updates') );
		$data = $db->loadAssoc();
		
		return $data;
	}
	
	/**
	 * Loads the evidence-related data from the database and stores it in our class variables
	 * @param int $id  The id of the update whose data is to be retrieved
	 */
	function _loadEvidenceData( $id )
	{
		$db = &JFactory::getDBO();
		$evidenceQuery = 'SELECT *'
		."\n".' FROM '.$db->nameQuote( '#__apoth_plan_update_evidence' )
		."\n".' WHERE '. $db->nameQuote( 'update_id' ) .' = '.$db->Quote($id);
		$db->setQuery( $evidenceQuery );
		$evidenceRaw = $db->loadAssocList( 'id' );
		$owners = array();
		$evidence = array( 'file'=>array(), 'url'=>array() );
		foreach( $evidenceRaw as $evId=>$evRow ) {
			if( !is_null($evRow['file_owner']) ) {
				$evidence['file'][$evId] = $evRow['evidence'];
				$owners[$evId] = $evRow['file_owner'];
			}
			else {
				$evidence['url'][$evId] = $evRow['evidence'];
			}
		}
		$this->setEvidence( $evidence['url'], $evidence['file'], $owners );
	}
	
	/**
	 * Loads the microtask-related data from the database and stores it in our class variables
	 * @param int $id  The id of the update whose data is to be retrieved
	 */
	function _loadMicrotaskData( $id )
	{
		$db = &JFactory::getDBO();
		$microQuery = 'SELECT task_id'
		."\n".' FROM '.$db->nameQuote( '#__apoth_plan_update_microtasks' )
		."\n".' WHERE '. $db->nameQuote( 'update_id' ) .' = '.$db->Quote($id);
		$db->setQuery( $microQuery );
		$micros = $db->loadResultArray();
		$this->setMicros( $micros );
	}
	
	
	/**
	 * Retrieves the ID of the update
	 * @return int  The update ID
	 */
	function getId()
	{
		return (int)$this->_data['id'];
	}
	
	/**
	 * Retrieves the group ID this update is assigned to
	 * @return int  The ID of the group
	 */
	function getGroupId()
	{
		return (int)$this->_data['groupId'];
	}
	
	/**
	 * Sets the group ID that this update should be assigned to
	 * @param int $groupId  The group ID this update should be assigned to
	 */
	function setGroupId( $groupId )
	{
		$this->_data['groupId'] = (int)$groupId;
	}
	
	/**
	 * Retrieves the category for this update
	 * @return string  The update category
	 */
	function getCategory()
	{
		return $this->_data['category'];
	}
	
	/**
	 * Sets the category for this update
	 * @param string $cat  The category for this update
	 */
	function setCategory( $cat )
	{
		$this->_data['category'] = $cat;
	}
		
	/**
	 * Retrieves the text for this update
	 * @return string  The update text
	 */
	function getText()
	{
		return $this->_data['text'];
	}
	
	/**
	 * Sets the text for this update
	 * @param string $text  The text for this update
	 */
	function setText( $text )
	{
		$this->_data['text'] = $text;
	}
		
	/**
	 * Retrieves the progress for this update
	 * @return int  The update progress (%)
	 */
	function getProgress()
	{
		return $this->_data['progress'];
	}
	
	/**
	 * Sets the progress for this update
	 * @param int $progress  The progress for this update (%)
	 */
	function setProgress( $progress )
	{
		if( !is_numeric($progress) || ($progress === '') ) {
			$progress = null;
		}
		elseif( is_numeric($progress) && ($progress < 0) ) {
			$progress = 0;
		}
		elseif( is_numeric($progress) && ($progress > 100) ) {
			$progress = 100;
		}
		else {
			$progress = floor( $progress );
		}
		
		$this->_data['progress'] = $progress;
	}
	
	/**
	 * Retrieves the author person object for this update
	 * @return object  The update author person object
	 */
	function getAuthor()
	{
		return $this->_data['author']->displayname;
	}
	
	/**
	 * Sets the author person object for this update
	 * @param string $author  The Arc ID of the author of this update
	 */
	function setAuthor( $author )
	{
		// get a database object
		$db = &JFactory::getDBO();
		
		$this->_data['author'] = reset( ApotheosisLib::getUserList(' WHERE p.id = '.$db->Quote($author), false) );
	}
	
	/**
	 * Retrieves the date this update was added
	 * @return string  The update added date
	 */
	function getDateAdded()
	{
		return $this->_data['date_added'];
	}
	
	/**
	 * Sets the date this update was added
	 * @param string $dateAdded  The update added date
	 */
	function setDateAdded( $dateAdded )
	{
		$this->_data['date_added'] = $dateAdded;
	}
	
	/**
	 * Retrieves the id of the user who owns an evidence file
	 *
	 * @param string $eId  The evidence id number
	 * @return string|null  The id of the file owner, or null if not a file
	 */
	function getFileOwner( $eId )
	{
		return $this->_evidence['owners'][$eId];
	}
	
	/**
	 * Retrieves the evidence for this update, a URL most likely
	 *
	 * @param string $type  One of "url", "file", "both" to indicate the type of evidence to retrieve. Defaults to "both".
	 * @return array  evidence ID indexed array of the update evidence
	 */
	function getEvidence( $type = 'both' )
	{
		switch( $type ) {
		case( 'url' ):
			return ( empty($this->_evidence['url']) ? array() : $this->_evidence['url'] );
			break;
		
		case( 'file' ):
			return ( empty($this->_evidence['file']) ? array() : $this->_evidence['file'] );
			break;
		
		case( 'both' ):
		default:
			return ( empty($this->_evidence['url']) ? array() : $this->_evidence['url'] );
				+ ( empty($this->_evidence['file']) ? array() : $this->_evidence['file'] );
			break;
		}
		return $this->_evidence;
	}
	
	/**
	 * Sets the evidence for this update, a URL most likely
	 * @param array $urls  evidence ID indexed array of the update evidence
	 * @param array $files  evidence ID indexed array of the update evidence
	 * @param string $owners  evidence ID indexed array of the owner of any files
	 */
	function setEvidence( $urls, $files, $owners )
	{
		$this->_evidence['url'] = (array)$urls;
		$this->_evidence['file'] = (array)$files;
		$this->_evidence['owners'] = (array)$owners;
	}
	
	/**
	 * Sets the evidence for this update, a URL most likely
	 * @param array $urls  evidence ID indexed array of the update evidence
	 * @param array $files  evidence ID indexed array of the update evidence
	 * @param string $owner  person id of the owner of any new files
	 */
	function addEvidence( $urls, $files, $owner = false )
	{
		// Sort out the owner of any files
		$owners = array();
		if( !empty($files) ) {
			// Assume it's this user if none provided
			if( $owner == false ) {
				$u = ApotheosisLib::getUser();
				$owner = $u->person_id;
			}
			
			foreach( $files as $k=>$v ) {
				$owners[$k] = $owner;
			}
		}
		
		$this->_newEvidence['url']  = (array)$urls;
		$this->_newEvidence['file'] = (array)$files;
		$this->_newEvidence['owners'] = (array)$owners;
	}
	
	function removeEvidence( $id )
	{
		if( isset($this->_evidence['url'][$id]) ) {
			echo 'removing from url list<br />';
			unset( $this->_evidence['url'][$id] );
		}
		elseif( isset($this->_evidence['file'][$id]) ) {
			echo 'removing from file list<br />';
			$pId = $this->_evidence['owners'][$id];
			if( ApotheosisPeopleData::deleteFile($pId, $this->_evidence['file'][$id]) ) {
				echo 'deleted, now removing<br />';
				unset( $this->_evidence['file'][$id] );
				unset( $this->_evidence['owners'][$id] );
			}
		}
	}
	
	/**
	 * Retrieves the microtask ID's completed in this update
	 * @return array  indexed array of microtasks
	 */
	function getMicros()
	{
		return $this->_micros;
	}
	
	/**
	 * Sets the microtask ID's completed in this update
	 * @param array $micros  ID indexed array of microtasks
	 */
	function setMicros( $micros )
	{
		$this->_micros = $micros;
	}
	
	/**
	 * Commits the current state of an update by writing it to the database
	 *
	 * @param boolean $progress  Do we include the progress value?
	 * @return boolean|int  The update id on success, false on failure
	 */
	function commit()
	{
		$db = &JFactory::getDBO();
		
		// Deal with core data
		$queryMid = 'SET '
			     .$db->nameQuote( 'group_id' ).' = '.$db->Quote( $this->_data['groupId'] )
			.', '.$db->nameQuote( 'category' ).' = '.$db->Quote( $this->_data['category'] )
			.', '.$db->nameQuote( 'text' )    .' = '.$db->Quote( $this->_data['text'] )
			.', '.$db->nameQuote( 'author' )  .' = '.$db->Quote( $this->_data['author']->id )
			.', '.$db->nameQuote( 'progress' ).' = '.( is_null($this->_data['progress']) ? 'NULL' : $db->Quote( $this->_data['progress'] ) );
		
		// ... inserting or updating as appropriate
		if( empty($this->_data['id']) ) {
			$query = 'INSERT INTO '.$db->nameQuote( '#__apoth_plan_updates' )
				."\n".$queryMid
				.', '.$db->nameQuote( 'date_added' ).' = '.' NOW()';
		}
		else {
			$query = 'UPDATE '.$db->nameQuote( '#__apoth_plan_updates' )
				."\n".$queryMid
				."\n".' WHERE '.$db->nameQuote( 'id' ).' = '.$db->Quote( $this->_data['id'] );
		}
		$db->setQuery( $query );
		$db->Query();
		// early abort if we couldn't even put in the core data
		if( $db->getErrorMsg() != '' ) {
			return false;
		}
		if( empty($this->_data['id']) ) {
			$this->_data['id'] = $db->insertid();
		}
		$id = $this->_data['id'];
				
		// Deal with old evidence (updates)
		if( !empty($this->_evidence['url']) || !empty($this->_evidence['file']) ) {
			$eIdList = array();
			$qStr = 'UPDATE '.$db->nameQuote( '#__apoth_plan_update_evidence' )
				."\n".' SET '
				."\n".'  '.$db->nameQuote('update_id') .' = %2$s'
				."\n".', '.$db->nameQuote('evidence')  .' = %3$s'
				."\n".', '.$db->nameQuote('file_owner').' = %4$s'
				."\n".' WHERE '
				."\n".'  '.$db->nameQuote('id')        .' = %1$s'
				."\n".' LIMIT 1';
			if( !empty($this->_evidence['url']) ) {
				foreach( $this->_evidence['url'] as $eId=>$e ) {
					if( !empty($e) ) {
						$eIdList[] = $db->Quote($eId);
						$query = sprintf( $qStr, $db->Quote($eId), $db->Quote($id), $db->Quote($e), 'NULL' );
						$db->setQuery( $query );
						$db->Query();
					}
				}
			}
			if( !empty($this->_evidence['file']) ){
				foreach( $this->_evidence['file'] as $eId=>$e ) {
					if( !empty($e) ) {
						$eIdList[] = $db->Quote($eId);
						$query = sprintf( $qStr, $db->Quote($eId), $db->Quote($id), $db->Quote($e), $db->Quote($this->_evidence['owners'][$eId]) );
						$db->setQuery( $query );
						$db->Query();
					}
				}
			}
		}
		
		// Deal with old evidence (remove)
		$query = 'DELETE FROM '.$db->nameQuote( '#__apoth_plan_update_evidence' )
			."\n".' WHERE '.$db->nameQuote( 'update_id' ).' = '.$db->Quote( $id )
			.( !empty($eIdList) ? "\n".'   AND '.$db->nameQuote( 'id' ).' NOT IN ('.implode(', ', $eIdList).')' : '' );
		$db->setQuery( $query );
		$db->Query();
		
		// Deal with new evidence (insert)
		$values = array();
		if( !empty($this->_newEvidence['url']) ) {
			foreach( $this->_newEvidence['url'] as $eId=>$e ) {
				if( !empty($e) ) {
					$values[] = '( NULL, '.$db->Quote($id).', '.$db->Quote($e).', NULL)';
					$this->_evidence['url'][] = $e;
				}
			}
		}
		if( !empty($this->_newEvidence['file']) ) {
			foreach( $this->_newEvidence['file'] as $eId=>$e ) {
				if( !empty($e) ) {
					$values[] = '( NULL, '.$db->Quote($id).', '.$db->Quote($e).', '.$db->Quote($this->_newEvidence['owners'][$eId]).')';
					$this->_evidence['file'][] = $e;
					$this->_evidence['owners'][] = $this->_newEvidence['owners'][$eId];
				}
			}
		}
		if( !empty($values) ) {
			$query = 'INSERT INTO '.$db->nameQuote('#__apoth_plan_update_evidence')
				.' ('.$db->nameQuote('id')
				.', '.$db->nameQuote('update_id')
				.', '.$db->nameQuote('evidence')
				.', '.$db->nameQuote('file_owner').')'
				."\n".' VALUES'
				."\n".implode( ",\n", $values );
			$db->setQuery( $query );
			$db->Query();
		}
		
		// Deal with microtasks
		$values = array();
		foreach( $this->_micros as $m ) {
			if( !empty($m) ) {
				$values[] = '('.$db->Quote($m).', '.$db->Quote($id).')';
			}
		}
		if( !empty($values) ) {
			$query = 'INSERT INTO '.$db->nameQuote('#__apoth_plan_update_microtasks')
				."\n".' VALUES'
				."\n".implode( ",\n", $values );
			$db->setQuery( $query );
			$db->Query();
		}
		
		return $id;
	}
	
	/**
	 * Remove this update from the database.
	 * Foreign key checks will get rid of its updates.
	 */
	function delete()
	{
		$db = &JFactory::getDBO();
		
		$query = 'DELETE u'
			."\n".'FROM '.$db->nameQuote( '#__apoth_plan_updates' ).' AS '.$db->nameQuote('u')
			."\n".'~LIMITINGJOIN~'
			."\n".'WHERE '.$db->nameQuote( 'u' ).'.'.$db->nameQuote( 'id' ).' = '.$db->Quote( $this->_data['id'] );
		$db->setQuery( ApotheosisLibAcl::limitQuery($query, 'planner.updates') );
		$db->Query();
	}
}
?>