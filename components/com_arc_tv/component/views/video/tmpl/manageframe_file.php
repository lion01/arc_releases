<form id="upload_form" method="post" action="<?php echo $this->curVideo->getUpload(); ?>" onsubmit="return window.parent.startFileUpload();" enctype="multipart/form-data">
	<input type="hidden" name="UPLOAD_IDENTIFIER" id="uid" value="<?php echo uniqid(""); ?>">
	<label style="font-family:verdana !important; font-size:12px; color:#67655E; margin-left:-8px;" for="file_uploload_selector"><? echo ($this->curVideo->getId() < 0) ? 'Select a file' : 'Select a different file?'; ?></label>
	<input type="file" name="file_uploload_selector" style="background:#F8F8F8;" />
	<input type="hidden" name="vidId" id="vidId_file_input" value="<?php echo $this->curVideo->getId(); ?>" />
	<input type="submit" name="subBtn" value="Upload" />
</form>