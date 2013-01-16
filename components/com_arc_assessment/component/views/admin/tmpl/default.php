<?php
/**
 * @package     Arc
 * @subpackage  Assessment
 * @copyright   Copyright (C) 2005 Punnet. All rights reserved. See COPYRIGHT_ARC.txt
 * @license     http://www.gnu.org/licenses/gpl.html GNU/GPL. See LICENSE_ARC.txt
 * Arc is free software: you can redistribute it and/or modify
 * it under the terms of version 3 of the GNU General Public License
 * as published by the Free Software Foundation.
 * The disclaimer of warranty as stated in the GPL applies to this program
 */

defined( '_JEXEC' ) or die( 'Restricted access' );

// add javascript
$coreJsPath = JURI::base().'administrator'.DS.'components'.DS.'com_arc_core'.DS.'libraries'.DS.'js'.DS;
JHTML::script( 'mooRainbow.js', $coreJsPath );
JHTML::script( 'colorNameHash.js', $coreJsPath );
JHTML::script( 'apothMooRainbow.js', $coreJsPath );
$adminViewPath = JURI::base().'components'.DS.'com_arc_assessment'.DS.'views'.DS.'admin'.DS.'tmpl'.DS;
JHTML::script( 'object_edit_boundary.js', $adminViewPath );
JHTML::script( 'object_edit_control.js', $adminViewPath );
JHTML::script( 'default.js', $adminViewPath );

// add CSS
$coreCssPath = JURI::base().'administrator'.DS.'components'.DS.'com_arc_core'.DS.'libraries'.DS.'css'.DS;
JHTML::stylesheet( 'mooRainbow.css', $coreCssPath );
JHTML::stylesheet( 'default.css', $adminViewPath );

// add script to pass default boundaries to JS
$boundaryDataScript = 'boundary_defaults = Json.evaluate( \''.json_encode( $this->defaultBoundaries ).'\' );';
$markstyleInfoScript = 'markstyle_info = $H( Json.evaluate( \''.json_encode( $this->markstyleInfo ).'\' ) );';
$imgPath = 'var img_path = Json.evaluate( \''.json_encode( JURI::base().'components'.DS.'com_arc_core'.DS.'images'.DS ).'\' );';
$doc = &JFactory::getDocument();
$doc->addScriptDeclaration( $boundaryDataScript );
$doc->addScriptDeclaration( $markstyleInfoScript );
$doc->addScriptDeclaration( $imgPath );

// set post variables for JHTML calls to access
foreach( $this->assProps['assessment'] as $k=>$v ) {
	JRequest::setVar( $k, $v );
}
JRequest::setVar( 'admin_groups', serialize($this->assGroups) );
?>
<form action="<?php echo ApotheosisLib::getActionLink( null, array('assessment.assessments'=>$this->assProps['assessment']['id']) ); ?>" name="arc_search" id="arc_search" method="post" >
<div class="form_row">
	<h3>Assessment Administration</h3>
	<h2>Assessment</h2>
	<input type="submit" name="task" value="Import" />
	<input type="submit" name="task" value="Export" />
	<input type="submit" name="task" value="Copy" />
<!-- 	<input type="submit" name="task" value="Repeat" />	-->
</div>
<div class="form_column">
	<input type="hidden" name="id" value="<?php echo $this->assProps['assessment']['id']; ?>" />
	<div class="input_div">
		<label for="title">Title:</label>
		<input type="text" name="title" value="<?php echo JRequest::getVar( 'title' ); ?>" /> 
	</div>
	<div class="input_div">
		<label for="short">Short:</label>
		<input type="text" name="short" value="<?php echo JRequest::getVar( 'short' ); ?>" /> 
	</div>
	<div class="input_div">
		<label for="valid_from">Valid from:</label>
		<?php echo JHTML::_( 'arc.dateField', 'valid_from', date('Y-m-d') );?>
	</div>
	<div class="input_div">
		<label for="valid_to">Valid to:</label>
		<?php echo JHTML::_( 'arc.dateField', 'valid_to', date('Y-m-d') );?>
	</div>
	<div class="input_div">
		<label for="color">Colour:</label>
		<?php echo JHTML::_( 'arc.color', 'color', 'ass_rainbow' );?>
	</div>
	<div class="input_div">
		<label for="description">Description:</label>
		<textarea cols="30" rows="7" name="description"><?php echo JRequest::getVar( 'description' ); ?></textarea> 
	</div>
</div>
<div class="form_column">
<!--
	<div class="input_div">
		<label for="assign_tab">Assign to tab:</label>
		<?php //echo JHTML::_( 'arc_assessment.tabs', 'assign_tab' );?> *** sort when assessment.tabs is done
	</div>
-->
	<div>
		<label for="assign_to">Assign to:</label>
		<?php echo JHTML::_( 'groups.grouptree', 'admin_groups', ApotheosisLib::getActionIdByName( 'apoth_ass_admin_add') ); ?>
	</div>
</div>
<div class="form_column">
<!--
	<div class="input_div">
		<label for="new_tab">or add to new tab:</label>
		<input type="text" name="new_tab" /> 
	</div>
-->
	<div>
		Visible to:
		<div class="input_div">
			<label for="access[teachers]">Teachers:</label>
			<input type="checkbox" name="access[teachers]" <?php echo ( (isset($this->assAccess['teachers']) && $this->assAccess['teachers']) ? 'checked="checked"' : '' ); ?>/>
		</div>
		<div class="input_div">
			<label for="access[students]">Students:</label>
			<input type="checkbox" name="access[students]" <?php echo ( (isset($this->assAccess['students']) && $this->assAccess['students']) ? 'checked="checked"' : '' ); ?>/>
		</div>
		<div class="input_div">
			<label for="access[parents]">Parents:</label>
			<input type="checkbox" name="access[parents]" <?php echo ( (isset($this->assAccess['parents']) && $this->assAccess['parents']) ? 'checked="checked"' : '' ); ?>/>
		</div>
		<br />
		<br />
		Advanced:
		<div class="input_div">
			<label for="group_specific">Group-specific:</label>
			<input type="checkbox" name="group_specific" <?php echo $this->assProps['assessment']['group_specific'] ? 'checked="checked"' : ''; ?>/>
		</div>
		<div class="input_div">
			<label for="always_show">Always show:</label>
			<input type="checkbox" name="always_show" <?php echo $this->assProps['assessment']['always_show'] ? 'checked="checked"' : ''; ?>/>
		</div>
	</div>
</div>
<div class="form_row">
	<h2>Aspects</h2>
	<input type="submit" name="task" value="Add" />
	<input type="submit" name="task" value="Copy Aspects" />
	<input type="submit" name="task" value="Remove" />
	<input type="submit" name="task" value="Import Aspects" />
	<input type="submit" name="task" value="Export Aspects" />
</div>
<?php
echo ApotheosisLib::arcDataStart( '10em' );
$this->oddrow = false;
$this->leftCol = true;
echo $this->loadTemplate( 'asp_table' );

echo ApotheosisLib::arcDataMiddle();
$this->oddrow = false;
$this->leftCol = false;
echo $this->loadTemplate( 'asp_table' );

echo ApotheosisLib::arcDataEnd();

echo $this->loadTemplate( 'asp_boundary_edit' );
?>
<div class="form_row">
	<div>
		<!-- *** hidden input for assessment id -->
		<input type="submit" name="task" value="Save" />
		<!-- *** SUBMIT input for Add & View -->
	</div>
</div>
</form>