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

/**
 * API OAuth Controller
 *
 * @author     Punnet - Arc Team <arc_developers@pun.net>
 * @package    Arc
 * @subpackage API
 * @since      1.6.1
 */
class ApiControllerOauth extends ApiController
{
	/**
	 * Default action.
	 * Calls appropriate display function
	 */
	function display()
	{
		$model = &$this->getModel( 'oauth' );
		$view =  &$this->getView( 'oauth', 'html' );
		
		$view->setModel( $model, true );
		$view->display();
	}
	
	/**
	 * Generate a request token for a consumer
	 * 
	 * Note that this must be done with OAUTH_AUTH_TYPE_FORM.
	 * see http://oauth.net/core/1.0a/#rfc.section.6.1.1
	 * Also, the action-identification looks at the url and can't handle seeing all the oauth fields
	 */
	function request_token()
	{
		$model = &$this->getModel( 'oauth' );
		$view =  &$this->getView( 'oauth', 'html' );
		
		$model->setProvider();
		
		if( $model->getError() ) { return; }
		
		$model->createToken();
		
		$view->setModel( $model, true );
		$view->requestToken();
	}
	
	/**
	 * Present the authorisation request to the user, or deal with their response
	 */
	function authorise()
	{
		$model = &$this->getModel( 'oauth' );
		$view =  &$this->getView( 'oauth', 'html' );
		$view->setModel( $model, true );
		
		$model->setToken( JRequest::getVar( 'oauth_token' ) );
		
		if( !$model->getTokenIsValid() ) {
			$view->invalidToken();
		}
		elseif( JRequest::getVar( 'decided', false ) ) {
			if( JRequest::getVar( 'grant_x', false ) !== false ) {
				$model->authoriseToken();
				global $mainframe;
				$callback = $model->getTokenCallback();
				if( $callback == 'oob' ) {
					$view->authGranted();
				}
				else {
					$mainframe->redirect( $model->getTokenCallback().'&oauth_token='.$model->getTokenId().'&oauth_verifier='.$model->getTokenVerifier() );
				}
			}
			else {
				$model->removeToken();
				$view->authRefused();
			}
		}
		else {
			$view->authRequest();
		}
	}
	
	/**
	 * Generate an access token for a consumer as a result of user authorisation
	 */
	function access_token()
	{
		$model = &$this->getModel( 'oauth' );
		$view =  &$this->getView( 'oauth', 'html' );
		$view->setModel( $model, true );
		
		$model->setProvider();
		
		$model->setToken(); // no arg means "get the token from the oauth provider"
		$model->removeToken();
		$model->createAccessToken();
		
		$view->accessToken();
	}
	
}
?>