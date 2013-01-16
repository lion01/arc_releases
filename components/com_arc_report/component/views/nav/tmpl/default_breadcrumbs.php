<?php
JHTML::script( 'breadcrumbs.js', $this->scriptPath, true );
JHTML::stylesheet( 'breadcrumbs.css', $this->scriptPath );
echo JHTML::_( 'arc.breadcrumbs', ARC_REPORT_CRUMB_TRAIL );
?>
<input type="hidden" id="breadcrumb_url" value="<?php echo ApotheosisLibAcl::getUserLinkAllowed( 'apoth_report_nav_elements', array( 'report.navelement'=>'breadcrumbs', 'report.params'=>'', 'report.format'=>'raw' ) ); ?>" />