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

$id = $this->subreport->getId();
$status = $this->subreport->getDatum( 'status_id' );

if( $status == ARC_REPORT_STATUS_SUBMITTED ) {
	$linkApprove  = ApotheosisLibAcl::getUserLinkAllowed( 'apoth_report_ajax_save', array( 'report.subreport'=>$id, 'report.commit'=>1, 'report.status'=>ARC_REPORT_STATUS_APPROVED ) );
	$linkReject   = ApotheosisLibAcl::getUserLinkAllowed( 'apoth_report_ajax_feedback', array( 'report.subreport'=>$id ) );
}
$linkSave     = ApotheosisLibAcl::getUserLinkAllowed( 'apoth_report_ajax_save', array( 'report.subreport'=>$id, 'report.commit'=>1, 'report.status'=>ARC_REPORT_STATUS_INCOMPLETE ) );
$linkSubmit   = ApotheosisLibAcl::getUserLinkAllowed( 'apoth_report_ajax_save', array( 'report.subreport'=>$id, 'report.commit'=>1, 'report.status'=>ARC_REPORT_STATUS_SUBMITTED ) );
$linkPreview  = ApotheosisLibAcl::getUserLinkAllowed( 'apoth_report_ajax_save', array( 'report.subreport'=>$id, 'report.commit'=>0, 'report.status'=>'' ) );
$linkPreview2 = ApotheosisLibAcl::getUserLinkAllowed( 'apoth_report_print_preview', array( 'report.subreport'=>$id ) );

if( ApotheosisLib::getActionId() != ApotheosisLib::getActionIdByName( 'apoth_report_'.$this->get( 'Activity' ).'_subreport' ) ) {
	$linkSingle   = ApotheosisLibAcl::getUserLinkAllowed( 'apoth_report_'.$this->get( 'Activity' ).'_subreport', array( 'report.subreport'=>$id ) );
}

?>
<form id="sub_<?php echo $id; ?>_form" method="post" action="#">
<div id="sub_<?php echo $id; ?>" class="subreport">

<?php echo $this->subreport->render( 'brief', 'HTML' ); ?>

<div class="subreport_status">
<?php
switch( $status ) {
case( ARC_REPORT_STATUS_NASCENT ):
default:
	echo JHTML::_( 'arc.dot', 'clear', 'Not started' );
	break;

case( ARC_REPORT_STATUS_INCOMPLETE ):
	echo JHTML::_( 'arc.dot', 'amber', 'More attention needed' );
	break;

case( ARC_REPORT_STATUS_SUBMITTED ):
	echo JHTML::_( 'arc.dot', 'green', 'Submitted for review' );
	break;

case( ARC_REPORT_STATUS_REJECTED ):
	$fl = $this->subreport->getFeedback();
	$txt = (is_null( $fl ) ? 'unknown' : htmlspecialchars( $fl[0]['comment'] ) );
	echo JHTML::_( 'arc.dot', 'amber', 'More attention needed because...' );
	echo JHTML::_( 'arc.dot', 'red', '...'.$txt );
	break;

case( ARC_REPORT_STATUS_APPROVED ):
	echo JHTML::_( 'arc.dot', 'green', 'Submitted...' );
	echo JHTML::_( 'arc.dot', 'green', '... and approved' );
	break;

}
?>
</div>

<div class="subreport_controls">
	<?php if( $linkApprove ) : ?><a class="btn control"        href="<?php echo $linkApprove; ?>">Approve</a><?php endif; ?>
	<?php if( $linkReject  ) : ?><a class="btn control reject" href="<?php echo $linkReject ; ?>" target="blank" rel="{handler: 'iframe', size: {x: 640, y: 300}}">Reject</a><?php endif; ?>
	<?php if( $linkSave    ) : ?><a class="btn control"        href="<?php echo $linkSave   ; ?>">Save Draft</a><?php endif; ?>
	<?php if( $linkSubmit  ) : ?><a class="btn control"        href="<?php echo $linkSubmit ; ?>">Submit</a><?php endif; ?>
	<?php if( $linkPreview && $linkPreview2 ) : ?><a class="btn control preview" href="<?php echo $linkPreview; ?>" target="blank" rel="{handler: 'iframe', size: {x: 640, y: 480}}" >Preview</a> <input type="hidden" value="<?php echo $linkPreview2; ?>" />	<?php endif; ?>
	<?php if( $linkSingle  ) : ?><a class="btn"                href="<?php echo $linkSingle ; ?>">Focus</a><?php endif; ?>
</div>

</div>
</form>