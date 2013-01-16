<?php
/**
 * @package     Arc
 * @subpackage  People
 * @copyright   Copyright (C) 2005 Punnet. All rights reserved. See COPYRIGHT_ARC.txt
 * @license     http://www.gnu.org/licenses/gpl.html GNU/GPL. See LICENSE_ARC.txt
 * Arc is free software: you can redistribute it and/or modify
 * it under the terms of version 3 of the GNU General Public License
 * as published by the Free Software Foundation.
 * The disclaimer of warranty as stated in the GPL applies to this program
 */

defined( '_JEXEC' ) or die( 'Restricted access' );
?>
<style>

#links_edit_div {
	padding: 10px;
}

.title_centre {
	text-align: center;
}

.row {
	overflow: auto;
}

.label {
	float: left;
	display: block;
	width: 15em;
	text-align: right;
	margin-right: 5px;
}
.value {
	float: left;
}
</style>
<div id="links_edit_div">
	<h2 class="title_centre">Edit your links</h2>
	<form enctype="multipart/form-data" action="<?php echo $this->link; ?>&task=saveLinks&scope=<?php echo $this->scope; ?>&tmpl=component" method="post" name="edit_links">
	
	<h3>Current Links</h3>
	<div class="row">
		<div class="label">Remove?</div>
		<div class="value">Link</div>
	</div>
	<?php
	$pId = $this->profile->getId();
	foreach( $this->links as $k=>$v ) :
	?>
	<div class="row">
		<div class="label"><input type="checkbox" name="del_link[<?php echo $k; ?>]" /></div>
		<div class="value"><a href="<?php echo $v['url']; ?>" target="_blank"><?php echo $v['text']; ?></a></div>
	</div>
	
	<?php
	endforeach;
	
	$files = ApotheosisPeopleData::getFileLinkList( $pId );
	$fileSelect = '<select name="file">';
	$fileSelect .= '<option></option>';
	foreach( $files as $f ) {
		$f = htmlspecialchars($f);
		$fileSelect .= '<option value="'.$f.'">'.$f.'</option>';
	}
	$fileSelect .= '</select>';
	
	?>
	<h3>Add Link</h3>
	<div class="row">
		<div class="label">Add link: Title</div>
		<div class="value"><input name="link_text" type="text" /></div>
	</div>
	<div class="row">
		<div class="label">URL</div>
		<div class="value"><input name="link" type="text" value="http://" /></div>
	</div>
	
	<?php if( $this->files ) : ?>
	<div class="row">
		<div class="label"><br />OR<br /><br /></div>
		<div class="value"></div>
	</div>
	<div class="row">
		<div class="label">Add existing File</div>
		<div class="value"><?php echo $fileSelect; ?></div>
	</div>
	<div class="row">
		<div class="label"><br />OR<br /><br /></div>
		<div class="value"></div>
	</div>
	<div class="row">
		<div class="label">Add new File</div>
		<div class="value"><input name="new_file" type="file" /></div>
	</div>
	<?php endif; ?>
	
	<br />
	<div class="row">
		<div class="label">&nbsp;</div>
		<div class="value"><input type="submit" name="submit" value="Save" /></div>
	</div>
	
	</form>
</div>