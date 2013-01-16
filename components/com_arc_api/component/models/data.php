<?php
/**
 * @package     Arc
 * @subpackage  API
 * @copyright   Copyright (C) 2005 Punnet. All rights reserved. See COPYRIGHT_ARC.txt
 * @license     http://www.gnu.org/licenses/gpl.html GNU/GPL. See LICENSE_ARC.txt
 * Arc is free software: you can redistribute it and/or modify
 * it under the terms of version 3 of the GNU General Public License
 * as published by the Free Software Foundation.
 * The disclaimer of warranty as stated in the GPL applies to this program
 */

defined( '_JEXEC' ) or die( 'Restricted access' );

jimport( 'joomla.application.component.model' );
require_once( JPATH_SITE.DS.'components'.DS.'com_arc_core'.DS.'libraries'.DS.'apoth_library.php' );
require_once( JPATH_SITE.DS.'components'.DS.'com_arc_api'.DS.'models'.DS.'objects.php' );
require_once( JPATH_SITE.DS.'components'.DS.'com_arc_api'.DS.'helpers'.DS.'lib_api.php' );

/**
 * API Data Model
 *
 * @author     Punnet - Arc Team <arc_developers@pun.net>
 * @package    Arc
 * @subpackage API
 * @since      1.6.1
 */
class ApiModelData extends JModel
{
	var $_data;
	
	/**
	 * Calls for the relevant api function to be run
	 * 
	 * @param string $ident  The identity of the function to be executed
	 * @return boolean  true if function ran, false otherwise
	 */
	function read( $ident, $params )
	{
		$this->_data = ArcApi::_( 'read.'.$ident, $params );
	}

	function write( $ident, $data )
	{
		$this->_data = ArcApi::_( 'write.'.$ident, array( $data ) );
	}
	
	function getData()
	{
		return $this->_data;
	}
}
?>