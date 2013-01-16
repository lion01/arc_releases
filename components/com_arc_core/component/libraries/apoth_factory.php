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
	// ### Vars and functions for management of child classes
	
	static $_factories = array();
	
	/**
	 * Static method to retrieve the singleton instance of the factory class named
	 * If we don't already have an instance and are given one then we set that as our singleton
	 * 
	 * @param string $ident  The component.factory identifier 
	 * @param object|null $target  Optionally the instance of the named factory, or null if not a defined factory
	 * @return object|false $f[$ident]  The factory if valid (passed in or new) or false otherwise
	 */
	function &_( $ident, &$factory = null )
	{
		$ident = strtolower( $ident );
		$f = &self::$_factories;
		
		// if we don't already have this factory stored then we need to either
		// fetch one from session storage, use the provided factory or initialise it
		if( !isset($f[$ident]) ) {
			// check the session first
			$session = &JSession::getInstance( 'none', array() );
			$incFiles = $session->get( 'incFiles' );
			$varName = ApothController::getVarName( $ident, 'factory' );
			
			// is the required factory stored in the session
			if( isset($incFiles['factory'][$varName]) ) {
				// require the incFiles
				if( is_array($incFiles['factory'][$varName]) ) {
					foreach( $incFiles['factory'][$varName] as $incFile ) {
						require_once( $incFile );
					}
				}
				elseif( !is_null($incFiles['factory'][$varName]) ) {
					require_once( $incFiles['factory'][$varName] );
				}
				
				// get and set the object from session
				$f[$ident] = &unserialize( $session->get($varName, 'N;') );
				
				// remove incFiles for this factory
				unset( $incFiles['factory'][$varName] );
				
				// if there are no more factory incFiles then unset factory incFiles array
				if( empty($incFiles['factory']) ) {
					unset( $incFiles['factory'] );
				}
				
				// update session incFiles and remove this factory from session
				$session->set( 'incFiles', $incFiles );
				$session->clear( $varName );
			}
			// required factory is not in the session so try elsewhere
			else {
				// fully identify required factory
				$facInfo = self::getFactoryInfo( $ident );
				
				if( $facInfo ) {
				// check we are seeking a valid factory
					if( is_a($factory, $facInfo['className']) ) {
						// a suitble factory has been supplied so use it
						$f[$ident] = &$factory;
					}
					elseif( class_exists($facInfo['className']) ) {
						// suitable factory not supplied so create a new one
						$f[$ident] = new $facInfo['className']();
						
						// is this a valid factory class
						if( is_a($f[$ident], 'ApothFactory') ) {
							// set our ident
							$f[$ident]->setIdent( $ident );
							
							// ensure we remember our own incFile
							$f[$ident]->setIncFiles( array($facInfo['fileName']) );
							
							// call init function if there is one
							if( method_exists($f[$ident], 'initialise') ) {
								$f[$ident]->initialise();
							}
						}
						else {
							// not a valid factory, mark as invalid
							$f[$ident] = false;
						}
					}
				}
				else {
					// mark as invalid
					$f[$ident] = false;
				}
			}
		}
		
		return $f[$ident];
	}
	
	/**
	 * Get all the info needed to instanciate a child factory
	 * In the process attempt to include the relevant class definition
	 * 
	 * @param string $ident The ident for the factory
	 * @return  array|false Array of def file and class name for the factory or false if no such factory
	 */
	function getFactoryInfo( $ident )
	{
		$parts = explode( '.', $ident );
		
		// check we have enough info in the ident to proceed
		if( count($parts) != 2 ) {
			$retVal = false;
		}
		else {
			$cName = $parts[0]; // component name
			$fName = $parts[1]; // factory name
			
			// check we have a valid definition file
			$retVal['fileName'] = JPATH_SITE.DS.'components'.DS.'com_arc_'.$cName.DS.'models'.DS.'object_'.$fName.'.php';
			if( file_exists($retVal['fileName']) ) {
				require_once( $retVal['fileName'] );
				$retVal['className'] = 'ApothFactory_'.ucfirst($cName).'_'.ucfirst($fName);
			}
			else {
				$retVal = false;
			}
		}
		
		return $retVal;
	}
	
	/**
	 * Save all the factories marked as persistent
	 */
	function savePersistentFactories()
	{
		// get required session info
		$session = &JSession::getInstance( 'none', array() );
		$sesIncFiles = $session->get( 'incFiles' );
		
		// loop through all factories and save any needing persistence
		foreach( self::$_factories as $factory ) {
			if( $factory->hasPersistent() ) {
				// *** ApothModel hack
				// *** remove this check when all models inherit ApothModel and just set the value into the array
				if( !$factory->_doNotPersist ) {
					// derive session var name
					$varName = ApothController::getVarName( $factory->getIdent(), 'factory' );
					
					// save the incFiles
					$sesIncFiles['factory'][$varName] = $factory->getIncFiles();
					$session->set( 'incFiles', $sesIncFiles );
					
					// serialise the factory
					$session->set( $varName, serialize($factory) );
				}
			}
		}
	}
	
	/**
	 * Clear persistence in all factories
	 */
	function clearPersistentFactories()
	{
		foreach( self::$_factories as $factory ) {
			$factory->clearAllPersistence();
		}
	}
	
	
	// ### Vars and functions to be inherited by child classes
	
	/**
	 * The ident for this factory (component.factory)
	 * 
	 * @var string
	 */
	var $_ident;
	
	/**
	 * Array of all the incFiles for this factory
	 * 
	 * @var array
	 */
	var $_incFiles;
	
	/**
	 * Array of all the object instances created by this factory so far
	 * Keyed by id
	 * 
	 * @var array
	 */
	var $_instances;
	
	/**
	 * 2-d array of all the searches performed by this factory and the ids they gave us
	 * Keyed by search hash, then ordered (indexed) array of result ids
	 * 
	 * @var array
	 */
	var $_searches;
	
	/**
	 * 2-d array of all the searches performed by this factory and the ids they gave us
	 * Ids are sorted by parent / indent level, and have extra info indicating parent and indent level
	 * Keyed by search hash, then ordered (indexed) array of result ids
	 * 
	 * @var array
	 */
	var $_structures;
	
	/**
	 * 2-d array of all the consumer classes registered with us
	 * Keyed by search hash then consumer ident
	 * 
	 * @var array
	 */
	var $_consumers;
	
	/**
	 * Array of all the requirements and orders arrays
	 * Keyed by search/registraton hash
	 * 
	 * @var array
	 */
	var $_searchParams;
	
	/**
	 * Array of all the vars we want to save
	 * Keyed by persistence constant then type
	 *
	 * @var array
	 */
	var $_persistent;
	
	/**
	 * Array of all the var names that can be persisted
	 *
	 * @var array
	 */
	var $_allowedPersistVars;
	
	/**
	 * Array of parameters with which to configure the factory
	 *
	 * @var array
	 */
	var $_config;
	
	/**
	 * Array of error messages built up over the course of using the factory
	 *
	 * @var array
	 */
	var $_errMsg;
	
	/**
	 * Creates an instance of the factory
	 * 
	 * @access private
	 */
	function __construct( $config = array() )
	{
		parent::__construct();
		$this->_ident = '';
		$this->_incFiles = array();
		$this->_instances = array();
		$this->_searches = array();
		$this->_structures = array();
		$this->_consumers = array();
		$this->_searchParams = array();
		$this->_persistent = array();
		$this->_allowedPersistVars = array( '_instances', '_searches', '_structures', '_searchParams' );
		$this->_config = $config;
		$this->_errMsg = array();
		$this->_doNotPersist = false; // *** ApothModel hack
	}
	
	/**
	 * On sleep we need to minify
	 */
	function __sleep()
	{
		// process vars for persisting
		$sleepVars = array();
		
		// always persist
		$sleepVars[] = '_ident';
		$sleepVars[] = '_incFiles';
		$sleepVars[] = '_consumers';
		$sleepVars[] = '_persistent';
		$sleepVars[] = '_allowedPersistVars';
		$sleepVars[] = '_config';
		$sleepVars[] = '_errMsg';
		$sleepVars[] = '_doNotPersist'; // *** ApothModel hack
		
		// empty out the consumers
		$this->_consumers = array();
		
		// reset error messages ready for next time we use the factory
		$this->_errMsg = array();
		
		// process optionally persisting vars
		foreach( $this->_allowedPersistVars as $optionalVar ) {
			// vars to not persist
			$remove =
				( isset($this->_persistent[ARC_PERSIST_REMOVE][$optionalVar]) )
				? $this->_persistent[ARC_PERSIST_REMOVE][$optionalVar]
				: array();
			
			// proceed only if we would end up with some items to save for this var
			if( $remove !== true ) {
				// once only persists
				$once =
					( isset($this->_persistent[ARC_PERSIST_ONCE][$optionalVar]) )
					? $this->_persistent[ARC_PERSIST_ONCE][$optionalVar]
					: array();
				
				// fill in all the possible vars if true is set
				if( $once === true ) {
					$once = array_keys( $this->$optionalVar );
				}
				
				// subtract the explicitly non-persisting items
				$once = array_diff( $once, $remove );
				
				// always persists
				$always =
					( isset($this->_persistent[ARC_PERSIST_ALWAYS][$optionalVar]) )
					? $this->_persistent[ARC_PERSIST_ALWAYS][$optionalVar]
					: array();
				
				// fill in all the possible vars if true is set
				if( $always === true ) {
					$always = array_keys( $this->$optionalVar );
				}
				
				// subtract the explicitly non-persisting items and set always persist members
				$always = $this->_persistent[ARC_PERSIST_ALWAYS][$optionalVar] = array_diff( $always, $remove );
				
				// determine all items to save and unset any not on the list
				$curKeys = array_keys( $this->$optionalVar );
				$saveKeys = array_unique( array_merge($once, $always) );
				$keysToUnset = array_diff( $curKeys, $saveKeys );
				foreach( $keysToUnset as $unsetKey ) {
					unset( $this->{$optionalVar}[$unsetKey] );
				}
				
				// forward this optional var to be saved (may be in default constructor state as empty array)
				$sleepVars[] = $optionalVar;
			}
			else {
				$this->_persistent[ARC_PERSIST_ALWAYS][$optionalVar] = array();
			}
			
			// tidy up always array for this var
			if( empty($this->_persistent[ARC_PERSIST_ALWAYS][$optionalVar]) ) {
				unset( $this->_persistent[ARC_PERSIST_ALWAYS][$optionalVar] );
			}
		}
		
		// clean up the persistent array
		unset( $this->_persistent[ARC_PERSIST_ONCE] );
		unset( $this->_persistent[ARC_PERSIST_REMOVE] );
		if( empty($this->_persistent[ARC_PERSIST_ALWAYS]) ) {
			unset( $this->_persistent[ARC_PERSIST_ALWAYS] );
		}
		
		return $sleepVars;
	}
	
	/**
	 * Get the ident for this factory
	 * 
	 * @return string  The ident for this factory
	 */
	function getIdent()
	{
		return $this->_ident;
	}
	
	/**
	 * Set the ident for this factory
	 * 
	 * @param string $ident  The ident for this factory
	 */
	function setIdent( $ident )
	{
		$this->_ident = $ident;
	}
	
	/**
	 * Get the incFiles for this factory
	 * 
	 * @return array  The incFiles for this factory
	 */
	function getIncFiles()
	{
		return $this->_incFiles;
	}
	
	/**
	 * Set the incFiles for this factory
	 * 
	 * @param array $incFiles  The incFiles for this factory
	 */
	function setIncFiles( $incFiles )
	{
		$this->_incFiles = $incFiles;
	}
	
	/**
	 * Retrieve all the class variables we wish to sleep with
	 *
	 * @return array  Array of all the class variables we wish to sleep with
	 */
	function getPersistent()
	{
		return $this->_persistent;
	}
	
	/**
	 * Set the specified type of var as persistent, optionally setting which specific items to persist
	 * 
	 * @param string $type  What type of var to consider
	 * @param string|array $varIds  The IDs of the items to persist, if omitted then persist all items
	 * @param int $persistence  Which level of persistence to set, if omitted then persist once
	 */
	function setPersistent( $type, $varIds = true, $persistence = ARC_PERSIST_ONCE )
	{
		// check we trying to persist a var in the allowed list
		$type = '_'.$type;
		if( in_array( $type, $this->_allowedPersistVars) ) {
			// make sure we just deal with arrays for specific var IDs
			if( !is_array($varIds) && !is_bool($varIds) ) {
				$varIds = array( $varIds );
			}
			
			// if this $type of var for a given persistence has not been set
			// or will be set to true (save all)
			// then just assign the incoming $varIds
			if( !isset($this->_persistent[$persistence][$type]) || ($varIds === true) ) {
				$this->_persistent[$persistence][$type] = $varIds;
			}
			// if this $type of var for a given persistence is an array (not 'true')
			// then merge it with what must also be an incoming array
			elseif( is_array($this->_persistent[$persistence][$type]) ) {
				$this->_persistent[$persistence][$type] = array_unique( array_merge($this->_persistent[$persistence][$type], $varIds) );
			}
		}
	}
	
	/**
	 * Do we have any class variables to sleep with?
	 *
	 * @return boolean  Does the persistent array contain any class variables to sleep with?
	 */
	function hasPersistent()
	{
		return ( !empty($this->_persistent[ARC_PERSIST_ONCE]) || !empty($this->_persistent[ARC_PERSIST_ALWAYS]) );
	}
	
	/**
	 * Clear all persistence for this factory
	 */
	function clearAllPersistence()
	{
		$this->_persistent = array();
	}
	
	/**
	 * Retrieve the specified parameter
	 * 
	 * @return mixed  The specified parameter
	 */
	function getParam( $param ) {
		return ( isset($this->_config[$param]) ? $this->_config[$param] : null );
	}
	
	/**
	 * Set a parameter using the given param name and value
	 * 
	 * @param string $param  The name of the parameter
	 * @param mixed $value  The value to set for the named parameter
	 */
	function setParam( $param, $value ) {
		$this->_config[$param] = $value;
	}
	
	/**
	 * Retrieve any error messages that have been set
	 * 
	 * @return string  The most recent error message
	 */
	function getErrMsg()
	{
		return $this->_errMsg;
	}
	
	/**
	 * Set an error message
	 * 
	 * @param string $errMsg  The error message to set
	 */
	function setErrMsg( $errMsg )
	{
		$this->_errMsg[] = $errMsg;
	}
	
	/**
	 * Clear the factory's cache of the specified instance
	 * 
	 * @param int $id  The ID of the instance to clear
	 * @see _clearCachedInstances
	 */
	function freeInstance( $id )
	{
		$this->_clearCachedInstances( $id );
	}
	
	/**
	 * Clear the factory's cache of all instances and searches
	 * 
	 * @see _clearCachedInstances
	 * @see _clearCachedSearches
	 */
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
			unset( $this->_instances[$id] );
		}
	}
	
	/**
	 * Clears the factory's cache of searches
	 * If both parameters are omitted then all searches are removed
	 * 
	 * @param string $sId  Optionally specify which identified search should be cleared
	 * @param int $iId  Optionally specify which identified instance whose inclusion in a result indicates that search should be cleared
	 */
	function _clearCachedSearches( $sId = null, $iId = null )
	{
		if( is_null($sId) && is_null($iId) ) {
			// clear all the stored searches
			$this->_searches = array();
			
			// loop through all consumers and notify them that their data needs refreshing
			foreach( $this->_searchParams as $regHash=>$searchParams ) {
				if( isset($this->_consumers[$regHash]) ) {
					$this->_notifyConsumers( $regHash );
				}
			}
		}
		else {
			if( !is_null($sId) ) {
				// clear the specified search
				unset( $this->_searches[$sId] );
				
				// notify the relevant consumers
				if( isset($this->_consumers[$sId]) ) {
					$this->_notifyConsumers( $sId );
				}
			}
			if( !is_null($iId) ) {
				foreach( $this->_searches as $k=>$s ) {
					if( is_array($s) && (array_search($iId, $s) !== false) ) {
						// clear the specific search
						unset( $this->_searches[$k] );
						
						// notify relevant consumer that its data needs refreshing
						if( isset($this->_consumers[$k]) ) {
							$this->_notifyConsumers( $k );
						}
					}
				}
			}
		}
	}
	
	/**
	 * Notify all the consumers of a given registration hash that their data is now stale
	 * 
	 * @param string $regHash  The consumer registration hash
	 */
	function _notifyConsumers( $regHash )
	{
		foreach( $this->_consumers[$regHash] as $ident=>$consumer ) {
			$this->_consumers[$regHash][$ident]->clearRegCache( $regHash );
		}
	}
	
	/**
	 * Retrieves the instance with the given id or null if instance does not exist
	 * 
	 * @param integer|string $id  The id of the instanciated object that we want to retrieve
	 * @return object|null $r  The identified objet if it's in our list, or null if it isn't
	 */
	function &_getInstance( $id )
	{
		if( isset($this->_instances[$id]) ) {
			$r = &$this->_instances[$id];
		}
		else {
			$r = null;
		}
		
		return $r;
	}
	
	/**
	 * Adds an instance to our list of instances
	 * 
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
		if( empty($this->_instances) ) {
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
		
		$orig = &$this->getInstance( $from );
		$new = clone( $orig );
		$new->setId( $to );
		$this->_clearCache( $to );
		$this->_addInstance( $to, $new );
		
		return $new->getId();
	}
	
	/**
	 * Generates a unique key for a given set of search requirements
	 * Wrapper for _getSearchId for use by consumer classes
	 * 
	 * @see _getSearchId
	 */
	function getRegHash( $requirements, $orders = null )
	{
		return $this->_getSearchId( $requirements, $orders );
	}
	
	/**
	 * Generates a unique key for a given set of search requirements
	 * Additionally stores the search parameters for re-use by consumer classes
	 * 
	 * @param array $requirements  The requirements array
	 * @param array $orders  Optional array of results ordering details
	 * @return string $sig  md5 hash as unique key for a given set of search requirements
	 */
	function _getSearchId( $requirements, $orders = null )
	{
		// Generate hash string
		$tmp = serialize( $requirements ).serialize($orders);
		$sig = md5( $tmp ).'-'.strlen($tmp);
		
		// Store search parameters
		$this->_searchParams[$sig]['reqs'] = $requirements;
		$this->_searchParams[$sig]['orders'] = $orders;
		
		return $sig;
	}
	
	/**
	 * Gives back the array of ids which came back for this search last time
	 * 
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
	 * 
	 * @param string $id  The search id to add / update
	 * @param array $results  The ids that resulted from this search. 
	 */
	function _addInstances( $id, $results )
	{
		$this->_searches[$id] = $results;
	}
	
	/**
	 * Register a consumer class by storing a reference to it keyed on the search ID
	 * 
	 * @param string $regHash  md5 hash as generated by _getsearchId
	 * @param string $conIdent  Unique identifier for the consumer
	 * @param object $consumer  The consumer class object
	 */
	function regConsumer( $regHash, $conIdent, &$consumer )
	{
		$this->_consumers[$regHash][$conIdent] = &$consumer;
	}
	
	/**
	 * Unregister a specific consumer class
	 * 
	 * @param string $regHash  md5 hash as generated by _getsearchId
	 * @param string $conIdent  Unique identifier for the consumer
	 */
	function unRegConsumer( $regHash, $conIdent )
	{
		// unset the consumer
		unset( $this->_consumers[$regHash][$conIdent] );
		
		// check if this regHash is now empty and if so delete that too
		if( empty($this->_consumers[$regHash]) ) {
			unset( $this->_consumers[$regHash] );
		}
	}
	
	/**
	 * Retrieve the search params associated with the given registration hash
	 * Used by consumer classes to refresh their data
	 * 
	 * @param string $regHash  The registration/search hash
	 * @return array  2 element array of search requirements and ordering
	 */
	function getSearchParams( $regHash )
	{
		return $this->_searchParams[$regHash];
	}
	
	/**
	 * Takes an array of arrays each representing the raw data for some object
	 * Constructs a new array of those elements ordered by tree hierarchy and with depth indicator
	 * 
	 * @param string $id  Typically a search ID
	 * @param array $results  The results of the search
	 * @param string $elemId  Array key within the results for the ID of the element
	 * @param string $elemParent  Array key within the results for the parent ID of the element
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
			$cur = array_shift( $leaves );
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
	
	/**
	 * Retrieve the structure that resulted from the specified requirements, if any
	 * 
	 * @param array $requirements  The requirements that were used for a search
	 * @return array|null  The structured array for the results of the search using the given requirements
	 */
	function getStructure( $requirements )
	{
		// determine the search ID from the requirements
		$sId = $this->_getSearchId( $requirements );
		
		return $this->_getStructure( $sId );
	}
	
	/**
	 * Helper function to determine the structure associated with a given search ID
	 * 
	 * @param string $sId  The search ID for a given set of requirements
	 * @return array|null $retVal  The structured array for the results of the search using the given requirements
	 */
	function _getStructure( $sId )
	{
		if( isset($this->_structures[$sId]) ) {
			$retVal = $this->_structures[$sId];
		}
		else {
			$retVal = null;
		}
		
		return $retVal;
	}
}
?>