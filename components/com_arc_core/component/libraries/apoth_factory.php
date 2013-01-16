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

/**
 * Apotheosis Factory
 */
class ApothFactory extends JObject
{
	
	// #####  Vars and functions for management of child classes
	
	static $_factories = array();
	static $_incFiles = array();
	
	/**
	 * Static method to retrieve the singleton instance of the factory class named
	 * If we don't already have an instance and are given one then we set that as our singleton
	 * 
	 * @param string $ident  The component.factory identifier 
	 * @param object|null $target  Optionally the instance of the named factory, or null if not a defined factory
	 */
	function &_( $ident, &$factory = null )
	{
		$f = &self::$_factories;
		
		$ident = strtolower( $ident );
		$parts = explode( '.', $ident );
		if( count( $parts ) != 2 ) {
			$rv = false;
			return $rv;
		}
		$cName = $parts[0]; // component name
		$fName = $parts[1]; // factory name
		
		if( !isset( $f[$ident] ) ) {
			// include the definition file
			$fileName = JPATH_SITE.DS.'components'.DS.'com_arc_'.$cName.DS.'models'.DS.'object_'.$fName.'.php';
//			var_dump_pre( $ident, 'getting instance for: ' );
//			var_dump_pre( $fileName, 'loading definition file: ' );
//			var_dump_pre( file_exists( $fileName ), 'which exists? ' );
			if( file_exists($fileName) ) {
				self::$_incFiles[] = $fileName;
				require_once($fileName);
			}
			
			$cNameFull = 'ApothFactory_'.ucfirst($cName).'_'.ucfirst($fName);
			
			// set a value for this ident
			if( is_a( $factory, $cNameFull ) ) {
				// use the provided factory instead of creating a new one if possible
//				echo 'using provided<br />';
				$f[$ident] = &$factory;
			}
			elseif( class_exists( $cNameFull ) ) { 
				// use a newly created object
//				echo 'creating fresh<br />';
				$f[$ident] = new $cNameFull();
				if( !is_a( $f[$ident], 'ApothFactory' ) ) {
					$f[$ident] = false;
				}
				elseif( method_exists( $f[$ident], 'initialise' ) ) {
					$f[$ident]->initialise();
				}
			}
			else {
//				echo 'un-creatable<br />';
				// mark as invalid
				$f[$ident] = false;
			}
		}
		
		return $f[$ident];
	}
	
	function getIncFile( $ident )
	{
		$ident = strtolower( $ident );
		$parts = explode( '.', $ident );
		if( count( $parts ) != 2 ) {
			$rv = false;
			return $rv;
		}
		$cName = $parts[0]; // component name
		$fName = $parts[1]; // factory name
		$cName = strtolower($cName);
		
		// include the definition file
		$fileName = JPATH_SITE.DS.'components'.DS.'com_arc_'.$cName.DS.'models'.DS.'object_'.$fName.'.php';
		return $fileName;
	}
	
	function getIncFiles()
	{
		return self::$_incFiles;
	}
	
	
	// #####  Vars and functions to be inherited by child classes
	
	/**
	 * Array of all the object instances created by this factory so far
	 * Keyed by id
	 * @var array
	 */
	var $_instances;
	
	/**
	 * 2-d array of all the searches performed by this factory and the ids they gave us
	 * Keyed by search hash, then ordered (indexed) array of result ids
	 * @var array
	 */
	var $_searches;
	
	/**
	 * 2-d array of all the searches performed by this factory and the ids they gave us
	 * Ids are sorted by parent / indent level, and have extra info indicating parent and indent level
	 * Keyed by search hash, then ordered (indexed) array of result ids
	 * @var array
	 */
	var $_structures;
	
	/**
	 * Creates an instance of the factory
	 * @access private
	 */
	function __construct( $config = array() )
	{
		parent::__construct();
		$this->_instances = array();
		$this->_searches = array();
		$this->_config = $config;
		$this->_errMsg = array();
	}
	
	function getParam( $param ) {
		return ( isset($this->_config[$param]) ? $this->_config[$param] : null );
	}
	
	function setParam( $param, $value ) {
		$this->_config[$param] = $value;
	}
	
	function getErrMsg()
	{
		return $this->_errMsg;
	}
	
	function setErrMsg( $errMsg )
	{
		$this->_errMsg[] = $errMsg;
	}
	
	function freeInstance( $id )
	{
		$this->_clearCachedInstances( $id );
	}
	
	function clearCache()
	{
		$this->_clearCachedInstances();
		$this->_clearCachedSearches();
	}
	
	/**
	 * Clears the factory's cache of instances and searches
	 * 
	 * @param int $id  Optionally specify which identified instance should be cleared
	 * @see _clearCachedInstances 
	 * @see _clearCachedSearches 
	 */
	function _clearCache( $id = null )
	{
		$this->_clearCachedInstances( $id );
		$this->_clearCachedSearches( null, $id );
	}
	
	/**
	 * Clears the factory's cache of instances 
	 * 
	 * @param int $id  Optionally specify which identified instance should be cleared
	 *                 If omitted all instances are removed
	 */
	function _clearCachedInstances( $id = null )
	{
		if( is_null($id) ) {
			$this->_instances = array();
		}
		else {
			unset($this->_instances[$id]);
		}
	}
	
	/**
	 * Clears the factory's cache of searches
	 * If both parameters are omitted then all searches are removed
	 *  
	 * @param string $sId  Optionally specify which identified search should be cleared
	 * @param int $id  Optionally specify which identified instance whose inclusion in a result indicates that search should be cleared
	 */
	function _clearCachedSearches( $sId = null, $iId = null )
	{
		if( is_null($sId) && is_null($iId) ) {
			$this->_searches = array();
		}
		else {
			if( !is_null($sId) ) {
				unset($this->_searches[$sId]);
			}
			if( !is_null($iId) ) {
				foreach($this->_searches as $k=>$s) {
					if( is_array($s) && (array_search($iId, $s) !== false) ) {
						unset($this->_searches[$k]);
					}
				}
			}
		}
}
			
	/**
	 * Retrieves the instance with the given id or null if instance does not exist
	 * @param integer|string $id  The id of the instanciated object that we want to retrieve
	 * @return object|null  The identified objet if it's in our list, or null if it isn't
	 */
	function &_getInstance( $id )
	{
		if( isset($this->_instances[$id]) ) {
			$r = $this->_instances[$id];
		}
		else {
			$r = null;
		}
		return $r;
	}
	
	/**
	 * Adds an instance to our list of instances
	 * @param mixed $id  The id of the object
	 * @param object $r  A reference to the object to store
	 */
	function _addInstance( $id, &$r )
	{
		$this->_instances[$id] = &$r;
	}
	
	/**
	 * Finds the next available dummy id (ie negative id)
	 */
	function _getDummyId()
	{
		if( empty( $this->_instances ) ) {
			$id = -1;
		}
		else {
			$min = min( array_keys($this->_instances) );
			$id = ( $min < 0 ) ? $min-1 : -1;
		}
		return $id;
	}
	
	/**
	 * Creates a copy of the identified object giving it an appropriate negative id
	 * 
	 * @param int $from  The id of the object to copy
	 * @param int $to  The target id of the new object. If not supplied one will be generated
	 * @return int  The id of the newly created object
	 */
	function &copy( $from, $to = null )
	{
		if( is_null($to) ) {
			$to = $this->_getDummyId();
		}
		
		$orig = $this->getInstance( $from );
		$new = clone( $orig );
		$new->setId( $to );
		$this->_clearCache( $to );
		$this->_addInstance( $to, $new );
		
		return $new->getId();
	}
	
	
	/**
	 * Generates a unique key for a given set of search requirements
	 * @param array $requirements  The requirements array
	 */
	function _getSearchId( $requirements )
	{
		$tmp = serialize($requirements);
		return md5( $tmp ).'-'.strlen($tmp);
	}
	
	/**
	 * Gives back the array of ids which came back for this search last time
	 * @param string $id  The unique id for this search
	 */
	function _getInstances( $id = null )
	{
		if( is_null($id) ) {
			$r = array_keys( $this->_instances );
		}
		else {
			if( isset($this->_searches[$id]) ) {
				$r = $this->_searches[$id];
			}
			else {
				$r = null;
			}
		}
		return $r;
	}
	
	/**
	 * Adds / updates a record of the search we performed and the resulting ids
	 * @param string $id  The search id to add / update
	 * @param array $results  The ids that resulted from this search. 
	 */
	function _addInstances( $id, $results )
	{
		$this->_searches[$id] = $results;
	}
	
	/**
	 * Takes an array of arrays each representing the raw data for some object
	 * Constructs a new array of those elements ordered by tree hierarchy and with depth indicator
	 * 
	 * @param unknown_type $id
	 * @param unknown_type $results
	 * @param unknown_type $elemId
	 * @param unknown_type $elemParent
	 */
	function _addStructure( $id, $results, $elemId, $elemParent )
	{
		// work out parentless elements
		foreach( $results as $k=>$r ) {
			if( !isset($results[$r[$elemParent]]) || ($r[$elemId] == $r[$elemParent]) ) {
				$leaves[] = array( $elemId=>$r[$elemId], $elemParent=>$r[$elemParent], 'level'=>0 );
			}
		}
		foreach( $leaves as $leaf ) {
			unset( $results[$leaf[$elemParent]] );
		}
		
		$struct = array();
		while( !empty($leaves) ) {
			$cur = array_shift($leaves);
			$children = array();
			$l = $cur['level'] + 1;
			$struct[] = $cur;
			foreach( $results as $k=>$r ) {
				if( $r[$elemParent] == $cur[$elemId] ) {
					$children[] = array( $elemId=>$r[$elemId], $elemParent=>$r[$elemParent], 'level'=>$l );
					unset( $results[$k] );
				}
			}
			$leaves = array_merge( $children, $leaves );
		}
		$this->_structures[$id] = $struct;
	}
	
	function getStructure( $requirements )
	{
		$sId = $this->_getSearchId( $requirements );
		return $this->_getStructure( $sId );
	}
	
	function _getStructure( $id )
	{
		if( isset($this->_structures[$id]) ) {
			$r = $this->_structures[$id];
		}
		else {
			$r = null;
		}
		return $r;
	}
	
	
/* *** Thought this would allow us to only serialize one instance of the factories
 * *** but it seems to just break things. Leaving it here in case we come back to this idea
	function __sleep()
	{
		if( $this->slept === true ) {
			echo 'slept already<br />';
			return null;
		}
		else {
			echo 'first sleep<br />';
			$this->slept = true;
			return array_keys(get_object_vars($this));
		}
	}
	
	function __wakeup()
	{
		echo 'waking up<br />';
		$this->slept = false;
		var_dump_pre($this);
	}
// */
}
?>