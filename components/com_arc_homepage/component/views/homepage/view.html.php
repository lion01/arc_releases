<?php
/**
 * @package     Arc
 * @subpackage  Homepage
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
 * Homepage Task View
 * 
 * @author     p.walker@wildern.hants.sch.uk
 * @package    Arc
 * @subpackage Homepage
 * @since      1.5
 */
class HomepageViewHomepage extends JView
{
	function __construct( $config = array() )
	{
		$document = &JFactory::getDocument();
		$document->setTitle( JText::_( 'Arc Homepage' ) );
		
		parent::__construct( $config );
	}
	
	/**
	 * Shows the main homepage or the eportfolio page
	 * Gets the task trees from the model and puts each under its own category heading
	 */
	function fullPage( $page )
	{
		$this->model = &$this->getModel();
		
		$params = JComponentHelper::getParams( 'com_arc_homepage' );
		$this->_user = $params->get( 'user' );
		$this->_pwd  = $params->get( 'pwd' );
		$this->profile = $this->get( 'profile' );
		
		global $mainframe;
		$f = $mainframe->getCfg( 'tmp_path' ).DS.'CURLCOOKIE';
		if( !file_exists($f) ) {
			$fh = fopen( $f, 'w' );
			if( $fh !== false ) {
				fclose( $fh );
			}
		}
		$this->_ckFile = $f;
		
		$this->c = curl_init();
		
		$this->_checkLogin();
		
		parent::display( $page );
		
		curl_close( $this->c );
	}
	
	/**
	 * To view most of the Arc pages requires the browser (curl) to be logged in.
	 * This checks the login page to see if we are already logged in, and logs in if we're not
	 */
	function _checkLogin()
	{
		// check the login page
		curl_setopt( $this->c, CURLOPT_RETURNTRANSFER, true );
		curl_setopt( $this->c, CURLOPT_COOKIEFILE, $this->_ckFile );
		curl_setopt( $this->c, CURLOPT_COOKIEJAR, $this->_ckFile );
		curl_setopt( $this->c, CURLOPT_FOLLOWLOCATION, true );
		curl_setopt( $this->c, CURLOPT_URL, JURI::base().'index.php?option=com_user&view=login' );
		$page1 = curl_exec( $this->c );
		
		// log in if necessary
		if( strpos($page1, '<input type="hidden" name="task" value="logout" />') === false ) {
			$matches = array();
			preg_match( '~<input type="hidden" name="(.*)" value="1".~', $page1, $matches );
			$somevar = $matches[1];
			$post = array( 'option'=>'com_user'
				, 'task'=>'login'
				, 'username'=>$this->_user
				, 'passwd'=>$this->_pwd
				, $somevar=>'1' );
			curl_setopt( $this->c, CURLOPT_POST, true );
			curl_setopt( $this->c, CURLOPT_POSTFIELDS, $post );
			curl_setopt( $this->c, CURLOPT_URL, JURI::base().'index.php' );
			curl_exec( $this->c );
		}
		curl_setopt( $this->c, CURLOPT_POST, false );
	}
}
?>