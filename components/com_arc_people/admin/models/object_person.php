<?php
/**
 * @package     Arc
 * @subpackage  People
 * @copyright   Copyright (C) 2005 Punnet. All rights reserved. See COPYRIGHT_ARC.txt
 * @license     http://www.gnu.org/licenses/gpl.html GNU/GPL. See LICENSE_ARC.txt
 * Arc is free software: you can redistribute it and/or modify
 * it under the terms of version 3 of the GNU General Public License
 * as published by the Free Software Foundation.
 * The disclaimer of warranty as stated in the GPL applies to this program
 */

defined( '_JEXEC' ) or die( 'Restricted access' );

/**
 * People object
 *
 * A single person is modeled by this class
 *
 * @author     Punnet - Arc Team
 * @package    Arc
 * @subpackage People
 * @since      1.6
 */
class AdminPerson extends JObject
{
	// The various properties of a people object
	var $_data;
	
	/**
	 * Construct an individual people object
	 */
	function __construct( $data = array() )
	{
		parent::__construct();
		
		$this->_data = $data;
		$this->_details = array();
	}
	
	/**
	 * Get the requested property
	 * 
	 * @param string $prop  The requested property
	 * @return mixed $retVal  The value of the property
	 */
	function getDatum( $prop )
	{
		$retVal = null;
		
		if( isset($this->_data[$prop]) ) {
			$retVal = $this->_data[$prop];
		}
		elseif( isset($this->_details[$prop]) ) {
			$retVal = $this->_details[$prop];
		}
		
		return $retVal;
	}
	
	/**
	 * Set the given property
	 * 
	 * @param string $prop  The given property
	 * @param mixed $value  The value to set
	 */
	function setDatum( $prop, $value )
	{
		if( array_key_exists($prop, $this->_data) ) {
			$this->_data[$prop] = $value;
		}
		if( array_key_exists($prop, $this->_details) ) {
			$this->_details[$prop] = $value;
		}
	}
}
?>