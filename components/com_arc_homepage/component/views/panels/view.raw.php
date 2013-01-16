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
	 * Show the 'my calendar' panel
	 */
	function clock()
	{
		$model = &$this->getModel( 'panels' );
		$this->calendar = $model->getHtmlCalendar( date('Y-m-d'), '' );
		
		$this->setLayout( 'panel' );
		parent::display( 'clock' );
	}
	
	/**
	 * Show the 'Select a person' panel
	 */
	function people()
	{
		$this->people = $this->get('PeopleList');
		$this->setLayout( 'panel' );
		parent::display( 'people' );
	}
	
	/**
	 * Show the 'wildern genius' panel
	 */
	function genius()
	{
		$this->setLayout( 'panel' );
		parent::display( 'genius' );
	}
	
	/**
	 * Show the 'link of the day' panel
	 */
	function lotd()
	{
		$this->setLayout( 'panel' );
		parent::display( 'lotd' );
	}
	
	/**
	 * Show the 'wildern tv' panel
	 */
	function wtv()
	{
		$this->setLayout( 'panel' );
		parent::display( 'wtv' );
	}
	
	/**
	 * Show the 'quick links' panel
	 */
	function quicklinks()
	{
		$this->setLayout( 'panel' );
		parent::display( 'quicklinks' );
	}
}
?>