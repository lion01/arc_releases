<?php
/**
 * @package     Arc
 * @subpackage  People
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
 * People Manager People View
 *
 * @author     Lightinthedark <code@lightinthedark.org.uk>
 * @package    Arc
 * @subpackage People manager
 * @since      1.5
 */
class PeopleViewProfile extends JView
{
	function display()
	{
		$this->model = &$this->getModel();
		$this->profile = &$this->model->getProfile();
		
		if( empty($this->profile) ) {
			echo 'No profiles found';
		}
		else {
			parent::display();
		}
	}
	
	function edit()
	{
		$this->model = &$this->getModel();
		$this->profile = &$this->model->getProfile();
		$this->scope = JRequest::getVar( 'scope', 'profile' );
		
		if( empty($this->profile) ) {
			echo 'No profile found';
		}
		else {
			$this->setLayout( 'edit' );
			switch( $this->scope ) {
			case( 'showcase' ):
				$this->files = true;
			case( 'links' ):
				$this->links = $this->profile->getLinks( $this->scope );
				parent::display( 'links' );
				break;
			
			default:
				parent::display( $this->scope );
				break;
			}
		}
	}
	
	/**
	 * Show the 'customise my homepage' edit interstital
	 */
	function panelEdit()
	{
		$this->panelSettings = $this->get( 'PanelSettings' );
		
		$u = &JFactory::getUser();
		$this->pId = $u->person_id;
		
		parent::display( 'edit' );
	}
}
?>
