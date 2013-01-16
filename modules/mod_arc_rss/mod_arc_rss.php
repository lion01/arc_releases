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

// Include the RSS Reader helper file
require_once( JPATH_SITE.DS.'modules'.DS.'mod_arc_rss'.DS.'helper.php' );

// Get an array of feed URLs...
$rssURLs = modArcFeedHelper::getURLs( $params );

// ... and check if any feed URLs have actually been set
if ( empty($rssURLs) ) {
	echo '<div>';
	echo JText::_( 'No feed URLs have been specified.' );
	echo '</div>';
	return;
}

// Check if cache diretory is writable as cache files may be created for the feed
$cacheDir = JPATH_BASE.DS.'cache';
if ( !is_writable($cacheDir) ) {
	echo '<div>';
	echo JText::_( 'Please make cache directory writable.' );
	echo '</div>';
	return;
}

// Create feed objects from the module params and supplied URLs
$data = modArcFeedHelper::getData( $params, $rssURLs );

// If we got back good feed data then display it
if ( !empty($data['feeds']) ) {
	require( JModuleHelper::getLayoutPath('mod_arc_rss') );
}
// If feed data was bad then stop
else {
	echo '<div>';
	echo JText::_( 'None of the feed URLs returned RSS items.' );
	echo '</div>';
}
?>