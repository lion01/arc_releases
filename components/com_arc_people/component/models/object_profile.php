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
 * Planner Task Object
 */
class ApothProfile extends JObject
{
	/**
	 * The person ID whose profile this is
	 * @access protected
	 * @var string
	 */
	var $_id;
	
	/**
	 * All the data for this profile (equates to a row in the db)
	 * @access protected
	 * @var array
	 */
	var $_data = array();
	
	/**
	 * Constructs a profile object.
	 * The result is either empty or if an ID is given it is
	 * populated by the $data array or by retrieving data from the db
	 * @param int $id  optional If not provided an empty profile object is created.
	 * @param array $data  optional If given along with an id this is used as the data for the object
	 * @return object  The newly created profile object
	 */
	function __construct( $uId = false, $data = array() )
	{
		parent::__construct();
		
		$db = &JFactory::getDBO();
		
		if( $uId !== false ) {
			$this->_id = $uId;
			// get data for the profile if none supplied
			if( empty($data) ) {
				$query = 'SELECT *'
					."\n".' FROM '.$db->nameQuote('#__apoth_ppl_profiles').' AS p'
					."\n".' WHERE '.$db->nameQuote('person_id').' = '.$db->Quote( $uId );
				$db->setQuery( $query );
				$data = $db->loadAssocList();
			}
			// store the data in the object
			$this->_data = $data;
		}
		
		$query = 'SELECT *'
			."\n".'FROM '.$db->nameQuote('#__apoth_ppl_profile_categories');
		$db->setQuery( $query );
		$raw = $db->loadAssocList();
		
		$this->_catIds = array();
		foreach( $raw as $cat ) {
			$this->_catIds[strtolower($cat['name'])] = (int)$cat['id'];
		}
		
		$this->_deletables = array();
	}
	
	function getId()
	{
		return $this->_id;
	}
	
	/**
	 * Retrieves the IDs used by this profile for various sites
	 * @return array  Associative array of property=>value pairs with all properties being id uses
	 */
	function getIds()
	{
		if( !isset($this->_ids) ) {
			$this->_loadIds();
		}
		return $this->_ids;
	}
	
	/**
	 * Loads the ids from the raw data into a nicer format
	 */
	function _loadIds()
	{
		$this->_ids = array();
		
		$db = &JFactory::getDBO();
		$query = 'SELECT *'
			."\n".' FROM '.$db->nameQuote('#__apoth_ppl_people').' AS p'
			."\n".' LEFT JOIN '.$db->nameQuote('#__users').' AS u'
			."\n".'   ON u.id = p.juserid'
			."\n".' WHERE p.'.$db->nameQuote('id').' = '.$db->Quote( $this->_id );
		$db->setQuery( $query );
		$r = $db->loadObjectList();
		if( !is_array($r) ) { $r = array(); }
		$r = reset($r);
		
		$this->_ids['ARC']      = $this->_id;
		$this->_ids['JUSERID']  = $r->juserid;
		$this->_ids['USERNAME'] = $r->username;
		$this->_ids['EXTERNAL'] = $r->ext_person_id;
		$this->_ids['UPN']      = $r->upn;
		
		foreach( $this->_data as $v ) {
			if( isset($this->_catIds['ids']) && ($v['category_id'] == $this->_catIds['ids']) ) {
				$this->_ids[$v['property']] = $v['value'];
			}
		}
	}
	
	/**
	 * Retrieves the panel definitions used by this profile
	 * 
	 * @param array $requirements  Associative array of property=>value-array pairs to restrict the data
	 * @return array  Associative array of sort-order=>config-array pairs with all entries being panels this profile requires
	 */
	function getPanels( $requirements = array() )
	{
		if( !isset($this->_panels) ) {
			$this->_loadPanels( $requirements );
		}
		
		return $this->_panels;
	}
	
	/**
	 * Loads the panel definitions from the raw data into a nicer format
	 * 
	 * @param array $requirements  Associative array of property=>value-array pairs to restrict the data
	 */
	function _loadPanels( $requirements = array() )
	{
		$this->_panels = array();
		foreach( $this->_data as $v ) {
			$ok = true;
			if( isset($this->_catIds['panels']) && ($v['category_id'] == $this->_catIds['panels']) ) {
				$def = array();
				$lines = explode( "\n", trim($v['value']) );
				foreach( $lines as $line ) {
					if( $pos = strpos($line, '=') ) {
						$property = trim( substr($line, 0, $pos) );
						$value = trim( substr($line, $pos+1) );
						$def[$property] = $value;
					}
				}
				foreach( $requirements as $property=>$valueArray ) {
					if( array_search($def[$property], $valueArray) === false ) {
						$ok = false;
						break; // this panel doesn't meet requirements so stop loading it in
					}
				}
				
				if( $ok ) {
					$this->_panels[$v['property']] = $def;
				}
			}
		}
		ksort($this->_panels);
	}
	
	/**
	 * Clears which panels are currently in use 
	 */
	function clearPanels()
	{
		unset($this->_panels);
	}
	
	/**
	 * Retrieves the link definitions used by this profile
	 * @return array  Associative array of sort-order=>config-array pairs with all entries being links this profile requires
	 */
	function getLinks( $panel = false )
	{
		if( !isset($this->_links) ) {
			$this->_loadLinks();
		}
		
		if( $panel == false ) {
			$retVal = $this->_links;
		}
		else {
			$retVal = array();
			foreach( $this->_links as $k=>$v ) {
				if( $v['panel'] == $panel ) {
					$retVal[$k] = $v;
				}
			}
			
			return $retVal;
		}	
	}
	
	/**
	 * Adds a link to the list of links.
	 * @param string $text  The text for the link
	 * @param string $item  The url for the link, or the file name to link to
	 * @param string $panel  The panel in which to display the link ('links' or 'showcase')
	 * @return int  The id of the new link
	 */
	function addLink( $text, $item, $panel, $isFile = false )
	{
		if( !isset($this->_links) ) {
			$this->_loadLinks();
		}
		
		$newLink = array( 'text'=>$text, 'panel'=>$panel );
		if( $isFile ) {
			$newLink['url'] = '~'.$this->_id.'.'.$item.'~';
		}
		else {
			$newLink['url'] = $item;
		}
		$this->_links[] = $newLink;
		
		end($this->_links);
		return key($this->_links);
	}
	
	/**
	 * Removes a link from the list of links
	 * @param int $id  The id of the link to remove
	 * @return boolean  True on success, false on failure
	 */
	function removeLink( $id )
	{
		$r = isset( $this->_links[$id] );
		$this->_deletables[] = array( 'category_id'=>$this->_catIds['links'], 'property'=>$id, 'value'=>$this->_links[$id]['id'] );
		unset( $this->_links[$id] );
		return $r;
	}
	
	/**
	 * Loads the link definitions from the raw data into a nicer format
	 */
	function _loadLinks()
	{
		$this->_links = array();
		$ids = $this->getIds();
		
		foreach( $this->_data as $v ) {
			if( isset($this->_catIds['links']) && ($v['category_id'] == $this->_catIds['links']) ) {
				$linkIds[$v['property']] = $v['value'];
			}
		}
		
		$db = &JFactory::getDBO();
		foreach( $linkIds as $linkId ) {
			$quotedIds[] = $db->Quote( $linkId );
		}
		$quotedIds = implode( ', ', $quotedIds );
		
		$query = 'SELECT *'
			."\n".'FROM '.$db->nameQuote('#__apoth_home_links')
			."\n".'WHERE '.$db->nameQuote('id').' IN ('.$quotedIds.')';
		$db->setQuery( $query );
		
		$links = $db->loadAssocList('id');
		
		foreach( $linkIds as $order=>$linkId ) {
			// transform file indicators to links
			$matches = array();
			preg_match( '/^~(.+?)\\.(.+)~$/', $links[$linkId]['url'], $matches );
			if( count( $matches ) == 3 ) {
				$links[$linkId]['url'] = ApotheosisPeopleData::getFileLink( $matches[1], $matches[2] );
			}
			
			// add to ordered list
			$this->_links[$order] = $links[$linkId];
		}
		
		ksort( $this->_links, SORT_NUMERIC );
	}
	
	/**
	 * Retrieves the link definitions used by this profile
	 * @return array  Associative array of sort-order=>config-array pairs with all entries being awards associated with this profile
	 */
	function getAwards()
	{
		if( !isset($this->_awards) ) {
			$this->_loadAwards();
		}
		return $this->_awards;
	}
	
	/**
	 * Loads the link definitions from the raw data into a nicer format
	 */
	function _loadAwards()
	{
		$db = &JFactory::getDBO();
		$query = 'SELECT * FROM #__apoth_ppl_profile_awards';
		$db->setQuery( $query );
		$awards = $db->loadAssocList( 'id' );
		
		$this->_awards = array();
		foreach( $this->_data as $v ) {
			if( isset($this->_catIds['awards']) && ($v['category_id'] == $this->_catIds['awards']) ) {
				$def = array();
				$lines = explode( "\n", trim($v['value']) );
				foreach( $lines as $line ) {
					if( $pos = strpos($line, '=') ) {
						$property = trim( substr($line, 0, $pos) );
						$value = trim( substr($line, $pos+1) );
						$def[$property] = $value;
					}
					if( isset($def['id']) ) {
						$def = array_merge($def, $awards[$def['id']]);
					}
				}
				
				$this->_awards[$v['property']] = $def;
			}
		}
		ksort($this->_awards);
	}
	
	/**
	 * Sets the sen information to have new values
	 * @param unknown_type $data
	 */
	function setSen( $data )
	{
		if( !isset($this->_sen) ) {
			$this->_loadSen();
		}
		
		foreach( $this->_sen as $k=>$v ) {
			if( isset($data[$k]) ) {
				$this->_sen[$k] = $data[$k];
			}
		}
	}
	
	/**
	 * Retrieves the sen information relevant to this profile
	 * @return array  Associative array of sort-order=>config-array pairs with all entries being awards associated with this profile
	 */
	function getSen()
	{
		if( !isset($this->_sen) ) {
			$this->_loadSen();
		}
		return $this->_sen;
	}
	
	/**
	 * Loads the the sen information from the raw data into a nicer format
	 */
	function _loadSen()
	{
		$this->_sen = array();
		
		$ids = $this->getIds();
		$patterns = array();
		foreach( $ids as $k=>$v ) {
			$patterns[] = '~'.preg_quote( '~'.$k.'~', '~' ).'~';
		}
		
		foreach( $this->_data as $v ) {
			if( isset($this->_catIds['sen']) && ($v['category_id'] == $this->_catIds['sen']) ) {
				$this->_sen[$v['property']] = $v['value'];
			}
		}
		ksort($this->_sen);
	}
	
	/**
	 * Retrieves the display name for this user
	 */
	function getDisplayName()
	{
		if( !isset($this->_personData) ) {
			$this->_loadPersonData();
		}
		return $this->_personData['displayname'];
	}
	
	function getBiography()
	{
		if( !isset($this->_profilePersonal) ) {
			$this->_loadPersonData();
		}
		return $this->_profilePersonal['biography'];
	}
	
	function setBiography( $text )
	{
		$this->_profilePersonal['biography'] = $text;
	}
	
	function getPersonData()
	{
		if( !isset($this->_personData) ) {
			$this->_loadPersonData();
		}
		return array_merge( $this->_personData, $this->_profilePersonal );
	}
	
	/**
	 * Loads the panel definitions from the raw data into a nicer format
	 */
	function _loadPersonData()
	{
		// Get data from people list
		$db = &JFactory::getDBO();
		$query = 'SELECT *'
			."\n".' FROM #__apoth_ppl_people AS p'
			."\n".' WHERE p.id = '.$db->Quote($this->_id);
		$db->setQuery($query);
		$data = $db->loadAssoc();
		$this->_personData = $data;
		$style = 'person';
		$this->_personData['displayname'] = ApotheosisLib::nameCase( $style, $data['title'], $data['firstname'], $data['middlenames'], $data['surname'] );
		
		$this->_profilePersonal = array();
		// Then get data from profile
		foreach( $this->_data as $v ) {
			if( isset($this->_catIds['personal']) && ($v['category_id'] == $this->_catIds['personal']) ) {
				$this->_profilePersonal[$v['property']] = $v['value'];
			}
		}
	}
	
	function commit()
	{
		$db = &JFactory::getDBO();
		$values = array();
		$id = $db->Quote($this->_id);
		
		// Add or update all the profile's data
		$query = 'REPLACE INTO '.$db->nameQuote('#__apoth_ppl_profiles')
			.'( '.$db->nameQuote('person_id')
			.', '.$db->nameQuote('category_id')
			.', '.$db->nameQuote('property')
			.', '.$db->nameQuote('value')
			.' )'
			."\n".' VALUES ';
		
		if( isset($this->_catIds['personal']) ) {
			$cat = $db->Quote( $this->_catIds['personal']);
			if( !isset($this->_profilePersonal) ) {
				$this->_loadPersonData();
			}
			foreach( $this->_profilePersonal as $property=>$value ) {
				$values[] = '('.$id.', '.$cat.', '.$db->Quote($property).', '.$db->Quote($value).')';
			}
		}
		
		if( isset($this->_catIds['sen']) ) {
			$cat = $db->Quote( $this->_catIds['sen']);
			if( !isset($this->_sen) ) {
				$this->_loadSen();
			}
			foreach( $this->_sen as $property=>$value ) {
				$values[] = '('.$id.', '.$cat.', '.$db->Quote($property).', '.$db->Quote($value).')';
			}
		}
		
		if( !empty($values) ) {
			$query .= implode( ', '."\n", $values );
			$db->setQuery( $query );
			$db->query();
		}
		
		// BEGIN Handle saving of links separately
		// *** Homepage component should be doing most of this
		if( isset($this->_catIds['links']) ) {
			// set the quoted category id
			$cat = $db->Quote( $this->_catIds['links']);
			
			// delete any links marked for deletion
			$delLinks = array();
			foreach( $this->_deletables as $k=>$deletable ) {
				if( $deletable['category_id'] == $this->_catIds['links'] ) {
					$delLinks[] = $deletable;
					unset( $this->_deletables[$k] );
				}
			}
			
			if( !empty($delLinks) ) {
				// delete from profile
				$query = 'DELETE FROM '.$db->nameQuote('#__apoth_ppl_profiles')
					."\n".'WHERE ';
				
				foreach( $delLinks as $delLink ) {
					$wheres[] = '( '.$db->nameQuote('person_id').' = '.$id
					.' AND '.$db->nameQuote('category_id').' = '.$db->Quote($delLink['category_id'])
					.' AND '.$db->nameQuote('property')   .' = '.$db->Quote($delLink['property'])
					.' )';
					
					$delLinksIds[] = $delLink['value'];
					$delLinksIdsQuoted[] = $db->quote( $delLink['value'] );
				}
				
				if( !empty($wheres) ) {
					$query .= implode( "\n".'   OR ', $wheres );
					$db->setQuery( $query );
					$db->query();
				}
				
				// delete links if no-one else is using them
				$delLinksIdsQuoted = implode( ', ', $delLinksIdsQuoted );
				
				$query = 'SELECT DISTINCT'.$db->nameQuote('value')
					."\n".'FROM '.$db->nameQuote('#__apoth_ppl_profiles')
					."\n".'WHERE '.$db->nameQuote('value').' IN ('.$delLinksIdsQuoted.' )';
				$db->setQuery( $query );
				$inUse = $db->loadResultArray();
				
				$removeLinks = array_diff( $delLinksIds, $inUse );
				if( !empty($removeLinks) ) {
					foreach( $removeLinks as $removeLink ) {
						$removeLinksQuoted[] = $db->Quote($removeLink);
					}
					$removeLinksQuoted = implode( ', ', $removeLinksQuoted );
					$query = 'DELETE FROM '.$db->nameQuote('#__apoth_home_links')
						."\n".'WHERE '.$db->nameQuote('id').' IN ( '.$removeLinksQuoted.' )';
					$db->setQuery( $query );
					$db->query();
				}
			}
			
			// get all the links stored in the model
			if( !isset($this->_links) ) {
				$this->_loadLinks();
			}
			$curLinks = $this->_links;
			
			// get all the link properties stored in the database profile
			$query = 'SELECT '.$db->nameQuote('property')
				."\n".'FROM '.$db->nameQuote('#__apoth_ppl_profiles')
				."\n".'WHERE '.$db->nameQuote('person_id').' = '.$id
				."\n".'  AND '.$db->nameQuote('category_id').' = '.$cat;
			$db->setQuery( $query );
			$existingProps = $db->loadResultArray();
			
			// work out which is the new link to deal with
			foreach( $existingProps as $prop ) {
				unset( $curLinks[$prop] );
			}
			if( !empty($curLinks) ) {
				$newLink = reset( $curLinks );
				$newProp = key( $curLinks );
				
				// see if the new link is already stored in the database
				$query = 'SELECT '.$db->nameQuote('id')
					."\n".'FROM '.$db->nameQuote('#__apoth_home_links')
					."\n".'WHERE '.$db->nameQuote('text').' = '.$db->Quote($newLink['text'])
					."\n".'  AND '.$db->nameQuote('panel').' = '.$db->Quote($newLink['panel'])
					."\n".'  AND '.$db->nameQuote('url').' = '.$db->Quote($newLink['url']);
				$db->setQuery( $query );
				$linkId = $db->loadResult();
				
				// if this is a totally new link insert it into links table
				if( is_null($linkId) ) {
					$query = 'INSERT INTO '.$db->nameQuote('#__apoth_home_links')
						."\n".'VALUES ( NULL, '.$db->Quote($newLink['text']).', '.$db->Quote($newLink['panel']).', '.$db->Quote($newLink['url']).' )';
					$db->setQuery( $query );
					$db->query();
					$linkId = $db->insertid();
				}
				
				// Insert the new profile entry
				$query = 'INSERT INTO '.$db->nameQuote('#__apoth_ppl_profiles')
					."\n".' VALUES ( '.$id.', '.$cat.', '.$db->Quote($newProp).', '.$db->Quote($linkId).' )';
				$db->setQuery( $query );
				$db->query();
			}
		}
		// END Handle saving of links separately
		
		// Delete any values which have been flagged for deletion
		$query = 'DELETE FROM '.$db->nameQuote('#__apoth_ppl_profiles')
			."\n".' WHERE '
			."\n";
		
		foreach( $this->_deletables as $d ) {
			$wheres[] = '( '.$db->nameQuote('person_id').' = '.$id
			.' AND '.$db->nameQuote('category_id').' = '.$db->Quote($d['category_id'])
			.' AND '.$db->nameQuote('property')   .' = '.$db->Quote($d['property'])
			.' )';
		}
		
		if( !empty($wheres) ) {
			$query .= implode( ' OR '."\n", $wheres );
			$db->setQuery( $query );
			$db->query();
		}
		$this->_deletables = array();
	}
}
?>