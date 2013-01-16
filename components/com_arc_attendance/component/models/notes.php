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

 /*
 * Extension Manager Absent Model
 *
 * @author		lightinthedark <code@lightinthedark.org.uk>
 * @package		Apotheosis
 * @subpackage	Attendance
 * @since		0.1
 */
class AttendanceModelNotes extends AttendanceModel
{
	var $_notes;
	
	function getNotes()
	{
		return $this->_notes;
	}
	
	function setGroupNotes( $gId )
	{
		$db = &JFactory::getDBO();
		$query = 'SELECT n.*, p.title, COALESCE( p.preferred_firstname, p.firstname ) AS firstname, p.middlenames, COALESCE( p.preferred_surname, p.surname ) AS surname'
			."\n".' FROM #__apoth_att_notes AS n'
			."\n".' INNER JOIN #__apoth_ppl_people AS p'
			."\n".'    ON p.id = n.pupil_id'
			."\n".' INNER JOIN #__apoth_tt_group_members AS gm'
			."\n".'    ON gm.person_id = n.pupil_id'
			."\n".'   AND gm.is_student = 1' // *** titikaka
			."\n".' ~LIMITINGJOIN~'
			."\n".' WHERE gm.group_id = '.$db->Quote($gId)
			."\n".' ORDER BY '.$db->nameQuote('delivered_on').', '.$db->nameQuote('last_modified').' DESC;';
		$db->setQuery( ApotheosisLibAcl::limitQuery($query, 'people.people') );
		$this->_notes = $db->loadObjectList( 'id' );
		if( !is_array($this->_notes) ) { $this->_notes = array(); }
		return $this->_notes;
	}
	
	function setPupilNotes( $pId )
	{
		$db = &JFactory::getDBO();
		$query = 'SELECT n.*, p.title, COALESCE( p.preferred_firstname, p.firstname ) AS firstname, p.middlenames, COALESCE( p.preferred_surname, p.surname ) AS surname'
			."\n".' FROM #__apoth_att_notes AS n'
			."\n".' INNER JOIN #__apoth_ppl_people AS p'
			."\n".'    ON p.id = n.pupil_id'
			."\n".' ~LIMITINGJOIN~'
			."\n".' WHERE '.$db->nameQuote('pupil_id').' = '.$db->Quote($pId)
			."\n".' ORDER BY '.$db->nameQuote('delivered_on').', '.$db->nameQuote('last_modified').' DESC;';
		$db->setQuery( ApotheosisLibAcl::limitQuery($query, 'people.people') );
		$this->_notes = $db->loadObjectList( 'id' );
		if( !is_array($this->_notes) ) { $this->_notes = array(); }
		return $this->_notes;
	}
	
	function saveNote( $noteObj )
	{
		$db = &JFactory::GetDBO();
		
		if( is_null($noteObj->id) ) {
			$query = 'INSERT INTO #__apoth_att_notes (`pupil_id`, `message`, `last_modified`, `delivered_on`)'
				."\n".' VALUES ('
				."\n".' '.$db->Quote($noteObj->pupil_id).','
				."\n".' '.$db->Quote($noteObj->message).','
				."\n".' '.$db->Quote($noteObj->last_modified).','
				."\n".' NULL );';
			$db->setQuery($query);
			$retVal = $db->query();
			$noteObj->id = $db->insertid();
		}
		else {
			$query = 'UPDATE #__apoth_att_notes'
				."\n".' SET '
				."\n".' `pupil_id` = '.$db->Quote($noteObj->pupil_id).','
				."\n".' `message`  = '.$db->Quote($noteObj->message).','
				."\n".' `last_modified` = '.$db->Quote($noteObj->last_modified).','
				."\n".' `delivered_on`  = '.( empty($noteObj->delivered_on) ? 'NULL' : $db->Quote($noteObj->delivered_on) )
				."\n".' WHERE `id` = '.$db->Quote($noteObj->id).';';
			$db->setQuery($query);
			$retVal = $db->query();
		}
		
		if( $retVal ) {
			$this->_notes[$noteObj->id] = $noteObj;
		}
		
		return $retVal;
	}
	
}
?>
