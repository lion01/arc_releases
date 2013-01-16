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

jimport( 'joomla.application.component.model' );
require_once( JPATH_SITE.DS.'components'.DS.'com_arc_core'.DS.'libraries'.DS.'apoth_library.php' );
require_once( JPATH_SITE.DS.'components'.DS.'com_arc_api'.DS.'models'.DS.'objects.php' );

/**
 * API OAuth Model
 *
 * @author     Punnet - Arc Team <arc_developers@pun.net>
 * @package    Arc
 * @subpackage API
 * @since      1.6.1
 */
class ApiModelOauth extends JModel
{
	/** @var object  Consumer detail object */
	var $_consumer;

	var $_oauthError;

	function __construct( $config = array() )
	{
		parent::__construct( $config );
		$this->_oauthError = false;
		$this->_params = JComponentHelper::getParams( 'com_arc_api' );
	}

	function setProvider()
	{
		try {
			$this->_provider = new OAuthProvider();
				
			$this->_provider->consumerHandler(array($this,'lookupConsumer'));
			$this->_provider->timestampNonceHandler(array($this,'timestampNonceChecker'));
			$this->_provider->tokenHandler(array($this,'tokenHandler'));
			
			if( JRequest::getVar( 'task' ) == 'request_token' ) {
				$this->_provider->isRequestTokenEndpoint( true );
			}
			$this->_provider->setRequestTokenPath( JURI::base().$this->_params->get( 'urlReqTok' ) );  // No token needed for this end point
			$this->_provider->checkOAuthRequest();
		} catch( OAuthException $E ) {
			echo OAuthProvider::reportProblem($E);
			$this->_oauthError = true;
		}
	}

	// #####  OAuth callbacks  #####

	/**
	 * Check the timestamp is within acceptable limits
	 * and that the nonce has not been used before
	 *
	 * @param OAuthProvider $provider  The provider object whose context is to be used
	 */
	function timestampNonceChecker( $provider )
	{
		// timestamp checking
		$t = time();
		if( $provider->timestamp - 10 > $t || $provider->timestamp + 300 < $t ) {
			return OAUTH_BAD_TIMESTAMP;
		}
		
		// nonce checking
		$db = &JFactory::getDBO();
		$query = 'INSERT INTO #__apoth_api_nonce SET '.$db->nameQuote( 'nonce' ).' = '.$db->Quote( $provider->nonce );
		$db->setQuery( $query );
		$db->Query();
		if( $db->getErrorMsg() !== '' ) {
			return OAUTH_BAD_NONCE;
		}
		
		return OAUTH_OK;
	}

	/**
	 * Ensure the request tokens (not access tokens) are used correctly
	 * different length ids indicate different purposes (request / access)
	 *
	 * @param OAuthProvider $provider  The provider object whose context is to be used
	 */
	function tokenHandler( $provider )
	{
		$l = strlen($provider->token);
		$this->setToken( $provider->token );
		
		if( $l == 8 ) {
			// request token
			if( empty($this->_token) ) {
				return OAUTH_TOKEN_USED; // not made, or used and removed
			}
			else if( $this->_token->verification != $provider->verifier ) {
				return OAUTH_VERIFIER_INVALID; // verification codes don't match
			}
		}
		else if( $l == 20 ) {
			// access token
			if( empty($this->_token) ) {
				return OAUTH_TOKEN_REJECTED; // access token never made
			}
			else if( !is_null($this->_token->valid_to) && (strtotime($this->_token->valid_to) <= time()) ) {
				return OAUTH_TOKEN_REVOKED; // valid_to in the past == revoked
			}
		}
		else {
			// not one of our tokens, or isn't set
			return OAUTH_TOKEN_REJECTED;
		}
		$this->_provider->token_secret = $this->_token->secret;
		return OAUTH_OK;
	}

	/**
	 * Check the consumer is in our list and is enabled on this site
	 *
	 * @param OAuthProvider $provider  The provider object whose context is to be used
	 */
	function lookupConsumer( $provider )
	{
		$fCon = ApothFactory::_( 'api.consumer' );
		$r = $fCon->getInstances( array('key'=>$provider->consumer_key) );
		if( empty( $r ) ) {
			return OAUTH_CONSUMER_KEY_UNKNOWN;
		}
		else {
			$this->_consumer = $fCon->getInstance( reset($r) );
			if( !$this->_consumer->getEnabled() ) {
				return OAUTH_CONSUMER_KEY_REFUSED;
			}
		}
		$provider->consumer_secret = $this->_consumer->getSecret();
		return OAUTH_OK;
	}

	// #####  token handling / error reporting  #####

	function getError()
	{
		return $this->_oauthError;
	}

	/**
	 * Create a request token
	 */
	function createToken()
	{
		do {
			$token  = bin2hex( $this->_provider->generateToken(4)  );
			$secret = bin2hex( $this->_provider->generateToken(12) );
			// save token to db
			$db = &JFactory::getDBO();
				
			$query = 'INSERT INTO '.$db->nameQuote( '#__apoth_api_request_tokens' )
				.'SET '.$db->nameQuote( 'cons_id' ) .' = '.$db->Quote( $this->_consumer->getId() )
				."\n, ".$db->nameQuote( 'token' )   .' = '.$db->Quote( $token )
				."\n, ".$db->nameQuote( 'secret' )  .' = '.$db->Quote( $secret )
				."\n, ".$db->nameQuote( 'callback' ).' = '.( is_null($this->_provider->callback) ? 'NULL' : $db->Quote( $this->_provider->callback ) )
				."\n, ".$db->nameQuote( 'created' ) .' = '.$db->Quote( date( 'Y-m-d H:i:s' ) )
			;
			$db->setQuery( $query );
			$db->query();
		} while( stristr($db->getErrorMsg(), 'duplicate entry') !== false );
		$this->setToken( $token );
		return $db->getErrorMsg() == '';
	}
	
	function createAccessToken()
	{
		$personId = $this->_token->person_id;
		$date = date( 'Y-m-d H:i:s' );
		do {
			$token  = bin2hex( $this->_provider->generateToken(10) );
			$secret = bin2hex( $this->_provider->generateToken(20) );
			// save token to db
			$db = &JFactory::getDBO();
				
			$query = 'INSERT INTO '.$db->nameQuote( '#__apoth_api_access_tokens' )
				.'SET '.$db->nameQuote( 'cons_id' )   .' = '.$db->Quote( $this->_consumer->getId() )
				."\n, ".$db->nameQuote( 'person_id' ) .' = '.$db->Quote( $personId )
				."\n, ".$db->nameQuote( 'token' )     .' = '.$db->Quote( $token )
				."\n, ".$db->nameQuote( 'secret' )    .' = '.$db->Quote( $secret )
				."\n, ".$db->nameQuote( 'valid_from' ).' = '.$db->Quote( $date )
				."\n, ".$db->nameQuote( 'valid_to' )  .' = NULL'
			;
			$db->setQuery( $query );
			$db->query();
		} while( stristr($db->getErrorMsg(), 'duplicate entry') !== false );
		$this->setToken( $token );
		return $db->getErrorMsg() == '';
	}

	function setToken( $token = null )
	{
		if( is_null( $token ) && !is_null( $this->_provider ) ) {
			$token = $this->_provider->token;
		}
		switch( strlen( $token ) ) {
		case( 8 ):
			$db = &JFactory::getDBO();
			$query = 'SELECT *'
				."\n".'FROM '.$db->nameQuote( '#__apoth_api_request_tokens' )
				."\n".'WHERE '.$db->nameQuote( 'token' ).' = '.$db->Quote( $token );
			$db->setQuery( $query );
			$this->_token = $db->loadObject();
			break;
		
		case( 20 ):
			$d = date( 'Y-m-d H:i:s' );
			$db = &JFactory::getDBO();
			$query = 'SELECT *'
				."\n".'FROM '.$db->nameQuote( '#__apoth_api_access_tokens' )
				."\n".'WHERE '.$db->nameQuote( 'token' ).' = '.$db->Quote( $token )
				."\n".'  AND '.ApotheosisLibDb::dateCheckSql( 'valid_from', 'valid_to', $d, $d );
			$db->setQuery( $query );
			$this->_token = $db->loadObject();
			break;
		
		default:
			$this->_token = null;
			break;
		}
//		debugQuery( $db, $this->_token );
		return !empty($this->_token);
	}

	function authoriseToken()
	{
		$u = ApotheosisLib::getUser();
				
		$this->_token->verification = rand( 100, 999 ).'-'.rand( 100, 999 );
		$this->_token->person_id = $u->person_id;

		$db = &JFactory::getDBO();
		$query = 'UPDATE '.$db->nameQuote( '#__apoth_api_request_tokens' )
			.'SET '.$db->nameQuote( 'verification' ).' = '.$db->Quote( $this->_token->verification )
			."\n, ".$db->nameQuote( 'person_id' ).' = '.$db->Quote( $this->_token->person_id )
			."\n".'WHERE '.$db->nameQuote( 'token' ).' = '.$db->Quote( $this->_token->token )
			."\n".'  AND '.$db->nameQuote( 'person_id' ).' IS NULL';
		$db->setQuery( $query );
		$db->query();
		return $db->getErrorMsg() == '';

	}

	function removeToken()
	{
		$db = &JFactory::getDBO();
		$query = 'DELETE FROM '.$db->nameQuote( '#__apoth_api_request_tokens' )
			."\n".'WHERE '.$db->nameQuote( 'token' ).' = '.$db->Quote( $this->_token->token );
		$db->setQuery( $query );
		$db->query();
		return $db->getErrorMsg() == '';
	}

	function getTokenId()
	{
		return $this->_token->token;
	}
	
	/**
	 * Request tokens are only valid for authorisation while they exist and are not already used by anyone
	 */
	function getTokenIsValid()
	{
		return (is_object($this->_token) && is_null( $this->_token->person_id ) );
	}

	function getTokenCallback()
	{
		return $this->_token->callback;
	}

	function getTokenVerifier()
	{
		return $this->_token->verification;
	}

	// #####  Data for the views  #####

	function getRequestTokenInfo()
	{
		// Build response with the authorization URL users should be sent to
		return '&oauth_token='.$this->_token->token
			.'&oauth_token_secret='.$this->_token->secret
			.'&oauth_callback_confirmed=true';
	}
	
	function getAccessTokenInfo()
	{
		// Build response with the authorization URL users should be sent to
		return '&oauth_token='.$this->_token->token
			.'&oauth_token_secret='.$this->_token->secret;
	}
	
	// #####  Data derived from the token  #####
	
	/**
	 * Retrieves the juser id of the person who authorised the current token
	 */
	function getUser()
	{
		return ApotheosisLib::getJUserId( $this->_token->person_id );
	}
	
	/**
	 * Retrieves the person id of the person who authorised the current token
	 */
	function getPerson()
	{
		return $this->_token->person_id;
	}
}
?>