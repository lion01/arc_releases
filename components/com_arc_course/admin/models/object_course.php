<?php
/**
 * @package     Arc
 * @subpackage  Course
 * @copyright   Copyright (C) 2005 Punnet. All rights reserved. See COPYRIGHT_ARC.txt
 * @license     http://www.gnu.org/licenses/gpl.html GNU/GPL. See LICENSE_ARC.txt
 * Arc is free software: you can redistribute it and/or modify
 * it under the terms of version 3 of the GNU General Public License
 * as published by the Free Software Foundation.
 * The disclaimer of warranty as stated in the GPL applies to this program
 */

defined( '_JEXEC' ) or die( 'Restricted access' );

/**
 * Course object
 *
 * A single course is modeled by this class
 *
 * @author     Punnet - Arc Team
 * @package    Arc
 * @subpackage Course
 * @since      1.6
 */
class AdminCourse extends JObject
{
	// The various properties of a course object
	var $_data;
	
	/**
	 * Construct an individual course object
	 */
	function __construct( $data = array() )
	{
		parent::__construct();
		
		$this->_data = $data;
	}
	
	/**
	 * Get the requested property
	 * 
	 * @param string $prop  The requested property
	 * @return mixed  The value of the property
	 */
	function getData( $prop )
	{
		return $this->_data[$prop];
	}
	
	/**
	 * Set the given property
	 * 
	 * @param string $prop  The given property
	 * @param mixed $value  The value to set
	 */
	function setData( $prop, $value )
	{
		$this->_data[$prop] = $value;
	}
	
	/**
	 * Save this course object to the database
	 * 
	 * @return boolean $queryResult  Whether or not the object was successfully committed
	 */
	function commit()
	{
		// Get a database connector
		$db = &JFactory::getDBO();
		
		// Get the current user
		$u = ApotheosisLib::getUser();
		$uId = $u->person_id;
		
		// Copy object for saving 
		$data = $this->_data;
		
		if( $this->_data['id'] == '' ) {
			// Remove empty strings and prepare query strings
			foreach( $data as $k=>$v ) {
				if( $v != '' ) {
					$fields[] = $db->nameQuote( $k );
					$values[] = $db->Quote( $v );
				}
			}
			$fieldsStr = implode( ', ', $fields );
			$valuesStr = implode( ', ', $values );
			
			$query = 'INSERT INTO '.$db->nameQuote('#__apoth_cm_courses').' ('
				."\n".$fieldsStr
				.', '.$db->nameQuote('time_created')
				.', '.$db->nameQuote('created_by')
				."\n".')'
				."\n".'VALUES ('
				."\n".$valuesStr
				.', '.$db->Quote(date('Y-m-d H:i:s'))
				.', '.$db->Quote($uId)
				."\n".')';
		}
		else {
			// Remove entries we never want to update
			unset( $data['id'] );
			unset( $data['ext_course_id'] );
			unset( $data['ext_type'] );
			
			// Remove empty strings and prepare query strings
			foreach( $data as $k=>$v ) {
				if( $v != '' ) {
					$updates[] = $db->nameQuote($k).' = '.$db->Quote($v);
				}
			}
			$updatesStr = implode( "\n".', ', $updates );
			
			// Build the whole query
			$query = 'UPDATE '.$db->nameQuote('#__apoth_cm_courses')
				."\n".'SET'
				."\n".'  '.$updatesStr
				."\n".', '.$db->nameQuote('time_modified').'   = '.$db->Quote(date('Y-m-d H:i:s'))
				."\n".', '.$db->nameQuote('modified_by').'     = '.$db->Quote($uId)
				."\n".'WHERE '.$db->nameQuote('id').'          = '.$db->Quote($this->_data['id']);
		}
		$db->setQuery( $query );
		$queryResult = $db->Query();
		
		return $queryResult;
	}
	
	/**
	 * Mark the course as deleted
	 * 
	 * @return bool $deleted  Was the course successfully marked as deleted 
	 */
	function delete()
	{
		// Get a database connector
		$db = &JFactory::getDBO();
		
		// Get the current user
		$u = ApotheosisLib::getUser();
		$uId = $u->person_id;
		
		// Create delete query
		$query = 'UPDATE '.$db->nameQuote('#__apoth_cm_courses')
		."\n".'SET '.$db->nameQuote('deleted').' = '.$db->Quote('1')
		."\n".', '.$db->nameQuote('time_modified').' = '.$db->Quote(date('Y-m-d H:i:s'))
		."\n".', '.$db->nameQuote('modified_by').' = '.$db->Quote($uId)
		."\n".'WHERE '.$db->nameQuote('id').' = '.$this->_data['id'];
		$db->setQuery( $query );
		$deleted = $db->Query();
		
		return $deleted;
	}
}
?>