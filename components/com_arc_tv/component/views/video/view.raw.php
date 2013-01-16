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
	function frameFile()
	{
		$this->curVideo = $this->get( 'Video' );
		
		$this->setLayout( 'manageframe' );
		parent::display( 'file' );
	}
	
	/**
	 * Show a homepage panel containing VotW 
	 */
	function panel()
	{
		$this->curVideo = $this->get( 'Video' );
		
		parent::display( 'video_player' );
	}
	
	/**
	 * Retrieve the updated manage status div
	 */
	function updateStatus()
	{
		$this->curVideo = $this->get( 'Video' );
		
		parent::display( 'manage_status' );
	}
	
	function sidebar()
	{
		$this->sidebarContentsOnly = true;
		$this->sidebarDivTitle = $this->get( 'SidebarTitle' );
		
		parent::display( 'sidebar' );
	}
}
?>