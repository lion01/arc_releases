<?php
/**
 * @package     Arc
 * @subpackage  Planner
 * @copyright   Copyright (C) 2005 Punnet. All rights reserved. See COPYRIGHT_ARC.txt
 * @license     http://www.gnu.org/licenses/gpl.html GNU/GPL. See LICENSE_ARC.txt
 * Arc is free software: you can redistribute it and/or modify
 * it under the terms of version 3 of the GNU General Public License
 * as published by the Free Software Foundation.
 * The disclaimer of warranty as stated in the GPL applies to this program
 */

defined( '_JEXEC' ) or die( 'Restricted access' );

$this->update = &$this->group->getUpdate( $this->updateId );
if( $this->curUpdateCat != $this->categoryId ) :
	$this->curUpdateCat = $this->categoryId; ?>
<!-- <div class="nolabel">
	<?php // echo $this->labels['update_intro']; ?>
</div> -->
<div>
	<?php
	$id = $this->inputNum++;
	$name = 'updates['.$this->taskId.']['.$this->groupId.']['.$this->updateId.'][text]';
	$nameHidden = 'updates['.$this->taskId.']['.$this->groupId.']['.$this->updateId.'][category]';
	$disabled = ($this->formUpdateEdit ? '' : 'readonly="readonly"');
	$isDisabled = 'readonly="readonly"';
	$value = $this->update->getText();
	$matches = array();
	$labelClass = ( ((!$this->formUpdateEdit) || ( stripos($this->category['html'], '~ISDISABLED~') !== false )) ? 'class="disabled"' : '' );
	
	// Generate the input from the given html
	if( is_null($this->category['html']) ) {
		$this->category['html'] = '<textarea id="~ID~" name="~NAME~" class="comment" ~DISABLED~>~VALUE~</textarea>';
	
	}
	
	// Process jhtml calls
	if( preg_match( '~JHTML::_\([^)]+?\)~', $this->category['html'], $matches ) ) {
		JRequest::setVar( $name, $value );
		$call = str_replace( array('~NAME~', '~ISDISABLED~'), array($name, 'true'), $matches[0] );
		eval( '$callResult = '.$call.';' );
		$this->category['html'] = str_replace( $matches[0], $callResult, $this->category['html'] );
	}
	
	// select lists need special handling
	if( preg_match('~<select.*<option.*/option>.*/select>~s', $this->category['html']) ) {
		if( stripos($this->category['html'], 'multiple="multiple"') !== false ) {
			$name .= '[]';
		}
		$input = str_replace( array('~ID~', '~NAME~', '~DISABLED~', '~ISDISABLED~'), array( $id, $name, $disabled, $isDisabled ), $this->category['html'] );
		$vals = explode( ';', $value );
		foreach( $vals as $vId=>$val ) {
			$input = str_replace( 'value="'.$val.'"', 'value="'.$val.'" selected="selected"', $input );
		}
	}
	else if( stripos( $this->category['html'], 'radio' ) ){
		$input = str_replace( array('~ID~', '~NAME~', '~DISABLED~', '~ISDISABLED~'), array( $id, $name, $disabled, $isDisabled), $this->category['html'] );
		$vals = explode( ';', $value );
		foreach( $vals as $vId=>$val ) {
			$input = str_replace( 'value="'.$val.'"', 'value="'.$val.'" checked="checked"', $input );
		}	
	}
	else {
		$input = str_replace( array('~ID~', '~NAME~', '~DISABLED~', '~ISDISABLED~', '~VALUE~'), array( $id, $name, $disabled, $isDisabled, $value), $this->category['html'] );
	}
	?>
	

	<label <?php echo 'for="'.$this->inputNum.'" '.$labelClass; ?>><?php echo $this->categoryText; ?></label>
	<?php echo $input; ?>
	<input type="hidden" name="<?php echo $nameHidden; ?>" value="<?php echo $this->categoryId; ?>" />
</div>
<?php endif; ?>
<?php if( $this->taskIsLeaf && $this->formProgress ) : ?>
	<div>
		<label for="<?php echo $this->inputNum; ?>">Progress:</label>
		<input id="<?php echo $this->inputNum++; ?>" class="progress" type="text" name="updates<?php echo '['.$this->taskId.']['.$this->groupId.']['.$this->updateId.']'; ?>[progress]" <?php echo 'value="'.htmlspecialchars($this->update->getProgress()).'"'.($this->formUpdateEdit ? ' />%&nbsp;&nbsp;&nbsp;( A whole number between 0 and 100 )' : 'disabled="disabled" />%'); ?>
	</div>
<?php endif; ?>

<?php if( $this->formEvidence && $this->category['has_evidence'] ) : ?>
	<?php
		// Gather all the evidence to display, then show it and the "add more" form if appropriate
		
		$evBlock = '';
		// display any pre-set evidence (URLs or files)
		$firstPass = true;
		
		// Roll up all evidence into one list if we're showing single updates
		// *** We could really do with another flag to say if edits overwrite or add, then we could check that here instead
		if( $this->formUpdateNumber == 's' ) {
			$evidence['url'] = array();
			$evidence['file'] = array();
			$allUpdates = &$this->group->getUpdates( false, $this->categoryId );
			foreach( $allUpdates as $eUpdate ) {
				$tmp1 = $eUpdate->getEvidence( 'url' );
				$tmp2 = $eUpdate->getEvidence( 'file' );
				foreach($tmp1 as $eId=>$val) {
					$evidence['url'][$eId] = $val;
				}
				foreach($tmp2 as $eId=>$val) {
					$evidence['file'][$eId] = $val;
					$evidence['owner'][$eId] = $eUpdate->getFileOwner( $eId );
				}
			}
		}
		else {
			$evidence['url'] = $this->update->getEvidence( 'url' );
			$evidence['file'] = $this->update->getEvidence( 'file' );
			foreach( $evidence['file'] as $eId=>$val ) {
				$evidence['owner'][$eId] = $this->update->getFileOwner( $eId );
			}
		}
		foreach( $evidence['url'] as $evidenceUrl ) {
			$evBlock .= '
				<div>
					<label>'.( $firstPass ? 'Existing Evidence:' : '&nbsp;' ).'</label>
					<a href="'.$evidenceUrl.'" class="evidence">'.htmlspecialchars($evidenceUrl).'</a><br />
				</div>
				';
			$firstPass = false;
		}
		foreach( $evidence['file'] as $eId=>$evidenceFile ) {
			$owner = $evidence['owner'][$eId];
			$evBlock .= '
				<div>
					<label>'.( $firstPass ? 'Existing Evidence:' : '&nbsp;' ).'</label>
					<a href="'.ApotheosisPeopleData::getFileLink( $owner , $evidenceFile).'" class="evidence">'.htmlspecialchars($evidenceFile).'</a><br />
				</div>
				';
			$firstPass = false;
		}
		
		// display the add evidence template if allowed
		if( $this->formUpdateEdit || $this->updateId == 'new' ) {
			$evBlock .= $this->loadTemplate( 'evidence_list' );
		}
		if( $evBlock != '' ) :
	?>
		<!-- evidence block div for JS slider -->
		<div class="evidence_clicker_div"><a class="evidence_slider" class="nolabel" href="#"><?php echo $this->labels['update_evidence_toggle']; ?></a></div>
		<div class="evidence_div">
			<?php echo $evBlock; ?>
		</div>
	<?php endif; ?>
<?php endif; ?>

<?php
/* *** micro task templateness 
 * not doing micro tasks for now
// display microtask fields, if any
$firstPass = true;
if( $this->edit ) {
	foreach( $this->update->getMicros() as $microId ) {
		$microTask = $this->model->getTask( $microId );
		echo '
			<div class="micro_task">
				<label for="micro'.$microId.'">'.( $firstPass ? 'Micro-tasks:' : '&nbsp;' ).'</label>
				<input id="micro'.$microId.'" type="checkbox" name="micro[]" value="'.$microId.'" checked="checked" disabled="disabled" /> '.$microTask->getTitle().'<br />
			</div>
			';
		$firstPass = false;
	}
}
foreach( $this->microTasks as $microId=>$microTask ) {
	echo '
		<div class="micro_task">
			<label for="micro'.$microId.'">'.( $firstPass ? 'Micro-tasks:' : '&nbsp;' ).'</label>
			<input id="micro'.$microId.'" type="checkbox" name="micro[]" value="'.$microId.'" /> '.$microTask->getTitle().'<br />
		</div>
		';
	$firstPass = false;
}
// */
?>
