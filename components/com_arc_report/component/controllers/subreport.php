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

/**
 * Report Controller Subreport
 *
 * @author     Punnet - Arc Team <arc_developers@pun.net>
 * @package    Arc
 * @subpackage Report
 * @since      1.8
 */
class ReportControllerSubreport extends ReportController
{
	function display()
	{
		$model = $this->getModel( 'subreport' );
		$view = &$this->getView ( 'subreport', jRequest::getVar( 'format', 'html' ) );
		$navModel = $this->getModel( 'nav' );
		$navView = &$this->getView ( 'nav', 'html' );
		
		$activity = JRequest::getVar( 'activity', 'view');
		$model->setActivity( $activity );
		
		$model->setSubreport( JRequest::getVar( 'subreport', null ) );
		
		$fCrumbs = ApothFactory::_( 'core.breadcrumb' );
		$fCrumbs->setPersistent( 'instances',    true, ARC_PERSIST_ALWAYS );
		$fCrumbs->setPersistent( 'searches',     true, ARC_PERSIST_ALWAYS );
		$fCrumbs->setPersistent( 'structures',   true, ARC_PERSIST_ALWAYS );
		$fCrumbs->setPersistent( 'searchParams', true, ARC_PERSIST_ALWAYS );
		$crumb = $fCrumbs->addBreadCrumb( ARC_REPORT_CRUMB_TRAIL, 'Individual Report', ApotheosisLibAcl::getUserLinkAllowed( 'apoth_report_'.$this->get( 'Activity' ).'_subreport' ) );
		$fCrumbs->curtailTrail( ARC_REPORT_CRUMB_TRAIL, $crumb->getId() );
		
		$view->nav = &$navView;
		$view->setModel( $model, true );
		$view->display();
		$this->saveModel( 'nav' );
	}
	
	function showmore()
	{
		$model = $this->getModel( 'subreport' );
		$view = &$this->getView ( 'subreport', jRequest::getVar( 'format', 'raw' ) );
		
		$ok = $model->setSubreport( JRequest::getVar( 'subreport', null ) );
		
		if( $ok ) {
			$view->setModel( $model, true );
			$view->displayMore();
		}
		else {
			echo 'No Report';
		}
	}
	
	function save()
	{
//		echo 'saving';
//		var_dump( $_POST );
		$model = $this->getModel( 'subreport' );
		
		$subreportId = JRequest::getVar( 'subreport', null );
		$ok = $model->setSubreport( $subreportId );
		
		if( $ok ) {
			$canEdit = ApotheosisLibAcl::getUserLinkAllowed( 'apoth_report_ajax_save', array( 'report.subreport'=>$subreportId, 'report.commit'=>1, 'report.status'=>ARC_REPORT_STATUS_INCOMPLETE ) );
			
			if( $canEdit ) {
				$data = array();
				$raw = JRequest::get( 'post');
				foreach( $raw as $k=>$v ) {
					$matches = array();
					if( preg_match( '~^f_(.*)$~', $k, $matches ) ) {
						$data[$matches[1]] = $v;
					}
					
				}
				
				$model->updateSubreport( $data );
			}
			
			if( JRequest::getVar( 'commit' ) ) {
				$ok = $model->saveSubreport( JRequest::getVar( 'status', ARC_REPORT_STATUS_NASCENT ), null );
			}
			
			echo ( $ok ? 'saved' : 'failed' );
		}
		else {
			echo 'No Report';
		}
	}
	
	function feedback()
	{
		$model = $this->getModel( 'subreport' );
		$view = &$this->getView ( 'subreport', jRequest::getVar( 'format', 'html' ) );
		
		$ok = $model->setSubreport( JRequest::getVar( 'subreport', null ) );
		
		$view->setModel( $model, true );
		$view->displayFeedback();
	}
	
	function saveFeedback()
	{
		$model = $this->getModel( 'subreport' );
		$view = &$this->getView ( 'subreport', jRequest::getVar( 'format', 'html' ) );
		
		$ok = $model->setSubreport( JRequest::getVar( 'subreport', null ) );
		$ok = $ok && $model->saveSubreport( ARC_REPORT_STATUS_REJECTED, JRequest::getVar( 'comment', false ) );
		
		$view->setModel( $model, true );
		$view->displayFeedbackSaved();
	}
	
	function statementList()
	{
		$model = $this->getModel( 'subreport' );
		$view = &$this->getView ( 'subreport', jRequest::getVar( 'format', 'html' ) );
		
		$ok = $model->setSubreport( JRequest::getVar( 'subreport', null ) );
		$ok = $ok && $model->setField( JRequest::getVar( 'field', null ) );
		
		$view->setModel( $model, true );
		$view->displayStatements();
	}
}
?>