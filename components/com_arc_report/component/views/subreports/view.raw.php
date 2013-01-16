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
 * Report View Subreports
 *
 * @author     Punnet - Arc Team <arc_developers@pun.net>
 * @package    Arc
 * @subpackage Report
 * @since      1.8
 */
class ReportViewSubreports extends JView
{
	function display()
	{
		echo 'This page must be loaded via AJAX, not on its own';
	}
	
	function displayPage()
	{
		$scripts = array();
		$this->addTemplatePath( JPATH_COMPONENT.DS.'views'.DS.'subreport'.DS.'tmpl' );
		
		while( ( $this->subreport = $this->get( 'NextSubreport' ) ) ) {
			parent::display( 'subreport' );
			$scripts = array_merge( $scripts, $this->subreport->getJavascript( 'brief' ) );
		}
		echo '<div class="scriptnames" style="display: none;">'.json_encode( array_values( array_unique( $scripts ) ) ).'</div>';
	}
	
	function displaySingle()
	{
		$this->addTemplatePath( JPATH_COMPONENT.DS.'views'.DS.'subreport'.DS.'tmpl' );
		
		$this->subreport = $this->get( 'Subreport' );
		parent::display( 'subreport' );
	}
}
?>