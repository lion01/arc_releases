<?php
/**
 * @package     Arc
 * @subpackage  Core
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
 * Arc Model
 *
 * @author     lightinthedark <code@lightinthedark.org.uk>
 * @package    Arc
 * @subpackage Core
 * @since      1.2
 */
class ApothModel extends JModel
{
	var $_persistent = array();
	
	/**
	 * Retrieve all the class variables we wish to sleep with
	 */
	function getPersistent()
	{
		return $this->_persistent;
	}
	
	/**
	 * Set the supplied class variable to sleep with later
	 * 
	 * @param string $classVar  The class variable to sleep with
	 * @param boolean $remove  Should we remove this class variable
	 */
	function setPersistent( $classVar, $remove = false )
	{
		if( !in_array($classVar, $this->_persistent) && !$remove ) {
			$this->_persistent[] = $classVar;
		}
		elseif( $remove ) {
			$arrayKey = array_search( $classVar, $this->_persistent );
			if( $arrayKey !== false ) {
				unset( $this->_persistent[$arrayKey] );
			}
		}
	}
}
?>