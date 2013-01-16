<?php
/**
 * @package     Arc
 * @subpackage  Report
 * @copyright   Copyright (C) 2005 Punnet. All rights reserved. See COPYRIGHT_ARC.txt
 * @license     http://www.gnu.org/licenses/gpl.html GNU/GPL. See LICENSE_ARC.txt
 * Arc is free software: you can redistribute it and/or modify
 * it under the terms of version 3 of the GNU General Public License
 * as published by the Free Software Foundation.
 * The disclaimer of warranty as stated in the GPL applies to this program
 */

defined( '_JEXEC' ) or die( 'Restricted access' );

JHTML::stylesheet( 'printshare.css', $this->scriptPath );
JHTML::script( 'printshare.js', $this->scriptPath, true );

$this->nav->displayNav();
?>
<div id="arc_main_narrow">
<?php $this->nav->displayBreadcrumbs(); ?>

<div id="print_content">

<div id="print_selectors">
<div id="print_filters_wrap">
<h2>Something something...</h2>
<div id="print_filters">
</div>
</div>

<div id="print_summary_wrap">
<h2>Report Print / Share Summary...</h2>
<div id="print_summary">
<ul>
<li>Cycle</li>
	<ul>
	<li>first</li>
	</ul>
<li>Sections</li>
	<ul>
	<li>Headline data</li>
	<li>Subreports</li>
	</ul>
</ul>
</div>
No. of pages: 123
</div>

</div>

<div id="print_buttons">
<?php if( $link = ApotheosisLibAcl::getUserLinkAllowed( 'apoth_report_print_preview', array( 'view'=>'printshare', 'format'=>'apothpdf', 'task'=>'preview' ) ) ) : ?>
	<ul><li><a href="<?php echo $link; ?>"><span id="print_btn_preview">Preview</span></a></ul>
<?php endif; ?>
<?php if( $link = ApotheosisLibAcl::getUserLinkAllowed( 'apoth_report_print_save', array( 'view'=>'printshare', 'format'=>'raw', 'task'=>'save' ) ) ) : ?>
	<ul><li><a href="<?php echo $link; ?>"><span id="print_btn_save">Save</span></a></ul>
<?php endif; ?>
</div>

</div>

</div>