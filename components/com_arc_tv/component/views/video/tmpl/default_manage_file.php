<?php
/**
 * @package     Arc
 * @subpackage  TV
 * @copyright   Copyright (C) 2005 Punnet. All rights reserved. See COPYRIGHT_ARC.txt
 * @license     http://www.gnu.org/licenses/gpl.html GNU/GPL. See LICENSE_ARC.txt
 * Arc is free software: you can redistribute it and/or modify
 * it under the terms of version 3 of the GNU General Public License
 * as published by the Free Software Foundation.
 * The disclaimer of warranty as stated in the GPL applies to this program
 */

defined( '_JEXEC' ) or die( 'Restricted access' );

JHTML::_( 'Arc.tip' );
?>
<div id="manage_file_div">
	<?php
		// set default div display property
		$uploadShow = false;
		$formatsShow = false;
		$availRes = implode( ', ', array_keys($this->curVideo->getFormats()) );
		
		// determine relevant links
		$uploadLink = ApotheosisLibAcl::getUserLinkAllowed( 'arc_tv_file_form', array( 'tv.videoId'=>$this->curVideo->getId() ) );
		$delLink    = ApotheosisLibAcl::getUserLinkAllowed( 'arc_tv_file_delete', array( 'tv.videoId'=>$this->curVideo->getId() ) );
		
		// show the upload link and progress bar if no video already uploaded
		if( $availRes == '' ) {
			$uploadShow = (bool)$uploadLink;
		}
		// otherwise show the available resolutions
		else {
			$formatsShow = true;
		}
	?>
	<div id="manage_file_upload_div" <?php echo $uploadShow ? '' : 'style="display: none;"'; ?>>
		<?php echo JHTML::_( 'arc.hidden', 'progress_url', $this->curVideo->getProgress(), 'id="progress_url"' ); ?>
		<iframe id="upload_frame" name="uploadFrame" src="<?php echo $uploadLink; ?>" <?php echo ( $this->curVideo->getId() < 0 ) ? 'style="display: none;"' : ''; ?>></iframe>
		<div id="upload_bar">
			<div id="upload_progress"></div>
			<div id="upload_copy_progress"></div>
			<div id="upload_text"><span id="upload_text_inner">Connecting...</span></div>
		</div>
	</div>
	<div id="manage_file_formats_div" <?php echo $formatsShow ? '' : 'style="display: none;"'; ?>>
		Available resolutions: <?php echo $availRes; ?>
		<?php if( $delLink ) : ?>
		<a id="remove_vid_files_link" class="btn" href="<?php echo $delLink; ?>">Remove Video Files</a>
		<?php endif; ?>
	</div>
	<input id="manage_form_save_button" style="margin-top: 10px;" type="submit" value="Save Draft" name="task" class="arcTip" title="Save the video for later" />
	<input id="manage_form_submit_button" style="margin-top: 10px;" type="submit" value="Submit" name="task" class="arcTip" title="Submit the video for moderation" />
	<?php echo $this->loadTemplate( 'manage_ajax_spinners' ); ?>
</div>