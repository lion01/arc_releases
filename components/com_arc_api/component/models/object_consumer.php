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

/**
 * API consumer factory
 */
class ApothFactory_Api_Consumer extends ApothFactory
{
	var $_date;
	/**
	 * To comply with automated saving of factories
	 * we must explicitly sleep any class vars in child factories
	 */
	function __sleep()
	{
		$parentVars = parent::__sleep();
		$myVars = array( '_date' );
		$allVars = array_merge( $parentVars, $myVars );
	
		return $allVars;
	}
	
	function initialise()
	{
		$this->setDate();
	}
	
	function setDate( $date = null )
	{
		$this->_date = ( is_null($date) ? date( 'Y-m-d H:i:s' ) : $date );
	}
	
	/**
	 * Creates a blank instance with the given id
	 * @param int $id  The id that should be used for the dummy object. Must be negative.
	 */
	function &getDummy( $id = -1 )
	{
		if( $id >= 0 ) {
			$r = null;
			return $r;
		}
		$r = &$this->_getInstance( $id );
		if( is_null($r) ) {
			$r = new ApothApiConsumer( array('id'=>$id, 'enabled'=>1, 'name'=>'', 'description'=>'', 'cons_key'=>'', 'cons_secret'=>'') );
			$this->_addInstance( $id, $r );
		}
		return $r;
	}
	
	/**
	 * Retrieves the identified message, creating the object if it didn't already exist
	 * @param $id
	 */
	function &getInstance( $id )
	{
		$r = &$this->_getInstance( $id );
		if( is_null($r) ) {
			$db = &JFactory::getDBO();
			$cId = $db->Quote( $id );
			$query = 'SELECT *'
				."\n".'FROM '.$db->nameQuote( '#__apoth_api_consumers' ).' AS c'
				."\n".'WHERE c.id = '.$cId
				."\n".'LIMIT 1';
			$db->setQuery($query);
			$data = $db->loadAssoc();
//			debugQuery( $db, $data );
			
			$r = new ApothApiConsumer( $data );
			$this->_addInstance( $id, $r );
		}
		return $r;
	}
	
	function &getInstances( $requirements )
	{
		$sId = $this->_getSearchId( $requirements );
		$ids = $this->_getInstances($sId);
		if( is_null($ids) ) {
			$db = &JFactory::getDBO();
			
			$where = array();
			foreach( $requirements as $col=>$val ) {
				if( is_array($val) ) {
					if( empty($val) ) {
						continue;
					}
					foreach( $val as $k=>$v ) {
						$val[$k] = $db->Quote( $v );
					}
					$assignPart = ' IN ('.implode( ', ',$val ).')';
				}
				else {
					$assignPart = ' = '.$db->Quote( $val );
				}
				switch( $col ) {
				case( 'id' ):
					$where[] = 'c.id '.$assignPart;
					break;
				
				case( 'key' ):
					$where[] = 'c.cons_key '.$assignPart;
					break;
				
				case( 'enabled' ):
					$where[] = 'c.enabled '.$assignPart;
					break;
				
				case( 'text' ):
					if( empty($val) ) {
						break;
					}
					if( !is_array($val) ) {
						$val = array($val);
					}
					
					$wordMatches = array();
					$dbName = $db->nameQuote( 'name' );
					$dbDesc = $db->nameQuote( 'description' );
					foreach( $val as $word ) {
						$dbWord = $db->Quote( '%'.$word.'%' );
						$wordMatches[] = '( '.$dbName.' LIKE '.$dbWord.' OR '.$dbDesc.' LIKE '.$dbWord.')';
					}
					$where[] = '('.implode( "\n, ", $wordMatches ).')';
					break;
				}
			}
			
			$query = 'SELECT c.id'
				."\n".'FROM '.$db->nameQuote( '#__apoth_api_consumers' ).' AS c'
				.( empty($where)  ? '' : "\nWHERE " .implode("\n AND ", $where) )
				."\n".'ORDER BY `enabled` DESC, `name` ASC';
			$db->setQuery($query);
			$ids = $db->loadResultArray();
//			debugQuery( $db, $ids );
			$this->_addInstances( $sId, $ids );
		}
		
		return $ids;
	}
	
	/**
	 * Commits the instance to the db,
	 * updates the cached instance,
	 * clears the search cache if we've added a new instance
	 *  (the newly created instance may match any of the searches we preveiously executed)
	 * 
	 * @param $id
	 */
	function commitInstance( $id )
	{
		$r = &$this->_getInstance( $id );
		if( is_null($r) ) {
			return false;
		}
		$db = &JFactory::getDBO();
		$id = $r->getId();
		$isNew = ( $id < 0 );
		
		// Set up core data
		if( $isNew ) {
			$query = 'INSERT INTO '.$db->nameQuote( '#__apoth_api_consumers' );
			$query2 = '';
		}
		else {
			$query = 'UPDATE '.$db->nameQuote( '#__apoth_api_consumers' );
			$query2 = "\n".' WHERE '.$db->nameQuote('id').' = '.$db->Quote($id);
		}
		
		$query .= "\n".'SET '
				."\n  ".$db->nameQuote('name').' = '.$db->Quote( $r->getName() )
				."\n, ".$db->nameQuote('enabled').' = '.$db->Quote( $r->getEnabled() )
				."\n, ".$db->nameQuote('description').' = '.$db->Quote( $r->getDescription() )
				."\n, ".$db->nameQuote('cons_key').' = '.$db->Quote( $r->getKey() )
				."\n, ".$db->nameQuote('cons_secret').' = '.$db->Quote( $r->getSecret() )
				.$query2;
		$db->setQuery( $query );
		$db->Query();
//		debugQuery( $db );
		
		if( $isNew ) {
			$r->setDatum( 'id', $db->insertId() );
		}
		$this->_clearCache( $r->getId() );
		
		return ( $db->getErrorMsg() == '' );
	}
}


/**
 * API consumer object
 *
 * A single consumer is modeled by this class
 *
 * @author     Punnet - Arc Team
 * @package    Arc
 * @subpackage API
 * @since      1.6
 */
class ApothApiConsumer extends JObject
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
	function getDatum( $prop )
	{
		return $this->_data[$prop];
	}
	
	function getId()          { return $this->_data['id']; }
	function getEnabled()     { return $this->_data['enabled']; }
	function getName()        { return $this->_data['name']; }
	function getDescription() { return $this->_data['description']; }
	function getKey()         { return $this->_data['cons_key']; }
	function getSecret()      { return $this->_data['cons_secret']; }
	
	/**
	 * Set the given property
	 * 
	 * @param string $prop  The given property
	 * @param mixed $value  The value to set
	 */
	function setDatum( $prop, $value )
	{
		switch( $prop ) {
		case( 'cons_key' ):
			// the secret is based on the key, so an updated key means an updated secret
			$params = JComponentHelper::getParams( 'com_arc_api' );
			$this->_data['cons_secret'] = sha1( $params->get( 'salt' ).$value.$params->get( 'salt' ) );
			break;
			
		case( 'cons_secret' ):
			// the secret may not be set directly
			return false;
			break;
		}
		
		$this->_data[$prop] = $value;
	}
	
	/**
	 * Mark the consumer as disabled
	 * 
	 * @return bool  Was the course successfully marked as disabled 
	 */
	function disable()
	{
		$this->_data['enabled'] = 0;
		return $this->commit();
	}
	
	/**
	 * Save this consumer object to the database
	 * 
	 * @return boolean  Whether or not the object was successfully committed
	 */
	function commit()
	{
		$fCon = ApothFactory::_( 'api.consumer' );
		return $fCon->commitInstance( $this->_data['id'] );
	}
}
?>