<?php
/**
 * @package     Arc
 * @subpackage  API
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
 * API OAuth View
 *
 * @author     Punnet - Arc Team <arc_developers@pun.net>
 * @package    Arc
 * @subpackage API
 * @since      1.6.1
 */
class ApiViewOauth extends JView
{
	function requestToken()
	{
		$document = &JFactory::getDocument();
		$document->setTitle(JText::_( 'Arc API Request Token' ));
		
		$tmp = $this->get( 'RequestTokenInfo' );
		$this->assignRef( 'tokenInfo', $tmp );
		
		parent::display( 'token' );
	}
	
	function invalidToken()
	{
		$document = &JFactory::getDocument();
		$document->setTitle(JText::_( 'Arc API Invalid Token' ));
		
		parent::display( 'invalid_token' );
	}
	
	function authRequest()
	{
		$document = &JFactory::getDocument();
		$document->setTitle(JText::_( 'Arc API Authorisation Requested' ));
		
		$this->token = JRequest::getVar( 'oauth_token' );
		
		parent::display( 'authorise' );
	}
	
	function authGranted()
	{
		$document = &JFactory::getDocument();
		$document->setTitle(JText::_( 'Arc API Authorisation Granted' ));
		
		$tmp = $this->get( 'TokenVerifier' );
		$this->assignRef( 'verifier', $tmp );
		
		parent::display( 'auth_granted' );
	}
	
	function authRefused()
	{
		$document = &JFactory::getDocument();
		$document->setTitle(JText::_( 'Arc API Authorisation Refused' ));
		
		parent::display( 'auth_refused' );
	}
	
	function AccessToken()
	{
		$document = &JFactory::getDocument();
		$document->setTitle(JText::_( 'Arc API Access Token' ));
		
		$tmp = $this->get( 'AccessTokenInfo' );
		$this->assignRef( 'tokenInfo', $tmp );
		
		parent::display( 'token' );
	}
	
}
?>
