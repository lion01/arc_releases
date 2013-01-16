<?php
/**
 * @package     Arc
 * @subpackage  Assessment
 * @copyright   Copyright (C) 2005 Punnet. All rights reserved. See COPYRIGHT_ARC.txt
 * @license     http://www.gnu.org/licenses/gpl.html GNU/GPL. See LICENSE_ARC.txt
 * Arc is free software: you can redistribute it and/or modify
 * it under the terms of version 3 of the GNU General Public License
 * as published by the Free Software Foundation.
 * The disclaimer of warranty as stated in the GPL applies to this program
 */

defined( '_JEXEC' ) or die( 'Restricted access' );

// path the common model parent class
require_once( JPATH_COMPONENT.DS.'models'.DS.'extension.php' );

/**
 * Assessment Model Admin
 */
class AssessmentsModelAdmin extends AssessmentsModel
{
	/**
	 * Sets the assessment to be used with this model
	 * @param $assId int  Optional id of the assessment to use. If omitted a new assessment is created and used 
	 */
	function setAss( $assId = null )
	{
		if( is_null($assId) ) {
			$this->_assessment = &$this->fAss->getDummy( -1 );
		}
		elseif( !isset($this->_assessment) || ($this->_assessment->getProperty( 'id' ) != $assId) ) {
			unset( $this->_assessment );
			$this->_assessment = &$this->fAss->getInstance( $assId );
		}
		
		if( count($this->_assessment->getAspects()) == 0 ) {
			$this->_assessment->addNewAspect();
		}
		return $this->_assessment->getProperty( 'id' );
	}
	
	function copyAss()
	{
		if( !isset($this->_assessment) ) {
			return false;
		}
		$oldId = $this->_assessment->getProperty( 'id' );
		$newId = $this->setAss( $this->fAss->copy( $oldId ) );
		return ($newId != $oldId);
	}
	
	/**
	 * Retrieves the current assessment object
	 * 
	 * @return object  The requested assessment object
	 */
	function &getAss()
	{
		return $this->_assessment;
	}
	
	/**
	 * Sets the property lookups to use when retrieving assessment / aspect details
	 * @param $properties
	 */
	function setAssProps( $properties )
	{
		$this->_properties = $properties;
	}
	
	/**
	 * Retrieves the details for a given assessment including its aspects
	 * 
	 * @return array $assDetails The array of assessment/aspect object properties
	 */
	function getAssDetails()
	{
		$aspects = $this->_assessment->getAspects();
		
		foreach( $this->_properties['assessment'] as $assProp ) {
			$assDetails['assessment'][$assProp] = $this->_assessment->getProperty( $assProp );
		}
		
		foreach( $aspects as $aspId=>$aspect ) {
			foreach( $this->_properties['aspect'] as $aspProp ) {
				$assDetails['aspects'][$aspId][$aspProp] = $aspect->getProperty( $aspProp );
			}
		}
		
		return $assDetails;
	}
	
	function getAssGroupIds()
	{
		return $this->_assessment->getGroupIds();
	}
	
	function setAspects( $aspIds )
	{
		if( !isset($this->_aspects) || ($this->_aspects != $aspIds) ) {
			$this->_aspects = $aspIds;
		}
		
		return $this->_aspects;
	}
	
	function getAspects()
	{
		$retVal = array();
		foreach( $this->_aspects as $aspId ) {
			$retVal[$aspId] = &$this->fAsp->getInstance( $aspId );
		}
		return $retVal;
	}
	
	
	/**
	 * Retrieves the access details for a given assessment
	 * 
	 * @return array $assAccess The array of assessment access details
	 */
	function getAssAccess()
	{
		return $this->_assessment->getAccess();
	}
	
	/**
	 * Retrieves the default aspect markstyle boundaries
	 * 
	 * @return array $defaults  Multi-dimensional array of default markstyle boundaries 
	 */
	function getDefaultBoundaries()
	{
		$db = &JFactory::getDBO();
		$query = 'SELECT *'
			."\n".'FROM '.$db->nameQuote( '#__apoth_sys_markstyles' )
			."\n".'ORDER BY '.$db->nameQuote( 'order' );
		$db->setQuery( $query );
		$data = $db->loadAssocList();
		
		foreach( $data as $row ) {
			$defaults['mark_values'][$row['style']][(string)$row['mark']] = $row['pc_equivalent'];
			$defaults['display_bounds'][$row['style']][(string)$row['mark']] = $row['pc_max'];
		}
		$defaults['mark_values']['comment'] = array();
		
		return $defaults;
	}
	
	/**
	 * Retrieves markstyle metadata
	 * 
	 * @return array $defaults  2-dimensional array of markstyle information 
	 */
	function getMarkstyleInfo()
	{
		$db = &JFactory::getDBO();
		$query = 'SELECT '.$db->nameQuote( 'style' ).', '.$db->nameQuote( 'label' ).', '.$db->nameQuote( 'type' )
			."\n".'FROM '.$db->nameQuote( '#__apoth_sys_markstyles_info' );
		$db->setQuery( $query );
		$data = $db->loadAssocList( 'style' );
		
		foreach( $data as $style=>$infoArray ) {
			unset($data[$style]['style']);
		}
		return $data;
	}
	
	/**
	 * Call the assessment to add a new apsect to itself
	 */
	function addNewAspect()
	{
		return $this->_assessment->addNewAspect();
	}
	
	function copyAspects( $aIds )
	{
		$retVal = 0;
		foreach( $aIds as $aId ) {
			$r = $this->_assessment->copyAspect( $aId );
			if( $r ) {
				$retVal++;
			}
		}
		return $retVal;
	}
	
	function removeAspects( $aIds )
	{
		$retVal = 0;
		foreach( $aIds as $aId ) {
			$r = $this->_assessment->removeAspect( $aId );
			if( $r ) {
				$retVal++;
			}
		}
		return $retVal;
	}
	
	function update( $data = null )
	{
		if( !is_null($data) ) {
			$this->_assessment->update( $data );
		}
		return true;
	}
	
	function save( $data = null )
	{
		if( !is_null($data) ) {
			$r = $this->update( $data );
			if( $r ) {
				$id = $this->_assessment->commit();
			}
		}
		return (( $data['id'] > 0 && $data['id'] == $id ) || ( $data['id'] < 0 && $id > 0 ) );
	}
}
?>