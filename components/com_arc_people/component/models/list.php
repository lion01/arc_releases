<?php
/**
 * @package     Arc
 * @subpackage  People
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

 /*
 * People Manager List Model
 *
 * @author     lightinthedark <code@lightinthedark.org.uk>
 * @package    Arc
 * @subpackage People Manager
 * @since      0.1
 */
class PeopleModelList extends JModel
{
	/**
	 * Declare relative search variable
	 * @access protected
	 * @var boolean
	 */
	var $_relSearch = false;
	
	/**
	 * Declare people list variable
	 * @access protected
	 * @var array
	 */
	var $_people = array();
	
	/**
	 * Constructs the people list model
	 */
	function __construct()
	{
		parent::__construct();
		$this->fPeo = ApothFactory::_( 'people.person', $this->fPeo );
	}
	
	/**
	 * Fetch a list of people
	 * 
	 * @return array  Array of person objects
	 */
	function getPeople()
	{
		$people = array();
		foreach( $this->_people as $id ) {
			$people[] = $this->fPeo->getInstance( $id );
		}
		
		return $people;
	}

	/**
	 * Set a list of people, loading them if necessary
	 * 
	 * @param array $requirements  Array of requiremnts on which to search
	 */
	function setPeople( $requirements = array() )
	{
		if( !empty($requirements) ) {
			$this->_people = $this->fPeo->getInstances( $requirements );
			$this->_relSearch = !empty( $requirements['relOf'] );
		}
	}
	
	/**
	 * Did we perform a "relative of" type of search?
	 * 
	 * @return boolean  True for yes, false for no
	 */
	function getRelSearch()
	{
		return $this->_relSearch;
	}
}
?>