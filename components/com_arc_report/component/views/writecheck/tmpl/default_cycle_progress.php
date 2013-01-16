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


?>
<div class="cycle">
<h2><?php echo htmlspecialchars( $this->cycle->getDatum( 'name' ) ); ?></h2>

<div class="written">
<h3>Written</h3>
<?php if( $this->written['total'] == 0 ) : ?>
	<p>Nothing to see here</p>
<?php else:
//	var_dump_pre( $this->written, 'written' );
	// prepare write link
	$link = ApotheosisLibAcl::getUserLinkAllowed( 'apoth_report_write_list', array( 'report.cycle'=>$this->cycle->getId() ) );
	
	// calculate percentages
	$pcIncomplete  = floor( ($this->written[ARC_REPORT_STATUS_INCOMPLETE] / $this->written['total']) * 100 );
	$pcSubmitted   = floor( ($this->written[ARC_REPORT_STATUS_SUBMITTED]  / $this->written['total']) * 100 );
	$pcRejected    = floor( ($this->written[ARC_REPORT_STATUS_REJECTED]   / $this->written['total']) * 100 );
	$pcNonexistent = 100 - ( $pcIncomplete + $pcSubmitted + $pcRejected );
	
	$countNonexistent = $this->written[ARC_REPORT_STATUS_NASCENT];
	
	if( $link ) {
		echo '<a href="'.$link.'">Go write</a>';
	}
	?>
	
	<table>
		<tr><td>Not started</td>        <td><?php echo $pcNonexistent; ?>%</td><td><?php echo $countNonexistent;            ?></td></tr>
		<tr><td>Draft</td>              <td><?php echo $pcIncomplete;  ?>%</td><td><?php echo $this->written[ARC_REPORT_STATUS_INCOMPLETE]; ?></td></tr>
		<tr><td>Submitted</td>          <td><?php echo $pcSubmitted;   ?>%</td><td><?php echo $this->written[ARC_REPORT_STATUS_SUBMITTED];  ?></td></tr>
		<tr><td>Require correction</td> <td><?php echo $pcRejected;    ?>%</td><td><?php echo $this->written[ARC_REPORT_STATUS_REJECTED];   ?></td></tr>
	</table>
<?php endif;?>
</div>

<div class="checked">
<h3>Checked</h3>
<?php if( $this->checked['total'] == 0 ) : ?>
	<p>Nothing to see here</p>
<?php else:
//	var_dump_pre( $this->checked, 'checked' );
	// prepare write link
	$link = ApotheosisLibAcl::getUserLinkAllowed( 'apoth_report_check_list', array( 'report.cycle'=>$this->cycle->getId() ) );
	
	// calculate percentages
	$pcSubmitted  = floor( ($this->checked[ARC_REPORT_STATUS_SUBMITTED] / $this->checked['total']) * 100 );
	$pcApproved   = floor( ($this->checked[ARC_REPORT_STATUS_APPROVED]  / $this->checked['total']) * 100 );
	$pcNonexistent = 100 - ( $pcSubmitted + $pcApproved );
	
	$countNonexistent = $this->checked['total'] - ( $this->checked[ARC_REPORT_STATUS_SUBMITTED] + $this->checked[ARC_REPORT_STATUS_APPROVED] );
	
	if( $link ) {
		echo '<a href="'.$link.'">Go check</a>';
	}
	?>
	
	<table>
		<tr><td>Awaiting submission</td> <td><?php echo $pcNonexistent; ?>%</td><td><?php echo $countNonexistent;           ?></td></tr>
		<tr><td>Not checked yet</td>     <td><?php echo $pcSubmitted;   ?>%</td><td><?php echo $this->checked[ARC_REPORT_STATUS_SUBMITTED]; ?></td></tr>
		<tr><td>Approved</td>            <td><?php echo $pcApproved;    ?>%</td><td><?php echo $this->checked[ARC_REPORT_STATUS_APPROVED];  ?></td></tr>
	</table>
<?php endif;?>
</div>

</div>
