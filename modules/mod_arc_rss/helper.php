<?php
/**
 * @package     Arc
 * @subpackage  Module_RSS
 * @copyright   Copyright (C) 2005 Punnet. All rights reserved. See COPYRIGHT_ARC.txt
 * @license     http://www.gnu.org/licenses/gpl.html GNU/GPL. See LICENSE_ARC.txt
 * Arc is free software: you can redistribute it and/or modify
 * it under the terms of version 3 of the GNU General Public License
 * as published by the Free Software Foundation.
 * The disclaimer of warranty as stated in the GPL applies to this program
 */

defined( '_JEXEC' ) or die( 'Restricted access' );

class modArcFeedHelper
{
	/**
	 * Retrieves the feed URL from the module params
	 * @param object $params  instance of JParameter
	 * @return array $rssURLs  array of feed URLs indexed on module feed number
	 */
	function getURLs( $params )
	{
		$rssURLs = array();
		$paramsArray = $params->toArray();
		$i = 0;
		while( isset($paramsArray['rss_url_'.$i]) ) {
			$tmpURL = $params->get( 'rss_url_'.$i, '' );
			if( $tmpURL != '' ) {
				$rssURLs[$i] = $tmpURL;
			}
			$i++;
		}
		
		return $rssURLs;
	}
	
	/**
	 * Generates the 2-way associated individual feed objects and combined items array
	 * @param object $params  instance of JParameter
	 * @param array $rssURLs  array of feed URLs indexed on module feed number
	 * @return array $data  the 2 element array of feed objects indexed on feed number and items super array
	 */
	function getData( $params, $rssURLs )
	{
		// initialise the feed object and items holding arrays
		$feeds = array();
		$allItems = array();
		
		// set up cache options for creating the RSS object
		$options = array();
		if( $params->get('cache') ) {
			$options['cache_time'] = $params->get( 'cache_time', 15 );
			$options['cache_time'] *= 60;
		}
		else {
			$options['cache_time'] = null;
		}
		
		$j = 0;
		foreach( $rssURLs as $k=>$url ) {
			$options['rssUrl'] = $url;
			$rssDoc = &JFactory::getXMLparser( 'RSS', $options );
			$feeds[$k] = new stdClass();
			
			if( $rssDoc != false ) {
				// channel header and link
				$feeds[$k]->title = $rssDoc->get_title();
				$feeds[$k]->link = $rssDoc->get_link();
				$feeds[$k]->description = $rssDoc->get_description();
				
				// channel image if exists
				$feeds[$k]->image->url = $rssDoc->get_image_url();
				$feeds[$k]->image->title = $rssDoc->get_image_title();
				
				// favicon if exists
				$feeds[$k]->favicon = $rssDoc->get_favicon();
				
				// retrieve array of item objects limited to number required
				$items = array_slice( $rssDoc->get_items(), 0, $params->get('rss_items_'.$k, 5) );
				
				// sort items into super array indexed on compound key of
				// timestamp~uniquenumber~feednumber and pass cound key to $feeds
				foreach( $items as $item ) {
					$key = $item->data['date']['parsed'].'~'.$j++.'~'.$k;
					$allItems[$key] = $item;
					$feeds[$k]->items[] = $key;
				}
			}
			else {
				$feeds[$k] = false;
			}
		}
		
		// sort each feeds list of its own items into latest first date order
		foreach( $feeds as $k=>$feed ) {
			if( is_object($feeds[$k]) && isset($feeds[$k]->items) && is_array($feeds[$k]->items) ) {
				rsort( $feeds[$k]->items );
			}
		}
		
		// sort the items array into latest first date order
		krsort( $allItems );
		
		// store both arrays
		$data['feeds'] = $feeds;
		$data['allItems'] = $allItems;
		
		return $data;
	}
}
?>
