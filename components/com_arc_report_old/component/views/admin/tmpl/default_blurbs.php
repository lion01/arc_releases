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

JHTML::_('behavior.modal'); ?>
<script>
<?php if( (JRequest::getVar('preview') == 'true') && ($this->group == JRequest::getVar('focus')) ): ?>
	window.addEvent('domready', function() {
		var pl = $('previewLink_<?php echo $this->group; ?>');
		try {
			pl.fireEvent('click', pl);
		}
		catch(e) {}
	});
<?php endif; ?>
	window.addEvent('domready', function() {
		try {
			$('previewLink_<?php echo $this->group; ?>').style.display = 'none';
		}
		catch(e) {}
	});
</script>

<h3><?php echo $this->groupName.($this->enabled ? ' - Change printed subject name and introduction text' : ' - Pre-set texts'); ?></h3>

<form method="post" action="<?php echo $this->link; ?>">
<?php
$subj = $this->report->getSubjectField();
echo $subj->dataHtml( true );

foreach( $this->fields as $v ) {
	$dataHtml = preg_replace('~%(?!\d+\$\w)~', '%%', $v->dataHtml( $this->enabled ));
	$widthHtml = ($v->hasSuffix() ? '98%' : '88%');
	$heightHtml = $v->getHtmlHeight();
	echo '<div style="width: 100%; overflow: hidden;">';
	echo $v->titleHtml()."\n".sprintf( $dataHtml, $widthHtml, $heightHtml )."\n";
	echo '</div>';
} ?>
<br />
<?php if( $this->enabled ) : ?>
<?php $link = Apotheosislib::getActionLinkByName( 'apoth_report_generate_blurb_preview', array('report.groups'=>$this->get('CycleId').'_'.$this->group, 'report.reports'=>'NULL') ); ?>
	<a class="modal" id="previewLink_<?php echo $this->group; ?>" name="previewLink_<?php echo $this->group; ?>" style="display: inline;" href="<?php echo $link; ?>" rel="{handler: 'iframe', size: {x: 700, y: 500}}" style="color: black; text-decoration: none;" target="_blank">Preview without changes</a>
	<input type="hidden" name="task" value="SetBlurbs" />
	<input type="submit" name="submit" value="Save" />
	<input type="submit" id="submitPreview" name="submitPreview" value="Save and Preview" />
<?php endif; ?>
</form><br />