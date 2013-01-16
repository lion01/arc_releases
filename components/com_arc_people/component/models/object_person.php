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
 * People Person Factory
 */
class ApothFactory_people_person extends ApothFactory
{
	/**
	 * Retrieve a blank person object with the given ID
	 * 
	 * @param int $id  The id that should be used for the dummy object. Must be negative if supplied.
	 */
	function &getDummy( $id = null )
	{
		if( is_null($id) ) {
			$id = $this->_getDummyId();
		}
		elseif( $id >= 0 ) {
			$r = null;
			return $r;
		}
		
		$r = &$this->_getInstance( $id );
		if( is_null($r) ) {
			$r = new ApothPplPeoplePerson( array('id'=>$id) );
			$this->_addInstance( $id, $r );
		}
		
		return $r;
	}
	
	/**
	 * Retrieves the identified person, creating the object if it didn't already exist
	 * 
	 * @param string $id  Arc ID
	 */
	function &getInstance( $id )
	{
		$r = &$this->_getInstance( $id );
		if( is_null($r) ) {
			$db = &JFactory::getDBO();
			
			$query = 'SELECT '.$db->nameQuote('p').'.'.$db->nameQuote('id')
				 .', '.$db->nameQuote('p').'.'.$db->nameQuote('juserid')
				 .', COALESCE( '.$db->nameQuote('p').'.'.$db->nameQuote('preferred_firstname').', '.$db->nameQuote('p').'.'.$db->nameQuote('firstname').' ) AS '.$db->nameQuote('firstname')
				 .', COALESCE( '.$db->nameQuote('p').'.'.$db->nameQuote('preferred_surname').', '.$db->nameQuote('p').'.'.$db->nameQuote('surname').' ) AS '.$db->nameQuote('surname')
				 .', '.$db->nameQuote('p').'.'.$db->nameQuote('middlenames')
				 .', '.$db->nameQuote('p').'.'.$db->nameQuote('dob')
				 .', '.$db->nameQuote('p').'.'.$db->nameQuote('gender')
				 .', '.$db->nameQuote('p').'.'.$db->nameQuote('email')
				."\n".'FROM '.$db->nameQuote( '#__apoth_ppl_people' ).' AS '.$db->nameQuote('p')
				."\n".'~LIMITINGJOIN~'
				."\n".'WHERE '.$db->nameQuote('id').' = '.$db->Quote($id);
			$db->setQuery( ApotheosisLibAcl::limitQuery($query, 'people.people') );
			$data = $db->loadAssoc();
			
			$r = new ApothPplPeoplePerson( $data );
			$this->_addInstance( $id, $r );
		}
		
		return $r;
	}
	
	/**
	 * Retrieves the people identified by the given requirements
	 * 
	 * @param array $requirements  Array of col/val pairs to search on
	 * @param boolean $init  Do we want to also initialise and cache the objects
	 */
	function &getInstances( $requirements, $init = true )
	{
		$sId = $this->_getSearchId( $requirements );
		$ids = $this->_getInstances( $sId );
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
				elseif( is_null($val) ) {
					$assignPart = ' IS NULL';
				}
				else {
					$assignPart = ' = '.$db->Quote( $val );
				}
				switch( $col ) {
				case( 'id' ):
					$where[] = $db->nameQuote('p').'.'.$db->nameQuote('id').$assignPart;
					break;
				
				case( 'firstname' ):
					$where[] = $db->nameQuote('p').'.'.$db->nameQuote('firstname').$assignPart;
					break;
				
				case( 'surname' ):
					$where[] = $db->nameQuote('p').'.'.$db->nameQuote('surname').$assignPart;
					break;
				
				case( 'relOf' ):
					$select[] = $db->nameQuote('rt').'.'.$db->nameQuote('description')
						 .', '.$db->nameQuote('r').'.'.$db->nameQuote('parental')
						 .', '.$db->nameQuote('r').'.'.$db->nameQuote('legal_order')
						 .', '.$db->nameQuote('r').'.'.$db->nameQuote('correspondence')
						 .', '.$db->nameQuote('r').'.'.$db->nameQuote('reports');
					$where[] = $db->nameQuote('r').'.'.$db->nameQuote('pupil_id').$assignPart;
					$joins[] = 'INNER JOIN '.$db->nameQuote( '#__apoth_ppl_relations' ).' AS '.$db->nameQuote('r')
						."\n".'   ON '.$db->nameQuote('r').'.'.$db->nameQuote('relation_id').' = '.$db->nameQuote('p').'.'.$db->nameQuote('id')
						."\n".'INNER JOIN '.$db->nameQuote( '#__apoth_ppl_relation_tree' ).' AS '.$db->nameQuote('rt')
						."\n".'   ON '.$db->nameQuote('rt').'.'.$db->nameQuote('id').' = '.$db->nameQuote('r').'.'.$db->nameQuote('relation_type_id');
					break;
				
				case( 'juserid' ):
					$where[] = $db->nameQuote('p').'.'.$db->nameQuote('juserid').$assignPart;
					break;
				}
			}
			
			$query = 'SELECT '.$db->nameQuote('p').'.'.$db->nameQuote('id')
				.', '.$db->nameQuote('p').'.'.$db->nameQuote('juserid')
				 .', COALESCE( '.$db->nameQuote('p').'.'.$db->nameQuote('preferred_firstname').', '.$db->nameQuote('p').'.'.$db->nameQuote('firstname').' ) AS '.$db->nameQuote('firstname')
				 .', COALESCE( '.$db->nameQuote('p').'.'.$db->nameQuote('preferred_surname').', '.$db->nameQuote('p').'.'.$db->nameQuote('surname').' ) AS '.$db->nameQuote('surname')
				 .', '.$db->nameQuote('p').'.'.$db->nameQuote('middlenames')
				 .', '.$db->nameQuote('p').'.'.$db->nameQuote('dob')
				 .', '.$db->nameQuote('p').'.'.$db->nameQuote('gender')
				 .', '.$db->nameQuote('p').'.'.$db->nameQuote('email')
				 .( empty($select) ? '' : ', '.implode(', ', $select) )
				."\n".'FROM '.$db->nameQuote( '#__apoth_ppl_people' ).' AS '.$db->nameQuote('p')
				."\n".'~LIMITINGJOIN~'
				.( empty($joins) ? '' : "\n".implode("\n", $joins) )
				.( empty($where) ? '' : "\n".'WHERE '.implode("\n".'  AND ', $where) )
				."\n".'ORDER BY '.$db->nameQuote('p').'.'.$db->nameQuote('surname').' ASC';
			$db->setQuery( ApotheosisLibAcl::limitQuery($query, 'people.people') );
			$data = $db->loadAssocList( 'id' );
			
			$ids = array_keys( $data );
			$this->_addInstances( $sId, $ids );
			
			if( $init ) {
				$existing = $this->_getInstances();
				$newIds = array_diff( $ids, $existing );
				
				// initialise and cache
				foreach( $newIds as $id ) {
					$r = new ApothPplPeoplePerson( $data[$id] );
					$this->_addInstance( $id, $r );
					unset( $r );
				}
			}
		}
		
		return $ids;
	}
	
	/**
	 * Commits the instance to the db,
	 * updates the cached instance,
	 * clears the search cache if we've added a new instance
	 *  (the newly created instance may match any of the searches we previously executed)
	 * 
	 * @param int $id  Unique identifer
	 * @return boolean $success  Indication of the success of the commit
	 */
	function commitInstance( $id )
	{
		$r = &$this->_getInstance( $id );
		if( is_null($r) ) {
			return false;
		}
		$db = &JFactory::getDBO();
		$isNew = $r->getId() < 0;
		
		if( $isNew ) {
			$query = 'INSERT INTO '.$db->nameQuote( '#__apoth_ppl_people' );
			$where = '';
		}
		else {
			$query = 'UPDATE '.$db->nameQuote( '#__apoth_ppl_people' );
			$where = 'WHERE '.$db->nameQuote( 'id' ).' = '.$db->Quote( $id );
		}
		
		$query = $query
			."\n".'SET'
			."\n  ".$db->nameQuote( 'id' )       .' = '.$db->Quote( $r->getDatum('id') )
			."\n, ".$db->nameQuote( 'juserid' )  .' = '.$db->Quote( $r->getDatum('juserid') )
			."\n, ".$db->nameQuote( 'firstname' ).' = '.$db->Quote( $r->getDatum('firstname') )
			."\n, ".$db->nameQuote( 'surname' )  .' = '.$db->Quote( $r->getDatum('surname') )
			."\n, ".$db->nameQuote( 'dob' )      .' = '.$db->Quote( $r->getDatum('dob') )
			."\n, ".$db->nameQuote( 'gender' )   .' = '.$db->Quote( $r->getDatum('gender') )
			."\n, ".$db->nameQuote( 'email' )    .' = '.$db->Quote( $r->getDatum('email') )
			."\n  ".$where;
		$db->setQuery( $query );
		$db->Query();
		
		// no errors means successful commit
		$errMsg = $db->getErrorMsg();
		$success = ( $errMsg == '' );
		if( !$success ) {
			$r->setError( $errMsg );
		}
		
		return ( $success );
	}
}


/**
 * People Person Object
 */
class ApothPplPeoplePerson extends JObject
{
	/**
	 * All the data for this person (equates to a row in the db)
	 * @access protected
	 * @var array
	 */
	var $_core = array();
	
	function __construct( $data )
	{
		$this->_core = $data;
	}
	
	/**
	 * Accessor function to retrieve id
	 * 
	 * @return int  The id for this object
	 */
	function getId()
	{
		return $this->_core['id'];
	}
	
	/**
	 * Accessor function to retrieve core data
	 * 
	 * @param string $key  The name of the value we want
	 * @return mixed  The value requested
	 */
	function getDatum( $key )
	{
		return ( isset($this->_core[$key]) ? $this->_core[$key] : null );
	}
	
	/**
	 * Set the value of a core data field
	 * 
	 * @param string $key  The field whose value we wish to set
	 * @param mixed $val  The value we wish to set
	 */
	function setDatum( $key, $val )
	{
		$this->_core[$key] = $val;
	}
	
	/**
	 * Is this person a full contact
	 * (This currently assumes an awful lot, like we know for sure who the person
	 * is a potenial full contact for)
	 * 
	 * @return mixed $isFullContact  Null if no decision could be made otherwise true/false
	 */
	function isFullContact()
	{
		$isFullContact = null;
		if( isset($this->_core['parental'], $this->_core['legal_order'], $this->_core['correspondence'], $this->_core['reports']) ) {
			$isFullContact = (
			    $this->_core['parental'] == '1'
			 && $this->_core['legal_order'] == '0'
			 && $this->_core['correspondence'] == '1'
			 && $this->_core['reports'] == '1'
			);
		}
		
		return $isFullContact;
	}
	
	/**
	 * Trigger saving of the object to the database
	 * 
	 * @return boolean  An indication of the success of the factory's commit procedure
	 */
	function commit()
	{
		$fPeo = ApothFactory::_( 'people.person' );
		return $fPeo->commitInstance( $this->getId() );
	}
}
?>