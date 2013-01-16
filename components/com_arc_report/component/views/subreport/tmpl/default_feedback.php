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

$linkRejectSave = ApotheosisLibAcl::getUserLinkAllowed( 'apoth_report_ajax_feedback_save', array( 'report.subreport'=>$this->subreport->getId() ) );
?>
<style>
body {
	padding: 10px 5% 10px 5%;
}
h1 {
	font-size: 1.5em;
}
textarea {
	width: 100%;
	height: 100px;
	margin-bottom: 10px;
}
</style>

<h1>Give feedback</h1>
<p>Explain to the author of the report why you are rejecting it, and what they need to do to ensure its approval upon re-submission.</p>
<form method="post" action="<?php echo $linkRejectSave ?>">
<textarea name="comment"></textarea>
<input type="submit" value="Send Feedback" />
</form>