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

/**
 * Planner Task Object
 */
class ApothPanel extends JObject
{
	/**
	 * All the data for this panel (equates to a row in the db)
	 * @access protected
	 * @var array
	 */
	var $_data = array();
	
	/**
	 * Constructs a panel object.
	 * The result is either empty or if an ID is given it is
	 * populated by the $data array or by retrieving data from the db
	 * @param int $id  optional If not provided an empty panel object is created.
	 * @param array $data  optional If given along with an id this is used as the data for the object
	 * @return object  The newly created panel object
	 */
	function __construct( $id = false, $data = array() )
	{
		parent::__construct();
		
		if( $id !== false ) {
			// get a database object
			$db = &JFactory::getDBO();
			
			// get data for the task if none supplied
			if( empty($data) ) {
				$query = 'SELECT *'
					."\n".' FROM '.$db->nameQuote('#__apoth_home_panels').' AS p'
					."\n".' WHERE '. $db->nameQuote( 'id' ) .' = '.$db->Quote($id);
				$db->setQuery( $mainQuery );
				$data = $db->loadAssoc();
			}
			// store the data in the object
			$this->_data = $data;
		}
	}
	
	/**
	 * Links the given profile with this panel
	 *
	 * @param object $profile  The profile to link with.
	 */
	function setProfile( &$profile )
	{
		$this->_profile = &$profile;
	}
	
	/**
	 * Retrieves the ID of the panel
	 * @return int  The panel's ID
	 */
	function getId()
	{
		return $this->_data['id'];
	}
	
	/**
	 * Retrieves the type of the panel
	 * @return string  The task type ('internal' or 'external' or 'module')
	 */
	function getType()
	{
		return $this->_data['type'];
	}
	
	/**
	 * Retrieves the url(s) to use for this panel, with the current user's relevant id injected
	 * @return string  The panel url(s)
	 */
	function getUrl()
	{
		if( $this->_data['type'] == 'internal' ) {
			$url = 'index.php?option='.$this->_data['option'].$this->_data['url'];
		}
		else {
			$url = $this->_data['url'];
		}
		
		$ids = $this->_profile->getIds();
		$patterns = array();
		foreach( $ids as $k=>$v ) {
			$patterns[] = '~'.preg_quote( '~'.$k.'~', '~' ).'~';
		}
		return preg_replace( $patterns, $ids, $url );
	}
	
	/**
	 * Retrieves the panel title
	 * @return string  The panel title for display in the panel header
	 */
	function getAltText()
	{
		return $this->_data['alt'];
	}
	
	/**
	 * Sets the text to display in the panel
	 * @param string $text  The text to display
	 */
	function setText( $text )
	{
		$this->_data['text'] = $text;
	}
	
	/**
	 * Retrieves the text to display in the panel
	 * @return string  The text to display
	 */
	function getText()
	{
		return $this->_data['text'];
	}
	
	/**
	 * Sets the parameters to be used when rendering this panel.
	 *
	 * @param array $params  Associative array of property=>value pairs
	 */
	function setParams( $params )
	{
		$this->_params = $params;
	}
	
	/**
	 * Gets the parameters to be used when rendering this panel.
	 *
	 * @param string $param  The property whose value is to be retrieved
	 * @return string|null  The string stored for that property, or null if not set
	 */
	function getParam( $param )
	{
		return ( isset($this->_params[$param]) ? $this->_params[$param] : null );
	}
	
	/**
	 * Retrieves the option to be used with this panel
	 * @return string|null  The panel option or null
	 */
	function getOption()
	{
		return $this->_data['option'];
	}
	
	/**
	 * Retrieves the location of the javascript files to be used with this panel
	 * @return array|null  The panel javascript locations or null
	 */
	function getJscript()
	{
		return ( !isset( $this->_data['jscript'] ) || is_null( $this->_data['jscript'] ) ) ? null : explode("\n", str_replace("\r", '', $this->_data['jscript']));
	}
	
	/**
	 * Retrieves the location of the css files to be used with this panel
	 * @return array|null  The panel css locations or null
	 */
	function getCSS()
	{
		return ( !isset( $this->_data['css'] ) || is_null( $this->_data['css'] ) ) ? null : explode("\n", str_replace("\r", '', $this->_data['css']));
	}
}
?>
