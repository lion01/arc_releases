<?php
/**
 * @package     Arc
 * @subpackage  Attendance
 * @copyright   Copyright (C) 2005 Punnet. All rights reserved. See COPYRIGHT_ARC.txt
 * @license     http://www.gnu.org/licenses/gpl.html GNU/GPL. See LICENSE_ARC.txt
 * Arc is free software: you can redistribute it and/or modify
 * it under the terms of version 3 of the GNU General Public License
 * as published by the Free Software Foundation.
 * The disclaimer of warranty as stated in the GPL applies to this program
 */

defined( '_JEXEC' ) or die( 'Restricted access' );
?>
<script type="text/javascript">
	window.addEvent('domready', function(){
		//do your tips stuff in here...
		var classTip = new Tips($$('.classTip'), {
			className: 'custom', //this is the prefix for the CSS class
			initialize:function(){
				this.fx = new Fx.Style(this.toolTip, 'opacity', {duration: 300, wait: false}).set(0);
			},
			onShow: function(toolTip) {
				this.fx.start(1);
			},
			onHide: function(toolTip) {
				this.fx.start(0);
			}
		});
	});
</script>
<?php
foreach ($this->common_attendanceMarks as $codes) {
	$this->codeArr[] = JHTML::_('select.option', $codes->code, '&nbsp;' );
}
if (!empty($this->uncommon_attendanceMarks)) {
	$this->codeArr[] = JHTML::_('select.option', 'Other', '&nbsp;' );
}

$curDate = $this->register->date == date('Y-m-d');
$curTime = (($this->register->startTime < date('H:i:s')) && ($this->register->endTime > date('H:i:s')));
$spanStr = '<span style="font-weight: bold; color: #ff0000;">';
$this->regId = $this->register->getCompId();
echo '<h4>'.$this->course->fullname.' ('.($curDate ? $this->register->date : $spanStr.$this->register->date.'</span>' ).', '.($curTime ? $this->register->time : $spanStr.$this->register->time.'</span>' ).')<br />';
$tmp = array();
foreach( $this->register->teachers as $t ) {
	$tmp[] = $t->displayname;
}
echo implode( ', ', $tmp ).'</h4>';
if (!$curDate) {
	echo '<span style="background: #ffdddd;">The register you have selected is not for today\'s date.<br />Please be sure you have selected the right register before continuing</span><br />';
}
if (!$curTime) {
	echo '<span style="background: #ffdddd;">The current time is not during the register you have selected.<br />Please be sure you have selected the right register before continuing</span><br />';
}
?>
<?php if (($this->showRecent) || ($this->showIncidents)) : ?>
<form>
   <input name="Recentbtn" type="button" 
          value="Today's Marks" 
          onclick='show("recent");hide("history");'>
   <input name="Historybtn" type="button" 
          value="Class History" 
          onclick='show("history");hide("recent");'>
</form>
<?php endif; ?>
<?php 
//**** need to make this dynamic (re:markbook Itemid mainly)
$group = serialize( array( $this->course->id ) );
$mbLink = ApotheosisLibAcl::getUserLinkAllowed( 'apoth_ass_main_class', array( 'timetable.groups'=>$group ) );
if( $mbLink !== false ) {
	echo '<a href="'.$mbLink.'">Class Markbook</a>';
}
?>
<form action="<?php echo $this->link; ?>" method="post" name="register">
<table class="register">
<tr>
	<td class="addhoc"></td>
	<td>&nbsp;</td>
	<?php
		if(($this->showRecent) || ($this->showIncidents)){
			$this->recentCellCounter = 1;
			$this->historyCellCounter = 1;
			$datesArr = array();
			while ( ($heading = $this->loadHeading($this->register, 'course')) !== false) {
				$datesArr[date('M-y', strtotime($heading->text))][$heading->text] = $heading->text;
				while (($subHeading = $this->loadHeading($this->register, 'course', $heading->id)) !== false) {
					$subHeadingArr[date('M-y', strtotime($heading->text))][$heading->text][] = $subHeading->text;
				}
			}
			reset($datesArr);

			foreach($datesArr as $headingDate=>$dates) {
				echo '<th id="history'.$this->historyCellCounter++.'" colspan="'.count($dates).'" class="regHeading hide">'.$headingDate.'</th>'."\n";			
			}

		}
	?>
</tr>
<tr>
	<td>&nbsp;</td>
	<th>Pupil Name</th>
	<?php
	if (($this->showRecent) || ($this->showIncidents)) {
		while ( ($heading = $this->loadHeading($this->register, 'course')) !== false) {
			echo '<th id="history'.$this->historyCellCounter++.'" colspan="'.$heading->colspan.'" class="regHeading hide">'.date('jS', strtotime($heading->text)).'</th>'."\n";
		}
		while ( ($heading = $this->loadHeading($this->register, 'day')) !== false) {
			echo '<th id="recent'.$this->recentCellCounter++.'" colspan="'.$heading->colspan.'" class="regHeading">'.date('Y M jS', strtotime($heading->text)).'</th>'."\n";
		}
	}
	
	$keyReqs = array( 'option'=>'com_arc_attendance', 'view'=>'ereg', 'scope'=>'key', 'tmpl'=>'component' );
	$actionId = ApotheosisLib::getActionId( $keyReqs );
	$user = JFActory::getUser();
	$uId = $user->id;
	$permitted = ApotheosisLibAcl::getUserPermitted( $uId, $actionId );
	$keyLink = '<a class="modal" href="'.ApotheosisLib::getActionLink( $actionId ).'" rel="{handler: \'iframe\', size: {x: 500, y: 400}}">Key</a>';
	
	echo '<th colspan="'.(count($this->common_attendanceMarks) + 1).'">Attendance Mark ('.$keyLink.')</th>'."\n";
	?>
</tr>
<tr>
	<td>&nbsp;</td>
	<td>&nbsp;</td>
	<?php
	if (($this->showRecent) || ($this->showIncidents)) {
		while (($heading = $this->loadHeading($this->register, 'course')) !== false) {
			while (($subHeading = $this->loadHeading($this->register, 'course', $heading->id)) !== false) {
				echo '<th id="history'.$this->historyCellCounter++.'" class="regHeading hide">'.$subHeading->text.'</th>'."\n";
			}
		}	
		while (($heading = $this->loadHeading($this->register, 'day')) !== false) {
			while (($subHeading = $this->loadHeading($this->register, 'day', $heading->id)) !== false) {
				echo '<th id="recent'.$this->recentCellCounter++.'" class="regHeading">'.$subHeading->text.'</th>'."\n";
			}
		}
	}
	?>	
<?php 
	foreach ($this->common_attendanceMarks as $code) {
		$this->mark = &$code;
		echo '<th>'.$this->loadTemplate('code').'</th>';
	} 
	if(!empty($this->uncommon_attendanceMarks)) {
		echo '<th colspan="2">Other</th>';
	}
?>
	
	<th></th>
</tr>
<?php
	$this->oddrow = false;
	// show the regular pupils on this register
	while( $this->loadPupil($this->register) ) {
		echo $this->loadTemplate('mark_row');
		$this->oddrow = !$this->oddrow;
	}
	
	ob_start();
	while( $this->loadAdhocPupil($this->register) ) {
		echo $this->loadTemplate('mark_row');
		$this->oddrow = !$this->oddrow;
	}
	if (($str = ob_get_clean()) !== '' ) : ?>
		<tr><td></td><th>Adhoc Pupils</th></tr>
		<?php echo $str; ?>
	<?php endif; ?>
</table>
<table>
<tr>
	<td></td>
	<td><h5>Edit children on the register, on an ad-hoc basis <input type="submit" name="task" value="Select Adhoc" /> / <input type="submit" name="task" value="Remove Adhoc" /></h5></td>
</tr>
<tr>
	<td colspan="2"><input type="submit" name="task" value="Save Register" /></td>
</tr>
</table>
</form>
