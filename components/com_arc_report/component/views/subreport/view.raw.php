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
 * Report View Subreport
 *
 * @author     Punnet - Arc Team <arc_developers@pun.net>
 * @package    Arc
 * @subpackage Report
 * @since      1.8
 */
class ReportViewSubreport extends JView
{
	function display()
	{
		echo 'This page must be loaded via AJAX, not on its own';
	}
	
	function displayMore()
	{
		$this->subreport = &$this->get( 'Subreport' );
		
		$this->setLayout( 'ajax' );
		
		parent::display( 'more' );
		$scripts = $this->subreport->getJavascript( 'more' );
		echo '<div class="scriptnames" style="display: none;">'.json_encode( array_values( array_unique( $scripts ) ) ).'</div>';
	}
}
?>