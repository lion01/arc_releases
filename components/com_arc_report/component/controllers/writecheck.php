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
		$navModel = $this->getModel( 'nav' );
		$navView = &$this->getView ( 'nav', 'html' );
		
		$fCrumbs = ApothFactory::_( 'core.breadcrumb' );
		$fCrumbs->setPersistent( 'instances',    true, ARC_PERSIST_ALWAYS );
		$fCrumbs->setPersistent( 'searches',     true, ARC_PERSIST_ALWAYS );
		$fCrumbs->setPersistent( 'structures',   true, ARC_PERSIST_ALWAYS );
		$fCrumbs->setPersistent( 'searchParams', true, ARC_PERSIST_ALWAYS );
		$crumb = $fCrumbs->addBreadCrumb( ARC_REPORT_CRUMB_TRAIL, 'Write & Check', ApotheosisLibAcl::getUserLinkAllowed( 'apoth_report_writecheck' ) );
		$fCrumbs->curtailTrail( ARC_REPORT_CRUMB_TRAIL, $crumb->getId() );
		
		$model->setCycles();
		$model->setWriteProgress();
		$model->setCheckProgress();
		
		$view->nav = &$navView;
		$view->setModel( $model, true );
		$view->display();
		
		$this->saveModel( 'nav' );
	}
}
?>