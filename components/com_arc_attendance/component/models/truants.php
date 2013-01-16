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

require_once( JPATH_COMPONENT.DS.'models'.DS.'extension.php' );

/**
 * Attendance Manager Truants Model
 *
 * @author		lightinthedark <code@lightinthedark.org.uk>
 * @package		Apotheosis
 * @subpackage	Attendance
 * @since 0.1
 */
class AttendanceModelTruants extends AttendanceModel
{
	/**
	 * Loads the list of current truants
	 */
	function _loadTruants()
	{
		$db = &JFactory::getDBO();
		$query = 'SELECT '.$db->nameQuote('ppl').'.'.$db->nameQuote('id').', '
			.$db->nameQuote('ppl').'.'.$db->nameQuote('title').', '
			.$db->nameQuote('ppl').'.'.$db->nameQuote('firstname').', '
			.$db->nameQuote('ppl').'.'.$db->nameQuote('middlenames').', '
			.$db->nameQuote('ppl').'.'.$db->nameQuote('surname')
			."\n".'FROM '.$db->nameQuote('#__apoth_ppl_people').' AS '.$db->nameQuote('ppl')
			."\n".'INNER JOIN '.$db->nameQuote('#__apoth_att_truants').' AS '.$db->nameQuote('t')
			."\n".'   ON '.$db->nameQuote('t').'.'.$db->nameQuote('pupil_id').' = '.$db->nameQuote('ppl').'.'.$db->nameQuote('id')
			."\n".'  AND '.ApotheosisLibDb::dateCheckSql( 't.valid_from', 't.valid_to', date('Y-m-d H:i:s'), date('Y-m-d H:i:s') )
			."\n".'ORDER BY '.$db->nameQuote('ppl').'.'.$db->nameQuote('surname').', '.$db->nameQuote('ppl').'.'.$db->nameQuote('firstname');
		$db->setQuery( $query );
		$this->_truants = $db->loadObjectList( 'id' );
		
		foreach( $this->_truants as $k=>$truant ) {
			$this->_truants[$k]->displayname = ApotheosisLib::nameCase( 'pupil', $truant->title, $truant->firstname, $truant->middlenames, $truant->surname );
			unset( $this->_truants[$k]->title, $this->_truants[$k]->firstname, $this->_truants[$k]->middlenames, $this->_truants[$k]->surname );
		}
	}
	
	/**
	 * Fetch the list of truants, loading if necessary
	 * 
	 * @return array  list of truants
	 */
	function getTruants()
	{
		if( !isset( $this->_truants ) ) {
			$this->_loadTruants();
		}
		
		return $this->_truants;
	}
	
	/**
	 * Loads the list of all non-truants
	 */
	function _loadNonTruants()
	{
		$db = &JFactory::getDBO();
		$query = 'SELECT DISTINCT '.$db->nameQuote('ppl').'.'.$db->nameQuote('id').', '
			.$db->nameQuote('ppl').'.'.$db->nameQuote('title').', '
			.$db->nameQuote('ppl').'.'.$db->nameQuote('firstname').', '
			.$db->nameQuote('ppl').'.'.$db->nameQuote('middlenames').', '
			.$db->nameQuote('ppl').'.'.$db->nameQuote('surname')
			."\n".'FROM '.$db->nameQuote('#__apoth_ppl_people').' AS '.$db->nameQuote('ppl')
			."\n".'INNER JOIN '.$db->nameQuote('#__apoth_tt_group_members').' AS '.$db->nameQuote('gm')
			."\n".'   ON '.$db->nameQuote('gm').'.'.$db->nameQuote('person_id').' = '.$db->nameQuote('ppl').'.'.$db->nameQuote('id')
			."\n".'  AND '.$db->nameQuote('gm').'.'.$db->nameQuote('role').' = '.ApotheosisLibAcl::getRoleId('group_participant_student')
			."\n".'  AND '.ApotheosisLibDb::dateCheckSql( 'gm.valid_from', 'gm.valid_to', date('Y-m-d H:i:s'), date('Y-m-d H:i:s') )
			."\n".'LEFT JOIN '.$db->nameQuote('#__apoth_att_truants').' AS '.$db->nameQuote('t')
			."\n".'   ON '.$db->nameQuote('t').'.'.$db->nameQuote('pupil_id').' = '.$db->nameQuote('ppl').'.'.$db->nameQuote('id')
			."\n".'  AND '.ApotheosisLibDb::dateCheckSql( 't.valid_from', 't.valid_to', date('Y-m-d H:i:s'), date('Y-m-d H:i:s') )
			."\n".'WHERE '.$db->nameQuote('t').'.'.$db->nameQuote('pupil_id').' IS NULL'
			."\n".'ORDER BY '.$db->nameQuote('ppl').'.'.$db->nameQuote('surname').', '.$db->nameQuote('ppl').'.'.$db->nameQuote('firstname');
		$db->setQuery( $query );
		$this->_nonTruants = $db->loadObjectList( 'id' );
		
		foreach( $this->_nonTruants as $k=>$nonTruant ) {
			$this->_nonTruants[$k]->displayname = ApotheosisLib::nameCase( 'pupil', $nonTruant->title, $nonTruant->firstname, $nonTruant->middlenames, $nonTruant->surname );
			unset( $this->_nonTruants[$k]->title, $this->_nonTruants[$k]->firstname, $this->_nonTruants[$k]->middlenames, $this->_nonTruants[$k]->surname );
		}
	}
	
	/**
	 * Fetch the list of non-truants, loading if necessary
	 * 
	 * @return array  list of non-truants
	 */
	function getNonTruants()
	{
		if( !isset( $this->_nonTruants ) ) {
			$this->_loadNonTruants();
		}
		
		return $this->_nonTruants;
	}
	
	/**
	 * Adds the pupils listed to the list of truants
	 * 
	 * @return array  array of results for the operation
	 */
	function addTruants( $new )
	{
		$retVal = array( 'deleted'=>0, 'added'=>0, 'errors'=>0 );
		
		if( is_array($new) && !empty($new) ) {
			$db = &JFactory::getDBO();
			$now = date( 'Y-m-d H:i:s' );
			
			// move the new truants from the non-truants to the truants list
			foreach( $new as $k=>$id ) {
				$this->_truants[$id] = $this->_nonTruants[$id];
				unset( $this->_nonTruants[$id] );
				$inserts[] = '( '.$db->Quote($id).', '.$db->Quote($now).' )';
			}
			
			// add new truants to the database and work out return values
			$query = 'INSERT IGNORE INTO '.$db->nameQuote('#__apoth_att_truants')
				."\n".'( '.$db->nameQuote('pupil_id').', '.$db->nameQuote('valid_from').' )'
				."\n".'VALUES'
				."\n".implode( ",\n ", $inserts );
			$db->setQuery( $query );
			
			if( $db->query() === false ) {
				$retVal['errors'] = count( $new );
			}
			else {
				$done = $db->getAffectedRows();
				$retVal['errors'] = ( count($new) - $done );
				$retVal['added'] = $done;
				
				// re-order the truants list
				ApotheosisLibArray::sortObjects( $this->_truants, 'displayname', 1, true );
			}
		}
		
		return $retVal;
	}
	
	/**
	 * Effectively removes the pupils listed from the list of truants
	 * by setting the valid_to date to now for any user listed for removal
	 * 
	 * @return array  array of results for the operation
	 */
	function removeTruants( $old )
	{
		$retVal = array( 'deleted'=>0, 'added'=>0, 'errors'=>0 );
		
		if( is_array($old) && !empty($old) ) {
			$db = &JFactory::getDBO();
			$now = date( 'Y-m-d H:i:s' );
			
			// move the old truants from the truants to the non-truants list
			foreach( $old as $k=>$id ) {
				$this->_nonTruants[$id] = $this->_truants[$id];
				unset( $this->_truants[$id] );
				$old[$k] = $db->Quote($id);
			}
			
			// remove old truants from the database and work out return values
			$query = 'UPDATE '.$db->nameQuote('#__apoth_att_truants')
				."\n".'SET '.$db->nameQuote('valid_to').' = '.$db->Quote($now)
				."\n".'WHERE '.$db->nameQuote('pupil_id').' IN ( '.implode(', ', $old).' )'
				."\n".'  AND '.$db->nameQuote('valid_to').' IS NULL';
			$db->setQuery( $query );
			
			if( $db->query() === false ) {
				$retVal['errors'] = count( $old );
			}
			else {
				$done = $db->getAffectedRows();
				$retVal['errors'] = ( count($old) - $done );
				$retVal['deleted'] = $done;
				
				// re-order the non-truants list
				ApotheosisLibArray::sortObjects( $this->_nonTruants, 'displayname', 1, true );
			}
		}
		
		return $retVal;
	}
}
?>