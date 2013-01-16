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

jimport('joomla.application.component.controller');
jimport('joomla.application.helper');

/**
 * Reports Controller Admin
 *
 * @author		lightinthedark <code@lightinthedark.org.uk>
 * @package		Apotheosis
 * @subpackage	Reports
 * @since 0.1
 */
class ReportsControllerAdmin extends ReportsController
{
	/**
	 * Constructor
	 */
	function __construct( $config = array() )
	{
		parent::__construct( $config );
		// Register Extra tasks
		$this->registerTask( 'Assign', 'assign' );
		$this->registerTask( 'SetBlurbs', 'blurb' );
		$this->registerTask( 'SetPageStyle', 'layout' );
		$this->registerTask( 'SetFieldStyle', 'fields' );
		$this->registerTask( 'SetMarkStyle', 'marks' );
		$this->registerTask( 'Add', 'assignAdd');
		$this->registerTask( 'Remove', 'assignRemove' );
		
		// un-tuple courseid
		if( !is_null($cycleGroupTuple = JRequest::getVar('courseid')) ) {
			$cgArray = explode( '_', $cycleGroupTuple );
			$courseId = array_pop( $cgArray );
			JRequest::setVar( 'courseid', $courseId );
		}
		
		// un-tuple focus
		if( !is_null($cycleGroupTuple = JRequest::getVar('focus')) ) {
			$cgArray = explode( '_', $cycleGroupTuple );
			$focus = array_pop( $cgArray );
			JRequest::setVar( 'focus', $focus );
		}
		
		// un-tuple ogroup
		if( !is_null($cycleGroupTuple = JRequest::getVar('ogroup')) ) {
			$cgArray = explode( '_', $cycleGroupTuple );
			$ogroup = array_pop( $cgArray );
			JRequest::setVar( 'ogroup', $ogroup );
		}
	}
	
	function display()
	{
		// the list model is pulled in for the sake of setting up re-viewing the lists. Not used in admin views
		$listModel = &$this->getModel( 'lists' );
		$c = $listModel->getCycleId();
		
		// get the model, and check it's relevant to the selected group.
		// if it isn't, get a new one
		$model = &$this->getModel( 'admin', array('cycle'=>$c) );
		$view = &$this->getView( 'admin', JRequest::getVar('format', 'html') );
		
		$oldGroup = $model->getGroup();
		$uGroup = JRequest::getVar('courseid', false);
		$lGroup = $listModel->getGroup();
		if( $uGroup !== false ) {
			$newGroup = $uGroup;
		}
		elseif( $lGroup != ApotheosisLibDb::getRootItem('#__apoth_cm_courses') ) {
			$newGroup = $lGroup;
		}
		
		if( isset($newGroup) && ($oldGroup != $newGroup) ) {
			$this->_session->clear( 'admin' );
			$model = &$this->getModel( 'admin' );
			$model->setGroup( $newGroup );
			$listModel->setGroup( $newGroup );
		}
		
		$baseGroup = $model->getGroup();
		$focus = JRequest::getVar('focus', false);
		$heritage = ApotheosisLibDb::getAncestors($baseGroup, '#__apoth_cm_courses');
		$view->heritage = $heritage;
		
//		var_dump_pre($heritage, 'heritage');
//		var_dump_pre(JRequest::getWord('scope'), 'scope');
		foreach($heritage as $h) {
			$group = $h->id;
			$model->setGroup( $group );
			
			$view->setModel($model, true);
			$view->link = $this->_getLink().'&focus='.$c.'_'.$group;
			// enable / disable the forms based on if the user is allowed to administrate the current group
			$model->setEnabled( ApotheosisLibAcl::checkDependancy('report.groups', $c.'_'.$group) );
			
			if( $model->getGroup() === false ) {
				$scope = 'selectCourse';
			}
			else {
				$report = &$model->getReport(); // this fake report gets created on setting of the group
				$scope = JRequest::getWord('scope');
			}
			
			switch( $scope ) {
			case('associate'):
				// The group tree is currently limited to one instance per page,
				// so it is shown only for the relevant group, not all groups in the heritage
				if( $group == $focus ) {
					$view->associate();
				}
				break;
			
			case('assignAdmins'):
				$view->assignAdmins();
				break;
			
			case('assignPeers'):
				$view->assignPeers();
				break;
			
			case('blurb'):
				$model->setFields( $report->getBlurbFields() );
				$fields = &$model->getFields();
				$settings = $model->getSettings();
				foreach($fields as $k=>$field) {
					$col = $field->getColumn();
					$fields[$k]->setValue( $settings->$col );
				}
				$view->blurb();
				break;
			
			case('addStatement'):
			case('editStatement'):
				// The statement edit screen needs to only show the selected statement for the relevant group, not all groups in the heritage
				if( $group == $focus ) {
					global $mainframe;
					$tmpl = $mainframe->getTemplate();
					$doc = &JFactory::getDocument();
					$doc->addStyleSheet( 'templates'.DS.$tmpl.DS.'css'.DS.'nomenu.css' );
					$doc->addScript( JURI::base().'administrator'.DS.'components'.DS.'com_arc_core'.DS.'libraries'.DS.'js'.DS.'variable.php_serializer.js' );
					$model->setFields( $report->getStatementFields() );
					
					$view->editStatement( ($scope == 'addStatement') );
				}
				break;
			
			case('orderStatements'):
				if( $group == $focus ) {
					$model->setFields( $report->getStatementFields() );
					
					$view->orderStatements();
				}
				break;
			
			case('exportStatements'):
				if( $group == $focus ) {
					$model->setFields( $report->getStatementFields() );
					
					$view->exportStatements();
				}
				break;
			
			case('importStatements'): // *** check $CSVerrors
				global $mainframe;
				$fields = JRequest::getVar('fields');
				$formErrors = false;
				if( $group == $focus ) {
					if( JRequest::getVar('upload', false) === false ) {
						$model->setFields( $report->getStatementFields() );
					}
					else {
						if( empty($fields) ) {
							$mainframe->enqueueMessage( 'Please select at least one checkbox', 'warning' );
							$formErrors = true;
						}
						if( !$_FILES['filename']['tmp_name'] ) {
							$mainframe->enqueueMessage( 'Please select a CSV to upload', 'warning' );
							$formErrors = true;
						}
						
						if ( !$formErrors ) {
							$this->uploadStatements($fields);
						}
					}
					$view->importStatements();
				}
				break;
			
			case('statements'):
				global $mainframe;
				$doc = &JFactory::getDocument();
				$doc->addScript( JURI::base().'includes'.DS.'js'.DS.'joomla.javascript.js' );
				$doc->addScript( JURI::base().'administrator'.DS.'components'.DS.'com_arc_core'.DS.'libraries'.DS.'js'.DS.'variable.php_serializer.js' );
				$doc->addScript( JURI::base().'components'.DS.'com_arc_report'.DS.'views'.DS.'admin'.DS.'tmpl'.DS.'default_statements.js' );
				
				$model->setFields( $report->getStatementFields() );
				$view->statements();
				break;
			
			case('statistics'):
				$view->statistics();
				break;
			
			case('layout'):
				$view->layout();
				break;
			
			case('fields'):
				if( $group == $focus ) {
					$view->fields();
				}
				break;
			
			case('marks'):
				$view->marks();
				break;
			
			case('selectCourse'):
				$view->selectCourse();
				break;
			
			default:
				$view->links['assignAdmins'] = $this->_getLink( array('scope'=>'assignAdmins') ).'&focus='.$c.'_'.$group;
				$view->links['assignPeers' ] = $this->_getLink( array('scope'=>'assignPeers' ) ).'&focus='.$c.'_'.$group;
				$view->links['associate'   ] = $this->_getLink( array('scope'=>'associate'   ) ).'&focus='.$c.'_'.$group;
				$view->links['statements'  ] = $this->_getLink( array('scope'=>'statements'  ) ).'&focus='.$c.'_'.$group;
				$view->links['layout'      ] = $this->_getLink( array('scope'=>'layout'      ) ).'&focus='.$c.'_'.$group;
				$view->links['fields'      ] = $this->_getLink( array('scope'=>'fields'      ) ).'&focus='.$c.'_'.$group;
				$view->links['marks'       ] = $this->_getLink( array('scope'=>'marks'       ) ).'&focus='.$c.'_'.$group;
				$view->links['blurb'       ] = $this->_getLink( array('scope'=>'blurb'       ) ).'&focus='.$c.'_'.$group;
				$view->links['statistics'  ] = $this->_getLink( array('scope'=>'statistics'  ) ).'&focus='.$c.'_'.$group;
				$view->options();
			}
		}
		
		$model->setGroup( $baseGroup );
		$listModel->setGroup( $baseGroup );
		$this->saveModel( 'admin' );
		$this->saveModel( 'lists' );
	}
	
	/**
	 * Uploads and processes a CSV of statements
	 *
	 * @param $importFields array  The array of field names whose statements we should import from the file
	 */
	function uploadStatements( $importFields )
	{
		global $mainframe;
		$listModel = &$this->getModel( 'lists' );
		$c = $listModel->getCycleId();
		$model = &$this->getModel( 'admin', array('cycle'=>$c) );
		$g = $model->getGroup();
		
		$fieldTypes = &$model->getFields();
		$headerDefaults = array('id'=>null, 'keyword'=>null, 'text'=>null, 'range_min'=>null, 'range_max'=>null, 'range_of'=>null, 'color'=>null);
		$CSVerrors = false;
		$insertStatements = array();
		$insertMaps = array();
		$updateStatements = array();
		$updateMaps = array();
		$unusedStatements = 0;
		$blankRows = 0;
		
		$rawCSV = $_FILES['filename']['tmp_name'];
		$rawContents = ApotheosisLib::file_get_contents_utf8( $rawCSV );
		$cleaned = str_replace( array("\r\n", "\r"), "\n", $rawContents );
		$cleanCSV = tmpfile();
		fwrite( $cleanCSV, $cleaned );
		rewind( $cleanCSV );
		
		while( $data = fgetcsv($cleanCSV, 2048) ) {
			if( implode('', $data) == '' ) {
				$blankRows++;
			}
			// Header row has 'group_id:'
			if( count(preg_grep('/^~group_id:/i', $data)) != 0 ) {
				$useGroup = false;
				$matches = 0;
				$len = count( $data );
				for( $i = 0; $i < $len; $i++ ) {
					if( preg_match('/(?<=^~group_id:)\d+/i', $data[$i], $matches) ) {
						$group_id = $matches[0];
						$useGroup = ( $group_id == $g ); // *** let superadmins ignore this check
						$i = $len;
					}
				}
				if( $useGroup ) {
					if( !isset($allValid[$group_id]) ) {
						$db = &JFactory::getDBO();
						$sql = 'SELECT '.$db->nameQuote('statement_id')
							."\n".' FROM '.$db->nameQuote('jos_apoth_rpt_statements_map')
							."\n".' WHERE '.$db->nameQuote('group_id').' = '.$db->Quote($g)
							."\n".'   AND '.$db->nameQuote('cycle_id').' = '.$db->Quote($c);
						$db->setQuery($sql);
						
						$allValid[$group_id] = $db->loadObjectList('statement_id');
					}
					unset($valid);
					$valid = &$allValid[$group_id];
				}
				else {
					$mainframe->enqueueMessage( '~group_id: has not been set or is incorrect ('.htmlspecialchars($group_id).')', 'error' );
					$CSVerrors = true;
				}
			}
			// Field header row has '~field:'
			elseif( count(preg_grep('/^~field:/i', $data)) != 0 ) {
				$useField = false;
				$matches = 0;
				$len = count( $data );
				for( $i = 0; $i < $len; $i++ ) {
					if( preg_match('/(?<=^~field:)\w+/i', $data[$i], $matches) ) {
						$field = $matches[0];
						if( !isset($order[$field]) ) {
							$order[$field] = 0;
						}
						$useField = array_key_exists($field, $importFields);
						$i = $len;
					}
				}
				
				if( !$useField && !array_key_exists($field, $fieldTypes) ) {
					$mainframe->enqueueMessage( '~field: is wrong or has not been set ('.htmlspecialchars($field).')', 'error' );
					$CSVerrors = true;
				}
			}
			// Statement header row has '~Text:'
			elseif( count(preg_grep('/^~Text/i', $data)) != 0 ) {
				$headerArray = $headerDefaults;
				foreach( $data as $k=>$v ) {
					$header = strtolower( substr($v, 1) );
					if( array_key_exists($header, $headerDefaults) ) {
						$headerArray[$header] = $k;
					}
					else {
						$mainframe->enqueueMessage( 'A statement header is incorrectly set ('.htmlspecialchars($header).')', 'error' );
						$CSVerrors = true;
					}
				}
			}
			// This must therefore be a statement row
			elseif( $useGroup && $useField ) {
				$statementObj = new stdClass();
				$statementMapObj = new stdClass();
				foreach( $headerArray as $colName=>$colNum ) {
					$statementObj->$colName = ( (!is_null($colNum) && isset($data[$colNum])) ? $data[$colNum] : null );
				}
				
				$statementObj->field = $field;
				$statementMapObj->group_id = $group_id;
				$statementMapObj->cycle_id = $c;
				$statementMapObj->order = $order[$field]++;
				
				if( isset($valid[$statementObj->id]) ) { // updating
					$statementMapObj->statement_id = $statementObj->id;
					$updateStatements[] = $statementObj;
					$updateMaps[] = $statementMapObj;
					unset( $valid[$statementObj->id] );
				}
				else { // inserting
					$statementObj->id = NULL;
					$insertStatements[] = $statementObj;
					$insertMaps[] = $statementMapObj;
				}
			}
			else {
				$unusedStatements++;
			}
		} // end of while
		
		if( !empty($insertStatements) ) {
			$db = &JFactory::getDBO();
			$sql = 'SELECT MAX('.$db->nameQuote('id').') FROM '.$db->nameQuote('jos_apoth_rpt_statements');
			
			$db->setQuery($sql);
			$oldMaxId = $db->loadResult();
			
			ApotheosisLibDb::insertList( '#__apoth_rpt_statements', $insertStatements );
			
			$db->setQuery($sql);
			$newMaxId = $db->loadResult();
			
			if( ($newMaxId - $oldMaxId) == count($insertStatements) ) {
				$insertMapsIndex = 0;
				for( $i = ($oldMaxId + 1); $i <= $newMaxId; $i++ ) {
					$insertMaps[$insertMapsIndex]->statement_id = $i;
					$insertMapsIndex++;
				}
				ApotheosisLibDb::insertList( '#__apoth_rpt_statements_map', $insertMaps );
			}
		}
		
		if( !empty($updateStatements) ) {
			ApotheosisLibDb::updateList( '#__apoth_rpt_statements', $updateStatements, array('id') );
			ApotheosisLibDb::updateList( '#__apoth_rpt_statements_map', $updateMaps, array('statement_id', 'group_id', 'cycle_id') );
		}
		
		//queue warning messages
		if( $unusedStatements != 0 ) {
			$mainframe->enqueueMessage( 'Number of unprocessed statements: '.$unusedStatements, 'warning' );
		}
		if( $blankRows != 0 ) {
			$mainframe->enqueueMessage( 'Number of empty rows in CSV file: '.$blankRows, 'warning' );
		}
		
		//queue success messages
		foreach( $order as $k=>$v ) {
			if( array_key_exists($k, $importFields) ) {
				$mainframe->enqueueMessage( 'Number of "'.$k.'" statements processed: '.$v, 'message' );
			}
		}
	}
	
	function setTwin()
	{
		$this->_saveChanges( 'setTwin' );
	}
	
	function assignAdd()
	{
		switch( JRequest::getCmd('scope') ){
		case( 'assignAdmins' ):
			$this->_saveChanges( 'assignAdminsAdd' );
			break;
		
		case( 'assignPeers' ):
			$this->_saveChanges( 'assignPeersAdd' );
			break;
		}
	}
	
	function assignRemove()
	{
		switch( JRequest::getCmd('scope') ){
		case( 'assignAdmins' ):
			$this->_saveChanges( 'assignAdminsRemove' );
			break;
		
		case( 'assignPeers' ):
			$this->_saveChanges( 'assignPeersRemove' );
			break;
		}
	}
	
	function blurb()
	{
		$this->_saveChanges( 'blurb' );
	}
	
	function layout()
	{
		$this->_saveChanges( 'layout' );
	}
	
	function fields()
	{
		$this->_saveChanges( 'fields' );
	}
	
	function marks()
	{
		$this->_saveChanges( 'marks' );
	}
	
	function deleteStatements()
	{
		$this->_saveChanges( 'deleteStatements' );
	}
	
	function addStatement()
	{
		$this->_saveChanges( 'addStatement' );
	}
	
	function saveStatement()
	{
		$this->_saveChanges( 'saveStatement' );
	}
	
	function saveOrder()
	{
		$this->_saveChanges( 'saveOrder' );
	}
	
	function _saveChanges( $action )
	{
		$msg = '';
		$actionName = '';
		ob_start();
		global $mainframe;
		$model = &$this->getModel( 'admin' );
		$baseGroup = $model->getGroup();
		if( ($g = JRequest::getVar('focus', false)) !== false ) {
			$model->setGroup( $g );
		}
		
		switch( $action ) {
		case( 'setTwin' ):
			$go   = JRequest::getVar( 'inherit',   false );
			$twin = JRequest::getVar( 'groups'.$g, false );
			if( ($go !== false) && ($twin !== false) ) {
				$res = $model->setTwin( $twin );
				if( $res == true ) {
					$mainframe->enqueueMessage( 'Twinning relationship set', 'message' );
				}
				else {
					$mainframe->enqueueMessage( 'There was a problem saving the inheritance settings', 'error' );
				}
			}
			else {
				$res = $model->setTwin();
				$mainframe->enqueueMessage( 'This group now has no twin associated with it.', 'warning' );
				$mainframe->enqueueMessage( 'Either the "inherit" check box was not checked, or an invalid twin was selected.', 'warning' );
			}
			break;
		
		case( 'assignAdminsAdd' ):
			$res = $model->addUsers( 'admin', JRequest::getVar('candidates', array()) );
			if( $res['errors'] == 0 ) {
				$mainframe->enqueueMessage( $res['added'].' new administrators added', 'message' );
			}
			else {
				$mainframe->enqueueMessage( 'There was a problem saving the new administrator assignments', 'error' );
			}
			break;
		
		case( 'assignAdminsRemove' ):
			$res = $model->removeUsers( 'admin', JRequest::getVar('admins', array()) );
			if( $res['errors'] == 0 ) {
				$mainframe->enqueueMessage( $res['deleted'].' administrators removed', 'message' );
			}
			else {
				$mainframe->enqueueMessage( 'There was a problem saving the new administrator assignments', 'error' );
			}
			break;
		
		case( 'assignPeersAdd' ):
			$res = $model->addUsers( 'peer', JRequest::getVar('candidates', array()) );
			if( $res['errors'] == 0 ) {
				$mainframe->enqueueMessage( $res['added'].' new peer checkers added', 'message' );
			}
			else {
				$mainframe->enqueueMessage( 'There was a problem saving the new peer checker assignments', 'error' );
			}
			break;
		
		case( 'assignPeersRemove' ):
			$res = $model->removeUsers( 'peer', JRequest::getVar('peers', array()) );
			if( $res['errors'] == 0 ) {
				$mainframe->enqueueMessage( $res['deleted'].' peer checkers removed', 'message' );
			}
			else {
				$mainframe->enqueueMessage( 'There was a problem saving the new peer checker assignments', 'error' );
			}
			break;
		
		case( 'blurb' ):
			$model->setPrintName( JRequest::getVar('subjectname') );
			$fields = &$model->getFields();
			$vals = array();
			foreach( $fields as $f ) {
				$n = $f->getName();
				$vals[$f->getColumn()] = JRequest::getVar($n, false);
			}
			$res = $model->setBlurbs( $vals );
			if( $res['errors'] == 0 ) {
				$mainframe->enqueueMessage( 'New descriptions saved', 'message' );
			}
			else {
				$mainframe->enqueueMessage( 'There was a problem saving the new descriptions', 'error' );
			}
			if( JRequest::getVar('submit', false) != false ) {
				$actionName = 'apoth_report_admin_intro';
			}
			elseif( JRequest::getVar('submitPreview', false) != false ) {
				$actionName = 'apoth_report_admin_intro_preview';
			}
			break;
		
		case( 'layout' ):
			$res = $model->setPageStyle( JRequest::getVar('layout'.$g, false) );
			if( $res['errors'] == 0 ) {
				$mainframe->enqueueMessage( 'New page style saved', 'message' );
			}
			else {
				$mainframe->enqueueMessage( 'There was a problem saving the new page style', 'error' );
			}
			break;
		
		case( 'fields' ):
			$res = $model->setFieldStyle();
			if( $res['errors'] == 0 ) {
				$mainframe->enqueueMessage( 'New field styles saved', 'message' );
			}
			else {
				$mainframe->enqueueMessage( 'There was a problem saving the new field style', 'error' );
			}
			break;
		
		case( 'marks' ):
			$res = $model->setMarkStyle( JRequest::getVar('marks'.$g, false) );
			if( $res['errors'] == 0 ) {
				$mainframe->enqueueMessage( 'New mark style saved', 'message' );
			}
			else {
				$mainframe->enqueueMessage( 'There was a problem saving the new mark style', 'error' );
			}
			break;
		
		case( 'deleteStatements' ):
			$fields = &$model->getFields();
			$fName = JRequest::getVar('field');
			if( array_key_exists( $fName, $fields ) ) {
				$field = &$fields[$fName];
				$statements = &$field->getStatementBank();
				$statementIds = unserialize( JRequest::getVar('statement', 'a:0:{}') );
				foreach($statementIds as $v) {
					$tmp = $statements->deleteStatement($v);
					if( $tmp !== true ) {
						$mainframe->enqueueMessage( $tmp, 'warning' );
					}
					else {
						$mainframe->enqueueMessage( 'Deleted statement' );
					}
				}
			}
			else {
				$mainframe->enqueueMessage( 'Could not modify statements for field '.$fName.'<br />', 'error' );
			}
			$actionName = 'apoth_report_admin_statements';
			break;
		
		case( 'saveStatement' ):
			$fields = &$model->getFields();
			$fName = JRequest::getVar('field');
			if( array_key_exists($fName, $fields) ) {
				$field = &$fields[$fName];
				$statements = &$field->getStatementBank();
				$statement = ApotheosisLib::mb_unserialize( JRequest::getVar('statement', '\N') );
				if( is_null($statement) ) {
					$mainframe->enqueueMessage( 'Could not save statement due to a javascript error.', 'error' );
					$mainframe->enqueueMessage( 'Please check you are logged in, reload the page and try again.', 'error' );
					break;
				}
				
				// retrospectively update the reports if appropriate
				$retro = ( (JRequest::getVar( 'retro', false ) == 'true') ? true : false);
				$old = $statements->getStatementText($statement->id);
				if( $retro ) {
					$updateCount = $model->updateReports( $old, $statement->text, $statement->field, $g );
					$mainframe->enqueueMessage( 'Updated '.$updateCount.' reports containing old text with the new text.', 'message' );
				}
				$res = $statements->updateStatement( $statement );
				
				if( $res['errors'] == 0 ) {
					$mainframe->enqueueMessage( 'Statement saved', 'message' );
					$mainframe->enqueueMessage( 'Statement was: '.htmlspecialchars($old), 'message' );
					$mainframe->enqueueMessage( 'Statement now: '.htmlspecialchars($statement->text), 'message' );
				}
				else {
					$mainframe->enqueueMessage( 'There was a problem saving the new statements', 'error');
				}
			}
			else {
				$mainframe->enqueueMessage( 'Could not modify statements for field '.$fName.'<br />', 'error' );
			}
			$actionName = 'apoth_report_admin_statements';
			break;
		
		case( 'addStatement' ):
			$fields = &$model->getFields();
			$fName = JRequest::getVar('field');
			if( array_key_exists( $fName, $fields ) ) {
				$field = &$fields[$fName];
				$statements = &$field->getStatementBank();
				$statement = ApotheosisLib::mb_unserialize( JRequest::getVar('statement', '\N') );
				if( is_null($statement) ) {
					$mainframe->enqueueMessage( 'Could not save statement due to a javascript error.', 'error' );
					$mainframe->enqueueMessage( 'Please check you are logged in, reload the page and try again.', 'error' );
					break;
				}
				
				$res = $statements->addStatement( $statement );
				
				if( $res == true ) {
					$mainframe->enqueueMessage( 'New statement added', 'message' );
				}
				else {
					$mainframe->enqueueMessage( 'There was a problem saving the new statement', 'error');
				}
			}
			else {
				$mainframe->enqueueMessage( 'Could not modify statements for field '.$fName.'<br />', 'error' );
			}
			$actionName = 'apoth_report_admin_statements';
			break;
		
		case( 'saveOrder' ):
			$fields = &$model->getFields();
			$fName = JRequest::getVar('field');
			if( array_key_exists($fName, $fields) ) {
				$field = &$fields[$fName];
				$statements = &$field->getStatementBank();
				$order = JRequest::getVar('order');
				$oldOrder = JRequest::getVar('oldOrder');
				$sorted = array();
				while( !empty($order) ) {
					$lowVal = reset($order);
					$lowKey = key($order);
					foreach( $order as $k=>$v ) {
						if( ($v < $lowVal)
						 || (($v == $lowVal) && ($oldOrder[$k] < $oldOrder[$lowKey])) ) {
							$lowVal = $v;
							$lowKey = $k;
						}
					}
					$sorted[] = $lowKey;
					unset( $order[$lowKey] );
				}
				
				$res = $statements->orderStatements( $sorted );
				
				if( $res === false ) {
					$mainframe->enqueueMessage( 'There was a problem re-ordering the statement bank', 'error' );
				}
				elseif( $res == 0 ) {
					$mainframe->enqueueMessage( 'Statements saved but no order changes were made', 'warning');
				}
				else {
					$mainframe->enqueueMessage( 'The statement bank was successfully re-ordered', 'message');
				}
			}
			else {
				$mainframe->enqueueMessage( 'Could not re-order statements for field '.$fName.'<br />', 'error' );
			}
			$actionName = 'apoth_report_admin_st_order';
			
			break;
		}
		
		$this->saveModel();
		$model->setGroup( $baseGroup );
		if( ($msg .= ob_get_clean()) != '' ) {
			$mainframe->enqueueMessage( $msg, 'message' );
		}
		$mainframe->redirect( ApotheosisLib::getActionLinkByName($actionName, array('report.groups'=>$model->getCycleId().'_'.$g, 'report.fields'=>$fName)) );
	}
}
?>