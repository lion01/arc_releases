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
 * Report Controller Subreports
 *
 * @author     Punnet - Arc Team <arc_developers@pun.net>
 * @package    Arc
 * @subpackage Report
 * @since      1.8
 */
class ReportControllerSubreports extends ReportController
{
	/**
	 * Find cycles and events with which the current user is involved
	 * and display them in due-date order grouped by cycle
	 */
	function display()
	{
		plgSystemArc_log::startTimer( 'report controllerSubreports display' );
		$model = $this->getModel( 'subreports' );
		$view = &$this->getView ( 'subreports', 'html' );
		$navModel = $this->getModel( 'nav' );
		$navView = &$this->getView ( 'nav', 'html' );
		
		$activity = JRequest::getVar( 'activity', 'view');
		
		$navModel->setCycle( JRequest::getVar( 'cycle', null ) );
		$model->setCycle( JRequest::getVar( 'cycle', null ) );
		$navModel->setActivity( $activity );
		$model->setActivity( $activity );
		
		
		// Get the search requirements from any pre-submitted search form
		$searchGroups = JRequest::getVar( 'groups', false );
		$searchPeople = JRequest::getVar( 'people', false );
		
		$requirements = array();
		if( !empty( $searchGroups ) ) { $requirements['groups'] = $searchGroups; }
		if( !empty( $searchPeople ) ) { $requirements['people'] = $searchPeople; }
		
		$navModel->setFilterValues( $requirements, $fCrumbs );
		$model->setSearch( $navModel->getFilterValues() );
		
		if( ($model->getSubreportCount() == 1) ) {
			$r = $model->getNextSubreport();
			$rId = $r->getId();
			$link = ApotheosisLibAcl::getUserLinkAllowed( 'apoth_report_'.$activity.'_subreport', array( 'report.subreport'=>$rId ) );
			if( $link ) {
				plgSystemArc_log::stopTimer( 'report controllerSubreports display' );
				global $mainframe;
				$mainframe->redirect( $link );
			}
		}
		
		// Set up the breadcrumbs
		$fCrumbs = ApothFactory::_( 'core.breadcrumb' );
		$fCrumbs->setPersistent( 'instances',    true, ARC_PERSIST_ALWAYS );
		$fCrumbs->setPersistent( 'searches',     true, ARC_PERSIST_ALWAYS );
		$fCrumbs->setPersistent( 'structures',   true, ARC_PERSIST_ALWAYS );
		$fCrumbs->setPersistent( 'searchParams', true, ARC_PERSIST_ALWAYS );
		$model->resetBreadcrumbs( $fCrumbs );
		$navModel->resetFilterCrumbs( $fCrumbs );
		
		// finally display
		$view->nav = &$navView;
		$view->setModel( $model, true );
		$view->display();
		
		$this->saveModel( 'subreports' );
		$this->saveModel( 'nav' );
		plgSystemArc_log::stopTimer( 'report controllerSubreports display' );
	}
	
	function showpage()
	{
		plgSystemArc_log::startTimer( 'report controllerSubreports showpage' );
		$model = $this->getModel( 'subreports' );
		$view = &$this->getView ( 'subreports', jRequest::getVar( 'format', 'html' ) );
		$navModel = $this->getModel( 'nav' );
		
		$model->setSearch( $navModel->getFilterValues() );
		$ok = $model->setPage( JRequest::getVar( 'pageId', 0 ) );
		
		if( $ok ) {
			$view->setModel( $model, true );
			$view->displayPage();
		}
		else {
			echo '~~End~~';
		}
		$this->saveModel( 'subreports' );
		plgSystemArc_log::stopTimer( 'report controllerSubreports showpage' );
	}
	
	function showsingle()
	{
		plgSystemArc_log::startTimer( 'report controllerSubreports showsingle' );
		$model = $this->getModel( 'subreports' );
		$view = &$this->getView ( 'subreports', jRequest::getVar( 'format', 'html' ) );
		
		$ok = $model->setSubreport( JRequest::getVar( 'subreport', -1 ) );
		
		if( $ok ) {
			$view->setModel( $model, true );
			$view->displaySingle();
		}
		else {
			echo 'No Report';
		}
		plgSystemArc_log::stopTimer( 'report controllerSubreports showsingle' );
	}
	
}
?>