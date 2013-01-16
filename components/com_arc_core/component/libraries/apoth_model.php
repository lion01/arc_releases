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

// Arc model constants
define( 'ARC_PERSIST_REMOVE', 0 );
define( 'ARC_PERSIST_ONCE'  , 1 );
define( 'ARC_PERSIST_ALWAYS', 2 );

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
	/**
	 * Initial state of persistent variables
	 *
	 * @var array
	 */
	var $_persistentBase = array( ARC_PERSIST_ONCE=>array(), ARC_PERSIST_ALWAYS=>array('_persistent', '_specials') );
	
	/**
	 * Current state of persistent variables
	 *
	 * @var array
	 */
	var $_persistent = array( ARC_PERSIST_ONCE=>array(), ARC_PERSIST_ALWAYS=>array('_persistent', '_specials') );
	
	/**
	 * Array of all the incFiles for this factory
	 *
	 * @var array
	 */
	var $_specials = array( ARC_PERSIST_ONCE=>array(), ARC_PERSIST_ALWAYS=>array() );
	
	/**
	 * Extend me in component models if you need to minify any data before sleeping.
	 * Following determination of return vars we clear the ARC_PERSIST_ONCE vars.
	 * 
	 * @return array $retVal  Array of all the class variables we wish to sleep with
	 */
	function __sleep()
	{
		// get all currently persistent vars
		$retVal = $this->getPersistent();
		
		// stop persisting with ARC_PERSIST_ONCE vars
		$this->_persistent[ARC_PERSIST_ONCE] = array();
		
		return $retVal; 
	}
	
	/**
	 * Here we process any saved vars that need special treatment after waking
	 */
	function __wakeup()
	{
		// combine the list of special vars and loop through them
		$specials = array_merge( $this->_specials[ARC_PERSIST_ONCE], $this->_specials[ARC_PERSIST_ALWAYS] );
		foreach( $specials as $classVar=>$type ) {
			$this->_specialVarsHelper( $type, $classVar );
		}
		
		// empty any specials that were persisted only once
		$this->_specials[ARC_PERSIST_ONCE] = array();
	}
	
	/**
	 * Process special vars as they are awoken
	 * 
	 * @param string $type  What type of special var is this
	 * @param string $classVar  The class var to process
	 */
	function _specialVarsHelper( $type, $classVar )
	{
		switch( $type ) {
		case( 'paginator' ):
			$this->$classVar = &ApothPagination::_( $this->$classVar->getIdent(), $this->$classVar );
			$this->$classVar->regWithFactory();
			break;
		}
	}
	
	/**
	 * Retrieve all the class variables we wish to sleep with
	 * 
	 * @param int $persistence  Which level of persistence to return:
	 *                          ARC_PERSIST_ONCE or ARC_PERSIST_ALWAYS.
	 *                          Defaults to both ARC_PERSIST_ONCE and ARC_PERSIST_ALWAYS.
	 * @return array $retVal    Array of all the class variables we wish to sleep with
	 */
	function getPersistent( $persistence = null )
	{
		if( is_null($persistence) ) {
			$retVal = array_unique( array_merge($this->_persistent[ARC_PERSIST_ONCE], $this->_persistent[ARC_PERSIST_ALWAYS]) );
		}
		else {
			$retVal = $this->_persistent[$persistence];
		}
		
		return $retVal;
	}
	
	/**
	 * Set the supplied class variable to sleep with later
	 * 
	 * @param string $classVar  The class variable to sleep with
	 * @param string $special   The type of var to be paid attention to in __wakeup()
	 * @param int $persistence  Level of persistence to set:
	 *                          ARC_PERSIST_REMOVE, ARC_PERSIST_ONCE or ARC_PERSIST_ALWAYS.
	 *                          Defaults to ARC_PERSIST_ONCE, persist for 1 page load.
	 */
	function setPersistent( $classVar, $special = false, $persistence = ARC_PERSIST_ONCE )
	{
		$arrayKeyOnce = array_search( $classVar, $this->_persistent[ARC_PERSIST_ONCE] );
		$arrayKeyAlways = array_search( $classVar, $this->_persistent[ARC_PERSIST_ALWAYS] );
		
		switch( $persistence ) {
		case( ARC_PERSIST_REMOVE ):
			if( $arrayKeyOnce !== false ) {
				unset( $this->_persistent[ARC_PERSIST_ONCE][$arrayKeyOnce] );
				unset( $this->_specials[ARC_PERSIST_ONCE][$classVar] );
			}
			if( $arrayKeyAlways !== false ) {
				unset( $this->_persistent[ARC_PERSIST_ALWAYS][$arrayKeyAlways] );
				unset( $this->_specials[ARC_PERSIST_ALWAYS][$classVar] );
			}
			break;
		
		case( ARC_PERSIST_ONCE ):
			if( $arrayKeyOnce === false ) {
				$this->_persistent[ARC_PERSIST_ONCE][] = $classVar;
				if( $special ) {
					$this->_specials[ARC_PERSIST_ONCE][$classVar] = $special;
				}
			}
			break;
		
		case( ARC_PERSIST_ALWAYS ):
			if( $arrayKeyAlways === false ) {
				$this->_persistent[ARC_PERSIST_ALWAYS][] = $classVar;
				if( $special ) {
					$this->_specials[ARC_PERSIST_ALWAYS][$classVar] = $special;
				}
			}
			break;
		}
	}
	
	/**
	 * Do we have any class variables to sleep with?
	 * 
	 * @return boolean  Does the persistent array contain any class variables to sleep with?
	 */
	function hasPersistent()
	{
		return $this->_persistent != $this->_persistentBase;
	}
	
	/**
	 * Do not sleep any variables for the next page load 
	 */
	function clearPersistence()
	{
		$this->_persistent = $this->_persistentBase;
	}
}
?>