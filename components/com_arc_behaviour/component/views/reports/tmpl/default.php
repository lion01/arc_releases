<?php
/**
 * @package     Arc
 * @subpackage  Behaviour
 * @copyright   Copyright (C) 2005 Punnet. All rights reserved. See COPYRIGHT_ARC.txt
 * @license     http://www.gnu.org/licenses/gpl.html GNU/GPL. See LICENSE_ARC.txt
 * Arc is free software: you can redistribute it and/or modify
 * it under the terms of version 3 of the GNU General Public License
 * as published by the Free Software Foundation.
 * The disclaimer of warranty as stated in the GPL applies to this program
 */

defined( '_JEXEC' ) or die( 'Restricted access' );

echo $this->loadTemplate( 'overrides' );

// add javascript
$this->extraPath = JURI::base().'components'.DS.'com_arc_behaviour'.DS.'views'.DS.'reports'.DS.'tmpl'.DS;
JHTML::script( 'default.js', $this->extraPath );

// and css
// JHTML::stylesheet( 'default.css', $this->extraPath );

// add tooltips behaviour
JHTML::_('behavior.tooltip');
?>
<h3 id="arc_title">Behaviour Reports</h3>

<div id="arc_main">
<?php
echo $this->loadTemplate( 'search' );

echo '<hr />';
if( is_null($this->seriesIds) ) {
	echo 'Use the search form to get the behaviour report.<br />';
	echo 'Limit your search by filling in as many of the fields above as you need.<br />';
	echo 'Mouseover any of the labels for more information.';
}
elseif( is_array($this->seriesIds) && !empty($this->seriesIds) ) {
	if( ($link = ApotheosisLibAcl::getUserLinkAllowed('apoth_be_reports_pdf', array())) != false ) {
		echo '<a id="pdf_link" name="pdf_link" href="'.$link.'">Get as pdf</a>';
	}
	echo '<input type="hidden" id="threadListUrl" name="threadListUrl" value="'.ApotheosisLib::getActionLinkByName( 'apoth_msg_hub_ajax',    array('message.tasks'=>'search') ).'">'."\n";
	echo '<input type="hidden" id="threadUrl"     name="threadUrl"     value="'.ApotheosisLib::getActionLinkByName( 'apoth_msg_thread_ajax', array('message.tasks'=>'toggleThread') ).'">'."\n";
	echo '<input type="hidden" id="loadImg"       name="loadImg"       value="'.ApotheosisLib::arcLoadImgUrl()."\n";
	echo $this->loadTemplate( 'graph' );
	
	foreach( $this->seriesIds as $this->sId ) {
		$this->data = $this->report->getParsedSeries( $this->sId );
		echo $this->loadTemplate( 'messages' );
	}
}
elseif( is_array($this->seriesIds) && empty($this->seriesIds) ) {
	global $mainframe;
	$mainframe->enqueueMessage( 'No results. Please widen your search.', 'notice' );
	echo 'The search returned no results.<br />';
	echo 'Please widen your search for example by selecting more students, less specific classes, or a longer date range.<br />';
}
elseif( !$this->seriesIds ) {
	global $mainframe;
	$mainframe->enqueueMessage( 'Too many results. Please narrow your search.', 'notice' );
	echo 'The search returned a LOT of individual scores. Far too many in fact to handle all at once.<br />';
	echo 'Please narrow your search for example by selecting fewer students, specific classes, a shorter date range or a subset.<br />';
	echo 'NOTE: If you do not specify ANY students you will in fact search ALL students.';
}
?>
</div>