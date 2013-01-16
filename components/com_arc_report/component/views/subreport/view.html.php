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
		$this->subreport = &$this->get( 'Subreport' );
		$this->scripts = array_merge( $this->subreport->getJavascript( 'brief' ), $this->subreport->getJavascript( 'more' ) );
		
		$this->scriptPath = JURI::base().'components'.DS.'com_arc_report'.DS.'views'.DS.'subreport'.DS.'tmpl'.DS;
		
		parent::display();
	}
	
	function displayFeedback()
	{
		$this->subreport = &$this->get( 'Subreport' );
		
		parent::display( 'feedback' );
	}
	
	function displayFeedbackSaved()
	{
		$this->subreport = &$this->get( 'Subreport' );
		
		parent::display( 'feedbackSaved' );
	}
	
	function displayStatements()
	{
		$this->setLayout( 'statements' );
		
		$this->subreport = &$this->get( 'Subreport' );
		$this->field = &$this->get( 'Field' );
		
		$this->scriptPath = JURI::base().'components'.DS.'com_arc_report'.DS.'views'.DS.'subreport'.DS.'tmpl'.DS;
		
		parent::display();
	}
}
?>