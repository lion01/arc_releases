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

// Page header
$user = ApotheosisLib::getUser();
$name = ApotheosisData::_( 'people.displayName', $user->person_id, 'teacher' );
$date = ApotheosisLibParent::arcDateTime();
ob_start();
?>
<table width="100%" cellpadding="2" border="0">
	<tr>
		<td width="167"><h2><?php echo $name; ?></h2></td>
		<td width="170" align="center"><h2>Behaviour Reports</h2></td>
		<td width="167" align="right"><h2><?php echo $date; ?></h2></td>
	</tr>
</table>
<?php
$header = ob_get_clean();
$this->pdf->writeHtml( $header );

// Main graph
$this->mainWidth = 504;
$this->mainHeight = 360;
$this->seriesHeight = 40;
$this->seriesBlobsHeight = 40;
$graphLink = $this->_getGraphLink( $this->seriesIds, $this->mainWidth, $this->mainHeight, 0, true );
$this->pdf->image( $graphLink, '', '', ($this->mainWidth / $this->scaleFactor), 0, '', '', 'N', true );
echo $this->loadTemplate( 'graph' );
unlink( $graphLink );

// Loop through each series and render
$this->firstSeries = true;
$this->safetyLimit = 100;
$this->threadCount = 0;
$this->limitReached = false;
foreach( $this->seriesIds as $this->sId ) {
	$this->data = $this->report->getParsedSeries( $this->sId );
	echo $this->loadTemplate( 'messages' );
	if( $this->limitReached ) {
		echo $this->loadTemplate( 'limit_reached' );
		break;
	}
}
?>