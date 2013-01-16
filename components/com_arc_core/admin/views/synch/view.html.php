<?php
/**
 * @package     Arc
 * @subpackage  Core
 * @copyright   Copyright (C) 2005 Punnet. All rights reserved. See COPYRIGHT_ARC.txt
 * @license     http://www.gnu.org/licenses/gpl.html GNU/GPL. See LICENSE_ARC.txt
 * Arc is free software: you can redistribute it and/or modify
 * it under the terms of version 3 of the GNU General Public License
 * as published by the Free Software Foundation.
 * The disclaimer of warranty as stated in the GPL applies to this program
 */

defined( '_JEXEC' ) or die( 'Restricted access' );

// Give us access to the joomla view class
jimport('joomla.application.component.view');

/**
 * Core Admin Synch View
 *
 * @author     Punnet - Arc Team
 * @package    Arc
 * @subpackage Core
 * @since      1.6
 */
class CoreAdminViewSynch extends JView
{
	/**
	 * Provides the synch view class
	 */
	function __construct()
	{
		parent::__construct();
		JHTML::_( 'behavior.tooltip' );
		
		// Path to tmpl to aid inclusion of CSS and JS
		$this->addPath = JURI::base().'components'.DS.'com_arc_core'.DS.'views'.DS.'synch'.DS.'tmpl'.DS;
	}
	

	function displayBatches()
	{
		// Document
		$document = &JFactory::getDocument();
		$document->setTitle( JText::_('Arc - Core Manager') );
		
		// Add missing style declaration for the export image
		$exportButtonStyle = '.icon-32-export { background-image: url(templates'.DS.'khepri'.DS.'images'.DS.'toolbar'.DS.'icon-32-export.png); }';
		$document->addStyleDeclaration( $exportButtonStyle );
		
		// Set toolbar items for the page
		JToolBarHelper::title( JText::_( 'Core Manager: Global Synchronisation' ), 'config.png' );
		JToolBarHelper::custom( 'upload_page', 'upload', 'upload', 'Upload Data', true );
		JToolBarHelper::custom( 'import', 'export', 'export', 'Import', true );
		JToolBarHelper::deleteList();
		
		$queue = $this->get( 'Batches' );
		$this->assignRef( 'batches', $queue );
		
		// and display
		parent::display( 'batches' );
	}
	
	function displayBatch()
	{
		// Document
		$document = &JFactory::getDocument();
		$document->setTitle( JText::_('Arc - Core Manager') );
		
		// Set toolbar items for the page
		JToolBarHelper::title( JText::_( 'Core Manager: Batch data sources' ), 'config.png' );
		JToolBarHelper::back();
		
		$queue = $this->get( 'Queue' );
		$this->assignRef( 'queue', $queue );
		
		// and display
		parent::display( 'batch' );
	}
	
	function uploadPage()
	{
		// Document
		$document = &JFactory::getDocument();
		$document->setTitle( JText::_('Arc - Core Manager') );
		
		// Set toolbar items for the page
		JToolBarHelper::title( JText::_( 'Core Manager: Upload data files' ), 'config.png' );
		JToolBarHelper::custom( 'upload_files', 'upload', 'upload', 'Upload Files', false );
		JToolBarHelper::custom( 'display', 'back', 'back', 'Back', false );
		
		$this->model = &$this->getModel();
		$this->batches = $this->get( 'batches');
		$this->queue = $this->get( 'Queue' );
		
		// and display
		parent::display( 'upload' );
	}
}
?>