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

jimport('joomla.application.component.view');

/**
 * Reports Lists View
 *
 * @author		lightinthedark <code@lightinthedark.org.uk>
 * @package		Apotheosis
 * @subpackage	Reports
 * @since 0.1
 */
class ReportsViewLists extends JView 
{
	/**
	 * Constructor
	 */
	function __construct( $config = array() )
	{
		parent::__construct( $config );
		$this->_varMap = array('state'=>'State', 'group'=>'Group', 'parent'=>'Parent', 'grandparent'=>'Grandparent', 'listLink'=>'ListLink');
		
		$paramsObj = &JComponentHelper::getParams('com_arc_report');
		$c = ( (isset($config['cycle']) && !empty($config['cycle'])) ? $config['cycle'] : JRequest::getVar('report_cycle', $paramsObj->get( 'current_cycle', false )) );
		JRequest::setVar('report_cycle', $c );
	}
	
	/**
	 * Displays a generic page
	 * (for when there are no actions or selected registers)
	 *
	 * @param string $template  Optional name of the template to use
	 */
	function all( $tpl = NULL )
	{
		$this->_varMap['subject'] = 'Subjects';
		$this->_varMap['pastoral'] = 'PastoralCourses';
		ApotheosisLib::setViewVars($this, $this->_varMap);
		
		parent::display( $tpl );
	}
	
	function normal( $tpl = NULL )
	{
		$this->_varMap['subject'] = 'Subjects';
		ApotheosisLib::setViewVars($this, $this->_varMap);
		
		parent::display( $tpl );
	}
	
	function pastoral( $tpl = NULL )
	{
		$this->_varMap['pastoral'] = 'PastoralCourses';
		ApotheosisLib::setViewVars($this, $this->_varMap);
		
		parent::display( $tpl );
	}
	
	function someCourses( $wantedCourses = false, $layout = 'small' )
	{
		$db = &JFactory::getDBO();
		$this->studentCoursesWanted = $wantedCourses;
		$this->_varMap['studentCourses'] = 'StudentCourses';
		$this->_varMap['reportLink'] = 'ReportLink';
		$this->_varMap['listLink']   = 'ListLink';
		$this->_varMap['group'] = 'Group';
		$this->_varMap['sourceGroup'] = 'SourceGroup';
		$this->_varMap['allowMultiple'] = 'CycleAllowMultiple';
		ApotheosisLib::setViewVars($this, $this->_varMap);
		
		$rModel = &$this->getModel( 'report' );
		$this->reports = &$rModel->getReports();
		
		$this->written = array();
		$people = array();
		foreach( $this->reports as $k=>$rpt ) {
			$depsArray = array( 'report.reports'=>$rpt->getId() );
			if( !(ApotheosisLibAcl::getUserLinkAllowed('apoth_report_save_draft_existing', $depsArray))
			 || !(ApotheosisLibAcl::getUserLinkAllowed('apoth_report_save_complete_existing', $depsArray))
			 || !(ApotheosisLibAcl::getUserLinkAllowed('apoth_report_save_incomplete_existing', $depsArray))
			 || !(ApotheosisLibAcl::getUserLinkAllowed('apoth_report_save_preview_existing', $depsArray))
			 ||  ($rpt->getStatus() == 'final') ) {
				$this->reports[$k]->disable();
			}
			$this->written[$rpt->getGroup()][] = &$this->reports[$k];
			$by = $rpt->getCheckedBy();
			if( ($by != false) && !array_key_exists($by, $people) ) {
				$people[$by] = $db->Quote($by);
			}
		}
		
		$this->people = ApotheosisLib::getUserList('WHERE p.id IN ('.implode(', ', $people).')', false, 'teacher');
		$this->layout = $layout;
		parent::display( 'student_courses' );
	}
	
	function classes()
	{
		$this->_varMap['classes'] = 'Children';
		ApotheosisLib::setViewVars($this, $this->_varMap);
		
		parent::display( 'classes' );
	}
	
	function members()
	{
		$students = $this->get('Students');
		$this->someMembers( $students, 'full' );
	}
	
	function someMembers( &$students, $layout = 'small' )
	{
		$db = &JFactory::getDBO();
		$this->students = &$students;
		$this->_varMap['reportLink'] = 'ReportLink';
		$this->_varMap['listLink']   = 'ListLink';
		$this->_varMap['allowMultiple'] = 'CycleAllowMultiple';
		ApotheosisLib::setViewVars($this, $this->_varMap);
		
		$rModel = &$this->getModel( 'report' );
		$this->reports = &$rModel->getReports();
		
		$this->written = array();
		$people = array();
		foreach( $this->reports as $k=>$rpt ) {
			$depsArray = array( 'report.reports'=>$rpt->getId() );
			if( !(ApotheosisLibAcl::getUserLinkAllowed('apoth_report_save_draft_existing', $depsArray))
			 || !(ApotheosisLibAcl::getUserLinkAllowed('apoth_report_save_complete_existing', $depsArray))
			 || !(ApotheosisLibAcl::getUserLinkAllowed('apoth_report_save_incomplete_existing', $depsArray))
			 || !(ApotheosisLibAcl::getUserLinkAllowed('apoth_report_save_preview_existing', $depsArray))
			 ||  ($rpt->getStatus() == 'final') ) {
				$this->reports[$k]->disable();
			}
			$this->written[$rpt->getStudent()][] = &$this->reports[$k];
			$by = $rpt->getCheckedBy();
			if( ($by != false) && !array_key_exists($by, $people) ) {
				$people[$by] = $db->Quote($by);
			}
		}
		$this->people = ApotheosisLib::getUserList('WHERE p.id IN ('.implode(', ', $people).')', false, 'teacher');
		
		if( $layout == 'report' ) {
			// create new reports in the report model for those kids that need them
			$cycle = $this->get('CycleId');
			foreach( $students as $id=>$student ) {
				$newDepsArray = array(
				 'report.people'=>$cycle.'_'.$student->pupilid,
				 'report.groups'=>$cycle.'_'.$student->courseid
				);
				if( !is_array($this->written[$student->pupilid]) ) {
					$this->written[$student->pupilid] = array();
				}
				if( empty($this->written[$student->pupilid]) || $this->allowMultiple ) {
					$rModel->setReportNew( $student->pupilid, $student->courseid, $cycle, true );
					$tmp = &$rModel->getReport();
					
					if( !(ApotheosisLibAcl::getUserLinkAllowed('apoth_report_save_draft_new', $newDepsArray))
					 || !(ApotheosisLibAcl::getUserLinkAllowed('apoth_report_save_complete_new', $newDepsArray))
					 || !(ApotheosisLibAcl::getUserLinkAllowed('apoth_report_save_incomplete_new', $newDepsArray))
					 || !(ApotheosisLibAcl::getUserLinkAllowed('apoth_report_save_preview_new', $newDepsArray)) ) {
						$tmp->disable();
					}
					array_unshift($this->written[$student->pupilid], $tmp );
				}
			}
		}
		
		$this->layout = $layout;
		parent::display( 'students' );
	}
	
	function memberReports()
	{
		// create a fake report so we can know which fields have statements, are dropdowns, etc.
		$this->dummyReport = &ApothReport::newInstance( 0, $this->get('Group'), $this->get('CycleId') );
		$this->fields = $this->dummyReport->getInputFields( 'small' );
		
		$students = $this->get('Students');
		
		$this->someMembers( $students, 'report' );
		// **** need to sort out the slowness of this page... once it's all working
//		timer(false, false, 'print');
	}
}
?>