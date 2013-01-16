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

jimport( 'joomla.application.component.controller' );
jimport( 'joomla.application.helper' );

/**
 * Apotheosis Controller
 *
 * @author     lightinthedark <code@lightinthedark.org.uk>
 * @package    Arc
 * @subpackage Core
 * @since      1.2
 */
class ApothController extends JController
{
	var $_session;
	var $_models;
	var $_modelName;
	var $_cycle;
	var $_links; // store of links retrieved so far
	
	/**
	 * Creates the controller (of course), and initialises all saved inclusion files
	 */
	function __construct( $config = array() )
	{
		JHTML::_('behavior.mootools'); 
		JHTML::_('behavior.modal'); 
		
		// Ensure we've checked access with our plugin, and that all is good there
		// if it's not, redirect to somewhere safe
		if( !defined( '_ARC_ACL' ) || _ARC_ACL !== true ){
			global $mainframe;
			$ref = ( isset( $_SERVER['HTTP_REFERER'] ) ? $_SERVER['HTTP_REFERER'] : null );
			$rObj = JURI::getInstance($ref);
			$cObj = JURI::getInstance();
			
			$target = ( (!JURI::isInternal($ref) || ($rObj->toString() == $cObj->toString())) ? 'index.php' : $ref );
			$mainframe->enqueueMessage( 'Access Denied. All Arc pages are protected by the Arc Access plugin which must be enabled for you to see these pages', 'error' );
			$mainframe->redirect( $target );
		}
		
		parent::__construct( $config );
		$this->_session = &JSession::getInstance( 'none', array() );
		$this->_incFiles = $this->_session->get( 'incFiles', array() );
		
		
		// Handle data to be set up prior to page load
		
		// 1) Passthrough is used by the search forms.
		//     If we have new data that needs to be saved
		//     Otherwise, previously saved data needs to be retrieved 
		$pt = false;
		// if either post or get has the passthrough flag, use that data for passthrough
		$post = JRequest::get( 'post' );
		if( isset($post['passthrough']) ) {
			$pt = $post;
		}
		else {
			$get = JRequest::get( 'get' );
			if( isset($get['passthrough']) ) {
				$pt = $get;
			}
		}
		// new passthrough data is to be cleaned of system variables then saved under 'search'
		if( is_array($pt) ) {
			unset( $pt['option'] );
			unset( $pt['view'] );
			unset( $pt['scope'] );
			unset( $pt['task'] );
			unset( $pt['Itemid'] );
			$this->saveVar( 'search', $pt, null, 'passthrough' );
		}
		// previous data is to be retrieved 
		// and set as the post data, not overwriting existing matching vars
		else {
			$pt = $this->getVar( 'search', 'passthrough' );
			if( !is_null($pt) ) {
				JRequest::set( $pt, 'post', false );
			}
		}
		
		// 2) The request var with a type of preLoad needs to be set in JRequest
		//    then removed so as not to appear next time
		$r = $this->getVar( 'request', 'preLoad' );
		if( is_array($r) ) {
			JRequest::set( $r, 'post', true );
		}
		$this->deleteVar( 'request', 'preLoad' );
	}
	
	/**
	 * Default method
	 * Clears all session variables
	 *
	 * @access public
	 */
	function display()
	{
		echo '<h3>To use the system, select one of the items from the dropdown menus</h3>';
		echo '<h4>Clearing cached data...</h4>';
		foreach( $this->_incFiles as $varName=>$v ) {
			$this->_session->clear( $varName );
			echo $varName.'<br />';
		}
		$this->_incFiles = array();
		$this->_session->clear( 'incFiles' );
		echo '<h4>...all cached data cleared </h4>';
	}
	
	/**
	 * Retrieves the already created model object if there is one
	 * Initialises this->_session in the process
	 * 
	 * @param string $type The type of the requested model, also default name
	 * @param array $config Configuration parameters (optional)
	 * @param string $name The model name instead of default taken from $type (optional)
	 * @return object The requested model
	 */
	function &getModel( $type, $config = array(), $name = false )
	{
		if( !is_array($config) ) {$config = array();}
		$this->_modelName = ( $name != false ? $name : $type );
		
		if( empty($this->_models) || (!array_key_exists($this->_modelName, $this->_models)) ) {
			$ses_model = $this->getVar( $this->_modelName, 'model' );
			
			if ( !is_null($ses_model) ) {
//				echo 'using existing model (type: '.$type.', name: '.$this->_modelName.')<br />';
				$this->_models[$this->_modelName] = $ses_model;
			}
			else {
//				echo 'using new model (type: '.$type.', name: '.$this->_modelName.')<br />';
				$this->_models[$this->_modelName] = &parent::getModel( $type, '', $config );
			}
			$this->_modelTypes[$this->_modelName] = $type;
		}
		// if we've already initialised the model, don't over-write it, just return it
		return $this->_models[$this->_modelName];
	}
	
	/**
	 * Saves the current model to the session for later retrieval
	 * 
	 * @param string $name The name of the model (optional)
	 * @param array $incFiles An optional array of files to be included in future page loads, for example the class definition file for an object (optional)
	 */
	function saveModel( $name = false, $incFiles = array() )
	{
		if( $name === false ) {
			$name = $this->_modelName;
		}
		$type = $this->_modelTypes[$name];
		$incFiles[] = $this->_getClassDefFile( $type );
		
		if( method_exists($this->_models[$name], 'getIncFiles') ) {
			$incFiles = array_merge( $incFiles, $this->_models[$name]->getIncFiles(), ApothFactory::getIncFiles() );
		}
		else {
			$incFiles = array_merge( $incFiles, ApothFactory::getIncFiles() );
		}
		
		$this->saveVar( $name, $this->_models[$name], $incFiles, 'model' );
	}
	
	/**
	 * Deletes the current model from the session
	 * 
	 * @param string $name The name of the model (optional)
	 */
	function deleteModel( $name = false )
	{
		if( $name === false ) {
			$name = $this->_modelName;
		}
		
		$this->deleteVar( $name, 'model' );
	}
	
	/**
	 * Finds the file containing the model class definition
	 * 
	 * @param string $name The name of the model
	 * @return string $file The file with full path
	 */
	function _getClassDefFile( $name )
	{
		jimport( 'joomla.filesystem.path' );
		
		$file = JPath::find(
			JModel::addIncludePath(),
			JModel::_createFileName( 'model', array('name' => $name) )
		);
		
		return $file;
	}
	
	/**
	 * Retrieves a variable from the session, any associated files are also retrieved and required
	 * 
	 * @param string $name The name of the variable
	 * @param string $type The type of variable (optional)
	 * @return object The requested session variable
	 */
	function getVar( $name, $type = 'var' )
	{
		$varName = $this->_getVarName( $name, $type );
		
		if( isset($this->_incFiles[$varName]) ) {
			if( is_array($this->_incFiles[$varName]) ) {
				foreach( $this->_incFiles[$varName] as $k=>$file ) {
					require_once( $file );
				}
			}
			elseif( !is_null($this->_incFiles[$varName]) ) {
				require_once( $this->_incFiles[$varName] );
			}
		}
		
		return unserialize( $this->_session->get($varName, 'N;') );
	}
	
	/**
	 * Saves a variable and any associated required files in the session
	 * 
	 * @param string $name The name which will be used to retrieve this variable later
	 * @param mixed $var The value to be saved
	 * @param mixed $incFiles An optional file or array of files to be included in future page loads, for example the class definition file for an object (optional)
	 * @param string $type The type of variable to save (optional)
	 */
	function saveVar( $name, $var, $incFiles = null, $type = 'var' )
	{
		$varName = $this->_getVarName( $name, $type );
		
		if( is_array($incFiles) ) {
			$incFiles = array_unique( $incFiles );
		}
		
		$this->_incFiles[$varName] = $incFiles;
		$this->_session->set( 'incFiles', $this->_incFiles );
		$this->_session->set( $varName, serialize($var) );
	}
	
	/**
	 * Deletes a variable and any associated required files from the session
	 * 
	 * @param string $name The name identifier of the saved model
	 * @param string $type The type of variable to delete (optional)
	 */
	function deleteVar( $name, $type = 'var' )
	{
		$varName = $this->_getVarName( $name, $type );
		
		if( is_array($this->_incFiles) && array_key_exists($varName, $this->_incFiles) ) {
			unset( $this->_incFiles[$varName] );
		}
		
		$this->_session->set( 'incFiles', $this->_incFiles );
		$this->_session->clear( $varName );
	}
	
	/**
	 * Determines the name of the session variable
	 * 
	 * @param string $name The passed name of the variable
	 * @return string $varName The session var name based on component, var type and passed name
	 */
	function _getVarName( $name, $type )
	{
		$component = JRequest::getVar( 'option' );
		if( $type == 'passthrough' ) {
			$varName = $type.'.'.$name;
		}
		else {
			$varName = $component.'.'.$type.'.'.$name;
		}
		
		return $varName;
	}
	
	function _getLink( $requirements = false, $dependancies = array() )
	{
		// as this is a convenience function to wrap around getActionId
		// we'll fill in current values for option and view
		if( $requirements !== false ) {
			if( !array_key_exists('option', $requirements) && ($val = JRequest::getVar('option', false)) !== false ) {
				$requirements['option'] = $val;
			}
			if( !array_key_exists('view',   $requirements) && ($val = JRequest::getVar('view',   false)) !== false ) {
				$requirements['view'] = $val;
			}
		}
		$actionId = ApotheosisLib::getActionId( $requirements, $dependancies );
		$link = ApotheosisLib::getActionLink( $actionId, $dependancies );
		return $link;
	}
}
?>
