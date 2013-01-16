<?php
/**
 * @package     Arc
 * @subpackage  message
 * @copyright   Copyright (C) 2005 Punnet. All rights reserved. See COPYRIGHT_ARC.txt
 * @license     http://www.gnu.org/licenses/gpl.html GNU/GPL. See LICENSE_ARC.txt
 * Arc is free software: you can redistribute it and/or modify
 * it under the terms of version 3 of the GNU General Public License
 * as published by the Free Software Foundation.
 * The disclaimer of warranty as stated in the GPL applies to this program
 */

defined( '_JEXEC' ) or die( 'Restricted access' );

$user = ApotheosisLib::getUser();
$name = ApotheosisData::_( 'people.displayName', $user->person_id, 'teacher' );
$date = ApotheosisLibParent::arcDateTime();
ob_start();
?>
<table width="100%" cellpadding="2" border="0">
	<tr>
		<td width="167"><h2><?php echo $name; ?></h2></td>
		<td width="166" align="center"><h2>Messages</h2></td>
		<td width="167" align="right"><h2><?php echo $date; ?></h2></td>
	</tr>
</table>
<?php
// Page header
$header = ob_get_clean();
$this->pdf->writeHtml( $header );

// Loop through the threads and output them
$safetyLimit = 50;
$safety = 1;
while( $this->thread = &$this->get('PdfThread') ) {
	$thread = JHTML::_( 'arc_message.render', 'renderThreadPdf', 'thread', $this->thread->getId() );
	$this->doc->pdfWriteHtmlWithPageBreak( $thread );
	if( $safety++ >= $safetyLimit ) {
		break;
	}
}
?>