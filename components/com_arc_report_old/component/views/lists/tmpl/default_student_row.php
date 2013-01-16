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

$name = $this->row->displayname;
?>
<?php if( empty($this->existing) || $this->allowMultiple ) : ?>
	<tr<?php if($this->_oddrow) { echo ' class="oddrow"'; } ?>>
		<td>
			<?php if( ($link = ApotheosisLibAcl::getUserlinkAllowed( 'apoth_report_list_classes_for_student', array('report.people'=>$this->get('CycleId').'_'.$this->row->pupilid))) != false ) {
				echo '<a href="'.$link.'">'.$name.'</a>';
			}
			else {
				echo $name;
			}
				$name = '&nbsp;';
			?>
		</td>
		<td>&nbsp;</td>
		<td>
			<?php if( ($link = ApotheosisLibAcl::getUserLinkAllowed('apoth_report_create', array('report.groups'=>$this->get('CycleId').'_'.$this->row->courseid, 'report.people'=>$this->get('CycleId').'_'.$this->row->pupilid, 'report.scope'=>'group'))) != false ) {
				echo '<a href="'.$link.'">New</a>';
			}
			else {
				echo 'New';
			} ?>
		</td>
		<td>
			<img src="<?php echo 'components'.DS.'com_arc_report'.DS.'images'.DS; ?>dot-bad.gif" />
		</td>
		<td>&nbsp;</td>
		<td>&nbsp;</td>
		<td>&nbsp;</td>
	</tr>
<?php endif; ?>

<?php foreach ($this->existing as $rpt) : ?>
	<tr<?php if($this->_oddrow) { echo ' class="oddrow"'; } ?>>
		<td>
			<?php if( ($link = ApotheosisLibAcl::getUserlinkAllowed( 'apoth_report_list_classes_for_student', array('report.people'=>$this->get('CycleId').'_'.$this->row->pupilid))) != false ) {
				echo '<a href="'.$link.'">'.$name.'</a>';
			}
			else {
				echo $name;
			}
				$name = '&nbsp;';
			?>
		</td>
		<td>
			<?php if( ($link = ApotheosisLibAcl::getUserLinkAllowed( 'apoth_report_delete', array('report.groups'=>$this->get('CycleId').'_'.$this->row->courseid, 'report.reports'=>$rpt->getId()) )) != false ) {
				echo '<a href="'.$link.'">'.JText::_('Delete').'</a>';
			}
			else {
				echo '&nbsp;';
			} ?>
		</td>
		<td>
			<?php if( ($link = ApotheosisLibAcl::getUserLinkAllowed( 'apoth_report_edit', array('report.reports'=>$rpt->getId(), 'report.scope'=>'group') )) != false ) {
				echo '<a href="'.$link.'">Existing</a>';
			}
			else {
				echo '&nbsp;';
			} ?>
		</td>
		<td>
			<?php $tmp = $rpt->validate();
			if( empty($tmp) ) {
				echo '<img src="components'.DS.'com_arc_report'.DS.'images'.DS.'dot-good.gif" />';
			}
			else {
				echo '<img src="components'.DS.'com_arc_report'.DS.'images'.DS.'dot-neutral.gif" class="classTip" title="Problems: :: '.nl2br(htmlspecialchars(implode("\n", $tmp))).'" />';
			} ?>
		</td>
		<td>
			<?php $status = $rpt->getStatus();
			if( $status == 'approved' ) {
				echo '<img src="components'.DS.'com_arc_report'.DS.'images'.DS.'dot-good.gif" />';
			}
			elseif( $status == 'submitted' ) {
				echo '<img src="components'.DS.'com_arc_report'.DS.'images'.DS.'dot-neutral.gif" />';
			}
			elseif( $status == 'rejected' ) {
				echo '<img src="components'.DS.'com_arc_report'.DS.'images'.DS.'dot-bad.gif" class="classTip" title="Rejected because: :: '.nl2br(htmlspecialchars($rpt->getFeedback())).'" />';
			}
			else {
				echo '&nbsp;';
			} ?>
		</td>
		<?php if( ($status == 'approved') || ($status == 'rejected') ) : ?>
		<td><?php echo $this->people[$rpt->getCheckedBy()]->displayname; ?></td>
		<td><?php echo $rpt->getCheckedOn(); ?></td>
		<?php else : ?>
		<td>&nbsp;</td>
		<td>&nbsp;</td>
		<?php endif; ?>
	</tr>
<?php endforeach; ?>
