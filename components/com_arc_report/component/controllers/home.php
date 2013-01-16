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
class ReportControllerHome extends ReportController
{
	/**
	 * Find cycles and events with which the current user is involved
	 * and display them in due-date order grouped by cycle
	 */
	function display()
	{
		$model = $this->getModel( 'home' );
		$view = &$this->getView ( 'home', 'html' );
		$navView = &$this->getView ( 'nav', 'html' );
		
		$fCrumbs = ApothFactory::_( 'core.breadcrumb', $this->getVar( 'fCrumbs' ) );
		$fCrumbs->sweepTrail( ARC_REPORT_CRUMB_TRAIL );
		$fCrumbs->addBreadCrumb( ARC_REPORT_CRUMB_TRAIL, 'Report', array( 'action'=>'apoth_report_home', 'style'=>'font-weight: bold;' ) );
		$this->saveVar( 'fCrumbs', $fCrumbs, ApothFactory::getIncFile( 'core.breadcrumb' ) );
		
		$model->setCycles();
		$model->setEvents();
		
		$navView->display();
		$view->setModel( $model, true );
		$view->display();
	}
}
?>