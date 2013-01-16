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

// Give us access to the joomla model class
jimport( 'joomla.application.component.model' );

/**
 * Message Admin Twitter Model
 *
 * @author     Punnet - Arc Team
 * @package    Arc
 * @subpackage Message
 * @since      1.6
 */
class MessageAdminModelTwitter extends ArcAdminModel
{
	function __construct( $config = array() )
	{
		$config['component'] = 'com_arc_message';
		parent::__construct( $config );
	}
	
	function setRequestToken()
	{
		$this->_setOauth( OAUTH_AUTH_TYPE_URI );
		$rt = $this->_oauth->getRequestToken( $this->_params->get( 'urlTwitReqTok' ).'?oauth_callback='.urlencode( $this->_params->get( 'callback' ) ) );
		$this->setOauthToken( $rt['oauth_token'] );
		$this->setOauthSecret( $rt['oauth_token_secret'] );
		return $rt['oauth_callback_confirmed'] == 'true';
	}
	
	function setAccessToken()
	{
		$this->_setOauth( OAUTH_AUTH_TYPE_URI );
		$this->_oauth->setToken( $this->getOauthToken(), $this->getOauthSecret() );
		$at = $this->_oauth->getAccessToken( $this->_params->get( 'urlTwitAccTok' ) );
		$this->setOauthToken( $at['oauth_token'] );
		$this->setOauthSecret( $at['oauth_token_secret'] );
	}
	
	function _setOauth( $authType )
	{
		if( !isset($this->_params) ) {
			$this->_loadParams();
		}
		
		$this->_oauth = new OAuth( $this->_params->get( 'consKey' ), $this->_params->get( 'consSecret' ), OAUTH_SIG_METHOD_HMACSHA1, $authType );
	}
	
	function setOauthToken( $val )
	{
		$this->_oauthToken = $val;
	}
	
	function setOauthSecret( $val )
	{
		$this->_oauthSecret = $val;
	}
	
	/**
	 * Get whatever token we have at the moment
	 */
	function getOauthToken()
	{
		if( !isset($this->_oauthToken) ) {
			$this->_oauthToken = $this->_params->get( 'token' );
		}
		return $this->_oauthToken;
	}
	
	/**
	 * Get whatever secret we have at the moment
	 */
	function getOauthSecret()
	{
		if( !isset($this->_oauthSecret) ) {
			$this->_oauthSecret = $this->_params->get( 'tokenSecret' );
		}
		return $this->_oauthSecret;
	}
	
	
	/**
	 * Get the url to direct the user to to authorize
	 */
	function getAuthUrl()
	{
		return $this->_params->get( 'urlTwitAuth' );
	}
	
	
	// #####  Getting data from Twitter  #####
	
	/**
	 * Pull down the most recent tweets of the authenticated user
	 */
	function getTweets()
	{
		$this->_setOauth( OAUTH_AUTH_TYPE_URI );
		$this->_oauth->setToken( $this->getOauthToken(), $this->getOauthSecret() );
		$this->_oauth->fetch( $this->_params->get( 'urlTwitTimeline' ) );
		return json_decode( $this->_oauth->getLastResponse() );
	}
	
	/**
	 * Pull down the user's screen name so the tweets can have trim_user and use less bandwidth
	 */
	function getTwitterAccount()
	{
		$this->_setOauth( OAUTH_AUTH_TYPE_URI );
		$this->_oauth->setToken( $this->getOauthToken(), $this->getOauthSecret() );
		$this->_oauth->fetch( $this->_params->get( 'urlTwitCredentials' ) );
		return json_decode( $this->_oauth->getLastResponse() );
	}
}
?>