<?php
/**
 * @package     Arc
 * @subpackage  Homepage
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
 * Homepage Panels View
 * 
 * @author     p.walker@wildern.hants.sch.uk
 * @package    Arc
 * @subpackage Homepage
 * @since      1.5
 */
class HomepageViewPanels extends JView
{
	function __construct( $config = array() )
	{
		$document = &JFactory::getDocument();
		$document->setTitle( JText::_( 'Arc Homepage' ) );
		
		parent::__construct( $config );
	}
	
	/**
	 * html view holding method if accessed via menu
	 */
	function customise()
	{
		echo 'Customisation of homepage panels is currently only available on the homepage itself';
	}
	
	/**
	 * html view holding method if accessed via menu
	 */
	function clock()
	{
		echo 'The desktop clock and calendar is currently only available on the homepage itself';
	}
	
	/**
	 * html view holding method if accessed via menu
	 */
	function lotd()
	{
		echo 'The link of the day is currently only available on the homepage itself';
	}
	
	/**
	 * html view holding method if accessed via menu
	 */
	function people()
	{
		echo 'The person selector is currently only available on the homepage itself';
	}
	
	/**
	 * html view holding method if accessed via menu
	 */
	function genius()
	{
		echo 'The "genius" panel is only available on the homepage itself';
	}
	
	/**
	 * html view holding method if accessed via menu
	 */
	function wtv()
	{
		echo 'The "wtv" panel is only available on the homepage itself';
	}
	
	/**
	 * html view holding method if accessed via menu
	 */
	function quicklinks()
	{
		echo 'The "quick links" panel is only available on the homepage itself';
	}
}
?>