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

// add default CSS
$this->addPath = JURI::base().'components'.DS.'com_arc_homepage'.DS.'views'.DS.'homepage'.DS.'tmpl'.DS;
JHTML::stylesheet( 'default.css', $this->addPath );

JHTML::_('behavior.mootools');
JHTML::_('behavior.modal');

// id of logged in user
$u = &JFactory::getUser();
$userId = $u->person_id;

// id of profile we are viewing
$profileId = $this->profile->getId();
?>
<h1>Welcome to your Homepage</h1>

<div id="container">
	<div id="leftCol">
<?php
// load the panels
foreach( $this->model->getColPanels( 1 ) as $this->panel ) {
	if( $this->panel->getParam('shown') != '0' || ($userId != $profileId) ) {
		if( !is_null($this->panel->getJscript())) {
			$jScripts[] = $this->panel->getJscript();
		}
		echo $this->loadTemplate( 'panel' );
	}
}
?>
	</div>
	
	<div id="rightCol">
<?php
// load the panels
foreach( $this->model->getColPanels( 3 ) as $this->panel ) {
	if( $this->panel->getParam('shown') != '0' || ($userId != $profileId) ) {
		if( !is_null($this->panel->getJscript())) {
			$jScripts[] = $this->panel->getJscript();
		}
		echo $this->loadTemplate( 'panel' );
	}
}
?>
	</div>
	
	<div id="center">
<?php
// load the panels
foreach( $this->model->getColPanels( 2 ) as $this->panel ) {
	if( $this->panel->getParam('shown') != '0' || ($userId != $profileId) ) {
		echo $this->loadTemplate( 'panel' );
	}
}
?>
	</div>
</div>