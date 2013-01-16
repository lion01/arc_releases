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

jimport('joomla.application.component.view');

/**
 * Reports Output View
 *
 * @author		lightinthedark <code@lightinthedark.org.uk>
 * @package		Apotheosis
 * @subpackage	Reports
 * @since 0.1
 */
class ReportsViewOutput extends JView 
{
	/**
	 * Constructor
	 */
	function __construct( $config = array() )
	{
		parent::__construct( $config );
		$this->_varMap = array('state'=>'State');
	}
	
	function displayExisting( $tpl = NULL )
	{
		ApotheosisLib::setViewVars($this, $this->_varMap);
		ApotheosisLib::setViewVars($this, $this->_varMap2, 'lists');
		$rModel = &$this->getModel( 'report' );
		$rModel->sortReports();
		$this->reports = &$rModel->getReports();
		$this->_display();
	}
	
	function displayNew( $tpl = NULL )
	{
		ApotheosisLib::setViewVars($this, $this->_varMap);
		ApotheosisLib::setViewVars($this, $this->_varMap2, 'lists');
		$rModel = &$this->getModel( 'report' );
		$rModel->sortReports();
		$this->reports = array($rModel->getReport());
		if( empty($this->reports) ) {
			$this->reports = array();
		}
		$this->_display();
	}


	function displayTemplate( $tpl = NULL )
	{
		ApotheosisLib::setViewVars($this, $this->_varMap);
		ApotheosisLib::setViewVars($this, $this->_varMap2, 'lists');
		$oModel = &$this->getModel( 'output' );
//		$rModel->sortReports();
		$this->reports = array($oModel->getReport());

		if( empty($this->reports) ) {
			$this->reports = array();
		}
		$this->_display();
	}


	/**
	 * Displays a generic page
	 * (for when there are no actions or selected registers)
	 *
	 * @param string $template  Optional name of the template to use
	 */
	function _display( $tpl = NULL )
	{
		$this->bullet = ApothReportLib::getBulletText();
		$this->setLayout('pdf');

		// Set up PDF, and fonts
		$this->doc = &JFactory::getDocument();
		$this->doc->getInstance('apothpdf');

		$this->doc->setTitle('Compulsory Title Area ...');
		$this->pdf = &$this->doc->getEngine();
		$this->pdf->setPrintHeader(false);
		$this->pdf->setPrintFooter(false);
		
		$this->font = $this->doc->getFont();
		$this->pdf->setFont($this->font, '', 8);
		
		// begin constructed output
		$this->doc->startDoc();
		parent::display( 'report_set' );
		$this->doc->endDoc();
		
	}

}
?>