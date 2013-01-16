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

/**
 * Message Admin Twitter Controller
 *
 * @author     Punnet - Arc Team
 * @package    Arc
 * @subpackage Message
 * @since      1.6
 */
class MessageAdminControllerTwitter extends MessageAdminController
{
	/**
	 * Default method
	 */
	function display()
	{
		global $mainframe, $option;
		jimport('joomla.html.pagination');
		$model = &$this->getModel( 'twitter' );
		$view = &$this->getView( 'twitter', 'html' );
		$view->setModel( $model, true );
		$view->display();
	}
	
	/**
	 * Save changes to a course
	 */
	function save()
	{
		global $mainframe;
		$model = &$this->getModel( 'twitter' );
		$view = &$this->getView( 'twitter', 'html' );
		$view->setModel( $model, true );
		
		// Retrieve data for twitter configuration 
		$data = JRequest::getVar( 'params' );
		
		// Save the configuration data
		$save = $model->saveParams( $data );
		
		if( $save ) {
			$mainframe->enqueueMessage( 'Settings were successfully saved' );
		}
		else {
			$mainframe->enqueueMessage( 'There was a problem saving the settings, please try again', 'error' );
		}
		$this->display();
	}
	
	/**
	 * Generate request token and redirect user to Twitter to authorize
	 */
	function displayAuth()
	{
		$model = &$this->getModel( 'twitter' );
		$view = &$this->getView( 'twitter', 'html' );
		$view->setModel( $model, true );
		
		// Retrieve data for twitter configuration 
		$data = JRequest::getVar( 'params' );
		
		// Save the configuration data
		$save = $model->saveParams( $data );
		
		if( !$save ) {
			$mainframe->enqueueMessage( 'There was a problem saving the settings, please try again', 'error' );
			$this->display();
			return;
		}
		
		// now the settings are saved, use them to perform oauth authorisation
		try{
			if( !$model->setRequestToken() ) {
				$mainframe->enqueueMessage( 'The callback url was not handled correctly', 'error' );
				$this->display();
				return;
			}
			
			$session = &JFactory::getSession();
			$session->set( 'twitter_model', serialize($model) );
			$view->displayAuth();
		}
		catch( OAuthException $E ) {
			$view->displayAuthError( $E );
		}
	}
	
	/**
	 * Catches the callback from Twitter and trades the authorization token for an access token
	 * which is then stored in the component's parameters
	 */
	function handleCallback()
	{
		require_once( JPATH_COMPONENT_ADMINISTRATOR.DS.'models'.DS.'twitter.php' );
		$session = &JFactory::getSession();
		$model = unserialize( $session->get( 'twitter_model' ) );
		$view = &$this->getView( 'twitter', 'html' );
		$view->setModel( $model, true );
		
		try{
			$model->setOauthToken( JRequest::getVar( 'oauth_token' ) );
			$model->setAccessToken();
			
			$p = &$model->getParams();
			$p->set( 'token', $model->getOauthToken() );
			$p->set( 'tokenSecret', $model->getOauthSecret() );
			$model->saveParams();
			
			global $mainframe;
			$mainframe->enqueueMessage( 'Welcome back from Twitter. Access token is set, everyting looks good' );
			
			$view->display();
		}
		catch( OAuthException $E ) {
			$view->displayAuthError( $E );
		}
	}
}
?>