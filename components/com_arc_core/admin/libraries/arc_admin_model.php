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

// Give us access to the joomla model class
jimport( 'joomla.application.component.model' );

/**
 * Arc Admin Model
 *
 * @author     lightinthedark <code@lightinthedark.org.uk>
 * @package    Arc
 * @subpackage Core
 * @since      1.2
 */
class ArcAdminModel extends JModel
{
	/**
	 * The params object for the component
	 * 
	 * @var JParameter
	 */
	var $_params;
	
	/**
	 * The name of the component
	 * 
	 * @var string
	 */
	var $_component;
	
	function __construct( $config = array() )
	{
		if( isset($config['component']) ) {
			$this->_component = $config['component'];
			unset( $config['component'] );
		}
		else {
			$this->_component = '';
		}
		parent::__construct( $config );
	}
	
	/**
	 * Retrieve the configuration options
	 */
	function &getParams()
	{
		if( !isset($this->_params) ) {
			$this->_loadParams();
		}
		
		return $this->_params;
	}
	
	/**
	 * Load up the configuration options
	 */
	function _loadParams()
	{
		$this->_params = JComponentHelper::getParams( $this->_component );
		$this->_params->loadSetupFile(JPATH_COMPONENT.DS.'config.xml');
	}
	
	/**
	 * Saves the parameters to the database
	 *
	 * @param string  The name of the component whose parameters are to be set
	 * @param array  Optional. If provided must be all the values to be set for the component's settings,
	 *               if not provided then the current values are saved
	 */
	function saveParams( $data = false )
	{
		if( !isset($this->_params) ) {
			$this->_loadParams();
		}
		if( $data === false ) {
			$data = $this->_params->toArray();
		}
		$this->_params->bind( $data );
		
		$table = &JTable::getInstance( 'component' );
		$table->loadByOption( $this->_component );
		$table->bind( array('params'=>$data) );
		return $table->store();
	}
	
}
?>
