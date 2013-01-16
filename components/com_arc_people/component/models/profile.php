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

jimport( 'joomla.application.component.model' );
require_once( JPATH_SITE.DS.'components'.DS.'com_arc_core'.DS.'libraries'.DS.'apoth_library.php' ); 
require_once(JPATH_SITE.DS.'components'.DS.'com_arc_people'.DS.'helpers'.DS.'data_access.php');

 /*
 * People Manager People Model
 *
 * @author     Lightinthedark<code@lightinthedark.org.uk>
 * @package    Arc
 * @subpackage People Manager
 * @since      1.5
 */
class PeopleModelProfile extends JModel
{
	/** @var array Array of items */
	var $_items = array();
	
	/** @var array Array of profiles */
	var $_profiles = array();
	
	/**
	 * Takes an associative array of criteria and loads all profiles that match them
	 * @param array $requirements  The values to search for
	 * @param bool $reset  Should we remove all the currently stored profiles from the model
	 * @return array  array of matched person ID's
	 */
	function setProfiles( $requirements, $reset = false )
	{
		if( $reset ) {
			$this->_profiles = array();
		}
		
		// get a database object
		$db = &JFactory::getDBO();
		
		$select = 'SELECT pr.*';
		$join[] = 'INNER JOIN #__apoth_ppl_people AS p'
			."\n".' ON p.id = pr.person_id';
		// loop through search result(s) to build query
		foreach( $requirements as $col=>$val ) {
			if( is_array($val) ) {
				foreach( $val as $k=>$v ) {
					$val[$k] = $db->Quote( $v );
				}
				$assignPart = ' IN ('.implode( ', ',$val ).')';
			}
			else {
				$assignPart = ' = '.$db->Quote( $val );
			}
			
			switch( $col ) {
			case( 'pId' ):
				$where[] = $db->nameQuote('pr').'.'.$db->nameQuote('person_id').$assignPart;
				break;
			}
		}
		
		// formulate query to return matched profile id's
		$query = $select
			."\n".' FROM '.$db->nameQuote( '#__apoth_ppl_profiles' ).' AS '.$db->nameQuote('pr')
			."\n".' '.( empty($join)  ? '' : "\n ".implode("\n ", $join) )
			."\n".' ~LIMITINGJOIN~'
			."\n".' '.( empty($where) ? '' : "\n WHERE ".implode("\n AND ", $where) );
		$db->setQuery( $query );
		$db->setQuery( ApotheosisLibAcl::limitQuery($query, 'people.people') );
		$data = $db->loadAssocList();
		
		$profiles = array();
		foreach( $data as $k=>$v ) {
			$profiles[$v['person_id']][] = $v;
		}
		
		// loop through target id's and instantiate profiles as objects if we don't already have them
		foreach( $profiles as $id=>$profileArray ) {
			if( !array_key_exists($id, $this->_profiles) ) {
				$this->_profiles[$id] = new ApothProfile( $id );
			}
		}
		
		return array_keys( $profiles );
	}
	
	/**
	 * Retrieves a profile
	 * @return object  The profile object
	 */
	function &getProfile()
	{
		reset($this->_profiles);
		$k = key($this->_profiles);
		
		return $this->_profiles[$k];
	}
	
	function getSenPeople()
	{
		$senIds = array(
			  ApotheosisLibAcl::getRoleId( 'pastoral_sen_mentor' )
			, ApotheosisLibAcl::getRoleId( 'pastoral_sen_self' ) );
		$db = &JFactory::getDbo();
		$query = 'SELECT '.$db->nameQuote('id').', '.$db->nameQuote('role')
			."\n".'FROM '.ApotheosisLibAcl::getUserTable( 'people.people', ApotheosisLib::getJUserId(JRequest::getVar('pId')) )
			."\n".'WHERE '.$db->nameQuote('role').' IN ('.implode( ', ', $senIds ).')';
		$db->setQuery($query); // START HERE - limit this based on page action (current if pos)
		$r = $db->loadAssocList();
		if( !is_array($r) ) { $r = array(); }
		
		$retVal = array();
		foreach( $r as $row ) {
			$retVal[$row['role']][] = ApotheosisPeopleData::getProfile( $row['id'] );
		}
		return $retVal;
	}
	
	/**
	 * Gets a list of panels to populate the hide/show form
	 * 
	 * @return array $panelDefs  array of panels indexed on panel id
	 */
	function getPanelSettings()
	{
		$catId = ApotheosisData::_( 'people.profileCatId', 'homepage', 'panels' );
		$u = &JFactory::getUser();
		$pId = $u->person_id;
		$db = &JFactory::getDBO();
		
		$query = 'SELECT *'
			."\n".' FROM '.$db->nameQuote( '#__apoth_ppl_profiles' )
			."\n".' WHERE '.$db->nameQuote( 'person_id' ).' = '.$db->Quote( $pId )
			."\n".'   AND '.$db->nameQuote( 'category_id' ).' = '.$db->Quote( $catId );
		$db->setQuery( $query );
		
		$settings = $db->loadAssocList();
		
		// sort each panels value string into a usable array
		foreach( $settings as $panel ) {
			$values = explode( "\n", trim($panel['value']) );
			foreach( $values as $valueLine ) {
				if( $pos = strpos($valueLine, '=') ) {
					$property = trim( substr($valueLine, 0, $pos) );
					$value = trim( substr($valueLine, $pos+1) );
					$panelDef[$property] = $value;
				}
			}
			$panelDefs[$panelDef['id']] = $panelDef;
		}
		
		// remove certain panels from the list of those hidable
		$excludes = ApotheosisData::_( 'homepage.fixedPanels' );
		foreach( $excludes as $id ) {
			unset( $panelDefs[$id] );
		}
		
		// make a list of panel ids so we can search for panel names
		$panelIds = array_keys( $panelDefs );
		
		// get panel names
		$names = ApotheosisData::_( 'homepage.panelNames', $panelIds );
		
		//add panel names
		foreach( $panelDefs as $panelId=>$panelDef ) {
			$panelDefs[$panelId]['alt'] = $names[$panelId]['alt'];
		}
		
		return $panelDefs;
	}
	
	/**
	 * Saves the shown status of the panels
	 */
	function savePanelSettings()
	{
		$catId = ApotheosisData::_( 'people.profileCatId', 'homepage', 'panels' );
		$db = &JFactory::getDBO();
		
		// get the form data
		$data = implode( '|', array_keys(JRequest::getVar('id')) );
		$pId = JRequest::getVar( 'pId' );
		
		// get the non-customisable panel ids
		$excludes = implode( '|', ApotheosisData::_('homepage.fixedPanels') );
		
		// set everything in the list to be 'shown=1'
		$query = 'UPDATE '.$db->nameQuote( '#__apoth_ppl_profiles' )
			."\n".' SET '.$db->nameQuote( 'value' ).' = REPLACE ('.$db->nameQuote( 'value' ).', '.$db->Quote( 'shown=0' ).', '.$db->Quote( 'shown=1' ).')'
			."\n".' WHERE '.$db->nameQuote( 'person_id' ).' = '.$db->Quote( $pId )
			."\n".'   AND '.$db->nameQuote( 'category_id' ).' = '.$db->Quote( $catId )
			."\n".'   AND '.$db->nameQuote( 'value' ).' REGEXP '.$db->Quote( '([[:space:]]|^)id=('.$data.')([[:space:]]|$)' ).' = 1'
			."\n".'   AND '.$db->nameQuote( 'value' ).' REGEXP '.$db->Quote( '([[:space:]]|^)id=('.$excludes.')([[:space:]]|$)' ).' = 0';
		$db->setQuery( $query );
		$db->Query();
		
		// set everything not in the list to be 'shown=0'
		$query = 'UPDATE '.$db->nameQuote( '#__apoth_ppl_profiles' )
			."\n".' SET '.$db->nameQuote( 'value' ).' = REPLACE ('.$db->nameQuote( 'value' ).', '.$db->Quote( 'shown=1' ).', '.$db->Quote( 'shown=0' ).')'
			."\n".' WHERE '.$db->nameQuote( 'person_id' ).' = '.$db->Quote( $pId )
			."\n".'   AND '.$db->nameQuote( 'category_id' ).' = '.$db->Quote( $catId )
			."\n".'   AND '.$db->nameQuote( 'value' ).' REGEXP '.$db->Quote( '([[:space:]]|^)id=('.$data.')([[:space:]]|$)' ).' = 0'
			."\n".'   AND '.$db->nameQuote( 'value' ).' REGEXP '.$db->Quote( '([[:space:]]|^)id=('.$excludes.')([[:space:]]|$)' ).' = 0';
		$db->setQuery( $query );
		$db->Query();
	}
}
?>