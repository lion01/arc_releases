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
		$pId = JRequest::getVar( 'pId', null );
		$fName = JRequest::getVar( 'file', null );
		$this->file = ApotheosisPeopleData::getFileName( $pId, $fName );
		
		if( $this->file == false ) {
			JError::raiseError( '404', 'no such file' );
		}
		
		$doc = &JFactory::getDocument();
		$doc->setFileName( $fName );
		
		JResponse::setHeader('Content-length', filesize( $this->file ), true);
		JResponse::sendHeaders();
		
		$this->setLayout( 'file' );
		parent::display();
	}
}
?>
