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

echo $this->loadTemplate('navigation'); ?>
<?php JHTML::_('behavior.mootools'); ?>
<?php JHTML::_('behavior.modal'); ?>

<script type="text/javascript">
<?php $s = $this->report->getStatus(); if( ($s == 'draft') || ($s == 'rejected') ) : ?>
	window.addEvent('domready', function() {
		$('previewLink').style.display = 'none';
		$('previewBtn').style.display  = 'inline';
		lineChecker();
	});
<?php endif; ?>

<?php if(JRequest::getVar('preview') == 'true'): ?>
	window.addEvent('domready', function() {
		$('previewLink').fireEvent('click', $('previewLink'));
	});
<?php endif; ?>

	window.addEvent('domready', function() {
		lineChecker();
	});
</script>

<?php
global $Itemid;
?>
<h3>Report for <?php echo $this->report->getStudentName(); ?></h3>
<form method="post" action="index.php?Itemid=<?php echo $Itemid; ?>&option=com_arc_report&view=report" id="reportForm" name="reportForm"/>
<table style="width: 100%;">
<?php
$hasLineCount = false;
$this->report->resetRow();
$this->counted = array();
while($row = $this->report->getRow()) : ?>
	<tr><td><table style="width: 100%;"><tr>
	<?php
	$count = count($row);
	$width = (1 / $count) * 100;
	foreach( $row as $k=>$v ) :
		if( strtolower(get_class($v)) != 'apothfieldhidden') {
			echo '<td width="'.$v->getHtmlWidth().'">';
		}
		else {
			$width = (1 / --$count) * 100;
		}
		
		$lc = ( ( method_exists($v, 'lineCountHtml') ) ? $v->lineCountHtml() : '' );
		$dataHtml = preg_replace('~%(?!\d+\$\w)~', '%%', $v->dataHtml());
		$widthHtml = ($v->hasSuffix() ? '95%' : '85%');
		if( is_null($v->getStatementBank()) && ($lc == '') ) {
			echo $v->titleHtml()."\n".sprintf( $dataHtml, $widthHtml, $v->getHtmlHeight())."\n";
		}
		else {
			echo $v->titleHtml();
			echo '<div style="width: 90%; float: left;">'."\n";
			echo sprintf( $dataHtml, $widthHtml, $v->getHtmlHeight() )."\n";
			echo '</div><div style="width: 8%; margin-left: 92%;">'."\n";
			if( $lc != '') {
				echo $lc.'<br />'."\n";
				$this->counted[] = $v;
			}
			if( method_exists($v, 'statementPickerHtml') ) {
				echo $v->statementPickerHtml( ApotheosisLib::getActionLinkByName('apoth_report_statement_picker', array('report.fields'=>$v->getName())), 'components'.DS.'com_arc_report'.DS.'images'.DS.'list.gif');
			}
			echo '</div>';
		}
		
		if( strtolower(get_class($v)) != 'apothfieldhidden') {
			echo '</td>';
		}
	endforeach; ?>
	</tr></table></td></tr>
<?php endwhile; ?>
</table>
<?php echo $this->loadTemplate('scripts'); ?>

<input type="hidden" name="student" value="<?php echo $this->student; ?>" />
<input type="hidden" name="group" value="<?php echo $this->group; ?>" />
<input type="hidden" name="repscope" value="<?php echo JRequest::getVar('repscope'); ?>" />
<?php
$status = $this->report->getStatus();
$id = $this->report->getId();
$suf = is_null($id) ? '_new' : '_existing';
$depsArray = array(
   'report.people'=>$this->get('CycleId').'_'.$this->student
 , 'report.groups'=>$this->get('CycleId').'_'.$this->group
 , 'report.reports'=>(is_null($id) ? 'NULL' : $this->report->getId()));
if( ($status == 'rejected')
 || ($status == 'approved')
 || ($status == 'draft')
 || ($status == 'submitted') ) {
	if( ($link = ApotheosisLibAcl::getUserLinkAllowed('apoth_report_output', $depsArray )) != false ) {
		echo '<a class="modal" id="previewLink" name="previewLink" style="display: inline;" href="'.$link.'" rel="{handler: \'iframe\', size: {x: 700, y: 500}}" style="color: black; text-decoration: none;" target="_blank">Preview without changes</a>'."\n";
	}
	if( ($link = ApotheosisLibAcl::getUserLinkAllowed('apoth_report_save_preview'.$suf, $depsArray )) != false ) {
		echo '<input type="submit" id="previewBtn" name="task" value="Save &amp; Preview" style="display: none;" />'."\n";
	}
	if( ($link = ApotheosisLibAcl::getUserLinkAllowed('apoth_report_save_draft'.$suf, $depsArray )) != false ) {
		echo '<input type="submit" name="task" value="Save draft" />'."\n";
	}
	if( ($link = ApotheosisLibAcl::getUserLinkAllowed('apoth_report_save_incomplete'.$suf, $depsArray )) != false ) {
		echo '<input type="submit" name="task" value="Save as incompletable" />'."\n";
	}
	if( ($link = ApotheosisLibAcl::getUserLinkAllowed('apoth_report_save_complete'.$suf, $depsArray )) != false ) {
		echo '<input type="submit" name="task" value="Save complete" />'."\n";
	}
}

if( $status == 'submitted' ) {
	if( ($link = ApotheosisLibAcl::getUserLinkAllowed('apoth_report_approve', $depsArray )) != false ) {
		echo '<input type="submit" name="task" value="Approve" />'."\n";
	}
	if( ($link = ApotheosisLibAcl::getUserLinkAllowed('apoth_report_reject', $depsArray )) != false ) {
		echo '<input type="submit" name="task" value="Reject" />'."\n";
	}
}

if( $status == 'approved' ) {
	if( ($link = ApotheosisLibAcl::getUserLinkAllowed('apoth_report_reject', $depsArray )) != false ) {
		echo '<input type="submit" name="task" value="Reject" />'."\n";
	}
	if( ($link = ApotheosisLibAcl::getUserLinkAllowed('apoth_report_finalise', $depsArray )) != false ) {
		echo '<input type="submit" name="task" value="Archive" />'."\n";
	}
}
?>
</form>
