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

jimport( 'joomla.application.component.controller' );
jimport( 'joomla.application.helper' );
require_once( JPATH_SITE.DS.'administrator'.DS.'components'.DS.'com_arc_core'.DS.'helpers'.DS.'lib_sync.php' ); 
require_once( JPATH_SITE.DS.'administrator'.DS.'components'.DS.'com_arc_attendance'.DS.'helpers'.DS.'sync.php' ); 

/**
 * Reports Controller Output
 *
 * @author		lightinthedark <code@lightinthedark.org.uk>
 * @package		Apotheosis
 * @subpackage	Reports
 * @since 0.1
 */
class ReportsControllerOutput extends ReportsController
{
	/**
	 * Constructor
	 */
	function __construct( $config = array() )
	{
		parent::__construct( $config );
		
		// un-tuple ogroup
		if( !is_null($cycleGroupTuple = JRequest::getVar('ogroup')) ) {
			$cgArray = explode( '_', $cycleGroupTuple );
			$oGroup = array_pop( $cgArray );
			JRequest::setVar( 'ogroup', $oGroup );
		}
	}
	
	function display()
	{
		$model1 = &$this->getModel( 'output' );
		$model2 = &$this->getModel( 'lists' );
		
		$view = &$this->getView ( 'output', JRequest::getWord( 'format', 'html' ) );
		$view->setModel( $model1, true );
		$view->setModel( $model2 );
		
		$doc = &JFactory::getDocument();
		$doc->addScript( JURI::base().'administrator/components/com_arc_core/libraries/js/variable.php_serializer.js' );
		
		switch('scope') {
		case('data'):
			$view->display();
			break;
		
		case('select'):
		default:
			$view->display();
			break;
		}
		$this->saveModel( 'output' );
		$this->saveModel( 'lists' );
	}
	
	function generate()
	{
//		timer( 'cont - start generation');
		$listModel = &$this->getModel( 'lists' );
		$c = $listModel->getCycleId();
		$model1 = &$this->getModel( 'output' );
		$model2 = &$this->getModel( 'report' );
		
		$requirements = array( 'cycle'=>array($c) );
		if( ($tmp = JRequest::getVar( 'ocourse',  false )) !== false ) { $requirements['course' ] = $tmp; }
		if( ($tmp = JRequest::getVar( 'ogroup',   false )) !== false ) { $requirements['group'  ] = $tmp; }
		if( ($tmp = JRequest::getVar( 'opupil',   false )) !== false ) { $requirements['pupil'  ] = $tmp; }
		if( ($tmp = JRequest::getVar( 'otutor',   false )) !== false ) { $requirements['tutor'  ] = $tmp; }
		if( ($tmp = JRequest::getVar( 'omember',  false )) !== false ) { $requirements['member' ] = $tmp; }
		if( ($tmp = JRequest::getVar( 'ocourse2', false )) !== false ) { $requirements['course2'] = $tmp; }
		if( ($tmp = JRequest::getVar( 'reportid', false )) !== false ) { $requirements['reportId'] = $tmp; }
		
		$view = &$this->getView ( 'output', JRequest::getWord( 'format', 'apothpdf' ) );

		$view->setModel( $model1, true );
		$view->setModel( $model2 );
		if( ($tmp = JRequest::getVar( 'bycourse', false )) !== false ) { $requirements['getBy'] = 'course'; }
		else { $requirements['getBy'] = 'tutor'; }
		
		$style = JRequest::getVar( 'style', false );
		
//		timer( 'cont - got requirements');
		if( isset($requirements['reportId']) && ($requirements['reportId'] == 'NULL') ) {
			if( !isset($requirements['pupil']) || !isset($requirements['group']) ) {
				$p = ( isset($requirements['pupil']) ? $requirements['pupil'] : false );
				$g = ( isset($requirements['group']) ? $requirements['group'] : false );
				$model1->setReportNew( $p, $g, $c, $style );
				$view->displayTemplate();
			}
			else {
				$model2->setReportNew( $requirements['pupil'], $requirements['group'], $c );
				$view->displayNew();
			}
		}
		else {
			$model2->setReports( $requirements, true );
//			timer( 'cont - set reports');
			$view->displayExisting();
		}
		$this->saveModel( 'output' );
	}
	
}
?>