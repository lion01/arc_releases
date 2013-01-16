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

jimport('joomla.application.component.controller');
jimport('joomla.application.helper');

require_once( JPATH_COMPONENT.DS.'helpers'.DS.'apotheosis.php' );

/**
 * Content Component Controller
 *
 * @package		Joomla
 * @subpackage	Content
 * @since 1.5
 */
class ApotheosisControllerLuhn extends ApotheosisController
{
	function __construct( $config = array() )
	{
		parent::__construct( $config );
		$this->registerDefaultTask( 'luhnDefault' );
	}
	
	function luhnDefault()
	{
		$model = &$this->getModel( 'luhn' );
		$view  = &$this->getView ( 'luhn' );
		$view->link = $this->_getLink();
		
		$view->setModel( $model, true );
		$view->display();
	}
	
	function generate()
	{
		$model = &$this->getModel( 'luhn' );
		$view  = &$this->getView ( 'luhn' );
		
		$model->generateLuhn( JRequest::getVar( 'inVal', false ), '-' );
		$view->setModel( $model, true );
		$view->generate();
	}
	
	function check()
	{
		$model = &$this->getModel( 'luhn' );
		$view  = &$this->getView( 'luhn' );
		
		$model->checkLuhn( JRequest::getVar( 'inVal', false ), '-' );
		$view->setModel( $model, true );
		$view->check();
	}
}
?>