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
 * Report Controller Writecheck
 *
 * @author     Punnet - Arc Team <arc_developers@pun.net>
 * @package    Arc
 * @subpackage Report
 * @since      1.8
 */
class ReportControllerWritecheck extends ReportController
{
	/**
	 * Find cycles and events with which the current user is involved
	 * and display them in due-date order grouped by cycle
	 */
	function display()
	{
		$model = $this->getModel( 'writecheck' );
		$view = &$this->getView ( 'writecheck', 'html' );
		$navView = &$this->getView ( 'nav', 'html' );
		
		$fCrumbs = ApothFactory::_( 'core.breadcrumb', $this->getVar( 'fCrumbs' ) );
		$fCrumbs->addBreadCrumb( ARC_REPORT_CRUMB_TRAIL, 'Write & Check', array( 'action'=>'apoth_report_writecheck' ) );
		$this->saveVar( 'fCrumbs', $fCrumbs, ApothFactory::getIncFile( 'core.breadcrumb' ) );
		
		$model->setCycles();
		$model->setWriteProgress();
		$model->setCheckProgress();
		
		$navView->display();
		$view->setModel( $model, true );
		$view->display();
		
//		$this->saveModel();
	}
}
?>