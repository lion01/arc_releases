<?php
/**
 * @package    Apotheosis
 * @subpackage Behaviour
 * @copyright Copyright (C) 2005 Open Source Matters. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 * Apotheosis is free software. This version may have been modified pursuant
 * to the GNU General Public License, and as distributed it includes or
 * is derivative of works licensed under the GNU General Public License or
 * other free or open source software licenses.
 * See COPYRIGHT.php for copyright notices and details.
 */

// no direct access
defined('_JEXEC') or die('Restricted access');

jimport('joomla.application.component.view');

/**
 * Behaviour Manager Reports View
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
		$this->report = $this->get( 'report' );
		if( is_object($this->report) ) {
			if( $this->report->getSeriesStatus() === false ) {
				$this->seriesIds = false;
			}
			elseif( $this->report->getSeriesStatus() === 0 ) {
				$this->seriesIds = array();
			}
			else {
				$this->seriesIds = $this->report->getSeriesIds();
			}
		}
		else {
			$this->seriesIds = null;
		}
		parent::display();
	}
	
	function _getGraphLink( $sIds, $h1, $h2, $labels = true )
	{
		$graphLink = JURI::Base().'components'.DS.'com_arc_behaviour'.DS.'views'.DS.'reports'.DS.'tmpl'.DS.'graph.php?w=%1$s&h1=%2$s&h2=%3$s&file=%4$s&labels=%5$s';
		$datFileName = $this->report->getDataFile( $sIds );
		return sprintf( $graphLink, 900, $h1, $h2, base64_encode( $datFileName ), ($labels ? 1 : 0) );
	}
}
?>