<?php
/**
 * @package     Arc
 * @subpackage  Homepage
 * @copyright   Copyright (C) 2005 Punnet. All rights reserved. See COPYRIGHT_ARC.txt
 * @license     http://www.gnu.org/licenses/gpl.html GNU/GPL. See LICENSE_ARC.txt
 * Arc is free software: you can redistribute it and/or modify
 * it under the terms of version 3 of the GNU General Public License
 * as published by the Free Software Foundation.
 * The disclaimer of warranty as stated in the GPL applies to this program
 */

defined( '_JEXEC' ) or die( 'Restricted access' );

jimport( 'joomla.application.component.model' );

/*
 * Homepage Task Model
 * 
 * @author     d.swain@wildern.hants.sch.uk
 * @package    Arc
 * @subpackage Homepage
 * @since      1.5
 */
class HomepageModelHomepage extends JModel
{
	function __construct()
	{
		parent::__construct();
		$this->_panels = array();
	}
	
	/**
	 * Sets the profile to use with the instanciated panels when generating them
	 * Checks that the current user is allowed to view the specified profile. If not, their own profile is set instead.
	 *
	 * @param string $uId  The arc user id whose profile is to be retrieved.
	 */
	function setProfile( $uId )
	{
		if( !isset($this->_profiles[$uId]) ) {
			$db = &JFactory::getDBO();
			$query = 'SELECT p.'.$db->nameQuote( 'id' )
				."\n".' FROM '.$db->nameQuote( '#__apoth_ppl_people' ).' AS p'
				."\n".' ~LIMITINGJOIN~'
				."\n".' WHERE p.'.$db->nameQuote( 'id' ).' = '.$db->Quote( $uId );
			$db->setQuery( ApotheosisLibAcl::limitQuery($query, 'people.people') );
			$result = $db->loadResultArray();
			
			if( empty($result) ) {
				$u = &ApotheosisLib::getUser();
				$uId = $u->person_id;
			}
			else {
				$uId = reset( $result );
			}
			
			$this->_profiles[$uId] = ApotheosisPeopleData::getProfile( $uId );
		}
		unset( $this->_profile );
		$this->_profile = &$this->_profiles[$uId];
	}
	
	/**
	 * Retrieves the currently set profile
	 */
	function getProfile()
	{
		return $this->_profile;
	}
	
	/**
	 * Sets the panels to be used based on the requirements given
	 * 
	 * @param array $requirements  Associative array of property=>value-array pairs to restrict the data
	 * @param boolean $persistent  Whether or not to force loading of persistent panels
	 * @return int  The number of panels loaded
	 */
	function setPanels( $requirements = array(), $persistent = false )
	{
		$db = &JFactory::getDBO();
		
		// make sure any persistent panels are included in the requirements sent to the profile object
		if( $persistent ) {
			$query = 'SELECT '.$db->nameQuote('id')
				."\n".'FROM '.$db->nameQuote('#__apoth_home_panels')
				."\n".'WHERE '.$db->nameQuote('persistent').' = '.$db->Quote('1');
			$db->setQuery( $query );
			
			$pers = $db->loadResultArray();
			foreach( $pers as $persId ) {
				$requirements['id'][] = $persId;
			}
			foreach( $requirements as $property=>$propArray ) {
				$requirements[$property] = array_unique( $propArray );
			}
		}
		
		$this->_panelDefs = $this->_profile->getPanels( $requirements );
		$panelIds = array();
		foreach( $this->_panelDefs as $def ) {
			$panelIds[] = $db->Quote( $def['id'] );
		}
		
		$query = 'SELECT *'
			."\n".' FROM '.$db->nameQuote( '#__apoth_home_panels' ).' AS p'
			."\n".' WHERE '.$db->nameQuote( 'id' ).' IN ('.implode( ', ', $panelIds ).' )';
		$db->setQuery( $query );
		$data = $db->loadAssocList( 'id' );
		foreach( $this->_panelDefs as $def ) {
			$id = $def['id'];
			if( isset($data[$id]) ) {
				if( $data[$id]['type'] == 'module' ) {
					$pName = 'ApothModulePanel'.$data[$id]['option'];
					$this->_panels[$id] = new $pName( $id, $data[$id] );
				}
				else {
					$this->_panels[$id] = new ApothPanel( $id, $data[$id], $this->_profile->getId() );
				}
				$this->_setPanel( $id, $def );
			}
		}
		
		$this->_numPanels = count($this->_panels);
		
		return $this->_numPanels;
	}
	
	/**
	 * Adds a panel to the model indexed on its id
	 *
	 * @param object $panel  the panel object
	 */
	function setPanel( $panel, $def = null )
	{
		$pId = $panel->getId();
		$this->_panels[$pId] = $panel;
		$this->_numPanels ++;
		$this->_setPanel( $pId, $def );
	}
	
	function _setPanel( $pId, $def )
	{
		$this->_panels[$pId]->setProfile( $this->_profile );
		if( !is_null($def) ) {
			$this->_panels[$pId]->setParams( $def );
			$this->_cols[$def['col']][$pId] = &$this->_panels[$pId];
		}
	}
	
	/**
	 * Retrieves the next panel object in our set
	 *
	 * @return object  The next panel
	 */
	function &getPanel( $id = null)
	{
		if( is_null($id) ) {
			if( !isset($this->_curPanel) ) {
				$this->_panelKeys = array_keys($this->_panels);
				$this->_curPanel = 0;
			}
			else {
				$this->_curPanel++;
			}
			
			if( isset($this->_panelKeys[$this->_curPanel]) ) {
				$retVal = $this->_panels[$this->_panelKeys[$this->_curPanel]];
			}
			else {
				$retVal = null;
				unset( $this->_curPanel );
			}
		}
		else {
			$retVal = $this->_panels[$id];
		}
		return $retVal;
	}
	
	/**
	 * Clears which panels are currently in use 
	 */
	function clearPanels()
	{
		$this->_panels = array();
		$this->_cols = array();
		$this->_numPanels = 0;
		foreach( $this->_profiles as $id=>$profile ) {
			$this->_profiles[$id]->clearPanels();
		}
	}
	
	/**
	 * Retrieve the panels to be shown in the column specified
	 *
	 * @param string $colId  the column number
	 * @return array  an array of panel objects indexed on panel id
	 */
	function getColPanels( $colId )
	{
		return ( is_array($this->_cols[$colId]) ? $this->_cols[$colId] : array() );
	}
	
	/**
	 * Retrieves the count of panels in our set
	 *
	 * @return int  The total number of panels we have
	 */
	function getNumPanels()
	{
		return $this->_numPanels;
	}
}
?>