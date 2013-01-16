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
 * Report Controller Home
 *
 * @author     Punnet - Arc Team <arc_developers@pun.net>
 * @package    Arc
 * @subpackage Report
 * @since      1.8
 */
class ReportControllerNav extends ReportController
{
	/**
	 * Find cycles and events with which the current user is involved
	 * and display them in due-date order grouped by cycle
	 */
	function display()
	{
		$params = json_decode( JRequest::getVar( 'params', '{}' ) );
		
		$model = $this->getModel( 'nav' );
		$view = &$this->getView ( 'nav', JRequest::getVar( 'format', 'html' ) );
		
		$fCrumbs = ApothFactory::_( 'core.breadcrumb' );
		$fCrumbs->setPersistent( 'instances',    true, ARC_PERSIST_ALWAYS );
		$fCrumbs->setPersistent( 'searches',     true, ARC_PERSIST_ALWAYS );
		$fCrumbs->setPersistent( 'structures',   true, ARC_PERSIST_ALWAYS );
		$fCrumbs->setPersistent( 'searchParams', true, ARC_PERSIST_ALWAYS );
		
		switch( JRequest::getVar( 'navElement' ) ) {
			case( 'filterList' ):
				$viewFunc = 'displayFilterList';
				$ident = $params->listIdent;
				unset( $params->listIdent );
				$requirements = (array)$params;
				$model->setListValues( $ident, $requirements );
				break;
			
			case( 'breadcrumbs' ):
				$viewFunc = 'displayBreadcrumbs';
				break;
		}
		
		$view->setModel( $model, true );
		$view->$viewFunc();
	}
	
	function setFilters()
	{
		$params = json_decode( JRequest::getVar( 'params', '{}' ), true );
		
		$model = $this->getModel( 'nav' );
		$view = &$this->getView ( 'nav', JRequest::getVar( 'format', 'html' ) );
		
		$fCrumbs = ApothFactory::_( 'core.breadcrumb' );
		$fCrumbs->setPersistent( 'instances',    true, ARC_PERSIST_ALWAYS );
		$fCrumbs->setPersistent( 'searches',     true, ARC_PERSIST_ALWAYS );
		$fCrumbs->setPersistent( 'structures',   true, ARC_PERSIST_ALWAYS );
		$fCrumbs->setPersistent( 'searchParams', true, ARC_PERSIST_ALWAYS );
		
		$model->setFilterValues( $params );
		$model->setFilterCrumbs( $fCrumbs );
		
		$view->setModel( $model, true );
		$view->displaySuccess();
		
		$this->saveModel();
	}
}
?>