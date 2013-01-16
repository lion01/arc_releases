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
	/**
	 * The current browsing session
	 *
	 * @var object
	 */
	var $_session;
	
	/**
	 * Array of inclusion files for management in and out of the session
	 *
	 * @var array
	 */
	var $_incFiles = array();
	
	/**
	 * Array of model objects for management in and out of the session
	 * Keyed on model name
	 *
	 * @var array
	 */
	var $_models = array();
	
	/**
	 * Array of model type strings
	 * Keyed on model name
	 *
	 * @var array
	 */
	var $_modelTypes = array();
	
	/**
	 * The name of the model currently being managed
	 *
	 * @var string
	 */
	var $_modelName;
	
	/**
	 * Creates the controller and initialises all saved inclusion files
	 */
	function __construct( $config = array() )
	{
		// ### Security
		
		// Ensure we've checked access with our plugin, and that all is good there.
		// If it's not, redirect to somewhere safe.
		if( !defined('_ARC_ACL') || (_ARC_ACL !== true) ) {
			global $mainframe;
			$ref = ( isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : null );
			$rObj = JURI::getInstance( $ref );
			$cObj = JURI::getInstance();
			
			$target = ( (!JURI::isInternal($ref) || ($rObj->toString() == $cObj->toString())) ? 'index.php' : $ref );
			$mainframe->enqueueMessage( 'Access Denied. All Arc pages are protected by the Arc Access plugin which must be enabled for you to see these pages', 'error' );
			$mainframe->redirect( $target );
		}
		
		
		// ### Construction
		
		// With security done we can get on with constructing the controller
		parent::__construct( $config );
		$this->_session = &JSession::getInstance( 'none', array() );
		$this->_incFiles = $this->_session->get( 'incFiles', array() );
		
		// Ensure mootools library and modal (interstitial) behaviors are present by default
		JHTML::_('behavior.mootools');
		JHTML::_('behavior.modal'); 
		
		
		// ### Handle data to be set up prior to page load
		
		// 1) Passthrough is used by the search forms.
		//    If we have new data that needs to be saved, save it
		//    Otherwise, previously saved data needs to be retrieved 
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
		// previous data is to be retrieved and set as the post data,
		// not overwriting existing matching vars
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
	
	
	// ### General controller methods
	
	/**
	 * Default method
	 * Clears all session variables
	 *
	 * @access public
	 */
	function display()
	{
		// output clearance messages whilst nuking all the vars
		echo '<b>To use the system, select one of the items from the dropdown menus</b><br /><br />';
		echo 'Clearing cached data...<br /><br />';
		
		$output = array();
		foreach( $this->_incFiles as $varType=>$varNames ) {
			ob_start();
			$varType = ( $varType == 'factory' ) ? 'factorie' : $varType;
			echo '&nbsp;&nbsp;&nbsp;<i><b>'.$varType.'s:</b></i><br />';
			foreach( $varNames as $varName=>$incFiles ) {
				$this->_session->clear( $varName );
				echo '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;'.$varName.'<br />';
			}
			$output[] = ob_get_clean();
		}
		$output = implode( '<br />', $output );
		
		echo $output;
		echo '<br />...all cached data cleared.';
		
		// clear any persistence set in any current factories
		ApothFactory::clearPersistentFactories();
		
		// clear the session of incFiles
		$this->_incFiles = array();
		$this->_session->clear( 'incFiles' );
	}
	
	/**
	 * Retrieves the specified saved model object if there is one
	 * 
	 * @param string $type  The type of the requested model, also default name
	 * @param array $config  Configuration parameters (optional)
	 * @param string $name  The model name instead of default taken from $type (optional)
	 * @return object  The requested model
	 */
	function &getModel( $type, $config = array(), $name = false )
	{
		if( !is_array($config) ) {$config = array();}
		$this->_modelName = ( $name != false ) ? $name : $type ;
		
		// if the model we want hasn't been initialised then try to do so now
		if( empty($this->_models) || (!array_key_exists($this->_modelName, $this->_models)) ) {
			$sesModel = &$this->getVar( $this->_modelName, 'model' );
			
			if ( !is_null($sesModel) ) {
				$this->_models[$this->_modelName] = &$sesModel;
				
				// *** ApothModel hack
				// *** this check can be dropped once all models inherit ApothModel
				// *** at that point we can just auto delete models from the session
				// this model inherits from ApothModel and therefore uses persistence
				// then it is now safe to delete the serialised version
				if( is_a($sesModel, 'ApothModel') ) {
					$this->deleteModel( $this->_modelName );
				}
			}
			else {
				$this->_models[$this->_modelName] = &parent::getModel( $type, '', $config );
			}
			
			$this->_modelTypes[$this->_modelName] = $type;
		}
		
		// return the model which may have already existed or just been initialised
		return $this->_models[$this->_modelName];
	}
	
	/**
	 * Saves the current model to the session for later retrieval
	 * 
	 * @param string $name  The name of the model (optional)
	 * @param array $incFiles  An optional array of files to be included in future page loads, for example the class definition file for an object (optional)
	 */
	function saveModel( $name = false, $incFiles = array() )
	{
		if( $name === false ) {
			$name = $this->_modelName;
		}
		$type = $this->_modelTypes[$name];
		$incFiles[] = $this->_getClassDefFile( $type );
		
		if( method_exists($this->_models[$name], 'getIncFiles') ) {
			$incFiles = array_merge( $incFiles, $this->_models[$name]->getIncFiles() );
		}
		
		// *** ApothModel hack
		// *** we won't need to do this once all models inherit apothmodel
		// *** and set their own persistence for factory vars
		if( !is_a($this->_models[$name], 'ApothModel') ) {
			// we do not inherit apoth model so go through all vars and set all factory optional vars to persist
			foreach( $this->_models[$name] as $modelVarName=>$modelVar ) {
				if( is_a($modelVar, 'ApothFactory') ) {
					
					// persist all possible factory vars
					$this->_models[$name]->$modelVarName->setPersistent( 'instances',    true, ARC_PERSIST_ALWAYS );
					$this->_models[$name]->$modelVarName->setPersistent( 'searches',     true, ARC_PERSIST_ALWAYS );
					$this->_models[$name]->$modelVarName->setPersistent( 'structures',   true, ARC_PERSIST_ALWAYS );
					$this->_models[$name]->$modelVarName->setPersistent( 'searchParams', true, ARC_PERSIST_ALWAYS );
					
					// make sure this factory isn't saved in the session
					// as it will be saved in the relevant model as it always was
					$this->_models[$name]->$modelVarName->_doNotPersist = true;
					
					// make sure factory inc files get saved
					$incFiles = array_merge( $incFiles, $modelVar->getIncFiles() );
				}
			}
		}
		
		$this->saveVar( $name, $this->_models[$name], $incFiles, 'model' );
	}
	
	/**
	 * Deletes the current model from the session
	 * 
	 * @param string $name  The name of the model (optional)
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
	 * @param string $name  The name of the model
	 * @return string $file  The file with full path
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
	 * @param string $name  The name of the variable
	 * @param string $type  The type of variable (optional)
	 * @return object  The requested session variable
	 */
	function getVar( $name, $type = 'var' )
	{
		$varName = $this->getVarName( $name, $type );
		
		if( isset($this->_incFiles[$type][$varName]) ) {
			if( is_array($this->_incFiles[$type][$varName]) ) {
				foreach( $this->_incFiles[$type][$varName] as $file ) {
					require_once( $file );
				}
			}
			elseif( !is_null($this->_incFiles[$type][$varName]) ) {
				require_once( $this->_incFiles[$type][$varName] );
			}
		}
		
		return unserialize( $this->_session->get($varName, 'N;') );
	}
	
	/**
	 * Saves a variable and any associated required files in the session
	 * 
	 * @param string $name The name which will be used to retrieve this variable later
	 * @param mixed $var  The value to be saved
	 * @param mixed $incFiles  An optional file or array of files to be included in future page loads, for example the class definition file for an object (optional)
	 * @param string $type  The type of variable to save (optional)
	 */
	function saveVar( $name, $var, $incFiles = null, $type = 'var' )
	{
		$varName = $this->getVarName( $name, $type );
		
		if( is_array($incFiles) ) {
			$incFiles = array_unique( $incFiles );
		}
		
		$this->_incFiles[$type][$varName] = $incFiles;
		$this->_session->set( 'incFiles', $this->_incFiles );
		$this->_session->set( $varName, serialize($var) );
	}
	
	/**
	 * Deletes a variable and any associated required files from the session
	 * 
	 * @param string $name  The name identifier of the saved model
	 * @param string $type  The type of variable to delete (optional)
	 */
	function deleteVar( $name, $type = 'var' )
	{
		$varName = $this->getVarName( $name, $type );
		
		if( isset( $this->_incFiles[$type] ) && is_array($this->_incFiles[$type]) && array_key_exists($varName, $this->_incFiles[$type]) ) {
			// unset the specified vars incFiles
			unset( $this->_incFiles[$type][$varName] );
			
			// if all the incFiles for the specified type have been unset, then unset the type array also
			if( empty($this->_incFiles[$type]) ) {
				unset( $this->_incFiles[$type] );
			}
		}
		
		$this->_session->set( 'incFiles', $this->_incFiles );
		$this->_session->clear( $varName );
	}
	
	/**
	 * Determines the name of the session variable
	 * 
	 * @param string $name  The passed name of the variable
	 * @return string $varName  The session var name based on component, var type and passed name
	 */
	function getVarName( $name, $type )
	{
		if( $type == 'passthrough' ) {
			$varName = $type.'.'.$name;
		}
		elseif( $type == 'factory' ) {
			$identParts = explode( '.', $name );
			$varName = 'com_arc_'.$identParts[0].'.'.$type.'.'.$identParts[1];
		}
		else {
			$component = JRequest::getVar( 'option' );
			$varName = $component.'.'.$type.'.'.$name;
		}
		
		return $varName;
	}
	
	/**
	 * Convenience function to allow legacy components to get their links
	 * using updated methodologies
	 * 
	 * @param array|false $requirements  Array of requiremts for the link or false
	 * @param array $dependancies  Array of dependencies for the link
	 * @return string $link  The link determined from requirements and dependencies
	 */
	function _getLink( $requirements = false, $dependancies = array() )
	{
		// as this is a convenience function to wrap around getActionId
		// we'll fill in current values for option and view
		if( $requirements !== false ) {
			if( !array_key_exists('option', $requirements) && (($val = JRequest::getVar('option', false)) !== false) ) {
				$requirements['option'] = $val;
			}
			if( !array_key_exists('view',   $requirements) && (($val = JRequest::getVar('view',   false)) !== false) ) {
				$requirements['view'] = $val;
			}
		}
		$actionId = ApotheosisLib::getActionId( $requirements, $dependancies );
		$link = ApotheosisLib::getActionLink( $actionId, $dependancies );
		
		return $link;
	}
	
	/**
	 * Intercept the component entry point call to JController->execute().
	 * We passthrough the name of the derived task.
	 * Check to see if any models have persistence set and if so save those models.
	 * Tell ApothFactory to save any factories needing persistence.
	 * 
	 * @see JController::execute()
	 * @param string $task  The task passed through from the child entry point controller
	 */
	function execute( $task )
	{
		// pass the derived task through from the component entry point
		parent::execute( $task );
		
		$this->_savePersistent();
	}
	
	function _savePersistent()
	{
		// after page has finished loading we perform persistence checks on all models,
		// loop through each of the models associated with this MVC
		foreach( $this->_models as $name=>$model ) {
			// *** ApothModel hack
			// *** the first if() check can be dropped once all models inherit ApothModel
			// *** at that point we can just call hasPersistent() automatically
			// does this model inherit from ApothModel
			if( is_a($model, 'ApothModel') ) {
				// do we have any vars to sleep?
				if( $this->_models[$name]->hasPersistent() ) {
					$this->saveModel( $name );
				}
			}
		}
		
		// instruct ApothFactory to save its persistent factories
		ApothFactory::savePersistentFactories();
	}
	
	/**
	 * Provides a wrapper for $mainframe call to redirect()
	 * Directly calling $mainframe->redirect skips the saving that execute performs
	 * All parameters to this function are as for redirect
	 * 
	 * @param $url
	 * @param $msg
	 * @param $msgType
	 * @param $moved
	 */
	function saveAndRedirect( $url, $msg='', $msgType='message', $moved = false )
	{
		$this->_savePersistent();
		global $mainframe;
		$mainframe->redirect( $url, $msg, $msgType, $moved );
	}
	
	/**
	 * Provides a wrapper for $mainframe call to enqueueMessage().
	 * Since JController->setMessage() cannot set a message type
	 * we need to use JApplication->enqueueMessage() and wrapping it here
	 * means no call to $mainframe in the child controllers
	 * 
	 * @param string $msg  The message to enqueue
	 * @param string $type  The message type
	 */
	function enqueueMessage( $msg, $type = 'message' )
	{
		global $mainframe;
		$mainframe->enqueueMessage( $msg, $type );
	}
}
?>