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
?>
<style>
.feed {
	
}
.feed hr {
	width: 75%;
}
.feed_body {
	
}
.feed_image {
	float: right;
}
.feed_title_desc {
	margin-bottom: 3px;
}
.item_body {
	clear: both;
}
.item {
	margin-bottom: 3px;
}
.favicon {
	width: 20px;
	float: left;
	text-align: center !important; 
}
.favicon img {
	width: 16px;
}
.item_link {
	margin-left: 20px;
}
.item_link_text {
	font-weight: bold;
}
.item_data {
	margin-left: 30px;
}
</style>
<?php
// add module specific mooTools slider javascript
$doc = &JFactory::getDocument();
$doc->addScript( 'modules'.DS.'mod_arc_rss'.DS.'tmpl'.DS.'mod_arc_rss_slider.js' );

// assign common parameters
$blockView    = ( ($params->get('block_view') == 1) ? true : false );
$showSummary  = ( ($params->get('rss_item_summary') == 1) ? true : false );
$summaryWords =    $params->get('word_count');
$modSuffix = $params->get( 'moduleclass_sfx' );

// determine which template to display
if( $blockView ) {
	include ( JModuleHelper::getLayoutPath('mod_arc_rss', 'default_block') );
}
else {
	include( JModuleHelper::getLayoutPath('mod_arc_rss', 'default_list') );
}
?>