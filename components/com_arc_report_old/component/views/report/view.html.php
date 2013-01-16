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
 * Reports Report View
 *
 * @author		lightinthedark <code@lightinthedark.org.uk>
 * @package		Apotheosis
 * @subpackage	Reports
 * @since 0.1
 */
class ReportsViewReport extends JView 
{
	/**
	 * Constructor
	 */
	function __construct( $config = array() )
	{
		parent::__construct( $config );
		$this->_varMap = array('state'=>'State');
	}
	
	/**
	 * Displays a generic report view page
	 *
	 * @param string $template  Optional name of the template to use
	 */
	function display( $tpl = NULL )
	{
		$this->_varMap['report'] = 'Report';
		ApotheosisLib::setViewVars($this, $this->_varMap);
		
		$this->bullet = ApothReportLib::getBulletText();
		
		$lister = &$this->getModel( 'lists' );
		
		switch( JRequest::getVar('repscope') ) {
		case( 'pupil' ):
			$this->_pupilNavigation( $lister );
			break;
			
		case( 'group' ):
			$this->_classNavigation( $lister );
			break;
			
		default:
			$this->members = array();
		}
		
		$this->student   = $this->report->getStudent();
		$this->cycle     = $this->report->getCycle();
		$this->group     = $this->report->getGroup();
		$this->doneFonts = array();
		
		parent::display( $tpl );
	}
	
	function _pupilNavigation($lister)
	{
		$needle = new stdClass();
		$needle->id = $this->report->getGroup();
		$rawCourses = $lister->getStudentCourses();
		foreach( $rawCourses as $k=>$v ) {
			if( ($v->type == 'non') || (!empty($v->_children)) ) {
				unset( $rawCourses[$k] );
			}
		}
		$haystack = $rawCourses;
		$matches = ApotheosisLibArray::array_search_partial( $needle, $haystack );
		$match = (empty($matches) ? '' : reset($matches));
		$this->members = array();
		$cur = reset($haystack);
		if( array_key_exists($match, $haystack) ) { // just to be sure we don't get an infinite loop
			while( key($haystack) != $match ) {
				$cur = next($haystack);
			}
		}
		
		// get previous, current, and next groups
		if( ($prev = prev($haystack)) !== false) {
			$this->members[] = $prev;
			next($haystack);
		}
		else {
			reset($haystack);
		}
		if( ($cur = current($haystack)) !== false ) {
			$this->members[] = $cur;
		}
		if( ($next = next($haystack)) !== false ) {
			$this->members[] = $next;
		}
	}
	
	function _classNavigation($lister)
	{
		$needle = new stdClass();
		$needle->pupilid = $this->report->getStudent();
		$haystack = $lister->getStudents();
		$matches = ApotheosisLibArray::array_search_partial( $needle, $haystack );
		$match = (empty($matches) ? '' : reset($matches));
		$this->members = array();
		$cur = reset($haystack);
		if( array_key_exists($match, $haystack) ) { // just to be sure we don't get an infinite loop
			while( key($haystack) != $match ) {
				$cur = next($haystack);
			}
		}
		
		// get previous, current, and next pupils
		if( ($prev = prev($haystack)) !== false ) {
			$this->members[] = $prev;
			next($haystack);
		}
		else {
			reset($haystack);
		}
		if( ($cur = current($haystack)) !== false ) {
			$this->members[] = $cur;
		}
		if( ($next = next($haystack)) !== false ) {
			$this->members[] = $next;
		}
	}
	
	function statementPicker( $tpl = NULL )
	{
		$this->_varMap['report'] = 'Report';
		$this->_varMap['mergeStart'] = 'MergeStart';
		$this->_varMap['mergeEnd']   = 'MergeEnd';
		ApotheosisLib::setViewVars($this, $this->_varMap);
		
		$this->bullet = ApothReportLib::getBulletText();
		
		$fieldName = JRequest::getVar( 'field', false );
		$this->field = $this->report->getField( $fieldName );
		$bank = &$this->field->getStatementBank();
		$this->statements = $bank->getStatements( true );
		
		foreach( $this->statements as $k=>$v ) {
			$str = $this->_models['report']->mergeText( $v->text );
			$this->statements[$k]->text = $str;
		}
		
		$this->layout = 'pick';
		parent::display( 'statement_picker' );
	}
	
	function statementFinisher( $tpl = NULL )
	{
		$this->_varMap['report'] = 'Report';
		$this->_varMap['mergeStart'] = 'MergeStart';
		$this->_varMap['mergeEnd']   = 'MergeEnd';
		ApotheosisLib::setViewVars($this, $this->_varMap);
		
		$this->bullet = ApothReportLib::getBulletText();
		
		$fieldName = JRequest::getVar( 'field', false );
		$this->field = $this->report->getField( $fieldName );
		
		$this->layout = 'finish';
		parent::display( 'statement_picker' );
	}
	
	function feedback( $tpl = NULL )
	{
		$this->_varMap['report'] = 'Report';
		ApotheosisLib::setViewVars($this, $this->_varMap);
		
		parent::display( 'feedback' );
	}
}
?>