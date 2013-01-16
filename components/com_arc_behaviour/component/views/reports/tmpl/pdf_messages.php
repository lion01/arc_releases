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

$showMsg = ApotheosisLibAcl::getUserLinkAllowed( 'apoth_msg_hub_inter' ); /* *** this should probably be more granular */
if( $showMsg ) {
	$haveMessages = false;
	foreach( $this->data as $date=>$datum ) {
		if( isset($datum['messages']) && is_array($datum['messages']) && !empty($datum['messages']) ) {
			$haveMessages = true;
			foreach( $datum['messages'] as $id ) {
				$msgIds[] = $id;
			}
		}
	}
	
	if( $haveMessages ) {
		$this->pdf->addPage();
		
		// Series header
		ob_start();
		?>
		<table>
			<tr>
				<td><h3><?php echo $this->data['_meta']['label']; ?></h3></td>
			</tr>
		</table>
		<?php
		$seriesHeader = ob_get_clean();
		
		// Threads
		$threads = array_unique( ApotheosisData::_('message.threads', $msgIds) );
		
		$this->pdf->writeHtml( $seriesHeader );
		$graphLink = $this->_getGraphLink( array($this->sId), $this->mainWidth, $this->seriesHeight, $this->seriesBlobsHeight, false );
		$this->pdf->image( $graphLink, '', '', ($this->mainWidth / $this->scaleFactor), 0, '', '', 'N', true, 300 );
		unlink( $graphLink );
		$this->pdf->writeHtml( '<br />', false );
		
		foreach( $threads as $threadId ) {
			$thread = JHTML::_( 'arc_message.render', 'renderThreadPdf', 'thread', $threadId );
			$this->doc->pdfWriteHtmlWithPageBreak( $thread );
			
			if( ++$this->threadCount >= $this->safetyLimit ) {
				$this->limitReached = true;
				break;
			}
		}
	}
}
?>