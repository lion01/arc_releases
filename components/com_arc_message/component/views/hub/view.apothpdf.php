<?php
/**
 * @package     Arc
 * @subpackage  Message
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
 * Message Hub Pdf View
 *
 * @author		lightinthedark <code@lightinthedark.org.uk>
 * @package		Apotheosis
 * @subpackage	Message
 * @since 0.1
 */
class MessageViewHub extends JView 
{
	/**
	 * Generates a pdf of the messages in the hub
	 */
	function display()
	{
		// Get model with its saved threads
		$model = &$this->getModel( 'hub' );
		
		// Set up PDF, and fonts
		$this->doc = &JFactory::getDocument();
		$this->doc->getInstance( 'apothpdf' );
		
		// Set up some pdf properties
		$this->pdf = &$this->doc->getEngine();
		$this->pdf->setPrintHeader( false );
		$this->pdf->setPrintFooter( false );
		$this->pdf->setFont( $this->doc->getFont(), '', 8 );
		$this->pdf->setHeaderMargin( 0 );
		$this->pdf->setFooterMargin( 0 );
		$this->pdf->setMargins( 15, 15, 15, true );
		$this->pdf->setAutoPageBreak( true, 15 );
		
		// Begin constructed output
		$this->setLayout( 'pdf' );
		$this->doc->startDoc();
		parent::display();
		$this->doc->endDoc();
	}
}
?>