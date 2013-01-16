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

// add default CSS
$this->addPath = JURI::base().'components'.DS.'com_arc_attendance'.DS.'views'.DS.'reports'.DS.'tmpl'.DS;
JHTML::stylesheet( 'att_reporting.css', $this->addPath );

// add tooltips behaviour
JHTML::_('behavior.tooltip');
?>
<h3>Reports</h3>
<?php echo $this->loadTemplate( 'search' ); ?>

<hr />

<?php
$this->model = &$this->getModel( 'reports' );
$this->edits = ApotheosisLibAcl::getUserLinkAllowed('att_reports_edit', array());
$this->editShown = $this->get('RowEditsOn');
$markSheetIds = $this->get('markSheetIds');

switch( $this->perspective ) {
case( 'no_marks_found' ):
	// show no marks found message
	echo 'There are no marks to display on this marksheet. Please search again.';
	break;

case( 'sheet' ):
	// add arcTip behaviour
	JHTML::_( 'Arc.tip' );
	
	// add att reporting javascript
	JHTML::script( 'att_reporting.js', $this->addPath, true );
	JHTML::script( 'edit_form.js', $this->addPath, true );
	$js = 'window.addEvent(\'domready\', function() {
		editFormInit();
	});
	';
	$document =& JFactory::getDocument();
	$document->addScriptDeclaration($js);
	
	$this->sheetId = reset( $markSheetIds );
	
	// Standard navigation sections
	if( ApotheosisLibAcl::getUserLinkAllowed('att_reports_drillup', array()) ) {
		echo $this->loadTemplate( 'breadcrumbs' );
	}
	echo $this->loadTemplate( 'filters' );
	
	// Show the attendance detail
	if( $this->edits ) {
		echo $this->loadTemplate( 'edit_controls' );
		echo $this->loadTemplate( 'sheet' );
		echo $this->loadTemplate( 'edit_controls' );
	}
	else {
		echo $this->loadTemplate( 'sheet' );
	}
	break;

case( 'summary' ):
	// add arcTip behaviour
	JHTML::_( 'Arc.tip' );
	
	// add att reporting javascript
	JHTML::script( 'att_reporting.js', $this->addPath, true );
	JHTML::script( 'att_graph_script.js', $this->addPath );
	JHTML::script( 'att_reporting_summary_script.js', $this->addPath );
	JHTML::script( 'edit_form.js', $this->addPath, true );
	
	// Breadcrumbs
	if( ApotheosisLibAcl::getUserLinkAllowed('att_reports_drillup', array()) ) {
		echo $this->loadTemplate( 'breadcrumbs' );
	}
	foreach( $markSheetIds as $this->sheetId ) {
		$sheetContainer = 'sheet_'.$this->sheetId;
		$js = 'window.addEvent(\'domready\', function() {
			graphSwitching( $(\''.$sheetContainer.'\') );
			summarySwitching( $(\''.$sheetContainer.'\') );
			editFormInit( $(\''.$sheetContainer.'\') );
		});
		';
		$document = &JFactory::getDocument();
		$document->addScriptDeclaration( $js );
		echo '<div id="'.$sheetContainer.'">';
		
		// Pupil name
		echo '<h3>'.ApotheosisData::_( 'people.displayname', reset( $this->model->getPupilList($this->sheetId) ) ).'</h3>';
		
		// Filters
		echo $this->loadTemplate( 'filters' );
		
		// Show the attendance detail in much the same way as for a full sheet.
		// Then the stat summary, code totals, and session totals
		if( $this->edits ) {
			echo $this->loadTemplate( 'edit_controls' );
			echo $this->loadTemplate( 'sheet' );
			echo $this->loadTemplate( 'edit_controls' );
		}
		else {
			echo $this->loadTemplate( 'sheet' );
		}
		echo $this->loadTemplate( 'stats' );
		echo $this->loadTemplate( 'totals' );
		echo '</div>';
	}
	break;

case( 'instructions' ):
default:
	echo 'Use the search form to get the attendance data';
}
?>