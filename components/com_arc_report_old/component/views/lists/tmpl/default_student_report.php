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

$name = $this->row->displayname; ?>
<?php foreach ($this->existing as $rpt) : ?>
<?php timer('starting existing'); ?>
	<tr<?php if($this->_oddrow) { echo ' class="oddrow"'; } ?>>
		<td><?php echo $name; $name = '&nbsp;'; ?></td>
		<td>
		<?php
		$id = $rpt->getId();
		if( is_null($id) || (($link = ApotheosisLibAcl::getUserLinkAllowed('apoth_report_delete', array('report.groups'=>$this->get('CycleId').'_'.$this->group, 'report.reports'=>$id))) == false )) {
			echo '&nbsp;';
		}
		else {
			echo '<a href="'.$link.'">'.JText::_('Delete').'</a>';
		}
		?>
		</td>
		<td>
			<?php
			if( is_null($id) ) {
				$id = 'new';
				echo '<img src="components'.DS.'com_arc_report'.DS.'images'.DS.'dot-bad.gif" />';
				echo '<input type="hidden" name="'.$this->row->pupilid.'['.$id.'][cycle]"   value="'.$rpt->getCycle().'" />';
				echo '<input type="hidden" name="'.$this->row->pupilid.'['.$id.'][group]"   value="'.$rpt->getGroup().'" />';
				echo '<input type="hidden" name="'.$this->row->pupilid.'['.$id.'][student]" value="'.$rpt->getStudent().'" />';
			}
			else {
				$tmp = $rpt->validate();
				if( empty($tmp) ) {
					echo '<img src="components'.DS.'com_arc_report'.DS.'images'.DS.'dot-good.gif" />';
				}
				else {
					echo '<img src="components'.DS.'com_arc_report'.DS.'images'.DS.'dot-neutral.gif" class="classTip" title="Problems: :: '.nl2br(htmlspecialchars(implode("\n", $tmp))).'" />';
				}
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
		<?php foreach( $this->fields as $k=>$v) {
			$v = $rpt->getField($k);
			if( is_object($v) ) {
				echo '<td>'.preg_replace('~ (id|name)="(.*?)(\\[\\])?"~', ' $1="'.$this->row->pupilid.'['.$id.'][$2]$3" ', $v->dataHtmlSmall( NULL, $rpt )).'</td>'."\n";
			}
			else {
				echo 'There was a problem showing field '.$k.' for pupil '.$this->row->pupilid.'. Please try again or contact support<br />';
			}
		} ?>
	</tr>
<?php timer('ending existing'); ?>
<?php endforeach; ?>
