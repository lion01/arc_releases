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
 * Behaviour Manager Reports View
 * *** Most of this view is copied straight from the view.html . Might be nice to make them share the utility functions
 *
 * @author		lightinthedark <code@lightinthedark.org.uk>
 * @package		Apotheosis
 * @subpackage	Behaviour
 * @since 0.1
 */
class BehaviourViewReports extends JView 
{
	/**
	 * Show the main behaviour reporting view
	 */
	function display()
	{
		$this->report = $this->get('report');
		if( is_object($this->report) ) {
			$this->seriesIds = $this->report->getSeriesIds();
		}
		else {
			$this->seriesIds = array();
		}
		$this->setLayout( 'panel' );
		parent::display();
	}
	
	function _getGraphLink( $sIds, $h1, $h2, $labels = true )
	{
		$graphLink = JURI::Base().'components'.DS.'com_arc_behaviour'.DS.'views'.DS.'reports'.DS.'tmpl'.DS.'graph.php?w=%1$s&h1=%2$s&h2=%3$s&file=%4$s&labels=%5$s';
		$datFileName = $this->report->getDataFile( $sIds );
		return sprintf( $graphLink, 200, $h1, $h2, base64_encode( $datFileName ), ($labels ? 1 : 0) );
	}
	
}
?>
