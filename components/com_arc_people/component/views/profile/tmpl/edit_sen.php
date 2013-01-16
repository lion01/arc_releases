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

$sen = $this->profile->getSen();
$enabled = (bool)ApotheosisLibAcl::getUserLinkAllowed('eportfolio_save_sen', array('people.arc_people'=>JRequest::getVar('pId')) );
?>
<style>

#sen_edit_div {
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

textarea {
	width: 20em;
	height: 10em;
}

</style>
<div id="sen_edit_div">
	<h2 class="title_centre">SEN profile for <?php echo $this->profile->getDisplayName(); ?></h2>
	<form enctype="multipart/form-data" action="<?php echo ApotheosisLib::getActionLinkByName( 'eportfolio_save_sen', array('people.arc_people'=>JRequest::getVar('pId')) ); ?>" method="post" name="edit_sen">
	
	<div class="row">
		<div class="label">Name</div>
		<div class="value"><?php echo $this->profile->getDisplayName(); ?></div>
	</div>
	<div class="row">
		<div class="label">Tutor</div>
		<div class="value"><?php echo ApotheosisLib::getPersonTutor( $this->profile->getId() ); ?></div>
	</div>
	
	<?php
	
	/* load up KS2 results from markbook */
	ApotheosisData::_( 'assessment.prepare', array( 237, 238, 239 ), $this->profile->getId(), null );
	$eng = JHTML::_( 'arc_assessment.markCoalesce', 237, $this->profile->getId(), null );
	$mat = JHTML::_( 'arc_assessment.markCoalesce', 238, $this->profile->getId(), null );
	$sci = JHTML::_( 'arc_assessment.markCoalesce', 239, $this->profile->getId(), null );
	$results['english'] = $eng['html'];
	$results['maths']   = $mat['html'];
	$results['science'] = $sci['html'];
	?>
	<h3>Key Stage 2 Results</h3>
	<div class="row">
		<div class="label">English</div>
		<div class="value"><input name="ks2_english" type="text" value="<?php echo $results['english']; ?>" disabled="disabled" /></div>
	</div>
	<div class="row">
		<div class="label">Maths</div>
		<div class="value"><input name="ks2_maths"   type="text" value="<?php echo $results['maths']; ?>"   disabled="disabled" /></div>
	</div>
	<div class="row">
		<div class="label">Science</div>
		<div class="value"><input name="ks2_science" type="text" value="<?php echo $results['science']; ?>" disabled="disabled" /></div>
	</div>
	
	<h3>Details</h3>
	<div class="row">
		<div class="label">Reading age</div>
		<div class="value"><input name="reading_age" type="text" value="<?php echo $sen['reading_age'].( $enabled ? '' : '" disabled="disabled' ); ?>"/></div>
	</div>
	<div class="row">
		<div class="label">Spelling age</div>
		<div class="value"><input name="spelling_age" type="text" value="<?php echo $sen['spelling_age'].( $enabled ? '' : '" disabled="disabled' ); ?>"/></div>
	</div>
	
	<div class="row">
		<div class="label">Concern</div>
		<div class="value"><textarea name="concerns" <?php echo ( $enabled ? '' : ' disabled="disabled"' ).'>'.$sen['concerns']; ?></textarea></div>
	</div>
	<div class="row">
		<div class="label">Strengths</div>
		<div class="value"><textarea name="strengths"<?php echo ( $enabled ? '' : ' disabled="disabled"' ).'>'.$sen['strengths']; ?></textarea></div>
	</div>
	<div class="row">
		<div class="label">Provision</div>
		<div class="value"><textarea name="provision"<?php echo ( $enabled ? '' : ' disabled="disabled"' ).'>'.$sen['provision']; ?></textarea></div>
	</div>
	<div class="row">
		<div class="label">Code of Practice</div>
		<div class="value"><select name="code_of_practice" >
			<option <?php echo ( ($sen['code_of_practice'] == '')                   ? 'selected="selected"' : '' ); ?>value=""></option>
			<option <?php echo ( ($sen['code_of_practice'] == 'School Action')      ? 'selected="selected"' : '' ); ?>value="School Action">School Action</option>
			<option <?php echo ( ($sen['code_of_practice'] == 'School Action Plus') ? 'selected="selected"' : '' ); ?>value="School Action Plus">School Action Plus</option>
			<option <?php echo ( ($sen['code_of_practice'] == 'Statement')          ? 'selected="selected"' : '' ); ?>value="Statement">Statement</option>
		</select></div>
	</div>
	<?php if( $enabled ) : ?>
	<div class="row">
		<div class="label">&nbsp;</div>
		<div class="value"><input type="submit" value="Save All" /></div>
	</div>
	<?php endif; ?>
	
	</form>
</div>