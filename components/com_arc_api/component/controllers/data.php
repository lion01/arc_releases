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
class ApiControllerData extends ApiController
{
	/**
	 * Default action.
	 * Calls appropriate display function
	 */
	function display()
	{
		$view = &$this->getView( 'data', 'html' );
		$view->display();
	}
	
	/**
	 * Performs a data read
	 * and outputs in the appropriate format
	 */
	function read()
	{
		$auth = &$this->getModel( 'oauth' );
		$auth->setProvider();
		if( $auth->getError() ) {
			echo 'OAuth access request failed';
			return false;
		}
		
		$session  =& JFactory::getSession();
		$origUser = $session->get( 'user' );
		$session->set('user', JUser::getInstance($auth->getUser()));
		
		$model = &$this->getModel( 'data' );
		$view =  &$this->getView( 'data', JRequest::getVar( 'format', 'xml' ) );
		
		$call = JRequest::getVar( 'call' );
		$parts = explode( '.', $call, 3 );
		switch( count($parts) ) {
		case( 2 ):
			$params = array();
			break;
		
		case( 3 ):
			$params = json_decode( urldecode( $parts[2] ), true);
			break;

		default:
			return;
		}
		
		$model->read( $parts[0].'.'.$parts[1], $params );
		
		$view->setModel( $model, true );
		$view->display();
		
		$session->set( 'user', $origUser );
	}
	
	/**
	 * Performs a data read
	 * and outputs in the appropriate format
	 */
	function write()
	{
//		echo 'post: '.var_export( $_POST, true )."\r\n";
		
		$auth = &$this->getModel( 'oauth' );
		$auth->setProvider();
		if( $auth->getError() ) {
			echo 'OAuth access request failed';
			return false;
		}
		
		$session  =& JFactory::getSession();
		$origUser = $session->get( 'user' );
		$session->set('user', JUser::getInstance($auth->getUser()));
		
		$model = &$this->getModel( 'data' );
		$view =  &$this->getView( 'data', JRequest::getVar( 'format', 'xml' ) );
		
		$call = JRequest::getVar( 'call' );
		$parts = explode( '.', $call, 3 );
		$params = JRequest::get( 'POST' );
		
		$model->write( $parts[0].'.'.$parts[1], $params );
		
		$view->setModel( $model, true );
		$view->display();
		
		$session->set( 'user', $origUser );
	}
}
?>