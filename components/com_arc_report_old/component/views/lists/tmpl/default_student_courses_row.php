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

	$parentId = $this->row->_parents[0];
	$student = JRequest::getVar('pupilid');
	// **** surely this is just a potential infinite loop. id doesn't seem to achieve anything
	// but the parentObj is the subject, which we need for the name. Maybe we should have a lib func
	// to find the subject of a given group
	do {
		$parentObj = $this->studentCourses[$parentId];
		$parentId = $this->studentCourses[$parentId]->_parents[0];
	} while(!is_null($this->studentCourses[$parentId]->_parents[0]));
?>

<?php $name = $this->row->fullname; ?>
<?php if( empty($this->existing) || $this->allowMultiple ) : ?>
	<tr<?php if($this->_oddrow) { echo ' class="oddrow"'; } ?>>
		<td><?php echo $parentObj->fullname; ?></td>
		<td>
			<?php
				$link = ApotheosisLibAcl::getUserLinkAllowed('apoth_report_list_pupils', array('report.groups'=>$this->get('CycleId').'_'.$this->row->id));
				echo ( ($link !== false) ? '<a href="'.$link.'">'.$name.'</a>' : $name );
				$name = '&nbsp;';
			?>
		</td>
		<td>&nbsp;</td>
		<td>
			<?php
				$link = ApotheosisLibAcl::getUserLinkAllowed('apoth_report_create', array('report.groups'=>$this->get('CycleId').'_'.$this->row->id, 'report.people'=>$this->get('CycleId').'_'.$student, 'report.scope'=>'pupil') );
				echo ( ($link !== false) ? '<a href="'.$link.'">New</a>' : 'New' );
			?>
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
		<td><?php echo (($name === '&nbsp;') ? $name : $parentObj->fullname); ?></td>
		<td>
			<?php
				$link = ApotheosisLibAcl::getUserLinkAllowed('apoth_report_list_pupils', array('report.groups'=>$this->get('CycleId').'_'.$this->row->id));
				echo ( ($link !== false) ? '<a href="'.$link.'">'.$name.'</a>' : $name );
				$name = '&nbsp;';
			?>
		</td>
		<td>
			<?php
				$link = ApotheosisLibAcl::getUserLinkAllowed('apoth_report_delete', array('report.groups'=>$this->get('CycleId').'_'.$this->row->id, 'report.reports'=>$rpt->getId()) );
				echo ( ($link !== false) ? '<a href="'.$link.'">'.JText::_('Delete').'</a>' : '&nbsp;' );
			?>
		</td>
		<td>
			<?php
				$link = ApotheosisLibAcl::getUserLinkAllowed('apoth_report_edit', array('report.reports'=>$rpt->getId(), 'report.scope'=>'pupil') );
				echo ( ($link !== false) ? '<a href="'.$link.'">'.JText::_('Existing').'</a>' : '&nbsp;' );
			?>
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
