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

class ApotheosisData_Homepage extends ApotheosisData
{
	function info()
	{
		return 'Homepage component installed';
	}
	
	/**
	 * Provides the title of a homepage panel given the ID
	 * 
	 * @param int $id  The ID of the panel whose title is requested
	 * @return string $  The panel title
	 */
	function panelTitle( $id )
	{
		static $panels = array();
		$db = JFactory::getDBO();
		
		if( empty($panels) ) {
			$query = 'SELECT '.$db->nameQuote('id').', '.$db->nameQuote('alt')
				."\n".'FROM '.$db->nameQuote('#__apoth_home_panels');
			$db->setQuery( $query );
			
			$panels = $db->loadAssocList('id');
		}
		
		return $panels[$id]['alt'];
	}
	
	/**
	 * Provides the title of a homepage panel given a URL snippet
	 * 
	 * @param string $urlSnippet  The url snippet of the panel whose title is requested
	 * @return string $  The panel title
	 */
	function panelTitleByUrl( $urlSnippet )
	{
		$db = JFactory::getDBO();
		
			$query = 'SELECT '.$db->nameQuote('alt')
				."\n".'FROM '.$db->nameQuote('#__apoth_home_panels')
				."\n".'WHERE '.$db->nameQuote('url').' LIKE '.$db->Quote('%'.$urlSnippet.'%');
			$db->setQuery( $query );
			
			return $db->loadResult();
	}
	
	/**
	 * Provides a list of all defined panel IDs
	 * 
	 * @return array  The list of defined panel IDs
	 */
	function panelIds()
	{
		$db = JFactory::getDBO();
		
		$query = 'SELECT '.$db->nameQuote('id')
			."\n".'FROM '.$db->nameQuote('#__apoth_home_panels');
		$db->setQuery( $query );
		
		return $db->loadResultArray();
	}
	
	/**
	 * The number of columns on the homepage panel
	 * *** For now just a fixed placeholder
	 * 
	 * @return int  The number of homepage panel columns
	 */
	function panelColsCount()
	{
		return 3;
	}
	
	/**
	 * Information about given homepage links (assoc list of database rows)
	 * 
	 * @return array $linkInfo  Associated array of link information
	 */
	function linkInfo()
	{
		$db = JFactory::getDBO();
		
		$query = 'SELECT *'
			."\n".'FROM '.$db->nameQuote('#__apoth_home_links');
		$db->setQuery( $query );
		
		$linkInfo = $db->loadAssocList( 'id' );
		
		return $linkInfo;
	}
	
	/**
	 * Provides a list of all defined links
	 * 
	 * @param string $panel  Which panel we want the links for
	 * @return array  The list of defined link IDs
	 */
	function linkIds( $panel )
	{
		$db = JFactory::getDBO();
		
		$query = 'SELECT '.$db->nameQuote('id')
			."\n".'FROM '.$db->nameQuote('#__apoth_home_links')
			."\n".'WHERE '.$db->nameQuote('panel').' = '.$db->Quote($panel);
		$db->setQuery( $query );
		
		return $db->loadResultArray();
	}
	
	/**
	 * The names of the panels that can contain links
	 * *** For now just a fixed placeholder
	 * 
	 * @return array  The names of links containing panels
	 */
	function linkPanelNames()
	{
		return array( 'links', 'showcase' );
	}
	
	/**
	 * Get a list of panels ids whose visibility/position etc must never be customisable
	 * 
	 * @return array  Array of panels ids
	 */
	function fixedPanels()
	{
		$db = &JFactory::getDBO();
		
		$query = 'SELECT '.$db->nameQuote( 'id' )
			."\n".'FROM '.$db->nameQuote( '#__apoth_home_panels' )
			."\n".'WHERE '.$db->nameQuote( 'customisable' ).' = '.$db->Quote( 0 );
		$db->setQuery( $query );
		
		return $db->loadResultArray();
	}
	
	/**
	 * Provides a list of homepage panel names from a list of panel IDs
	 * 
	 * @param array $panelIds  Array of panel IDS whose name we want
	 * @return array  Array of panel IDs and names indexed on panel ID
	 */
	function panelNames( $panelIds )
	{
		$db = JFactory::getDBO();
		
		foreach( $panelIds as $k=>$panelId ) {
			$panelIds[$k] = $db->Quote( $panelId );
		}
		$panelIds = implode( ',', $panelIds );
		
		$query = 'SELECT '.$db->nameQuote( 'id' ).', '.$db->nameQuote( 'alt' )
			."\n".'FROM '.$db->nameQuote( '#__apoth_home_panels' )
			."\n".'WHERE '.$db->nameQuote( 'id' ).' IN ('.$panelIds.')';
		$db->setQuery( $query );
		
		return $db->loadAssocList( 'id' );
	}
}
?>