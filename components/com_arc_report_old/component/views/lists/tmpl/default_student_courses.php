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

JHTML::_('behavior.mootools'); 
JHTML::_('behavior.tooltip');
$parents = ApotheosisLibDb::getAncestors(JRequest::getVar('courseid'), '#__apoth_cm_courses', 'id', 'parent');
$this->parent = $parents[JRequest::getVar('courseid')]->_parents[0];
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

<?php if( $this->layout != 'small' ) : ?>
	<a href="<?php echo ApotheosisLib::getActionLinkByName( 'apoth_report_list_pupils', array('report.groups'=>$this->get('CycleId').'_'.$this->sourceGroup) ); ?>">Pupil List</a>
<?php endif; ?>

<table id="student-list" class="data">
<?php if( ($this->layout == 'full') || ($this->layout == 'small') ) : ?>
	<colgroup>
		<col class="col1"></col>
		<col class="col2"></col>
		<col class="col3"></col>
		<col class="col4"></col>
		<col class="col5"></col>
		<col class="col6"></col>
	</colgroup>
<?php endif; ?>
<?php
if (isset($this->studentCourses)) {
	echo $this->loadTemplate('student_courses_list');
}
?>
</table>

<?php if( $this->layout == 'report' ) : ?>
<input type="submit" id="submit" name="submit" value="Save All"
</form>
<?php endif; ?>