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
	function panel()
	{
		$this->model = &$this->getModel();
		$this->profile = &$this->model->getProfile();
		$this->panel = JRequest::getVar( 'panel', 'links' );
		
		if( $this->profile == false ) {
			echo 'no profile found';
		}
		else {
			switch( $this->panel ) {
			case( 'links' ):
			case( 'showcase' ):
				$this->links = $this->profile->getLinks( $this->panel );
				parent::display( 'links' );
				break;
			
			case( 'awards' ):
				$this->awards = $this->profile->getAwards();
				parent::display( 'awards' );
				break;
			
			case( 'biography' ):
				$this->text = $this->profile->getBiography();
				parent::display( 'biography' );
				break;
			
			case( 'sen' ):
				$this->people = $this->get('SenPeople');
				parent::display( 'sen' );
				break;
				
			case( 'customise' ):
				$this->panels = ApotheosisData::_( 'homepage.panels' );
				parent::display( 'customise' );
				break;
			
			}
		}
	}
}
?>
