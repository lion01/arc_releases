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

// first some scripts
JHTML::_('behavior.mootools'); 
JHTML::_('behavior.tooltip');
JHTML::script( 'markbook_scripts.js', JURI::base().'components'.DS.'com_arc_assessment'.DS.'views'.DS.'markbook'.DS.'tmpl'.DS );
JHTML::stylesheet( 'markbook.css', JURI::base().'components'.DS.'com_arc_assessment'.DS.'views'.DS.'markbook'.DS.'tmpl'.DS );
?>

<h3>Markbook</h3>
<!-- Search form -->
<?php echo $this->loadTemplate('search'); ?>

<hr />

<!-- Export controls -->
<?php
if( ($l = ApotheosisLibAcl::getUserLinkAllowed( 'ass_export', array( 'format'=>'raw' ) )) !== false ) {
	echo '<a href="'.$l.'">Get as csv</a>';
}
?>

<!-- Data (if any to display) -->
<?php if( !empty($this->rows) ) : ?>
<div>
<form action="<?php echo ApotheosisLib::getActionLinkByName( 'apoth_ass_main' ); ?>" method="post" name="markbook">
<?php
echo ApotheosisLib::arcDataStart( '20em' );
echo $this->loadTemplate( 'data_left' );
echo ApotheosisLib::arcDataMiddle();
echo $this->loadTemplate( 'data_main' );
echo ApotheosisLib::arcDataEnd();
?>
</form>
</div>
<?php endif; ?>
