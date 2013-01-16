<?php
/**
 * @package     Arc
 * @subpackage  TV
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
 * TV Video View
 *
 * @author     Lightinthedark <code@lightinthedark.org.uk>
 * @package    Arc
 * @subpackage TV
 * @since      1.5
 */
class TvViewVideo extends JView
{
	function __construct()
	{
		$document = &JFactory::getDocument();
		$document->setTitle( JText::_('Arc TV') );
		$this->addPath = JURI::base().'components'.DS.'com_arc_tv'.DS.'views'.DS.'video'.DS.'tmpl'.DS;
		
		parent::__construct();
	}
	
	/**
	 * Display the home page
	 */
	function home()
	{
		$this->model = &$this->getModel();
		
		$this->mainView = 'video';
		$this->curVideo = &$this->get( 'Video' );
		$this->vidDivTitle = 'Video of the Week...';
		$this->wrapperType = 'recommended';
		$this->wrapperDivTitle = 'Recommended for you...';
		
		$this->sidebarDivTitle = $this->get( 'SidebarTitle' );
		$this->tagCloud = &$this->get( 'TagCloud' );
		$this->tagCloudDivTitle = 'Tags...';
		
		parent::display();
	}
	
	/**
	 * Display the video page
	 */
	function video()
	{
		$this->model = &$this->getModel();
		
		$this->mainView = 'video';
		$this->curVideo = &$this->get( 'Video' );
		$this->vidDivTitle = $this->curVideo->getDatum( 'title' );
		$this->wrapperType = 'recommended'; // **** will be comments eventually
		$this->wrapperDivTitle = 'Recommended for you...'; // **** will be comments eventually
		
		$this->sidebarDivTitle = $this->get( 'SidebarTitle' );
		$this->tagCloud = &$this->get( 'TagCloud' );
		$this->tagCloudDivTitle = 'Tags...';
		
		parent::display();
	}
	
	/**
	 * Display the search page
	 */
	function search( $searchDivTitle )
	{
		$this->model = &$this->getModel();
		$this->searchPageCount = $this->model->getPageCount( 'searched' );
		
		$this->mainView = 'search';
		$this->searchDivTitle = $searchDivTitle;
		if( $this->searchPageCount == 0 ) {
			$this->wrapperType = 'recommended';
			$this->wrapperDivTitle = 'Recommended for you...';
		}
		
		$this->sidebarDivTitle = $this->get( 'SidebarTitle' );
		$this->tagCloud = &$this->get( 'TagCloud' );
		$this->tagCloudDivTitle = 'Tags...';
		
		parent::display();
	}
	
	/**
	 * Display the video management page
	 */
	function manage()
	{
		$this->model = &$this->getModel();
		
		$this->mainView = 'manage';
		$this->curVideo = &$this->get( 'Video' );

		$this->manageDivTitle = ( $this->curVideo->getId() < 0 ) ? 'Upload a video...' : 'Manage a video...';
		
		$this->sidebarDivTitle = $this->get( 'SidebarTitle' );
		$this->tagCloud = &$this->get( 'TagCloud' );
		$this->tagCloudDivTitle = 'Tags...';
		
		$this->siteId = $this->get( 'SiteId' );
		$this->filters = $this->curVideo->getFilters();
		$this->moderate = ApotheosisLibAcl::getUserLinkAllowed('arc_tv_moderate', array()) ? true : false;
		
		$this->submitted = ( $this->curVideo->getDatum('status') == ARC_TV_PENDING );
		
		parent::display();
	}
}
?>