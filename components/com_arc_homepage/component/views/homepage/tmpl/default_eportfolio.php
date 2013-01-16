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
?>
<h1>ePortfolio for <?php echo $this->profile->getDisplayName(); ?></h1>
<div id="container">
	<div id="leftCol">
		<div id="summary">
<?php
// load the links panel
if( !is_null($this->panel = $this->model->getPanel(1)) ) {
	echo $this->loadTemplate( 'panel' );
}
?>
		</div>
		<div id="showcase">
<?php
// load the showcase panel
if( !is_null($this->panel = $this->model->getPanel(2)) ) {
	echo $this->loadTemplate( 'panel' );
}
?>
		</div>
	</div>
	
	<div id="rightCol">
<?php
// load the active questions panel
if( !is_null($this->panel = $this->model->getPanel(10)) ) {
	echo $this->loadTemplate( 'panel' );
}
?>
<?php
// load the SEN profile panel
if( !is_null($this->panel = $this->model->getPanel(11)) ) {
	echo $this->loadTemplate( 'panel' );
}
?>
	</div>
	
	<div id="center">
		<div id="about">
<?php
// load the profile "about" panel
if( !is_null($this->panel = $this->model->getPanel(20)) ) {
	echo $this->loadTemplate( 'panel' );
}
?>
		</div>
		<div id="blog">
<?php
// load the blog panel
if( !is_null($this->panel = $this->model->getPanel(21)) ) {
	echo $this->loadTemplate( 'panel' );
}
?>
		</div>
	</div>
</div>
<br />