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
<tr <?php echo (($this->oddrow) ? 'class="oddrow"' : ''); ?>>
<?php
	if (is_null($this->pupil->mark)) {
		$checked = '/';
	}
	elseif( array_key_exists( $this->pupil->mark, $this->common_attendanceMarks ) ) {
		$checked = $this->pupil->mark;
	}
	else {
		$checked = 'Other';
	}
	
	// if the code is in either of the lists of codes that this user can see, they are allowed to select / change it
	$codeAuthed = ( is_null($this->pupil->mark)
	             || array_key_exists($this->pupil->mark, $this->common_attendanceMarks)
	             || array_key_exists($this->pupil->mark, $this->uncommon_attendanceMarks) );
	
	?>
	<?php if ($this->pupil->adhoc) : ?>
		<td><input type="checkbox" name="remove[<?php echo $this->regId; ?>][]" value="<?php echo $this->pupil->id; ?>" /></td>
	<?php else : ?>
		<td>&nbsp;</td>
	<?php endif; ?>
	
	<td>
	<?php
	if( $this->showIncidents ) {
		if( isset( $this->pupil->incidents[$this->register->date][''] ) ) {
			$this->incidents = $this->pupil->incidents[$this->register->date][''];
			echo $this->loadTemplate( 'messages' );
		}
		echo JHTML::_('arc_behaviour.addIncident', array('student_id'=>$this->pupil->id, 'group_id'=>$this->course->id, 'room_id'=>$this->register->location) );
	}
	?>
	(<span style="font-size: 0.9em;"><?php echo $this->pupil->tutorgroup ?></span>)
	<?php echo ApotheosisLib::nameCase( 'pupil', '', $this->pupil->firstname, $this->pupil->middlenames, $this->pupil->surname ) ?>
	</td>
	
	<?php // show recently recorded marks
		if (($this->showRecent) || ($this->showIncidents)) {
			$shownCount = 0;
			$r = $this->pupil->history;
			foreach($this->_recentHeadings['course'] as $headingsDate=>$headingsTimes) {
				foreach($headingsTimes as $start_time=>$heading) {
					echo '<td id="history'.$this->historyCellCounter++.'" class="hide">';
					if ((is_array($r)) && (array_key_exists($headingsDate, $r))) {
						$this->mark = $r[$headingsDate][$start_time];
						$this->incidents = ( isset( $this->pupil->incHistory[$headingsDate][$this->mark->group] )
							? $this->pupil->incHistory[$headingsDate][$this->mark->group]
							: array() );
						echo $this->loadTemplate('code'); 
					}
					else {
						echo '&nbsp;';
					}
					echo '</td>';
				}
			}
			$r = $this->pupil->recent;
			foreach($this->_recentHeadings['day'] as $headingsDate=>$headingsTimes) {
				foreach($headingsTimes as $start_time=>$heading) {
					echo '<td id="recent'.$this->recentCellCounter++.'">';
					if ((is_array($r)) && (array_key_exists($headingsDate, $r))) {
						$this->mark = ( isset($r[$headingsDate][$start_time]) ? $r[$headingsDate][$start_time] : null );
						$this->incidents = ( !is_null($this->mark)
								&& isset( $this->pupil->incidents[$headingsDate][$this->mark->group] )
							? $this->pupil->incHistory[$headingsDate][$this->mark->group]
							: array() );
						echo $this->loadTemplate('code'); 
					}
					else {
						echo '&nbsp;';
					}
					echo '</td>';
				}
			}
		}
		
		
		// show mark options to be recorded (common)
		if( !empty($this->common_attendanceMarks) ) {
			if( !$codeAuthed ) {
				echo '<input type="hidden" id="attendance'.$this->regId.''.$this->pupil->id.'" name="attendance['.$this->regId.']['.$this->pupil->id.']" value="'.$checked.'" />';
					$cArr = $this->codeArr;
					foreach( $cArr as $k ) {
						echo '<td></td>';
					}
			}
			else {
				echo str_replace( array( '<input', '</label>' ), array( '<td><input', '</label></td>' ), JHTML::_( 'select.radioList', $this->codeArr, 'attendance['.$this->regId.']['.$this->pupil->id.']', ( $codeAuthed ? '' : 'disabled = "disabled"' ), 'value','text', $checked ) );
			}

		}
	?>
	<td>
	<?php // show mark options to be recorded (uncommon)
		
		// if we should be displaying an "other" code, make sure it's in the list
		if ($checked == 'Other' && !$codeAuthed) {
			$cObj = new stdclass();
			$cObj->code = $this->pupil->mark;
			$codes = array($cObj->code => $cObj);
		}
		else {
			$codes = &$this->uncommon_attendanceMarks;
		}
		
		if( !empty($codes) ) {
			if( !$codeAuthed ) {
				echo '<input type="hidden" id="other'.$this->regId.''.$this->pupil->id.'" name="other['.$this->regId.']['.$this->pupil->id.']" value="'.$this->pupil->mark.'" />';
				echo '<div style="background-color:#C3D2E5; width: 1.5em; margin:auto; text-align:center; border:2px solid #84A7DB;"><strong>'.$this->pupil->mark.'</strong></div>';
			}
			else{
				echo JHTML::_('select.genericList', $codes, 'other['.$this->regId.']['.$this->pupil->id.']', ( $codeAuthed ? '' : 'disabled = "disabled"' ), 'code', 'code', (($checked == 'Other') ? $this->pupil->mark : key(reset($codes))) );
			}
			
		}
	?>
	<?php if ($this->pupil->adhoc) : ?>
		<input type="hidden" name="adhocPupils[<?php echo $this->regId; ?>][]" value="<?php echo $this->pupil->id; ?>" />
	<?php endif; ?>
	</td>
</tr>