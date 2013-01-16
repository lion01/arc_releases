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

jimport( 'joomla.application.component.view' );

/**
 * Report View Home
 *
 * @author     Punnet - Arc Team <arc_developers@pun.net>
 * @package    Arc
 * @subpackage Report
 * @since      1.8
 */
class ReportViewNav extends JView
{
	function __construct( $config = array() )
	{
		parent::__construct( $config );
		$this->scriptPath = JURI::base().'components'.DS.'com_arc_report'.DS.'views'.DS.'nav'.DS.'tmpl'.DS;
	}
	
	function displayBreadcrumbs()
	{
		$this->setLayout( 'raw' );
		parent::display( 'breadcrumbs' );
	}
}
?>