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

echo $this->field->titleHtml();
$fName = $this->field->getName();
$jsSelectStr = $this->formName.'.'.$fName;
$jsButtonsStr =
	 $this->formName.'.edit'.$fName.', '
	.'document.getElementById(\''.$this->formName.'_edit'.$fName.'_link\'), '
	.$this->formName.'.del'. $fName;

$statements = $this->bank->getStatements();
echo '<select name="'.$fName.'" id="'.$fName.'" multiple="multiple" style="width: 100%; height: 10em;" onclick="listChanged( this, '.$jsButtonsStr.' );">';
if( is_array($statements) ) {
	foreach( $statements as $k=>$v ) {
		echo '<option value="'.$k.'" style="background: '.htmlspecialchars($v->color).';">'.$v->text.'</option>';
	}
}
echo '</select>';
?>
<?php if( $this->enabled ) : ?>
	<input type="button" name="selA" value="Select All"  onclick="selectAll( <?php echo $jsSelectStr.', '.$jsButtonsStr; ?>);" />
	<input type="button" name="selN" value="Select None" onclick="selectNone(<?php echo $jsSelectStr.', '.$jsButtonsStr; ?>);" />
	<input type="button" id="del<?php echo $fName; ?>" name="del" disabled="disabled" value="Delete Selected" onclick="delSelection('<?php echo $this->formName; ?>', '<?php echo $fName; ?>');" />
	<br />
	<?php if( ($link = ApotheosisLibAcl::getUserLinkAllowed('apoth_report_admin_st_edit', array('report.fields'=>$fName, 'report.statements'=>0, 'report.groups'=>$this->get('CycleId').'_'.$this->get('Group')))) !== false ): ?>
		<a class="modal" rel="{handler: 'iframe', size: {x: 640, y: 480}}" href="<?php echo $link; ?>" id="<?php echo $this->formName.'_edit'.$fName.'_link'; ?>">
			<input type="button" id="edit<?php echo $fName; ?>" name="edit" disabled="disabled" value="Edit" />
		</a>
	<?php endif; ?>
	
	<?php if( ($link = ApotheosisLibAcl::getUserLinkAllowed('apoth_report_admin_st_add', array('report.fields'=>$fName, 'report.groups'=>$this->get('CycleId').'_'.$this->get('Group')))) !== false ): ?>
		<a class="modal" rel="{handler: 'iframe', size: {x: 640, y: 480}}" href="<?php echo $link; ?>">
			<input type="button" id="add<?php echo $fName; ?>" name="add" value="Add new" />
		</a>
	<?php endif; ?>
	
	<?php if( ($link = ApotheosisLibAcl::getUserLinkAllowed('apoth_report_admin_st_order', array('report.fields'=>$fName, 'report.groups'=>$this->get('CycleId').'_'.$this->get('Group')))) !== false ): ?>
		<a class="modal" rel="{handler: 'iframe', size: {x: 640, y: 480}}" href="<?php echo $link; ?>">
			<input type="button" id="order<?php echo $fName; ?>" name="order" value="Re-order" />
		</a>
	<?php endif; ?>
	<br /><br />
<?php endif; ?>
