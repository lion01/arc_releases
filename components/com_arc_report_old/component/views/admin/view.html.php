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
 * Reports Admin View
 *
 * @author		lightinthedark <code@lightinthedark.org.uk>
 * @package		Apotheosis
 * @subpackage	Reports
 * @since 0.1
 */
class ReportsViewAdmin extends JView 
{
	/**
	 * Constructor
	 */
	function __construct( $config = array() )
	{
		parent::__construct( $config );
		$this->_varMap = array('state'=>'State', 'group'=>'Group', 'groupName'=>'GroupName', 'enabled'=>'Enabled');
	}
	
	/**
	 * Displays a generic page
	 * (for when there are no actions or selected registers)
	 *
	 * @param string $template  Optional name of the template to use
	 */
	function options( $tpl = NULL )
	{
		ApotheosisLib::setViewVars($this, $this->_varMap);
		
		parent::display( );
	}
	
	function associate()
	{
		ApotheosisLib::setViewVars($this, $this->_varMap);
		
		$this->nodeLink = $this->link.'&task=loadnode&format=xml';
		parent::display( 'associate' );
	}
	
	function assignAdmins()
	{
		$this->_varMap['admins'] = 'Admins';
		$this->_varMap['adminCandidates'] = 'AdminCandidates';
		ApotheosisLib::setViewVars($this, $this->_varMap);
		
		$this->adminsNames = array();
		foreach($this->admins as $v) {
			$this->adminsNames[] = $v->displayname;
		}
		
		parent::display( 'assign_admins' );
		
	}
	
	function assignPeers()
	{
		$this->_varMap['peers'] = 'Peers';
		$this->_varMap['peerCandidates'] = 'PeerCandidates';
		ApotheosisLib::setViewVars($this, $this->_varMap);
		
		$this->peersNames = array();
		foreach($this->peers as $v) {
			$this->peersNames[] = $v->displayname;
		}
		
		parent::display('assign_peers');
	}
	
	function layout()
	{
		$this->_varMap['style'] = 'PageStyle';
		$this->_varMap['styles'] = 'PageStyles';
		ApotheosisLib::setViewVars($this, $this->_varMap);
		
		foreach ($this->styles as $k=>$v) {
			$link = ApotheosisLib::getActionLinkByName( 'apoth_report_generate_preview', array('report.groups'=>$this->get('CycleId').'_'.$this->group, 'report.reports'=>'NULL', 'report.styles'=>$v) );
			$obj = new stdClass();
			$obj->name = $v;
			$obj->display = $v.' ... <a class="modal" href="'.$link.'" rel="{handler: \'iframe\', size: {x: 700, y: 500}}" target="_blank">See example</a>';
			$this->styles[$k] = $obj;
		}
		
		if( !empty($this->heritage[$this->group]->_parents) ) {
			$inherit = new stdClass();
			$inherit->name = '';
			$inherit->display = 'Inherit page style from';
			array_unshift($this->styles, $inherit);
		}
		
		parent::display( 'page_style' );
	}
	
	function marks()
	{
		$this->_varMap['style'] = 'MarkStyle';
		$this->_varMap['stylesTmp'] = 'MarkStyles';
		ApotheosisLib::setViewVars($this, $this->_varMap);
		
		$this->styles = array();
		foreach ($this->stylesTmp as $k=>$v) {
			$obj = new stdClass();
			$obj->name = $k;
			$obj->display = ucfirst($k).' - '.$v;
			$this->styles[] = $obj;
		}
		
		if( !empty($this->heritage[$this->group]->_parents) ) {
			$inherit = new stdClass();
			$inherit->name = '';
			$inherit->display = 'Inherit mark style from';
			array_unshift($this->styles, $inherit);
		}
		
		parent::display( 'mark_style' );
	}
	
	function fields()
	{
		$this->_varMap['report'] = 'Report';
		ApotheosisLib::setViewVars($this, $this->_varMap);
		
		$this->fields = $this->report->getFields();
		
		parent::display( 'field_style' );
	}
	
	function blurb()
	{
		$this->_varMap['report'] = 'Report';
		$this->_varMap['fields'] = 'Fields';
		ApotheosisLib::setViewVars($this, $this->_varMap);
		
		$this->description = reset($this->fields);
		$this->coursework  = next($this->fields);
		parent::display( 'blurbs' );
	}
	
	function statistics()
	{
		ApotheosisLib::setViewVars($this, $this->_varMap);
		parent::display( 'stats' );
	}
	
	function editStatement( $new )
	{
		$this->_varMap['mergeWords'] = 'MergeWords';
		$this->_varMap['mergeStart'] = 'MergeStart';
		$this->_varMap['mergeEnd']   = 'MergeEnd';
		$this->_varMap['fields'] = 'Fields';
		ApotheosisLib::setViewVars($this, $this->_varMap);
		$this->formName = 'statForm_'.$this->group;
		
		$this->field = &$this->fields[JRequest::getCmd( 'field' )];
		$this->bank = &$this->field->getStatementBank();
		$this->statFields = $this->bank->getFields( JRequest::getVar('statementId', false) );
		
		$this->new = $new;
		
		$doc = &JFactory::getDocument();
		$doc->addStyleSheet( 'administrator'.DS.'components'.DS.'com_arc_core'.DS.'libraries'.DS.'css'.DS.'mooRainbow.css' );
		
		parent::display( 'statement_edit' );
	}
	
	function orderStatements()
	{
		$this->_varMap['fields'] = 'Fields';
		ApotheosisLib::setViewVars($this, $this->_varMap);
		$this->formName = 'orderForm_'.$this->group;
		
		$this->field = &$this->fields[JRequest::getCmd( 'field' )];
		
		parent::display( 'statements_order' );
	}
	
	function exportStatements()
	{
		$this->_varMap['fields'] = 'Fields';
		ApotheosisLib::setViewVars($this, $this->_varMap);
		$this->formName = 'exportForm_'.$this->group;
		
		parent::display( 'statements_export' );
	}
	
	function importStatements()
	{
		$this->_varMap['fields'] = 'Fields';
		ApotheosisLib::setViewVars($this, $this->_varMap);
		$this->importFields = JRequest::getVar('fields', array() );
		$this->formName = 'importForm_'.$this->group;
		
		parent::display( 'statements_import' );
	}
	
	function statements()
	{
		$this->_varMap['fields'] = 'Fields';
		ApotheosisLib::setViewVars($this, $this->_varMap);
		$this->formName = 'statForm_'.$this->group;
		
		parent::display( 'statements' );
	}
	
	function selectCourse()
	{
		echo 'You need to select a course first<br />';
	}
}
?>
