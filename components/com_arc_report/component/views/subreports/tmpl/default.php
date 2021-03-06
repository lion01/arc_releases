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

JHTML::stylesheet( 'subreports.css', $this->scriptPath );
JHTML::stylesheet( 'subreport.css', $this->scriptPathSubreport );

JHTML::script( 'progressive_load.js', $this->scriptPath, true );
JHTML::script( 'vivify.js', $this->scriptPath, true );
JHTML::script( 'vivify_subreport.js', $this->scriptPathSubreport, true );
JHTML::script( 'vivify_controlset.js', $this->scriptPathSubreport, true );

$this->nav->displayNav();
?>

<div id="arc_main_narrow">

<?php
$this->nav->displayBreadcrumbs();
$this->nav->displayFilters();
echo JHTML::_( 'arc.hidden', 'ajax_page_url',   ApotheosisLibAcl::getUserLinkAllowed( 'apoth_report_list_ajax_page', array( 'report.listpage'=>0 ) ), 'id="ajax_page_url"' )."\r\n";
echo JHTML::_( 'arc.hidden', 'ajax_single_url', ApotheosisLibAcl::getUserLinkAllowed( 'apoth_report_list_ajax_single', array( 'report.subreport'=>'~SUBREPORT~' ) ), 'id="ajax_single_url"' )."\r\n";
echo JHTML::_( 'arc.hidden', 'ajax_more_url',   ApotheosisLibAcl::getUserLinkAllowed( 'apoth_report_ajax_more', array( 'report.subreport'=>'~SUBREPORT~' ) ), 'id="ajax_more_url"' )."\r\n";
?>

<h2><?php echo $this->cycle->getDatum( 'name' ); ?></h2>


<div id="subreports">
<div id="list_container"></div>
<div id="list_footer">All loaded</div>
<div id="list_error">Oops... Something went wrong when loading your data.<br />Please refresh the page to try again.</div>
<div id="list_loader"><?php echo JHTML::_( 'arc.loading' ); ?></div>
</div>

</div>