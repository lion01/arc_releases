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
?>
<div style="font-size: 0.9em; border: solid #E1E1CE; border-width: 2px 0px; margin: 5px 0px; padding: 2px; background: #F9F9F9;">
<?php
if( JRequest::getVar('repscope') == 'pupil' ) {
	if( ($link = ApotheosisLibAcl::getUserLinkAllowed('apoth_report_list_classes_for_student', array('report.people'=>$this->cycle.'_'.$this->student))) != false ) {
		echo '<a href="'.$link.'">Course List</a>';
	}
	$this->listView->someCourses( $this->members );
}
elseif( JRequest::getVar('repscope') == 'group' ) {
	if( ($link = ApotheosisLibAcl::getUserLinkAllowed('apoth_report_list_pupils', array('report.groups'=>$this->cycle.'_'.$this->group))) != false ) {
		echo '<a href="'.$link.'">Pupil List&nbsp;</a>';
	}
	$this->listView->someMembers( $this->members );
}
?>
</div>