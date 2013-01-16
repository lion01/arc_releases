<?php
/**
 * @package     Arc
 * @subpackage  Behaviour
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
 * Behaviour Reports Output View
 *
 * @author		lightinthedark <code@lightinthedark.org.uk>
 * @package		Apotheosis
 * @subpackage	Behaviour
 * @since 0.1
 */
class BehaviourViewReports extends JView 
{
	/**
	 * Displays a generic page
	 */
	function display()
	{
		// Get list of series Ids
		$this->report = &$this->get('report');
		if( is_object($this->report) ) {
			$this->seriesIds = $this->report->getSeriesIds();
		}
		else {
			$this->seriesIds = array();
		}
		
		// Set up PDF, and fonts
		$this->doc = &JFactory::getDocument();
		$this->doc->getInstance( 'apothpdf' );
		
		// Set up some pdf properties
		$this->pdf = &$this->doc->getEngine();
		$this->pdf->setPrintHeader( false );
		$this->pdf->setPrintFooter( false );
		$this->pdf->setFont( $this->doc->getFont(), '', 8 );
		$this->pdf->setHeaderMargin( 0 );
		$this->pdf->setFooterMargin( 0 );
		$this->pdf->setMargins( 15, 15, 15, true );
		$this->pdf->setAutoPageBreak( true, 15 );
		$this->scaleFactor = $this->pdf->getScaleFactor();
		
		// Begin constructed output
		$this->setLayout( 'pdf' );
		$this->doc->startDoc();
		parent::display();
		$this->doc->endDoc();
	}
	
	function _getGraphLink( $sIds, $w, $h1, $h2, $labels = false )
	{
		// Generate graph image and save to tmp folder
		$datFileName = $this->report->getDataFile( $sIds );
		$tmp = $_GET;
		$_GET = array(
			'w' => $w, // width
			'h1' => $h1, // height of main graph
			'h2' => $h2, // height of blobs
			'file' => base64_encode( $datFileName ), // path to image file
			'labels' => $labels, // labels or not
			'write' => 1 // write img to file system
		);
		include_once( 'tmpl/graph.php' );
		ArcGraphBehaviour::main();
		$_GET = $tmp;
		
		// Link to graph image
		return $datFileName.'.png';
	}
}
?>